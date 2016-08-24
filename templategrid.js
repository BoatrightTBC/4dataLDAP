function validateTemplate(row) {
    return validateCommon(row);
}

function templateDataLoaded() {
    var grid = $("#templateList");
    var ids = grid.getDataIDs();
    for (var i = 0; i < ids.length; i++) {
        var id = ids[i];
        var row = grid.getRowData(id);
        row['pay_meth'] = getJob(row['jobcode'])['pay_meth'];
        if (row['pay_meth'] === "") {
            row['pay_meth'] = "X";
        }
        row['job_descr'] = getJob(row['jobcode'])['task_descr'];
        if (row['job_descr'] === "") {
            row['job_descr'] = "Invalid Job Code";
        }
        grid.setRowData(id, row);
    }
}

function newTemplate() {
    $("#templateList").editGridRow("new", {
        modal : true,
        //custom parameter added to the jqgrid code
        //function that generates extra parameters to be passed to the server
        //  should return an array of params with each param being of the form [name, value]
        // extraParams not currently in use
        closeAfterAdd : true,
        width : 450,
        height : 300,
        left : 0,
        onInitializeForm : initFormEditTemplate,
        beforeShowForm : setFormDataNewTemplate,
        afterSubmit : function (a, b) {
            var ret = new Array();
            ret[0] = checkSave(a.responseText);
            return ret;
        },
        beforeSubmit : function (data) {
            var ret = new Array();
            ret[0] = validateTemplate(data);
            return ret
        }
    });
}

function editTemplate() {
    $("#templateList").editGridRow($("#templateList").getGridParam('selrow'), {
        modal : true,
        closeAfterAdd : true,
        closeAfterEdit : true,
        width : 450,
        height : 400,
        onInitializeForm : initFormEditTemplate,
        beforeShowForm : setFormDataEditTemplate,
        afterSubmit : function (a, b) {
            ret = new Array();
            ret[0] = checkSave(a.responseText);
            return ret;
        },
        beforeSubmit : function (data) {
            data.time_in = $("#time_in").val();
            data.time_out = $("#time_out").val();
            data.date = $("#maindate").val();
            if (data.units === undefined ) {
                data.units = $("#units").val();
            }
            var ret = new Array();
            ret[0] = validateTemplate(data);
            return ret
        }
    });
}

function selectEmployees() {
    $("#employeeMultiSelect").val("");
    var tgrid = $("#templateList");
    if (!(tgrid.getGridParam('selrow'))) {
        alert("You must choose a template");
        return;
    } else {
        passTemplateRow = tgrid.getRowData(tgrid.getGridParam('selrow'));
        passTemplateRow.date = $("#maindate").val();
    }
    $("#employeeselect_div").jqmShow();
}
function selectedEmployees() {
    var params = {}
    params['user'] = _user;
    var selected = $("#employeeMultiSelect").selectedValues();
    for (var i = 0; i < selected.length; i++) {
        params['employees[' + i + ']'] = selected[i];
    }
    params['template'] = $("#templateList").getGridParam('selrow');
    params['date'] = $("#maindate").val();
    $.post('savedata.php?save=applyTemplate', params, function (data) {
        if (parseInt(data, 10) == data) {
            if (data != 0)
                alert(data + " Duplicates would have been, but were not created. ");
            //$("#employeeselect_div").jqmHide();
            $("#list").trigger("reloadGrid");
        } else {
            alert(data);
        }
    });
    $("#employeeselect_div").jqmHide();
    $("#template_div").jqmHide();
}

