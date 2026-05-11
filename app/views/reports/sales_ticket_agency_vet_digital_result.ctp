<?php
include('includes/function.php');
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
/**
 * export to excel
 */
$filename = "public/report/sales_ticket_agency_vet_digital" . $user['User']['id'] . ".csv";
$fp = fopen($filename,"wb");
$excelContent = REPORT_SALES_TICKET_AGENCY_ONLINE. " (VET Digital)\n\n";
$excelContent .= "\n".TABLE_NO."\t".MENU_AGENT."\tTransaction ID\t".TABLE_REFERENCE."\tBank Ref\t".TABLE_TICKET_CODE."\t".TABLE_BOOKING_DATE."\t".TABLE_JOURNEY_DATE."\t".REPORT_FROM."\t".REPORT_TO."\t".TABLE_SEAT."\t".TABLE_FARE."\t".TABLE_COMMISSION."\t".TABLE_CREATED."\t".TABLE_STATUS;
$msg = '<b style="font-size: 18px;">' . REPORT_SALES_TICKET_AGENCY_ONLINE . ' (VET Digital)</b><br /><br />';
if($_POST['status'] != '') {
    $condition = " t_tickets.status = ".$_POST['status'];
} else {
    $condition = " t_tickets.status >= 0";
}
$condition .= " AND t_tickets.offline_project_id = 1";
if($_POST['booking_from'] !='' ) {
    $msg .= TABLE_BOOKING_FROM.': '.$_POST['booking_from'];
    $condition .= " AND t_tickets.date >= '".dateConvert($_POST['booking_from'])."'";
}
if($_POST['booking_to'] !='' ) {
    $msg .= ' '.TABLE_BOOKING_TO.': '.$_POST['booking_to'];
    $condition .= " AND t_tickets.date <= '".dateConvert($_POST['booking_to'])."'";
}
$msg .= "<br/>";
if($_POST['traveling_from'] != '') {
    $msg .= TABLE_TRAVELING_FROM.': '.$_POST['traveling_from'];
    $condition .= " AND t_tickets.journey_date >= '".dateConvert($_POST['traveling_from'])."'";
}
if($_POST['traveling_to']!='') {
    $msg .= ' '.TABLE_TRAVELING_TO.': '.$_POST['traveling_to'];
    $condition .= " AND t_tickets.journey_date <= '".dateConvert($_POST['traveling_to'])."'";
}
if($_POST['destination_from']!='') {
    $sqlDesFrom = mysql_query("SELECT name FROM t_destinations WHERE id = ".$_POST['destination_from']);
    $rowDesFrom = mysql_fetch_array($sqlDesFrom);
    $msg .= '<br/>'.TABLE_DESTINATION_FROM.': '.$rowDesFrom[0];
    $condition .= " AND t_tickets.t_destination_from_id = ".$_POST['destination_from'];
}
if($_POST['destination_to']!='') {
    $sqlDesTo = mysql_query("SELECT name FROM t_destinations WHERE id = ".$_POST['destination_to']);
    $rowDesTo = mysql_fetch_array($sqlDesTo);
    $msg .= '<br/>'.TABLE_DESTINATION_TO.': '.$rowDesTo[0];
    $condition .= " AND t_tickets.t_destination_to_id = ".$_POST['destination_to'];
}
if($_POST['company']!='') {
    $sqlCom = mysql_query("SELECT GROUP_CONCAT(name) FROM companies WHERE id IN (".$_POST['company'].")");
    $rowCom = mysql_fetch_array($sqlCom);
    $msg .= '<br/>'.MENU_COMPANY_MANAGEMENT.': '.$rowCom[0];
    $condition .= " AND t_tickets.company_id IN (".$_POST['company'].")";
}
if($_POST['book_type'] != ''){
    if($_POST['book_type'] == 1){ // VET Digital (API)
        $condition .= " AND t_tickets.t_agent_id = 47 AND t_tickets.online_order_id IS NOT NULL AND t_tickets.booking_type = 1";
    } else if($_POST['book_type'] == 2){ // VET APP (API)
        $condition .= " AND t_tickets.t_agent_id = 47 AND t_tickets.online_order_id IS NOT NULL AND t_tickets.booking_type = 2";
    } else if($_POST['book_type'] == 3){ // VET Digital (Manual) (user: BMB2022)
        $condition .= " AND t_tickets.t_agent_id = 86 AND t_tickets.online_order_id IS NULL";
    } else if($_POST['book_type'] == 4){ // VET APP (Manual) (user: VETApp)
        $condition .= " AND t_tickets.t_agent_id = 91 AND t_tickets.online_order_id IS NULL";
    }
} else {
    $condition .= " AND t_tickets.t_agent_id IN (47, 86, 91)";
}
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $btnPrint; ?>").click(function(){
            $(".dataTables_length").hide();
            $(".dataTables_filter").hide();
            $(".dataTables_paginate").hide();
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
            $(".dataTables_length").show();
            $(".dataTables_filter").show();
            $(".dataTables_paginate").show();
        });

        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/sales_ticket_agency_vet_digital<?php echo $user['User']['id']; ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $totalAmount = 0;
    $totalVat    = 0;
    $totalCommission = 0;
    $totalBooked = 0;
    $totalMarkup = 0;
    $totalSeat   = 0;
    $records     = array();
    $sqlTicket   = mysql_query("SELECT 
                                t_tickets.id,
                                t_tickets.t_agent_id,
                                t_tickets.agt_refer_code,
                                t_tickets.api_bank_ref,
                                t_tickets.code AS ticket_code,
                                t_tickets.date AS ticket_date, 
                                t_tickets.booking_type,
                                IFNULL(t_tickets.total_amount, 0) AS total_amount,
                                IFNULL(t_tickets.discount_amount, 0) AS discount_amount,
                                CONCAT_WS(' ', t_tickets.journey_date,t_tickets.journey_time) AS travel_date,
                                (SELECT name FROM t_destinations WHERE id = t_tickets.t_destination_from_id) AS dest_from,
                                (SELECT name FROM t_destinations WHERE id = t_tickets.t_destination_to_id) AS dest_to,
                                (SELECT GROUP_CONCAT(label_number) FROM t_ticket_details WHERE t_ticket_id = t_tickets.id AND is_active = 1) AS seat_num,
                                (SELECT COUNT(id) FROM t_ticket_details WHERE t_ticket_id = t_tickets.id AND is_active = 1) AS total_seat,
                                IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) AS fare,
                                IFNULL(t_tickets.total_vat, 0) AS total_vat,
                                IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) AS total,
                                t_tickets.created,
                                t_tickets.status,
                                t_tickets.t_journey_id,
                                t_agents.name AS agency_name,
                                t_agents.type AS agency_type,
                                t_agents.commission AS agency_commission,
                                t_agents.commission_type,
                                online_orders.code AS transaction_no,
                                t_tickets.online_order_id,
                                currency_centers.symbol
                                FROM t_tickets 
                                INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id 
                                INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id
                                LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id
                                WHERE ".$condition."
                                UNION ALL
                                SELECT 
                                t_tickets.id,
                                t_tickets.t_agent_id,
                                t_tickets.agt_refer_code,
                                t_tickets.api_bank_ref,
                                t_tickets.code AS ticket_code,
                                t_tickets.date AS ticket_date, 
                                t_tickets.booking_type,
                                IFNULL(t_tickets.total_amount, 0) AS total_amount,
                                IFNULL(t_tickets.discount_amount, 0) AS discount_amount,
                                CONCAT_WS(' ', t_tickets.journey_date,t_tickets.journey_time) AS travel_date,
                                (SELECT name FROM t_destinations WHERE id = t_tickets.t_destination_from_id) AS dest_from,
                                (SELECT name FROM t_destinations WHERE id = t_tickets.t_destination_to_id) AS dest_to,
                                (SELECT GROUP_CONCAT(label_number) FROM t_ticket_detail_3months WHERE t_ticket_id = t_tickets.id AND is_active = 1) AS seat_num,
                                (SELECT COUNT(id) FROM t_ticket_detail_3months WHERE t_ticket_id = t_tickets.id AND is_active = 1) AS total_seat,
                                IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) AS fare,
                                IFNULL(t_tickets.total_vat, 0) AS total_vat,
                                IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) AS total,
                                t_tickets.created,
                                t_tickets.status,
                                t_tickets.t_journey_id,
                                t_agents.name AS agency_name,
                                t_agents.type AS agency_type,
                                t_agents.commission AS agency_commission,
                                t_agents.commission_type,
                                online_orders.code AS transaction_no,
                                t_tickets.online_order_id,
                                currency_centers.symbol
                                FROM t_ticket_3months AS t_tickets 
                                INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id 
                                INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id
                                LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id
                                WHERE ".$condition."");
    while($rowTicket = mysql_fetch_array($sqlTicket)){
        $agencyName = $rowTicket['agency_name'];
        $discount = 0;
        if($rowTicket['t_agent_id'] == 47){
            if($rowTicket['booking_type'] == 2){
                // $sqlTicketTmp = mysql_query("SELECT discount_amount FROM `t_ticket_api_tmps` WHERE `online_order_id` = ".$rowTicket['online_order_id']);
                // if(mysql_num_rows($sqlTicketTmp)){
                //     $rowTicketTmp = mysql_fetch_array($sqlTicketTmp);
                //     $discount = $rowTicketTmp['discount_amount'];
                //     mysql_query("UPDATE t_tickets SET discount_amount = ".$discount." WHERE id = ".$rowTicket['id']);
                // }
                $agencyName = "VET APP (API)";
                // if($rowTicket['discount_amount'] == 0){
                //     $discount = ($rowTicket['total_amount'] * 5) / 100;
                //     mysql_query("UPDATE t_tickets SET discount_amount = ".$discount." WHERE id = ".$rowTicket['id']);
                // }
            }
        }
        $commission = 0;
        if($rowTicket['t_agent_id'] != 91){ // != VET APP
            if($rowTicket['agency_type'] == 3){ // API
                if($rowTicket['commission_type'] == 1){ // Commission (%)
                    $commission += (($rowTicket['fare'] + $rowTicket['total_vat'] - $discount) * $rowTicket['agency_commission']) / 100;
                } else {
                    $sqlJou = mysql_query("SELECT * FROM t_journeys WHERE id = ".$rowTicket['t_journey_id']);
                    $rowJou = mysql_fetch_array($sqlJou);
                    $commission += $rowJou['unit_price'] - $rowJou['agent_price_amount'];
                }
            }
        }
        $records[$totalBooked]['agency']         = $agencyName;
        $records[$totalBooked]['transaction_no'] = $rowTicket['transaction_no'];
        $records[$totalBooked]['reference']      = $rowTicket['agt_refer_code'];
        $records[$totalBooked]['api_bank_ref']   = $rowTicket['api_bank_ref'];
        $records[$totalBooked]['ticket_code']    = $rowTicket['ticket_code'];
        $records[$totalBooked]['ticket_date']    = $rowTicket['ticket_date'];
        $records[$totalBooked]['travel_date']    = $rowTicket['travel_date'];
        $records[$totalBooked]['dest_from']      = $rowTicket['dest_from'];
        $records[$totalBooked]['dest_to']        = $rowTicket['dest_to'];
        $records[$totalBooked]['seat_num']       = $rowTicket['seat_num'];
        $records[$totalBooked]['fare']           = $rowTicket['fare'] - $discount;
        $records[$totalBooked]['total_vat']      = $rowTicket['total_vat'];
        $records[$totalBooked]['total']          = $rowTicket['total'];
        $records[$totalBooked]['commission']     = $commission;
        $records[$totalBooked]['created']        = $rowTicket['created'];
        $records[$totalBooked]['status']         = $rowTicket['status'];
        $totalSeat   += $rowTicket['total_seat'];
        $totalAmount += $rowTicket['fare'];
        $totalVat    += $rowTicket['total_vat'];
        $totalCommission += $commission;
        $totalBooked++;
    }
    ?>
    <table cellpadding="5" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="font-size: 14px; width: 100px;"><?php echo TABLE_TOTAL_BOOKED; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalBooked, 0); ?></td>
            <td style="font-size: 14px; width: 100px;"><?php echo TABLE_TOTAL_SEAT; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalSeat, 0); ?></td>
            <td colspan="6"></td>
        </tr>
        <tr>
            <td style="font-size: 14px; width: 80px;"><?php echo TABLE_TOTAL_FARE; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalAmount + $totalVat, 2); ?> $</td>
            <td style="font-size: 14px; width: 130px;"><?php echo TABLE_TOTAL_COMMISSION; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalCommission, 2); ?> $</td>
            <td style="font-size: 14px; width: 100px;"><?php echo TABLE_NET_PAYMENT; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format((($totalAmount + $totalVat) - $totalCommission), 2); ?> $</td>
        </tr>
    </table>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" style="width: 120%;">
            <thead>
                <tr>
                    <th style="font-size: 10px; width: 35px;" class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 140px !important; font-size: 10px;"><?php echo MENU_AGENT; ?></th>
                    <th style="width: 120px !important; font-size: 10px;"><?php echo "Transaction ID"; ?></th>
                    <th style="width: 120px !important; font-size: 10px;"><?php echo TABLE_REFERENCE; ?></th>
                    <th style="width: 120px !important; font-size: 10px;"><?php echo "Bank Ref"; ?></th>
                    <th style="width: 120px !important; font-size: 10px;"><?php echo TABLE_TICKET_CODE; ?></th>
                    <th style="width: 110px !important; font-size: 10px;"><?php echo TABLE_BOOKING_DATE; ?></th>
                    <th style="width: 130px !important; font-size: 10px;"><?php echo TABLE_JOURNEY_DATE; ?></th>
                    <th style="width: 110px !important; font-size: 10px; text-align: center;"><?php echo REPORT_FROM; ?></th>
                    <th style="width: 110px !important; font-size: 10px; text-align: center;"><?php echo REPORT_TO; ?></th>
                    <th style="width: 100px !important; font-size: 10px; text-align: center;"><?php echo TABLE_SEAT; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo TABLE_FARE; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo TABLE_COMMISSION; ?></th>
                    <th style="width: 130px !important; font-size: 10px; text-align: center;"><?php echo TABLE_CREATED; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo TABLE_STATUS; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                if(!empty($records)){
                    foreach($records AS $record){
                ?>
                <tr>
                    <td style="font-size: 10px;" class="first">
                        <?php 
                        echo ++$i; 
                        $excelContent .= "\n" . $i;
                        ?>
                    </td>
                    <td style="font-size: 10px;">
                        <?php 
                        echo $record['agency']; 
                        $excelContent .= "\t" . $record['agency']; 
                        ?>
                    </td>
                    <td style="font-size: 10px;">
                        <?php 
                        echo $record['transaction_no']; 
                        $excelContent .= "\t" . $record['transaction_no']; 
                        ?>
                    </td>
                    <td style="font-size: 10px;">
                        <?php 
                        echo $record['reference']; 
                        $excelContent .= "\t" . $record['reference']; 
                        ?>
                    </td>
                    <td style="font-size: 10px;">
                        <?php 
                        echo $record['api_bank_ref']; 
                        $excelContent .= "\t" . $record['api_bank_ref']; 
                        ?>
                    </td>
                    <td style="font-size: 10px;">
                        <?php 
                        echo $record['ticket_code'];
                        $excelContent .= "\t" . $record['ticket_code'];  
                        ?>
                    </td>
                    <td style="font-size: 10px;">
                        <?php 
                        echo $record['ticket_date']; 
                        $excelContent .= "\t" . $record['ticket_date']; 
                        ?>
                    </td>
                    <td style="font-size: 10px;">
                        <?php 
                        echo $record['travel_date']; 
                        $excelContent .= "\t" . $record['travel_date']; 
                        ?>
                    </td>
                    <td style="font-size: 10px;">
                        <?php 
                        echo $record['dest_from']; 
                        $excelContent .= "\t" . $record['dest_from']; 
                        ?>
                    </td>
                    <td style="font-size: 10px;">
                        <?php 
                        echo $record['dest_to']; 
                        $excelContent .= "\t" . $record['dest_to']; 
                        ?>
                    </td>
                    <td style="font-size: 10px;">
                        <?php 
                        echo $record['seat_num']; 
                        $excelContent .= "\t" . $record['seat_num']; 
                        ?>
                    </td>
                    <td style="font-size: 10px;">
                        <?php 
                        echo number_format($record['fare'] + $record['total_vat'], 2); 
                        $excelContent .= "\t" . number_format(($record['fare'] + $record['total_vat']), 2); 
                        ?>
                    </td>
                    <td style="font-size: 10px;">
                        <?php 
                        echo number_format($record['commission'], 2); 
                        $excelContent .= "\t" . number_format($record['commission'], 2); 
                        ?>
                    </td>
                    <td style="font-size: 10px;">
                        <?php
                        echo dateShort($record['created'], "d/m/Y H:i:s"); 
                        $excelContent .= "\t" . dateShort($record['created'], "d/m/Y H:i:s"); 
                        ?>
                    </td>
                    <td style="font-size: 10px;">
                        <?php
                        if($record['status'] == 0){
                            echo "Void";
                            $excelContent .= "\tVoid"; 
                        } else {
                            echo "Completed";
                            $excelContent .= "\tCompleted"; 
                        }   
                        ?>
                    </td>
                </tr>
                <?php
                    }
                } else {
                    $excelContent .= "\n".GENERAL_NO_RECORD;
                ?>
                <tr>
                    <td colspan="15" class="dataTables_empty first"><?php echo GENERAL_NO_RECORD; ?></td>
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