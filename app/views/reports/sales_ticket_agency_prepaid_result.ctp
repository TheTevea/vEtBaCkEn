<?php
include('includes/function.php');
$rnd = rand();
$oTable = "oTable" . $rnd;
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;

$msg = '<b style="font-size: 18px;">' . REPORT_SALES_TICKET_AGENCY_ONLINE . ' (Prepaid)</b><br /><br />';
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
    $condition .= " AND t_agent_id IN (SELECT id FROM t_agents WHERE status = 1 AND payment = 1 AND type = 1 AND id != 55)";
}
if($_POST['company']!='') {
    $sqlCom = mysql_query("SELECT name FROM companies WHERE id IN (".$_POST['company'].")");
    $rowCom = mysql_fetch_array($sqlCom);
    $msg .= '<br/>'.MENU_COMPANY_MANAGEMENT.': '.$rowCom[0];
    $condition .= " AND company_id IN (".$_POST['company'].")";
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
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/salesTicketAgencyPrepaidAjax/<?php echo str_replace("/", "|||", implode(',', $_POST)); ?>",
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
                $("#<?php echo $tblName; ?> td:nth-child(10)").css("text-align", "left");
                $("#<?php echo $tblName; ?> td:nth-child(11)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(12)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(13)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(14)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(15)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(16)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(17)").css("text-align", "right");
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0, -1, -2, -3, -4, -5, -6, -7, -8, -9, -10, -12, -13, -14, -15 ]
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
            window.open("<?php echo $this->webroot; ?>public/report/sales_ticket_agency_postpaid<?php echo $user['User']['id']; ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $totalAmount     = 0;
    $totalNetPrice   = 0;
    $totalCommission = 0;
    $totalBooked = 0;
    $totalMarkup = 0;
    $totalDis    = 0;
    $totalVat    = 0;
    $totalBonus  = 0;
    $totalSeat   = 0;
    $sqlTicket = mysql_query("SELECT id, t_tickets.date, 
                              IFNULL(t_tickets.total_amount, 0) AS total_amount, 
                              IFNULL(t_tickets.discount_amount, 0) AS discount_amount, 
                              IFNULL(t_tickets.total_vat, 0) AS total_vat, 
                              IFNULL(t_tickets.total_bonus, 0) AS total_bonus, 
                              t_agent_id, 
                              t_journey_id, 
                              IFNULL(t_tickets.total_markup, 0) AS total_markup, 
                              IFNULL(t_tickets.commission, 0) AS commission, 
                              t_tickets.journey_date 
                              FROM t_tickets WHERE ".$condition);
    while($rowTicket = mysql_fetch_array($sqlTicket)){
        $sqlSeat   = mysql_query("SELECT COUNT(id) AS total, nationally FROM t_ticket_details WHERE t_ticket_id = ".$rowTicket['id']." AND is_active = 1");
        $rowSeat   = mysql_fetch_array($sqlSeat);
        $sqlAgency = mysql_query("SELECT * FROM t_agents WHERE id = ".$rowTicket['t_agent_id']);
        $rowAgency = mysql_fetch_array($sqlAgency);
        $sqlJou = mysql_query("SELECT t_journeys.*, companies.type AS com_type FROM t_journeys INNER JOIN companies ON companies.id = t_journeys.company_id WHERE t_journeys.id = ".$rowTicket['t_journey_id']);
        $rowJou = mysql_fetch_array($sqlJou);
        $agePrice  = 0;
        $agentComType = $rowAgency['commission_type'];
        $agentCom = $rowAgency['commission'];
        if($rowJou['com_type'] == 2){ // Boat
            $agentComType = $rowAgency['commission_buva_sea_type'];
            $agentCom = $rowAgency['commission_buva_sea'];
        }
        if($agentComType == 1){ // Percentage
            $totalCommission += (($rowTicket['total_amount'] + $rowTicket['total_vat']) * $agentCom) / 100;
        } else if($agentComType == 2){ // Agency Price
            $sellPrice = $rowTicket['total_amount'];
            $agePrice  = $rowJou['agent_price_amount'] * $rowSeat['total'];
            if($rowSeat['nationally'] == 2){
                $agePrice  = $rowJou['agetn_price_percent'] * $rowSeat['total'];
            }
            // Check Price in Period
            // By Journey
            $date  = $rowTicket['journey_date'];
            $sqlPJ = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = 1 AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND t_journey_id = ".$rowTicket['t_journey_id'].' ORDER BY id DESC LIMIT 1');
            if(mysql_num_rows($sqlPJ)){
                $rowPJ = mysql_fetch_array($sqlPJ);
                if($rowSeat['nationally'] == 2){ // Foriegner  
                    $agePrice  = $rowPJ['agency_price_foreigner'] * $rowSeat['total'];
                } else {
                    $agePrice  = $rowPJ['agency_price'] * $rowSeat['total'];
                }
            } else { // By Destination
                $sqlPA = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = 1 AND destination_from_id = ".$rowJou['t_destination_from_id']." AND destination_to_id = ".$rowJou['t_destination_to_id']." AND t_transportation_type_id = ".$rowJou['t_transportation_type_id']." AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
                if(mysql_num_rows($sqlPA)){
                    $rowPAPrice = mysql_fetch_array($sqlPA);
                    if($rowPAPrice['price_type'] == 1){
                        if($rowSeat['nationally'] == 2){ // Foriegner  
                            $agePrice  = $rowPAPrice['agency_price_foreigner'] * $rowSeat['total'];
                        } else {
                            $agePrice  = $rowPAPrice['agency_price'] * $rowSeat['total'];
                        }
                    } else {
                        if($rowSeat['nationally'] == 2){ // Foriegner
                            $agePrice  = ($agePrice + ($rowPAPrice['agency_price_foreigner'])) * $rowSeat['total'];
                        } else {
                            $agePrice  = ($agePrice + ($rowPAPrice['agency_price'])) * $rowSeat['total'];   
                        }
                    }
                }
            }
            $commission  = $sellPrice - $agePrice;
            $totalCommission += $commission;
        } else { // Fixed Amount
            $totalCommission += $agentCom;
        }
        $totalNetPrice += $agePrice;
        $totalVat      += $rowTicket['total_vat'];
        $totalAmount   += $rowTicket['total_amount'];
        $totalMarkup   += $rowTicket['total_markup'];
        $totalSeat     += $rowSeat['total'];
        $totalBonus    += $rowTicket['total_bonus'];
        $totalDis      += $rowTicket['discount_amount'];
        $totalBooked++;
    }
    ?>
    <table cellpadding="5" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="font-size: 14px; width: 140px;"><?php echo TABLE_TOTAL_BOOKED; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalBooked, 0); ?></td>
            <td style="font-size: 14px; width: 100px;"><?php echo TABLE_TOTAL_SEAT; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalSeat, 0); ?></td>
            <td colspan="8"></td>
        </tr>
        <tr>
            <td style="font-size: 14px; width: 100px;"><?php echo "Total Selling Price"; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalAmount + $totalVat, 2); ?> $</td>
            <td style="font-size: 14px; width: 100px;"><?php echo "Total Net Price"; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalNetPrice + $totalVat, 2); ?> $</td>
            <td style="font-size: 14px; width: 140px;"><?php echo 'Total Discount'; ?>:</td>
            <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($totalDis, 2); ?> $</td>
            <td style="font-size: 14px; width: 130px;"><?php echo TABLE_TOTAL_COMMISSION; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalCommission, 2); ?> $</td>
            <td style="font-size: 14px; width: 140px;"><?php echo 'Total Bonus'; ?>:</td>
            <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($totalBonus, 2); ?> $</td>
            <td style="font-size: 14px; width: 140px;"><?php echo 'Total Markup'; ?>:</td>
            <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($totalMarkup, 2); ?> $</td>
        </tr>
    </table>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" style="width: 130%;">
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
                    <th style="width: 100px !important; font-size: 10px; text-align: left;"><?php echo TABLE_SEAT; ?>#</th>
                    <th style="width: 75px !important; font-size: 10px; text-align: right;"><?php echo "Selling Price"; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: right;"><?php echo "Net Price"; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: right;"><?php echo TABLE_COMMISSION; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: right;"><?php echo "Bonus"; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: right;"><?php echo TABLE_MARKUP; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: right;"><?php echo GENERAL_DISCOUNT; ?></th>
                    <th style="width: 130px !important; font-size: 10px; text-align: center;"><?php echo TABLE_CREATED; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo TABLE_STATUS; ?></th>
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
<div class="buttons">
    <button type="button" id="<?php echo $btnExport; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/csv.png" alt=""/>
        <?php echo ACTION_EXPORT_TO_EXCEL; ?>
    </button>
</div>
<div style="clear: both;"></div>