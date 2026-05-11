<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Vireak Buntham | Acleda XPay</title>
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->webroot; ?>img/favicon.ico" />
        <!— Make a copy of this code to paste into your site—>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        <meta name="author" content="Acleda XPay">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    </head>
    <body>
        <style type="text/css">
            body{
                overflow: hidden;
                padding-top: 120px;
            }

            .loader {
                z-index: 999;
                margin: auto;
                border: 0.2em solid #f3f3f3;
                border-radius: 50%;
                border-top: 0.2em solid #de5d09;
                width: 35px;
                height: 35px;
                -webkit-animation: spin 900ms linear infinite; /* Safari */
                animation: spin 900ms linear infinite;
            }

                /* Safari */
            @-webkit-keyframes spin {
                0% { -webkit-transform: rotate(0deg); }
                100% { -webkit-transform: rotate(360deg); }
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
        <?php
        $html = '<form id="_xpayTestForm" name="_xpayTestForm" action="'.$response['url'].'" method="post">
            <input type="hidden" id="merchantID" name="merchantID" value="'.$response['merchantID'].'">
            <input type="hidden" id="sessionid" name="sessionid" value="'.$response['sessionId'].'">
            <input type="hidden" id="paymenttokenid" name="paymenttokenid" value="'.$response['paymenttokenid'].'">
            <input type="hidden" id="description" name="description" value="'.$response['description'].'"> 
            <input type="hidden" id="expirytime" name="expirytime" value="'.$response['expirytime'].'"> 
            <input type="hidden" id="amount" name="amount" value="'.$response['amount'].'">
            <input type="hidden" id="quantity" name="quantity" value="'.$response['quantity'].'">
            <input type="hidden" id="item" name="item" value="'.$response['item'].'">
            <input type="hidden" id="invoiceid" name="invoiceid" value="'.$response['invoiceid'].'"> 
            <input type="hidden" id="currencytype" name="currencytype" value="'.$response['currencytype'].'"> 
            <input type="hidden" id="transactionID" name="transactionID" value="'.$response['transactionID'].'">
            <input type="hidden" id="successUrlToReturn" name="successUrlToReturn" value="'.$response['url_success'].$response['transactionID'].'/'.$response['token'].'/2">
            <input type="hidden" id="errorUrl" name="errorUrl" value="'.$response['url_cancel'].$response['paymenttokenid'].'/'.$response['transactionID'].'"> 
            <input type="hidden" id="companyName" name="companyName" value="'.$response['companyName'].'"/>
            <input type="hidden" id="paymentCard" name="paymentCard" value="1">
            <input type="submit" value="Submit" style="visibility: hidden;">
        </form>';
        echo $html;
        ?>
        <div class="loader"></div>
        <script>
            $(document).ready(function() {
                function disableBack() { window.history.forward() }
                window.onload = disableBack();
                window.onpageshow = function(evt) { if (evt.persisted) disableBack() } 
                $("#_xpayTestForm").submit();
            });
        </script>
    </body>
</html>
    
