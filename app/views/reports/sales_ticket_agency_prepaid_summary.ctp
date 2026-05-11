<?php
include('includes/function.php');
$rnd       = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
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
    });
</script>
<div class="leftPanel">
    <div id="<?php echo $printArea; ?>">
        <?php
        $msg = '<b style="font-size: 18px;">' . REPORT_SALES_TICKET_AGENCY_ONLINE . ' Prepaid (SUMMARY)</b><br /><br />';
        $condition = "t_tickets.offline_project_id = 1 AND t_tickets.t_agent_id != 55";
        $conTicket = "t_tickets.status >= 0 AND t_tickets.offline_project_id = 1";
        if($_POST['status']!='') {
            $condition .= " AND t_tickets.status = ".$_POST['status'];
        } else {
            $condition .= " AND t_tickets.status >= 0";
        }
        if($_POST['booking_from'] !='' ) {
            $msg .= TABLE_BOOKING_FROM.': '.$_POST['booking_from'];
            // $condition .= " AND t_tickets.date >= '".dateConvert($_POST['booking_from'])."'";
            $conTicket .= " AND t_tickets.date >= '".dateConvert($_POST['booking_from'])."'";
        }
        if($_POST['booking_to'] !='' ) {
            $msg .= ' '.TABLE_BOOKING_TO.': '.$_POST['booking_to'];
            // $condition .= " AND t_tickets.date <= '".dateConvert($_POST['booking_to'])."'";
            $conTicket .= " AND t_tickets.date <= '".dateConvert($_POST['booking_to'])."'";
        }
        $msg .= "<br/>";
        if($_POST['traveling_from'] != '') {
            $msg .= TABLE_TRAVELING_FROM.': '.$_POST['traveling_from'];
            // $condition .= " AND t_tickets.journey_date >= '".dateConvert($_POST['traveling_from'])."'";
            $conTicket .= " AND t_tickets.journey_date >= '".dateConvert($_POST['traveling_from'])."'";
        }
        if($_POST['traveling_to']!='') {
            $msg .= ' '.TABLE_TRAVELING_TO.': '.$_POST['traveling_to'];
            // $condition .= " AND t_tickets.journey_date <= '".dateConvert($_POST['traveling_to'])."'";
            $conTicket .= " AND t_tickets.journey_date <= '".dateConvert($_POST['traveling_to'])."'";
        }
        if($_POST['company']!='') {
            $sqlCompany = mysql_query("SELECT name FROM companies WHERE id IN (".$_POST['company'].")");
            $rowCompany = mysql_fetch_array($sqlCompany);
            $msg .= '<br/>'.MENU_COMPANY_MANAGEMENT.': '.$rowCompany[0];
            $condition .= " AND t_tickets.company_id IN (".$_POST['company'].")";
            $conTicket .= " AND t_tickets.company_id IN (".$_POST['company'].")";
        } else {
            $condition .= " AND t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '" . $user['User']['id']. "')";
            $conTicket .= " AND t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '" . $user['User']['id']. "')";
        }
    
        if($_POST['agency']!='') {
            $condition .= " AND t_tickets.t_agent_id = ".$_POST['agency'];
            $conTicket .= " AND t_tickets.t_agent_id = ".$_POST['agency'];
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

        if($_POST['main_branch']!='') {
            $condition .= " AND t_agents.main_branch_id = ".$_POST['main_branch'];
            $conTicket .= " AND t_agents.main_branch_id = ".$_POST['main_branch'];
        }
        echo $this->element('/print/header-report',array('msg'=>$msg));
        ?>
        <div id="dynamic">
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <th class="first" style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;"><?php echo TABLE_NO; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo MENU_MAIN_BRANCH; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo "Agency Name"; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo "Agency Group"; ?></th>
                        <th style="width: 7%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "ទឹកលុយនៅសល់"; ?></th>
                        <th style="width: 7%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo TABLE_TOTAL_BOOKED; ?></th>
                        <th style="width: 7%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Total Seats"; ?></th>
                        <th style="width: 9%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Total Selling Price"; ?></th>
                        <th style="width: 9%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Total Commission"; ?></th>
                        <th style="width: 7%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Total Bonus"; ?></th>
                        <th style="width: 7%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Total Markup"; ?></th>
                    </tr>
                    <?php
                    $index = 0;
                    $totalAmt = 0;
                    $totalBooked = 0;
                    $totalSeats  = 0;
                    $totalCom    = 0;
                    $totalMarkup = 0;
                    $totalBonus  = 0;
                    $totalBalance     = 0;
                    $totalBalanceSell = 0;
                    $datas = array();
                    $sqlTicket = mysql_query("SELECT t_tickets.id, t_tickets.code, t_tickets.t_agent_id, t_agents.name AS agent_name, t_agents.type AS agent_type, t_agents.commission AS agent_commission, t_agents.commission_type AS agent_commission_type, t_agent_types.name AS agent_group, t_agents.max_balance,
                                            (IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0)) AS total_amount,
                                            (IFNULL(agency_balances.debit, 0) + IFNULL(agency_balances.bonus, 0)) AS total_net,
                                            (IFNULL(t_tickets.total_markup, 0)) AS total_markup,
                                            (IFNULL(t_tickets.total_bonus, 0)) AS total_bonus,
                                            t_tickets.journey_date,
                                            t_journeys.id AS journey_id, t_journeys.foreigner_price, t_journeys.unit_price, t_journeys.agent_price_amount, t_journeys.agetn_price_percent, t_journeys.t_destination_from_id, t_journeys.t_destination_to_id, t_journeys.t_transportation_type_id,
                                            main_branches.name AS main_branch
                                            FROM 
                                            (
                                                SELECT t_tickets.*  
                                                FROM t_tickets
                                                INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.status = 1 AND t_agents.type = 1 AND t_agents.payment = 1 AND t_agents.id != 55
                                                WHERE ".$conTicket."
                                                UNION ALL
                                                SELECT t_tickets.*  
                                                FROM t_ticket_3months AS t_tickets
                                                INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.status = 1 AND t_agents.type = 1 AND t_agents.payment = 1 AND t_agents.id != 55
                                                WHERE ".$conTicket."
                                            ) AS t_tickets 
                                            INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.status = 1 AND t_agents.type = 1 AND t_agents.payment = 1 AND t_agents.id != 55
                                            INNER JOIN main_branches ON main_branches.id = t_agents.main_branch_id
                                            INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id
                                            INNER JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
                                            LEFT JOIN t_agent_types ON t_agent_types.id = t_agents.t_agent_type_id
                                            WHERE ".$condition."  
                                            ORDER BY t_agent_types.name, t_agents.name");
                    if(mysql_num_rows($sqlTicket)){
                        while($rowTicket = mysql_fetch_array($sqlTicket)){
                            $sqlSeat   = mysql_query("SELECT COUNT(id) AS total, nationally FROM t_ticket_details WHERE t_ticket_id = ".$rowTicket['id']." AND is_active = 1");
                            $rowSeat   = mysql_fetch_array($sqlSeat);
                            if(empty($rowSeat[0])){
                                $sqlSeat   = mysql_query("SELECT COUNT(id) AS total, nationally FROM t_ticket_detail_3months WHERE t_ticket_id = ".$rowTicket['id']." AND is_active = 1");
                                $rowSeat   = mysql_fetch_array($sqlSeat);
                            }
                            $key  = $rowTicket['t_agent_id'];
                            if(array_key_exists($key, $datas)){
                                $datas[$key]['total_booked'] += 1;
                                $datas[$key]['total_seats']  += $rowSeat['total'];
                                $datas[$key]['total_amount'] += $rowTicket['total_amount'];
                                $datas[$key]['total_commission'] += $rowTicket['total_amount'] - $rowTicket['total_net'];
                                $datas[$key]['total_markup'] += $rowTicket['total_markup'];
                                $datas[$key]['total_bonus']  += $rowTicket['total_bonus'];
                            } else {
                                $datas[$key]['main_branch']  = $rowTicket['main_branch'];
                                $datas[$key]['name']  = $rowTicket['agent_name'];
                                $datas[$key]['group'] = $rowTicket['agent_group'];
                                $datas[$key]['total_booked'] = 1;
                                $datas[$key]['total_seats']  = $rowSeat['total'];
                                $datas[$key]['total_amount'] = $rowTicket['total_amount'];
                                $datas[$key]['total_commission'] = $rowTicket['total_amount'] - $rowTicket['total_net'];
                                $datas[$key]['total_markup'] = $rowTicket['total_markup'];
                                $datas[$key]['total_bonus']  = $rowTicket['total_bonus'];
                            }
                        }
                    }
                    if(!empty($datas)){
                        foreach($datas AS $key => $data){
                            $sqlBalance = mysql_query("SELECT IFNULL((SELECT SUM(credit - debit) FROM `agency_balances` WHERE t_agency_id = ".$key."), 0)");
                            $rowBalance = mysql_fetch_array($sqlBalance);
                            $balance    = $rowBalance[0];
                            $totalBalance += $balance;
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php 
                            echo ++$index;
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo $data['main_branch'];
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo $data['name'];
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo $data['group'];
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            echo "$ ".number_format($balance, 2);
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
                            $totalSeats += $data['total_seats'];
                            echo number_format($data['total_seats'], 0); 
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
                            if($data['total_commission'] < 0){
                                $data['total_commission'] = $data['total_commission'] * -1;
                            }
                            $totalCom += $data['total_commission']; 
                            echo "$ ".number_format($data['total_commission'], 2); 
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            $totalBonus += $data['total_bonus'];
                            echo "$ ".number_format($data['total_bonus'], 2); 
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            $totalMarkup += $data['total_markup']; 
                            echo "$ ".number_format($data['total_markup'], 2); 
                            ?>
                        </td>
                    </tr>
                    <?php
                        }
                    ?>
                    <tr>
                        <td colspan="4" style="text-align: right;"><?php echo TABLE_TOTAL; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalBalance, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"><?php echo number_format($totalBooked, 0); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"><?php echo number_format($totalSeats, 0); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalAmt, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalCom, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalBonus, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalMarkup, 2); ?></td>
                    </tr>
                    <?php
                    } else {
                    ?>
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_RECORD; ?></td>
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