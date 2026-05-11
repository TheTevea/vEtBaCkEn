<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackMainBranch").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableMainBranch.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackMainBranch">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_MAIN_BRANCH_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_NAME); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['MainBranch']['name']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_ORIGIN); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['TDestination']['name']; ?></td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php __(USER_USER_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <td colspan="2">Users Internal</td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(USER_USER_NAME); ?> :</th>
            <td style="font-size: 12px;">
                <?php 
                $sqlUser = mysql_query("SELECT GROUP_CONCAT(CONCAT(first_name,' ',last_name)) FROM users WHERE is_active = 1 AND `type` = 2 AND main_branch_id = ".$this->data['MainBranch']['id']);
                $rowUser = mysql_fetch_array($sqlUser);
                echo $rowUser[0];
                ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">Users Agency</td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(USER_USER_NAME); ?> :</th>
            <td style="font-size: 12px;">
                <?php 
                $sqlUser = mysql_query("SELECT GROUP_CONCAT(CONCAT(first_name,' ',last_name)) FROM users WHERE is_active = 1 AND `type` IN (3,4) AND main_branch_id = ".$this->data['MainBranch']['id']);
                $rowUser = mysql_fetch_array($sqlUser);
                echo $rowUser[0];
                ?>
            </td>
        </tr>
    </table>
</fieldset>