<?php
/**
 * Copyright UDAYA Technology Co,.LTD (http://www.udaya-tech.com)
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <title>
            <?php __('VET Ticket - Payment Success'); ?>
        </title>

        <!-- icon -->
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->webroot; ?>img/logo.ico" />

        <!-- general stylesheet -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/login.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" />

        <!-- jquery -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>

        <!-- autoNumeric -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/autoNumeric-1.6.2.js"></script>
        
    </head>
    <body>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/wz_tooltip_v4.js"></script>
        <center>
            <table>
                <tr>
                    <td style="vertical-align: middle;">
                        <div>
                            <?php echo $content_for_layout; ?>
                            <div class="clear"></div>
                        </div>
                    </td>
                </tr>
            </table>
        </center>
    </body>
</html>