<?php
/**
 * Copyright UDAYA Technology Co,.LTD (http://www.udaya-tech.com)
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
include("includes/function.php");
$start = 2018;
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>
            <?php __('Virak Buntham Express'); ?>
        </title>

        <!-- icon -->
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->webroot; ?>img/favicon.ico" />
        <!-- Style Sheet -->
        <!-- General Style Sheet -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/login.css?key=<?php echo time(); ?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" />
        <!-- Jquery UI -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/jquery-ui-1.8.14.custom/development-bundle/themes/base/jquery.ui.all.css" />
        <!-- Validator -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/validateEngine/css/validationEngine.jquery.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/validateEngine/css/template.css" />
        <!-- Time Picker -->
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>js/timePicker/jquery-ui-timepicker.css" />
        
        <!-- Jquery Script -->
        <!-- Jquery -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.7.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.cookie.js"></script>
        <!-- Jquery UI -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-ui-1.8.14.custom/js/jquery-ui-1.8.14.custom.min-<?php echo $this->Session->read('lang'); ?>.js"></script>
        <!-- Validator -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/validateEngine/js/jquery.validationEngine-en.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/validateEngine/js/jquery.validationEngine.js"></script>
        <!-- Time Picker -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/timePicker/jquery-ui-timepicker.js"></script>
    </head>
    <body>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/wz_tooltip_v4.js"></script>
        <div style="width: 350px; height: 546px; position: absolute; top: 50%; left: 49%; margin-left: -170px; margin-top: -320px;" id="loginForm">
            <div style="width: 340px; height: 600px; margin: 0px auto;">
                <div style="width: 100%; height: 550px;">
                    <?php echo $content_for_layout; ?>
                </div>
                <div style="width: 100%; text-align: center;">
                    © <?php echo $start; ?><?php echo date("Y") != $start ? "-" . date("Y") : ""; ?> VET TICKETING. All rights reserved.<br/>
                    Powered by UDAYA TECHNOLOGY CO.,LTD
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </body>
</html>