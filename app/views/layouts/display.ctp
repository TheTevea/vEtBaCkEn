<?php
/**
 * Copyright UDAYA Technology Co,.LTD (http://www.udaya-tech.com)
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
include("includes/function.php");
$start = '2018';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <?php echo $this->element('embed_font'); ?>

        <title>
            <?php __('Schedule Display'); ?>
        </title>

        <!-- icon -->
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->webroot; ?>img/favicon.ico" />

        <!-- Style Sheet -->
        <!-- General Style Sheet -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css?32323" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/report.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"></link>
        <!-- Jquery UI -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/jquery-ui-1.8.14.custom/development-bundle/themes/base/jquery.ui.all.css" />
        <!-- Layout -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/jquery.layout.all-1.2.0/layout.css" />
        <!-- Validate -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/validateEngine/css/validationEngine.jquery.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/validateEngine/css/template.css" />
        <!-- Data Table -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/DataTables-1.8.1/media/css/custom.css" />
        <!-- Choosen -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/harvesthq-chosen-v0.9.1/chosen_1.8.2/chosen.css" />
        <!-- Tooltip -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/atooltip.css" />
        <!--  Auto Complete -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/jquery.autocomplete.css" />
        <!-- JS Crop Photo -->
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>js/tapmodo-Jcrop-25f2e18/css/jquery.Jcrop.css" type="text/css" />
        <!-- Mini Select 
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>js/minimalect/jquery.minimalect.min.css" type="text/css" media="screen" />
        -->
        <!-- Check box Style -->
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>js/checkboxStyle/bootstrap2-toggle.css" />
        <!-- Tour -->
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>js/tours/introjs.css" />
        <!-- Time Picker -->
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>js/timePicker/jquery-ui-timepicker.css" />
        <!-- Scroll Bar -->
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>js/scrollbar/jquery.nicescroll.min.css" />
        
        <!-- Jquery Script -->
        <!-- Jquery -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.7.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.cookie.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/shortcut.js"></script>
        <!-- Jquery UI -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-ui-1.8.14.custom/js/jquery-ui-1.8.14.custom.min-<?php echo $this->Session->read('lang'); ?>.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-ui-1.8.14.custom/js/ui.tabs.closable.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-ui-1.8.14.custom/js/ui.tabs.paging.js"></script>
        <!-- Layout -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.layout.all-1.2.0/jquery.layout.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.layout.all-1.2.0/jquery.layout.state.js"></script>
        <!-- Validator -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/validateEngine/js/jquery.validationEngine-<?php echo $this->Session->read('lang'); ?>.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/validateEngine/js/jquery.validationEngine.js"></script>
        <!-- Data Table -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/DataTables-1.8.1/media/js/jquery.dataTables.min.<?php echo $this->Session->read('lang'); ?>.js"></script>
        <!-- Choosen -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/harvesthq-chosen-v0.9.1/chosen_1.8.2/chosen.jquery.min.js"></script>
        <!-- autoNumeric -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/autoNumeric-1.6.2.js"></script>
        <!-- Price Format -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.price_format-1.3.js"></script>
        <!-- input mask for number - support unicode -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/uninums.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.caret.1.02.min.js"></script>
        <!-- Tooltip -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.atooltip.js"></script>
        <!-- Date -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/date-en-US.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/function.js"></script>
        <!-- Ajax Form -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.form.js"></script>
        <!--  Auto Complete -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.autocomplete.min.js"></script>
        <!-- JS Crop Photo -->
        <script src="<?php echo $this->webroot; ?>js/tapmodo-Jcrop-25f2e18/js/jquery.Jcrop.js" type="text/javascript"></script>
        <!-- List Box -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/listbox.js"></script>
        <!-- Format Currency -->
        <script type="text/javascript" src="<?php echo $this->webroot.'js/jquery.formatCurrency-1.4.0.min.js'; ?>"></script>
        <!-- To Word -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/toword/toword_<?php echo $this->Session->read('lang'); ?>.js"></script>
        <!-- High Chart -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/HighChart-4-2-2/js/highcharts.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/HighChart-4-2-2/js/modules/exporting.js"></script>
        <!-- Check box Style -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/checkboxStyle/bootstrap2-toggle.min.js"></script>
        <!-- Menu Setting -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/menuSetting.js"></script>
        <!-- Tour -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/tours/intro.js"></script>
        <!-- Time Picker -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/timePicker/jquery-ui-timepicker.js"></script>
        <!-- Scroll Bar -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/scrollbar/jquery.nicescroll.min.js"></script>
        <style type="text/css">
            body{
                overflow: hidden;
            }
            
            .buttonMenu {
                display: block;
                height: 40px;
                width: 270px;
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 5px;
                text-align: center;
                border-color: #322883;
                color: #fff;
                box-shadow: 0 0 40px 40px #322883 inset, 0 0 0 0 #322883;
                transition: all 150ms ease-in-out;

                &:hover {
                    box-shadow: 0 0 10px 0 #e40138 inset, 0 0 10px 4px #e40138;
                    cursor: pointer;
                }
            }

            /* Absolute Center Spinner */
            .loading {
                position: fixed;
                z-index: 999;
                height: 2em;
                width: 2em;
                overflow: show;
                margin: auto;
                top: 0;
                left: 0;
                bottom: 0;
                right: 0;
            }

            /* Transparent Overlay */
            .loading:before {
                content: '';
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: radial-gradient(rgba(20, 20, 20,.8), rgba(0, 0, 0, .8));

                background: -webkit-radial-gradient(rgba(20, 20, 20,.8), rgba(0, 0, 0,.8));
            }

            /* :not(:required) hides these rules from IE9 and below */
            .loading:not(:required) {
                /* hide "loading..." text */
                font: 0/0 a;
                color: transparent;
                text-shadow: none;
                background-color: transparent;
                border: 0;
            }

            .loading:not(:required):after {
                content: '';
                display: block;
                font-size: 10px;
                width: 1em;
                height: 1em;
                margin-top: -0.5em;
                -webkit-animation: spinner 150ms infinite linear;
                -moz-animation: spinner 150ms infinite linear;
                -ms-animation: spinner 150ms infinite linear;
                -o-animation: spinner 150ms infinite linear;
                animation: spinner 150ms infinite linear;
                border-radius: 0.5em;
                -webkit-box-shadow: rgba(255,255,255, 0.75) 1.5em 0 0 0, rgba(255,255,255, 0.75) 1.1em 1.1em 0 0, rgba(255,255,255, 0.75) 0 1.5em 0 0, rgba(255,255,255, 0.75) -1.1em 1.1em 0 0, rgba(255,255,255, 0.75) -1.5em 0 0 0, rgba(255,255,255, 0.75) -1.1em -1.1em 0 0, rgba(255,255,255, 0.75) 0 -1.5em 0 0, rgba(255,255,255, 0.75) 1.1em -1.1em 0 0;
                box-shadow: rgba(255,255,255, 0.75) 1.5em 0 0 0, rgba(255,255,255, 0.75) 1.1em 1.1em 0 0, rgba(255,255,255, 0.75) 0 1.5em 0 0, rgba(255,255,255, 0.75) -1.1em 1.1em 0 0, rgba(255,255,255, 0.75) -1.5em 0 0 0, rgba(255,255,255, 0.75) -1.1em -1.1em 0 0, rgba(255,255,255, 0.75) 0 -1.5em 0 0, rgba(255,255,255, 0.75) 1.1em -1.1em 0 0;
            }

            /* Animation */

            @-webkit-keyframes spinner {
                0% {
                    -webkit-transform: rotate(0deg);
                    -moz-transform: rotate(0deg);
                    -ms-transform: rotate(0deg);
                    -o-transform: rotate(0deg);
                    transform: rotate(0deg);
                }
                100% {
                    -webkit-transform: rotate(360deg);
                    -moz-transform: rotate(360deg);
                    -ms-transform: rotate(360deg);
                    -o-transform: rotate(360deg);
                    transform: rotate(360deg);
                }
            }
            @-moz-keyframes spinner {
                0% {
                    -webkit-transform: rotate(0deg);
                    -moz-transform: rotate(0deg);
                    -ms-transform: rotate(0deg);
                    -o-transform: rotate(0deg);
                    transform: rotate(0deg);
                }
                100% {
                    -webkit-transform: rotate(360deg);
                    -moz-transform: rotate(360deg);
                    -ms-transform: rotate(360deg);
                    -o-transform: rotate(360deg);
                    transform: rotate(360deg);
                }
            }
            @-o-keyframes spinner {
                0% {
                    -webkit-transform: rotate(0deg);
                    -moz-transform: rotate(0deg);
                    -ms-transform: rotate(0deg);
                    -o-transform: rotate(0deg);
                    transform: rotate(0deg);
                }
                100% {
                    -webkit-transform: rotate(360deg);
                    -moz-transform: rotate(360deg);
                    -ms-transform: rotate(360deg);
                    -o-transform: rotate(360deg);
                    transform: rotate(360deg);
                }
            }
            @keyframes spinner {
                0% {
                    -webkit-transform: rotate(0deg);
                    -moz-transform: rotate(0deg);
                    -ms-transform: rotate(0deg);
                    -o-transform: rotate(0deg);
                    transform: rotate(0deg);
                }
                100% {
                    -webkit-transform: rotate(360deg);
                    -moz-transform: rotate(360deg);
                    -ms-transform: rotate(360deg);
                    -o-transform: rotate(360deg);
                    transform: rotate(360deg);
                }
            }
        </style>
        <script type="text/javascript">
            function preventKeyEnter() {
                // Prevent Input Key Enter
                $("input[type='text']").keypress(function (e) {
                    if ((e.which && e.which == 13) || e.keyCode == 13) {
                        return false;
                    }
                });
            }

            function replaceNum(str) {
                if (str != "" && str != undefined && str != null) {
                    var str = parseFloat(str.toString().replace(/,/g, ""));
                } else {
                    var str = 0;
                }
                return str;
            }

            function converDicemalJS(value) {
                return Math.round(parseFloat(value) * 1000000000) / 1000000000;
            }
            
            function msgAlert(text, valid, enable){
                if(enable == true){
                    $("#msgAlert").show();
                    $("#msgAlert").text(text);
                    if(valid == true){
                        $("#msgAlert").css("background-color", "#0063DC");
                    } else {
                        $("#msgAlert").css("background-color", "red");
                    }
                } else {
                    $("#msgAlert").hide();
                    $("#msgAlert").text("");
                }
            }
            
            $(document).ready(function(){
                // Function Option Hide/Show
                $.fn.showHideDropdownOptions = function(value, canShowOption) { 
                    $(this).find('option[value="' + value + '"]').map(function () {
                        return $(this).parent('span').length === 0 ? this : null;
                    }).wrap('<span>').hide();

                    if (canShowOption) {
                        $(this).find('option[value="' + value + '"]').unwrap().show();
                    } else {
                        $(this).find('option[value="' + value + '"]').hide();
                    }
               };

               // Function Option Hide/Show
               $.fn.filterOptions = function(objCompare, compare, selected) { 
                    var object = $(this);
                    // Hide by Filter
                    object.find("option").removeAttr('selected');
                    object.find("option").each(function(){
                        if($(this).val() != '' && $(this).val() != 'all'){
                            var value = $(this).val();
                            var compareId = $(this).attr(objCompare).split(",");
                            if(compareId.indexOf(compare)==-1){
                                object.showHideDropdownOptions(value, false);
                            } else {
                                object.showHideDropdownOptions(value, true);
                            }
                        }
                    });
                    // OPTION SELECTED
                    object.find('option[value="'+selected+'"]').attr('selected', true);
               };
            });
        </script>
    </head>
    <body>
        <div class="loading" style="display: none;">Loading&#8230;</div>
        <div>
            <?php echo $content_for_layout; ?>
        </div>
        <div id="dialog" title=""></div>
        <div id="dialog1" title=""></div>
    </body>
</html>