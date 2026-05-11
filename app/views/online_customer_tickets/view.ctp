<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackOnlineCustomerTicket").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableOnlineCustomerTicket.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackOnlineCustomerTicket">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_MEMBERSHIP_CARD_INFO); ?></legend>
    <table style="width: 100%;">
        <tr>
            <th style="width:20%;"><?php __(MENU_MAIN_BRANCH); ?></th>
            <td style="width:1%;">:</td>
            <td>
                <?php echo $this->data['MainBranch']['name']; ?>
            </td>
        </tr>
        <tr>
            <th><?php __(TABLE_NAME); ?></th>
            <td>:</td>
            <td>
                <?php echo $this->data['OnlineCustomerTicket']['name']; ?>
            </td>
        </tr>
        <tr>
            <th><?php __(TABLE_TELEPHONE); ?></th>
            <td>:</td>
            <td>
                <?php echo $this->data['OnlineCustomerTicket']['telephone']; ?>
            </td>
        </tr>
        <tr>
            <th><?php __(TABLE_CREATED); ?></th>
            <td>:</td>
            <td>
                <?php echo dateShort($this->data['OnlineCustomerTicket']['created'], "d/m/Y H:i:s"); ?>
            </td>
        </tr>
        <tr>
            <th><?php __(TABLE_CREATED_BY); ?></th>
            <td>:</td>
            <td>
                <?php echo $this->data['User']['username']; ?>
            </td>
        </tr>
    </table>
 </fieldset>