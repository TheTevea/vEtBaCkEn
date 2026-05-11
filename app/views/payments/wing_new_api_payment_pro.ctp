<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<?php
include('includes/NewWingConfig.php');
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
        $wingPay       = new WingSdkRequest();
        $paymentAccess = $wingPay->request($transactionId, $token, $amount);
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