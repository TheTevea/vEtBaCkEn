<?php
// Authentication
$this->element('check_access');
$allowAdd=checkAccess($user['User']['id'], $this->params['controller'], 'add');
// include("includes/function.php");
// $sqlAgencyBalance = mysql_query("SELECT agency_balances.id,
//                                  t_agents.commission, t_agents.commission_type, t_agents.payment, t_agents.max_balance, t_agents.`type`, t_agents.apply_bonus, t_agents.bonus,
//                                  t_tickets.date, t_tickets.total_amount, t_tickets.total_vat, t_tickets.discount_amount, t_tickets.lucky_draw_fee, t_tickets.total_seat, t_tickets.t_journey_id, t_tickets.journey_date,
//                                  t_journeys.agent_price_amount, t_journeys.t_destination_from_id, t_journeys.t_destination_to_id, t_journeys.t_transportation_type_id
//                                  FROM agency_balances 
//                                  INNER JOIN t_tickets ON t_tickets.id = agency_balances.t_ticket_id AND t_tickets.status = 2
//                                  INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id
//                                  INNER JOIN t_agents ON t_agents.id = agency_balances.t_agency_id
//                                  WHERE agency_balances.t_ticket_id IS NOT NULL AND agency_balances.module = 'Ticket Booking' AND DATE(agency_balances.created) >= '2024-11-17' AND DATE(agency_balances.created) <= '2024-11-17'");
// while($rowAgencyBalance = mysql_fetch_array($sqlAgencyBalance)){
//     $totalCommission = 0;
//     $date            = $rowAgencyBalance['journey_date'];
//     $agencyMarkupDis = $rowAgencyBalance['discount_amount'];
//     $luckyTicket     = $rowAgencyBalance['lucky_draw_fee'];
//     $agentBalance    = 0;
//     // Calculate Commission (Default Agency Price No Commission)
//     if($rowAgencyBalance['commission_type'] != 2){ // != Default Agency Price
//         if($rowAgencyBalance['commission_type'] == 1){ // Commission (%)
//             if($rowAgencyBalance['commission'] > 0 && $rowAgencyBalance['total_amount'] > 0){
//                 $totalCommission = (($rowAgencyBalance['total_amount'] + $rowAgencyBalance['total_vat']) * $rowAgencyBalance['commission']) / 100;
//             }
//         } else { // Fixed Amount
//             $totalCommission  = $rowAgencyBalance['commission'] * $rowAgencyBalance['total_seat'];
//         }
//     }
//     if($rowAgencyBalance['type'] == 1 || $rowAgencyBalance['type'] == 2){ // Agency Type (Offline or Online)
//         // Check Balance
//         if($rowAgencyBalance['payment'] == 1 || $rowAgencyBalance['payment'] == 2){ // Prepaid or Postpaid
//             if($rowAgencyBalance['commission_type'] == 2){ // Agency Price
//                 $totalAgencyPrice = $rowAgencyBalance['agent_price_amount'] * $rowAgencyBalance['total_seat'];
//                 // Check Price in Period
//                 // By Journey
//                 $sqlPJ = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = 1 AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND t_journey_id = ".$rowAgencyBalance['t_journey_id']." AND apply_type = 1 ORDER BY id DESC LIMIT 1");
//                 if(mysql_num_rows($sqlPJ)){
//                     $rowPJ = mysql_fetch_array($sqlPJ);
//                     $totalAgencyPrice  = $rowPJ['agency_price'] * $rowAgencyBalance['total_seat'];
//                 } else { // By Destination
//                     $sqlPA = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = 1 AND destination_from_id = ".$rowAgencyBalance['t_destination_from_id']." AND destination_to_id = ".$rowAgencyBalance['t_destination_to_id']." AND t_transportation_type_id = ".$rowAgencyBalance['t_transportation_type_id']." AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND apply_type = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
//                     if(mysql_num_rows($sqlPA)){
//                         $rowPAPrice = mysql_fetch_array($sqlPA);
//                         if($rowPAPrice['price_type'] == 1){
//                             $totalAgencyPrice  = $rowPAPrice['agency_price'] * $rowAgencyBalance['total_seat'];
//                         } else {
//                             $totalAgencyPrice  = $totalAgencyPrice + ($rowPAPrice['agency_price'] * $rowAgencyBalance['total_seat']);  
//                         }
//                     }
//                 }
//                 $totalAgencyVatPrice = $rowAgencyBalance['total_vat'] - $agencyMarkupDis;
//                 $totalAgencyNetPrice = $totalAgencyPrice + $totalAgencyVatPrice;
//             } else { // Commission (%) and Fixed Amount
//                 $totalAgencyVatPrice = 0;
//                 $totalAgencyPrice    = ($rowAgencyBalance['total_amount'] + $rowAgencyBalance['total_vat']) - replaceThousand(number_format($totalCommission, 2));
//                 $totalAgencyNetPrice = $totalAgencyPrice;
//             }
//             $bunusAgency = 0;
//             // Agency Online type prepaid check bonus
//             if($rowAgencyBalance['type'] == 1 && $rowAgencyBalance['payment'] == 1){
//                 if($rowAgencyBalance['apply_bonus'] == 1 && $rowAgencyBalance['bonus'] > 0){
//                     $bunusAgency = $rowAgencyBalance['bonus'] * $rowAgencyBalance['total_seat'];
//                 }
//             }
//             $agentBalance = $totalAgencyNetPrice + $luckyTicket - $bunusAgency;
//             mysql_query("UPDATE agency_balances SET net_price = ".$totalAgencyPrice.", vat_price = ".$totalAgencyVatPrice.", bonus = ".$bunusAgency.", lucky_ticket = ".$luckyTicket.", debit = ".$agentBalance." WHERE id = ".$rowAgencyBalance['id']);
//         }
//     }
// }

