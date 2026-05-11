<?php
include('includes/function.php');
$sqlRiel = mysql_query("SELECT symbol FROM currency_centers WHERE id = 1");
$rowRiel = mysql_fetch_array($sqlRiel);
$totalRecord     = 0;
$records         = array();
$sqlTicket = mysql_query("SELECT agency_balances.module, DATE(agency_balances.created) AS date, agency_balances.reference, IF(agency_balances.credit>0,agency_balances.credit,agency_balances.debit*-1) AS amount
                          FROM agency_balances
                          LEFT JOIN t_tickets ON t_tickets.id = agency_balances.t_ticket_id 
                          WHERE agency_balances.t_agency_id = ".$_POST['branch']." AND DATE(agency_balances.created) >= '".dateConvert($_POST['date_from']) ."' AND DATE(agency_balances.created) <= '".dateConvert($_POST['date_to']) ."'
                          ORDER BY agency_balances.created DESC");
while($rowTicket = mysql_fetch_array($sqlTicket)){
    $records[$totalRecord]['date'] = $rowTicket['date'];
    $records[$totalRecord]['code'] = $rowTicket['reference'];
    $records[$totalRecord]['desc'] = $rowTicket['module'];
    $records[$totalRecord]['amt']  = $rowTicket['amount'];
    $totalRecord++;
}

$sqlBalance = mysql_query("SELECT IFNULL((SELECT SUM(credit - debit) FROM `agency_balances` WHERE t_agency_id = ".$_POST['branch']."), 0)");
$rowBalance = mysql_fetch_array($sqlBalance);
$agentBalance = $rowBalance[0];

$tableName = "agency_balance_" . $userId;
mysql_query("DROP TABLE `".$tableName."`;");
mysql_query("SET max_heap_table_size = 1024*1024*1024");
mysql_query("CREATE TABLE IF NOT EXISTS `$tableName` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `debit` double DEFAULT NULL,
                  `credit` double DEFAULT NULL,
                  `t_agency_id` bigint(20) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `t_agency_id` (`t_agency_id`)
                ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
mysql_query("TRUNCATE $tableName");

$glCondition = "1";
$glCondition != '' ? $glCondition .= ' AND ' : '';
$glCondition .= '"' . dateConvert($_POST['date_from']) . '" > DATE(created)';
$glCondition != '' ? $glCondition .= ' AND ' : '';
$glCondition .= 't_agency_id=' . $_POST['branch'];
mysql_query("INSERT INTO `".$tableName."` (t_agency_id, debit, credit) SELECT t_agency_id, SUM(debit), SUM(credit) FROM agency_balances WHERE ".$glCondition." GROUP BY t_agency_id");

$sqlAgent = mysql_query("SELECT * FROM t_agents WHERE id = ".$_POST['branch']);
$rowAgent = mysql_fetch_array($sqlAgent);

$agentName    = $rowAgent['name'];
$agentPayment = "Postpaid";
if($rowAgent['payment'] == 1){
    $agentPayment = "Prepaid";
}
if($rowAgent['commission_type'] == 1){
    $agentPayment .= " (Commission %)";
} else if($rowAgent['commission_type'] == 2){
    $agentPayment .= " (Net Price)";
} else {
    $agentPayment .= " (Commission Fixed Amount)";
}
$agentType = $agentPayment;
?>
<script type="text/javascript">
    $(document).ready(function(){
        var tabHeight = $(window).height() - ($("#headReport").height() + 140);
        $("#contentReport").css("min-height", tabHeight);
    });
</script>
<br />
<table style="width: 100%;">
    <tr>
        <td style="font-size: 14px; font-weight: bold; width: 100px; text-align: left;">ឈ្មោះ</td>
        <td style="font-size: 14px; font-weight: bold; width: 2%;">:</td>
        <td style="font-size: 14px; font-weight: bold;"><?php echo $agentName; ?></td>
    </tr>
    <tr>
        <td style="font-size: 14px; font-weight: bold; text-align: left;">ទឹកប្រាក់សមតុល្យ</td>
        <td style="font-size: 14px; font-weight: bold;">:</td>
        <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($agentBalance, 2)." ".$rowRiel[0]; ?></td>
    </tr>
    <tr>
        <td style="font-size: 14px; font-weight: bold; text-align: left;">ប្រភេទភ្នាក់ងារ</td>
        <td style="font-size: 14px; font-weight: bold;">:</td>
        <td style="font-size: 14px; font-weight: bold;"><?php echo $agentType; ?></td>
    </tr>
</table>
<br />
<div id="contentReport" style="width: 100%; margin: 0px auto; overflow: auto; height: 200px;">
    <table class="table_print" cellspacing="0" style="width: 99%; margin: 0px auto;">
        <thead>
            <tr>
                <th class="first" style="width: 25px; font-size: 9px; font-weight: bold; padding: 5px;">ល.រ</th>
                <th style="width: 40px; font-size: 9px; font-weight: bold; padding: 5px; text-align: left;">ថ្ងៃខែ</th>
                <th style="font-size: 9px; font-weight: bold; padding: 5px; text-align: left;">បរិយាយ</th>
                <th style="width: 100px; font-size: 9px; font-weight: bold; padding: 5px;">លេខកូដ</th>
                <th style="width: 80px; font-size: 9px; font-weight: bold; padding: 5px; text-align: right;">សមតុល្យ</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $index = 0;
            foreach($records AS $data){
            ?>
            <tr>
                <td class="first" style="font-size: 9px; padding: 5px;"><?php echo ++$index; ?></td>
                <td style="font-size: 9px; padding: 5px;"><?php echo dateShort($data['date'], "d/m/Y"); ?></td>
                <td style="font-size: 9px; padding: 5px; text-align: left;"><?php echo $data['desc']; ?></td>
                <td style="font-size: 9px; padding: 5px; text-align: left;"><?php echo $data['code']; ?></td>
                <td style="font-size: 9px; padding: 5px; text-align: right;"><?php echo number_format($data['amt'], 2)." ".$rowRiel[0]; ?></td>
            </tr>
            <?php
            }
            ?>
            <?php
            // Balance Forward
            $sqlBg = mysql_query("SELECT SUM(credit - debit) FROM `".$tableName."` WHERE t_agency_id = ".$_POST['branch']);
            if(mysql_num_rows($sqlBg)){
                $rowBg = mysql_fetch_array($sqlBg);
            ?>
            <tr>
                <td class="first" style="font-size: 9px; padding: 5px;" colspan="2"></td>
                <td style="font-size: 9px; padding: 5px; text-align: left;"><?php echo "Balance Forward"; ?></td>
                <td></td>
                <td style="font-size: 9px; padding: 5px; text-align: right;"><?php echo number_format($rowBg[0], 2)." ".$rowRiel[0]; ?></td>
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
</div>
<div style="clear: both;"></div>
<br />