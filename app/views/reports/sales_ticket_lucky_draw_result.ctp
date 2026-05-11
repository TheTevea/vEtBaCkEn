<?php
include('includes/function.php');
$rnd        = rand();
$oTable     = "oTable" . $rnd;
$printArea  = "printArea" . $rnd;
$btnPrint   = "btnPrint" . $rnd;
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
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/salesTicketLuckyDrawAjax/<?php echo str_replace("/", "|||", implode(';', $_POST)); ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?>_length, #<?php echo $tblName; ?>_filter, #<?php echo $tblName; ?>_info, #<?php echo $tblName; ?>_paginate").hide();
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td").css("font-size", "11px");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td:nth-child(11)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(13)").css("text-align", "left");
                $("#<?php echo $tblName; ?> td:nth-child(14)").css("text-align", "left");
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0, -1, -2, -3, -4, -5, -6, -7, -8, -9, -10 ]
            }],
            "aaSorting": [[ 2, "asc" ]]
        });
        $("#<?php echo $btnPrint; ?>").click(function(){
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . REPORT_SALES_TICKET_LUCKY_DRAW . '</b><br /><br />';
    $condition = "t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND t_tickets.lucky_draw_fee > 0";
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
    if($_POST['company']!='') {
        $sqlCompany = mysql_query("SELECT GROUP_CONCAT(name) AS name FROM companies WHERE id IN (".$_POST['company'].")");
        $rowCompany = mysql_fetch_array($sqlCompany);
        $msg .= '<br/>'.MENU_COMPANY_MANAGEMENT.': '.$rowCompany[0];
        $condition .= " AND t_tickets.company_id IN (".$_POST['company'].")";
    } else {
        $condition .= " AND t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '" . $user['User']['id']. "')";
    }

    if($_POST['main_branch']!='') {
        $sqlMB = mysql_query("SELECT name FROM main_branches WHERE id = ".$_POST['main_branch']);
        $rowMB = mysql_fetch_array($sqlMB);
        $msg  .= '<br/>'.MENU_MAIN_BRANCH.': '.$rowMB[0];
        $condition .= " AND t_tickets.main_branch_id = ".$_POST['main_branch'];
    } else {
        if($user['User']['is_admin'] == 0){
            $condition .= ' AND t_tickets.main_branch_id = '.$user['User']['main_branch_id'];
        } else {
            $condition .= ' AND t_tickets.main_branch_id > 0';
        }
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

    echo $this->element('/print/header-report',array('msg'=>$msg));
    $sqlTicket = mysql_query("SELECT COUNT(id) AS total_booked, SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0)) AS total_amount, SUM(IFNULL(t_tickets.lucky_draw_fee, 0)) AS lucky_draw_fee FROM t_tickets WHERE ".$condition);
    $rowTicket = mysql_fetch_array($sqlTicket);
    
    $sqlSeat = mysql_query("SELECT COUNT(t_ticket_details.id) FROM t_ticket_details INNER JOIN t_tickets ON t_tickets.id = t_ticket_details.t_ticket_id WHERE ".$condition);
    $rowSeat = mysql_fetch_array($sqlSeat);
    ?>
    <table cellpadding="5" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="font-size: 14px; width: 100px;"><?php echo TABLE_TOTAL_BOOKED; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($rowTicket['total_booked'], 0); ?></td>
            <td style="font-size: 14px; width: 120px;"><?php echo TABLE_TOTAL_SEAT; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($rowSeat[0], 0); ?></td>
            <td style="font-size: 14px; width: 80px;"><?php echo TABLE_TOTAL_FARE; ?>:</td>
            <td style="font-size: 14px; width: 120px; font-weight: bold;"><?php echo number_format($rowTicket['total_amount'], 2); ?> $</td>
            <td style="font-size: 14px; width: 170px;"><?php echo "Total Lucky Ticket Fee"; ?>:</td>
            <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($rowTicket['lucky_draw_fee'], 2); ?> $</td>
        </tr>
    </table>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" style="width: 100%;">
            <thead>  
                <tr>
                    <th style="font-size: 10px; width: 35px;" class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 170px !important; font-size: 10px;"><?php echo MENU_MAIN_BRANCH; ?></th>
                    <th style="width: 130px !important; font-size: 10px;"><?php echo TABLE_BOOKING_DATE; ?></th>
                    <th style="width: 170px !important; font-size: 10px;"><?php echo TABLE_TICKET_CODE; ?></th>
                    <th style="width: 100px !important; font-size: 10px;"><?php echo TABLE_JOURNEY_DATE; ?></th>
                    <th style="width: 90px !important; font-size: 10px;"><?php echo TABLE_DEPARTURE; ?></th>
                    <th style="width: 110px !important; font-size: 10px;"><?php echo REPORT_FROM; ?></th>
                    <th style="width: 110px !important; font-size: 10px;"><?php echo REPORT_TO; ?></th>
                    <th style="width: 110px !important; font-size: 10px;"><?php echo TABLE_TELEPHONE; ?></th>
                    <th style="width: 110px !important; font-size: 10px;"><?php echo "Seat Number"; ?></th>
                    <th style="width: 75px !important; font-size: 10px;"><?php echo "LUCKY FEE"; ?></th>
                    <th style="width: 75px !important; font-size: 10px;"><?php echo GENERAL_AMOUNT; ?></th>
                    <th style="width: 75px !important; font-size: 10px;"><?php echo TABLE_TYPE; ?></th>
                    <th style="width: 80px !important;  font-size: 10px;"><?php echo TABLE_CREATED_BY; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="14" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
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