// $sqlAgencyBalance = mysql_query("SELECT * FROM agency_balances WHERE `t_agency_id` = 699 AND created >= '2024-05-01 00:00:00' AND created <= '2025-05-25 23:59:59' AND module = 'Ticket Booking' AND t_ticket_id IS NULL AND debit = 0 AND reference != '' AND net_price = 0");
// while($rowAgencyBalance = mysql_fetch_array($sqlAgencyBalance)){
//     $sqlTicket = mysql_query("SELECT * FROM t_tickets WHERE code = '".$rowAgencyBalance['reference']."'");
//     if(!mysql_num_rows($sqlTicket)){
//         $sqlTicket = mysql_query("SELECT * FROM t_ticket_3months WHERE code = '".$rowAgencyBalance['reference']."'");
//     }
//     if(mysql_num_rows($sqlTicket)){
//         $rowTicket   = mysql_fetch_array($sqlTicket);
//         $totalAmount = $rowTicket['total_amount'];
//         $commission  = $totalAmount * 0.1;
//         $debit = $totalAmount - $commission;
//         mysql_query("UPDATE agency_balances SET t_ticket_id = ".$rowTicket['id'].", net_price = ".$debit.", debit = ".$debit." WHERE id = ".$rowAgencyBalance['id']);
//     }
// }

// Fix Mini App Wrong Telephone
$sqlMiniApp = mysql_query("SELECT id, online_order_id FROM t_tickets WHERE telephone = '0969271027' AND status = 2 AND online_order_id IS NOT NULL");
while($rowMiniApp = mysql_fetch_array($sqlMiniApp)){
    $sqlOnline = mysql_query("SELECT contact_telephone FROM online_orders WHERE id = ".$rowMiniApp['online_order_id']);
    if(mysql_num_rows($sqlOnline)){
        $rowOnline = mysql_fetch_array($sqlOnline);
        mysql_query("UPDATE t_tickets SET telephone = '".$rowOnline['contact_telephone']."' WHERE id = ".$rowMiniApp['id']);
    }
}

