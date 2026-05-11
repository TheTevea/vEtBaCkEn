<!-- QR Code -->
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.qrcode.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackBus").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableBus.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });

        $("#busQRCode").qrcode({
            width	: "150",
            height	: "150",
            text	: "https://vireakbuntham.com/feedback.php?busId=<?php echo $this->data['Bus']['id']; ?>"
        }); 
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackBus">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<table width="100%" cellpadding="5">
    <tr>
        <th style="width: 10%; font-size: 12px;"><?php echo MENU_BUS_TYPE; ?></th>
        <td style="font-size: 12px;"><?php echo $this->data['BusType']['name']; ?></td>
    </tr>
    <tr>
        <th style="width: 10%; font-size: 12px;">Code</th>
        <td style="font-size: 12px;"><?php echo $this->data['Bus']['code']; ?></td>
    </tr>
    <tr>
        <th style="width: 10%; font-size: 12px;"><?php __("Plate No"); ?></th>
        <td style="font-size: 12px;"><?php echo $this->data['Bus']['name']; ?></td>
    </tr>
    <tr>
        <th style="width: 10%; font-size: 12px; padding-top: 20px;" colspan="2">
            <div id="busQRCode"></div>
        </th>
    </tr>
</table>