//set up global variables.
var _defaults, _jobs, _employees, lastsel, _jobinput;
var _focusedJobInput = null;
var _jobSelectMode = "main";
var _newRow = null;
var _errorFilter = false;
var _height = $(window).height() - 50;
var jobOptions = null;

String.prototype.trim = function () {
	return this.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
}

function checkSave(response) {
	if (response !== null) {
		if (response.trim().length > 0) {
			if (parseInt(response).toString() !== response.trim()) {
				alert(response);
				return false;
			}
		}
	}
	return true;
}

function validateCommon(row) {
	if (!checkTimes(row['time_in'], row['time_out'])) {
		return false;
	}
	job = getJob(row['jobcode'])
		if (job['pay_meth'] == "") {
			alert("You must enter a jobcode!");
			return false;
		}
		if (job['pay_meth'] === 'U') {
			var units = parseInt(row['units'], 10);
			if (isNaN(units) || units <= 0) {
				//not a fatal error so no return false
				alert("Units can not be blank.");
			}
		}
		return true;
}

function hours(time_in, time_out) {
	time_in = parseInt(time_in, 10);
	time_out = parseInt(time_out, 10);
	time_in = Math.floor(time_in / 100) + (time_in % 100) / 60.0;
	time_out = Math.floor(time_out / 100) + (time_out % 100) / 60.0;
	return Math.round((time_out - time_in) * 100) / 100;
}

function getExtraParams() {
	return [["emplno", $("#employeeSelect").val()]];
}

function getJob(jobcode) {
	for (var i = 0; i < _jobs.length; i++) {
		if (_jobs[i]['jobcode'] == jobcode)
			return _jobs[i];
	}
	//fake empty job
	return {
		pay_meth : "",
		task_descr : ""
	};
}

function populateJobList() {
	var ul = $("<table cellspacing='0'></table>")
		ul.addClass('selection')
		ul.css({
			position : 'absolute',
			listStyle : 'none',
			zIndex : '9999',
			opacity : 1,
			padding : '1px'
		})
		ul.appendTo($("#jobList"));
	for (var i = 0; i < _jobs.length; i++) {
		var jobcode = _jobs[i]['jobcode'];
		var pm = _jobs[i]['pay_meth'];
		var desc = _jobs[i]['task_descr'];
		var pad = "";
		//if(i!=0)
		//	jobSelect += ";";
		//jobSelect += jobcode + ":" + jobcode + "|" + pm + "|" + desc;
		$('<tr></tr>')
		.html("<td>" + jobcode + "</td><td class='leftborder'>" + pm + "</td><td class='leftborder'>" + desc + "</td>")
		.attr('title', jobcode)
		.css({
			display : 'block'
		})
		.mouseover(function () {
			$(this).addClass('hover');
		})
		.mouseout(function () {
			$(this).removeClass('hover');
		})
		.click(function () {
			if (!_focusedJobInput)
				return;
			$(_focusedJobInput).val($(this).attr('title'))
			hideJobSelect(_focusedJobInput);
		})
		.appendTo(ul);

	}
}

function getIDFromField(f) {
	var ret = "";
	var id = f.id;
	for (var i = 0; i < id.length; i++) {
		if (id.charAt(i) == '_')
			return ret;
		ret = ret + id.charAt(i);
	}
}

function hideJobSelect(input) {
	if ($(input).val()) {
		if (_jobSelectMode == 'main') {
			$("#job_desc_span").text(getJob($(input).val()).task_descr);
			var id = getIDFromField(input);
			$("#list").setRowData(id, {
				'pay_meth' : getJob($(input).val()).pay_meth
			});
			$("#job_desc_list").text(getJob($(input).val()).task_descr);
			$("#pay_meth_list").html(getJob($(input).val()).pay_meth);
		}
		if (_jobSelectMode == 'template') {
			$("#job_desc_template").text(getJob($(input).val()).task_descr);
			$("#pay_meth_template").text(getJob($(input).val()).pay_meth);
		}
	}
	$("#jobList").hide();
	$("#jobList").hide();
	_focusedJobInput = null;
	_jobinput = null;
}

