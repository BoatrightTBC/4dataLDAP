var _mainTrainerField;
var _mainForm;
var c = 0;

function getDuration(time_in, time_out) {
    time_in = parseInt(time_in, 10);
    time_out = parseInt(time_out, 10);
    time_in = Math.floor(time_in / 100) + (time_in % 100) / 60.0;
    time_out = Math.floor(time_out / 100) + (time_out % 100) / 60.0;
    var thehours = Math.round((time_out - time_in) * 100) / 100;
    return thehours.toFixed(2);
}

function initInlineEditMain(id) {
	$("#" + id + "_date", "#list").datepicker({
		showOn : 'both',
		dateFormat : "yy-mm-dd",
		buttonImage : "calendar.gif",
		buttonImageOnly : true,
		maxDate : '+0',
		minDate : '-31'
	});
	$("#" + id + "_jobcode", "#list").focus(function () {
		showJobSelect(this, "main");
	});
	//using setTimeout so that the onclick of the jobSelect list has time to trigger otherwise this could go first
	$("#" + id + "_jobcode", "#list").blur(function (e) {
		_jobinput = e.target;
		setTimeout('hideJobSelect(_jobinput)', 500);
	});
	$("#" + id + "_time_in, #" + id + "_time_out").blur(function (e) {
		$("#hours_span").text(
			getDuration($("#" + id + "_time_in").val(), $("#" + id + "_time_out").val()));
	});
}

function validateMain(row) {
	if (!validateCommon(row)) {
		return false;
	}
	//row = removeSpaces(row);
	//hideJobSelect(_focusedJobInput);
	//post validation
	$("#hours_span").text(getDuration(row.time_in, row.time_out));
	$("#error_span").html(row.error);
	//row['error'] = errorsSingleRow(row['id']);
	//$("#" + row['id'] + "_error", "#list").val(row['error']);
	if (lastsel == row.id) {
		lastsel = null;
	}
	return true;
}

function gridDataLoadedMain() {
	//var grid = $("#list");
	//var ids = grid.getDataIDs();
	// for (var i = 0; i < ids.length; i++) {
	// var id = ids[i];
	// var row = grid.getRowData(id);
	// row['pay_meth'] = getJob(row['jobcode'])['pay_meth'];
	// grid.setRowData(id, row);
	// }
	lastsel = null;
	checkErrors();
	if (_newRow !== null) {
		editRowMain(_newRow);
		_newRow = null;
	}
}

function checkErrors() {
	var grid = $("#list");
	if (lastsel) {
		grid.saveRow(lastsel, checkSave);
	} else {
		checkErrorsHelper();
	}
}
function checkErrorsHelper() {
	lastsel = null;
	var grid = $("#list");
	var ids = grid.getDataIDs();
	var row1 = [];
	var row2 = [];
	var job = [];
	var error = '';
	var rowChanged = false;
	for (var i = 0; i < ids.length; i++) {
		var id = ids[i];
		row1[i] = grid.getRowData(id);
		row2[i] = row1[i];
	}
	for (i = 0; i < ids.length; i++) {
		rowChanged = false;
		error = "";
		if (row1[i].time_in.length != 4) {
			error += "In,";
			rowChanged = true;
		}
		if (row1[i].time_out.length != 4) {
			error += "Out,";
			rowChanged = true;
		}
		if (row1[i].jobcode == 0) {
			error += "No Job, ";
			rowChanged = true;
		}
		if (row1[i].time_in == row1[i].time_out) {
			error += "0 hours, ";
			rowChanged = true;
		}
		job = getJob(row1[i].jobcode)
			if (row1[i].pay_meth !== job.pay_meth) {
				row1[i].pay_meth = job.pay_meth;
				rowChanged = true;
			}
			if (row1[i].pay_meth == 'U' && row1[i].units == "0") {
				error += "Units,";
				rowChanged = true;
			}
		for (var j = i + 1; j < ids.length; j++) {

			if (row1[i].emplno < row2[j].emplno) {
				break;
			}
			if (row1[i].emplno > row2[j].emplno) {
				continue;
			}
			if (row1[i].date < row2[j].date) {
				break;
			}
			if (row1[i].date == row2[j].date) {
				var i_in = parseInt(row1[i].time_in, 10);
				var j_in = parseInt(row2[j].time_in, 10);
				var i_out = parseInt(row1[i].time_out, 10);
				var j_out = parseInt(row2[j].time_out, 10);
				if (isNaN(i_in) && isNaN(i_out))
					break;
				if (isNaN(j_in) && isNaN(j_out))
					continue;
				if (isNaN(i_in))
					i_in = i_out;
				if (isNaN(j_in))
					j_in = j_out;
				if (isNaN(i_out))
					i_out = i_in;
				if (isNaN(j_out))
					j_out = j_in;
				//start time of j inbetween times for i
				if ((i_in < j_in && j_in < i_out) ||
					//end time of j inbetween times for i
					(i_in < j_out && j_out < i_out) ||
					//start for i is between j
					(j_in < i_in && i_in < j_out) ||
					//end for i is between j
					(j_in < i_out && i_out < j_out) ||
					//exactly equal
					(i_in == j_in && i_out == j_out)) {
					error += "Lap,";
					break;
				}
			}
		}
		if (row1[i].error !== error || rowChanged) {
			row1[i].error = error;
			grid.setRowData(ids[i], row1[i]);
		}
	}
}

