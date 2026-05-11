<?php
include('includes/function.php');
$rnd = rand();
$oTable = "oTable" . $rnd;
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;

$msg = '<b style="font-size: 18px;">' . REPORT_SALES_TICKET_AGENCY_ONLINE . '</b><br /><br />';
if($_POST['status']!='') {
    $condition = "status = ".$_POST['status'];
} else {
    $condition = "status >= 0";
}
$condition .= " AND offline_project_id = ".$user['User']['offline_project_id'];
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
$isApi = 0;
if($_POST['agency']!='') {
    $sqlAgency = mysql_query("SELECT name, type FROM t_agents WHERE id = ".$_POST['agency']);
    $rowAgency = mysql_fetch_array($sqlAgency);
    $msg .= '<br/>'.MENU_AGENT.': '.$rowAgency[0];
    $condition .= " AND t_agent_id = ".$_POST['agency'];
    if($rowAgency['type'] == 3){
        $isApi = 1;
    }
} else {
    $condition .= " AND t_agent_id IS NOT NULL";
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
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/salesTicketAgencyOnlineAjax/<?php echo str_replace("/", "|||", implode(',', $_POST)); ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?>_length, #<?php echo $tblName; ?>_filter, #<?php echo $tblName; ?>_info, #<?php echo $tblName; ?>_paginate").hide();
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td").css("font-size", "11px");
                $("#<?php echo $tblName; ?> td:first-child").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td:nth-child(7)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(8)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(9)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(10)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(11)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(12)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(13)").css("text-align", "center");
                <?php if($isApi == 1){ ?>
                $("#<?php echo $tblName; ?> td:nth-child(13)").hide();
                <?php } ?>
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0, -1, -2, -3, -4, -5, -6, -7, -8, -9, -10, -12 ]
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

        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/sales_ticket_agency_online<?php echo $user['User']['id']; ?>.csv", "_blank");
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
    $sqlTicket = mysql_query("SELECT id, (IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0)) AS total_amount, total_vat, t_agent_id, t_journey_id, IFNULL(t_tickets.total_markup, 0) AS total_markup, IFNULL(t_tickets.commission, 0) AS commission FROM t_tickets WHERE ".$condition);
    while($rowTicket = mysql_fetch_array($sqlTicket)){
        $sqlAgency = mysql_query("SELECT * FROM t_agents WHERE id = ".$rowTicket['t_agent_id']);
        $rowAgency = mysql_fetch_array($sqlAgency);
        if($rowAgency['type'] == 3){ // APi
            if($rowAgency['commission_type'] == 1){
                $totalCommission += ($rowTicket['total_amount'] * $rowAgency['commission']) / 100;
            } else {
                $sqlJou = mysql_query("SELECT * FROM t_journeys WHERE id = ".$rowTicket['t_journey_id']);
                $rowJou = mysql_fetch_array($sqlJou);
                $totalCommission += $rowJou['unit_price'] - $rowJou['agent_price_amount'];
            }
        } else {
            $totalCommission += $rowTicket['commission'];
        }
        $totalAmount += $rowTicket['total_amount'];
        $totalVat    += $rowTicket['total_vat'];
        $totalMarkup += $rowTicket['total_markup'];
        $totalBooked++;
    }
    $sqlSeat = mysql_query("SELECT COUNT(t_ticket_details.id) FROM t_ticket_details INNER JOIN t_tickets ON t_tickets.id = t_ticket_details.t_ticket_id AND t_tickets.t_agent_id != 55 WHERE ".$condition." AND t_ticket_details.is_active = 1");
    $rowSeat = mysql_fetch_array($sqlSeat);
    if($isApi == 0){
        $colspan = 6;
    } else {
        $colspan = 8;
    }
    ?>
    <table cellpadding="5" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="font-size: 14px; width: 100px;"><?php echo TABLE_TOTAL_BOOKED; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalBooked, 0); ?></td>
            <td style="font-size: 14px; width: 100px;"><?php echo TABLE_TOTAL_SEAT; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($rowSeat[0], 0); ?></td>
            <td colspan="<?php echo $colspan; ?>"></td>
        </tr>
        <tr>
            <td style="font-size: 14px; width: 80px;"><?php echo TABLE_TOTAL_FARE; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalAmount, 2); ?> $</td>
            <td style="font-size: 14px; width: 80px;"><?php echo "Total VAT"; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalVat, 2); ?> $</td>
            <td style="font-size: 14px; width: 80px;"><?php echo "Total"; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalAmount + $totalVat, 2); ?> $</td>
            <td style="font-size: 14px; width: 130px;"><?php echo TABLE_TOTAL_COMMISSION; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalCommission, 2); ?> $</td>
            <td style="font-size: 14px; width: 100px;"><?php echo TABLE_NET_PAYMENT; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format(($totalAmount - $totalCommission), 2); ?> $</td>
            <?php
            if($isApi == 0){
            ?>
            <td style="font-size: 14px; width: 140px;"><?php echo 'Total Markup Price'; ?>:</td>
            <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($totalMarkup, 2); ?> $</td>
            <?php
            }
            ?>
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
                    <th style="width: 120px !important; font-size: 10px;"><?php echo TABLE_TICKET_CODE; ?></th>
                    <th style="width: 110px !important; font-size: 10px;"><?php echo TABLE_BOOKING_DATE; ?></th>
                    <th style="width: 130px !important; font-size: 10px;"><?php echo TABLE_JOURNEY_DATE; ?></th>
                    <th style="width: 110px !important; font-size: 10px; text-align: center;"><?php echo REPORT_FROM; ?></th>
                    <th style="width: 110px !important; font-size: 10px; text-align: center;"><?php echo REPORT_TO; ?></th>
                    <th style="width: 100px !important; font-size: 10px; text-align: center;"><?php echo TABLE_SEAT; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo TABLE_FARE; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo "VAT"; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo "Total"; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo TABLE_COMMISSION; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo TABLE_MARKUP; ?></th>
                    <th style="width: 130px !important; font-size: 10px; text-align: center; <?php if($isApi == 1){ ?>display: none;<?php } ?>"><?php echo TABLE_CREATED; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo TABLE_STATUS; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="17" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
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
<div class="buttons">
    <button type="button" id="<?php echo $btnExport; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/csv.png" alt=""/>
        <?php echo ACTION_EXPORT_TO_EXCEL; ?>
    </button>
</div>
<div style="clear: both;"></div>