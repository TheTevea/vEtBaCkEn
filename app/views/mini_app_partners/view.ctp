<?php include("includes/function.php"); ?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackMiniAppPartner").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableMiniAppPartner.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackMiniAppPartner">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php echo MENU_MINI_APP_INFO; ?></legend>
    <table width="100%" class="info">
        <tr>
            <th style="width:20%;"><?php __(TABLE_NAME); ?></th>
            <td><?php echo $this->data['MiniAppPartner']['name']; ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_CODE); ?></th>
            <td><?php echo $this->data['MiniAppPartner']['code']; ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_STATUS); ?></th>
            <td><?php echo $this->data['MiniAppPartner']['status'] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE; ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_CREATED); ?></th>
            <td><?php echo dateShort($this->data['MiniAppPartner']['created_at'], "d/m/Y"); ?></td>
        </tr>
    </table>
</fieldset>