function errorsSingleRow(id, doNotSave) {
	var grid = $("#list");
	var ids = grid.getDataIDs();
	var row = grid.getRowData(id);
	var error = "";
	if (row.time_in.length != 4)
		error += "In,";
	if (row.time_out.length != 4)
		error += "Out,";
	if (row.pay_meth == 'U' && row.units == "0")
		error += "Units,";
	for (var j = 0; j < ids.length; j++) {
		if (id == ids[j]) {
			continue;
		}
		var row2 = grid.getRowData(ids[j]);
		if (row.emplno < row2.emplno) {
			break;
		}
		if (row.emplno > row2.emplno) {
			continue;
		}
		if (row.date < row2.date) {
			break;
		}
		if (row.date === row2.date) {
			var i_in = parseInt(row.time_in, 10);
			var j_in = parseInt(row2.time_in, 10);
			var i_out = parseInt(row.time_out, 10);
			var j_out = parseInt(row2.time_out, 10);
			if (isNaN(i_in) && isNaN(i_out))
				break;
			if (isNaN(j_in) && isNaN(j_out))
				continue;
			if (isNaN(i_in))
				i_in = i_out;
			if (isNaN(j_in))
				j_in = j_out;
			if (isNaN(i_out))
				i_out = i_in;
			if (isNaN(j_out))
				j_out = j_in;
			//start time of j inbetween times for i
			if ((i_in < j_in && j_in < i_out) ||
				//end time of j inbetween times for i
				(i_in < j_out && j_out < i_out) ||
				//start for i is between j
				(j_in < i_in && i_in < j_out) ||
				//end for i is between j
				(j_in < i_out && i_out < j_out) ||
				//exactly equal
				(i_in == j_in && i_out == j_out)) {
				error += "Lap,";
				break;
			}
		}
	}
	if (error !== row.error) {
		if (doNotSave) {
			return error;
		} else {
			row.error = error;
			grid.setRowData(id, row);
			grid.saveRow(id);
		}
	}
}

function editRowMain(id) {
	var grid = jQuery('#list');
	if (id && id !== lastsel) {
		var row = grid.getRowData(id);
		if (row.jobcode > 0) {
			$("#job_desc_span").text(getJob(row.jobcode).task_descr);
			$("#jobcode_span").text(row.jobcode);
		} else {
			$("#job_desc_span").html('<b>No Job chosen!!</b>');
			$("#jobcode_span").text(' ');
		}
		$("#hours_span").text(getDuration(row.time_in, row.time_out));
		$('#error_span').html(row.error);
		lastsel = id;
	}
}
function delthis() {
	$("#list").delGridRow($("#list").getGridParam('selrow'), {
		modal : true,
		height : 250,
		width : 330,
		afterSubmit : function (a, b) {
			ret = [];
			ret[0] = checkSave(a.responseText);
			if (ret[0] == true) {
				lastsel = null;
			}
			return ret;
		}
	});
}

