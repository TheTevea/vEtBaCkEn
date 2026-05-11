<!DOCTYPE html>
<html lang="en">

    <head>
        <title>Vireak Buntham | ABA PayWay</title>

        <!— Make a copy of this code to paste into your site—>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        <meta name="author" content="PayWay">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/payment.css" media="print" />
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
                $apiKey       = ABA_PAYWAY_API_KEY;
                $merchant_id  = ABA_PAYWAY_MERCHANT_ID;
                $machineName  = "Vireak Buntham";
                if($rowTicket['company_id'] == 6 || $rowTicket['company_id'] == 17){
                    $machineName  = "Buva Sea";
                    $merchant_id  = ABA_PAYWAY_MERCHANT_ID_BUVASEA;
                    $apiKey       = ABA_PAYWAY_API_KEY_BUVASEA;
                } else if($rowTicket['company_id'] == 7 || $rowTicket['company_id'] == 12 || $rowTicket['company_id'] == 13 || $rowTicket['company_id'] == 14){
                    $machineName  = "Air Bus";
                    $merchant_id  = ABA_PAYWAY_MERCHANT_ID_AIRBUS;
                    $apiKey       = ABA_PAYWAY_API_KEY_AIRBUS;
                }
                $req_time      = time();
                $transactionId = $rowChk['code'];
                $amount        = ($rowChk['total_amount'] + $rowChk['total_vat'] + $rowChk['lucky_draw_fee'] - $rowChk['discount_amount'] - $rowChk['coupon_amount']);
                // $paymentOption = 'abapay';
                $paymentOption = '';
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
            ?>
            <table cellpadding="0" cellspacing="0" style="width: 100%; height: 380px;">
                <tr>
                    <td style="vertical-align: top; text-align: center; padding-top: 20px; width: 380px;">
                        <div style="background-color: #005D7B; width: 380px; height: 410px; border-top-left-radius: 10px; border-top-right-radius: 10px; margin: 0px auto; padding: 0px;">
                            <table cellpadding="0" cellspacing="0" style="width: 100%;">
                                <tr>
                                    <td style="text-align: center; font-size: 22px; font-weight: bold; color: #fff; height: 60px; font-family: Roboto;">
                                        <img src="<?php echo $this->webroot;?>img/ABA_Bank.png">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-size: 16px; font-weight: bold; color: #fff; height: 30px; font-family: Roboto;"><span style="font-family: KhmerOSDangrek;">ស្គែនទូទាត់</span> / Scan to Pay</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-size: 16px; font-weight: bold; color: #fff; padding-top: 10px;">
                                        <img style="width: 180px; height: 180px;" src="<?php echo $qrCode; ?>" id="abaQrCode">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-size: 16px; font-weight: bold; color: #fff; padding-top: 10px; padding-bottom: 10px;">
                                        <div style="padding: 0px; margin-top: 10px; line-height: 30px;">
                                            <img src="<?php echo $this->webroot;?>img/ABA-PAY.png">
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div style="width: 380px; text-align: center; font-size: 22px; font-weight: bold; color: #fff; padding-top: 10px; background-color: #004358; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; height: 60px; margin: 0px auto; font-family: Roboto;">
                            $<?php echo number_format($rowChk['total_amount'] - $rowChk['discount_amount'], 2); ?>
                        </div>
                    </td>
                    <td style="vertical-align: top; padding: 10px; text-align: right; border-left: 1px solid #f6f6f6;">
                        <table cellpadding="10" cellspacing="0" style="width: 100%; margin: 10px;">
                            <tr>
                                <td colspan="2" style="font-size: 26px; font-weight: bold; text-align: left; color: #000; border-bottom: dotted 1px #000;">
                                    Transaction Summary
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 110px; font-size: 16px; text-align: left; color: #000;">Order ID :</td>
                                <td style="text-align: right; color: #000; font-size: 18px;">
                                    <?php echo $rowChk['code']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 16px; text-align: left; color: #000; border-top: dotted 1px #000;">Sub Total  :</td>
                                <td style="font-size: 18px; text-align: right; color: #000; border-top: dotted 1px #000;">
                                    <?php echo number_format($rowChk['total_amount'], 2); ?> USD
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 16px; text-align: left; color: #000; border-top: dotted 1px #000;">Discount :</td>
                                <td style="font-size: 18px; text-align: right; color: #000; border-top: dotted 1px #000;">
                                    <?php echo number_format($rowChk['discount_amount'] + $rowChk['coupon_amount'], 2); ?> USD
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 16px; font-weight: bold; border-top: dotted 1px #000; text-align: left; color: #081b37;">TOTAL</td>
                                <td style="font-size: 18px; font-weight: bold; border-top: dotted 1px #000; text-align: right; color: #081b37;">
                                    <?php echo number_format($rowChk['total_amount'] - $rowChk['discount_amount'] - $rowChk['coupon_amount'], 2); ?> USD
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <!-- <div class="loading">Loading&#8230;</div> -->
            <?php 
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
</html>