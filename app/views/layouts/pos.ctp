<?php
/**
 * Copyright UDAYA Technology Co,.LTD (http://www.udaya-tech.com)
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
include("includes/function.php");
$config = getSysconfig();
if(!empty($config)){
    $title = $config['title'];
    $start = $config['start'];
}else{
    $title = "";
    $start = "";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <?php echo $this->element('embed_font'); ?>

        <title>
            <?php __('UT-POS • '.$title); ?>
        </title>

        <!-- icon -->
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->webroot; ?>img/favicon.ico" />

        <!-- General stylesheet -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/pos.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />
        <!-- Validator -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/validateEngine/css/validationEngine.jquery.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/validateEngine/css/template.css" />

        <!-- jquery -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.7.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.cookie.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/shortcut.js"></script>

        <!-- jquery ui -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/jquery-ui-1.10.0.custom/development-bundle/themes/base/jquery.ui.all.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-ui-1.10.0.custom/js/jquery-ui-1.10.0.custom.min.js"></script>

        <!-- Validator -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/validateEngine/js/jquery.validationEngine-<?php echo $this->Session->read('lang'); ?>.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/validateEngine/js/jquery.validationEngine.js"></script>

        <!-- Data Table -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/DataTables-1.8.1/media/css/custom.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/DataTables-1.8.1/media/js/jquery.dataTables.min.kh.js"></script>

        <!-- Auto Numeric -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/autoNumeric-1.6.2.js"></script>

        <!-- date -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/date-en-US.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/function.js"></script>

        <style type="text/css">
            body{
                overflow: hidden;
            }
            .ui-tabs-panel{overflow-y: scroll;}
            .key {
                min-width: 18px;
                height: 18px;
                margin: 2px;
                padding: 2px;
                text-align: center;
                font: 14px/18px sans-serif;
                color: #777;
                background: #EFF0F2;
                border-top: 1px solid #F5F5F5;
                text-shadow: 0px 1px 0px #F5F5F5;
                -webkit-box-shadow: inset 0 0 25px #eee, 0 1px 0 #c3c3c3, 0 2px 0 #c9c9c9, 0 2px 3px #333;
                -moz-box-shadow: inset 0 0 25px #eee, 0 1px 0 #c3c3c3, 0 2px 0 #c9c9c9, 0 2px 3px #333;
                box-shadow: inset 0 0 25px #eee, 0 1px 0 #c3c3c3, 0 2px 0 #c9c9c9, 0 2px 3px #333;
                display: inline-block;
                -moz-border-radius: 1px;
                border-radius: 1px;
            }
            h1 .key {
                width: 42px;
                height: 40px;
                font: 15px/40px sans-serif;
                -moz-border-radius: 5px;
                border-radius: 5px;
            }
        </style>
        <script type="text/javascript">
            function replaceNum(str){
                if(str != "" && str != undefined && str != null){
                    var str = parseFloat(str.toString().replace(/,/g,""));
                }else{
                    var str = 0;
                }
                return str;
            }
            
            function converDicemalJS(value){
                return Math.round(parseFloat(value) * 1000000000)/1000000000;
            }
            
            function preventKeyEnter(){
                // Prevent Input Key Enter
                $("input[type='text']").keypress(function(e){
                    if((e.which && e.which == 13) || e.keyCode == 13){
                        return false;
                    }
                });
            }
            
            $(document).ready(function(){
                if($.cookie('showStock') != null ) {
                    $("#showStock").attr("checked", true);
                }else{
                    $("#showStock").attr("checked", false);
                }
                $("#showStock").click(function(){
                    $.cookie("showStock", 1, {
                        expires : 5,
                        path    : '/'
                    });
                });
            });
        </script>
    </head>
    <body>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/wz_tooltip_v4.js"></script>
        <div class="ui-layout-center">
            <div id="main_page">
                <?php echo $this->Session->flash(); ?>
                <?php echo $content_for_layout; ?>
            </div>
        </div>
        <div id="dialog" title=""></div>
        <div id="dialogConfirm" title=""></div>
        <div id="progress">
            មេត្តារង់ចាំ ...
        </div>
    </body>
</html>