function initGridMain() {
	var wheight = $(window).height();
	var wwidth = $(window).width();
	var wrapHeight = (wheight - 20);
	if (wrapHeight < 500) {
		wrapHeight = 500;
	}
	var namewidth = 100;
	var wfactor = 1;
	var colWidths  = [65,200,60,60,60,40,90,100,40,30];
	var naw = 244;
	var jow = 49;
	var wfactor = 1;
	var pmHidden = false;
	var trainerHidden = false;
	var baseFontSize = 0.90;
	var adjFontSize = 0.90;
	// set max width for selection grid. 
	if (wwidth >= 880) {
		wwidth = 880;
	} else {
		//Adjust column widths to fit within narrower window 
		wfactor = wwidth / 860; 
	}
	// set max width for popup windows 
	if (wwidth > 550) {
		templatewidth = 550;
	} else {
		if (wwidth <= 470) {
			templatewidth = $(window).width() - 6;
		} else {
			templatewidth = wwidth;
		}
	}
	if (wwidth < 500){
		var trainerHidden = true;
		wfactor = wfactor * 1.2;
	}
	if (wwidth < 400){
		pmHidden = true;
		wfactor = wfactor * 1.1;
	}
	
	
	$('#wrapper').height(wrapHeight);
	$('#wrapper').width(wwidth);
	$('#template_div').width(templatewidth);
	$('#employeeselect_div').width(templatewidth);
	$('#reports_div').width(templatewidth);
	$('#timecard_div').width(templatewidth);

	var gridHeight = (wrapHeight - 260);
	if (gridHeight < 250) {
		gridHeight = 250;
	}

	//$('#debug').html('ww:' + wwidth + ' wh:' + wheight + ' wr:' + wrapHeight + ' gh:' + gridHeight);
	jQuery("#list").jqGrid({
		url : 'getdata.php?get=trans&user=' + _user,
		datatype : 'json',
		jsonReader : {
			repeatitems : false,
			cell : "",
			id : "tid"
		},
		colNames : ['Empl:',
			'Name',
			'JOB',
			'IN',
			'OUT',
			'Units',
			'Error',
			'Date',
			'Trnr',
			'PM',
			'WO'],
		colModel : [{
				name : 'emplno',
				index : 'emplno',
				editable : true,
				editoptions : {
					readonly : true
				},
				width : colWidths[0] * wfactor
			}, {
				name : 'empname',
				index : 'empname',
				width : colWidths[1] * wfactor
			}, {
				name : 'jobcode',
				index : 'jobcode',
				editable : true,
				edittype : "select",
				editoptions : {
					value : jobOptions
				},
				width : colWidths[2] * wfactor
			}, {
				name : 'time_in',
				index : 'time_in',
				editable : true,
				editoptions : {
					size : 4,
					maxlength : 4,
					pattern : "[0-9]*"
				},
				width : colWidths[3] * wfactor
			}, {
				name : 'time_out',
				index : 'time_out',
				editable : true,
				editoptions : {
					size : 4,
					maxlength : 4,
					pattern : "[0-9]*"
				},
				width : colWidths[4] * wfactor
			}, {
				name : 'units',
				index : 'units',
				editable : true,
				editoptions : {
					size : 4,
					maxlength : 4,
					pattern : "[0-9]*"
				},
				width : colWidths[5] * wfactor,
				hidden : false
			}, {
				name : 'error',
				index : 'error',
				width : colWidths[6] * wfactor,
				editable : true,
				editoptions : {
					size : 16,
					readonly : true
				},
				hidden : false
			}, {
				name : 'date',
				index : 'date',
				editable : true,
				editoptions : {
					size : 10,
					maxlength : 10
				},
				sorttype : 'date',
				width : colWidths[7] * wfactor
			}, {
				name : 'trainer',
				index : 'trainer',
				editable : true,
				editoptions : {
					size : 2,
					maxlength : 2
				},
				width : colWidths[8] * wfactor,
				pattern : "[0-9]*",
				hidden : trainerHidden
			}, {
				name : 'pay_meth',
				index : 'pay_meth',
				width : colWidths[9] * wfactor,
				sortable : false,
				hidden : pmHidden
			}, {
				name : 'workorder',
				index : 'workorder',
				editable : true,
				editoptions : {
					size : 15,
					maxlength : 15
				},
				width : 110,
				hidden : true
			}
		],
		caption : "4Data - Mobile",
		height : gridHeight,
		pager : 'gridpager',
		rowNum : 200,
		rowList : [20, 40, 60, 80],
		scroll : true,
		multisort : true,
		sortname : 'emplno asc, date asc, time_in',
		sortorder : "asc",
		viewrecords : true,
		loadonce : false,
		altrows : true,
		imgpath : 'themes/basic/images',
		hidegrid : false,
		gridview : true,
		onSelectRow : editRowMain,
		editurl : "savedata.php?save=trans&user=" + _user,
		ondblClickRow : function () {
			editRecordMain();
		},
		loadComplete : gridDataLoadedMain
	});
	jQuery("#list").navGrid('#gridpager', {
		add : true,
		del : true,
		edit : true,
		refresh : true
	}, {}, {}, {}, {});
	adjFontSize = Math.round(120 * baseFontSize * wfactor)/100;
	adjFontSize = adjFontSize + "em";
	$("#gbox_list").css("font-size", adjFontSize);
}

