<?php
include("includes/function.php");

$sqlSym = mysql_query("SELECT symbol FROM currency_centers WHERE id = (SELECT currency_center_id FROM branches WHERE id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].") LIMIT 1);");
$rowSym = mysql_fetch_array($sqlSym);
$symbol = $rowSym[0];

$destFrom = "";
$destTo   = "";
$sqlDest = mysql_query("SELECT id, name FROM t_destinations WHERE id IN (".$this->data['TJourneyPricePeriod']['destination_from_id'].", ".$this->data['TJourneyPricePeriod']['destination_to_id'].")");
while($rowDest = mysql_fetch_array($sqlDest)){
    if($rowDest['id'] == $this->data['TJourneyPricePeriod']['destination_from_id']){
        $destFrom = $rowDest['name'];
    } else {
        $destTo = $rowDest['name'];
    }
}
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackTJourneyPricePeriod").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTJourneyPricePeriod.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTJourneyPricePeriod">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_SET_PRICE_PERIOD_INFO); ?></legend>
    <table width="100%" class="info">
        <tr>
            <th><?php __(TABLE_APPLY_TO); ?></th>
            <td>
                <?php 
                if($this->data['TJourneyPricePeriod']['apply_to'] == 1){
                    echo TABLE_ALL;
                } else {
                    echo MENU_MAIN_BRANCH;
                }
                ?>
            </td>
        </tr>
        <?php
        if($this->data['TJourneyPricePeriod']['apply_to'] == 2){
        ?>
        <tr>
            <th><?php __(MENU_MAIN_BRANCH); ?></th>
            <td><?php echo $this->data['MainBranch']['name']; ?></td>
        </tr>
        <?php
        }
        ?>
        <tr>
            <th><?php __(GENERAL_DESCRIPTION); ?></th>
            <td><?php echo $this->data['TJourneyPricePeriod']['name']; ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_DESTINATION_FROM); ?></th>
            <td><?php echo $destFrom; ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_DESTINATION_TO); ?></th>
            <td><?php echo $destTo; ?></td>
        </tr>
        <tr>
            <th><?php __(MENU_TRANSPORTATION_TYPE); ?></th>
            <td><?php echo $this->data['TTransportationType']['name']; ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_START_DATE); ?></th>
            <td><?php echo dateShort($this->data['TJourneyPricePeriod']['start']); ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_END_DATE); ?></th>
            <td><?php echo dateShort($this->data['TJourneyPricePeriod']['end']); ?></td>
        </tr>
        <tr>
            <th><?php __(MENU_TRANSPORTATION_TYPE); ?></th>
            <td><?php echo $this->data['TTransportationType']['name']; ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_PRICE_TYPE); ?></th>
            <td>
                <?php 
                if($this->data['TTransportationType']['price_type'] == 1){
                    echo TABLE_FIX_AMOUNT;
                } else if($this->data['TTransportationType']['price_type'] == 2){
                    echo TABLE_MARKUP_AMOUNT;
                }
                ?>
            </td>
        </tr>
        <tr>
            <th><?php __("Selling Price ".TABLE_NORMAL); ?></th>
            <td><?php echo number_format($this->data['TJourneyPricePeriod']['price'], 2); ?> <?php echo $symbol; ?></td>
        </tr>
        <tr>
            <th><?php __("Selling Price ".TABLE_FOREIGNER); ?></th>
            <td><?php echo number_format($this->data['TJourneyPricePeriod']['foreigner_price'], 2); ?> <?php echo $symbol; ?></td>
        </tr>
        <tr>
            <th><?php __("Selling Price VIP Card"); ?></th>
            <td><?php echo number_format($this->data['TJourneyPricePeriod']['membership'], 2); ?> <?php echo $symbol; ?></td>
        </tr>
        <tr>
            <th><?php __("Agency Price (Khmer)"); ?></th>
            <td><?php echo number_format($this->data['TJourneyPricePeriod']['agency_price'], 2); ?> <?php echo $symbol; ?></td>
        </tr>
        <tr>
            <th><?php __("Agency Price (Foreigner)"); ?></th>
            <td><?php echo number_format($this->data['TJourneyPricePeriod']['agency_price_foreigner'], 2); ?> <?php echo $symbol; ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_CREATED); ?></th>
            <td><?php echo dateShort($this->data['TJourneyPricePeriod']['created'], "d/m/Y H:i:s"); ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_CREATED_BY); ?></th>
            <td><?php echo $this->data['User']['username']; ?></td>
        </tr>
    </table>
</fieldset>