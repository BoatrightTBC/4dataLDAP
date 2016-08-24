<?PHP 
require_once("settings.php");
require_once("utils.php");
killcache();
if(!checkLogin())
          showLogin();
?>
<!DOCTYPE HTML>
<html>
<head>
    <link rel="apple-touch-icon" href="apple-touch-icon.png"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />

    <title>4data Mobile</title>
    <LINK HREF="themes/ui.jqgrid.css" MEDIA="screen" REL="stylesheet" TYPE="text/css" />
    <LINK HREF="themes/basic/grid.css" MEDIA="screen" REL="stylesheet" TYPE="text/css" />
    <LINK HREF="themes/jqModal.css" MEDIA="screen" REL="stylesheet" TYPE="text/css" />
    <LINK HREF="js/jquery-ui.min.css" MEDIA="screen" REL="stylesheet" TYPE="text/css" />
    <LINK HREF="common.css" MEDIA="screen" REL="stylesheet" TYPE="text/css" />
    <LINK HREF="4data.css" MEDIA="screen" REL="stylesheet" TYPE="text/css" />

    <SCRIPT SRC="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></SCRIPT> 
    <script src="js/minified/jquery.jqGrid.min.js" type="text/javascript"></script>
    <script src="js/i18n/grid.locale-en.js" type="text/javascript"></script>
    <script src="js/jqModal.js" type="text/javascript"></script>
    <script src="js/jqDnR.js" type="text/javascript"></script>
    <script src="validate.js" type="text/javascript"></script>
    <script src="maingrid.js" type="text/javascript"></script>
    <script src="templategrid.js" type="text/javascript"></script>
    <script src="js/jquery.selectboxes.js" type="text/javascript"></script>
    <script src="js/jquery-ui.min.js" type="text/javascript"></script> 
    <script src="4dataIP.js" type="text/javascript"></script> 
    <script type="text/javascript">
        //this has to be in the php file, so that we can get the user id from the session variable. 
        var _user="<?=$_SESSION['user_id']?>";
    </script>
</head>
<body>
<div class="jqmWindow modalwin" id="employeeselect_div">
    <div id="employeeselecthdlist" class="modalhead jqDrag" style="cursor: move;">
        <table width="100%">
            <tr>
                    <td class="modaltext"><?=$STRINGS[$LANG]['employee_select_header']?></td>
                    <td align="right"><a class="jqmClose" href="javascript:void(0);"><img border="0" src="themes/basic/images//ico-close.gif"/></a></td>
            </tr>
        </table>
    </div>
    <div class="modalcontent" id="employeeselect_body">
        <div align="center">
            <select multiple=true id="employeeMultiSelect"></select>
        </div>
        <button id="employeeSelectDone"><?=$STRINGS[$LANG]['select']?></button>
        <button class="jqmClose"><?=$STRINGS[$LANG]['close']?></button>
    </div>
    <img src="themes/basic/images/resize.gif" class="jqResize"/>
</div>

<div class="jqmWindow modalwin" id="reports_div">
    <div id="reportshdlist" class="modalhead jqDrag" style="cursor: move;">
        <table width="100%">
            <tr>
                <td class="modaltext"><?=$STRINGS[$LANG]['reports']?></td>
                <td align="right"><a class="jqmClose" href="javascript:void(0);"><img border="0" src="themes/basic/images//ico-close.gif"/></a></td>
            </tr>
        </table>
    </div>
    <div class="modalcontent" id="reports_body">
        <br >
        <div align="center">
            <fieldset>
                <div align='left'>
                    <input type='radio' name='order' value='name' checked=true/> Use Name order.<br />
                    <input type='radio' name='order' value='id' />Use ID order<br />
                    <input type='radio' name='order' value='job' />Use Job Code order<br />
                </div>
            </fieldset><br />
            <fieldset>
                    <div align='left'>
                    <input type='radio' name='single' value='no' checked=true />All workers<br />
                    <input type='radio' name='single' value='yes' />Selected worker only<br />
                    </div>
            </fieldset>
        </div>
        <button id="open_report_button"><?=$STRINGS[$LANG]['get_report']?></button>
        <button class="jqmClose"><?=$STRINGS[$LANG]['close']?></button>
        <div style="clear:both;"></div>         
    </div>
    <img src="themes/basic/images/resize.gif" class="jqResize"/>
