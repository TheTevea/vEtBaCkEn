<?php
// Authentication
$this->element('check_access');
$allowPaid = checkAccess($user['User']['id'], $this->params['controller'], 'agencyOnlinePostpaidClaim');

include('includes/function.php');
$rnd = rand();
$oTable    = "oTable" . $rnd;
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
$btnClaim  = "btnClaim" . $rnd;

$msg = '<b style="font-size: 18px;">' . REPORT_SALES_TICKET_AGENCY_ONLINE_POSTPAID . '</b><br /><br />';
if($_POST['status']!='') {
    $condition = "t_tickets.status = ".$_POST['status'];
} else {
    $condition = "t_tickets.status >= 0";
}
$condition .= " AND t_tickets.offline_project_id = ".$user['User']['offline_project_id'];
$conTicket = "t_tickets.status >= 0 AND t_tickets.offline_project_id = 1";
if($_POST['booking_from'] !='' ) {
    $msg .= TABLE_BOOKING_FROM.': '.$_POST['booking_from'];
    $condition .= " AND t_tickets.date >= '".dateConvert($_POST['booking_from'])."'";
    $conTicket .= " AND t_tickets.date >= '".dateConvert($_POST['booking_from'])."'";
}
if($_POST['booking_to'] !='' ) {
    $msg .= ' '.TABLE_BOOKING_TO.': '.$_POST['booking_to'];
    $condition .= " AND t_tickets.date <= '".dateConvert($_POST['booking_to'])."'";
    $conTicket .= " AND t_tickets.date <= '".dateConvert($_POST['booking_to'])."'";
}
$msg .= "<br/>";
if($_POST['traveling_from'] != '') {
    $msg .= TABLE_TRAVELING_FROM.': '.$_POST['traveling_from'];
    $condition .= " AND t_tickets.journey_date >= '".dateConvert($_POST['traveling_from'])."'";
    $conTicket .= " AND t_tickets.journey_date >= '".dateConvert($_POST['traveling_from'])."'";
}
if($_POST['traveling_to']!='') {
    $msg .= ' '.TABLE_TRAVELING_TO.': '.$_POST['traveling_to'];
    $condition .= " AND t_tickets.journey_date <= '".dateConvert($_POST['traveling_to'])."'";
    $conTicket .= " AND t_tickets.journey_date <= '".dateConvert($_POST['traveling_to'])."'";
}
if($_POST['destination_from']!='') {
    $sqlDesFrom = mysql_query("SELECT name FROM t_destinations WHERE id = ".$_POST['destination_from']);
    $rowDesFrom = mysql_fetch_array($sqlDesFrom);
    $msg .= '<br/>'.TABLE_DESTINATION_FROM.': '.$rowDesFrom[0];
    $condition .= " AND t_tickets.t_destination_from_id = ".$_POST['destination_from'];
    $conTicket .= " AND t_tickets.t_destination_from_id = ".$_POST['destination_from'];
}
if($_POST['destination_to']!='') {
    $sqlDesTo = mysql_query("SELECT name FROM t_destinations WHERE id = ".$_POST['destination_to']);
    $rowDesTo = mysql_fetch_array($sqlDesTo);
    $msg .= '<br/>'.TABLE_DESTINATION_TO.': '.$rowDesTo[0];
    $condition .= " AND t_tickets.t_destination_to_id = ".$_POST['destination_to'];
    $conTicket .= " AND t_tickets.t_destination_to_id = ".$_POST['destination_to'];
}
$isApi = 0;
$agentBalance    = 0;
$agentMaxBalance = 0;
if($_POST['agency']!='') {
    $sqlAgency  = mysql_query("SELECT name, type, max_balance FROM t_agents WHERE id = ".$_POST['agency']);
    $rowAgency  = mysql_fetch_array($sqlAgency);
    $sqlBalance = mysql_query("SELECT IFNULL((SELECT SUM(credit - debit) FROM `agency_balances` WHERE t_agency_id = ".$_POST['agency']."), 0)");
    $rowBalance = mysql_fetch_array($sqlBalance);
    if($rowAgency['max_balance'] > 0){
        $agentBalance = $rowAgency['max_balance'] - ($rowBalance[0] * -1);
    }
    $agentMaxBalance = $rowAgency['max_balance'];
    $msg .= '<br/>'.MENU_AGENT.': '.$rowAgency[0];
    $condition .= " AND t_tickets.t_agent_id = ".$_POST['agency'];
    $conTicket .= " AND t_tickets.t_agent_id = ".$_POST['agency'];
    if($rowAgency['type'] == 3){
        $isApi = 1;
    }
}
if($_POST['company']!='') {
    $sqlCom = mysql_query("SELECT name FROM companies WHERE id IN (".$_POST['company'].")");
    $rowCom = mysql_fetch_array($sqlCom);
    $msg .= '<br/>'.MENU_COMPANY_MANAGEMENT.': '.$rowCom[0];
    $condition .= " AND t_tickets.company_id IN (".$_POST['company'].")";
    $conTicket .= " AND t_tickets.company_id IN (".$_POST['company'].")";
}
if($_POST['agency_group']!='') {
    $condition .= " AND t_agents.t_agent_type_id = ".$_POST['agency_group'];
    $conTicket .= " AND t_agents.t_agent_type_id = ".$_POST['agency_group'];
}
if($_POST['main_branch']!='') {
    $condition .= " AND t_agents.main_branch_id = ".$_POST['main_branch'];
    $conTicket .= " AND t_agents.main_branch_id = ".$_POST['main_branch'];
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
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/agencyOnlinePostpaidAjax/<?php echo str_replace("/", "|||", implode(',', $_POST)); ?>",
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
                $("#<?php echo $tblName; ?> td:nth-child(14)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(15)").css("text-align", "center");
                <?php
                if($allowPaid == false){
                ?>
                $("#<?php echo $tblName; ?> td:nth-child(16)").css("display", "none");
                <?php
                } else {
                ?>
                $(".btnAgentOnlinePostPaidUnPaid").unbind("click").click(function(event){
                    event.preventDefault();
                    var id   = $(this).attr('rel');
                    var name = $(this).attr('name');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo "Do you want to void paid on ticket code "; ?> <b>' + name + '</b>?</p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_CONFIRMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
			            buttons: {
                            '<?php echo ACTION_YES; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    dataType: "json",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/agencyOnlinePostPaidUnpaid/"+id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        <?php echo $oTable; ?>.fnDraw(false);
                                        createSysAct('Report', 'Agent PostPaid Paid', 1, '');
                                        // alert message
                                        if(result.error == "0"){
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                                        }else {
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                                        }
                                        $("#dialog").dialog({
                                            title: '<?php echo DIALOG_INFORMATION; ?>',
                                            resizable: false,
                                            modal: true,
                                            width: 'auto',
                                            height: 'auto',
                                            buttons: {
                                                '<?php echo ACTION_CLOSE; ?>': function() {
                                                    $(this).dialog("close");
                                                }
                                            }
                                        });
                                    }
                                });
                            },
                            '<?php echo ACTION_CANCEL; ?>': function() {
                                $(this).dialog("close");
                            }
			            }
                    });
                });
                <?php
                }
                ?>
                $("#chkAgencyOnlinePostpaid").unbind("click").click(function(){
                    if($(this).is(':checked')){
                        $(".chkAgencyOnlinePostpaid").attr("checked", true);
                    } else {
                        $(".chkAgencyOnlinePostpaid").attr("checked", false);
                    }
                });
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                <?php
                if($allowPaid){
                ?>
                "bSortable": false, "aTargets": [ 0, 1, 2, 4, 5, 6, 7, 8, 9, 10, 11, 13, 14, 15, 16 ]
                <?php
                } else {
                ?>
                "bSortable": false, "aTargets": [ 0, 1, 2, 4, 5, 6, 7, 8, 9, 10, 11, 13, 14, 15 ]
                <?php
                }
                ?>
            }],
            "aaSorting": [[ 3, "asc" ]]
        });

        $("#<?php echo $btnPrint; ?>").unbind("click").click(function(){
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

        $("#<?php echo $btnClaim; ?>").unbind("click").click(function(event){
            event.preventDefault();
            var post = agencyOnlinePostpaidConvertPost();
            if(post != ""){
                $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to save claim?</p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_CONFIRMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        '<?php echo ACTION_YES; ?>': function() {
                            $(".<?php echo $btnClaim; ?>").attr("disabled", true);
                            $("#lblBtnAgencyOnlinePostpaidClaim").text("<?php echo ACTION_LOADING; ?>");
                            $.ajax({
                                type: "POST",
                                dataType: "json",
                                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/agencyOnlinePostpaidClaim/1",
                                data: post,
                                beforeSend: function(){
                                    $("#dialog").dialog("close");
                                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                },
                                success: function(result){
                                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                    oCache.iCacheLower = -1;
                                    <?php echo $oTable; ?>.fnDraw(false);
                                    createSysAct('Report', 'Agency Online PostPaid Claim', 1, '');
                                    $(".<?php echo $btnClaim; ?>").attr("disabled", false);
                                    $("#lblBtnAgencyOnlinePostpaidClaim").text("Paid & Print");
                                    // alert message
                                    if(result.error == "0"){
                                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                                        $.ajax({
                                            type: "GET",
                                            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/agencyOnlinePostpaidPrint/"+result.id,
                                            beforeSend: function(){
                                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                            },
                                            success: function(printInvoiceResult){
                                                w=window.open();
                                                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css?2329980" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css?879182167" media="print" />');
                                                w.document.write(printInvoiceResult);
                                                w.document.close();
                                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            }
                                        });
                                    }else {
                                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                                    }
                                    $("#dialog").dialog({
                                        title: '<?php echo DIALOG_INFORMATION; ?>',
                                        resizable: false,
                                        modal: true,
                                        width: 'auto',
                                        height: 'auto',
                                        buttons: {
                                            '<?php echo ACTION_CLOSE; ?>': function() {
                                                $(this).dialog("close");
                                            }
                                        }
                                    });
                                }
                            });
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            } else {
                alert("Please check ticket before save.");
            }
        });

        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/sales_ticket_agency_online_postpaid<?php echo $user['User']['id']; ?>.csv", "_blank");
        });
    });

    function agencyOnlinePostpaidConvertPost(){
        var post = "";
        $(".chkAgencyOnlinePostpaid").each(function(){
            if($(this).is(':checked')){
                if(post != ''){
                    post += '&';
                }
                post += 'tid[]='+$(this).val();
            }
        });
        return post;
    }
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $totalAmount = 0;
    $totalNet    = 0;
    $totalCommission = 0;
    $totalBooked = 0;
    $totalMarkup = 0;
    $totalSeat   = 0;
    $totalPaid   = 0;
    $totalUnpaid = 0;
    $sqlTicket = mysql_query("SELECT t_tickets.id, 
                              (IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0)) AS total_amount, 
                              (IFNULL(agency_balances.debit, 0)) AS total_net,
                              (IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0)) AS total, 
                              t_tickets.t_agent_id, 
                              t_tickets.is_agent_paid, 
                              IFNULL(t_tickets.total_markup, 0) AS total_markup,
                              t_tickets.status 
                              FROM 
                              (
                                SELECT t_tickets.*  
                                FROM t_tickets
                                INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.status = 1 AND t_agents.type = 1 AND t_agents.payment = 2 AND t_agents.id != 55
                                WHERE ".$conTicket."
                                UNION ALL
                                SELECT t_tickets.*  
                                FROM t_ticket_3months AS t_tickets
                                INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.status = 1 AND t_agents.type = 1 AND t_agents.payment = 2 AND t_agents.id != 55
                                WHERE ".$conTicket."
                              ) AS t_tickets 
                              INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.status = 1 AND t_agents.type = 1 AND t_agents.payment = 2 AND t_agents.id != 55
                              INNER JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
                              WHERE ".$condition);
    while($rowTicket = mysql_fetch_array($sqlTicket)){
        $sqlSeat   = mysql_query("SELECT COUNT(id) AS total, nationally FROM t_ticket_details WHERE t_ticket_id = ".$rowTicket['id']." AND is_active = 1");
        $rowSeat   = mysql_fetch_array($sqlSeat);
        if(empty($rowSeat[0])){
            $sqlSeat   = mysql_query("SELECT COUNT(id) AS total, nationally FROM t_ticket_detail_3months WHERE t_ticket_id = ".$rowTicket['id']." AND is_active = 1");
            $rowSeat   = mysql_fetch_array($sqlSeat);
            if(empty($rowSeat[0])){
                $sqlSeat   = mysql_query("SELECT COUNT(id) AS total, nationally FROM 2023_t_ticket_details WHERE t_ticket_id = ".$rowTicket['id']." AND is_active = 1");
                $rowSeat   = mysql_fetch_array($sqlSeat);
            }
        }
        $sqlAgency = mysql_query("SELECT * FROM t_agents WHERE id = ".$rowTicket['t_agent_id']);
        $rowAgency = mysql_fetch_array($sqlAgency);
        $markUp    = $rowTicket['total_markup'];
        if($rowTicket['status'] > 0){
            if($rowTicket['is_agent_paid'] == 1){
                $totalPaid   += $rowTicket['total_net'];
            } else {
                $totalUnpaid += $rowTicket['total_net'];
            }
        }
        $totalAmount += $rowTicket['total_amount'];
        $totalNet    += $rowTicket['total_net'];
        $totalCommission += $rowTicket['total_amount'] - $rowTicket['total_net'];
        $totalMarkup += $markUp;
        $totalSeat   += $rowSeat['total'];
        $totalBooked++;
    }
    ?>
    <table cellpadding="5" cellspacing="0" style="width: 100%;">
        <?php
        if($agentMaxBalance > 0){
        ?>
        <tr>
            <td style="font-size: 14px; width: 100px;"><?php echo "Balance"; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($agentBalance, 2); ?> $</td>
            <td style="font-size: 14px; width: 100px;"><?php echo "Max Balance"; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($agentMaxBalance, 2); ?> $</td>
            <td style="font-size: 14px; width: 150px;">Total Paid Net Price:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalPaid, 2); ?> $</td>
            <td style="font-size: 14px; width: 150px;">Total Unpaid Net Price:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalUnpaid, 2); ?> $</td>
            <td style="font-size: 14px; width: 140px;"></td>
            <td style="font-size: 14px; font-weight: bold;"></td>
        </tr>
        <?php
        }
        ?>
        <tr>
            <td style="font-size: 14px; width: 100px;"><?php echo TABLE_TOTAL_BOOKED; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalBooked, 0); ?></td>
            <td style="font-size: 14px; width: 100px;"><?php echo TABLE_TOTAL_SEAT; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalSeat, 0); ?></td>
            <td style="font-size: 14px; width: 120px;"><?php echo "Total Selling Price"; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalAmount, 2); ?> $</td>
            <td style="font-size: 14px; width: 120px;"><?php echo "Total Net Price"; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalNet, 2); ?> $</td>
            <td style="font-size: 14px; width: 130px;"><?php echo TABLE_TOTAL_COMMISSION; ?>:</td>
            <td style="width: 120px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalCommission, 2); ?> $</td>
            <td style="font-size: 14px; width: 140px;"><?php echo 'Total Markup Price'; ?>:</td>
            <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($totalMarkup, 2); ?> $</td>
        </tr>
    </table>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="font-size: 10px; width: 35px;" class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 140px !important; font-size: 10px;"><?php echo MENU_AGENT; ?></th>
                    <th style="width: 120px !important; font-size: 10px;"><?php echo TABLE_TICKET_CODE; ?></th>
                    <th style="width: 110px !important; font-size: 10px;"><?php echo TABLE_BOOKING_DATE; ?></th>
                    <th style="width: 130px !important; font-size: 10px;"><?php echo TABLE_JOURNEY_DATE; ?></th>
                    <th style="width: 110px !important; font-size: 10px; text-align: center;"><?php echo REPORT_FROM; ?></th>
                    <th style="width: 110px !important; font-size: 10px; text-align: center;"><?php echo REPORT_TO; ?></th>
                    <th style="width: 100px !important; font-size: 10px; text-align: center;"><?php echo TABLE_SEAT; ?>#</th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo "Selling Price"; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo "Net Price"; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo TABLE_COMMISSION; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo TABLE_MARKUP; ?></th>
                    <th style="width: 130px !important; font-size: 10px; text-align: center;"><?php echo TABLE_CREATED; ?></th>
                    <th style="width: 130px !important; font-size: 10px; text-align: center;"><?php echo "Paid Date"; ?></th>
                    <th style="width: 130px !important; font-size: 10px; text-align: center;"><?php echo "Paid By"; ?></th>
                    <th style="width: 75px !important; font-size: 10px; text-align: center;"><?php echo TABLE_STATUS; ?></th>
                    <th style="width: 120px !important; font-size: 10px; <?php if($allowPaid == false){ echo 'display: none;'; } ?>"><?php echo ACTION_ACTION; ?> <input type="checkbox" id="chkAgencyOnlinePostpaid" /></th>
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
<?php
if($allowPaid){
?>
<div class="buttons">
    <button type="button" id="<?php echo $btnClaim; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span id="lblBtnAgencyOnlinePostpaidClaim"><?php echo 'Paid & Print'; ?></span>
    </button>
</div>
<?php
} 
?>
<div style="clear: both;"></div>