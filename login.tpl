<html>
<head> 

  <link rel="apple-touch-icon" href="apple-touch-icon.png"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black" />

<style type='text/css'>
			body {background: url(images/bg.jpg) #10a5d3;font: 14px "HelveticaNeue", "Helvetica Neue", Helvetica, Arial, sans-serif;color: #444; -webkit-font-smoothing: antialiased; /* Fix for webkit rendering */-webkit-text-size-adjust: none;}
			#login_form {width: 320px; height: 248px; -moz-border-radius:9px; -webkit-border-radius:9px; border-radius:9px;	margin: 15px auto 0; background: url(images/form-bg.png) top left; padding: 8px 0 0 8px}
			#title-div {color: #fff;text-shadow: 0 1px 0 rgba(0,0,0,0.3);text-align:center; width: 320px; -moz-border-radius:9px; -webkit-border-radius:9px; border-radius:9px;	margin: 25px auto 0;}
			#login_form h1, #login_form h2, #login_form h3 {margin:0; padding:0 0 5px 0;}
			#login_form h1 {font-size:180%;}
			#login_form h2 {font-size: 14px;font-weight: bold;color: #555555;font-family:"HelveticaNeue", "Helvetica Neue", Helvetica, Arial, sans-serif;text-shadow: 0 1px 0 #fff;filter: dropshadow(color=#fff, offx=0, offy=1);line-height: 45px;margin-bottom: 24px;margin-left:25px;}
			#login_form h3 {font-size:100%; color:#444;}
			#login_form form {width: 310px; height: 239px; background: #fff url(images/bg_form.jpg) repeat-x top left;-moz-border-radius:4px;-webkit-border-radius:4px;border-radius:4px;-moz-box-shadow:0px 1px 3px 2px rgba(0,0,0,0.1);-webkit-box-shadow:0px 1px 3px 2px rgba(0,0,0,0.1);box-shadow:0px 1px 3px 2px rgba(0,0,0,0.1);}
			
			#login_form fieldset {margin:0; padding:20px 20px 35px 20px; *padding-top:0; border:0px solid #7488A9;}
			#login_form legend {padding:0 10px; *margin:0 0 20px 0; color:#000; font-weight:bold;}
			#login_form label {display: inline;margin-left: 24px;vertical-align: middle;}
			#login_form label input {}
			#login_form .checkbox {}
			#login_form .checkbox input {}
			#login_form .submit {background:url(images/login.png) no-repeat;width:82px;height:32px;border:0px;float: right;margin-right: 24px;}
			#login_form .submit:hover {background:url(images/login_hover.png) no-repeat;width:82px;height:32px;border:0px;}
			#login_form .submit input {cursor:pointer;}
			#return-div {color: #fff;text-shadow: 0 1px 0 rgba(0,0,0,0.3);text-align:center; width: 320px; -moz-border-radius:9px; -webkit-border-radius:9px; border-radius:9px;	margin: 25px auto 0;}

input[type="text"], input[type="password"]{
		box-shadow: 0px 0px 0px 4px #f2f5f7;
		width: 260px;
		height: 33px;
		padding: 0 10px 0 10px;
		margin: 0 auto;
		color: #aeaeae;
		border: 1px solid #bec2c4;
	}
input[type="text"]:focus, input[type="password"]:focus{
		box-shadow: 0px 0px 0px 4px #e0f1fc;
		border:1px solid #7dc6dd;
	}
	
	input[type="checkbox"] {
		vertical-align: middle;
	}

		</style>

</head>
<body onLoad=document.form1.user_id.focus();>
<div id='title-div'>
	<img src='Merricklogo.jpg' height='91' />
	<h1>4Data Mobile</h1>
	<h2>Web Data Entry</h2>
</div>
<div id='login_form'>
	<form id='form1' name='form1' action='<?=getURL()?>' method='post'>
		<fieldset>
			<h3>Login With Assigned User ID/Password.</h3>
			<p><input type='text' name='user_id' placeholder="User ID" id='user_id' /></p>
			<p><input type='password' placeholder="Password" name='user_password' id='user_password' /></p>
			
			<p><button type='submit' class='submit'></button></p>					
			<label for='remember' class='checkbox'><input type='checkbox' name='remember' id='remember' />Remember me?</label>
		</fieldset>
	</form>
</div> 
<div id="return-div">
	<h2><a href='/index.html'>Return to main menu</a><h2>
</div>	
</body>
</html>
