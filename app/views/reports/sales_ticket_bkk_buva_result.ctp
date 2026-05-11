<?php
include('includes/function.php');
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
/**
 * export to excel
 */
$filename = "public/report/sales_ticket_bkk_buva" . $user['User']['id'] . ".csv";
$fp = fopen($filename,"wb");
$excelContent = REPORT_SALES_TICKET_BRANCH. " (Bkk & Buva Sea)\n\n";
$excelContent .= "\n".TABLE_NO."\t".TABLE_TICKET_CODE."\t".TABLE_BOOKING_DATE."\t".TABLE_JOURNEY_DATE."\t".REPORT_FROM."\t".REPORT_TO."\t".TABLE_SEAT."\t".TABLE_FARE."\t".TABLE_CREATED;
$msg = '<b style="font-size: 18px;">' . REPORT_SALES_TICKET_BRANCH . ' (Bkk & Buva Sea)</b><br /><br />';
$condition = "t_tickets.status > 0 AND t_tickets.offline_project_id = 1";
if($_POST['booking_from'] != '') {
    $msg .= TABLE_BOOKING_FROM.': '.$_POST['booking_from'];
    $condition .= " AND t_tickets.date >= '".dateConvert($_POST['booking_from'])."'";
}
if($_POST['booking_to'] != '') {
    $msg .= ' '.TABLE_BOOKING_TO.': '.$_POST['booking_to'];
    $condition .= " AND t_tickets.date <= '".dateConvert($_POST['booking_to'])."'";
}
$msg .= "<br/>";
if($_POST['traveling_from'] != '') {
    $msg .= TABLE_TRAVELING_FROM.': '.$_POST['traveling_from'];
    $condition .= " AND t_tickets.journey_date >= '".dateConvert($_POST['traveling_from'])."'";
}
if($_POST['traveling_to'] != '') {
    $msg .= ' '.TABLE_TRAVELING_TO.': '.$_POST['traveling_to'];
    $condition .= " AND t_tickets.journey_date <= '".dateConvert($_POST['traveling_to'])."'";
}
if($_POST['destination_from'] != '') {
    $sqlDesFrom = mysql_query("SELECT name FROM t_destinations WHERE id = ".$_POST['destination_from']);
    $rowDesFrom = mysql_fetch_array($sqlDesFrom);
    $msg .= '<br/>'.TABLE_DESTINATION_FROM.': '.$rowDesFrom[0];
    $condition .= " AND t_tickets.t_destination_from_id IN (".$_POST['destination_from'].")";
} else {
    $condition .= " AND t_tickets.t_destination_from_id IN (4, 26, 32)";
}
if($_POST['destination_to'] != '') {
    $sqlDesTo = mysql_query("SELECT name FROM t_destinations WHERE id = ".$_POST['destination_to']);
    $rowDesTo = mysql_fetch_array($sqlDesTo);
    $msg .= '<br/>'.TABLE_DESTINATION_TO.': '.$rowDesTo[0];
    $condition .= " AND t_tickets.t_destination_to_id = ".$_POST['destination_to'];
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
            window.open("<?php echo $this->webroot; ?>public/report/sales_ticket_bkk_buva<?php echo $user['User']['id']; ?>.csv", "_blank");
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
                                t_tickets.code AS ticket_code,
                                t_tickets.date AS ticket_date,
                                CONCAT_WS(' ', t_tickets.journey_date,t_tickets.journey_time) AS travel_date,
                                (SELECT name FROM t_destinations WHERE id = t_tickets.t_destination_from_id) AS dest_from,
                                (SELECT name FROM t_destinations WHERE id = t_tickets.t_destination_to_id) AS dest_to,
                                (SELECT GROUP_CONCAT(label_number) FROM t_ticket_details WHERE t_ticket_id = t_tickets.id AND is_active = 1) AS seat_num,
                                (SELECT COUNT(id) FROM t_ticket_details WHERE t_ticket_id = t_tickets.id AND is_active = 1) AS total_seat,
                                IFNULL(t_tickets.total_amount, 0) + IFNULL(t_tickets.total_vat, 0) - IFNULL(t_tickets.discount_amount, 0) AS fare,
                                t_tickets.created,
                                t_tickets.status,
                                currency_centers.symbol
                                FROM t_tickets 
                                INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id
                                WHERE ".$condition);
    while($rowTicket = mysql_fetch_array($sqlTicket)){
        $records[$totalBooked]['ticket_code']    = $rowTicket['ticket_code'];
        $records[$totalBooked]['ticket_date']    = $rowTicket['ticket_date'];
        $records[$totalBooked]['travel_date']    = $rowTicket['travel_date'];
        $records[$totalBooked]['dest_from']      = $rowTicket['dest_from'];
        $records[$totalBooked]['dest_to']        = $rowTicket['dest_to'];
        $records[$totalBooked]['seat_num']       = $rowTicket['seat_num'];
        $records[$totalBooked]['fare']           = $rowTicket['fare'];
        $records[$totalBooked]['created']        = $rowTicket['created'];
        $records[$totalBooked]['status']         = $rowTicket['status'];
        $totalSeat   += $rowTicket['total_seat'];
        $totalAmount += $rowTicket['fare'];
        $totalBooked++;
    }
    ?>
    <table cellpadding="5" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="font-size: 14px; width: 110px;"><?php echo TABLE_TOTAL_BOOKED; ?>:</td>
            <td style="width: 150px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalBooked, 0); ?></td>
            <td style="font-size: 14px; width: 90px;"><?php echo TABLE_TOTAL_SEAT; ?>:</td>
            <td style="width: 150px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalSeat, 0); ?></td>
            <td style="font-size: 14px; width: 90px;"><?php echo TABLE_TOTAL_FARE; ?>:</td>
            <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($totalAmount, 2); ?> $</td>
        </tr>
    </table>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" style="width: 120%;">
            <thead>
                <tr>
                    <th style="font-size: 10px; width: 35px;" class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 120px !important; font-size: 10px;"><?php echo TABLE_TICKET_CODE; ?></th>
                    <th style="width: 110px !important; font-size: 10px;"><?php echo TABLE_BOOKING_DATE; ?></th>
                    <th style="width: 130px !important; font-size: 10px;"><?php echo TABLE_JOURNEY_DATE; ?></th>
                    <th style="width: 110px !important; font-size: 10px; text-align: center;"><?php echo REPORT_FROM; ?></th>
                    <th style="width: 110px !important; font-size: 10px; text-align: center;"><?php echo REPORT_TO; ?></th>
                    <th style="width: 100px !important; font-size: 10px; text-align: center;"><?php echo TABLE_SEAT; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo TABLE_FARE; ?></th>
                    <th style="width: 130px !important; font-size: 10px; text-align: center;"><?php echo TABLE_CREATED; ?></th>
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
                        echo number_format($record['fare'], 2); 
                        $excelContent .= "\t" . $record['fare']; 
                        ?>
                    </td>
                    <td style="font-size: 10px;">
                        <?php
                        echo dateShort($record['created'], "d/m/Y H:i:s"); 
                        $excelContent .= "\t" . dateShort($record['created'], "d/m/Y H:i:s"); 
                        ?>
                    </td>
                </tr>
                <?php
                    }
                } else {
                    $excelContent .= "\n".GENERAL_NO_RECORD;
                ?>
                <tr>
                    <td colspan="9" class="dataTables_empty first"><?php echo GENERAL_NO_RECORD; ?></td>
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