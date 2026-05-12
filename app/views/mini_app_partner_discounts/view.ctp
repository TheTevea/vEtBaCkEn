<?php include("includes/function.php"); ?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackMiniAppPartnerDiscount").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableMiniAppPartnerDiscount.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackMiniAppPartnerDiscount">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php echo MENU_MINI_APP_DISCOUNT_INFO; ?></legend>
    <table width="100%" class="info">
        <tr>
            <th style="width:20%;"><?php echo MENU_MINI_APP_PARTNER_NAME; ?></th>
            <td><?php echo !empty($this->data['MiniAppPartner']['name']) ? $this->data['MiniAppPartner']['name'] : $this->data['MiniAppPartnerDiscount']['mini_app_partner_id']; ?></td>
        </tr>
        <tr>
            <th><?php echo TABLE_FIXED_DISCOUNT; ?></th>
            <td><?php echo number_format($this->data['MiniAppPartnerDiscount']['fixed_discount'], 2).' $'; ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_PERCENT); ?></th>
            <td><?php echo number_format($this->data['MiniAppPartnerDiscount']['percent'], 2).' %'; ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_START_DATE); ?></th>
            <td><?php echo dateShort($this->data['MiniAppPartnerDiscount']['start_date'], "d/m/Y"); ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_END_DATE); ?></th>
            <td><?php echo dateShort($this->data['MiniAppPartnerDiscount']['end_date'], "d/m/Y"); ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_STATUS); ?></th>
            <td><?php echo $this->data['MiniAppPartnerDiscount']['status'] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE; ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_CREATED); ?></th>
            <td><?php echo dateShort($this->data['MiniAppPartnerDiscount']['created_at'], "d/m/Y"); ?></td>
        </tr>
    </table>
</fieldset>
