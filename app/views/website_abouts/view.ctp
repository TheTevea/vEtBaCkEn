<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackWebsiteAbout").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableWebsiteAbout.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackWebsiteAbout">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_WEBSITE_ABOUT); ?></legend>
    <table width="100%" class="info">
        <tr>
            <th><?php __(TABLE_NAME); ?></th>
            <td><?php echo $this->data['WebsiteAbout']['name']; ?></td>
        </tr>
        <tr>
            <th style="vertical-align: top;"><?php __(GENERAL_DESCRIPTION.' (Short)'); ?></th>
            <td style="vertical-align: top;"><?php echo $this->data['WebsiteAbout']['description']; ?></td>
        </tr>
        <tr>
            <th style="vertical-align: top;"><?php __(GENERAL_DESCRIPTION.' (Long)'); ?></th>
            <td style="vertical-align: top;"><?php echo $this->data['WebsiteAbout']['long_description']; ?></td>
        </tr>
    </table>
</fieldset>