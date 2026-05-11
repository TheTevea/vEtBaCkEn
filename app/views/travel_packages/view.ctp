<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackTravelPackage").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTravelPackage.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTravelPackage">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_TRAVEL_PACKAGE_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <td rowspan="5" style="width: 160px;">
                <?php
                $img = "";
                if(!empty($this->data['TravelPackage']['photo'])){
                    $img = $this->data['TravelPackage']['photo_path'].$this->data['TravelPackage']['photo'];
                }
                ?>
                <img src="<?php echo $img; ?>" style="width: 150px; height: 100px;" />
            </td>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_NAME); ?> (Khmer)</th>
            <td style="font-size: 12px;"><?php echo $this->data['TravelPackage']['name_kh']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_NAME); ?> (English)</th>
            <td style="font-size: 12px;"><?php echo $this->data['TravelPackage']['name']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_NAME); ?> (Chinese)</th>
            <td style="font-size: 12px;"><?php echo $this->data['TravelPackage']['name_cn']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Price"); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['TravelPackage']['price'], 2); ?> $</td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Discount"); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['TravelPackage']['discount'], 2); ?> $</td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Period Use"); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['TravelPackage']['period_expired'], 0); ?> Months</td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(GENERAL_DESCRIPTION); ?> (Khmer)</th>
            <td style="font-size: 12px;"><?php echo nl2br($this->data['TravelPackage']['description_kh']); ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(GENERAL_DESCRIPTION); ?> (English)</th>
            <td style="font-size: 12px;"><?php echo nl2br($this->data['TravelPackage']['description']); ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(GENERAL_DESCRIPTION); ?> (Chinese)</th>
            <td style="font-size: 12px;"><?php echo nl2br($this->data['TravelPackage']['description_cn']); ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Term and Conditions"); ?> (Khmer)</th>
            <td style="font-size: 12px;"><?php echo nl2br($this->data['TravelPackage']['term_condition_kh']); ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Term and Conditions"); ?> (English)</th>
            <td style="font-size: 12px;"><?php echo nl2br($this->data['TravelPackage']['term_condition']); ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Term and Conditions"); ?> (Chinese)</th>
            <td style="font-size: 12px;"><?php echo nl2br($this->data['TravelPackage']['term_condition_cn']); ?></td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 12px; font-weight: bold;">Apply Discount Condition</td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Buva Sea"); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['TravelPackage']['buva_sea'], 0); ?> %</td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 12px; font-weight: bold;">International Route</td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(" - Thailand"); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['TravelPackage']['international_thai'], 0); ?> %</td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(" - Vietname"); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['TravelPackage']['international_viet'], 0); ?> %</td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(" - Loas"); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['TravelPackage']['international_laos'], 0); ?> %</td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __("Local Route"); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['TravelPackage']['local'], 0); ?> %</td>
        </tr>
    </table>
</fieldset>
<br/>
<fieldset>
    <legend><?php __("Other Phto"); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <td valign="top">
                <?php
                $sqlOtherPhoto = mysql_query("SELECT * FROM travel_package_photos WHERE travel_package_id = ".$this->data['TravelPackage']['id']);
                while($rowOtherPhoto = mysql_fetch_array($sqlOtherPhoto)){
                ?>
                <div style="float: left; width: 100px; margin-left: 3px; margin-bottom: 3px;">
                    <img src="<?php echo $rowOtherPhoto['photo_path'].$rowOtherPhoto['photo']; ?>" style="width: 100px; height: 65px;" />
                </div>
                <?php
                }
                ?>
            </td>
        </tr>
    </table>
</fieldset>