function showJobSelect(input, mode) {
	_jobSelectMode = mode;
	if (mode == "main")
		return;
	if (_focusedJobInput == input)
		return;
	_focusedJobInput = input;
	input = $(input);
	var offset = input.offset()
		$("#jobList").show()
		.css({
			top : offset['top'] + input.height(),
			left : offset['left']
		})

}
function init(data) {
	_defaults = data['defaults'];
	_jobs = data['jobs'];
	_employees = data['employees'];
	populateJobList();
	var employeeOptions = {};

	var employeeOpt = {};
	for (var i = 0; i < _employees.length; i++) {
		var emp = _employees[i];
		employeeOpt = {
			value : emp.emplno,
			text : emp.empname + ', ' + emp.emplno
		};
		$("#employeeSelect").append($('<option>', employeeOpt));
		$("#employeeMultiSelect").append($('<option>', employeeOpt));
		employeeOptions[emp.emplno] = emp.empname + ', ' + emp.emplno;
	}
	//$("#employeeSelect").addOption(employeeOptions);
	//$("#employeeMultiSelect").addOption(employeeOptions, false);
	//$("#employeeSelect").val("");
	_employees = employeeOptions;
	jobOptions = '0: '
		for (i = 0; i < _jobs.length; i++) {
			j = _jobs[i]['jobcode'] + '|' + _jobs[i]['pay_meth'] + '|' + _jobs[i]['task_descr'];
			j = j.replace(/[;:]/g, '');
			if (jobOptions === null) {
				jobOptions = j;
			} else {
				jobOptions = jobOptions + ';' + _jobs[i]['jobcode'] + ':' + j;
			}
		}

		//$("#employeeSelect").addOption(employeeOptions);
		//$("#employeeMultiSelect").addOption(employeeOptions,false);
		$("#employeeSelect").val("");

	initTemplateGrid();
	initGridMain();

	$("#newTemplate").click(newTemplate);
	$("#newRecord").click(newRecordMain);
	$("#editRecord").click(editRecordMain);
	$("#deleteRecord").click(delthis);
}

function toggleErrors() {

	if (lastsel) {
		var grid = $("#list");
		grid.saveRow(lastsel, checkSave);
	}
	toggleErrorsHelper();
}
function toggleErrorsHelper() {
	lastsel = null;
	var grid = $("#list")
		var lnNameWidth = $("td[aria-describedby='list_empname']").width()
		if (_errorFilter) {
			$("#errors_button").removeClass("depressed");
			$("#errors_button").addClass("released");
			//grid.trigger("reloadGrid");
			//$('td[aria-describedby="list_empname"]').each(function() {$(this).html(_employees[$(this).siblings('td[aria-describedby="list_emplno"]').text()])})
			$("#list tr").show();
			$("#list tr").each(function () {
				$(this).css('background-color', '#fcfdfc');
			});
		} else {
			checkErrors();
			$("#errors_button").removeClass("released");
			$("#errors_button").addClass("depressed");
			$("#list tr").hide()
			$("td[aria-describedby='list_error']").each(
				function () {
				if ($(this).text().trim() !== '') {
					$(this).parent().show();
				}
			});
			$("#list tr").each(function () {
				$(this).css('background-color', '#F6CEE3');
			});

			//var nameWidth = $("td[aria-describedby='list_empname']").width();
			//$('td[aria-describedby="list_empname"]').each(function() {$(this).prepend($(this).siblings('td[aria-describedby="list_error"]').text())})
			//$("td[aria-describedby='list_empname']").width(nameWidth);
			$("td[aria-describedby='list_emplno']").width($("#list_emplno").width());
			$("td[aria-describedby='list_empname']").width($("#list_empname").width());
			$("td[aria-describedby='list_jobcode']").width($("#list_jobcode").width());
			$("td[aria-describedby='list_time_in']").width($("#list_time_in").width());
			$("td[aria-describedby='list_time_out']").width($("#list_time_out").width());
			$("td[aria-describedby='list_units']").width($("#list_units").width());
			$("td[aria-describedby='list_error']").width($("#list_error").width());
			$("td[aria-describedby='list_date']").width($("#list_date").width());
			$("td[aria-describedby='list_trainer']").width($("#list_trainer").width());
		}
		_errorFilter = !_errorFilter;
}

