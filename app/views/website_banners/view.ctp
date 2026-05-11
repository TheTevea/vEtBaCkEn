<?php
include("includes/websiteUpload.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackWebsiteBanner").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableWebsiteBanner.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackWebsiteBanner">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_WEBSITE_BANNER); ?></legend>
    <table width="100%" class="info">
        <tr>
            <th><?php __(TABLE_TYPE); ?></th>
            <td>
                <?php 
                if($this->data['WebsiteBanner']['website_type'] == 1){
                    echo "Vireak Buntham";
                } else {
                    echo "Buva Sea";
                }
                ?>
            </td>
        </tr>
        <tr>
            <th><?php __(TABLE_NAME); ?></th>
            <td><?php echo $this->data['WebsiteBanner']['name']; ?></td>
        </tr>
        <tr>
            <th style="vertical-align: top;"><?php __("Photo"); ?></th>
            <td style="vertical-align: top;">
                <?php 
                if($this->data['WebsiteBanner']['photo'] != ""){
                    $photo = $this->data['WebsiteBanner']['photo'];
                } else {
                    $photo = $this->webroot."img/no-images.png";
                }
                ?>
                <img src="<?php echo $photo; ?>" style=" max-width: 140px; max-height: 140px;" />
            </td>
        </tr>
    </table>
</fieldset>