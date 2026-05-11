<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackUser").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableUser.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackUser">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_USER_MANAGEMENT_INFO); ?></legend>
    <table width="100%" cellpadding="5">
    <tr>
        <th style="width: 10%; font-size: 12px;"><?php __(TABLE_FIRST_NAME); ?></th>
        <td style="font-size: 12px;"><?php echo $user['User']['first_name']; ?></td>
        <th style="width: 10%; font-size: 12px;"><?php __(TABLE_LAST_NAME); ?></th>
        <td style="font-size: 12px;"><?php echo $user['User']['last_name']; ?></td>
    </tr>
    <tr>
        <th style="font-size: 12px;"><?php __(TABLE_EMAIL); ?></th>
        <td style="font-size: 12px;"><?php echo $user['User']['email']; ?></td>
        <th style="font-size: 12px;"><?php __(TABLE_TELEPHONE); ?></th>
        <td style="font-size: 12px;"><?php echo $user['User']['telephone']; ?></td>
    </tr
    <tr>
        
    </tr>
</table>
</fieldset>