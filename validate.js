function removeSpaces(data)//jquery forms have &nbsp; for empty fields;
{
	for(key in data)
	{
		if(data[key]=='&nbsp;')
			data[key]='';
	}
	return data;
}

function setDefault(value, defaultValue)
{
	if(value==null || typeof(value) == 'undefined')
		return defaultValue;
	else
		return value;
}

function checkTimes(time_in,time_out) {
	if(!checkTimeFormat('Time In', time_in))
		return false;
	if(!checkTimeFormat('Time Out', time_out, true))
		return false;
	if(time_out.length>0 && parseInt(time_out,10)<=parseInt(time_in,10)) {
		alert("TimeOut < TimeIn");
		return false;
	}
	return true;
}
//check a single time for valid format
function checkTimeFormat(name,time,allowNull)
{	
	var valid = false;
	var error;
	allowNull = setDefault(allowNull, true);
//jk vocshop Allow Time Out to be blank (populates with	"&nbsp;" on blank
	if (name == "Time out" && time == "&nbsp;") {
		time = "";
	}
	if(time.length == 0)
	{
		if(!allowNull)
		{
			valid = false;
			error = name+": 0 Hours";
		} else
			valid = true;
	} else if(time.length==4)
	{
		var hours=false, minutes=false;
		if( (time.charAt(0)=='0' || time.charAt(0)=='1') && (time.charAt(1)>='0' && time.charAt(1)<='9') )
			hours = true;
		if( time.charAt(0)=='2' && (time.charAt(1)>='0' && time.charAt(1)<='3') )
			hours = true;
		if( (time.charAt(2)>='0' && time.charAt(2)<='5') && (time.charAt(3)>='0' && time.charAt(3)<='9') )
			minutes = true;
		if(minutes && hours)
			valid = true;
		else {
			valid = false;
			error = name+": Enter time as HHMM";
		}
	} else
	{
		valid = false;
		error = name+": Enter time as HHMM";
	}
	if(!valid)
		alert(error);
	return valid;
}

	/*if(row['jobcode'])
	{	
		s = row['jobcode'];
		for(var i=0; i<s.length; i++)
		if( s[i]<'0' || s[i]>'9')
		{
			alert('<?=$STRINGS[$LANG]['jobcode_numeric_error'];?>');
			return false;
		}
	}*/


/*function checkMaxLength(x) {
	alert(foo);
	if(!$(x).attr('maxLenInit') && x.parentNode)
	{
		$(x).attr('maxLenInit',true);
		var counter = document.createElement('div');
		counter.className = 'counter';
		if (x.getAttribute('maxlength')) {
			var counterClone = counter.cloneNode(true);
			counterClone.relatedElement = x;
			counterClone.innerHTML = '<span>0</span>/'+x.getAttribute('maxlength');
			x.parentNode.insertBefore(counterClone,x.nextSibling);
			x.relatedElement = counterClone.getElementsByTagName('span')[0];
			x.onkeyup = x.onchange = function(e) {checkMaxLength(e.target)};
			x.onfocus = null;
		}
	}
	var maxLength = x.getAttribute('maxlength');
	var currentLength = x.value.length;
	if (currentLength > maxLength)
		x.relatedElement.className = 'toomuch';
	else
		x.relatedElement.className = '';
	x.relatedElement.firstChild.nodeValue = currentLength;
	// not innerHTML
}*/
function setMaxLength(id) {
	var x;
	var setInit=false;
	if(typeof(id)=="object")
	{
		x = id[0].getElementsByTagName('textarea');
		setInit=true;
	}
	else
		x = document.getElementsByTagName('textarea');
	var counter = document.createElement('div');
	counter.className = 'counter';
	for (var i=0;i<x.length;i++) {
		if (x[i].getAttribute('maxlength')) {
			if($(x[i]).attr('maxLenInit'))
				continue;
			if(setInit)
				$(x[i]).attr("maxLenInit",true);
			var counterClone = counter.cloneNode(true);
			counterClone.relatedElement = x[i];
			counterClone.innerHTML = '<span>0</span>/'+x[i].getAttribute('maxlength');
			x[i].parentNode.insertBefore(counterClone,x[i].nextSibling);
			x[i].relatedElement = counterClone.getElementsByTagName('span')[0];

			x[i].onkeyup = x[i].onchange = checkMaxLength;
			x[i].onkeyup();
		}
	}
}

function checkMaxLength() {
	var maxLength = this.getAttribute('maxlength');
	var currentLength = this.value.length;
	if (currentLength > maxLength)
		this.relatedElement.className = 'toomuch';
	else
		this.relatedElement.className = '';
	this.relatedElement.firstChild.nodeValue = currentLength;
	// not innerHTML
}