function newRecordMain() {
	var newEmp = $("#employeeSelect").val();
	if (newEmp === 0 || newEmp === null) {
		alert("You must select an employee");
		return false;
	}
	$("#list").editGridRow("new", {
		modal : true,
		closeAfterAdd : true,
		closeAfterEdit : true,
		reloadAfterSubmit : true,
		width : 350,
		height : 500,
		onInitializeForm : initFormEditMain,
		beforeShowForm : setFormDataNewMain,
		//onClose : closeMainEdit,
		beforeSubmit : function(postdata, formid) {
			var thisunit, thisjob, thispaymeth, errormess;
			thisunit = $('#units').val();
			thisjob = postdata.jobcode;
			errormess = '';
			 
			if ( parseInt(thisjob) === 0 || thisjob === undefined ) {
					errormess = errormess + " You must select a job. ";
			} else {
				thispaymeth = getJob(thisjob)['pay_meth']
				if (thispaymeth === "U" && ( parseInt(thisunit) === 0 || thisunit === undefined )) {
						errormess = errormess + " Units can not be 0 for a 'U' job. ";
				}
			}
			if (errormess.length > 0) {
				return([false, errormess]);
			} else {
				return([true, ""]);
			}
		}, 
		onclickSubmit : function (params, postdata) {
			var add_data = {
				time_in : $('#time_in').val(),
				time_out : $('#time_out').val(),
				units : $('#units').val()
			};
			return add_data;
		},
		afterSubmit : function (a, b) {
			var ret = [];
			ret[0] = checkSave(a.responseText);
			return ret;
		}
	});

}

