<?php
include('includes/function.php');
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
?>
<style type="text/css" media="screen">
    .fontScreen {font-size: 12px;}
</style> 
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $btnPrint; ?>").click(function(){
            $("#netProfitSignature").show();
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
            $("#netProfitSignature").hide();
        });
        
        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/report_net_profit_month<?php echo $user['User']['id']; ?>.csv", "_blank");
        });
    });
</script>
<div class="leftPanel">
    <div id="<?php echo $printArea; ?>">
        <style type="text/css" media="print">
            .fontScreen {font-size: 9px;}
        </style>
        <?php
        $condtion = '';
        $conExpense = '';
        $msg = '<b style="font-size: 12px;">Net Profit</b><br />';
        if($_POST['branch']!='') {
            $sqlBranch = mysql_query("SELECT name FROM branches WHERE id = ".$_POST['branch']);
            $rowBranch = mysql_fetch_array($sqlBranch);
            $msg .= $rowBranch[0].'<br/>';
            $condtion .= ' AND t_tickets.branch_id = '.$_POST['branch'];
            $conExpense .= ' AND expenses.branch_id = '.$_POST['branch'];
        } else {
            $condtion .= ' AND t_tickets.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
            $conExpense .= ' AND expenses.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
        }
        if($_POST['month']!='') {
            $msg .= 'For '.convertMonthToEnglish($_POST['month']);
        }
        if($_POST['year']!='') {
            $msg .= ' '.$_POST['year'];
        }
        echo $this->element('/print/header-report',array('msg'=>$msg));
        $filename = "public/report/report_net_profit_month" . $user['User']['id'] . ".csv";
        $fp = fopen($filename, "wb");
        $excelContent = "Net Profit\nFor ".convertMonthToEnglish($_POST['month'])." ".$_POST['year'];
        ?>
        <div id="dynamic">
            <?php
            if(COUNT($branches) == 1 && $_POST['branch']!=''){
                $firstDay = $_POST['year']."-".$_POST['month']."-01";
                $lastDay  = date("Y-m-t", strtotime($firstDay));
                $days = listDays($firstDay, $lastDay);
                $totalIncome = 0;
                $totalExpense = 0;
                $totalProfit = 0;
                $sqlSym = mysql_query("SELECT symbol, id FROM currency_centers WHERE id = (SELECT currency_center_id FROM branches WHERE id = ".$_POST['branch'].")");
                $rowSym = mysql_fetch_array($sqlSym);
                $decimal = 2;
                if($rowSym[1] == 2 || $rowSym[1] == 3 || $rowSym[1] == 4){
                    $decimal = 0;
                }
                $excelContent .= "\n\nDate\tRevenue";
                $excelContent .= "\n\tRevenues\tExpense\tBalance";
            ?>
            <table class="table_print">
                <thead>
                    <tr>
                        <th style="width: 150px; background: #c8c8c8; text-align: center;" class="fontScreen" rowspan="2">Date</th>
                        <th colspan="3" style="background: #c8c8c8; text-align: center;" class="fontScreen">Revenue</th>
                    </tr>
                    <tr>
                        <th style="background: #c8c8c8; text-align: center;" class="fontScreen">Revenues</th>
                        <th style="background: #c8c8c8; text-align: center;" class="fontScreen">Expense</th>
                        <th style="background: #c8c8c8; text-align: center;" class="fontScreen">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($days AS $day) {
                        $sqlIncome = mysql_query("SELECT SUM(IFNULL(total_amount, 0) - IFNULL(discount_amount, 0)) AS total_amount FROM t_tickets WHERE t_tickets.status > 0 AND t_tickets.date = '".$day."'".$condtion.";");
                        $rowIncome  = mysql_fetch_array($sqlIncome);
                        $sqlExpense = mysql_query("SELECT SUM(expense_details.total_amount / expense_details.rate) AS total_amount FROM expense_details INNER JOIN expenses ON expenses.id = expense_details.expense_id WHERE expenses.status > 0 AND expenses.date = '".$day."'".$conExpense.";");
                        $rowExpense = mysql_fetch_array($sqlExpense);
                        $totalIncome += $rowIncome[0];
                        $totalExpense += $rowExpense[0];
                        $totalProfit += $rowIncome[0] - $rowExpense[0];
                    ?>
                    <tr>
                        <td class="fontScreen">
                        <?php 
                        $excelContent .= "\n".dateShort($day);
                        echo dateShort($day); 
                        ?>
                        </td>
                        <td style="font-weight: bold;" class="fontScreen">
                        <?php 
                        $excelContent .= "\t" . number_format($rowIncome[0], $decimal)." ".$rowSym[0];
                        echo number_format($rowIncome[0], $decimal)." ".$rowSym[0]; 
                        ?>
                        </td>
                        <td style="font-weight: bold;" class="fontScreen">
                        <?php 
                        $excelContent .= "\t" . number_format($rowExpense[0], $decimal)." ".$rowSym[0];
                        echo number_format($rowExpense[0], $decimal)." ".$rowSym[0]; 
                        ?>
                        </td>
                        <td style="font-weight: bold;" class="fontScreen">
                        <?php 
                        $excelContent .= "\t" . number_format($rowIncome[0] - $rowExpense[0], $decimal)." ".$rowSym[0];
                        echo number_format($rowIncome[0] - $rowExpense[0], $decimal)." ".$rowSym[0]; 
                        ?>
                        </td>
                    </tr>
                    <?php
                    }
                    $excelContent .= "\nTotal";
                    ?>
                    <tr>
                        <td class="fontScreen" style="color: red;">Total</td>
                        <td style="font-weight: bold; color: red;" class="fontScreen" id="netProfitRevenuse">
                            <?php 
                            $excelContent .= "\t" . number_format($totalIncome, $decimal)." ".$rowSym[0];
                            echo number_format($totalIncome, $decimal)." ".$rowSym[0]; 
                            ?>
                        </td>
                        <td style="font-weight: bold; color: red;" class="fontScreen" id="netProfitExpense">
                            <?php 
                            $excelContent .= "\t" . number_format($totalExpense, $decimal)." ".$rowSym[0];
                            echo number_format($totalExpense, $decimal)." ".$rowSym[0]; 
                            ?>
                        </td>
                        <td style="font-weight: bold; color: red;" class="fontScreen" id="netProfitBalance">
                            <?php 
                            $excelContent .= "\t" . number_format($totalProfit, $decimal)." ".$rowSym[0]; 
                            echo number_format($totalProfit, $decimal)." ".$rowSym[0]; 
                            $excelContent .= "\n1 UDS = 4000 KHR";
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table cellpadding="0" cellspacing="0" style="margin-top: 10px; width: 100%;" id="netProfitSignature">
                <tr>
                    <td style="width: 50%;">Checked By Branch Manager</td>
                    <td>Prepared By</td>
                </tr>
            </table>
            <?php
            } else {
                $firstDay = $_POST['year']."-".$_POST['month']."-01";
                $lastDay  = date("Y-m-t", strtotime($firstDay));
                $days = listDays($firstDay, $lastDay);
            ?>
            <table class="table_print">
                <thead>
                    <tr>
                        <th style="width: 90px; background: #c8c8c8;">ថ្ងៃ/ខែ/ឆ្នាំ</th>
                        <?php
                        foreach($branches AS $branch){
                        ?>
                        <th style="width: 120px; background: #c8c8c8;"><?php echo $branch['Branch']['name']; ?></th>
                        <?php
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalProfit = array();
                    foreach($days AS $day) {
                    ?>
                    <tr>
                        <td><?php echo dateShort($day); ?></td>
                        <?php
                        foreach($branches AS $branch){
                            $sqlSym = mysql_query("SELECT symbol, id FROM currency_centers WHERE id = (SELECT currency_center_id FROM branches WHERE id = ".$branch['Branch']['id'].")");
                            $rowSym = mysql_fetch_array($sqlSym);
                            $decimal = 2;
                            $symbol  = $rowSym[0];
                            if($rowSym[1] == 2 || $rowSym[1] == 3 || $rowSym[1] == 4){
                                $decimal = 0;
                            }
                            if($_POST['reference'] == 1) {
                                $sqlIncome = mysql_query("SELECT SUM(fee) AS total_amount FROM t_tickets WHERE t_tickets.status > 0 AND t_tickets.date = '".$day."' AND t_tickets.branch_id = ".$branch['Branch']['id'].";");
                            } else {
                                $sqlIncome = mysql_query("SELECT SUM(goods_transfer_details.total_price) AS total_amount FROM move_transportations INNER JOIN move_transportation_details ON move_transportation_details.move_transportation_id = move_transportations.id INNER JOIN goods_transfer_details ON goods_transfer_details.product_id = move_transportation_details.product_id INNER JOIN t_tickets ON t_tickets.id = goods_transfer_details.goods_transfer_id WHERE move_transportations.date = '".$day."' AND move_transportations.branch_id = ".$branch['Branch']['id'].";");
                            }
                            $rowIncome  = mysql_fetch_array($sqlIncome);
                            $sqlExpense = mysql_query("SELECT SUM(expense_details.total_amount / expense_details.rate) AS total_amount FROM expense_details INNER JOIN expenses ON expenses.id = expense_details.expense_id WHERE expenses.status > 0 AND expenses.date = '".$day."' AND expenses.branch_id = ".$branch['Branch']['id'].";");
                            $rowExpense = mysql_fetch_array($sqlExpense);
                            $totalIncome = $rowIncome[0];
                            $totalExpense = $rowExpense[0];
                            $profit = $totalIncome - $totalExpense;
                            if (array_key_exists($branch['Branch']['id'], $totalProfit)) { 
                                $totalProfit[$branch['Branch']['id']]['total'] += $profit;
                            } else {
                                $totalProfit[$branch['Branch']['id']]['total'] = $profit;
                                $totalProfit[$branch['Branch']['id']]['decimal'] = $decimal;
                                $totalProfit[$branch['Branch']['id']]['symbol'] = $symbol;
                            }
                        ?>
                        <td><?php echo number_format($profit, $decimal)." ".$symbol; ?></td>
                        <?php
                        }
                        ?>
                    </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td>សរុប</td>
                        <?php
                        foreach($branches AS $branch){
                            $total = 0;
                            $decimal = 0;
                            $symbol = '';
                            if (array_key_exists($branch['Branch']['id'], $totalProfit)) { 
                                $total = $totalProfit[$branch['Branch']['id']]['total'];
                                $decimal = $totalProfit[$branch['Branch']['id']]['decimal'];
                                $symbol = $totalProfit[$branch['Branch']['id']]['symbol'];
                            }
                            if($total > 0){
                                $total = number_format($total, $decimal)." ".$symbol;
                            }
                        ?>
                        <td><?php echo $total; ?></td>
                        <?php
                        }
                        ?>
                    </tr>
                </tbody>
            </table>
            <?php
            }
            ?>
        </div>
    </div>
    <div style="clear: both;"></div>
    <br />
<?php
$excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp, $excelContent);
fclose($fp);
?>
    <div class="buttons">
        <button type="button" id="<?php echo $btnPrint; ?>" class="positive">
            <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
            <?php echo ACTION_PRINT; ?>
        </button>
        <button type="button" id="<?php echo $btnExport; ?>" class="positive">
            <img src="<?php echo $this->webroot; ?>img/button/csv.png" alt=""/>
            <?php echo ACTION_EXPORT_TO_EXCEL; ?>
        </button>
    </div>
    <div style="clear: both;"></div>
</div>
<div class="rightPanel"></div>