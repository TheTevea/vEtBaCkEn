<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackTCommision").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTCommision.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTCommision">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_COMMISION_INFO); ?></legend>
    <table style="width: 100%;">
        <tr>
            <th style="width:20%;"><?php __(TABLE_NAME); ?></th>
            <td style="width:1%;">:</td>
            <td>
                <?php echo $this->data['TCommision']['name']; ?>
            </td>
        </tr>
        <tr>
            <th style="width:20%;"><?php __(GENERAL_AMOUNT); ?></th>
            <td style="width:1%;">:</td>
            <td>
                <?php echo number_format($this->data['TCommision']['amount'], 2); ?> $
            </td>
        </tr>
        <tr>
            <th style="width:20%;"><?php __(TABLE_PERCENT); ?></th>
            <td style="width:1%;">:</td>
            <td>
                <?php echo number_format($this->data['TCommision']['percentage'], 2); ?> (%)
            </td>
        </tr>
    </table>
 </fieldset>