function sendRecords() {
	var grid = $("#list");
	if (!confirm("Send records to GAT?"))
		return;
	if (lastsel) {
		grid.saveRow(lastsel, checkSave);
	}
	sendRecordsHelper();
}
function sendRecordsHelper() {
	lastsel = null;
	var params = {};
	params['user'] = _user;
	params['save'] = 'send';
	$.post('savedata.php', params, function (data) {
		if (data.length == 0) {
			$("#list").trigger("reloadGrid");
		} else {
			alert(data);
		}
	});
}

function openReport() {
	var grid = $("#list");
	if (lastsel) {
		grid.saveRow(lastsel, checkSave);
	}
	openReportHelper();
}
function openReportHelper() {
	var qs = "print.php?report="
		qs += $("#reports_div input[name='order']:checked").val(); ;
	if ($("#reports_div input[name='single']:checked").val() == 'yes') {
		qs += '&emplno=' + $("#employeeSelect").val();
	}
	qs += '&user=' + _user;
	qs += '&ms=' + new Date().getTime()
	window.open(qs, '_blank', 'menubar=no,location=no');
}

jQuery(document).ready(function () {
	//controlsHeight = ($("#wrapper").height()+4*$("#newRecord").height() + 10);
	//#wrapper holds everything but the grid at this point
	//+3 * newRecord height is an attempt at getting the height of grid controls whichas 3 lines: title, headers, and scroll bar
	//+10 for padding
	//if(_height == 'auto')
	//{
	//  if (typeof window.innerHeight != 'undefined')
	//  {
	//    _height = window.innerHeight-controlsHeight;
	//    if(_height>700)
	//      _height=500;
	//  }
	//}
	//_height = _height + "px"
	$("#maindate").datepicker();
	$.datepicker.setDefaults({
		showOn : 'both',
		dateFormat : "yy-mm-dd",
		buttonImage : "calendar.gif",
		buttonImageOnly : true,
		maxDate : '+0',
		minDate : '-30'
	});
	$("#timecard_div").jqDrag('.jqDrag').jqResize('.jqResize').jqm({
		overlay : 0,
		modal : false
	});
	$("#template_div").jqDrag('.jqDrag').jqResize('.jqResize').jqm({
		overlay : 0,
		modal : false
	});
	$("#reports_div").jqDrag('.jqDrag').jqResize('.jqResize').jqm({
		overlay : 0,
		modal : false
	});
	$("#employeeselect_div").jqDrag('.jqDrag').jqResize('.jqResize').jqm({
		overlay : 50,
		modal : true
	});

	$(window).unload(function () {
		/*var grid = $("#list");
		var ids = grid.getDataIDs();
		for(var i=0; i<ids.length; i++) {
		var id=ids[i];
		if(id!=lastsel) {
		grid.editRow(id);
		grid.saveRow(id);
		}
		}*/
		$("#list").saveRow(lastsel, validateMain);
	});
	$('#timecard_button').click(function () {
		$.getJSON("getdata.php", {
			get : 'notimes',
			user : _user,
			ms : new Date().getTime()
		}, function (data) {
			$("#timecard_body").empty();
			for (var i = 0; i < data.length; i++) {
				$("#timecard_body").append("<p>" + data[i]['empname'] + ', ' + data[i]['emplno'] + "</p>");
			}
			$("#timecard_div").jqmShow();
		});
	});
	$('#errors_button').click(toggleErrors);
	$('#print_button').click(function () {
		$('#reports_div').jqmShow();
	});
	$('#open_report_button').click(openReport);

	$('#template_button').click(function () {
		$('#template_div').jqmShow();
	});
	$.getJSON("getdata.php", {
		get : "single",
		user : _user,
		ms : new Date().getTime()
	}, init);
	$("#sendButton").click(sendRecords);
});
