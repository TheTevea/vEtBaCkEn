<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackTravelPackageIntroduce").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTravelPackageIntroduce.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTravelPackageIntroduce">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_TRAVEL_PACKAGE_INTRODUCT_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Title"); ?> (Khmer)</th>
            <td style="font-size: 12px;"><?php echo $this->data['TravelPackageIntroduce']['title_kh']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __("Title"); ?> (English)</th>
            <td style="font-size: 12px;"><?php echo $this->data['TravelPackageIntroduce']['title_en']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __("Title"); ?> (Chinese)</th>
            <td style="font-size: 12px;"><?php echo $this->data['TravelPackageIntroduce']['title_cn']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(GENERAL_DESCRIPTION); ?> (Khmer)</th>
            <td style="font-size: 12px;"><?php echo nl2br($this->data['TravelPackageIntroduce']['desc_kh']); ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(GENERAL_DESCRIPTION); ?> (English)</th>
            <td style="font-size: 12px;"><?php echo nl2br($this->data['TravelPackageIntroduce']['desc_en']); ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(GENERAL_DESCRIPTION); ?> (Chinese)</th>
            <td style="font-size: 12px;"><?php echo nl2br($this->data['TravelPackageIntroduce']['desc_cn']); ?></td>
        </tr>
    </table>
</fieldset>
<br/>