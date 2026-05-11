<?php
include('includes/function.php');
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $btnPrint; ?>").click(function(){
            $(".rowView").hide();
            $("#singatureNetProfit").show();
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
            $(".rowView").show();
            $("#singatureNetProfit").hide();
        });
    });
</script>
<div class="leftPanel">
    <div id="<?php echo $printArea; ?>">
        <?php
        $condtion = '';
        $comCond  = '';
        $conMainbranch = '';
        $conAgency = '';
        $userName = '';
        $msg  = '<b style="font-size: 18px;">របាយការណ៏ចំណូលសរុប</b><br />';
        $logo = "";
        if($_POST['booking_from']!='') {
            $msg .= 'ថ្ងៃទី : '.$_POST['booking_from'];
            $condtion .= "t_tickets.date >= '".dateConvert($_POST['booking_from'])."'";
        }
        if($_POST['booking_to']!='') {
            $msg .= ' ទៅ: '.$_POST['booking_to'];
            $condtion .= " AND t_tickets.date <= '".dateConvert($_POST['booking_to'])."'";
        }
        if($_POST['traveling_from']!='') {
            $msg .= 'ថ្ងៃទី : '.$_POST['traveling_from'];
            $condtion .= "t_tickets.journey_date >= '".dateConvert($_POST['traveling_from'])."'";
        }
        if($_POST['traveling_to']!='' && $_POST['traveling_to'] != $_POST['traveling_from']) {
            $msg .= ' ទៅ: '.$_POST['traveling_to'];
            $condtion .= " AND t_tickets.journey_date >= '".dateConvert($_POST['traveling_to'])."'";
        }
        if($_POST['company'] != '') {
            if($_POST['company'] == "1,2"){
                $msg .= '<br/>ក្រុមហ៊ុន: VET Ticket<br/>';
            } else {
                $sqlCompany = mysql_query("SELECT GROUP_CONCAT(name) AS name FROM companies WHERE id IN (".$_POST['company'].")");
                $rowCompany = mysql_fetch_array($sqlCompany);
                $msg .= '<br/>ក្រុមហ៊ុន: '.$rowCompany[0].'<br/>';
            }
            $sqlLogo = mysql_query("SELECT photo FROM companies WHERE id IN (".$_POST['company'].") LIMIT 1");
            $rowLogo = mysql_fetch_array($sqlLogo);
            $logo    = $rowLogo[0];
            $condtion .= ' AND t_tickets.company_id IN ('.$_POST['company'].')';
            $comCond  .= ' AND id IN ('.$_POST['company'].')';
        }
        if($_POST['main_branch'] != '') {
            $sqlMB = mysql_query("SELECT name FROM main_branches WHERE id = ".$_POST['main_branch']);
            $rowMB = mysql_fetch_array($sqlMB);
            $msg .= '<br/ >សាខាលក់សំបុត្រ: '.$rowMB['name'];
            $conMainbranch = " AND t_tickets.main_branch_id = ".$_POST['main_branch'];
        }
        if($_POST['agency'] != '') {
            $sqlAgency = mysql_query("SELECT name FROM t_agents WHERE id = ".$_POST['agency']);
            $rowAgency = mysql_fetch_array($sqlAgency);
            $msg .= '<br/ >ភ្នាក់ងារ: '.$rowAgency['name'];
            $conAgency = " AND t_tickets.t_agent_id = ".$_POST['agency'];
        }
        $msg .= '<br />ថ្ងៃបោះពុម្ព: '.date("d/m/Y H:i:s");
        echo $this->element('/print/header-report',array('msg' => $msg, 'logo' => $logo));
        $symbol = '';
        $sqlSym = mysql_query("SELECT symbol FROM currency_centers WHERE id = 1;");
        $rowSym = mysql_fetch_array($sqlSym);
        $symbol = $rowSym[0];
        $totalIncome = 0;
        ?>
        <div id="dynamic">
            <?php
            if($_POST['show'] == "" || $_POST['show'] == 1){
            ?>
            <table class="table_print" cellspacing="0" style="width: 100%;">
                <tbody>
                    <tr>
                        <th colspan="25">Branch</th>
                    </tr>
                    <tr>
                        <th class="first" rowspan="2" style="width: 2%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th rowspan="2" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;">Location Branch</th>
                        <?php
                        $comLists = array();
                        $sqlCom = mysql_query("SELECT * FROM companies WHERE offline_project_id = 1 AND id != 2 AND is_active = 1".$comCond);
                        while($rowCom = mysql_fetch_array($sqlCom)){
                            $comLists[$rowCom['id']] = $rowCom['name'];
                            if($rowCom['id'] == 1){
                                $rowCom['name'] = "VET Ticket";
                            }
                        ?>
                        <th colspan="3" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;"><?php echo $rowCom['name']; ?></th>
                        <?php
                        }
                        ?>
                        <!-- <th colspan="3" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Total</th> -->
                    </tr>
                    <tr>
                        <?php
                        $totalCol = 2;
                        foreach($comLists AS $com){
                            $totalCol += 3;
                        ?>
                        <th style="width: 4%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Booked</th>
                        <th style="width: 4%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Seats</th>
                        <th style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Fare ($)</th>
                        <?php
                        }
                        ?>
                        <!-- <th style="width: 4%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Booked</th>
                        <th style="width: 4%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Seats</th>
                        <th style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Fare ($)</th> -->
                    </tr>
                    <?php
                    $totalPaid   = 0;
                    $totalBooked = 0;
                    $totalSeats  = 0;
                    $index   = 0;
                    $userAmt = array();
                    $sqlWalk = mysql_query("SELECT t_tickets.id, t_tickets.main_branch_id, t_tickets.company_id, main_branches.name AS branch_name 
                                            FROM t_tickets 
                                            INNER JOIN main_branches ON main_branches.id = t_tickets.main_branch_id 
                                            WHERE t_tickets.status = 2 AND t_tickets.type = 1 AND t_tickets.offline_project_id = 1 AND t_tickets.journey_type IN (1, 2) AND ".$condtion.$conMainbranch." 
                                            ORDER BY t_tickets.main_branch_id, t_tickets.company_id, t_tickets.id;");
                    if(mysql_num_rows($sqlWalk)){
                        while($rowWalk = mysql_fetch_array($sqlWalk)){
                            $indexCom = $rowWalk['company_id'];
                            if($indexCom == 1 || $indexCom == 2){
                                $indexCom = 1;
                            }
                            $sqlDetail = mysql_query("SELECT COUNT(t_ticket_details.id) AS seats, SUM(IFNULL(t_ticket_details.total_amount, 0) - IFNULL(t_ticket_details.discount, 0) + IFNULL(t_ticket_details.vat_price, 0)) AS total_amount FROM t_ticket_details WHERE t_ticket_details.is_active = 1 AND t_ticket_details.t_ticket_id = ".$rowWalk['id']);
                            $rowDetail = mysql_fetch_array($sqlDetail);
                            if (array_key_exists($rowWalk['main_branch_id'], $userAmt)){
                                if (array_key_exists($indexCom, $userAmt[$rowWalk['main_branch_id']])){
                                    $userAmt[$rowWalk['main_branch_id']][$indexCom]['total']  += $rowDetail['total_amount'];
                                    $userAmt[$rowWalk['main_branch_id']][$indexCom]['booked'] += 1;
                                    $userAmt[$rowWalk['main_branch_id']][$indexCom]['seats']  += $rowDetail['seats'];
                                } else {
                                    $userAmt[$rowWalk['main_branch_id']][$indexCom]['total']  = $rowDetail['total_amount'];
                                    $userAmt[$rowWalk['main_branch_id']][$indexCom]['booked'] = 1;
                                    $userAmt[$rowWalk['main_branch_id']][$indexCom]['seats']  = $rowDetail['seats'];
                                }
                                $userAmt[$rowWalk['main_branch_id']]['total']  += $rowDetail['total_amount'];
                                $userAmt[$rowWalk['main_branch_id']]['booked'] += 1;
                                $userAmt[$rowWalk['main_branch_id']]['seats']  += $rowDetail['seats'];
                            } else {
                                $userAmt[$rowWalk['main_branch_id']]['user']   = $rowWalk['branch_name'];
                                $userAmt[$rowWalk['main_branch_id']]['total']  = $rowDetail['total_amount'];
                                $userAmt[$rowWalk['main_branch_id']]['booked'] = 1;
                                $userAmt[$rowWalk['main_branch_id']]['seats']  = $rowDetail['seats'];
                                $userAmt[$rowWalk['main_branch_id']][$indexCom]['total']  = $rowDetail['total_amount'];
                                $userAmt[$rowWalk['main_branch_id']][$indexCom]['booked'] = 1;
                                $userAmt[$rowWalk['main_branch_id']][$indexCom]['seats']  = $rowDetail['seats'];
                            }
                        }
                    }
                    $totalBranchCompany = array();
                    if(!empty($userAmt)){
                        foreach($userAmt AS $user){
                            $totalPaid   += $user['total'];
                            $totalBooked += $user['booked'];
                            $totalSeats  += $user['seats'];
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: center;"><?php echo ++$index; ?></td>
                        <td style="padding: 5px; font-size: 12px; text-align: left;"><?php echo $user['user']; ?></td>
                        <?php
                        foreach($comLists AS $key => $com){
                            if (array_key_exists($key, $user)){
                                if(array_key_exists($key, $totalBranchCompany)){
                                    $totalBranchCompany[$key]['booked'] += $user[$key]['booked'];
                                    $totalBranchCompany[$key]['seats']  += $user[$key]['seats'];
                                    $totalBranchCompany[$key]['total']  += $user[$key]['total'];
                                } else {
                                    $totalBranchCompany[$key]['booked'] = $user[$key]['booked'];
                                    $totalBranchCompany[$key]['seats']  = $user[$key]['seats'];
                                    $totalBranchCompany[$key]['total']  = $user[$key]['total'];
                                }
                        ?>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user[$key]['booked'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user[$key]['seats'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user[$key]['total'], 2); ?></td>
                        <?php
                            } else {
                        ?>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                        <?php
                            }
                        }
                        ?>
                        <!-- <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user['booked'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user['seats'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user['total'], 2); ?></td> -->
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="<?php echo $totalCol; ?>" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="2">Total</td>
                        <?php
                        foreach($comLists AS $key => $com){
                            if (array_key_exists($key, $totalBranchCompany)){
                        ?>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalBranchCompany[$key]['booked'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalBranchCompany[$key]['seats'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalBranchCompany[$key]['total'], 2); ?></td>
                        <?php
                            } else {
                        ?>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;">-</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;">-</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;">-</td>
                        <?php
                            }
                        }
                        ?>
                        <!-- <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalBooked, 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalSeats, 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalPaid, 2); ?></td> -->
                    </tr>
                </tbody>
            </table>
            <?php
            } 
            if($_POST['show'] == "" || $_POST['show'] == 2){
            ?>
            <br />
            <table class="table_print" cellspacing="0" style="width: 100%;">
                <tbody>
                    <tr>
                        <th colspan="25">Agency</th>
                    </tr>
                    <tr>
                        <th class="first" rowspan="2" style="width: 2%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th rowspan="2" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;">Agency Name</th>
                        <?php
                        $comLists = array();
                        $sqlCom = mysql_query("SELECT * FROM companies WHERE offline_project_id = 1 AND id != 2 AND is_active = 1".$comCond);
                        while($rowCom = mysql_fetch_array($sqlCom)){
                            $comLists[$rowCom['id']] = $rowCom['name'];
                            if($rowCom['id'] == 1){
                                $rowCom['name'] = "VET Ticket";
                            }
                        ?>
                        <th colspan="3" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;"><?php echo $rowCom['name']; ?></th>
                        <?php
                        }
                        ?>
                        <!-- <th colspan="3" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Total</th> -->
                    </tr>
                    <tr>
                        <?php
                        $totalCol = 2;
                        foreach($comLists AS $com){
                            $totalCol += 3;
                        ?>
                        <th style="width: 4%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Booked</th>
                        <th style="width: 4%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Seats</th>
                        <th style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Fare ($)</th>
                        <?php
                        }
                        ?>
                        <!-- <th style="width: 4%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Booked</th>
                        <th style="width: 4%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Seats</th>
                        <th style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Fare ($)</th> -->
                    </tr>
                    <?php
                    $totalPaid   = 0;
                    $totalBooked = 0;
                    $totalSeats  = 0;
                    $index   = 0;
                    $userAgency = array();
                    $totalAgencyCompany = array();
                    $sqlWalk = mysql_query("SELECT t_tickets.id, 
                                            t_tickets.t_agent_id AS main_branch_id, 
                                            t_tickets.company_id, 
                                            t_agents.name AS branch_name,
                                            t_agents.type AS agent_type,
                                            (IFNULL(agency_balances.debit, 0)) AS total_net,
                                            ((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0)) - (((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0)) * t_agents.commission) / 100)) AS total_api 
                                            FROM t_tickets 
                                            INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.id != 55 AND t_agents.id != 106
                                            LEFT JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id
                                            WHERE t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND t_tickets.journey_type IN (1, 2) AND ".$condtion.$conAgency." 
                                            ORDER BY t_tickets.t_agent_id, t_tickets.company_id;");
                    if(mysql_num_rows($sqlWalk)){
                        while($rowWalk = mysql_fetch_array($sqlWalk)){
                            $indexCom = $rowWalk['company_id'];
                            if($indexCom == 1 || $indexCom == 2){
                                $indexCom = 1;
                            }
                            $sqlDetail = mysql_query("SELECT COUNT(t_ticket_details.id) AS seats FROM t_ticket_details WHERE t_ticket_details.is_active = 1 AND t_ticket_details.t_ticket_id = ".$rowWalk['id']);
                            $rowDetail = mysql_fetch_array($sqlDetail);
                            if($rowWalk['agent_type'] == 3){ // API
                                $totalAmount = $rowWalk['total_api'];
                            } else {
                                $totalAmount = $rowWalk['total_net'];
                            }
                            if (array_key_exists($rowWalk['main_branch_id'], $userAgency)){
                                if (array_key_exists($indexCom, $userAgency[$rowWalk['main_branch_id']])){
                                    $userAgency[$rowWalk['main_branch_id']][$indexCom]['total']  += $totalAmount;
                                    $userAgency[$rowWalk['main_branch_id']][$indexCom]['booked'] += 1;
                                    $userAgency[$rowWalk['main_branch_id']][$indexCom]['seats']  += $rowDetail['seats'];
                                } else {
                                    $userAgency[$rowWalk['main_branch_id']][$indexCom]['total']  = $totalAmount;
                                    $userAgency[$rowWalk['main_branch_id']][$indexCom]['booked'] = 1;
                                    $userAgency[$rowWalk['main_branch_id']][$indexCom]['seats']  = $rowDetail['seats'];
                                }
                                $userAgency[$rowWalk['main_branch_id']]['total']  += $totalAmount;
                                $userAgency[$rowWalk['main_branch_id']]['booked'] += 1;
                                $userAgency[$rowWalk['main_branch_id']]['seats']  += $rowDetail['seats'];
                            } else {
                                $userAgency[$rowWalk['main_branch_id']]['user']   = $rowWalk['branch_name'];
                                $userAgency[$rowWalk['main_branch_id']]['total']  = $totalAmount;
                                $userAgency[$rowWalk['main_branch_id']]['booked'] = 1;
                                $userAgency[$rowWalk['main_branch_id']]['seats']  = $rowDetail['seats'];
                                $userAgency[$rowWalk['main_branch_id']][$indexCom]['total']  = $totalAmount;
                                $userAgency[$rowWalk['main_branch_id']][$indexCom]['booked'] = 1;
                                $userAgency[$rowWalk['main_branch_id']][$indexCom]['seats']  = $rowDetail['seats'];
                            }
                        }
                    }
                    if(!empty($userAgency)){
                        foreach($userAgency AS $user){
                            $totalPaid   += $user['total'];
                            $totalBooked += $user['booked'];
                            $totalSeats  += $user['seats'];
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: center;"><?php echo ++$index; ?></td>
                        <td style="padding: 5px; font-size: 12px; text-align: left;"><?php echo $user['user']; ?></td>
                        <?php
                        $totalCol = 2;
                        foreach($comLists AS $key => $com){
                            $totalCol += 3;
                            if (array_key_exists($key, $user)){
                                if(array_key_exists($key, $totalAgencyCompany)){
                                    $totalAgencyCompany[$key]['booked'] += $user[$key]['booked'];
                                    $totalAgencyCompany[$key]['seats']  += $user[$key]['seats'];
                                    $totalAgencyCompany[$key]['total']  += $user[$key]['total'];
                                } else {
                                    $totalAgencyCompany[$key]['booked'] = $user[$key]['booked'];
                                    $totalAgencyCompany[$key]['seats']  = $user[$key]['seats'];
                                    $totalAgencyCompany[$key]['total']  = $user[$key]['total'];
                                }
                        ?>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user[$key]['booked'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user[$key]['seats'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user[$key]['total'], 2); ?></td>
                        <?php
                            } else {
                        ?>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                        <?php
                            }
                        }
                        ?>
                        <!-- <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user['booked'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user['seats'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user['total'], 2); ?></td> -->
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="<?php echo $totalCol+3; ?>" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="2">Total</td>
                        <?php
                        foreach($comLists AS $key => $com){
                            if (array_key_exists($key, $totalAgencyCompany)){
                        ?>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalAgencyCompany[$key]['booked'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalAgencyCompany[$key]['seats'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalAgencyCompany[$key]['total'], 2); ?></td>
                        <?php
                            } else {
                        ?>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;">-</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;">-</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;">-</td>
                        <?php
                            }
                        }
                        ?>
                        <!-- <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalBooked, 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalSeats, 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalPaid, 2); ?></td> -->
                    </tr>
                </tbody>
            </table>
            <?php
            }
            ?>
            <br />
            <table cellpadding="0" cellspacing="0" style="margin-top: 15px; display: none; width: 100%;" id="singatureNetProfit">
                <tr>
                    <td style="width: 25%; font-size: 12px; font-weight: bold; text-align: center;">បានឃើញនិងឯកភាព</td>
                    <td style="width: 25%; font-size: 12px; font-weight: bold; text-align: center;">ប្រធានផ្នែកឥវ៉ាន់</td>
                    <td style="width: 25%; font-size: 12px; font-weight: bold; text-align: center;">អ្នកត្រួតពិនិត្យ</td>
                    <td style="width: 25%; font-size: 12px; font-weight: bold; text-align: center;">អ្នកធ្វើរបាយការណ៏</td>
                </tr>
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