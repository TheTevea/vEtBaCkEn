<?php
include('includes/function.php');
$rnd       = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
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
    });
</script>
<div class="leftPanel">
    <div id="<?php echo $printArea; ?>">
        <?php
        $msg = '<b style="font-size: 18px;">' . REPORT_SALES_TICKET_BRANCH . ' (SUMMARY)</b><br /><br />';
        if($_POST['status'] != '') {
            $condFilter = "t_tickets.status = ".$_POST['status'];
        } else {
            $condFilter = "t_tickets.status > 0";
        }
        $condFilter .= " AND t_tickets.offline_project_id = 1";
        if($_POST['booking_from'] !='' ) {
            $msg .= TABLE_BOOKING_FROM.': '.$_POST['booking_from'];
            $condFilter .= " AND t_tickets.date >= '".dateConvert($_POST['booking_from'])."'";
        }
        if($_POST['booking_to'] !='' ) {
            $msg .= ' '.TABLE_BOOKING_TO.': '.$_POST['booking_to'];
            $condFilter .= " AND t_tickets.date <= '".dateConvert($_POST['booking_to'])."'";
        }
        $msg .= "<br/>";
        if($_POST['traveling_from'] != '') {
            $msg .= TABLE_TRAVELING_FROM.': '.$_POST['traveling_from'];
            $condFilter .= " AND t_tickets.journey_date >= '".dateConvert($_POST['traveling_from'])."'";
        }
        if($_POST['traveling_to']!='') {
            $msg .= ' '.TABLE_TRAVELING_TO.': '.$_POST['traveling_to'];
            $condFilter .= " AND t_tickets.journey_date <= '".dateConvert($_POST['traveling_to'])."'";
        }
        if($_POST['company']!='') {
            if($_POST['company'] == "1,2"){
                $msg .= '<br/>'.MENU_COMPANY_MANAGEMENT.': VET Ticket';
            } else {
                $company = "";
                $comCon  = $_POST['company'];
                if (strpos($_POST['company'], "1,2") !== false) {
                    $company = "VET Ticket,";
                    $comCon  = str_replace("1,2,", "", $_POST['company']);
                }
                $sqlCompany = mysql_query("SELECT GROUP_CONCAT(name) FROM companies WHERE id IN (".$comCon.")");
                $rowCompany = mysql_fetch_array($sqlCompany);
                $company .= $rowCompany[0];
                $msg .= '<br/>'.MENU_COMPANY_MANAGEMENT.': '.$company;
            }
            $condFilter .= " AND t_tickets.company_id IN (".$_POST['company'].")";
        } else {
            $condFilter .= " AND t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '" . $user['User']['id']. "')";
        }
    
        if($_POST['main_branch']!='') {
            $sqlMB = mysql_query("SELECT name FROM main_branches WHERE id = ".$_POST['main_branch']);
            $rowMB = mysql_fetch_array($sqlMB);
            $msg  .= '<br/>'.MENU_MAIN_BRANCH.': '.$rowMB[0];
            $condFilter .= " AND t_tickets.main_branch_id = ".$_POST['main_branch'];
        }
    
        if($_POST['destination_from']!='') {
            $sqlDesFrom = mysql_query("SELECT name FROM t_destinations WHERE id = ".$_POST['destination_from']);
            $rowDesFrom = mysql_fetch_array($sqlDesFrom);
            $msg .= '<br/>'.TABLE_DESTINATION_FROM.': '.$rowDesFrom[0];
            $condFilter .= " AND t_tickets.t_destination_from_id = ".$_POST['destination_from'];
        }
        if($_POST['destination_to']!='') {
            $sqlDesTo = mysql_query("SELECT name FROM t_destinations WHERE id = ".$_POST['destination_to']);
            $rowDesTo = mysql_fetch_array($sqlDesTo);
            $msg .= '<br/>'.TABLE_DESTINATION_TO.': '.$rowDesTo[0];
            $condFilter .= " AND t_tickets.t_destination_to_id = ".$_POST['destination_to'];
        }
        if($_POST['created_by']!='') {
            $sqlUser = mysql_query("SELECT username FROM users WHERE id = ".$_POST['created_by']);
            $rowUser = mysql_fetch_array($sqlUser);
            $msg .= '<br/>'.MENU_USERS.': '.$rowUser[0];
            $condFilter .= " AND t_tickets.created_by = ".$_POST['created_by'];
        }
        if($_POST['payment_method']!='') {
            $condFilter .= " AND t_tickets.payment_method_id = ".$_POST['payment_method'];
        }

        if($_POST['agency']!='') {
            if($_POST['agency'] == "vetDg1" || $_POST['agency'] == "vetDg2" || $_POST['agency'] == "vetDg3" || $_POST['agency'] == "vetDg4"){
                if($_POST['agency'] == "vetDg1"){
                    $msg .= '<br/>'.MENU_AGENT.': VET Digital (API)';
                    $condFilter .= " AND t_tickets.t_agent_id = 47 AND t_tickets.online_order_id IS NOT NULL AND t_tickets.booking_type = 1";
                } else if($_POST['agency'] == "vetDg2"){
                    $msg .= '<br/>'.MENU_AGENT.': VET App (API)';
                    $condFilter .= " AND t_tickets.t_agent_id = 47 AND t_tickets.online_order_id IS NOT NULL AND t_tickets.booking_type = 2";
                } else if($_POST['agency'] == "vetDg3"){
                    $msg .= '<br/>'.MENU_AGENT.': VET Digital (Manual)';
                    $condFilter .= " AND t_tickets.t_agent_id = 86 AND t_tickets.online_order_id IS NULL";
                } else {
                    $msg .= '<br/>'.MENU_AGENT.': VET APP (Manual)';
                    $condFilter .= " AND t_tickets.t_agent_id = 91 AND t_tickets.online_order_id IS NULL";
                }
            } else {
                $sqlAgency = mysql_query("SELECT name FROM t_agents WHERE id = ".$_POST['agency']);
                $rowAgency = mysql_fetch_array($sqlAgency);
                $msg .= '<br/>'.MENU_AGENT.': '.$rowAgency[0];
                $condFilter .= " AND t_tickets.t_agent_id = ".$_POST['agency'];
            }
        }

        $condition = "t_tickets.offline_project_id = 1";
        if($_POST['booking_type']!=''){
            if($_POST['booking_type'] == 1){ // Walk In
                $condition .= " AND t_tickets.type = 1";
            } else if($_POST['booking_type'] == 2){ // Website
                $condition .= " AND t_tickets.terminal_id IS NULL AND ((t_tickets.type = 5 OR t_tickets.type = 11) AND t_tickets.t_agent_id = 55)";
            } else if($_POST['booking_type'] == 3){ // Agent APi (Prepaid)
                $condition .= " AND ((t_tickets.type = 3 OR t_tickets.type = 7) AND t_agents.type = 3 AND t_agents.payment = 1)";
            } else if($_POST['booking_type'] == 4){ // Agent Online (Prepaid)
                $condition .= " AND ((t_tickets.type = 3 OR t_tickets.type = 9) AND t_agents.type = 1 AND t_agents.payment = 1)";
            } else if($_POST['booking_type'] == 5){ // Agent Offline (Prepaid)
                $condition .= " AND ((t_tickets.type = 3 OR t_tickets.type = 9) AND t_agents.type = 2 AND t_agents.payment = 1)";
            } else if($_POST['booking_type'] == 6){ // Mini App
                $condition .= " AND ((t_tickets.type = 5 OR t_tickets.type = 10 OR t_tickets.type = 11) AND t_tickets.t_agent_id = 106)";
            } else if($_POST['booking_type'] == 7){ // App
                $condition .= " AND t_tickets.terminal_id IS NULL AND ((t_tickets.type = 5 OR t_tickets.type = 11) AND t_tickets.t_agent_id IS NULL)";
            } else if($_POST['booking_type'] == 8){ // Terminal
                $condition .= " AND t_tickets.terminal_id IS NOT NULL";
            } else if($_POST['booking_type'] == 9){ // Agent APi (Postpaid)
                $condition .= " AND ((t_tickets.type = 3 OR t_tickets.type = 7) AND t_agents.type = 3 AND t_agents.payment = 2)";
            } else if($_POST['booking_type'] == 10){ // Agent Online (Postpaid)
                $condition .= " AND ((t_tickets.type = 3 OR t_tickets.type = 9) AND t_agents.type = 1 AND t_agents.payment = 2)";
            } else if($_POST['booking_type'] == 11){ // Agent Offline (Postpaid)
                $condition .= " AND ((t_tickets.type = 3 OR t_tickets.type = 9) AND t_agents.type = 2 AND t_agents.payment = 2)";
            } 
        }
        if($_POST['destination_group']!='') {
            $condition .= " AND t_destinations.t_destination_group_id = ".$_POST['destination_group'];
        }
        if($_POST['province']!='') {
            $condition .= " AND t_destinations.province_id = ".$_POST['province'];
        }
        echo $this->element('/print/header-report',array('msg'=>$msg));
        ?>
        <div id="dynamic">
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <th class="first" style="width: 10%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;"><?php echo TABLE_NO; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo "Booking Type"; ?></th>
                        <th style="width: 20%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Total Seat"; ?></th>
                        <th style="width: 15%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Gross Sales"; ?></th>
                        <th style="width: 15%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Commission"; ?></th>
                        <th style="width: 15%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Net Revenue"; ?></th>
                    </tr>
                    <?php
                    $index = 0;
                    $totalAmt = 0;
                    $totalCom = 0;
                    $totalRev = 0;
                    $totalBooked = 0;
                    $datas = array();
                    // if($user['User']['id'] == 2 || $user['User']['id'] == 925 || $user['User']['id'] == 2193){ // Admin, Poty, Yunheng COO
                    //     $sqlTicket = mysql_query("SELECT t_tickets.type, t_tickets.t_agent_id, t_agents.type AS agent_type, t_agents.payment, t_tickets.terminal_id,
                    //                         SUM(t_tickets.total_seat) AS total_booked, 
                    //                         SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) AS total_amount, 
                    //                         SUM(IFNULL(agency_balances.debit, 0)) AS total_net,
                    //                         SUM((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) - (((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) * t_agents.commission) / 100)) AS total_api 
                    //                         FROM 
                    //                         (
                    //                             SELECT * FROM t_tickets WHERE ".$condFilter." AND t_tickets.id IN (SELECT t_ticket_id FROM t_ticket_details WHERE t_ticket_id > 0)
                    //                             UNION ALL
                    //                             SELECT * FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND t_tickets.id IN (SELECT t_ticket_id FROM t_ticket_detail_3months WHERE t_ticket_id > 0)
                    //                             UNION ALL
                    //                             SELECT * FROM 2023_t_tickets AS t_tickets WHERE ".$condFilter." AND t_tickets.id IN (SELECT t_ticket_id FROM 2023_t_ticket_details WHERE t_ticket_id > 0)
                    //                         ) AS t_tickets 
                    //                         INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                    //                         LEFT JOIN t_agents ON t_agents.id = t_tickets.t_agent_id
                    //                         LEFT JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
                    //                         WHERE ".$condition." GROUP BY t_tickets.id ORDER BY t_tickets.type");
                    // } else {
                    //     $sqlTicket = mysql_query("SELECT t_tickets.type, t_tickets.t_agent_id, t_agents.type AS agent_type, t_agents.payment, t_tickets.terminal_id,
                    //                         SUM(t_tickets.total_seat) AS total_booked, 
                    //                         SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) AS total_amount, 
                    //                         SUM(IFNULL(agency_balances.debit, 0)) AS total_net,
                    //                         SUM((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) - (((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) * t_agents.commission) / 100)) AS total_api 
                    //                         FROM 
                    //                         (
                    //                             SELECT * FROM t_tickets WHERE ".$condFilter." AND t_tickets.id IN (SELECT t_ticket_id FROM t_ticket_details WHERE t_ticket_id > 0)
                    //                             UNION ALL
                    //                             SELECT * FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND t_tickets.id IN (SELECT t_ticket_id FROM t_ticket_detail_3months WHERE t_ticket_id > 0)
                    //                         ) AS t_tickets 
                    //                         INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                    //                         LEFT JOIN t_agents ON t_agents.id = t_tickets.t_agent_id
                    //                         LEFT JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
                    //                         WHERE ".$condition." GROUP BY t_tickets.id ORDER BY t_tickets.type");
                    // }

                    $sqlTicket = mysql_query("SELECT t_tickets.type, t_tickets.t_agent_id, t_agents.type AS agent_type, t_agents.payment, t_tickets.terminal_id, t_tickets.online_order_id,
                                            SUM(t_tickets.total_seat) AS total_booked, 
                                            SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) AS total_amount, 
                                            SUM(IFNULL(agency_balances.debit, 0)) AS total_net,
                                            SUM((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) - (((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) * t_agents.commission) / 100)) AS total_api 
                                            FROM 
                                            (
                                                SELECT * FROM t_tickets WHERE ".$condFilter."
                                                UNION ALL
                                                SELECT * FROM t_ticket_3months AS t_tickets WHERE ".$condFilter."
                                            ) AS t_tickets 
                                            INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                                            LEFT JOIN t_agents ON t_agents.id = t_tickets.t_agent_id
                                            LEFT JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
                                            WHERE ".$condition." GROUP BY t_tickets.id ORDER BY t_tickets.type");
                    if(mysql_num_rows($sqlTicket)){
                        while($rowTicket = mysql_fetch_array($sqlTicket)){
                            $key  = "1_walk_in";
                            $name = "Walk In";
                            $rowTicket['commission'] = 0;
                            $rowTicket['revenue']    = $rowTicket['total_amount'];
                            if($rowTicket['type'] == 1){
                                $key  = "1_walk_in";
                                $name = "Walk In";
                            } else if($rowTicket['type'] == 3 || $rowTicket['type'] == 9){
                                if($rowTicket['agent_type'] == 1){ // Online
                                    if($rowTicket['payment'] == 1){ // Prepaid
                                        $key  = "9_agen_onl_prepaid";
                                        $name = "Agent Online Prepaid";
                                    } else {
                                        $key  = "8_agen_onl_postpaid";
                                        $name = "Agent Online Postpaid";
                                    }
                                } else if($rowTicket['agent_type'] == 2) { // Offline
                                    if($rowTicket['payment'] == 1){ // Prepaid
                                        $key  = "b_agen_off_prepaid";
                                        $name = "Agent Offline Prepaid";
                                    } else {
                                        $key  = "a_agen_off_postpaid";
                                        $name = "Agent Offline Postpaid";
                                    }
                                } else  { // APi
                                    if($rowTicket['payment'] == 1){ // Prepaid
                                        $key  = "7_agen_api_prepaid";
                                        $name = "Agent APi Prepaid";
                                    } else {
                                        $key  = "6_agen_api_postpaid";
                                        $name = "Agent APi Postpaid";
                                    }
                                }
                                $rowTicket['commission'] = $rowTicket['total_amount'] - $rowTicket['total_net'];
                                $rowTicket['revenue']    = $rowTicket['total_net'];
                            } else if($rowTicket['type'] == 5 || $rowTicket['type'] == 11 || $rowTicket['type'] == 10){
                                if($rowTicket['t_agent_id'] == 106){
                                    $key  = "4_mini_app";
                                    $name = "Mini App";
                                } else {
                                    if(!empty($rowTicket['terminal_id'])){
                                        $key  = "5_terminal";
                                        $name = "Terminal";
                                    } else {
                                        if($rowTicket['t_agent_id'] == 55){
                                            $key  = "3_web";
                                            $name = "Website";
                                        } else {
                                            $key  = "2_app";
                                            $name = "App";
                                        }
                                    }
                                    
                                }
                            } else if($rowTicket['type'] == 7){ // APi Booking
                                if($rowTicket['payment'] == 1){ // Prepaid
                                    $key  = "7_agen_api_prepaid";
                                    $name = "Agent APi Prepaid";
                                    $sqlAmt = mysql_query("SELECT agency_balances.net_price FROM agency_balances 
                                                           INNER JOIN t_ticket_api_tmps ON t_ticket_api_tmps.id = agency_balances.t_ticket_id AND t_ticket_api_tmps.online_order_id = '".$rowTicket['online_order_id']."'
                                                           WHERE agency_balances.module = 'Ticket Booking' LIMIT 1");
                                    if(mysql_num_rows($sqlAmt)){
                                        $rowAmt = mysql_fetch_array($sqlAmt);
                                        $rowTicket['commission'] = $rowTicket['total_amount'] - $rowAmt['net_price'];
                                        $rowTicket['revenue']    = $rowAmt['net_price'];
                                    }
                                } else {
                                    $key  = "6_agen_api_postpaid";
                                    $name = "Agent APi Postpaid";
                                    $rowTicket['commission'] = $rowTicket['total_amount'] - $rowTicket['total_api'];
                                    $rowTicket['revenue']    = $rowTicket['total_api'];
                                }
                            }
                            if(array_key_exists($key, $datas)){
                                $datas[$key]['total_booked'] += $rowTicket['total_booked'];
                                $datas[$key]['total_amount'] += $rowTicket['total_amount'];
                                $datas[$key]['commission']   += $rowTicket['commission'];
                                $datas[$key]['revenue']      += $rowTicket['revenue'];
                            } else {
                                $datas[$key]['name'] = $name;
                                $datas[$key]['total_booked'] = $rowTicket['total_booked'];
                                $datas[$key]['total_amount'] = $rowTicket['total_amount'];
                                $datas[$key]['commission']   = $rowTicket['commission'];
                                $datas[$key]['revenue']      = $rowTicket['revenue'];
                            }
                        }
                    }
                    if(!empty($datas)){
                        ksort($datas);
                        foreach($datas AS $data){
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php 
                            echo ++$index;
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo $data['name'];
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            $totalBooked += $data['total_booked'];
                            echo number_format($data['total_booked'], 0); 
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            $totalAmt += $data['total_amount']; 
                            echo "$ ".number_format($data['total_amount'], 2); 
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            $totalCom += $data['commission']; 
                            echo "$ ".number_format($data['commission'], 2); 
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            $totalRev += $data['revenue']; 
                            echo "$ ".number_format($data['revenue'], 2); 
                            ?>
                        </td>
                    </tr>
                    <?php
                        }
                    ?>
                    <tr>
                        <td colspan="2" style="text-align: right;"><?php echo TABLE_TOTAL; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"><?php echo number_format($totalBooked, 0); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalAmt, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalCom, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalRev, 2); ?></td>
                    </tr>
                    <?php
                    } else {
                    ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_RECORD; ?></td>
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
    <div style="clear: both;"></div>
</div>
<div class="rightPanel"></div>