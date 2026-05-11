<?php
$sqlChk = mysql_query("SELECT * FROM online_orders WHERE code = '".$transactionId."' AND status = 4 AND payment_method_id = 4 LIMIT 1");
$rowChk = mysql_fetch_array($sqlChk);
?>
<div style="padding: 0; margin: 0; vertical-align: top;">
    <div class="content" style="width: 100%;text-align: center;height: 100%;vertical-align: top;align-items: center;display: flex;justify-content: center; padding-top: 20px;">            
        <div style="width: 100%; text-align: center;" id="content-sms">
            <img src="<?php echo $this->webroot;?>img/checked.png" style="width: auto;height: 90px;" alt="" />
            <h1 style="font-size: 32px; color: #de5d09;">THANK YOU</h1>
            <p style="font-size: 16px;">
                Your payment was processed successfully.
            </p>
            <table style="width: 100%; text-align: left;" cellspacing="5" cellpadding="5">
                <tr>
                    <td colspan="2" style="border-top: #000 dotted;padding-top: 10px; font-size: 14px;">DATE: <?php echo date("d/m/Y H:i:s"); ?></td>
                </tr>
                <tr style="padding-top: 10px;">
                    <td colspan="2" style="font-size: 14px;">Order ID: <?php echo $rowChk['code']; ?></td>
                </tr>
                <tr>
                    <td style="border-top: #000 dotted; padding-top: 10px; font-size: 14px;">Sub Total: </td>
                    <td style="text-align: right;border-top: #000 dotted; padding-top: 10px; font-size: 14px;"><?php echo number_format($rowChk['total_amount'] + $rowChk['total_vat'], 2); ?> USD</td>
                </tr>
                <tr>
                    <td style="border-top: #000 dotted; padding-top: 10px; font-size: 14px;">Discount: </td>
                    <td style="text-align: right;border-top: #000 dotted; padding-top: 10px; font-size: 14px;"><?php echo number_format($rowChk['discount_amount'] + $rowChk['coupon_amount'], 2); ?> USD</td>
                </tr>
                <tr style="padding-top: 10px;">
                    <td style="border-top: #000 dotted; padding-top: 10px; font-size: 20px; font-weight: bold;">TOTAL: </td>
                    <td style="text-align: right;border-top: #000 dotted; padding-top: 10px; font-size: 20px; font-weight: bold;"><?php echo number_format((($rowChk['total_amount'] + $rowChk['total_vat']) - $rowChk['discount_amount']), 2); ?> USD</td>
                </tr>
            </table>
        </div>
    </div>
</div>