<!DOCTYPE html>
<html lang="en">

    <head>
        <title>Vireak Buntham | ABA Credit/Debit Card</title>

        <!— Make a copy of this code to paste into your site—>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        <meta name="author" content="PayWay">
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
    // Function
    include('includes/PayWayApiCheckout.php');
    $sqlChk = mysql_query("SELECT * FROM travel_package_orders WHERE code = '".$transactionId."' AND status = 1 AND payment_method_id = 6 LIMIT 1");
    if(mysql_num_rows($sqlChk)){
        $rowChk = mysql_fetch_array($sqlChk);
        $dateCreated = strtotime($rowChk['created'].' + 10 minute');
        $dateNow     = strtotime(date("Y-m-d H:i:s")); 
        if($dateCreated > $dateNow){
            $apiKey        = ABA_PAYWAY_API_KEY; // Bus
            $merchant_id   = ABA_PAYWAY_MERCHANT_ID;
            $req_time      = time();
            $transactionId = $rowChk['code'];
            $amount        = $rowChk['package_price'];
            $paymentOption = 'cards';
            $lifeTime      = 10; // 10 minute
            // Update Click Payment
            mysql_query("UPDATE travel_package_orders SET pay_date = now() WHERE id = ".$rowChk['id']);
            // ABA payment success route to booking complete
            $returnUrl = base64_encode(PAYMENT_URL."packagePaymentComplete/".$transactionId."/".$token);
            // ABA route to payment success page
            $continueSuccess = "vetapp://payment/abaMobilePayPackage";
            $hash = base64_encode(hash_hmac('sha512', $req_time .$merchant_id .$transactionId .$amount .$paymentOption .$returnUrl .$continueSuccess .$lifeTime, $apiKey, true));
        ?>
        <form method="POST" action="<?php echo PayWayApiCheckout::getApiUrl(); ?>" id="aba_merchant_request">
            <input type="hidden" name="hash" value="<?php echo $hash; ?>" id="hash"/>
            <input type="hidden" name="tran_id" value="<?php echo $transactionId; ?>" id="tran_id"/>
            <input type="hidden" name="amount" value="<?php echo $amount; ?>" id="amount"/>
            <input type="hidden" name="req_time" value="<?php echo $req_time; ?>"/>
            <input type="hidden" name="merchant_id" value="<?php echo $merchant_id; ?>"/>
            <input type="hidden" name="payment_option" value="<?php echo $paymentOption; ?>"/>
            <input type="hidden" name="payment_gate" value="0"/>
            <input type="hidden" name="view_type" value="hosted_view"/>
            <input type="hidden" name="return_url" value="<?php echo $returnUrl; ?>"/>
            <input type="hidden" name="lifetime" value="<?php echo $lifeTime; ?>"/>
            <input type="hidden" name="continue_success_url" value="<?php echo $continueSuccess; ?>"/>
        </form>
        <div class="loader"></div>
        <script>
            $(document).ready(function(){
                $("#aba_merchant_request").submit();
            });
        </script>
        <?php
        } else{
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
<!— End —>
</body>
</html>
