<!-- QR Code -->
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.qrcode.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackAmenity").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableAmenity.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackAmenity">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<table width="100%" cellpadding="10">
    <tr>
        <td style="font-size: 12px;" colspan="2">
            <?php
            $img = "";
            if(!empty($this->data['Amenity']['photo'])){
                $img = $this->webroot."public/amenities/".$this->data['Amenity']['photo'];
            }
            ?>
            <img src="<?php echo $img; ?>" style="width: 128px; height: 128px;" />
        </td>
    </tr>
    <tr>
        <th style="width: 90px; font-size: 12px;"><?php echo TABLE_NAME; ?> :</th>
        <td style="font-size: 12px;"><?php echo $this->data['Amenity']['name']; ?></td>
    </tr>
</table>