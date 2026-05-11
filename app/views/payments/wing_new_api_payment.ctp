<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<?php
include('includes/WingApiCheckout.php');

class TestSdkRequest
{
    // Testing
    // private static $urlToken = "https://stageonline.wingmoney.com/wingonlinesdk/v2/online/payment/authorization";
    // private static $urlConfirm = "https://stageonline.wingmoney.com/wingonlinesdk/v2/online/payment/confirm";
    // private static $port = 443;
    // Testing
    // private static $username = "online.vireak";
    // private static $password = "204cb7ed77cca3c0ab04639da94c5a7e";
    // private static $apiKey   = "927e88094c83c6b69bb4ad219d92e9df91fab3eb010b2516168ee6487da5def1";
    // private static $billCode = "5004";
    // Production
    private static $urlToken = "https://wingsdk.wingmoney.com:334/v2/online/payment/authorization";
    private static $urlConfirm = "https://wingsdk.wingmoney.com:334/v2/online/payment/confirm";
    private static $port = 334;
    private static $method = "POST";
    private static $header = array("content-type: application/json", "cache-control: no-cache");
    // Production
    private static $username = "online.vireakapp";
    private static $password = "914bade01fd32493a0f2efc583e1a5f6";
    private static $apiKey   = "05c353921c75fd7810da5ca933471a3f891adc01ee0bfee58a8c51548e04d744";
    private static $billCode = "5017";
    // Local
    // private static $cancelUrl = "http://localhost/0430_CamTicket/payments/wingCancel/";
    // private static $returnUrl = "http://localhost/0430_CamTicket/payments/saveWingCompleted/";
    // QA 
    // private static $cancelUrl = "https://qacl.udaya-tech.com/0430_CamTicket/payments/wingCancel/";
    // private static $returnUrl = "https://qacl.udaya-tech.com/0430_CamTicket/payments/saveWingCompleted/";
    // Production
    private static $cancelUrl = "https://vetticket.utlog.net/payments/wingCancel/";
    private static $returnUrl = "https://vetticket.utlog.net/payments/saveWingCompleted/";
    private static $sandbox   = "0";  // 1: Testing; 0: Production

    public function request($referenceNo, $token, $totalAmount)
    {
        /* Credential and Data Partner */
        $password = self::$password; // Wing Provide
        $data = array(
            "username" => self::$username, // Wing Provide
            "rest_api_key" => self::$apiKey, // Wing Provide
            "bill_till_number" => self::$billCode, // Wing Provide
            "bill_till_rbtn" => "0",
            "amount" => $totalAmount,
            "order_reference_no" => $referenceNo,
            "return_url" => self::$returnUrl.$referenceNo."/".$token,
            "cancel_url" => self::$cancelUrl.$referenceNo,
            "rand_str" => WingApiCheckout::generateRandomString(16),
            "sandbox" => self::$sandbox, // 1: Testing, 0: Production
            "timestamp" => WingApiCheckout::genTimeStamp(),
            "wing_account" => "", // Optional
            "merchant_name" => "", // Optional
            "is_inquiry" => "1" // 0: callback, 1: Inquiry
        );

        /* Prepare Data Request SDK */
        $dataReq = array(
            "username" => $data['username'],
            "rest_api_key" => $data['rest_api_key'],
            "payload" => WingApiCheckout::genPayload($data, $password),
            "hash" => WingApiCheckout::genHash($data, $password),
            "sandbox" => $data['sandbox']
        );

        /* Request SDK Token */
        $respResult = WingApiCheckout::requestSdkToken(self::$urlToken, self::$port, self::$method, self::$header, json_encode($dataReq));
        if ($respResult != "") {
            $dataResp = json_decode($respResult, true);
            if (isset($dataResp['errorCode']) && $dataResp['errorCode'] == "200") {
                // Save Wing Token
                mysql_query("INSERT INTO `wing_payments` (`id`, `transaction_no`, `token`, `created`) VALUES (NULL, '".$referenceNo."', '".$dataResp['token']."', now());");
                /* Redirect to Confirm */
                return WingApiCheckout::requestSdkConfirm(self::$urlConfirm, $dataResp['token']);
            } else {
                return $respResult;
            }
        } else {
            return "fail request";
        }
    }
}
$sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 2 AND payment_method_id = 4 LIMIT 1");
if(mysql_num_rows($sqlChk)){
    $rowChk = mysql_fetch_array($sqlChk);
    $dateCreated = strtotime($rowChk['created'].' + 30 minute');
    $dateNow     = strtotime(date("Y-m-d H:i:s")); 
    if($dateCreated > $dateNow){
        // Update Click Payment
        mysql_query("UPDATE online_orders SET click_payment = 1 WHERE id = ".$rowChk['id']);
        // Request to wing
        $transactionId = $rowChk['code'];
        $amount        = number_format(($rowChk['total_amount'] + $rowChk['total_vat'] + $rowChk['lucky_draw_fee'] - $rowChk['discount_amount'] - $rowChk['coupon_amount']), 2);
        $test = new TestSdkRequest();
        $paymentAccess = $test->request($transactionId, $token, $amount);
?>
    Wing Processing....
<script>
    window.location.href = "<?php echo $paymentAccess; ?>";
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