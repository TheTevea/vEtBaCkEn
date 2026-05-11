<?php
include('includes/function.php');
$rnd       = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
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
            window.open("<?php echo $this->webroot; ?>public/report/sales_summary_province<?php echo $user['User']['id']; ?>.csv", "_blank");
        });
    });
</script>
<div class="leftPanel">
    <div id="<?php echo $printArea; ?>">
        <?php
        /**
         * export to excel
         */
        $filename = "public/report/sales_summary_province" . $user['User']['id'] . ".csv";
        $fp = fopen($filename,"wb");
        $excelContent = REPORT_SALES_TICKET_BRANCH." (SUMMARY BY City/Province)\n\n";
        $msg = '<b style="font-size: 18px;">' . REPORT_SALES_TICKET_BRANCH . ' (SUMMARY BY City/Province)</b><br /><br />';
        if($_POST['status']!='') {
            $condFilter = "t_tickets.status = ".$_POST['status'];
        } else {
            $condFilter = "t_tickets.status >= 0";
        }
        $condFilter .= " AND t_tickets.offline_project_id = 1";
        if($_POST['booking_from'] !='' ) {
            $msg .= TABLE_BOOKING_FROM.': '.$_POST['booking_from'];
            $excelContent .= TABLE_BOOKING_FROM.": ".$_POST['booking_from'];
            $condFilter .= " AND t_tickets.date >= '".dateConvert($_POST['booking_from'])."'";
        }
        if($_POST['booking_to'] !='' ) {
            $msg .= ' '.TABLE_BOOKING_TO.': '.$_POST['booking_to'];
            $excelContent .= TABLE_BOOKING_TO.": ".$_POST['booking_to'];
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

        $condition = "t_tickets.offline_project_id = 1 AND t_destinations.province_id IS NOT NULL";
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
        $excelContent .= "\n\nCity/Province\tTotal ($)\tWalk-In ($)\tApp ($)\tWebsite ($)\tMini App ($)\tTerminal ($)\tAgent API Postpaid ($)\tAgent API Prepaid ($)\tAgent Online Postpaid ($)\tAgent Online Prepaid ($)\tAgent Offline Postpaid ($)";
        ?>
        <div id="dynamic">
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo "City/Province"; ?></th>
                        <th style="width: 90px; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Total ($)"; ?></th>
                        <th style="width: 90px; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Walk-In ($)"; ?></th>
                        <th style="width: 90px; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "App ($)"; ?></th>
                        <th style="width: 90px; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Website ($)"; ?></th>
                        <th style="width: 90px; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Mini App ($)"; ?></th>
                        <th style="width: 90px; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Terminal ($)"; ?></th>
                        <th style="width: 130px; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Agent API Postpaid ($)"; ?></th>
                        <th style="width: 130px; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Agent API Prepaid ($)"; ?></th>
                        <th style="width: 150px; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Agent Online Postpaid ($)"; ?></th>
                        <th style="width: 140px; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Agent Online Prepaid ($)"; ?></th>
                        <th style="width: 150px; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Agent Offline Postpaid ($)"; ?></th>
                    </tr>
                    <?php
                    $index = 0;
                    $totalAmt = 0;
                    $totalBooked = 0;
                    $datas = array();
                    // $sqlTicket = mysql_query("SELECT t_tickets.type, t_tickets.t_agent_id, t_agents.type AS agent_type, t_agents.payment, t_tickets.terminal_id, t_destinations.province_id,
                    //                         SUM(t_tickets.total_seat) AS total_booked, 
                    //                         SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) AS total_amount, 
                    //                         SUM(IFNULL(agency_balances.debit, 0)) AS total_net,
                    //                         SUM((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) - (((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) * t_agents.commission) / 100)) AS total_api 
                    //                         FROM 
                    //                         (
                    //                             SELECT * FROM t_tickets WHERE ".$condFilter." AND t_tickets.id IN (SELECT t_ticket_id FROM t_ticket_details WHERE t_ticket_id > 0 AND is_active = 1)
                    //                             UNION ALL
                    //                             SELECT * FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND t_tickets.id IN (SELECT t_ticket_id FROM t_ticket_detail_3months WHERE t_ticket_id > 0 AND is_active = 1)
                    //                         ) AS t_tickets 
                    //                         INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                    //                         LEFT JOIN t_agents ON t_agents.id = t_tickets.t_agent_id
                    //                         LEFT JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
                    //                         WHERE ".$condition." GROUP BY t_tickets.id ORDER BY t_destinations.province_id, t_tickets.type");
                    $sqlTicket = mysql_query("SELECT t_tickets.type, t_tickets.t_agent_id, t_agents.type AS agent_type, t_agents.payment, t_tickets.terminal_id, t_destinations.province_id,
                                            SUM(t_tickets.total_seat) AS total_booked, 
                                            SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) AS total_amount, 
                                            SUM(IFNULL(agency_balances.debit, 0)) AS total_net,
                                            SUM((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) - (((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) * t_agents.commission) / 100)) AS total_api 
                                            FROM 
                                            (
                                                SELECT * FROM t_tickets WHERE ".$condFilter." AND t_tickets.id IN (SELECT t_ticket_id FROM t_ticket_details WHERE t_ticket_id > 0 AND is_active = 1)
                                            ) AS t_tickets 
                                            INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                                            LEFT JOIN t_agents ON t_agents.id = t_tickets.t_agent_id
                                            LEFT JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
                                            WHERE ".$condition." GROUP BY t_tickets.id ORDER BY t_destinations.province_id, t_tickets.type");
                    if(mysql_num_rows($sqlTicket)){
                        while($rowTicket = mysql_fetch_array($sqlTicket)){
                            $key  = "1_walk_in";
                            $name = "Walk In";
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
                                $rowTicket['total_amount'] = $rowTicket['total_net'];
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
                            } else if($rowTicket['type'] == 7){
                                if($rowTicket['payment'] == 1){ // Prepaid
                                    $key  = "7_agen_api_prepaid";
                                    $name = "Agent APi Prepaid";
                                } else {
                                    $key  = "6_agen_api_postpaid";
                                    $name = "Agent APi Postpaid";
                                }
                                $rowTicket['total_amount'] = $rowTicket['total_api'];
                            }
                            if(array_key_exists($rowTicket['province_id'], $datas)){
                                if(array_key_exists($key, $datas[$rowTicket['province_id']])){
                                    $datas[$rowTicket['province_id']][$key]['total_booked'] += $rowTicket['total_booked'];
                                    $datas[$rowTicket['province_id']][$key]['total_amount'] += $rowTicket['total_amount'];
                                } else {
                                    $datas[$rowTicket['province_id']][$key]['name'] = $name;
                                    $datas[$rowTicket['province_id']][$key]['total_booked'] = $rowTicket['total_booked'];
                                    $datas[$rowTicket['province_id']][$key]['total_amount'] = $rowTicket['total_amount'];
                                }
                                $datas[$rowTicket['province_id']]['total_amount'] += $rowTicket['total_amount'];
                            } else { 
                                $datas[$rowTicket['province_id']][$key]['name'] = $name;
                                $datas[$rowTicket['province_id']][$key]['total_booked'] = $rowTicket['total_booked'];
                                $datas[$rowTicket['province_id']][$key]['total_amount'] = $rowTicket['total_amount'];  
                                $datas[$rowTicket['province_id']]['total_amount'] = $rowTicket['total_amount'];
                            }
                        }
                    }
                    if(!empty($datas)){
                        $total         = 0;
                        $totalWalkIn   = 0;
                        $totalApp      = 0;
                        $totalWebsite  = 0;
                        $totalMiniApp  = 0;
                        $totalTerminal = 0;
                        $totalAgApiPos = 0;
                        $totalAgApiPre = 0;
                        $totalAgOnlPos = 0;
                        $totalAgOnlPre = 0;
                        $totalAgOffPos = 0;
                        $sqlProvince   = mysql_query("SELECT * FROM provinces WHERE is_active = 1");
                        while($rowProvince = mysql_fetch_array($sqlProvince)){
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: left;">
                            <?php 
                            echo $rowProvince['name'];
                            $excelContent .= "\n" . $rowProvince['name'];
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            if(!empty($datas[$rowProvince['id']]['total_amount'])){
                                $total += $datas[$rowProvince['id']]['total_amount'];
                                echo number_format($datas[$rowProvince['id']]['total_amount'], 2); 
                                $excelContent .= "\t".number_format($datas[$rowProvince['id']]['total_amount'], 2);
                            } else {
                                echo 0;
                                $excelContent .= "\t0";
                            }
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            if(!empty($datas[$rowProvince['id']]['1_walk_in'])){
                                $totalWalkIn += $datas[$rowProvince['id']]['1_walk_in']['total_amount'];
                                echo number_format($datas[$rowProvince['id']]['1_walk_in']['total_amount'], 2); 
                                $excelContent .= "\t".number_format($datas[$rowProvince['id']]['1_walk_in']['total_amount'], 2);
                            } else {
                                echo 0;
                                $excelContent .= "\t0";
                            }
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            if(!empty($datas[$rowProvince['id']]['2_app'])){
                                $totalApp += $datas[$rowProvince['id']]['2_app']['total_amount'];
                                echo number_format($datas[$rowProvince['id']]['2_app']['total_amount'], 2); 
                                $excelContent .= "\t".number_format($datas[$rowProvince['id']]['2_app']['total_amount'], 2);
                            } else {
                                echo 0;
                                $excelContent .= "\t0";
                            }
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            if(!empty($datas[$rowProvince['id']]['3_web'])){
                                $totalWebsite += $datas[$rowProvince['id']]['3_web']['total_amount'];
                                echo number_format($datas[$rowProvince['id']]['3_web']['total_amount'], 2); 
                                $excelContent .= "\t".number_format($datas[$rowProvince['id']]['3_web']['total_amount'], 2);
                            } else {
                                echo 0;
                                $excelContent .= "\t0";
                            }
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            if(!empty($datas[$rowProvince['id']]['4_mini_app'])){
                                $totalMiniApp += $datas[$rowProvince['id']]['4_mini_app']['total_amount'];
                                echo number_format($datas[$rowProvince['id']]['4_mini_app']['total_amount'], 2); 
                                $excelContent .= "\t".number_format($datas[$rowProvince['id']]['4_mini_app']['total_amount'], 2);
                            } else {
                                echo 0;
                                $excelContent .= "\t0";
                            }
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            if(!empty($datas[$rowProvince['id']]['5_terminal'])){
                                $totalTerminal += $datas[$rowProvince['id']]['5_terminal']['total_amount'];
                                echo number_format($datas[$rowProvince['id']]['5_terminal']['total_amount'], 2); 
                                $excelContent .= "\t".number_format($datas[$rowProvince['id']]['5_terminal']['total_amount'], 2);
                            } else {
                                echo 0;
                                $excelContent .= "\t0";
                            }
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            if(!empty($datas[$rowProvince['id']]['6_agen_api_postpaid'])){
                                $totalAgApiPos += $datas[$rowProvince['id']]['6_agen_api_postpaid']['total_amount'];
                                echo number_format($datas[$rowProvince['id']]['6_agen_api_postpaid']['total_amount'], 2); 
                                $excelContent .= "\t".number_format($datas[$rowProvince['id']]['6_agen_api_postpaid']['total_amount'], 2);
                            } else {
                                echo 0;
                                $excelContent .= "\t0";
                            }
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            if(!empty($datas[$rowProvince['id']]['7_agen_api_prepaid'])){
                                $totalAgApiPre += $datas[$rowProvince['id']]['7_agen_api_prepaid']['total_amount'];
                                echo number_format($datas[$rowProvince['id']]['7_agen_api_prepaid']['total_amount'], 2); 
                                $excelContent .= "\t".number_format($datas[$rowProvince['id']]['7_agen_api_prepaid']['total_amount'], 2);
                            } else {
                                echo 0;
                                $excelContent .= "\t0";
                            }
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            if(!empty($datas[$rowProvince['id']]['8_agen_onl_postpaid'])){
                                $totalAgOnlPos += $datas[$rowProvince['id']]['8_agen_onl_postpaid']['total_amount'];
                                echo number_format($datas[$rowProvince['id']]['8_agen_onl_postpaid']['total_amount'], 2); 
                                $excelContent .= "\t".number_format($datas[$rowProvince['id']]['8_agen_onl_postpaid']['total_amount'], 2);
                            } else {
                                echo 0;
                                $excelContent .= "\t0";
                            }
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            if(!empty($datas[$rowProvince['id']]['9_agen_onl_prepaid'])){
                                $totalAgOnlPre += $datas[$rowProvince['id']]['9_agen_onl_prepaid']['total_amount'];
                                echo number_format($datas[$rowProvince['id']]['9_agen_onl_prepaid']['total_amount'], 2); 
                                $excelContent .= "\t".number_format($datas[$rowProvince['id']]['9_agen_onl_prepaid']['total_amount'], 2);
                            } else {
                                echo 0;
                                $excelContent .= "\t0";
                            }
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            if(!empty($datas[$rowProvince['id']]['a_agen_off_postpaid'])){
                                $totalAgOffPos += $datas[$rowProvince['id']]['a_agen_off_postpaid']['total_amount'];
                                echo number_format($datas[$rowProvince['id']]['a_agen_off_postpaid']['total_amount'], 2); 
                                $excelContent .= "\t".number_format($datas[$rowProvince['id']]['a_agen_off_postpaid']['total_amount'], 2);
                            } else {
                                echo 0;
                                $excelContent .= "\t0";
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                        }
                        $excelContent .= "\nTotal\t".number_format($total, 2)."\t".number_format($totalWalkIn, 2)."\t".number_format($totalApp, 2)."\t".number_format($totalWebsite, 2)."\t".number_format($totalMiniApp, 2)."\t".number_format($totalTerminal, 2)."\t".number_format($totalAgApiPos, 2)."\t".number_format($totalAgApiPre, 2)."\t".number_format($totalAgOnlPos, 2)."\t".number_format($totalAgOnlPre, 2)."\t".number_format($totalAgOffPos, 2);
                    ?>
                    <tr>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"><?php echo TABLE_TOTAL; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($total, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalWalkIn, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalApp, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalWebsite, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalMiniApp, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalTerminal, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalAgApiPos, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalAgApiPre, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalAgOnlPos, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalAgOnlPre, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalAgOffPos, 2); ?></td>
                    </tr>
                    <?php
                    } else {
                        $excelContent .= "\n".TABLE_NO_RECORD;
                    ?>
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_RECORD; ?></td>
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
</div>
<div class="rightPanel"></div>
<?php
$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);
?>