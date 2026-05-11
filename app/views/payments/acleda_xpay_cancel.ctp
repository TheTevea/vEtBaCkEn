<?php
include('includes/AcledaCheckout.php');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Vireak Buntham | Acleda Payment</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        <meta name="author" content="Acleda Payment Cancel">
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.7.min.js"></script>
    </head>
<body>
<script>
    window.location.href = "<?php echo ACLENDA_WEB_PAYMENT_CART_URL; ?>?transactionId=<?php echo $transactionId; ?>";
</script>
</body>
</html>