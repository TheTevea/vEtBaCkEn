<?php
include("includes/function.php");
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <title>Vireak Buntham | ABA PayWay</title>

        <!— Make a copy of this code to paste into your site—>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        <meta name="author" content="PayWay">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <!-- QR Code -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.qrcode.min.js"></script>
        <style>
            html{
                line-height: 1;
            }
            .uk-flex {
                display: flex;
            }
            
            .uk-flex-center {
                justify-content: center;
            }
            
            .uk-flex-right {
                justify-content: flex-end;
            }
            
            .uk-flex-middle {
                align-items: center;
            }

            .uk-flex-column {
                flex-direction: column;
            }
            
            .uk-height-1-1 {
                height: 100%;
            }
            
            .uk-animation-fade {
                animation-name: uk-fade;
                animation-duration: .8s;
                animation-timing-function: linear;
            }

            .alipay-qr-frame{
                width: 181px;
                height: 178px;
                margin-top:80px;
                margin-left: 46px;
                background-image: url("<?php echo $this->webroot;?>img/alipay_frame.png");
            }
            
            .khqr-content {
                width: 360px;
                height: 360px;
                /* box-shadow: 0 8px 16px rgba(0,0,0,.08); */
                border-radius: 18px;
                margin-bottom: 36px;
                text-align: left;
            }
            
            .khqr-banner {
                height: 47.52px;
                background: #e21a1a;
                border-top-left-radius: 22px;
                border-top-right-radius: 22px;
            }
            
            .triangle-top-right {
                width: 0;
                height: 0;
                border-top: 22.86px solid #e21a1a;
                border-left: 22.86px solid transparent;
            }
            
            .merc-khqr-info {
                padding: 0 8px 0 39px;
            }
            
            .uk-text-12 {
                font-size: 12px;
            }
            
            .uk-margin-12-top {
                margin-top: 12px !important;
            }
            
            .total-amount-khqr .amount-value {
                position: relative;
                font-size: 20px;
                font-weight: 700;
                margin-bottom: 0;
                width: 100% !important;
            }
            
            .total-amount-khqr .amount-currency {
                font-size: 12px;
                font-weight: 400;
                position: absolute;
                margin-left: 4px;
                bottom: 4px;
            }
            
            .line-divider {
                background-image: url("<?php echo $this->webroot;?>img/line-divider.svg");
                width: 100%;
                height: 1px;
                margin-top: 18px;
            }
            
            .scanToPay{
                position: absolute;
                top: 98px;
                margin-left: 56px;
                font-size: 14px;
            }
        </style>
    </head>
    <body style="padding: 0; margin: 0;">   
        <?php 
        // Function
        include('includes/PayWayApiCheckout.php');
        $sqlLastPay  = mysql_query("SELECT * FROM ticket_payments WHERE t_ticket_id = ".$ticketId." AND payment_method_id = 7 ORDER BY id DESC LIMIT 1");
        if(mysql_num_rows($sqlLastPay)){
            $rowLastPay = mysql_fetch_array($sqlLastPay);
            $sqlChk     = mysql_query("SELECT * FROM t_tickets WHERE id = ".$ticketId." AND offline_project_id = 1 AND status = 1 LIMIT 1");
            if(mysql_num_rows($sqlChk)){
                $rowChk      = mysql_fetch_array($sqlChk);
                $dateCreated = strtotime($rowLastPay['created'].' + 10 minute');
                $dateNow     = strtotime(date("Y-m-d H:i:s")); 
                if($dateCreated > $dateNow){
                    // Check Bank Ref
                    if(empty($rowChk['api_bank_ref'])){
                        $transactionId = "TMLP-".getRandomString(13);
                        mysql_query("UPDATE t_tickets SET api_bank_ref = '".$transactionId."' WHERE id = ".$ticketId);
                        mysql_query("INSERT INTO `terminal_phone_call_pay_codes` (`id`, `t_ticket_id`, `code`, `created`) 
                                     VALUES (NULL, ".$ticketId.", '".$transactionId."', now());");
                    } else {
                        $transactionId = $rowChk['api_bank_ref'];
                    }
                    $apiKey       = ABA_PAYWAY_API_KEY;
                    $merchant_id  = ABA_PAYWAY_MERCHANT_ID;
                    $machineName  = "Vireak Buntham";
                    if($rowChk['company_id'] == 6 || $rowChk['company_id'] == 17){
                        $apiKey       = ABA_PAYWAY_API_KEY_BUVASEA;
                        $merchant_id  = ABA_PAYWAY_MERCHANT_ID_BUVASEA;
                        $machineName  = "Buva Sea";
                    } else if($rowChk['company_id'] == 7 || $rowChk['company_id'] == 12){
                        $apiKey       = ABA_PAYWAY_API_KEY_AIRBUS;
                        $merchant_id  = ABA_PAYWAY_MERCHANT_ID_AIRBUS;
                        $machineName  = "VET AIR BUS";                    
                    }
                    $req_time      = time();
                    $amount        = ($rowChk['total_amount'] + $rowChk['total_vat'] + $rowChk['lucky_draw_fee'] + $rowChk['total_markup'] - $rowChk['discount_amount'] - $rowChk['coupon_amount']);
                    $paymentOption = 'alipay';
                    $qrCode        =  "";
                    $lifeTime      = 10; // 10 minutes
                    // CURL
                    $url  = PayWayApiCheckout::getApiUrl();
                    $post = [
                        'hash'     => PayWayApiCheckout::getHash($req_time, $merchant_id, $transactionId, $amount, $paymentOption, $lifeTime, $apiKey),
                        'tran_id'  => $transactionId,
                        'amount'   => $amount,
                        'req_time' => $req_time,
                        'merchant_id'  => $merchant_id,
                        'payment_option'  => $paymentOption,
                        'view_type'  => 'qr',
                        'lifetime' => $lifeTime
                    ];
                    $headers = array(
                        'accept: */*',
                        'Content-Type: multipart/form-data',
                        'Referer: '.PAYMENT_URL_REF
                    );
                    // CURL
                    $curl  = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($curl, CURLOPT_POST, 1);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                    $curlResp     = curl_exec($curl);
                    $curl_errno   = curl_errno($curl);
                    $curl_error   = curl_error($curl);
                    curl_close ($curl);
                    if ($curl_errno > 0) {
                        $result['status'] = 0;
                        $result['info'] = "cURL Error ($curl_errno): $curl_error\n";
                    } else {
                        $response = json_decode($curlResp, true);
                        $qrCode =  $response['qrString'];
                        switch (json_last_error()) {
                            case JSON_ERROR_NONE:
                                $convertJson = true;
                            break;
                            case JSON_ERROR_DEPTH:
                                $result['info']   = ' - Maximum stack depth exceeded';
                            break;
                            case JSON_ERROR_STATE_MISMATCH:
                                $result['info']   = ' - Underflow or the modes mismatch';
                            break;
                            case JSON_ERROR_CTRL_CHAR:
                                $result['info']   = ' - Unexpected control character found';
                            break;
                            case JSON_ERROR_SYNTAX:
                                $result['info']   = ' - Syntax error, malformed JSON';
                            break;
                            case JSON_ERROR_UTF8:
                                $result['info']   = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                            break;
                            default:
                                $result['info']   = ' - Unknown error';
                            break;
                        }
                    }
                    if(!empty($qrCode)){
                ?>
                <div style="width:273px; height:505px; padding:24px; margin: 0px auto;">
                    <div class="khqr-content uk-flex uk-flex-column">
                        <div class="alipay-qr-frame">
                            <div style="width: 144px; height: 144px; margin-left: 18.5px; margin-top: 17px;" class="cardQRCode"></div>
                            <span class="scanToPay">Scan to pay</span>
                        </div>
                        <div style="margin-top: 34px; margin-left: 113px;">
                            <img src="<?php echo $this->webroot;?>img/Alipay.png">
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                    $(document).ready(function(){  
                        var qrCode = "<?php echo $qrCode; ?>";
                        var obj    = $(".cardQRCode");
                        obj.qrcode({
                            width	: "144",
                            height	: "144",
                            text	: qrCode
                        }); 
                    });
                </script>
                <?php
                    } else {
                        echo "Invalid Payment QR Code";
                    }
                }else{
                ?>
                    <div class="content" style="width: 100%;vertical-align: middle;text-align: center;height: 100%;vertical-align: middle;align-items: center;display: flex;justify-content: center;">
                        <div style="width: 100%; text-align: center;" id="content-sms">
                            <img src="<?php echo $this->webroot;?>img/error.png" style="width: auto;height: 180px;" alt="" />
                            <p style="font-size: 30px;">
                                Your token has been expired!
                            </p>
                        </div>
                    </div>
                <?php
                }    
            } else {

            }
        } else {
            ?>
            <div class="content" style="width: 100%;vertical-align: middle;text-align: center;height: 100%;vertical-align: middle;align-items: center;display: flex;justify-content: center;">
                <div style="width: 100%; text-align: center;" id="content-sms">
                    <img src="<?php echo $this->webroot;?>img/error.png" style="width: auto;height: 180px;" alt="" />
                    <p style="font-size: 26px;">
                        Not match data record!
                    </p>
                </div>
            </div>
            <?php
        }
        ?>
    </body>
</html>