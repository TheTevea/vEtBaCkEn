<?php
include('includes/function.php');
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
?>
<style type="text/css" media="screen">
    .fontScreen {font-size: 12px;}
</style> 
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
        <style type="text/css" media="print">
            .fontScreen {font-size: 9px;}
        </style>
        <?php
        $condtion = '';
        $conExpense = '';
        $msg = '<b style="font-size: 12px;">Net Profit</b><br />';
        if($_POST['company']!='') {
            $sqlBranch = mysql_query("SELECT name FROM branches WHERE id = ".$_POST['company']);
            $rowBranch = mysql_fetch_array($sqlBranch);
            $msg .= ''.$rowBranch[0].'<br/>';
            $condtion .= ' AND goods_transfers.branch_id = '.$_POST['company'];
            $conExpense .= ' AND expenses.branch_id = '.$_POST['company'];
        } else {
            $condtion .= ' AND goods_transfers.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
            $conExpense .= ' AND expenses.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
        }
        if($_POST['branch']!='') {
            $sqlBranch = mysql_query("SELECT name FROM branches WHERE id = ".$_POST['branch']);
            $rowBranch = mysql_fetch_array($sqlBranch);
            $msg .= ''.$rowBranch[0].'<br/>';
            $condtion .= ' AND goods_transfers.branch_id = '.$_POST['branch'];
            $conExpense .= ' AND expenses.branch_id = '.$_POST['branch'];
        } else {
            $condtion .= ' AND goods_transfers.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
            $conExpense .= ' AND expenses.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
        }
        if($_POST['date_from']!='') {
            $msg .= 'From : '.$_POST['date_from'];
        }
        if($_POST['date_to']!='' && $_POST['date_to'] != $_POST['date_from']) {
            $msg .= ' To: '.$_POST['date_to'];
        }
        echo $this->element('/print/header-report',array('msg'=>$msg));
        $symbol = '';
        $decimal = 2;
        if($_POST['branch']!=''){
            $sqlSym = mysql_query("SELECT symbol, id FROM currency_centers WHERE id = (SELECT currency_center_id FROM branches WHERE id = {$_POST['branch']});");
            $rowSym = mysql_fetch_array($sqlSym);
            $symbol = $rowSym[0];
            if($rowSym[1] == 2 || $rowSym[1] == 3 || $rowSym[1] == 4){
                $decimal = 0;
            }
        }
        ?>
        <div id="dynamic">
            <table class="table_print" cellspacing="0">
                <thead>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 5px;">Revenues</td>
                    </tr>
                    <tr>
                        <th class="first" style="width: 10%; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th style="width: 28%; font-weight: bold; padding: 5px; background: #c8c8c8;">Item Type</th>
                        <th style="font-weight: bold; padding: 5px; background: #c8c8c8;">Name of Goods</th>
                        <th style="width: 20%; font-weight: bold; padding: 5px; background: #c8c8c8;">Fee</th>
                        <th style="width: 5%; padding: 5px; background: #c8c8c8; display: none;" class="rowView"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalIncome = 0;
                    $totalExpense = 0;
                    $index = 0;
                    $sqlAct = mysql_query("SELECT SUM(goods_transfer_details.total_price) AS total_amount, pgroups.name, goods_transfer_details.note FROM goods_transfers INNER JOIN goods_transfer_details ON goods_transfer_details.goods_transfer_id = goods_transfers.id INNER JOIN pgroups ON pgroups.id = goods_transfer_details.pgroup_id WHERE goods_transfers.status > 0 AND goods_transfers.date >= '".dateConvert($_POST['date_from'])."' AND goods_transfers.date <= '".dateConvert($_POST['date_to'])."'".$condtion." GROUP BY goods_transfer_details.pgroup_id;");
                    if(mysql_num_rows($sqlAct)){
                        while($rowAct = mysql_fetch_array($sqlAct)){
                            $totalIncome += $rowAct['total_amount'];
                    ?>
                    <tr>
                        <td style="padding: 5px; text-align: center;"><?php echo ++$index; ?></td>
                        <td style="padding: 5px;"><?php echo $rowAct['name']; ?></td>
                        <td style="padding: 5px;"><?php echo $rowAct['note']; ?></td>
                        <td style="text-align: right; padding: 5px; font-weight: bold;"><span style="float: left; width:20px;"><?php echo $symbol; ?></span><?php echo number_format($rowAct['total_amount'], $decimal); ?></td>
                        <td style="padding: 5px; display: none;" class="rowView">
                            <a href="#" class="viewIncomeDetail"><img alt="View" onmouseover="Tip('<?php echo ACTION_VIEW; ?>')" src="<?php echo $this->webroot; ?>img/button/view.png" /></a>
                        </td>
                    </tr>
                    <?php
                            }
                    ?>
                    <tr>
                        <td style="text-align: right; padding: 5px; font-weight: bold;" colspan="3">Total Revenues (1)</td>
                        <td style="text-align: right; padding: 5px; font-weight: bold;"><span style="float: left; width:20px;"><?php echo $symbol; ?></span><?php echo number_format($totalIncome, $decimal); ?></td>
                        <td class="rowView" style="display: none;"></td>
                    </tr>
                    <?php
                    } else {
                    ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
                    </tr>
                    <?php        
                    }
                    ?>
                </tbody>
            </table>
            <br />
            <table class="table_print" cellspacing="0">
                <tr>
                    <td colspan="3" style="text-align: center; padding: 5px;">Expenses</td>
                </tr>
                <tr>
                    <td class="first" style="width: 10%; font-weight: bold; padding: 5px; text-align: center; background: #c8c8c8;">No</td>
                    <td class="first" style="font-weight: bold; padding: 5px; text-align: center; background: #c8c8c8;">Description</td>
                    <td class="first" style="width: 20%; font-weight: bold; padding: 5px; text-align: center; background: #c8c8c8;">Amount</td>
                </tr>
                <?php
                $sqlExpense = mysql_query("SELECT expense_types.name, SUM(expense_details.total_amount / expense_details.rate) AS total_amount FROM expense_details INNER JOIN expenses ON expenses.id = expense_details.expense_id INNER JOIN expense_types ON expense_types.id = expense_details.expense_type_id WHERE expenses.status > 0 AND expenses.date >= '".dateConvert($_POST['date_from'])."' AND expenses.date <= '".dateConvert($_POST['date_to'])."'".$conExpense." GROUP BY expense_details.expense_type_id;");
                $index = 0;
                while($rowExpense = mysql_fetch_array($sqlExpense)){
                    $totalExpense += $rowExpense['total_amount'];
                ?>
                <tr>
                    <td style="padding: 5px; text-align: center;"><?php echo ++$index; ?></td>
                    <td style="padding: 5px;"><?php echo $rowExpense['name']; ?></td>
                    <td style="padding: 5px; text-align: right; font-weight: bold;"><span style="float: left; width:20px;"><?php echo $symbol; ?></span><?php echo number_format($rowExpense['total_amount'], $decimal); ?></td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td style="text-align: right; padding: 5px; font-weight: bold;" colspan="2">Total Expense (2)</td>
                    <td style="text-align: right; padding: 5px; font-weight: bold;"><span style="float: left; width:20px;"><?php echo $symbol; ?></span><?php echo number_format($totalExpense, $decimal); ?></td>
                </tr>
                <tr>
                    <td style="text-align: right; padding: 5px; font-weight: bold;" colspan="2">Net Profit for the day = (1) - (2)</td>
                    <td style="text-align: right; padding: 5px; font-weight: bold;"><span style="float: left; width:20px;"><?php echo $symbol; ?></span><?php echo number_format(($totalIncome - $totalExpense), $decimal); ?></td>
                </tr>
            </table>
            <table cellpadding="0" cellspacing="0" style="margin-top: 15px; display: none; width: 100%;" id="singatureNetProfit">
                <tr>
                    <td style="width: 25%; font-weight: bold; text-align: center;">បានឃើញនិងឯកភាព</td>
                    <td style="width: 25%; font-weight: bold; text-align: center;">ប្រធានផ្នែកឥវ៉ាន់</td>
                    <td style="width: 25%; font-weight: bold; text-align: center;">អ្នកត្រួតពិនិត្យ</td>
                    <td style="width: 25%; font-weight: bold; text-align: center;">អ្នកធ្វើរបាយការណ៏</td>
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