// Fixed Ticket Duplicate
// $sqlTickeDuplicate = mysql_query("SELECT online_order_id FROM `t_tickets` WHERE status = 2 AND `online_order_id` IS NOT NULL AND date >= '2025-03-01' GROUP BY online_order_id, `t_destination_from_id` HAVING COUNT(id) > 1 ORDER BY `code` ASC");
// while($rowTicketDuplicate = mysql_fetch_array($sqlTickeDuplicate)){
//     $ticketId = array();
//     $sqlTicket = mysql_query("SELECT id FROM `t_tickets` WHERE online_order_id = ".$rowTicketDuplicate['online_order_id']);
//     while($rowTicket = mysql_fetch_array($sqlTicket)){
//         $ticketId[] = $rowTicket['id'];
//     }
//     $id = implode(",",$ticketId);
//     $ticketDetailList = array();
//     $sqlSeat = mysql_query("SELECT t_ticket_detail_id FROM `t_seat_controls` WHERE `t_ticket_id` IN (".$id.") GROUP BY t_ticket_detail_id");
//     while($rowSeat = mysql_fetch_array($sqlSeat)){
//         $ticketDetailList[] = $rowSeat['t_ticket_detail_id'];
//     }
//     if(!empty($ticketDetailList)){
//         $detailId = implode(",",$ticketDetailList);
//         mysql_query("UPDATE t_ticket_details SET is_active = -5 WHERE id NOT IN (".$detailId.") AND `t_ticket_id` IN (".$id.")");
//     }
//     $ticketList = array();
//     $sqlSeatTicket = mysql_query("SELECT t_ticket_id FROM `t_seat_controls` WHERE `t_ticket_id` IN (".$id.") GROUP BY t_ticket_id");
//     while($rowSeat = mysql_fetch_array($sqlSeatTicket)){
//         $ticketList[] = $rowSeat['t_ticket_id'];
//     }
//     if(!empty($ticketList)){
//         $id = implode(",",$ticketList);
//         mysql_query("UPDATE t_tickets SET status = -3 WHERE status = 2 AND id NOT IN (".$id.") AND online_order_id = ".$rowTicketDuplicate['online_order_id']);
//     }
// }

// Fixed Ticket Online
// $sqlTicketOnline = mysql_query("SELECT * FROM t_tickets WHERE status = 2 AND online_order_id IS NOT NULL");
// while($rowTicketOnline = mysql_fetch_array($sqlTicketOnline)){
    // $sqlOnline = mysql_query("SELECT * FROM online_orders WHERE id = ".$rowTicketOnline['online_order_id']);
    // if(mysql_num_rows($sqlOnline)){
    //     $rowOnline = mysql_fetch_array($sqlOnline);
    //     if($rowOnline['status'] != 4){
    //         mysql_query("UPDATE t_tickets SET status = 0 WHERE id = ".$rowTicketOnline['id']);
    //     }
    // } else {
    //     mysql_query("UPDATE t_tickets SET status = 0 WHERE id = ".$rowTicketOnline['id']);
    // }
//     $sqlDetail = mysql_query("SELECT id FROM t_ticket_details WHERE t_ticket_id = ".$rowTicketOnline['id']." AND is_active = 1 LIMIT 1");
//     if(!mysql_num_rows($sqlDetail)){ // Empty Seat
//         mysql_query("UPDATE t_tickets SET status = 0 WHERE id = ".$rowTicketOnline['id']);
//     }
// }

// Fixed Ticket Walk-In
// $sqlTicketWalkIn = mysql_query("SELECT * FROM t_tickets WHERE status = 2 AND online_order_id IS NULL");
// while($rowTicketWalkIn = mysql_fetch_array($sqlTicketWalkIn)){
//     $sqlDetail = mysql_query("SELECT id FROM t_ticket_details WHERE t_ticket_id = ".$rowTicketWalkIn['id']." AND is_active = 1 LIMIT 1");
//     if(!mysql_num_rows($sqlDetail)){ // Empty Seat
//         mysql_query("UPDATE t_tickets SET status = 0 WHERE id = ".$rowTicketWalkIn['id']);
//     }
// }

