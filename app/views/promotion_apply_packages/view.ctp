<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackPromotionApplyPackage").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePromotionApplyPackage.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPromotionApplyPackage">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_PROMOTION_PACKAGE_APPLY_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <td rowspan="5" style="width: 160px;">
                <?php
                $img = "";
                if(!empty($this->data['TravelPackageOrder']['photo'])){
                    $img = $this->data['TravelPackageOrder']['photo_path'].$this->data['TravelPackageOrder']['photo'];
                }
                ?>
                <img src="<?php echo $img; ?>" style="width: 150px;" />
            </td>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_NAME); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['TravelPackageOrder']['name']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_TELEPHONE); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['TravelPackageOrder']['telephone']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_PASSPORT); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['TravelPackageOrder']['passport']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_SEX); ?></th>
            <td style="font-size: 12px;">
                <?php
                if($this->data['TravelPackageOrder']['sex'] == 1){
                    echo "Male"; 
                } else if($this->data['TravelPackageOrder']['sex'] == 2){
                    echo "Female"; 
                }
                ?>
            </td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Package Date"); ?></th>
            <td style="font-size: 12px;"><?php echo dateShort($this->data['TravelPackageOrder']['package_date']); ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Package Code"); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['TravelPackageOrder']['package_code']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Package Expired"); ?></th>
            <td style="font-size: 12px;"><?php echo dateShort($this->data['TravelPackageOrder']['package_expired']); ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Created"); ?></th>
            <td style="font-size: 12px;"><?php echo dateShort($this->data['TravelPackageOrder']['created'], "d/m/Y H:i:s"); ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Created By"); ?></th>
            <td style="font-size: 12px;">
                <?php 
                if(!empty($this->data['TravelPackageOrder']['created_by'])){
                    $sqlUser = mysql_query("SELECT * FROM users WHERE id = ".$this->data['TravelPackageOrder']['created_by']);
                    $rowUser = mysql_fetch_array($sqlUser);
                    echo $rowUser['username'];
                }
                ?>
            </td>
        </tr>
    </table>
</fieldset>
<br/>