</div>

<div class="jqmWindow modalwin" id="timecard_div">
    <div id="timecardhdlist" class="modalhead jqDrag" style="cursor: move;">
        <table width="98%">
            <tr>
                <td class="modaltext"><?=$STRINGS[$LANG]['timecard_header']?></td>
                <td align="right"><a class="jqmClose" href="javascript:void(0);"><img border="0" src="themes/basic/images//ico-close.gif"/></a></td>
            </tr>
        </table>
    </div>
    <div class="modalcontent" id="timecard_body"></div>
    <img src="themes/basic/images/resize.gif" class="jqResize"/>
</div>

<div class="jqmWindow modalwin" style="z-index:99; width:100%; left:0px; top:0px" id="template_div">
    <div id="templatehdlist" class="modalhead jqDrag" style="cursor: move;">
        <table width="100%">
            <tr>
                <td class="modaltext"><?=$STRINGS[$LANG]['template_header']?></td>
                <td align="right"><a class="jqmClose" href="javascript:void(0);"><img border="0" src="themes/basic/images//ico-close.gif"/></a></td>
            </tr>
        </table>
    </div>
    <div class="modalcontent" style="margin-top: 5px; overflow: hidden;"id="template_body">
    <div style="overflow: auto">
        <table id="templateList" class="scroll" cellpadding="0" cellspacing="0"></table>
    </div>
    <button id='newTemplate'><?=$STRINGS[$LANG]['new']?></button>
    <button style='margin-top:15px;' onclick='$("#templateList").delGridRow($("#templateList").getGridParam('selrow'),
        {modal:true,
        height: 250,
        width: 350,
        afterSubmit: function(a,b){ret=new Array(); ret[0]=checkSave(a.responseText); return ret;}});'>
        <?=$STRINGS[$LANG]['delete_selected'];?>
    </button>
    <button id='editTemplate'><?=$STRINGS[$LANG]['edit']?></button>
   <button id='employeesTemplate'><?=$STRINGS[$LANG]['select_employees']?></button>
   </div>
   <img src="themes/basic/images/resize.gif" class="jqResize"/>
</div>

<div id="wrapper">
    <button id = 'timecard_button'><?=$STRINGS[$LANG]['missing']?></button>
    <button id='errors_button'><?=$STRINGS[$LANG]['errors']?></button>
    <button id='print_button'><?=$STRINGS[$LANG]['reports']?></button>
    <a class='button-link' href='4data.php?logout'><?=$STRINGS[$LANG]['logout']?></a>
    <br />
<div>
    <select id="employeeSelect">
    <option value="" disabled=false></option>
    <label for="maindate">Date:</label><INPUT ID="maindate" MAXLENGTH="10" SIZE="10" TYPE="text" VALUE="<?PHP  echo date("Y-m-d"); ?>">
    <br/>
    <button id='newRecord'><?=$STRINGS[$LANG]['new'];?></button> 
    <button id='editRecord'><?=$STRINGS[$LANG]['edit'];?></button>
    <button id='deleteRecord'>Delete</button> 
    <button id='template_button'>Template</button>
    <button id='sendButton'>Send</button>
</div> 
<div style="float:left; overflow:auto; width: 100%">
    <!--<div id='debug'> </div> -->
    <div style="width: 940px;">
        <table id="list" class="scroll" cellpadding="0" cellspacing="0"></table>
        <div id="gridpager" class="scroll" style="text-align:center;"></div>
    </div>
</div>

<div style="clear:both"></div>
<div id='jobList' style='position: absolute; display: none'> </div>
    <div style="width:100%; max-width:640px;">
        <table><tbody>
            <tr><td><b><?=$STRINGS[$LANG]['hours']?>:</b></td><td><span id='hours_span'></span></td></tr>
            <tr><td><b><?=$STRINGS[$LANG]['job_desc']?>:</b></td><td><span id='job_desc_span'></span> - <span id="jobcode_span"></span></td></tr>
            <tr><td><b><?=$STRINGS[$LANG]['error']?>:</b></td><td><span id='error_span'></span></td></tr>
        </tbody></table>
    </div>
</div>
</body>
</html>