function editRecordMain() {
	$("#list").editGridRow($("#list").getGridParam('selrow'), {
		modal : true,
		closeAfterAdd : true,
		closeAfterEdit : true,
		reloadAfterSubmit : true,
		width : 350,
		height : 500,
		top: 45, 
		left: 10, 
		onInitializeForm : initFormEditMain,
		beforeShowForm : setFormDataEditMain,
		//onClose : closeMainEdit,
		onclickSubmit : function (params, postdata) {
			var add_data = {
				time_in : $('#time_in').val(),
				time_out : $('#time_out').val(),
				units : $('#units').val()
			};
			return add_data;
		},
		afterSubmit : function (a, b) {
			var ret = [];
			ret[0] = checkSave(a.responseText);
			return ret;
		},
		afterComplete : function (response, postdata, formid) {
			if (_errorFilter) {
				$("#list tr").each(function () {
					$(this).css('background-color', '#F6CEE3');
				});
				$("#list tr").hide()
				$("td[aria-describedby='list_error']").each(
					function () {
					if ($(this).text().trim() !== '') {
						$(this).parent().show();
					}
				});
			}
			$("td[aria-describedby='list_empname']").width(nameWidth);
		}
	});

}

function initFormEditMain(form) {
	var datefield = $(form[0].date);
	var d = new Date();
	//datefield.val($.datepicker.formatDate("yy-mm-dd", d));
	//  $(form[0].jobcode).focus(function () {if(c==1) {alert("focus"); }c++;});

	_mainTrainerField = form[0].trainer;
	_mainForm = form[0];
	$(datefield).attr('disabled', 'true');
	$(datefield).datepicker({
		showOn : "both",
		onClose : function (dateText, inst) {
			//hideJobSelect(_jobinput);
			$(_mainForm.jobcode).removeAttr('disabled');
			$(_mainForm.time_in).removeAttr('disabled');
			$(_mainForm.time_out).removeAttr('disabled');
			$(_mainTrainerField).focus();

		},
		beforeShow : function (dateText, inst) {
			$(_mainForm.jobcode).attr('disabled', 'true');
			$(_mainForm.time_in).attr('disabled', 'true');
			$(_mainForm.time_out).attr('disabled', 'true');
		}
	});
	$(form[0].jobcode).css("width", "190px");
	//  $(form[0].jobcode).focus(function () { showJobSelect(this,"main") });
	//	$(form[0].jobcode).blur(function (e) { _jobinput=e.target; setTimeout('hideJobSelect(_jobinput)',500)});
	$(form[0].jobcode).blur(function (e) {
		hideJobSelect(e.target);
	});
	$(form[0].jobcode).change(function (e) {
		hideJobSelect(e.target);
	});
	// $(form[0].time_out).blur(function (e) {
		// $("#hours_list").text(
			// hours($(form[0].time_in).val(), $(form[0].time_out).val()));
	// });
	// $(form[0].time_in).blur(function (e) {
		// $("#hours_list").text(
			// hours($(form[0].time_in).val(), $(form[0].time_out).val()));
	// });
	$("input[name^='time_']").blur(function (e) {
		var thisTime = this.value;
		thisTime = thisTime.replace(" ", "");
		thisTime = thisTime.replace(":", "");
		if (thisTime.length === 0) {
			$("#hours_span").text("Error");
			$("#error_span").text("Time can not be blank!");
			this.focus();
			return false;
		}
		if (thisTime.length < 2) {
			thisTime = "0" + thisTime + "00";
		}
		if (thisTime.length < 3) {
			thisTime = thisTime + "00";
		}
		if (thisTime.length < 4) {
			thisTime = "0" + thisTime;
		}
		if (thisTime.length > 0 && $("#error_span").text() == "Time can not be blank!") {
			$("#error_span").text($("#" + id + "_error").val());
		}
		if (thisTime.length == 4) {
			var hours = false;
			var minutes = false;
			var thiserror = '';
			if ((thisTime.charAt(0) == '0' || thisTime.charAt(0) == '1') && (thisTime.charAt(1) >= '0' && thisTime.charAt(1) <= '9'))
				hours = true;
			if (thisTime.charAt(0) == '2' && (thisTime.charAt(1) >= '0' && thisTime.charAt(1) <= '3'))
				hours = true;
			if ((thisTime.charAt(2) >= '0' && thisTime.charAt(2) <= '5') && (thisTime.charAt(3) >= '0' && thisTime.charAt(3) <= '9'))
				minutes = true;
			if (!(minutes && hours)) {
				$("#hours_span").text("Error");
				$("#error_span").text() = ' hours or minutes invalid ';
			}
		}
		this.value = thisTime;
		var inField = parseInt($("#time_in").val());
		var outField = parseInt($("#time_out").val());
		if (inField > 0 && outField > 0) {
			var duration = getDuration(inField, outField);
		}
		$("#hours_list").html(duration); 
	});

	$(form[0].emplno).hide().after("<span id='editFormMainEmployee'></span>");
	$("<tr class='formdata'>" +
		"<td class='captiontd'>Hours:</td>" +
		"<td><span id='hours_list'></span></td>" +
		"</tr>").appendTo($("#TblGrid_list"));
	$("<tr class='formdata'>" +
		"<td class='captiontd'>Task:</td>" +
		"<td><span id='job_desc_list'></span></td>" +
		"</tr>").appendTo($("#TblGrid_list"));
	$("<tr class='formdata'>" +
		"<td class='captiontd'>PM:</td>" +
		"<td><span id='pay_meth_list'></span></td>" +
		"</tr>").appendTo($("#TblGrid_list"));
    $("#time_in").focus(function () {
		$(this).select();
	})
    $("#time_out").focus(function () {
		$(this).select();
	})
    $("#units").focus(function () {
		$(this).select();
	})
}

