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
            
            .khqr-content {
                width: 273px;
                height: 396px;
                box-shadow: 0 8px 16px rgba(0,0,0,.08);
                border-radius: 22px;
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
            
            .logo{
                position: absolute;
            }
        </style>
    </head>
    <body style="padding: 0; margin: 0;">   
        <?php 
        // Function
        include('includes/PayWayApiCheckout.php');
        $sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id = 5 LIMIT 1");
        if(mysql_num_rows($sqlChk)){
            $rowChk      = mysql_fetch_array($sqlChk);
            $sqlTicket   = mysql_query("SELECT * FROM t_ticket_api_tmps WHERE online_order_id = ".$rowChk['id']." LIMIT 1");
            $rowTicket   = mysql_fetch_array($sqlTicket);
            $dateCreated = strtotime($rowChk['created'].' + 10 minute');
            $dateNow     = strtotime(date("Y-m-d H:i:s")); 
            if($dateCreated > $dateNow){
                $apiKey        = ABA_PAYWAY_API_KEY;
                $merchant_id   = ABA_PAYWAY_MERCHANT_ID;
                $machineName   = "Vireak Buntham";
                $paymentOption = 'abapay_khqr';
                if($rowTicket['company_id'] == 6 || $rowTicket['company_id'] == 17){ // Buva Sea
                    $apiKey       = ABA_PAYWAY_API_KEY_BUVASEA;
                    $merchant_id  = ABA_PAYWAY_MERCHANT_ID_BUVASEA;
                    $machineName  = "Buva Sea";
                } else if($rowTicket['company_id'] == 7 || $rowTicket['company_id'] == 12){ // VET Air Bus
                    $apiKey       = ABA_PAYWAY_API_KEY_AIRBUS;
                    $merchant_id  = ABA_PAYWAY_MERCHANT_ID_AIRBUS;  
                    $machineName  = "VET AIR BUS";            
                }
                $req_time      = time();
                $transactionId = $rowChk['code'];
                $amount        = ($rowChk['total_amount'] + $rowChk['total_vat'] + $rowChk['lucky_draw_fee'] - $rowChk['discount_amount'] - $rowChk['coupon_amount']);
                $qrCode        = "";
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
                    $qrCode =  $response['qrImage'];
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
                <div style="display:none; float:left; border:2px solid black; border-radius:15px; padding:5px; background-color:lightgray; border-color:transparent;" id="abaExpiredCount">
                </div>
                <div style="margin-top:24px; margin-bottom:50px; text-align:center;">
                    <img style="width: 273px;" src="<?php echo $this->webroot;?>img/ABA'Pay.png">
                </div>
                <div class="khqr-content uk-flex uk-flex-column">
                    <div>
                    <div class="khqr-banner uk-flex uk-flex-center uk-flex-middle">
                        <img src="https://checkout.payway.com.kh/images/khqr-icon.svg" alt="KHQR">
                    </div> <div class="uk-flex uk-flex-right">
                    <div class="triangle-top-right"></div>
                    </div> 
                    <div class="merc-khqr-info">
                        <span class="uk-text-12"><?php echo $machineName ?></span> 
                        <div class="total-amount-khqr uk-margin-12-top">
                            <span>
                            <div class="amount-value">
                                <?php echo number_format($amount, 2); ?>
                                <span class="amount-currency">USD</span>
                            </div>
                            </span>
                        </div>
                    </div> 
                    <div class="line-divider"></div>
                    </div> 
                    <div class="uk-flex uk-flex-center uk-flex-middle uk-height-1-1 uk-animation-fade">
                        <img style="width: 220px;" src="<?php echo $qrCode; ?>">
                        <img style="" class="logo" src="<?php echo $this->webroot;?>img/icon-usd.png">
                    </div>
                </div>
            </div>
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
    <script type="text/javascript">
        $(document).ready(function(){  
            
        });
    </script>
</html>