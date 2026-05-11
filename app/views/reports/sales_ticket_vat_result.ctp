<?php
include('includes/function.php');
$rnd = rand();
$oTable    = "oTable" . $rnd;
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
$tblName   = "tbl" . rand(); 
$sqlSym = mysql_query("SELECT symbol FROM currency_centers WHERE id = 1;");
$rowSym = mysql_fetch_array($sqlSym);
$symbol = $rowSym[0];

/**
 * export to excel
 */
$filename = "public/report/sales_ticket_vat" . $user['User']['id'] . ".csv";
$fp = fopen($filename,"wb");
$excelContent = REPORT_SALES_TICKET_BRANCH. " (VAT)\n\n";
$excelContent .= "\n".TABLE_NO."\tReference Code\t".TABLE_TICKET_CODE."\t".TABLE_BOOKING_DATE."\t".TABLE_JOURNEY_DATE."\t".REPORT_FROM."\t".REPORT_TO."\t".TABLE_TOTAL_SEAT."\tTotal Amount\tType"."\t".TABLE_STATUS;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $btnPrint; ?>").click(function(){
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
        });

        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/sales_ticket_vat<?php echo $user['User']['id']; ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . REPORT_SALES_TICKET_BRANCH . ' (VAT)</b><br /><br />';
    $condition = "";
    if($_POST['booking_from'] !='' ) {
        $msg .= TABLE_BOOKING_FROM.': '.$_POST['booking_from'];
        $condition .= " AND date >= '".dateConvert($_POST['booking_from'])."'";
    }
    if($_POST['booking_to'] !='' ) {
        $msg .= ' '.TABLE_BOOKING_TO.': '.$_POST['booking_to'];
        $condition .= " AND date <= '".dateConvert($_POST['booking_to'])."'";
    }
    $msg .= "<br/>";
    if($_POST['traveling_from'] != '') {
        $msg .= TABLE_TRAVELING_FROM.': '.$_POST['traveling_from'];
        $condition .= " AND journey_date >= '".dateConvert($_POST['traveling_from'])."'";
    }
    if($_POST['traveling_to']!='') {
        $msg .= ' '.TABLE_TRAVELING_TO.': '.$_POST['traveling_to'];
        $condition .= " AND journey_date <= '".dateConvert($_POST['traveling_to'])."'";
    }
    if($_POST['company']!='') {
        $sqlCompany = mysql_query("SELECT GROUP_CONCAT(name) FROM companies WHERE id IN (".$_POST['company'].")");
        $rowCompany = mysql_fetch_array($sqlCompany);
        $msg .= '<br/>'.MENU_COMPANY_MANAGEMENT.': '.$rowCompany[0];
        $condition .= " AND company_id IN (".$_POST['company'].")";
    } else {
        $condition .= " AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = '" . $user['User']['id']. "')";
    }
    if($_POST['branch']!='') {
        $sqlBranch = mysql_query("SELECT name FROM branches WHERE id = ".$_POST['branch']);
        $rowBranch = mysql_fetch_array($sqlBranch);
        $msg .= '<br/>'.MENU_BRANCH.': '.$rowBranch[0];
        $condition .= " AND branch_id = ".$_POST['branch'];
    } else {
        $condition .= " AND branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '" . $user['User']['id']. "')";
    }
    if($_POST['destination_from']!='') {
        $sqlDesFrom = mysql_query("SELECT name FROM t_destinations WHERE id = ".$_POST['destination_from']);
        $rowDesFrom = mysql_fetch_array($sqlDesFrom);
        $msg .= '<br/>'.TABLE_DESTINATION_FROM.': '.$rowDesFrom[0];
        $condition .= " AND t_destination_from_id = ".$_POST['destination_from'];
    }
    if($_POST['destination_to']!='') {
        $sqlDesTo = mysql_query("SELECT name FROM t_destinations WHERE id = ".$_POST['destination_to']);
        $rowDesTo = mysql_fetch_array($sqlDesTo);
        $msg .= '<br/>'.TABLE_DESTINATION_TO.': '.$rowDesTo[0];
        $condition .= " AND t_destination_to_id = ".$_POST['destination_to'];
    }
    if($_POST['type']!='') {
        if($_POST['type'] == 1) {
            $condition .= " AND `type` != 5 AND `type` != 7";
        } else if($_POST['type'] == 2){
            $condition .= " AND `type` = 5 AND `t_agent_id` IS NOT NULL";
        } else if($_POST['type'] == 3){
            $condition .= " AND `type` = 5 AND `t_agent_id` IS NULL";
        } else if($_POST['type'] == 4){
            $condition .= " AND `type` = 7";
        }
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $totalAmount = 0;
    $totalVat    = 0;
    $grandTotal  = 0;
    $totalBooked = 0;
    $totalSeat   = 0;
    $records = array();
    $i = 0;
    $sqlTicket = mysql_query("SELECT *, IFNULL(vat_code, code) AS invoice_code, IF(company_id = 7, (CAST(SUBSTR(IFNULL(vat_code, code), 5, 9) AS SIGNED)), IF(company_id = 12, (CAST(SUBSTR(IFNULL(vat_code, code), 5, 9) AS SIGNED)), IF(company_id = 13, (CAST(SUBSTR(IFNULL(vat_code, code), 5, 9) AS SIGNED)), IF(company_id = 14, (CAST(SUBSTR(IFNULL(vat_code, code), 5, 9) AS SIGNED)), (CAST(SUBSTR(IFNULL(vat_code, code), 4, 9) AS SIGNED)))))) AS inv_code 
                              FROM t_tickets WHERE offline_project_id = 1 AND is_vat = 1".$condition." ORDER BY inv_code ASC");
    if(mysql_num_rows($sqlTicket)){
        while($rowTicket = mysql_fetch_array($sqlTicket)){
            $grandTotal  += $rowTicket['total_amount'] + $rowTicket['total_vat'];
            $records[$i]['ticket_code']  = $rowTicket['code'];
            $records[$i]['invoice_code'] = $rowTicket['invoice_code'];
            $records[$i]['date']         = $rowTicket['date'];
            $records[$i]['is_open_date'] = $rowTicket['is_open_date'];
            $records[$i]['journey_date'] = $rowTicket['journey_date'];
            $records[$i]['journey_time'] = $rowTicket['journey_time'];
            $records[$i]['total_seat']   = $rowTicket['total_seat'];
            $records[$i]['total_amount'] = $rowTicket['total_amount'];
            $records[$i]['total_vat']    = $rowTicket['total_vat'];
            $records[$i]['type']         = $rowTicket['type'];
            $records[$i]['t_agent_id']   = $rowTicket['t_agent_id'];
            $records[$i]['status']       = $rowTicket['status'];
            $records[$i]['t_destination_from_id'] = $rowTicket['t_destination_from_id'];
            $records[$i]['t_destination_to_id']   = $rowTicket['t_destination_to_id'];
            $totalSeat  += $rowTicket['total_seat'];
            $totalBooked++;
            $i++;
        }
    }
    ?>
    <div id="dynamic">
        <table cellpadding="5" cellspacing="0" style="width: 100%;">
            <tr>
                <td style="font-size: 14px; width: 100px;"><?php echo TABLE_TOTAL_BOOKED; ?>:</td>
                <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalBooked, 0); ?></td>
                <td style="font-size: 14px; width: 120px;"><?php echo TABLE_TOTAL_SEAT; ?>:</td>
                <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalSeat, 0); ?></td>
                <td style="font-size: 14px; width: 80px;"><?php echo TABLE_TOTAL_FARE; ?>:</td>
                <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($grandTotal, 2); ?> $</td>
            </tr>
        </table>
        <table id="<?php echo $tblName; ?>" class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="font-size: 10px; width: 35px;" class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 170px !important; font-size: 10px;"><?php echo "Reference Code"; ?></th>
                    <th style="width: 170px !important; font-size: 10px;"><?php echo TABLE_TICKET_CODE; ?></th>
                    <th style="width: 130px !important; font-size: 10px;"><?php echo TABLE_BOOKING_DATE; ?></th>
                    <th style="width: 160px !important; font-size: 10px;"><?php echo TABLE_JOURNEY_DATE; ?></th>
                    <th style="width: 110px !important; font-size: 10px;"><?php echo REPORT_FROM; ?></th>
                    <th style="width: 110px !important; font-size: 10px;"><?php echo REPORT_TO; ?></th>
                    <th style="width: 110px !important; font-size: 10px;"><?php echo TABLE_TOTAL_SEAT; ?></th>
                    <th style="width: 75px !important; font-size: 10px;"><?php echo "Total (VAT Included)"; ?></th>
                    <th style="width: 75px !important; font-size: 10px;"><?php echo "Type"; ?></th>
                    <th style="width: 75px !important; font-size: 10px;"><?php echo "Status"; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $index   = 0;
                if(!empty($records)){
                    foreach($records AS $rowTicket){
                        $destFrom = "";
                        $destTo   = "";
                        $sqlDes = mysql_query("SELECT * FROM t_destinations WHERE id IN (".$rowTicket['t_destination_from_id'].", ".$rowTicket['t_destination_to_id'].")");
                        while($rowDes = mysql_fetch_array($sqlDes)){
                            if($rowDes['id'] == $rowTicket['t_destination_from_id']){
                                $destFrom = $rowDes['name'];
                            } else {
                                $destTo = $rowDes['name'];
                            }
                        }
                ?>
                <tr>
                    <td style="padding: 5px; font-size: 12px; text-align: center;" class="first">
                        <?php 
                        echo ++$index; 
                        $excelContent .= "\n" . $index;
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowTicket['ticket_code']; 
                        $excelContent .= "\t" . $rowTicket['ticket_code']; 
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowTicket['invoice_code']; 
                        $excelContent .= "\t" . $rowTicket['invoice_code']; 
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo dateShort($rowTicket['date']); 
                        $excelContent .= "\t" . dateShort($rowTicket['date']); 
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        if($rowTicket['is_open_date'] == 1){
                            echo 'Open Date';
                            $excelContent .= "\tOpen Date"; 
                        } else {
                            echo dateShort($rowTicket['journey_date'])." ".date("h:i A", strtotime($rowTicket['journey_time']));
                            $excelContent .= "\t".dateShort($rowTicket['journey_date'])." ".date("h:i A", strtotime($rowTicket['journey_time'])); 
                        }
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $destFrom; 
                        $excelContent .= "\t" . $destFrom; 
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $destTo; 
                        $excelContent .= "\t" . $destTo; 
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowTicket['total_seat']; 
                        $excelContent .= "\t" . $rowTicket['total_seat'];
                        ?>
                    </td>
                    <td style="text-align: right; padding: 5px; font-size: 12px;">
                        <span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span>
                        <?php 
                        echo number_format($rowTicket['total_amount'] + $rowTicket['total_vat'], 2); 
                        $excelContent .= "\t" . number_format($rowTicket['total_amount'] + $rowTicket['total_vat'], 2);
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                            $type = "";
                            if($rowTicket['type'] == 5){
                                if(!empty($rowTicket['t_agent_id'])){
                                    $type = "APP";
                                } else {
                                    $type = "Website";
                                }
                            } else if($rowTicket['type'] == 7){
                                if(!empty($rowTicket['t_agent_id'])){
                                    $sqlAgen = mysql_query("SELECT * FROM t_agents WHERE id = ".$rowTicket['t_agent_id']);
                                    $rowAgen = mysql_fetch_array($sqlAgen);
                                    $type = $rowAgen['name'];
                                } else {
                                    $type = "Agency APi";
                                }
                            } else {
                                $type = "Walk In";
                            }
                            echo $type;
                            $excelContent .= "\t".$type;
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        if($rowTicket['status'] == 0){
                            if($rowTicket['type'] != 5 && $rowTicket['type'] != 7){
                                echo "Booked";
                                $excelContent .= "\tBooked";
                            } else {
                                echo "Void";
                                $excelContent .= "\tVoid";
                            }
                        } else {
                            echo "Booked";
                            $excelContent .= "\tBooked";
                        }
                        ?>
                    </td>
                </tr>
                <?php
                    }
                    $excelContent .= "\n\t\t\t\t\t\t\tTotal\t".number_format($grandTotal, 2);
                ?>
                <tr>
                    <td style="padding: 5px; font-size: 12px; text-align: right;" colspan="8" class="first">Total:</td>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($grandTotal, 2); ?></td>
                    <td colspan="2"></td>
                </tr>
                <?php
                } else {
                    $excelContent .= "\n".TABLE_LOADING;
                ?>
                <tr>
                    <td colspan="13" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
                </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<div style="clear: both;"></div>
<br />
<div class="buttons">
    <button type="button" id="<?php echo $btnPrint; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
        <?php echo ACTION_PRINT; ?>
    </button>
</div>
<div class="buttons">
    <button type="button" id="<?php echo $btnExport; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/csv.png" alt=""/>
        <?php echo ACTION_EXPORT_TO_EXCEL; ?>
    </button>
</div>
<div style="clear: both;"></div>
<?php
$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);
?>