function setFormDataEditMain(form) {
	$("#editFormMainEmployee").text(_employees[$(form[0].emplno).val()]);
	_jobcodeTemp = $("#jobcode_span").text();
	for (var i = 0; i < _jobs.length; i++) {
		if (_jobs[i].jobcode == _jobcodeTemp) {
			form[0].jobcode.selectedIndex = i + 1;
		}
	}
	$(form[0].jobcode).blur();
	$(form[0].time_out).blur();
	//jrb -- don't disable
	//$("#employeeSelect").attr('disabled','true');
	$("#employeeSelect").attr('disabled', 'false');
	$("#employeeSelect").removeAttr('disabled');
	$(form[0].units).parent().parent().show();
	$(form[0].workorder).parent().parent().show();
	$(form[0].error).parent().parent().show();
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
	$('td.navButton').hide();
	$("input:text")
	.focus(function () {
		$(this).select();
	})
	.mouseup(function (e) {
		e.preventDefault();
	});
	$('td.navButton').hide();
}

function setFormDataNewMain(form) {

	newParams = newRecordMainHelper();
	$(form[0].date).val(newParams.date);
	$(form[0].emplno).val(newParams.emplno);
	$("#editFormMainEmployee").text(_employees[$(form[0].emplno).val()]);
	$(form[0].trainer).val(_defaults.trainer);
	$(form[0].time_in).val(newParams.time_in);
	$(form[0].time_out).val(_defaults.time_out);
	$(form[0].units).val(0);
	//$("#employeeSelect").attr('disabled','true');
	$(form[0].time_out).blur();
	$(form[0].units).parent().parent().show();
	$(form[0].workorder).parent().parent().show();
	$(form[0].error).parent().parent().show();

}

function newRecordMainHelper() {
	lastsel = null;
	var newEmpl = $("#employeeSelect").val();

	if (newEmpl === "" || newEmpl === null) {
		alert("You must select an employee");
		return;
	}
	var grid = $("#list");
	var ids = grid.getDataIDs();
	var row = [];
	var newDate = $('#maindate').val();
	var newTimeIn = _defaults.time_in;
	for (var i = 0; i < ids.length; i++) {
		row = grid.getRowData(ids[i]);
		if (row.emplno < newEmpl || row.emplno > newEmpl) {
			continue;
		}
		if (row.date < newDate || row.date > newDate) {
			continue;
		}
		newTimeIn = row.time_out;
	}
	var params = [];
	params.date = newDate;
	params.trainer = _defaults.trainer;
	params.time_in = newTimeIn;
	params.time_out = _defaults.time_out;
	params.user = _user;
	params.emplno = newEmpl;
	params.oper = 'add';
	return params;
}

//<script>
