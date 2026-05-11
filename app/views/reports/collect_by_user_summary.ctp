<?php
include('includes/function.php');
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
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
    });
</script>
<div class="leftPanel">
    <div id="<?php echo $printArea; ?>">
        <?php
        if($_POST['branch'] != ''){
            $userCreated = array();
            $sqlUserC = mysql_query("SELECT created_by FROM t_tickets WHERE branch_id = ".$_POST['branch']." AND main_branch_id = ".$_POST['main_branch']." AND status = 2 AND date >= '".dateConvert($_POST['date_from'])."' AND date <= '".dateConvert($_POST['date_to'])."' GROUP BY created_by");
            if(mysql_num_rows($sqlUserC)){
                while($rowUserC = mysql_fetch_array($sqlUserC)){
                    $userCreated[] = $rowUserC['created_by'];
                }
            } else {
                $userCreated[] = 0;
            }
            $userConfirm = array();
            $sqlUserCF = mysql_query("SELECT confirm_by FROM t_tickets WHERE branch_id = ".$_POST['branch']." AND main_branch_id = ".$_POST['main_branch']." AND status = 2 AND type = 2 AND date >= '".dateConvert($_POST['date_from'])."' AND date <= '".dateConvert($_POST['date_to'])."' GROUP BY confirm_by");
            if(mysql_num_rows($sqlUserCF)){
                while($rowUserCF = mysql_fetch_array($sqlUserCF)){
                    $userConfirm[] = $rowUserCF['created_by'];
                }
            } else {
                $userConfirm[] = 0;
            }

            $userLucky = array();
            $sqlUserLucky = mysql_query("SELECT created_by FROM lucky_tickets WHERE main_branch_id = ".$_POST['main_branch']." AND DATE(created) >= '".dateConvert($_POST['date_from'])."' AND DATE(created) <= '".dateConvert($_POST['date_to'])."' GROUP BY created_by");
            if(mysql_num_rows($sqlUserLucky)){
                while($rowUserLucky = mysql_fetch_array($sqlUserLucky)){
                    $userLucky[] = $rowUserLucky['created_by'];
                }
            } else {
                $userLucky[] = 0;
            }
        }
        $conAgency = '';
        $userMainBranch = array();
        if($_POST['main_branch'] != ''){
            $sqlUserMB = mysql_query("SELECT id FROM users WHERE is_active = 1 AND main_branch_id = ".$_POST['main_branch']);
            if(mysql_num_rows($sqlUserMB)){
                while($rowUserMB = mysql_fetch_array($sqlUserMB)){
                    $userMainBranch[] = $rowUserMB['id'];
                }
            } else {
                $userMainBranch[] = 0;
            }
            $conAgency .= ' AND t_agents.main_branch_id = '.$_POST['main_branch'];
        } else {
            $sqlUserMB = mysql_query("SELECT id FROM users WHERE is_active = 1 AND main_branch_id IS NOT NULL");
            if(mysql_num_rows($sqlUserMB)){
                while($rowUserMB = mysql_fetch_array($sqlUserMB)){
                    $userMainBranch[] = $rowUserMB['id'];
                }
            } else {
                $userMainBranch[] = 0;
            }
            $conAgency .= ' AND t_agents.main_branch_id IN (SELECT main_branch_id FROM users WHERE id = '.$user['User']['id'].')';
        }
        $condtion  = '';
        $conPhone  = '';
        $userName  = '';
        $condLucky = '';
        if($_POST['user_select'] != ''){
            $sqlUser = mysql_query("SELECT CONCAT_WS(' ',first_name,last_name) FROM users WHERE id = ".$_POST['user_select']);
            $rowUser = mysql_fetch_array($sqlUser);
            $userName = $rowUser[0];
            $condtion  .= ' AND t_tickets.created_by = '.$_POST['user_select'];
            $conPhone  .= ' AND t_tickets.confirm_by = '.$_POST['user_select'];
            $conAgency .= ' AND t_tickets.confirm_by = '.$_POST['user_select'];
            $condLucky .= ' AND lucky_tickets.created_by = '.$_POST['user_select'];
        } else {
            if($_POST['branch']!=''){
                $condtion  .= " AND t_tickets.created_by IN (".implode(",", $userMainBranch).") AND t_tickets.created_by IN (".implode(",", $userCreated).")";
                $conPhone  .= " AND t_tickets.confirm_by IN (".implode(",", $userMainBranch).") AND t_tickets.confirm_by IN (".implode(",", $userConfirm).")";
                $condLucky  .= " AND lucky_tickets.created_by IN (".implode(",", $userLucky).")";
            } else {
                $condtion  .= " AND t_tickets.created_by IN (".implode(",", $userMainBranch).")";
                $conPhone  .= " AND t_tickets.confirm_by IN (".implode(",", $userMainBranch).")";
                $condLucky  .= " AND lucky_tickets.created_by IN (".implode(",", $userMainBranch).")";
            }
        }
        $msg  = '<b style="font-size: 18px;">របាយការណ៏ចំណូលសរុប</b><br />';
        $logo = "";
        if($_POST['company'] != '') {
            if($_POST['company'] == "1,2"){
                $msg .= 'ក្រុមហ៊ុន: VET Ticket<br/>';
            } else {
                $sqlCompany = mysql_query("SELECT GROUP_CONCAT(name) AS name FROM companies WHERE id IN (".$_POST['company'].")");
                $rowCompany = mysql_fetch_array($sqlCompany);
                $msg .= 'ក្រុមហ៊ុន: '.$rowCompany[0].'<br/>';
            }
            $condtion  .= ' AND t_tickets.company_id IN ('.$_POST['company'].')';
            $conPhone  .= ' AND t_tickets.company_id IN ('.$_POST['company'].')';
            $conAgency .= ' AND t_tickets.company_id IN ('.$_POST['company'].')';
            $condLucky  .= ' AND t_tickets.company_id IN ('.$_POST['company'].')';
            $sqlLogo = mysql_query("SELECT photo FROM companies WHERE id IN (".$_POST['company'].") LIMIT 1");
            $rowLogo = mysql_fetch_array($sqlLogo);
            $logo    = $rowLogo[0];
        } else {
            // $condtion  .= ' AND t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
            // $conPhone  .= ' AND t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
            // $conAgency .= ' AND t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
        }
        if($_POST['branch'] != '') {
            $sqlBranch = mysql_query("SELECT name FROM branches WHERE id = ".$_POST['branch']);
            $rowBranch = mysql_fetch_array($sqlBranch);
            $msg .= 'សាខា: '.$rowBranch[0].'<br/>';
            $condtion  .= ' AND t_tickets.branch_id = '.$_POST['branch'];
            $conPhone  .= ' AND t_tickets.branch_id = '.$_POST['branch'];
            $conAgency .= ' AND t_tickets.branch_id = '.$_POST['branch'];
            $condLucky  .= ' AND t_tickets.branch_id = '.$_POST['branch'];
        }
        if($_POST['date_from']!='') {
            $msg .= 'ថ្ងៃទី : '.$_POST['date_from'];
        }
        if($_POST['date_to']!='' && $_POST['date_to'] != $_POST['date_from']) {
            $msg .= ' ទៅ: '.$_POST['date_to'];
        }
        if($_POST['main_branch'] != '') {
            $sqlMB = mysql_query("SELECT name FROM main_branches WHERE id = ".$_POST['main_branch']);
            $rowMB = mysql_fetch_array($sqlMB);
            $msg .= '<br/ >សាខាដើម: '.$rowMB['name'];
            $condtion   .= ' AND t_tickets.main_branch_id = '.$_POST['main_branch'];
            $conPhone   .= ' AND t_tickets.main_branch_id = '.$_POST['main_branch'];
            $condLucky  .= ' AND lucky_tickets.main_branch_id = '.$_POST['main_branch'];
        }
        $msg .= '<br />ថ្ងៃបោះពុម្ព: '.date("d/m/Y H:i:s");
        echo $this->element('/print/header-report',array('msg' => $msg, 'logo' => $logo));
        $sqlSym = mysql_query("SELECT symbol FROM currency_centers WHERE id = 1;");
        $rowSym = mysql_fetch_array($sqlSym);
        $symbol = $rowSym[0];
        $totalIncome = 0;
        $totalPaid   = 0;
        $index   = 0;
        $userAmt = array();
        $comRecord = array();
        $sqlWalk = mysql_query("SELECT SUM(IFNULL(t_ticket_details.total_amount, 0) - IFNULL(t_ticket_details.discount, 0) + IFNULL(t_ticket_details.vat_price, 0) - IFNULL(t_ticket_details.total_amt_change, 0)) AS total_amount, t_tickets.created_by, CONCAT_WS(' ', users.first_name, users.last_name) AS username, t_tickets.company_id, COUNT(t_ticket_details.id) AS total_seat, IFNULL(t_tickets.lucky_draw_fee, 0) AS lucky_draw_fee, t_tickets.id
                                FROM t_tickets 
                                INNER JOIN users ON users.id = t_tickets.created_by 
                                INNER JOIN t_ticket_details ON t_ticket_details.t_ticket_id = t_tickets.id AND t_ticket_details.is_active = 1
                                WHERE t_tickets.status = 2 AND t_tickets.type = 1 AND t_tickets.confirm_by IS NULL AND t_tickets.offline_project_id = ".$user['User']['offline_project_id']." AND journey_type IN (1, 2) AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$condtion." 
                                GROUP BY t_tickets.created_by, t_tickets.company_id, t_tickets.id
                                UNION ALL
                                SELECT SUM(IFNULL(t_ticket_details.total_amount, 0) - IFNULL(t_ticket_details.discount, 0) + IFNULL(t_ticket_details.vat_price, 0) - IFNULL(t_ticket_details.total_amt_change, 0)) AS total_amount, t_tickets.created_by, CONCAT_WS(' ', users.first_name, users.last_name) AS username, t_tickets.company_id, COUNT(t_ticket_details.id) AS total_seat, IFNULL(t_tickets.lucky_draw_fee, 0) AS lucky_draw_fee, t_tickets.id
                                FROM t_ticket_3months AS t_tickets 
                                INNER JOIN users ON users.id = t_tickets.created_by 
                                INNER JOIN t_ticket_detail_3months AS t_ticket_details ON t_ticket_details.t_ticket_id = t_tickets.id AND t_ticket_details.is_active = 1
                                WHERE t_tickets.status = 2 AND t_tickets.type = 1 AND t_tickets.confirm_by IS NULL AND t_tickets.offline_project_id = ".$user['User']['offline_project_id']." AND journey_type IN (1, 2) AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$condtion." 
                                GROUP BY t_tickets.created_by, t_tickets.company_id, t_tickets.id;");
        if(mysql_num_rows($sqlWalk)){
            while($rowWalk = mysql_fetch_array($sqlWalk)){
                $luckTicket = 0;
                $sqlLucky   = mysql_query("SELECT * FROM lucky_tickets WHERE t_ticket_id = ".$rowWalk['id']);
                if(!mysql_num_rows($sqlLucky)){
                    if($rowWalk['lucky_draw_fee'] > 0){
                        $luckTicket = $rowWalk['lucky_draw_fee'];
                    }
                }
                $comIndex = $rowWalk['company_id'];
                if($rowWalk['company_id'] == 1 || $rowWalk['company_id'] == 2){
                    $comIndex = 1;
                }
                if($rowWalk['total_amount'] > 0){
                    $total = $rowWalk['total_amount'] + $luckTicket;
                } else {
                    $total = 0;
                }
                if (array_key_exists($rowWalk['created_by'], $userAmt)){
                    if (array_key_exists($comIndex, $userAmt[$rowWalk['created_by']])){
                        $userAmt[$rowWalk['created_by']][$comIndex]['total'] += $total;
                        $userAmt[$rowWalk['created_by']][$comIndex]['total_seat'] += $rowWalk['total_seat'];
                    } else {
                        $userAmt[$rowWalk['created_by']][$comIndex]['total'] = $total;
                        $userAmt[$rowWalk['created_by']][$comIndex]['total_seat'] = $rowWalk['total_seat'];
                        $comRecord[$comIndex] = $comIndex;
                    }
                    $userAmt[$rowWalk['created_by']]['total'] += $total;
                } else {
                    $userAmt[$rowWalk['created_by']]['user']  = $rowWalk['username'];
                    $userAmt[$rowWalk['created_by']]['total'] = $total;
                    $userAmt[$rowWalk['created_by']]['total_seat'] = $rowWalk['total_seat'];
                    $userAmt[$rowWalk['created_by']][$comIndex]['total'] = $total;
                    $userAmt[$rowWalk['created_by']][$comIndex]['total_seat'] = $rowWalk['total_seat'];
                    $comRecord[$comIndex] = $comIndex;
                }
            }
        }
        // Open Date
        $sqlOpenDate = mysql_query("SELECT SUM((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0) - IFNULL(t_tickets.total_change, 0))) AS total_amount, t_tickets.created_by, CONCAT_WS(' ', users.first_name, users.last_name) AS username, t_tickets.company_id, t_tickets.total_seat, IFNULL(t_tickets.lucky_draw_fee, 0) AS lucky_draw_fee 
                                    FROM t_tickets
                                    INNER JOIN users ON users.id = t_tickets.created_by 
                                    WHERE t_tickets.status = 2 AND t_tickets.is_open_date = 1 AND t_tickets.confirm_by IS NULL AND t_tickets.offline_project_id = ".$user['User']['offline_project_id']." AND journey_type IN (1, 2) AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$condtion." 
                                    GROUP BY t_tickets.created_by, t_tickets.company_id
                                    UNION ALL
                                    SELECT SUM((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0) - IFNULL(t_tickets.total_change, 0))) AS total_amount, t_tickets.created_by, CONCAT_WS(' ', users.first_name, users.last_name) AS username, t_tickets.company_id, t_tickets.total_seat, IFNULL(t_tickets.lucky_draw_fee, 0) AS lucky_draw_fee 
                                    FROM t_ticket_3months AS t_tickets
                                    INNER JOIN users ON users.id = t_tickets.created_by 
                                    WHERE t_tickets.status = 2 AND t_tickets.is_open_date = 1 AND t_tickets.confirm_by IS NULL AND t_tickets.offline_project_id = ".$user['User']['offline_project_id']." AND journey_type IN (1, 2) AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$condtion." 
                                    GROUP BY t_tickets.created_by, t_tickets.company_id;");
        if(mysql_num_rows($sqlOpenDate)){
            while($rowOpen = mysql_fetch_array($sqlOpenDate)){
                $comIndex = $rowOpen['company_id'];
                if($rowOpen['company_id'] == 1 || $rowOpen['company_id'] == 2){
                    $comIndex = 1;
                }
                if (array_key_exists($rowOpen['created_by'], $userAmt)){
                    if(array_key_exists($comIndex, $userAmt[$rowOpen['created_by']])){
                        $userAmt[$rowOpen['created_by']][$comIndex]['total'] += $rowOpen['total_amount'] + $rowOpen['lucky_draw_fee'];
                        $userAmt[$rowOpen['created_by']][$comIndex]['total_seat'] += $rowOpen['total_seat'];
                    } else {
                        $userAmt[$rowOpen['created_by']][$comIndex]['total'] = $rowOpen['total_amount'] + $rowOpen['lucky_draw_fee'];
                        $userAmt[$rowOpen['created_by']][$comIndex]['total_seat'] = $rowOpen['total_seat'];
                        $comRecord[$comIndex] = $comIndex;
                    }
                    $userAmt[$rowOpen['created_by']]['total'] += $rowOpen['total_amount'] + $rowOpen['lucky_draw_fee'];
                } else {
                    $userAmt[$rowOpen['created_by']]['user']  = $rowOpen['username'];
                    $userAmt[$rowOpen['created_by']]['total'] = $rowOpen['total_amount'] + $rowOpen['lucky_draw_fee'];
                    $userAmt[$rowOpen['created_by']]['total_seat'] = $rowOpen['total_seat'];
                    $userAmt[$rowOpen['created_by']][$comIndex]['total'] = $rowOpen['total_amount'] + $rowOpen['lucky_draw_fee'];
                    $userAmt[$rowOpen['created_by']][$comIndex]['total_seat'] = $rowOpen['total_seat'];
                    $comRecord[$comIndex] = $comIndex;
                }
            }
        }
        $sqlPhone = mysql_query("SELECT SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0) - IFNULL(t_tickets.total_change, 0)) AS total_amount, t_tickets.confirm_by AS created_by, CONCAT_WS(' ', users.first_name, users.last_name) AS username, t_tickets.company_id, COUNT(t_ticket_details.id) AS total_seat, IFNULL(t_tickets.lucky_draw_fee, 0) AS lucky_draw_fee 
                                 FROM t_tickets 
                                 INNER JOIN users ON users.id = t_tickets.created_by 
                                 INNER JOIN t_ticket_details ON t_ticket_details.t_ticket_id = t_tickets.id AND t_ticket_details.is_active = 1 
                                 WHERE t_tickets.status = 2 AND t_tickets.type = 2 AND t_tickets.offline_project_id = ".$user['User']['offline_project_id']." AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$conPhone." 
                                 GROUP BY t_tickets.confirm_by, t_tickets.company_id
                                 UNION ALL
                                 SELECT SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0) - IFNULL(t_tickets.total_change, 0)) AS total_amount, t_tickets.confirm_by AS created_by, CONCAT_WS(' ', users.first_name, users.last_name) AS username, t_tickets.company_id, COUNT(t_ticket_details.id) AS total_seat, IFNULL(t_tickets.lucky_draw_fee, 0) AS lucky_draw_fee 
                                 FROM t_ticket_3months AS t_tickets 
                                 INNER JOIN users ON users.id = t_tickets.created_by 
                                 INNER JOIN t_ticket_detail_3months AS t_ticket_details ON t_ticket_details.t_ticket_id = t_tickets.id AND t_ticket_details.is_active = 1 
                                 WHERE t_tickets.status = 2 AND t_tickets.type = 2 AND t_tickets.offline_project_id = ".$user['User']['offline_project_id']." AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$conPhone." 
                                 GROUP BY t_tickets.confirm_by, t_tickets.company_id;");
        if(mysql_num_rows($sqlPhone)){
            while($rowPhone = mysql_fetch_array($sqlPhone)){
                $comIndex = $rowOpen['company_id'];
                if($rowPhone['company_id'] == 1 || $rowPhone['company_id'] == 2){
                    $comIndex = 1;
                }
                if (array_key_exists($rowPhone['created_by'], $userAmt)){
                    if (array_key_exists($comIndex, $userAmt[$rowPhone['created_by']])){
                        $userAmt[$rowPhone['created_by']][$comIndex]['total'] += $rowPhone['total_amount'] + $rowPhone['lucky_draw_fee'];
                        $userAmt[$rowPhone['created_by']][$comIndex]['total_seat'] += $rowPhone['total_seat'];
                    } else {
                        $userAmt[$rowPhone['created_by']][$rowPhone['company_id']]['total'] = $rowPhone['total_amount'] + $rowPhone['lucky_draw_fee'];
                        $userAmt[$rowPhone['created_by']][$rowPhone['company_id']]['total_seat'] = $rowPhone['total_seat'];
                        $comRecord[$comIndex] = $comIndex;
                    }
                    $userAmt[$rowPhone['created_by']]['total'] += $rowPhone['total_amount'] + $rowPhone['lucky_draw_fee'];
                } else {
                    $userAmt[$rowPhone['created_by']]['user']  = $rowPhone['username'];
                    $userAmt[$rowPhone['created_by']]['total'] = $rowPhone['total_amount'] + $rowPhone['lucky_draw_fee'];
                    $userAmt[$rowPhone['created_by']]['total_seat'] = $rowPhone['total_seat'];
                    $userAmt[$rowPhone['created_by']][$comIndex]['total'] = $rowPhone['total_amount'] + $rowPhone['lucky_draw_fee'];
                    $userAmt[$rowPhone['created_by']][$comIndex]['total_seat'] = $rowPhone['total_seat'];
                    $comRecord[$comIndex] = $comIndex;
                }
            }
        }
        ?>
        <div id="dynamic">
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <th rowspan="2" class="first" style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th rowspan="2" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Username</th>
                        <?php
                        $totalCol = 2;
                        $comLists = array();
                        $comCon   = "";
                        if(!empty($comRecord)){
                            $comCon = " AND id IN (".implode(",", $comRecord).")";
                            $sqlCom = mysql_query("SELECT * FROM companies WHERE offline_project_id = 1 AND is_active = 1".$comCon);
                            while($rowCom = mysql_fetch_array($sqlCom)){
                                $comLists[$rowCom['id']] = $rowCom['name'];
                                if($rowCom['id'] == 1){
                                    $rowCom['name'] = "VET Ticket";
                                }
                                $totalCol += 2;
                            ?>
                            <th colspan="2" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;"><?php echo $rowCom['name']; ?></th>
                            <?php
                            }
                        }
                        ?>
                        <th rowspan="2" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right; width: 150px;">ទឹកប្រាក់សរុប</th>
                    </tr>
                    <tr>
                        <?php
                        foreach($comLists AS $com){
                        ?>
                        <th style="width: 8%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">ចំនួន</th>
                        <th style="width: 8%; text-align: right; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">ទឹកប្រាក់</th>
                        <?php
                        }
                        ?>
                    </tr>
                    <?php
                    $comTotal = array();
                    if(!empty($userAmt)){
                        foreach($userAmt AS $k => $user){
                            $totalPaid += $user['total'];
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: center;"><?php echo ++$index; ?></td>
                        <td style="padding: 5px; font-size: 12px; text-align: left;"><?php echo $user['user']; ?></td>
                        <?php
                            foreach($comLists AS $key => $com){
                                $totalSeat = 0;
                                $totalAmt  = 0;
                                if (array_key_exists($key, $user)){
                                    $totalSeat = $user[$key]['total_seat'];
                                    $totalAmt  = $user[$key]['total'];
                        ?>
                        <td style="text-align: center; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user[$key]['total_seat'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($user[$key]['total'], 2); ?></td>
                        <?php
                                } else {
                        ?>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                        <?php
                                }
                                if(array_key_exists($key, $comTotal)){
                                    $comTotal[$key]['total_seat'] += $totalSeat;
                                    $comTotal[$key]['total'] += $totalAmt;
                                } else {
                                    $comTotal[$key]['total_seat'] = $totalSeat;
                                    $comTotal[$key]['total'] = $totalAmt;
                                }
                            }
                        ?>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo $symbol; ?> <?php echo number_format($user['total'], 2); ?></td>
                    </tr>
                    <?php
                        }
                        if(!empty($comTotal)){
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: right;" colspan="2">សរុប</td>
                    <?php
                            foreach($comLists AS $key => $com){
                                if (array_key_exists($key, $comTotal)){
                    ?>
                    <td style="text-align: center; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($comTotal[$key]['total_seat'], 0); ?></td>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($comTotal[$key]['total'], 2); ?></td>
                    <?php
                                } else {
                    ?>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                    <?php
                                }
                            }
                        }
                    ?>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo $symbol; ?> <?php echo number_format($totalPaid, 2); ?></td>
                    </tr>
                    <!-- <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: right;" colspan="<?php //echo $totalCol; ?>">សរុប</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php //echo $symbol; ?> <?php //echo number_format($totalPaid, 2); ?></td>
                    </tr> -->
                    <?php
                    } else {
                    ?>
                    <tr>
                        <td colspan="<?php echo $totalCol+1; ?>" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
            <br />
            <?php
            $userAmt = array();
            $luckyRecord = array();
            $sqlLucky = mysql_query("SELECT  t_tickets.created_by, CONCAT_WS(' ', users.first_name, users.last_name) AS username, COUNT(t_ticket_details.id) AS total_seat, IFNULL(t_tickets.lucky_draw_fee, 0) AS lucky_draw_fee
                                     FROM t_tickets 
                                     INNER JOIN lucky_tickets ON lucky_tickets.t_ticket_id = t_tickets.id
                                     INNER JOIN users ON users.id = lucky_tickets.created_by 
                                     INNER JOIN t_ticket_details ON t_ticket_details.t_ticket_id = t_tickets.id AND t_ticket_details.is_active = 1
                                     WHERE t_ticket_details.is_active = 1 AND t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND DATE(lucky_tickets.created) >= '".dateConvert($_POST['date_from'])."' AND DATE(lucky_tickets.created) <= '".dateConvert($_POST['date_to'])."'".$condLucky." 
                                     GROUP BY lucky_tickets.created_by, t_tickets.id
                                     UNION ALL
                                     SELECT  t_tickets.created_by, CONCAT_WS(' ', users.first_name, users.last_name) AS username, COUNT(t_ticket_details.id) AS total_seat, IFNULL(t_tickets.lucky_draw_fee, 0) AS lucky_draw_fee
                                     FROM t_ticket_3months AS t_tickets 
                                     INNER JOIN lucky_tickets ON lucky_tickets.t_ticket_id = t_tickets.id
                                     INNER JOIN users ON users.id = lucky_tickets.created_by 
                                     INNER JOIN t_ticket_detail_3months AS t_ticket_details ON t_ticket_details.t_ticket_id = t_tickets.id AND t_ticket_details.is_active = 1
                                     WHERE t_ticket_details.is_active = 1 AND t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND DATE(lucky_tickets.created) >= '".dateConvert($_POST['date_from'])."' AND DATE(lucky_tickets.created) <= '".dateConvert($_POST['date_to'])."'".$condLucky." 
                                     GROUP BY lucky_tickets.created_by, t_tickets.id;");
            if(mysql_num_rows($sqlLucky)){
                while($rowLucky = mysql_fetch_array($sqlLucky)){
                    if (array_key_exists($rowLucky['created_by'], $luckyRecord)){
                        $luckyRecord[$rowLucky['created_by']]['total'] += $rowLucky['lucky_draw_fee'];
                    } else {
                        $luckyRecord[$rowLucky['created_by']]['user']  = $rowLucky['username'];
                        $luckyRecord[$rowLucky['created_by']]['total'] = $rowLucky['lucky_draw_fee'];
                    }
                }
            }
            ?>
            <table class="table_print" cellspacing="0">
                <thead>
                    <tr>
                        <th class="first" style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Lucky Tikcet (Username)</th>
                        <th style="width: 150px; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right; width: 150px;">Amount</th>
                    </tr>
                </thead>
                <?php
                $index = 0;
                $totalLucky = 0;
                if(!empty($luckyRecord)){
                    foreach($luckyRecord AS $data){
                        $totalLucky += $data['total'];
                        $totalPaid  += $data['total'];
                ?>
                <tr>
                    <td style="padding: 5px; font-size: 12px; text-align: center;"><?php echo ++$index; ?></td>
                    <td style="padding: 5px; font-size: 12px; text-align: left;"><?php echo $data['user']; ?></td>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($data['total'], 2); ?></td>
                </tr>
                <?php
                    }
                } else {
                ?>
                <tr>
                    <td colspan="3" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="2">សរុប</td>
                    <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo $symbol; ?> <?php echo number_format($totalLucky, 2); ?></td>
                </tr>
            </table>
            <br />
            <?php
            $agencyRecord = array();
            $comAgency = array();
            $sqlAgency = mysql_query("SELECT SUM(IFNULL(agency_balances.debit, 0)) AS total_amount,
                                      SUM(t_tickets.total_seat) AS total_seat,
                                      t_tickets.company_id,
                                      t_tickets.t_agent_id, 
                                      t_agents.name AS agency_name
                                      FROM t_tickets 
                                      INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.status = 1 AND t_agents.type = 2 AND t_agents.payment = 2 AND t_agents.id != 55
                                      INNER JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
                                      WHERE t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$conAgency." 
                                      GROUP BY t_tickets.t_agent_id, t_tickets.company_id
                                      UNION ALL
                                      SELECT SUM(IFNULL(agency_balances.debit, 0)) AS total_amount,
                                      SUM(t_tickets.total_seat) AS total_seat,
                                      t_tickets.company_id,
                                      t_tickets.t_agent_id, 
                                      t_agents.name AS agency_name
                                      FROM t_ticket_3months AS t_tickets 
                                      INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.status = 1 AND t_agents.type = 2 AND t_agents.payment = 2 AND t_agents.id != 55
                                      INNER JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
                                      WHERE t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$conAgency." 
                                      GROUP BY t_tickets.t_agent_id, t_tickets.company_id;");
            if(mysql_num_rows($sqlAgency)){
                while($rowAgency = mysql_fetch_array($sqlAgency)){
                    $comIndex = $rowAgency['company_id'];
                    if($rowAgency['company_id'] == 1 || $rowAgency['company_id'] == 2){
                        $comIndex = 1;
                    }
                    if (array_key_exists($rowAgency['t_agent_id'], $agencyRecord)){
                        if (array_key_exists($comIndex, $agencyRecord[$rowAgency['t_agent_id']])){
                            $agencyRecord[$rowAgency['t_agent_id']][$comIndex]['total'] += $rowAgency['total_amount'];
                            $agencyRecord[$rowAgency['t_agent_id']][$comIndex]['total_seat'] += $rowAgency['total_seat'];
                        } else {
                            $agencyRecord[$rowAgency['t_agent_id']][$comIndex]['total'] = $rowAgency['total_amount'];
                            $agencyRecord[$rowAgency['t_agent_id']][$comIndex]['total_seat'] = $rowAgency['total_seat'];
                            $comAgency[$comIndex] = $comIndex;
                        }
                        $agencyRecord[$rowAgency['t_agent_id']]['total'] += $rowAgency['total_amount'];
                        $agencyRecord[$rowAgency['t_agent_id']]['total_seat'] += $rowAgency['total_seat'];
                    } else {
                        $agencyRecord[$rowAgency['t_agent_id']]['agency'] = $rowAgency['agency_name'];
                        $agencyRecord[$rowAgency['t_agent_id']]['total']  = $rowAgency['total_amount'];
                        $agencyRecord[$rowAgency['t_agent_id']]['total_seat']  = $rowAgency['total_seat'];
                        $agencyRecord[$rowAgency['t_agent_id']][$comIndex]['total'] = $rowAgency['total_amount'];
                        $agencyRecord[$rowAgency['t_agent_id']][$comIndex]['total_seat'] = $rowAgency['total_seat'];
                        $comAgency[$comIndex] = $comIndex;
                    }
                }
            }
            ?>
            <table class="table_print" cellspacing="0">
                <thead>
                    <tr>
                        <th rowspan="2" class="first" style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th rowspan="2" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Agency Offline Postpaid Sales</th>
                        <?php
                        $totalColAg = 2;
                        $comLists = array();
                        $comCon   = "";
                        if(!empty($comAgency)){
                            $comCon = " AND id IN (".implode(",", $comAgency).")";
                            $sqlCom = mysql_query("SELECT * FROM companies WHERE offline_project_id = 1 AND is_active = 1".$comCon);
                            while($rowCom = mysql_fetch_array($sqlCom)){
                                $comLists[$rowCom['id']] = $rowCom['name'];
                                if($rowCom['id'] == 1){
                                    $rowCom['name'] = "VET Ticket";
                                }
                                $totalColAg += 2;
                            ?>
                            <th colspan="2" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;"><?php echo $rowCom['name']; ?></th>
                            <?php
                            }
                        }
                        ?>
                        <th rowspan="2" style="width: 150px; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right; width: 150px;">Net Price</th>
                    </tr>
                    <tr>
                        <?php
                        foreach($comLists AS $com){
                        ?>
                        <th style="width: 8%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">ចំនួន</th>
                        <th style="width: 8%; text-align: right; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">ទឹកប្រាក់</th>
                        <?php
                        }
                        ?>
                    </tr>
                </thead>
                <?php
                $index = 0;
                $totalAgency = 0;
                if(!empty($agencyRecord)){
                    foreach($agencyRecord AS $data){
                        $totalAgency += $data['total'];
                ?>
                <tr>
                    <td style="padding: 5px; font-size: 12px; text-align: center;"><?php echo ++$index; ?></td>
                    <td style="padding: 5px; font-size: 12px; text-align: left;"><?php echo $data['agency']; ?></td>
                    <?php
                    foreach($comLists AS $key => $com){
                        if (array_key_exists($key, $data)){
                    ?>
                    <td style="text-align: center; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($data[$key]['total_seat'], 0); ?></td>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($data[$key]['total'], 2); ?></td>
                    <?php
                        } else {
                    ?>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                    <?php
                        }
                    }
                    ?>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($data['total'], 2); ?></td>
                </tr>
                <?php
                    }
                } else {
                ?>
                <tr>
                    <td colspan="<?php echo $totalColAg+1; ?>" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="<?php echo $totalColAg; ?>">សរុប</td>
                    <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo $symbol; ?> <?php echo number_format($totalAgency, 2); ?></td>
                </tr>
            </table>
            <br/>
            <?php
            $agencyPostData = array();
            $comAgencyPost  = array();
            $sqlAgencyPost  = mysql_query("SELECT SUM(IFNULL(agency_balances.debit, 0)) AS total_amount,
                                            SUM(t_tickets.total_seat) AS total_seat,
                                            t_tickets.company_id,
                                            t_tickets.t_agent_id, 
                                            t_agents.name AS agency_name
                                            FROM t_tickets
                                            INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.type = 1 AND t_agents.payment = 2 AND t_agents.id != 55
                                            INNER JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
                                            WHERE t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$conAgency." 
                                            GROUP BY t_tickets.t_agent_id, t_tickets.company_id
                                            UNION ALL
                                            SELECT SUM(IFNULL(agency_balances.debit, 0)) AS total_amount,
                                            SUM(t_tickets.total_seat) AS total_seat,
                                            t_tickets.company_id,
                                            t_tickets.t_agent_id, 
                                            t_agents.name AS agency_name
                                            FROM t_ticket_3months AS t_tickets
                                            INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.type = 1 AND t_agents.payment = 2 AND t_agents.id != 55
                                            INNER JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
                                            WHERE t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$conAgency." 
                                            GROUP BY t_tickets.t_agent_id, t_tickets.company_id;");
            if(mysql_num_rows($sqlAgencyPost)){
                while($rowAgency = mysql_fetch_array($sqlAgencyPost)){
                    $comIndex = $rowAgency['company_id'];
                    if($rowAgency['company_id'] == 1 || $rowAgency['company_id'] == 2){
                        $comIndex = 1;
                    }
                    if (array_key_exists($rowAgency['t_agent_id'], $agencyPostData)){
                        if (array_key_exists($comIndex, $agencyPostData[$rowAgency['t_agent_id']])){
                            $agencyPostData[$rowAgency['t_agent_id']][$comIndex]['total'] += $rowAgency['total_amount'];
                            $agencyPostData[$rowAgency['t_agent_id']][$comIndex]['total_seat'] += $rowAgency['total_seat'];
                        } else {
                            $agencyPostData[$rowAgency['t_agent_id']][$comIndex]['total'] = $rowAgency['total_amount'];
                            $agencyPostData[$rowAgency['t_agent_id']][$comIndex]['total_seat'] = $rowAgency['total_seat'];
                            $comAgencyPost[$comIndex] = $comIndex;
                        }
                        $agencyPostData[$rowAgency['t_agent_id']]['total'] += $rowAgency['total_amount'];
                        $agencyPostData[$rowAgency['t_agent_id']]['total_seat'] += $rowAgency['total_seat'];
                    } else {
                        $agencyPostData[$rowAgency['t_agent_id']]['agency'] = $rowAgency['agency_name'];
                        $agencyPostData[$rowAgency['t_agent_id']]['total']  = $rowAgency['total_amount'];
                        $agencyPostData[$rowAgency['t_agent_id']]['total_seat']  = $rowAgency['total_seat'];
                        $agencyPostData[$rowAgency['t_agent_id']][$comIndex]['total'] = $rowAgency['total_amount'];
                        $agencyPostData[$rowAgency['t_agent_id']][$comIndex]['total_seat'] = $rowAgency['total_seat'];
                        $comAgencyPost[$comIndex] = $comIndex;
                    }
                }
            }
            ?>
            <table class="table_print" cellspacing="0">
                <thead>
                    <tr>
                        <th rowspan="2" class="first" style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th rowspan="2" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Agency Online Postpaid Sales</th>
                        <?php
                        $totalColAgPost = 2;
                        $comLists = array();
                        $comCon   = "";
                        if(!empty($comAgencyPost)){
                            $comCon = " AND id IN (".implode(",", $comAgencyPost).")";
                            $sqlCom = mysql_query("SELECT * FROM companies WHERE offline_project_id = 1 AND is_active = 1".$comCon);
                            while($rowCom = mysql_fetch_array($sqlCom)){
                                $comLists[$rowCom['id']] = $rowCom['name'];
                                if($rowCom['id'] == 1){
                                    $rowCom['name'] = "VET Ticket";
                                }
                                $totalColAgPost += 2;
                            ?>
                            <th colspan="2" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;"><?php echo $rowCom['name']; ?></th>
                            <?php
                            }
                        }
                        ?>
                        <th rowspan="2" style="width: 150px; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right; width: 150px;">Net Price</th>
                    </tr>
                    <tr>
                        <?php
                        foreach($comLists AS $com){
                        ?>
                        <th style="width: 8%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">ចំនួន</th>
                        <th style="width: 8%; text-align: right; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">ទឹកប្រាក់</th>
                        <?php
                        }
                        ?>
                    </tr>
                </thead>
                <?php
                $index = 0;
                $totalAgencyPost = 0;
                $comAgencyTotal  = array();
                if(!empty($agencyPostData)){
                    foreach($agencyPostData AS $data){
                        $totalAgencyPost += $data['total'];
                ?>
                <tr>
                    <td style="padding: 5px; font-size: 12px; text-align: center;"><?php echo ++$index; ?></td>
                    <td style="padding: 5px; font-size: 12px; text-align: left;"><?php echo $data['agency']; ?></td>
                    <?php
                    foreach($comLists AS $key => $com){
                        $totalSeat = 0;
                        $totalAmt  = 0;
                        if (array_key_exists($key, $data)){
                            $totalSeat = $data[$key]['total_seat'];
                            $totalAmt  = $data[$key]['total'];
                    ?>
                    <td style="text-align: center; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($data[$key]['total_seat'], 0); ?></td>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($data[$key]['total'], 2); ?></td>
                    <?php
                        } else {
                    ?>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                    <?php
                        }
                        if(array_key_exists($key, $comAgencyTotal)){
                            $comAgencyTotal[$key]['total_seat'] += $totalSeat;
                            $comAgencyTotal[$key]['total'] += $totalAmt;
                        } else {
                            $comAgencyTotal[$key]['total_seat'] = $totalSeat;
                            $comAgencyTotal[$key]['total'] = $totalAmt;
                        }
                    }
                    ?>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($data['total'], 2); ?></td>
                </tr>
                <?php
                    }
                } else {
                ?>
                <tr>
                    <td colspan="<?php echo $totalColAgPost+1; ?>" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
                </tr>
                <?php
                }
                if(!empty($comAgencyTotal) && !empty($comAgencyTotal)){
                    ?>
                <tr>
                    <td style="padding: 5px; font-size: 12px; text-align: right;" colspan="2">សរុប</td>
                    <?php
                        foreach($comLists AS $key => $com){
                            if (array_key_exists($key, $comAgencyTotal)){
                    ?>
                    <td style="text-align: center; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($comAgencyTotal[$key]['total_seat'], 0); ?></td>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($comAgencyTotal[$key]['total'], 2); ?></td>
                    <?php
                            } else {
                    ?>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                    <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;">-</td>
                    <?php
                            }
                        }
                ?>
                    <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo $symbol; ?> <?php echo number_format($totalAgencyPost, 2); ?></td>
                </tr>
                <?php
                }
                ?>
                <!-- <tr>
                    <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="<?php //echo $totalColAgPost; ?>">សរុប</td>
                    <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php //echo $symbol; ?> <?php //echo number_format($totalAgencyPost, 2); ?></td>
                </tr> -->
            </table>
            <br/>
            <table style="border: 0px; width: 100%;">
                <tr>
                    <td style="text-align: right; padding: 5px;  font-size: 16px; font-weight: bold; width: 87%;" colspan="2">សរុបចុងក្រោយ</td>
                    <td style="text-align: right; padding: 5px; font-size: 16px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($totalPaid + $totalAgency + $totalAgencyPost, 2); ?></td>
                </tr>
            </table>
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