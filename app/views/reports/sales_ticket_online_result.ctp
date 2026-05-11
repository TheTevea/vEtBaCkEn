<?php
include('includes/function.php');
$rnd = rand();
$oTable = "oTable" . $rnd;
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$msg = '<b style="font-size: 18px;">' . REPORT_SALES_TICKET_ONLINE . '</b><br /><br />';
if($_POST['status']!='') {
    $condition = "t_tickets.status = ".$_POST['status']." AND (t_tickets.type = 5 OR t_tickets.type = 10 OR t_tickets.type = 11 OR (t_tickets.type = 2 AND t_tickets.api_bank_ref != ''))";
} else {
    $condition = "t_tickets.status >= 0 AND (t_tickets.type = 5 OR t_tickets.type = 10 OR t_tickets.type = 11 OR (t_tickets.type = 2 AND t_tickets.api_bank_ref != ''))";
}
$condition .= " AND t_tickets.offline_project_id = ".$user['User']['offline_project_id'];
if($_POST['booking_from'] !='' ) {
    $msg .= TABLE_BOOKING_FROM.': '.$_POST['booking_from'];
    $condition .= " AND DATE(IF(t_tickets.api_bank_ref!='',t_tickets.pay_date,t_tickets.date)) >= '".dateConvert($_POST['booking_from'])."'";
}
if($_POST['booking_to'] !='' ) {
    $msg .= ' '.TABLE_BOOKING_TO.': '.$_POST['booking_to'];
    $condition .= " AND DATE(IF(t_tickets.api_bank_ref!='',t_tickets.pay_date,t_tickets.date)) <= '".dateConvert($_POST['booking_to'])."'";
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
if($_POST['type'] != '') {
    $typeMulti = explode(",", $_POST['type']);
    if(!empty($typeMulti[1])){
        $condition .= " AND (";
        foreach($typeMulti AS $key => $type){
            if($key > 0){
                $condition .= ' OR ';
            }
            if($type == "1"){ // App
                $condition .= "(t_tickets.t_agent_id IS NULL AND t_tickets.terminal_id IS NULL)";
            } else if($type == "2"){ // Website
                $condition .= "(t_tickets.t_agent_id = 55)";
            } else if($type == "3") { // Mini App
                $condition .= "(t_tickets.t_agent_id = 106)";
            } else if($type == "5") { // Phone Call Payment
                $condition .= "(t_tickets.api_bank_ref != '')";
            } else { // Terminal
                $condition .= "(t_tickets.terminal_id IS NOT NULL)";
            }
        }
        $condition .= ')';
    } else {
        if($_POST['type'] == 1){ // Mobile
            $condition .= " AND t_tickets.t_agent_id IS NULL AND t_tickets.terminal_id IS NULL";
        } else if($_POST['type'] == 2) { // Website
            $condition .= " AND t_tickets.t_agent_id = 55";
        } else if($_POST['type'] == 3){ // Mini App
            $condition .= " AND t_tickets.t_agent_id = 106";
        } else if($_POST['type'] == 5) { // Phone Call Payment
            $condition .= " AND t_tickets.api_bank_ref != ''";
        } else {
            $condition .= " AND t_tickets.terminal_id IS NOT NULL";
        }
    }
}
if($_POST['payment_method']!='') {
    $condition .= " AND IFNULL(t_tickets.payment_method_id, online_orders.payment_method_id) IN (".$_POST['payment_method'].")";
}
if($_POST['company']!='') {
    $condition .= " AND t_tickets.company_id IN (".$_POST['company'].")";
}
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var <?php echo $oTable; ?>;
    $(document).ready(function(){
        <?php echo $oTable; ?> = $("#<?php echo $tblName; ?>").dataTable({
            "aLengthMenu": [[50, 100, 500, 1000, 5000, 10000, 1000000*1000000], [50, 100, 500, 1000, 5000, 10000, "All"]],
            "iDisplayLength": 1000000*1000000,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/salesTicketOnlineAjax/<?php echo str_replace("/", "|||", implode(',', str_replace(",", "-", $_POST))); ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?>_length, #<?php echo $tblName; ?>_filter, #<?php echo $tblName; ?>_info, #<?php echo $tblName; ?>_paginate").hide();
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td").css("font-size", "11px");
                $("#<?php echo $tblName; ?> td:first-child").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td:nth-child(8)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(9)").css("text-align", "center");
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0, -1, -2, -3, -4, -5, -6, -7, -8, -9 ]
            }],
            "aaSorting": [[ 2, "asc" ]]
        });
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
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $totalAmount = 0;
    $totalCommission = 0;
    $totalBooked = 0;
    $totalMarkup = 0;
    $sqlTicket = mysql_query("SELECT t_tickets.id, (IFNULL(t_tickets.total_amount, 0) + IFNULL(t_tickets.total_vat, 0) - IFNULL(t_tickets.discount_amount, 0)) AS total_amount, t_tickets.t_agent_id, t_tickets.t_journey_id, IFNULL(t_tickets.total_markup, 0) AS total_markup, IFNULL(t_tickets.commission, 0) AS commission 
                              FROM t_tickets 
                              LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id
                              WHERE ".$condition);
    while($rowTicket = mysql_fetch_array($sqlTicket)){
        $totalAmount += $rowTicket['total_amount'];
        $totalBooked++;
    }
    $sqlSeat = mysql_query("SELECT COUNT(t_ticket_details.id) FROM t_ticket_details 
                            INNER JOIN t_tickets ON t_tickets.id = t_ticket_details.t_ticket_id 
                            LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id
                            WHERE ".$condition." AND t_ticket_details.is_active = 1");
    $rowSeat = mysql_fetch_array($sqlSeat);
    ?>
    <table cellpadding="5" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="font-size: 14px; width: 100px;"><?php echo TABLE_TOTAL_BOOKED; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalBooked, 0); ?></td>
            <td style="font-size: 14px; width: 100px;"><?php echo TABLE_TOTAL_SEAT; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($rowSeat[0], 0); ?></td>
            <td style="font-size: 14px; width: 80px;"><?php echo TABLE_TOTAL_FARE; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalAmount, 2); ?> $</td>
        </tr>
    </table>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="font-size: 10px; width: 35px;" class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 120px !important; font-size: 10px;"><?php echo "Transaction ID"; ?></th>
                    <th style="width: 120px !important; font-size: 10px;"><?php echo TABLE_TICKET_CODE; ?></th>
                    <th style="width: 110px !important; font-size: 10px;"><?php echo TABLE_BOOKING_DATE; ?></th>
                    <th style="width: 130px !important; font-size: 10px;"><?php echo TABLE_JOURNEY_DATE; ?></th>
                    <th style="width: 110px !important; font-size: 10px;"><?php echo REPORT_FROM; ?></th>
                    <th style="width: 110px !important; font-size: 10px;"><?php echo REPORT_TO; ?></th>
                    <th style="width: 100px !important; font-size: 10px; text-align: center;"><?php echo TABLE_SEAT; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo TABLE_FARE; ?></th>
                    <th style="width: 130px !important; font-size: 10px;"><?php echo TABLE_TELEPHONE; ?></th>
                    <th style="width: 130px !important; font-size: 10px;"><?php echo "Payment Type"; ?></th>
                    <th style="width: 130px !important; font-size: 10px;"><?php echo TABLE_TYPE; ?></th>
                    <th style="width: 130px !important; font-size: 10px;"><?php echo TABLE_CREATED; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="15" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
                </tr>
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
<div style="clear: both;"></div>