function initFormEditTemplate(form) {
    $(form[0].jobcode).focus(function () {
        showJobSelect(this, "template")
    });
    $(form[0].jobcode).blur(function (e) {
        _jobinput = e.target;
        setTimeout('hideJobSelect(_jobinput)', 500)
    });
    $("<tr class='formdata'>" +
        "<td class='captiontd'>Job Desc:</td>" +
        "<td><span id='job_desc_template'></span></td>" +
        "</tr>").appendTo($("#TblGrid_templateList"));
    $("<tr class='formdata'>" +
        "<td class='captiontd'>PM:</td>" +
        "<td><span id='pay_meth_template'></span></td>" +
        "</tr>").appendTo($("#TblGrid_templateList"));
     $("<h3 style='align=center;'>Templates are always applied <br>using the default date.</h3>").appendTo($("#editcnttemplateList"));
    $(form[0].time_in).attr('type', 'number');
	$(form[0].time_out).attr('type', 'number');
	$(form[0].units).attr('type', 'number');
	$("#units")
        .focus(function () {
            $(this).select();
        })
        .mouseup(function (e) {
            e.preventDefault();
        });
	$("#time_in")
        .focus(function () {
            $(this).select();
        })
        .mouseup(function (e) {
            e.preventDefault();
        });
	$("#time_out")
        .focus(function () {
            $(this).select();
        })
        .mouseup(function (e) {
            e.preventDefault();
        });
}

function setFormDataNewTemplate(form) {
    var datefield = $(form[0].date);
    var d = new Date();
    datefield.val($.datepicker.formatDate("yy-mm-dd", d));
    $(form[0].trainer).val(_defaults['trainer']);
    $(form[0].time_in).val(_defaults['time_in']);
    $(form[0].time_out).val(_defaults['time_out']);
}

function setFormDataEditTemplate(form) {
    var job = getJob($(form[0].jobcode).val());
    $("#job_desc_template").text(job['task_descr']);
    $("#pay_meth_template").text(job['pay_meth']);
}

function initTemplateGrid() {
    jQuery("#templateList").jqGrid({
        url : 'getdata.php?get=templates&user=' + _user,
        datatype : 'json',
        jsonReader : {
            repeatitems : false,
            cell : "",
            id : "id"
        },
        colNames : ['Date',
            'In',
            'Out',
            'Trnr',
            'Job',
            'PM',
            'Units',
            'Job Desc',
            'WO'],
        colModel : [{
                name : 'date',
                index : 'date',
                editable : true,
                editoptions : {
                    size : 10,
                    maxlength : 10
                },
                sorttype : 'date',
                width : 90,
                hidden : true
            }, {
                name : 'time_in',
                index : 'time_in',
                editable : true,
                editoptions : {
                    size : 4,
                    maxlength : 4
                },
                width : 50
            }, {
                name : 'time_out',
                index : 'time_out',
                editable : true,
                editoptions : {
                    size : 4,
                    maxlength : 4
                },
                width : 50
            }, {
                name : 'trainer',
                index : 'trainer',
                hidden : true,
                editable : true,
                editoptions : {
                    size : 2,
                    maxlength : 2
                },
                width : 10
            }, {
                name : 'jobcode',
                index : 'jobcode',
                editable : true,
                editoptions : {
                    size : 4
                },
                width : 50
            }, {
                name : 'pay_meth',
                index : 'pay_meth',
                sortable : false,
                width : 50
            }, {
                name : 'units',
                index : 'units',
                editable : true,
                editoptions : {
                    size : 4
                },
                width : 45
            }, {
                name : 'job_descr',
                index : 'job_descr',
                sortable : false
            }, {
                name : 'workorder',
                index : 'workorder',
                editable : true,
                editoptions : {
                    size : 15,
                    maxlength : 15
                },
                width : 60
            }
        ],
        multiSort : true,
        sortname : 'time_in, jobcode',
        sortorder : "asc",
        viewrecords : true,
        height : '250px',
        imgpath : 'themes/basic/images',
        editurl : "savedata.php?save=template&user=" + _user,
        loadComplete : templateDataLoaded
    });
    $("#editTemplate").click(editTemplate);
    $("#employeesTemplate").click(selectEmployees);
    $("#employeeSelectDone").click(selectedEmployees);
}