// Fixed Open Date
// $sqlOpenDate = mysql_query("SELECT * FROM t_tickets WHERE is_open_date =1");
// while($rowOpenDate = mysql_fetch_array($sqlOpenDate)){
//     $sqlTicket = mysql_query("SELECT id FROM t_tickets WHERE edit_from = ".$rowOpenDate['id']." LIMIT 1");
//     if(mysql_num_rows($sqlTicket)){ // Release ticket open date
//         mysql_query("UPDATE t_tickets SET status = -2 WHERE id = ".$rowOpenDate['id']);
//     } else {
//         mysql_query("UPDATE t_tickets SET status = 2 WHERE id = ".$rowOpenDate['id']);
//     }
// }

// Fixed Phone Call
// $sqlPhoneCall = mysql_query("SELECT * FROM t_tickets WHERE type = 2");
// while($rowOpenDate = mysql_fetch_array($sqlPhoneCall)){
    // $sqlTicket = mysql_query("SELECT id FROM t_tickets WHERE edit_from = ".$rowOpenDate['id']." LIMIT 1");
    // if(mysql_num_rows($sqlTicket)){ // Release ticket phone call
    //     mysql_query("UPDATE t_tickets SET status = -2 WHERE id = ".$rowOpenDate['id']);
    // } else {
        // $sqlDetail = mysql_query("SELECT id FROM t_ticket_details WHERE t_ticket_id = ".$rowOpenDate['id']." AND is_active = 1 LIMIT 1");
        // if(!mysql_num_rows($sqlDetail)){ // Empty Seat
        //     mysql_query("UPDATE t_tickets SET status = 0 WHERE id = ".$rowOpenDate['id']);
        // } else {
        //     mysql_query("UPDATE t_tickets SET status = 1 WHERE id = ".$rowOpenDate['id']);
        // }
    // }
// }
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableTerminal;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableTerminal = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewTerminal").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/" + id);
                });

                $(".btnEditTerminal").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/" + id);
                });
                
                $(".btnDeleteTerminal").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_DELETE; ?> <b>' + name + '</b>?</p>');
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
                            '<?php echo ACTION_DELETE; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/delete/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableTerminal.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                            createSysAct('Terminal', 'Delete', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Terminal', 'Delete', 1, '');
                                            // alert message
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
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

                $(".btnUpdateStatusTerminal").unbind("click").click(function(event){
                    event.preventDefault();
                    var id     = $(this).attr('rel');
                    var name   = $(this).attr('name');
                    var status = $(this).attr('status');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_UPDATE_STATUS; ?> <b>' + name + '</b>?</p>');
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
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/updateStatus/"+id+"/"+status,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableTerminal.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_INVALID; ?>' && result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                                            createSysAct('Terminal', 'Update Status', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Terminal', 'Update Status', 1, '');
                                            // alert message
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
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
                            '<?php echo ACTION_NO; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                });

                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1 ]
            }]
        });
        $(".btnExportTerminal").click(function(){
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/exportExcel",
                data: "action=export",
                beforeSend: function(){
                    $(".btnExportTerminal").attr('disabled','disabled');
                    $(".btnExportTerminal").find('img').attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                },
                success: function(){
                    $(".btnExportTerminal").removeAttr('disabled');
                    $(".btnExportTerminal").find('img').attr("src", "<?php echo $this->webroot; ?>img/button/csv.png");
                    window.open("<?php echo $this->webroot; ?>public/report/streets_export.csv", "_blank");
                }
            });
        });
        $(".btnAddTerminal").click(function(event){
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
        });
    });
</script>
<div class="leftPanel">
    <?php if($allowAdd){ ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnAddTerminal">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_TERMINAL_ADD; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
    <br />
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_NAME; ?></th>
                    <th><?php echo 'Map'; ?></th>
                    <th><?php echo 'Last Active'; ?></th>
                    <th><?php echo TABLE_STATUS; ?></th>
                    <th><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
    <?php if($allowAdd){ ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnAddTerminal">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_TERMINAL_ADD; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
</div>
<div class="rightPanel"></div>