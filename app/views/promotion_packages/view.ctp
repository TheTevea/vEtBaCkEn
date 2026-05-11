<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackPromotionPackage").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePromotionPackage.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPromotionPackage">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_PROMOTION_PACKAGE_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_NAME); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['TravelPackages']['name']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Period Use"); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['TravelPackages']['period_expired'], 0); ?> Months</td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 12px; font-weight: bold;">Apply Discount Condition</td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Buva Sea"); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['TravelPackages']['buva_sea'], 0); ?> %</td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 12px; font-weight: bold;">International Route</td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(" - Thailand"); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['TravelPackages']['international_thai'], 0); ?> %</td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(" - Vietname"); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['TravelPackages']['international_viet'], 0); ?> %</td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(" - Loas"); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['TravelPackages']['international_laos'], 0); ?> %</td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Local Route"); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['TravelPackages']['local'], 0); ?> %</td>
        </tr>
    </table>
</fieldset>
<br/>