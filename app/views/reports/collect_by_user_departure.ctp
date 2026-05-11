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
        }
        $userMainBranch = array();
        if($_POST['main_branch'] != ''){
            $sqlUserMB = mysql_query("SELECT id FROM users WHERE main_branch_id = ".$_POST['main_branch']);
            if(mysql_num_rows($sqlUserMB)){
                while($rowUserMB = mysql_fetch_array($sqlUserMB)){
                    $userMainBranch[] = $rowUserMB['id'];
                }
            } else {
                $userMainBranch[] = 0;
            }
        } else {
            $userMainBranch[] = 0;
        }
        $condtion = '';
        $conPhone = '';
        $userName = '';
        if($_POST['user_select'] != ''){
            $sqlUser = mysql_query("SELECT CONCAT_WS(' ',first_name,last_name) FROM users WHERE id = ".$_POST['user_select']);
            $rowUser = mysql_fetch_array($sqlUser);
            $userName = $rowUser[0];
            $condtion  .= ' AND t_tickets.created_by = '.$_POST['user_select'];
            $conPhone  .= ' AND t_tickets.confirm_by = '.$_POST['user_select'];
        } else {
            $dateNow = strtotime(date("Y-m-d"));
            $dateSt  = strtotime("2018-06-01");
            if($dateNow < $dateSt){
                if($_POST['branch'] != ''){
                    $condtion  .= " AND t_tickets.created_by IN (".implode(",", $userCreated).")";
                    $conPhone  .= " AND t_tickets.confirm_by IN (".implode(",", $userConfirm).")";
                }
            } else {
                if($_POST['branch']!=''){
                    $condtion  .= " AND t_tickets.created_by IN (".implode(",", $userMainBranch).") AND t_tickets.created_by IN (".implode(",", $userCreated).")";
                    $conPhone  .= " AND t_tickets.confirm_by IN (".implode(",", $userMainBranch).") AND t_tickets.confirm_by IN (".implode(",", $userConfirm).")";
                } else {
                    $condtion  .= " AND t_tickets.created_by IN (".implode(",", $userMainBranch).")";
                    $conPhone  .= " AND t_tickets.confirm_by IN (".implode(",", $userMainBranch).")";
                }
            }
        }
        $msg  = '<b style="font-size: 18px;">របាយការណ៏ចំណូលសរុបតាមម៉ោងធ្វើដំណើរ</b><br />';
        $logo = "";
        if($_POST['company'] != '') {
            $sqlCompany = mysql_query("SELECT name FROM companies WHERE id = ".$_POST['company']);
            $rowCompany = mysql_fetch_array($sqlCompany);
            $msg .= 'សាខា: '.$rowCompany[0].'<br/>';
            $condtion  .= ' AND t_tickets.company_id = '.$_POST['company'];
            $conPhone  .= ' AND t_tickets.company_id = '.$_POST['company'];
            $sqlLogo = mysql_query("SELECT photo FROM companies WHERE id = ".$_POST['company']);
            $rowLogo = mysql_fetch_array($sqlLogo);
            $logo    = $rowLogo[0];
        } else {
//            $condtion  .= ' AND t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
//            $conPhone  .= ' AND t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
        }
        if($_POST['branch'] != '') {
            $sqlBranch = mysql_query("SELECT name FROM branches WHERE id = ".$_POST['branch']);
            $rowBranch = mysql_fetch_array($sqlBranch);
            $msg .= 'សាខា: '.$rowBranch[0].'<br/>';
            $condtion  .= ' AND t_tickets.branch_id = '.$_POST['branch'];
            $conPhone  .= ' AND t_tickets.branch_id = '.$_POST['branch'];
        } else {
//            $condtion  .= ' AND t_tickets.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
//            $conPhone  .= ' AND t_tickets.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
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
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <th class="first" style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th style="width: 25%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Travel Date</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Total Passenger</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Total Return</th>
                        <th style="width: 15%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Total Amount</th>
                    </tr>
                    <?php
                    $subTotal   = 0;
                    $totalPaid  = 0;
                    $userAmt = array();
                    $sqlWalk = mysql_query("SELECT SUM(IFNULL(t_ticket_details.total_amount, 0) - IFNULL(t_ticket_details.discount, 0) + IFNULL(t_ticket_details.vat_price, 0)) AS total_amount, t_tickets.journey_date, t_tickets.journey_time, UNIX_TIMESTAMP(CONCAT('1970-01-01 ', t_tickets.journey_time)) AS key_index, COUNT(t_ticket_details.id) AS passenger FROM t_tickets INNER JOIN t_ticket_details ON t_ticket_details.t_ticket_id = t_tickets.id INNER JOIN users ON users.id = t_tickets.created_by WHERE t_tickets.status = 2 AND t_tickets.type = 1 AND t_tickets.offline_project_id = ".$user['User']['offline_project_id']." AND journey_type IN (1, 2) AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$condtion." GROUP BY t_tickets.journey_time, t_tickets.journey_date;");
                    if(mysql_num_rows($sqlWalk)){
                        while($rowWalk = mysql_fetch_array($sqlWalk)){
                            $userAmt[$rowWalk['key_index']]['data'][0] = 0;
                            $userAmt[$rowWalk['key_index']]['departure'] = $rowWalk['journey_time'];
                            if (array_key_exists($rowWalk['key_index'], $userAmt)){
                                if (array_key_exists($rowWalk['journey_date'], $userAmt[$rowWalk['key_index']]['data'])){
                                    $userAmt[$rowWalk['key_index']]['data'][$rowWalk['journey_date']]['total'] += $rowWalk['total_amount'];
                                    $userAmt[$rowWalk['key_index']]['data'][$rowWalk['journey_date']]['passenger'] += $rowWalk['passenger'];
                                } else {
                                    if($rowWalk['journey_date'] != '' && $rowWalk['journey_date'] != '0000-00-00'){
                                        $userAmt[$rowWalk['key_index']]['data'][$rowWalk['journey_date']]['date'] = dateShort($rowWalk['journey_date']);
                                    } else {
                                        $userAmt[$rowWalk['key_index']]['data'][$rowWalk['journey_date']]['date'] = $rowWalk['journey_date'];    
                                    }
                                    $userAmt[$rowWalk['key_index']]['data'][$rowWalk['journey_date']]['return'] = 0;
                                    $userAmt[$rowWalk['key_index']]['data'][$rowWalk['journey_date']]['passenger'] = $rowWalk['passenger'];
                                    $userAmt[$rowWalk['key_index']]['data'][$rowWalk['journey_date']]['total'] = $rowWalk['total_amount'];
                                }
                            } else {
                                if($rowWalk['journey_date'] != '' && $rowWalk['journey_date'] != '0000-00-00'){
                                    $userAmt[$rowWalk['key_index']]['data'][$rowWalk['journey_date']]['date'] = dateShort($rowWalk['journey_date']);
                                } else {
                                    $userAmt[$rowWalk['key_index']]['data'][$rowWalk['journey_date']]['date'] = $rowWalk['journey_date'];    
                                }
                                $userAmt[$rowWalk['key_index']]['data'][$rowWalk['journey_date']]['return'] = 0;
                                $userAmt[$rowWalk['key_index']]['data'][$rowWalk['journey_date']]['passenger'] = $rowWalk['passenger'];
                                $userAmt[$rowWalk['key_index']]['data'][$rowWalk['journey_date']]['total'] = $rowWalk['total_amount'];
                            }
                        }
                    }
                    $sqlPhone = mysql_query("SELECT SUM(IFNULL(t_ticket_details.total_amount, 0) - IFNULL(t_ticket_details.discount, 0) + IFNULL(t_ticket_details.vat_price, 0)) AS total_amount, t_tickets.journey_date, t_tickets.journey_time, UNIX_TIMESTAMP(CONCAT('1970-01-01 ', t_tickets.journey_time)) AS key_index, COUNT(t_ticket_details.id) AS passenger FROM t_tickets INNER JOIN t_ticket_details ON t_ticket_details.t_ticket_id = t_tickets.id INNER JOIN users ON users.id = t_tickets.created_by WHERE t_tickets.status = 2 AND t_tickets.type = 2 AND t_tickets.offline_project_id = ".$user['User']['offline_project_id']." AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$conPhone." GROUP BY t_tickets.journey_time, t_tickets.journey_date;");
                    if(mysql_num_rows($sqlPhone)){
                        while($rowPhone = mysql_fetch_array($sqlPhone)){
                            $userAmt[$rowPhone['key_index']]['data'][0] = 0;
                            $userAmt[$rowPhone['key_index']]['departure'] = $rowPhone['journey_time'];
                            if (array_key_exists($rowPhone['key_index'], $userAmt)){
                                if (array_key_exists($rowPhone['journey_date'], $userAmt[$rowPhone['key_index']]['data'])){
                                    $userAmt[$rowPhone['key_index']]['data'][$rowPhone['journey_date']]['total'] += $rowPhone['total_amount'];
                                    $userAmt[$rowPhone['key_index']]['data'][$rowPhone['journey_date']]['passenger'] += $rowPhone['passenger'];
                                } else {
                                    if($rowPhone['journey_date'] != '' && $rowPhone['journey_date'] != '0000-00-00'){
                                        $userAmt[$rowPhone['key_index']]['data'][$rowPhone['journey_date']]['date'] = dateShort($rowPhone['journey_date']);
                                    } else {
                                        $userAmt[$rowPhone['key_index']]['data'][$rowPhone['journey_date']]['date'] = $rowPhone['journey_date'];    
                                    }
                                    $userAmt[$rowPhone['key_index']]['data'][$rowPhone['journey_date']]['return'] = 0;
                                    $userAmt[$rowPhone['key_index']]['data'][$rowPhone['journey_date']]['passenger'] = $rowPhone['passenger'];
                                    $userAmt[$rowPhone['key_index']]['data'][$rowPhone['journey_date']]['total'] = $rowPhone['total_amount'];
                                }
                            } else {
                                if($rowPhone['journey_date'] != '' && $rowPhone['journey_date'] != '0000-00-00'){
                                    $userAmt[$rowPhone['key_index']]['data'][$rowPhone['journey_date']]['date'] = dateShort($rowPhone['journey_date']);
                                } else {
                                    $userAmt[$rowPhone['key_index']]['data'][$rowPhone['journey_date']]['date'] = $rowPhone['journey_date'];    
                                }
                                $userAmt[$rowPhone['key_index']]['data'][$rowPhone['journey_date']]['return'] = 0;
                                $userAmt[$rowPhone['key_index']]['data'][$rowPhone['journey_date']]['passenger'] = $rowPhone['passenger'];
                                $userAmt[$rowPhone['key_index']]['data'][$rowPhone['journey_date']]['total'] = $rowPhone['total_amount'];
                            }
                        }
                    }
                    if(!empty($userAmt)){
                        ksort($userAmt);
                        foreach($userAmt AS $departure){
                            $index = 0;
                            $subTotal = 0;
                    ?>
                    <tr>
                        <td colspan="5" style="font-size: 13px; font-weight: bold;"><?php echo $departure['departure']; ?></td>
                    </tr>
                    <?php
                            foreach($departure['data'] AS $key => $detail){
                                if($key != 0){
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: center;"><?php echo ++$index; ?></td>
                        <td style="padding: 5px; font-size: 12px; text-align: left;"><?php echo $detail['date']; ?></td>
                        <td style="padding: 5px; font-size: 12px; text-align: left;"><?php echo $detail['passenger']; ?></td>
                        <td style="padding: 5px; font-size: 12px; text-align: left;"><?php echo $detail['return']; ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($detail['total'], 2); ?></td>
                    </tr>
                    <?php
                                    $subTotal  += $detail['total'];
                                    $totalPaid += $detail['total'];
                                }
                            }
                    ?>
                    <tr>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;" colspan="4">Sub Total:</td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($subTotal, 2); ?></td>
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="4">Total</td>
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