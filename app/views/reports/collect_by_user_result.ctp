<?php
include('includes/function.php');
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
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
        $(".viewIncomeDetail").unbind("click");
        $(".viewIncomeDetail").click(function(event){
            event.preventDefault();
            var branchId = $(this).attr('branch');
            var destinationId = '';
            var vanId = $(this).attr('ware');
            var leftPanel  = $(this).parent().parent().parent().parent().parent().parent().parent();
            var rightPanel = leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/goodsTransferByVanDetail/?date_from=<?php echo $_POST['date_from']; ?>&date_to=<?php echo $_POST['date_from']; ?>&branch="+branchId+"&van="+vanId+"&destination="+destinationId);
        });
        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/collectByUserDetail.csv", "_blank");
        });
    });
</script>
<div class="leftPanel">
    <div id="<?php echo $printArea; ?>">
        <?php
$conAgency = '';
if ($_POST['branch'] != '') {
    $userCreated = array();
    $sqlUserC = mysql_query("SELECT created_by FROM t_tickets WHERE branch_id = " . $_POST['branch'] . " AND main_branch_id = " . $_POST['main_branch'] . " AND status = 2 AND date >= '" . dateConvert($_POST['date_from']) . "' AND date <= '" . dateConvert($_POST['date_to']) . "' GROUP BY created_by");
    if (mysql_num_rows($sqlUserC)) {
        while ($rowUserC = mysql_fetch_array($sqlUserC)) {
            $userCreated[] = $rowUserC['created_by'];
        }
    }
    else {
        $userCreated[] = 0;
    }
    $userConfirm = array();
    $sqlUserCF = mysql_query("SELECT confirm_by FROM t_tickets WHERE branch_id = " . $_POST['branch'] . " AND main_branch_id = " . $_POST['main_branch'] . " AND status = 2 AND type = 2 AND date >= '" . dateConvert($_POST['date_from']) . "' AND date <= '" . dateConvert($_POST['date_to']) . "' GROUP BY confirm_by");
    if (mysql_num_rows($sqlUserCF)) {
        while ($rowUserCF = mysql_fetch_array($sqlUserCF)) {
            $userConfirm[] = $rowUserCF['created_by'];
        }
    }
    else {
        $userConfirm[] = 0;
    }

    $userLucky = array();
    $sqlUserLucky = mysql_query("SELECT created_by FROM lucky_tickets WHERE main_branch_id = " . $_POST['main_branch'] . " AND DATE(created) >= '" . dateConvert($_POST['date_from']) . "' AND DATE(created) <= '" . dateConvert($_POST['date_to']) . "' GROUP BY created_by");
    if (mysql_num_rows($sqlUserLucky)) {
        while ($rowUserLucky = mysql_fetch_array($sqlUserLucky)) {
            $userLucky[] = $rowUserLucky['created_by'];
        }
    }
    else {
        $userLucky[] = 0;
    }
}
$userMainBranch = array();
if ($_POST['main_branch'] != '') {
    $sqlUserMB = mysql_query("SELECT id FROM users WHERE is_active = 1 AND main_branch_id = " . $_POST['main_branch']);
    if (mysql_num_rows($sqlUserMB)) {
        while ($rowUserMB = mysql_fetch_array($sqlUserMB)) {
            $userMainBranch[] = $rowUserMB['id'];
        }
    }
    else {
        $userMainBranch[] = 0;
    }
    $conAgency .= ' AND t_agents.main_branch_id = ' . $_POST['main_branch'];
}
else {
    $sqlUserMB = mysql_query("SELECT id FROM users WHERE is_active = 1 AND main_branch_id IS NOT NULL");
    if (mysql_num_rows($sqlUserMB)) {
        while ($rowUserMB = mysql_fetch_array($sqlUserMB)) {
            $userMainBranch[] = $rowUserMB['id'];
        }
    }
    else {
        $userMainBranch[] = 0;
    }
    $conAgency .= ' AND t_agents.main_branch_id IN (SELECT main_branch_id FROM users WHERE id = ' . $user['User']['id'] . ')';
}
$condtion = '';
$conPhone = '';
$userName = '';
$condLucky = '';
if ($_POST['user_select'] != '') {
    $sqlUser = mysql_query("SELECT CONCAT_WS(' ',first_name,last_name) FROM users WHERE id = " . $_POST['user_select']);
    $rowUser = mysql_fetch_array($sqlUser);
    $userName = $rowUser[0];
    $condtion .= ' AND t_tickets.created_by = ' . $_POST['user_select'];
    $conPhone .= ' AND t_tickets.confirm_by = ' . $_POST['user_select'];
    $conAgency .= ' AND t_tickets.confirm_by = ' . $_POST['user_select'];
    $condLucky .= ' AND lucky_tickets.created_by = ' . $_POST['user_select'];
}
else {
    $dateNow = strtotime(date("Y-m-d"));
    $dateSt = strtotime("2018-06-01");
    if ($dateNow < $dateSt) {
        if ($_POST['branch'] != '') {
            $condtion .= " AND t_tickets.created_by IN (" . implode(",", $userCreated) . ")";
            $conPhone .= " AND t_tickets.confirm_by IN (" . implode(",", $userConfirm) . ")";
            $condLucky .= " AND lucky_tickets.created_by IN (" . implode(",", $userLucky) . ")";
        }
    }
    else {
        if ($_POST['branch'] != '') {
            $condtion .= " AND t_tickets.created_by IN (" . implode(",", $userMainBranch) . ") AND t_tickets.created_by IN (" . implode(",", $userCreated) . ")";
            $conPhone .= " AND t_tickets.confirm_by IN (" . implode(",", $userMainBranch) . ") AND t_tickets.confirm_by IN (" . implode(",", $userConfirm) . ")";
            $condLucky .= " AND lucky_tickets.created_by IN (" . implode(",", $userLucky) . ")";
        }
        else {
            $condtion .= " AND t_tickets.created_by IN (" . implode(",", $userMainBranch) . ")";
            $conPhone .= " AND t_tickets.confirm_by IN (" . implode(",", $userMainBranch) . ")";
            $condLucky .= " AND lucky_tickets.created_by IN (" . implode(",", $userMainBranch) . ")";
        }
    }
}
$excelTitle = "";
if ($userName != '') {
    $msg = '<b style="font-size: 18px;">របាយការណ៏ចំណូលសំរាប់ ' . $userName . '</b><br />';
    $excelTitle = "របាយការណ៏ចំណូលសំរាប់ " . $userName;
}
else {
    $msg = '<b style="font-size: 18px;">របាយការណ៏ចំណូល</b><br />';
    $excelTitle = "របាយការណ៏ចំណូល";
}
$filename = "public/report/collectByUserDetail.csv";
$fp = fopen($filename, "wb");
$excelContent = $excelTitle . "\n";
$logo = "";
if ($_POST['company'] != '') {
    if ($_POST['company'] == "1,2") {
        $msg .= 'ក្រុមហ៊ុន: VET Ticket<br/>';
        $excelContent .= "ក្រុមហ៊ុន : VET Ticket\n";
    }
    else {
        $sqlCompany = mysql_query("SELECT GROUP_CONCAT(name) AS name FROM companies WHERE id IN (" . $_POST['company'] . ")");
        $rowCompany = mysql_fetch_array($sqlCompany);
        $msg .= 'ក្រុមហ៊ុន: ' . $rowCompany[0] . '<br/>';
        $excelContent .= "ក្រុមហ៊ុន : " . $rowCompany[0] . "\n";
    }
    $condtion .= ' AND t_tickets.company_id IN (' . $_POST['company'] . ')';
    $conPhone .= ' AND t_tickets.company_id IN (' . $_POST['company'] . ')';
    $conAgency .= ' AND t_tickets.company_id IN (' . $_POST['company'] . ')';
    $condLucky .= ' AND t_tickets.company_id IN (' . $_POST['company'] . ')';
    $sqlLogo = mysql_query("SELECT photo FROM companies WHERE id IN (" . $_POST['company'] . ") LIMIT 1");
    $rowLogo = mysql_fetch_array($sqlLogo);
    $logo = $rowLogo[0];
}
else {
// $condtion   .= ' AND t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
// $conPhone   .= ' AND t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
// $conAgency  .= ' AND t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
}
if ($_POST['branch'] != '') {
    $sqlBranch = mysql_query("SELECT name FROM branches WHERE id = " . $_POST['branch']);
    $rowBranch = mysql_fetch_array($sqlBranch);
    $msg .= 'សាខា: ' . $rowBranch[0] . '<br/>';
    $excelContent .= "សាខា : " . $rowBranch[0] . "\n";
    $condtion .= ' AND t_tickets.branch_id = ' . $_POST['branch'];
    $conPhone .= ' AND t_tickets.branch_id = ' . $_POST['branch'];
    $conAgency .= ' AND t_tickets.branch_id = ' . $_POST['branch'];
    $condLucky .= ' AND t_tickets.branch_id = ' . $_POST['branch'];
}
if ($_POST['date_from'] != '') {
    $msg .= 'ថ្ងៃទី : ' . $_POST['date_from'];
    $excelContent .= "ថ្ងៃទី : " . $_POST['date_from'];
}
if ($_POST['date_to'] != '' && $_POST['date_to'] != $_POST['date_from']) {
    $msg .= ' ទៅ: ' . $_POST['date_to'];
    $excelContent .= " ទៅ: " . $_POST['date_to'] . "\n";
}
if ($_POST['main_branch'] != '') {
    $sqlMB = mysql_query("SELECT name FROM main_branches WHERE id = " . $_POST['main_branch']);
    $rowMB = mysql_fetch_array($sqlMB);
    $msg .= '<br/ >សាខាដើម: ' . $rowMB['name'];
    $excelContent .= "សាខាដើម : " . $rowMB['name'] . "\n";
    $condtion .= ' AND t_tickets.main_branch_id = ' . $_POST['main_branch'];
    $conPhone .= ' AND t_tickets.main_branch_id = ' . $_POST['main_branch'];
    $condLucky .= ' AND lucky_tickets.main_branch_id = ' . $_POST['main_branch'];
}
$msg .= '<br />ថ្ងៃបោះពុម្ព: ' . date("d/m/Y H:i:s");
echo $this->element('/print/header-report', array('msg' => $msg, 'logo' => $logo));
$excelContent .= "\nWalk In Revenue";
$excelContent .= "\nNo\tType\tTicket Code\tBooking Date\tTravel Date\tDeparture Time\tDirection\tType\tSeat No\tEdited\tDiscount\tAmount";
$symbol = '';
$sqlSym = mysql_query("SELECT symbol FROM currency_centers WHERE id = 1;");
$rowSym = mysql_fetch_array($sqlSym);
$symbol = $rowSym[0];
$totalIncome = 0;
?>
        <div id="dynamic">
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 5px; font-size: 12px;">Walk In Revenue</td>
                    </tr>
                    <tr>
                        <th class="first" style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Type</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Ticket Code</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Booking Date</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Travel Date</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Departure Time</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Direction</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Type</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Seat No</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Edited</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Discount</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Luck Ticket</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Amount</th>
                    </tr>
                    <?php
