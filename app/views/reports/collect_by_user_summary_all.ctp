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
        $condtion = '';
        $conPhone = '';
        $userName = '';
        $msg  = '<b style="font-size: 18px;">របាយការណ៏ចំណូលសរុប</b><br />';
        $logo = "";
        if($_POST['company'] != '') {
            $sqlCompany = mysql_query("SELECT GROUP_CONCAT(name) AS name FROM companies WHERE id IN (".$_POST['company'].")");
            $rowCompany = mysql_fetch_array($sqlCompany);
            $msg .= 'សាខា: '.$rowCompany[0].'<br/>';
            $condtion  .= ' AND t_tickets.company_id IN ('.$_POST['company'].')';
            $conPhone  .= ' AND t_tickets.company_id IN ('.$_POST['company'].')';
            $sqlLogo = mysql_query("SELECT photo FROM companies WHERE id IN (".$_POST['company'].") LIMIT 1");
            $rowLogo = mysql_fetch_array($sqlLogo);
            $logo    = $rowLogo[0];
        }
        if($_POST['branch'] != '') {
            $sqlBranch = mysql_query("SELECT name FROM branches WHERE id = ".$_POST['branch']);
            $rowBranch = mysql_fetch_array($sqlBranch);
            $msg .= 'សាខា: '.$rowBranch[0].'<br/>';
            $condtion  .= ' AND t_tickets.branch_id = '.$_POST['branch'];
            $conPhone  .= ' AND t_tickets.branch_id = '.$_POST['branch'];
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
        }
        $msg .= '<br />ថ្ងៃបោះពុម្ព: '.date("d/m/Y H:i:s");
        echo $this->element('/print/header-report',array('msg' => $msg, 'logo' => $logo));
        $symbol = '';
        if($_POST['branch']!=''){
            $sqlSym = mysql_query("SELECT symbol FROM currency_centers WHERE id = (SELECT currency_center_id FROM branches WHERE id = {$_POST['branch']});");
            $rowSym = mysql_fetch_array($sqlSym);
            $symbol = $rowSym[0];
        }
        $totalIncome = 0;
        ?>
        <div id="dynamic">
            <table class="table_print" cellspacing="0" style="width: 100%;">
                <tbody>
                    <tr>
                        <th class="first" rowspan="2" style="width: 2%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th rowspan="2" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;">Location Branch</th>
                        <?php
                        $totalCol = 2;
                        $comLists = array();
                        $sqlCom   = mysql_query("SELECT * FROM companies WHERE offline_project_id = 1 AND is_active = 1");
                        while($rowCom = mysql_fetch_array($sqlCom)){
                            $totalCol += 3;
                            $comLists[$rowCom['id']] = $rowCom['name'];
                        ?>
                        <th colspan="3" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;"><?php echo $rowCom['name']; ?></th>
                        <?php
                        }
                        ?>
                        <th colspan="3" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Total</th>
                    </tr>
                    <tr>
                        <?php
                        foreach($comLists AS $com){
                        ?>
                        <th style="width: 4%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Booked</th>
                        <th style="width: 4%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Seats</th>
                        <th style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Fare ($)</th>
                        <?php
                        }
                        ?>
                        <th style="width: 4%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Booked</th>
                        <th style="width: 4%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Seats</th>
                        <th style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;">Fare ($)</th>
                    </tr>
                    <?php
                    $totalPaid   = 0;
                    $totalBooked = 0;
                    $totalSeats  = 0;
                    $index   = 0;
                    $userAmt = array();
                    $sqlWalk = mysql_query("SELECT t_tickets.id, t_tickets.main_branch_id, t_tickets.company_id, main_branches.name AS branch_name, t_tickets.lucky_draw_fee 
                                            FROM t_tickets 
                                            INNER JOIN main_branches ON main_branches.id = t_tickets.main_branch_id 
                                            WHERE t_tickets.status = 2 AND t_tickets.type = 1 AND t_tickets.confirm_by IS NULL AND t_tickets.offline_project_id = ".$user['User']['offline_project_id']." AND journey_type IN (1, 2) AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$condtion." 
                                            UNION ALL
                                            SELECT t_tickets.id, t_tickets.main_branch_id, t_tickets.company_id, main_branches.name AS branch_name, t_tickets.lucky_draw_fee 
                                            FROM t_ticket_3months AS t_tickets 
                                            INNER JOIN main_branches ON main_branches.id = t_tickets.main_branch_id 
                                            WHERE t_tickets.status = 2 AND t_tickets.type = 1 AND t_tickets.confirm_by IS NULL AND t_tickets.offline_project_id = ".$user['User']['offline_project_id']." AND journey_type IN (1, 2) AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$condtion." 
                                            ORDER BY t_tickets.main_branch_id, t_tickets.company_id, t_tickets.id;");
                    if(mysql_num_rows($sqlWalk)){
                        while($rowWalk = mysql_fetch_array($sqlWalk)){
                            $sqlDetail = mysql_query("SELECT COUNT(t_ticket_details.id) AS seats, SUM(IFNULL(t_ticket_details.total_amount, 0) - IFNULL(t_ticket_details.discount, 0) + IFNULL(t_ticket_details.vat_price, 0) - IFNULL(t_ticket_details.total_amt_change, 0)) AS total_amount 
                                                      FROM t_ticket_details 
                                                      WHERE t_ticket_details.is_active = 1 AND t_ticket_details.t_ticket_id = ".$rowWalk['id']);
                            $rowDetail = mysql_fetch_array($sqlDetail);
                            if($rowDetail['total_amount'] > 0){
                                $total = $rowDetail['total_amount'] + $rowWalk['lucky_draw_fee'];
                            } else {
                                $total = 0;
                            }
                            if (array_key_exists($rowWalk['main_branch_id'], $userAmt)){
                                if (array_key_exists($rowWalk['company_id'], $userAmt[$rowWalk['main_branch_id']])){
                                    $userAmt[$rowWalk['main_branch_id']][$rowWalk['company_id']]['total']  += $total;
                                    $userAmt[$rowWalk['main_branch_id']][$rowWalk['company_id']]['booked'] += 1;
                                    $userAmt[$rowWalk['main_branch_id']][$rowWalk['company_id']]['seats']  += $rowDetail['seats'];
                                } else {
                                    $userAmt[$rowWalk['main_branch_id']][$rowWalk['company_id']]['total']  = $total;
                                    $userAmt[$rowWalk['main_branch_id']][$rowWalk['company_id']]['booked'] = 1;
                                    $userAmt[$rowWalk['main_branch_id']][$rowWalk['company_id']]['seats']  = $rowDetail['seats'];
                                }
                                $userAmt[$rowWalk['main_branch_id']]['total']  += $total;
                                $userAmt[$rowWalk['main_branch_id']]['booked'] += 1;
                                $userAmt[$rowWalk['main_branch_id']]['seats']  += $rowDetail['seats'];
                            } else {
                                $userAmt[$rowWalk['main_branch_id']]['user']   = $rowWalk['branch_name'];
                                $userAmt[$rowWalk['main_branch_id']]['total']  = $total;
                                $userAmt[$rowWalk['main_branch_id']]['booked'] = 1;
                                $userAmt[$rowWalk['main_branch_id']]['seats']  = $rowDetail['seats'];
                                $userAmt[$rowWalk['main_branch_id']][$rowWalk['company_id']]['total']  = $total;
                                $userAmt[$rowWalk['main_branch_id']][$rowWalk['company_id']]['booked'] = 1;
                                $userAmt[$rowWalk['main_branch_id']][$rowWalk['company_id']]['seats']  = $rowDetail['seats'];
                            }
                        }
                    }
                    // Open Date
                    $sqlOpenDate = mysql_query("SELECT (IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) AS total_amount, t_tickets.main_branch_id, main_branches.name AS branch_name, t_tickets.company_id 
                                                FROM t_tickets
                                                INNER JOIN main_branches ON main_branches.id = t_tickets.main_branch_id 
                                                WHERE t_tickets.status = 2 AND t_tickets.is_open_date = 1 AND t_tickets.confirm_by IS NULL AND t_tickets.offline_project_id = ".$user['User']['offline_project_id']." AND journey_type IN (1, 2) AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$condtion." 
                                                GROUP BY t_tickets.main_branch_id, t_tickets.company_id
                                                
                                                ORDER BY t_tickets.main_branch_id, t_tickets.company_id;");
                    if(mysql_num_rows($sqlOpenDate)){
                        while($rowOpen = mysql_fetch_array($sqlOpenDate)){
                            if (array_key_exists($rowOpen['main_branch_id'], $userAmt)){
                                if (array_key_exists($rowOpen['company_id'], $userAmt[$rowOpen['main_branch_id']])){
                                    $userAmt[$rowOpen['main_branch_id']][$rowOpen['company_id']]['total']  += $rowOpen['total_amount'];
                                    $userAmt[$rowOpen['main_branch_id']][$rowOpen['company_id']]['booked'] += 1;
                                    $userAmt[$rowOpen['main_branch_id']][$rowOpen['company_id']]['seats']  += 1;
                                } else {
                                    $userAmt[$rowOpen['main_branch_id']][$rowOpen['company_id']]['total']  = $rowOpen['total_amount'];
                                    $userAmt[$rowOpen['main_branch_id']][$rowOpen['company_id']]['booked'] = 1;
                                    $userAmt[$rowOpen['main_branch_id']][$rowOpen['company_id']]['seats']  = 1;
                                }
                                $userAmt[$rowOpen['main_branch_id']]['total']  += $rowOpen['total_amount'];
                                $userAmt[$rowOpen['main_branch_id']]['booked'] += 1;
                                $userAmt[$rowOpen['main_branch_id']]['seats']  += 1;
                            } else {
                                $userAmt[$rowOpen['main_branch_id']]['user']   = $rowOpen['branch_name'];
                                $userAmt[$rowOpen['main_branch_id']]['total']  = $rowOpen['total_amount'];
                                $userAmt[$rowOpen['main_branch_id']]['booked'] = 1;
                                $userAmt[$rowOpen['main_branch_id']]['seats']  = 1;
                                $userAmt[$rowOpen['main_branch_id']][$rowOpen['company_id']]['total']  = $rowOpen['total_amount'];
                                $userAmt[$rowOpen['main_branch_id']][$rowOpen['company_id']]['booked'] = 1;
                                $userAmt[$rowOpen['main_branch_id']][$rowOpen['company_id']]['seats']  = 1;
                            }
                        }
                    }
                    $sqlPhone = mysql_query("SELECT t_tickets.id, main_branches.id AS main_branch_id, main_branches.name AS branch_name, t_tickets.company_id, t_tickets.lucky_draw_fee  
                                             FROM t_tickets 
                                             INNER JOIN main_branches ON main_branches.id = t_tickets.main_branch_id 
                                             INNER JOIN t_ticket_details ON t_ticket_details.t_ticket_id = t_tickets.id AND t_ticket_details.is_active = 1 
                                             WHERE t_tickets.status = 2 AND t_tickets.type = 2 AND t_tickets.offline_project_id = ".$user['User']['offline_project_id']." AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$conPhone." 
                                             ORDER BY t_tickets.main_branch_id, t_tickets.company_id, t_tickets.id;");
                    if(mysql_num_rows($sqlPhone)){
                        while($rowPhone = mysql_fetch_array($sqlPhone)){
                            $sqlDetail = mysql_query("SELECT COUNT(t_ticket_details.id) AS seats, SUM(IFNULL(t_ticket_details.total_amount, 0) - IFNULL(t_ticket_details.discount, 0) + IFNULL(t_ticket_details.vat_price, 0)) AS total_amount FROM t_ticket_details WHERE t_ticket_details.is_active = 1 AND t_ticket_details.t_ticket_id = ".$rowPhone['id']);
                            $rowDetail = mysql_fetch_array($sqlDetail);
                            if (array_key_exists($rowReturn['main_branch_id'], $userAmt)){
                                if (array_key_exists($rowReturn['company_id'], $userAmt[$rowReturn['main_branch_id']])){
                                    $userAmt[$rowReturn['main_branch_id']][$rowReturn['company_id']]['total']  += $rowDetail['total_amount'] + $rowPhone['lucky_draw_fee'];
                                    $userAmt[$rowReturn['main_branch_id']][$rowReturn['company_id']]['booked'] += 1;
                                    $userAmt[$rowReturn['main_branch_id']][$rowReturn['company_id']]['seats']  += $rowDetail['seats'];
                                } else {
                                    $userAmt[$rowReturn['main_branch_id']][$rowReturn['company_id']]['total']  = $rowDetail['total_amount'] + $rowPhone['lucky_draw_fee'];
                                    $userAmt[$rowReturn['main_branch_id']][$rowReturn['company_id']]['booked'] = 1;
                                    $userAmt[$rowReturn['main_branch_id']][$rowReturn['company_id']]['seats']  = $rowDetail['seats'];
                                }
                                $userAmt[$rowReturn['main_branch_id']]['total']  += $rowDetail['total_amount'] + $rowPhone['lucky_draw_fee'];
                                $userAmt[$rowReturn['main_branch_id']]['booked'] += 1;
                                $userAmt[$rowReturn['main_branch_id']]['seats']  += $rowDetail['seats'];
                            } else {
                                $userAmt[$rowReturn['main_branch_id']]['user']   = $rowReturn['branch_name'];
                                $userAmt[$rowReturn['main_branch_id']]['total']  = $rowDetail['total_amount'] + $rowPhone['lucky_draw_fee'];
                                $userAmt[$rowReturn['main_branch_id']]['booked'] = 1;
                                $userAmt[$rowReturn['main_branch_id']]['seats']  = $rowDetail['seats'];
                                $userAmt[$rowReturn['main_branch_id']][$rowReturn['company_id']]['total']  = $rowDetail['total_amount'] + $rowPhone['lucky_draw_fee'];
                                $userAmt[$rowReturn['main_branch_id']][$rowReturn['company_id']]['booked'] = 1;
                                $userAmt[$rowReturn['main_branch_id']][$rowReturn['company_id']]['seats']  = $rowDetail['seats'];
                            }
                        }
                    }
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
                        $totalCol = 2;
                        foreach($comLists AS $key => $com){
                            $totalCol += 3;
                            if (array_key_exists($key, $user)){
                        ?>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user[$key]['booked'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user[$key]['seats'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($user[$key]['total'], 2); ?></td>
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
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user['booked'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo number_format($user['seats'], 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($user['total'], 2); ?></td>
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
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="<?php echo $totalCol; ?>">Total</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalBooked, 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalSeats, 0); ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($totalPaid, 2); ?></td>
                    </tr>
                </tbody>
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