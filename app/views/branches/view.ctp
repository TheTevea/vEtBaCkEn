<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackBranch").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableBranch.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackBranch">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php
$sqlInfo = mysql_query("SELECT currency_centers.name AS currency, countries.name AS country_name,  provinces.name AS province_name
                        FROM branches 
                        INNER JOIN currency_centers ON currency_centers.id = branches.currency_center_id 
                        INNER JOIN countries ON countries.id = branches.country_id 
                        LEFT JOIN provinces ON provinces.id = branches.province_id 
                        WHERE branches.id = ".$this->data['Branch']['id']);
$rowInfo = mysql_fetch_array($sqlInfo);
?>
<fieldset>
    <legend><?php __(MENU_BRANCH_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_NAME); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['name']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_TELEPHONE); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['telephone']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_BASE_CURRENCY); ?> :</th>
            <td style="font-size: 12px;"><?php echo $rowInfo['currency']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_COUNTRY); ?> :</th>
            <td style="font-size: 12px;"><?php echo $rowInfo['country_name']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(MENU_PROVINCE_MANAGEMENT); ?> :</th>
            <td style="font-size: 12px;"><?php echo $rowInfo['province_name']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_LONG); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['longs']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_LAT); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['lats']; ?></td>
        </tr>
    </table>
</fieldset>