$seatNumber = 1;
$ticketId = 0;
$totalPaid = 0;
$index = 0;
$sqlAct = mysql_query("SELECT IFNULL(label_number, seat_number) AS seat, t_tickets.id, t_journeys.description, t_tickets.date, t_tickets.code, t_tickets.journey_date, t_tickets.journey_time, t_tickets.is_round_trip, (IFNULL(t_ticket_details.total_amount, 0) - IFNULL(t_ticket_details.discount, 0) + IFNULL(t_ticket_details.vat_price, 0) - IFNULL(t_ticket_details.total_amt_change, 0)) AS total_amount, t_tickets.balance, IFNULL(t_ticket_details.discount, 0) AS discount, t_tickets.is_open_date, t_tickets.type, t_tickets.edit_from, t_tickets.modified_by, t_ticket_details.is_free, t_tickets.lucky_draw_fee, t_ticket_details.total_amt_change 
                                           FROM t_ticket_details 
                                           INNER JOIN t_tickets ON t_tickets.id = t_ticket_details.t_ticket_id 
                                           INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id 
                                           WHERE t_ticket_details.is_active = 1 AND t_tickets.status = 2 AND t_tickets.type = 1 AND t_tickets.confirm_by IS NULL AND t_tickets.offline_project_id = " . $user['User']['offline_project_id'] . " AND journey_type IN (1, 2) AND t_tickets.date >= '" . dateConvert($_POST['date_from']) . "' AND t_tickets.date <= '" . dateConvert($_POST['date_to']) . "'" . $condtion . " 
                                           GROUP BY t_ticket_details.id
                                           UNION ALL
                                           SELECT IFNULL(label_number, seat_number) AS seat, t_tickets.id, t_journeys.description, t_tickets.date, t_tickets.code, t_tickets.journey_date, t_tickets.journey_time, t_tickets.is_round_trip, (IFNULL(t_ticket_details.total_amount, 0) - IFNULL(t_ticket_details.discount, 0) + IFNULL(t_ticket_details.vat_price, 0) - IFNULL(t_ticket_details.total_amt_change, 0)) AS total_amount, t_tickets.balance, IFNULL(t_ticket_details.discount, 0) AS discount, t_tickets.is_open_date, t_tickets.type, t_tickets.edit_from, t_tickets.modified_by, t_ticket_details.is_free, t_tickets.lucky_draw_fee, t_ticket_details.total_amt_change 
                                           FROM t_ticket_detail_3months AS t_ticket_details 
                                           INNER JOIN t_ticket_3months AS t_tickets ON t_tickets.id = t_ticket_details.t_ticket_id 
                                           INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id 
                                           WHERE t_ticket_details.is_active = 1 AND t_tickets.status = 2 AND t_tickets.type = 1 AND t_tickets.confirm_by IS NULL AND t_tickets.offline_project_id = " . $user['User']['offline_project_id'] . " AND journey_type IN (1, 2) AND t_tickets.date >= '" . dateConvert($_POST['date_from']) . "' AND t_tickets.date <= '" . dateConvert($_POST['date_to']) . "'" . $condtion . " 
                                           GROUP BY t_ticket_details.id;");
if (mysql_num_rows($sqlAct)) {
    while ($rowAct = mysql_fetch_array($sqlAct)) {
        $luckTicket = 0;
        if ($ticketId != $rowAct['id']) {
            $seatNumber = 1;
            $ticketId = $rowAct['id'];
        }
        if ($rowAct['is_free'] == 0) {
            $sqlLucky = mysql_query("SELECT * FROM lucky_tickets WHERE t_ticket_id = " . $rowAct['id']);
            if (!mysql_num_rows($sqlLucky)) {
                if ($rowAct['lucky_draw_fee'] > 0) {
                    $luckTicket = 0.25;
                }
            }
            if ($rowAct['total_amount'] > 0) {
                $paid = $rowAct['total_amount'] + $luckTicket;
                $totalPaid += $paid;
                $totalIncome += $paid;
            }
            else {
                $paid = 0;
            }
        }
?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo ++$index;
        $excelContent .= "\n" . $index;
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        $tripType = 'Single Trip';
        if ($rowAct['is_round_trip'] == 1) {
            $tripType = 'Round Trip';
        }
        echo $tripType;
        $excelContent .= "\t" . $tripType;
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo $rowAct['code'] . "-" . $seatNumber;
        if ($rowAct['total_amt_change'] > 0) {
            echo " (Shift)";
        }
        $excelContent .= "\t" . $rowAct['code'] . "-" . $seatNumber;
        if ($rowAct['total_amt_change'] > 0) {
            $excelContent .= " (Shift)";
        }
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo dateShort($rowAct['date']);
        $excelContent .= "\t" . dateShort($rowAct['date']);
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        if ($rowAct['is_open_date'] == 1) {
            echo 'Open Date';
            $excelContent .= "\tOpen Date";
        }
        else {
            echo dateShort($rowAct['journey_date']);
            $excelContent .= "\t" . dateShort($rowAct['journey_date']);
        }
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        if ($rowAct['is_open_date'] == 1) {
            echo 'Open Date';
            $excelContent .= "\tOpen Date";
        }
        else {
            echo date("h:i A", strtotime($rowAct['journey_time']));
            $excelContent .= "\t" . date("h:i A", strtotime($rowAct['journey_time']));
        }
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php
        echo $rowAct['description'];
        $excelContent .= "\t" . $rowAct['description'];
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php
        if ($rowAct['type'] == 1) {
            echo 'Walk In';
            $excelContent .= "\tWalk In";
        }
        else if ($rowAct['type'] == 2) {
            echo 'VIP';
            $excelContent .= "\tVIP";
        }
        else if ($rowAct['type'] == 5) {
            echo 'Agency';
        }
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo $rowAct['seat'];
        $excelContent .= "\t" . $rowAct['seat'];
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php
        $excelContent .= "\t";
        if (!empty($rowAct['edit_from']) && !empty($rowAct['modified_by'])) {
            $sqlEdit = mysql_query("SELECT CONCAT_WS(' ', first_name, last_name) FROM users WHERE id = " . $rowAct['modified_by']);
            $rowEdit = mysql_fetch_array($sqlEdit);
            echo $rowEdit[0];
            $excelContent .= $rowEdit[0];
        }
?>
                        </td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">
                            <?php
        $excelContent .= "\t";
        if ($rowAct['is_free'] == 1) {
            echo "";
        }
        else {
            $excelContent .= number_format($rowAct['discount'], 2);
?>
                            <span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($rowAct['discount'], 2); ?>
                            <?php
        }
?>
                        </td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">
                            <?php
        $excelContent .= "\t";
        if ($rowAct['is_free'] == 1) {
            echo "";
        }
        else {
            $excelContent .= $luckTicket;
?>
                            <span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($luckTicket, 2); ?>
                            <?php
        }
?>
                        </td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">
                            <?php
        if ($rowAct['is_free'] == 1) {
            echo " (Free)";
            $excelContent .= "\tFree";
        }
        else {
            $excelContent .= "\t" . number_format($paid, 2);
?>
                            <span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($paid, 2); ?>
                            <?php
        }
?>
                        </td>
                    </tr>
                    <?php
        $seatNumber++;
    }
}
else {
    $excelContent .= "\n" . TABLE_NO_MATCHING_RECORD;
?>
                    <tr>
                        <td colspan="13" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
                    </tr>
                    <?php
}
$excelContent .= "\n\t\t\t\t\t\t\t\t\t\tTotal\t" . number_format($totalPaid, 2);
?>
                    <tr>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="12">Total</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($totalPaid, 2); ?></td>
                    </tr>
                </tbody>
            </table>
            <br />
            <?php
$excelContent .= "\nOpen Date Revenue";
$excelContent .= "\nNo\tTicket Code\tBooking Date\tTravel Date\tDirection\tDiscount\tAmount";
?>
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 5px; font-size: 12px;">Open Date Revenue</td>
                    </tr>
                    <tr>
                        <th class="first" style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Ticket Code</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Booking Date</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Travel Date</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Direction</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Discount</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Lucky Ticket</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Amount</th>
                    </tr>
                    <?php
$seatNumber = 1;
$ticketId = 0;
$totalPaid = 0;
$index = 0;
$sqlAct = mysql_query("SELECT t_tickets.id, t_journeys.description, t_tickets.date, t_tickets.code,  (IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0) - IFNULL(t_tickets.total_change, 0)) AS total_amount, IFNULL(t_tickets.discount_amount, 0) AS discount_amount, t_tickets.is_open_date, t_tickets.lucky_draw_fee 
                                           FROM t_tickets
                                           INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id 
                                           WHERE t_tickets.status = 2 AND t_tickets.is_open_date = 1 AND t_tickets.confirm_by IS NULL AND t_tickets.offline_project_id = " . $user['User']['offline_project_id'] . " AND journey_type IN (1, 2) AND t_tickets.date >= '" . dateConvert($_POST['date_from']) . "' AND t_tickets.date <= '" . dateConvert($_POST['date_to']) . "'" . $condtion . "
                                           UNION ALL
                                           SELECT t_tickets.id, t_journeys.description, t_tickets.date, t_tickets.code,  (IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0) - IFNULL(t_tickets.total_change, 0)) AS total_amount, IFNULL(t_tickets.discount_amount, 0) AS discount_amount, t_tickets.is_open_date, t_tickets.lucky_draw_fee 
                                           FROM t_ticket_3months AS t_tickets
                                           INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id 
                                           WHERE t_tickets.status = 2 AND t_tickets.is_open_date = 1 AND t_tickets.confirm_by IS NULL AND t_tickets.offline_project_id = " . $user['User']['offline_project_id'] . " AND journey_type IN (1, 2) AND t_tickets.date >= '" . dateConvert($_POST['date_from']) . "' AND t_tickets.date <= '" . dateConvert($_POST['date_to']) . "'" . $condtion);
if (mysql_num_rows($sqlAct)) {
    while ($rowAct = mysql_fetch_array($sqlAct)) {
        $luckyTicket = 0;
        if ($rowAct['lucky_draw_fee'] > 0) {
            $luckyTicket = 0.25;
        }
        $paid = $rowAct['total_amount'] + $luckyTicket;
        $totalPaid += $paid;
        $totalIncome += $paid;
?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo ++$index;
        $excelContent .= "\n" . $index;
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo $rowAct['code'];
        $excelContent .= "\t" . $rowAct['code'];
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo dateShort($rowAct['date']);
        $excelContent .= "\t" . dateShort($rowAct['date']);
        $excelContent .= "\tOpen Date";
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">Open Date</td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php
        echo $rowAct['description'];
        $excelContent .= "\t" . $rowAct['description']; ?>
                        </td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">
                            <span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span>
                            <?php
        echo number_format($rowAct['discount_amount'], 2);
        $excelContent .= "\t" . number_format($rowAct['discount_amount'], 2);
?>
                        </td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">
                            <span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span>
                            <?php

        echo number_format($luckyTicket, 2);
        $excelContent .= "\t" . number_format($luckyTicket, 2);
?>
                        </td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span>
                            <?php
        echo number_format($paid, 2);
        $excelContent .= "\t" . number_format($paid, 2);
?>
                        </td>
                    </tr>
                    <?php
    }
}
else {
    $excelContent .= "\n" . TABLE_NO_MATCHING_RECORD;
?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
                    </tr>
                    <?php
}
$excelContent .= "\n\t\t\t\t\tTotal\t" . number_format($totalPaid, 2);
?>
                    <tr>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="7">Total</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($totalPaid, 2); ?></td>
                    </tr>
                </tbody>
            </table>
            <br />
            <?php
$excelContent .= "\nPhone Call Revenue";
$excelContent .= "\nNo\tTicket Code\tBooking Date\tTravel Date\tDeparture Time\tDirection\tType\tSeat No\tDiscount\tAmount";
?>
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 5px; font-size: 12px;">Phone Call Revenue</td>
                    </tr>
                    <tr>
                        <th class="first" style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Ticket Code</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Booking Date</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Travel Date</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Departure Time</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Direction</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Type</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Seat No</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Discount</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Luck Ticket</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Amount</th>
                    </tr>
                    <?php
$totalPaid = 0;
$index = 0;
$sqlAct = mysql_query("SELECT t_tickets.id, t_journeys.description, t_tickets.date, t_tickets.code, t_tickets.journey_date, t_tickets.journey_time, (IFNULL(t_ticket_details.total_amount, 0) - IFNULL(t_ticket_details.discount, 0) + IFNULL(t_ticket_details.vat_price, 0) - IFNULL(t_ticket_details.total_amt_change, 0)) AS total_amount, t_tickets.balance, IFNULL(t_ticket_details.discount, 0) AS discount, t_tickets.is_open_date, t_tickets.type, t_tickets.edit_from, t_tickets.modified_by, t_tickets.lucky_draw_fee 
                                           FROM t_ticket_details 
                                           INNER JOIN t_tickets ON t_tickets.id = t_ticket_details.t_ticket_id 
                                           INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id 
                                           WHERE t_ticket_details.is_active = 1 AND t_tickets.status = 2 AND t_tickets.type = 1 AND t_tickets.confirm_by IS NOT NULL AND t_tickets.offline_project_id = " . $user['User']['offline_project_id'] . " AND journey_type IN (1, 2) AND t_tickets.date >= '" . dateConvert($_POST['date_from']) . "' AND t_tickets.date <= '" . dateConvert($_POST['date_to']) . "'" . $condtion . " GROUP BY t_tickets.id
                                           UNION ALL
                                           SELECT t_tickets.id, t_journeys.description, t_tickets.date, t_tickets.code, t_tickets.journey_date, t_tickets.journey_time, (IFNULL(t_ticket_details.total_amount, 0) - IFNULL(t_ticket_details.discount, 0) + IFNULL(t_ticket_details.vat_price, 0) - IFNULL(t_ticket_details.total_amt_change, 0)) AS total_amount, t_tickets.balance, IFNULL(t_ticket_details.discount, 0) AS discount, t_tickets.is_open_date, t_tickets.type, t_tickets.edit_from, t_tickets.modified_by, t_tickets.lucky_draw_fee 
                                           FROM t_ticket_detail_3months AS t_ticket_details 
                                           INNER JOIN t_ticket_3months AS t_tickets ON t_tickets.id = t_ticket_details.t_ticket_id 
                                           INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id 
                                           WHERE t_ticket_details.is_active = 1 AND t_tickets.status = 2 AND t_tickets.type = 1 AND t_tickets.confirm_by IS NOT NULL AND t_tickets.offline_project_id = " . $user['User']['offline_project_id'] . " AND journey_type IN (1, 2) AND t_tickets.date >= '" . dateConvert($_POST['date_from']) . "' AND t_tickets.date <= '" . dateConvert($_POST['date_to']) . "'" . $condtion . " GROUP BY t_tickets.id;");
if (mysql_num_rows($sqlAct)) {
    while ($rowAct = mysql_fetch_array($sqlAct)) {
        $luckyTicket = 0;
        if ($rowAct['lucky_draw_fee'] > 0) {
            $luckyTicket = 0.25;
        }
        $paid = $rowAct['total_amount'] + $luckyTicket;
        $totalPaid += $paid;
        $totalIncome += $paid;
        $sqlSeat = mysql_query("SELECT GROUP_CONCAT(seat_number) FROM t_ticket_details WHERE t_ticket_id = " . $rowAct['id']);
        $rowSeat = mysql_fetch_array($sqlSeat);
?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo ++$index;
        $excelContent .= "\n" . $index;
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo $rowAct['code'];
        $excelContent .= "\t" . $rowAct['code'];
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo dateShort($rowAct['date']);
        $excelContent .= "\t" . dateShort($rowAct['date']);
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        if ($rowAct['is_open_date'] == 1) {
            echo 'Open Date';
            $excelContent .= "\tOpen Date";
        }
        else {
            echo dateShort($rowAct['journey_date']);
            $excelContent .= "\t" . dateShort($rowAct['journey_date']);
        }
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        if ($rowAct['is_open_date'] == 1) {
            echo 'Open Date';
            $excelContent .= "\tOpen Date";
        }
        else {
            echo date("h:i A", strtotime($rowAct['journey_time']));
            $excelContent .= "\t" . date("h:i A", strtotime($rowAct['journey_time']));
        }
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php
        echo $rowAct['description'];
        $excelContent .= "\t" . $rowAct['description'];
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php
        if ($rowAct['type'] == 1) {
            echo 'Walk In';
            $excelContent .= "\tWalk In";
        }
        else if ($rowAct['type'] == 2) {
            echo 'VIP';
            $excelContent .= "\tVIP";
        }
        else if ($rowAct['type'] == 5) {
            echo 'Agency';
            $excelContent .= "\tAgency";
        }
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo $rowSeat[0];
        $excelContent .= "\t" . $rowSeat[0];
?>
                        </td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">
                            <span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span>
                            <?php
        echo number_format($rowAct['discount'], 2);
        $excelContent .= "\t" . number_format($rowAct['discount'], 2);
?>
                        </td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">
                            <span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span>
                            <?php
        echo number_format($luckyTicket, 2);
        $excelContent .= "\t" . number_format($luckyTicket, 2);
?>
                        </td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">
                            <span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span>
                            <?php
        echo number_format($paid, 2);
        $excelContent .= "\t" . number_format($paid, 2);
?>
                        </td>
                    </tr>
                    <?php
    }
}
else {
    $excelContent .= "\n" . TABLE_NO_MATCHING_RECORD;
?>
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
                    </tr>
                    <?php
}
$excelContent .= "\n\t\t\t\t\t\t\t\tTotal\t" . number_format($totalPaid, 2);
?>
                    <tr>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="10">Total</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($totalPaid, 2); ?></td>
                    </tr>
                </tbody>
            </table>
            <br />
            <?php
$excelContent .= "\nLucky Ticket Revenue";
$excelContent .= "\nNo\tTicket Code\tDate\tTravel Date\tDeparture Time\tDirection\tType\tSeat No\tLuck Ticket";
?>
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 5px; font-size: 12px;">Lucky Ticket Revenue</td>
                    </tr>
                    <tr>
                        <th class="first" style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Ticket Code</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Date</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Travel Date</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Departure Time</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Direction</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Seat No</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Luck Ticket</th>
                    </tr>
                    <?php
$totalPaid = 0;
$index = 0;
$sqlAct = mysql_query("SELECT t_tickets.id, t_journeys.description, t_tickets.date, t_tickets.code, t_tickets.journey_date, t_tickets.journey_time, t_tickets.lucky_draw_fee, t_tickets.is_open_date, lucky_tickets.created
                                           FROM t_ticket_details 
                                           INNER JOIN t_tickets ON t_tickets.id = t_ticket_details.t_ticket_id 
                                           INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id 
                                           INNER JOIN lucky_tickets ON lucky_tickets.t_ticket_id = t_tickets.id
                                           WHERE t_ticket_details.is_active = 1 AND t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND DATE(lucky_tickets.created) >= '" . dateConvert($_POST['date_from']) . "' AND DATE(lucky_tickets.created) <= '" . dateConvert($_POST['date_to']) . "'" . $condLucky . " GROUP BY t_tickets.id
                                           UNION ALL
                                           SELECT t_tickets.id, t_journeys.description, t_tickets.date, t_tickets.code, t_tickets.journey_date, t_tickets.journey_time, t_tickets.lucky_draw_fee, t_tickets.is_open_date, lucky_tickets.created
                                           FROM t_ticket_detail_3months AS t_ticket_details 
                                           INNER JOIN t_ticket_3months AS t_tickets ON t_tickets.id = t_ticket_details.t_ticket_id 
                                           INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id 
                                           INNER JOIN lucky_tickets ON lucky_tickets.t_ticket_id = t_tickets.id
                                           WHERE t_ticket_details.is_active = 1 AND t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND DATE(lucky_tickets.created) >= '" . dateConvert($_POST['date_from']) . "' AND DATE(lucky_tickets.created) <= '" . dateConvert($_POST['date_to']) . "'" . $condLucky . " GROUP BY t_tickets.id;");
if (mysql_num_rows($sqlAct)) {
    while ($rowAct = mysql_fetch_array($sqlAct)) {
        $luckyTicket = 0;
        if ($rowAct['lucky_draw_fee'] > 0) {
            $luckyTicket = 0.25;
        }
        $paid = $luckyTicket;
        $totalPaid += $paid;
        $totalIncome += $paid;
        $sqlSeat = mysql_query("SELECT GROUP_CONCAT(seat_number) FROM t_ticket_details WHERE t_ticket_id = " . $rowAct['id']);
        $rowSeat = mysql_fetch_array($sqlSeat);
?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo ++$index;
        $excelContent .= "\n" . $index;
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo $rowAct['code'];
        $excelContent .= "\t" . $rowAct['code'];
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo dateShort($rowAct['created']);
        $excelContent .= "\t" . dateShort($rowAct['created']);
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        if ($rowAct['is_open_date'] == 1) {
            echo 'Open Date';
            $excelContent .= "\tOpen Date";
        }
        else {
            echo dateShort($rowAct['journey_date']);
            $excelContent .= "\t" . dateShort($rowAct['journey_date']);
        }
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        if ($rowAct['is_open_date'] == 1) {
            echo 'Open Date';
            $excelContent .= "\tOpen Date";
        }
        else {
            echo date("h:i A", strtotime($rowAct['journey_time']));
            $excelContent .= "\t" . date("h:i A", strtotime($rowAct['journey_time']));
        }
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php
        echo $rowAct['description'];
        $excelContent .= "\t" . $rowAct['description'];
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo $rowSeat[0];
        $excelContent .= "\t" . $rowSeat[0];
?>
                        </td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">
                            <span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span>
                            <?php
        echo number_format($luckyTicket, 2);
        $excelContent .= "\t" . number_format($luckyTicket, 2);
?>
                        </td>
                    </tr>
                    <?php
    }
}
else {
    $excelContent .= "\n" . TABLE_NO_MATCHING_RECORD;
?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
                    </tr>
                    <?php
}
$excelContent .= "\n\t\t\t\t\t\tTotal\t" . number_format($totalPaid, 2);
?>
                    <tr>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="7">Total</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($totalPaid, 2); ?></td>
                    </tr>
                </tbody>
            </table>
            <br />
            <?php
$excelContent .= "\nOffline Agency Sales";
$excelContent .= "\nNo\tTicket Code\tBooking Date\tTravel Date\tDeparture Time\tDirection\tType\tSeat No\tNet Price";
?>
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 5px; font-size: 12px;">Agency Offline Postpaid Sales</td>
                    </tr>
                    <tr>
                        <th class="first" style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Ticket Code</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Booking Date</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Travel Date</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Departure Time</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Direction</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Type</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Seat No</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Net Price</th>
                    </tr>
                    <?php
$totalPaid = 0;
$index = 0;
$sqlAct = mysql_query("SELECT t_tickets.id, t_journeys.description, t_tickets.date, t_tickets.code, t_tickets.journey_date, t_tickets.journey_time, IFNULL(agency_balances.debit, 0) AS total_amount, t_agents.code AS agency_code, t_agents.name AS agency_name
                                           FROM t_tickets 
                                           INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.type = 2 AND t_agents.payment = 2 AND t_agents.id != 55
                                           INNER JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
                                           INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id
                                           WHERE t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND t_tickets.date >= '" . dateConvert($_POST['date_from']) . "' AND t_tickets.date <= '" . dateConvert($_POST['date_to']) . "'" . $conAgency . " 
                                           GROUP BY t_tickets.id
                                           UNION ALL
                                           SELECT t_tickets.id, t_journeys.description, t_tickets.date, t_tickets.code, t_tickets.journey_date, t_tickets.journey_time, IFNULL(agency_balances.debit, 0) AS total_amount, t_agents.code AS agency_code, t_agents.name AS agency_name
                                           FROM t_ticket_3months AS t_tickets 
                                           INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.type = 2 AND t_agents.payment = 2 AND t_agents.id != 55
                                           INNER JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
                                           INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id
                                           WHERE t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND t_tickets.date >= '" . dateConvert($_POST['date_from']) . "' AND t_tickets.date <= '" . dateConvert($_POST['date_to']) . "'" . $conAgency . " 
                                           GROUP BY t_tickets.id;");
if (mysql_num_rows($sqlAct)) {
    while ($rowAct = mysql_fetch_array($sqlAct)) {
        $paid = $rowAct['total_amount'];
        $totalPaid += $paid;
        $totalIncome += $paid;
        $sqlSeat = mysql_query("SELECT GROUP_CONCAT(seat_number) FROM t_ticket_details WHERE t_ticket_id = " . $rowAct['id']);
        $rowSeat = mysql_fetch_array($sqlSeat);
?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo ++$index;
        $excelContent .= "\n" . $index;
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo $rowAct['code'];
        $excelContent .= "\t" . $rowAct['code'];
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo dateShort($rowAct['date']);
        $excelContent .= "\t" . dateShort($rowAct['date']);
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo dateShort($rowAct['journey_date']);
        $excelContent .= "\t" . dateShort($rowAct['journey_date']);
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo date("h:i A", strtotime($rowAct['journey_time']));
        $excelContent .= "\t" . date("h:i A", strtotime($rowAct['journey_time']));
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php
        echo $rowAct['description'];
        $excelContent .= "\t" . $rowAct['description'];
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php
        echo 'Agency';
        $excelContent .= "\tAgency";
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo $rowSeat[0];
        $excelContent .= "\t" . $rowSeat[0];
?>
                        </td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span>
                            <?php
        echo number_format($paid, 2);
        $excelContent .= "\t" . number_format($paid, 2);
?>
                        </td>
                    </tr>
                    <?php
    }
}
else {
    $excelContent .= "\n" . TABLE_NO_MATCHING_RECORD;
?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
                    </tr>
                    <?php
}
$excelContent .= "\n\t\t\t\t\t\t\tTotal\t" . number_format($totalPaid, 2);
?>
                    <tr>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="8">Total</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($totalPaid, 2); ?></td>
                    </tr>
                </tbody>
            </table>
            <br />
            <?php
$excelContent .= "\nOnline Agency Postpaid Sales";
$excelContent .= "\nNo\tTicket Code\tBooking Date\tTravel Date\tDeparture Time\tDirection\tType\tSeat No\tNet Price";
?>
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 5px; font-size: 12px;">Agency Online Postpaid Sales</td>
                    </tr>
                    <tr>
                        <th class="first" style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Agency Name</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Ticket Code</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Booking Date</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Travel Date</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Departure Time</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Direction</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Seat No</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Net Price</th>
                    </tr>
                    <?php
$totalPaid = 0;
$index = 0;
$sqlAct = mysql_query("SELECT t_tickets.id, t_journeys.description, t_tickets.date, t_tickets.code, t_tickets.journey_date, t_tickets.journey_time, IFNULL(agency_balances.debit, 0) AS total_amount, t_agents.code AS agency_code, t_agents.name AS agency_name
                                           FROM t_tickets 
                                           INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.type = 1 AND t_agents.payment = 2 AND t_agents.id != 55
                                           INNER JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
                                           INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id
                                           WHERE t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND t_tickets.date >= '" . dateConvert($_POST['date_from']) . "' AND t_tickets.date <= '" . dateConvert($_POST['date_to']) . "'" . $conAgency . " 
                                           GROUP BY t_tickets.id
                                           UNION ALL
                                           SELECT t_tickets.id, t_journeys.description, t_tickets.date, t_tickets.code, t_tickets.journey_date, t_tickets.journey_time, IFNULL(agency_balances.debit, 0) AS total_amount, t_agents.code AS agency_code, t_agents.name AS agency_name
                                           FROM t_ticket_3months AS t_tickets 
                                           INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.type = 1 AND t_agents.payment = 2 AND t_agents.id != 55
                                           INNER JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
                                           INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id
                                           WHERE t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND t_tickets.date >= '" . dateConvert($_POST['date_from']) . "' AND t_tickets.date <= '" . dateConvert($_POST['date_to']) . "'" . $conAgency . " 
                                           GROUP BY t_tickets.id;");
if (mysql_num_rows($sqlAct)) {
    while ($rowAct = mysql_fetch_array($sqlAct)) {
        $paid = $rowAct['total_amount'];
        $totalPaid += $paid;
        $totalIncome += $paid;
        $sqlSeat = mysql_query("SELECT GROUP_CONCAT(seat_number) FROM t_ticket_details WHERE t_ticket_id = " . $rowAct['id']);
        $rowSeat = mysql_fetch_array($sqlSeat);
?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo ++$index;
        $excelContent .= "\n" . $index;
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo $rowAct['agency_code'] . ' - ' . $rowAct['agency_name'];
        $excelContent .= "\t" . $rowAct['agency_code'] . ' - ' . $rowAct['agency_name'];
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo $rowAct['code'];
        $excelContent .= "\t" . $rowAct['code'];
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo dateShort($rowAct['date']);
        $excelContent .= "\t" . dateShort($rowAct['date']);
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo dateShort($rowAct['journey_date']);
        $excelContent .= "\t" . dateShort($rowAct['journey_date']);
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo date("h:i A", strtotime($rowAct['journey_time']));
        $excelContent .= "\t" . date("h:i A", strtotime($rowAct['journey_time']));
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php
        echo $rowAct['description'];
        $excelContent .= "\t" . $rowAct['description'];
?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php
        echo $rowSeat[0];
        $excelContent .= "\t" . $rowSeat[0];
?>
                        </td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span>
                            <?php
        echo number_format($paid, 2);
        $excelContent .= "\t" . number_format($paid, 2);
?>
                        </td>
                    </tr>
                    <?php
    }
}
else {
    $excelContent .= "\n" . TABLE_NO_MATCHING_RECORD;
?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
                    </tr>
                    <?php
}
$excelContent .= "\n\t\t\t\t\t\t\tTotal\t" . number_format($totalPaid, 2);
$excelContent .= "\n\t\t\t\t\t\t\tGrand Total\t" . number_format($totalIncome, 2);
?>
                    <tr>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="8">Total</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($totalPaid, 2); ?></td>
                    </tr>
                    <tr>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="8">Grand Total</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($totalIncome, 2); ?></td>
                    </tr>
                </tbody>
            </table>
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
$excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp, $excelContent);
fclose($fp);
?>