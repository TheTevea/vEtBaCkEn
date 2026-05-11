<?php 
    include("includes/function.php");
?>
<style type="text/css" media="screen">
    div.print-footer {display: none;}
</style> 
<style type="text/css" media="print">
    div.print_doc { width:100%;}
    #btnDisappearPrint { display: none;}
    div.print-footer {display: block; width:100%} 
</style>
<div class="print_doc">
    <table cellpadding="0" cellspacing="0" style="width: 100%;">
        <thead>
            <tr>
                <td>
                    <?php
                    $msg = 'Expense Report';
                    echo $this->element('/print/header', array('msg' => $msg, 'barcode' => $this->data['Expense']['code']));
                    ?>
                    <div style="height: 10px"></div>
                    <table width="100%">
                        <tr>
                            <td style="width: 10%; font-size: 12px; text-transform: uppercase;">EXP No :</td>
                            <td style="font-size: 12px; text-align: left;"><?php echo $this->data['Expense']['code']; ?></td>
                            <td style="font-size: 12px; text-transform: uppercase;">EXP Date :</td>
                            <td style="font-size: 12px; text-align: left;"><?php echo dateShort($this->data['Expense']['date']); ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td style="height: 90px;">
                    <br/>
                    <table style="width: 100%;" class="print-footer">
                        <tr>
                            <td style="font-size: 12px; width: 33%; vertical-align: top;">REQUESTED BY</td>
                            <td style="text-align: left; font-size: 12px; vertical-align: top;">APPROVED BY</td>
                            <td style="text-align: left; font-size: 12px; width: 33%; vertical-align: top;">RECEIVED BY</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: bottom; height: 80px;">..............................</td>
                            <td style="text-align: left; vertical-align: bottom;">..............................</td>
                            <td style="text-align: left; vertical-align: bottom;">..............................</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tfoot>
        <tbody>
            <tr>
                <td>
                    <table id="tblTO" class="table_print">
                        <tr>
                            <th class="first" style="width:5%"><?php echo TABLE_NO; ?></th>
                            <th style="width:15%;"><?php echo MENU_EXPENSE_TYPE; ?></th>
                            <th style="width:10%;"><?php echo TABLE_REFERENCE; ?></th>
                            <th style="width:35%;"><?php echo GENERAL_DESCRIPTION; ?></th>
                            <th style="width:10%;"><?php echo GENERAL_AMOUNT; ?></th>
                            <th style="width:10%;"><?php echo TABLE_QTY; ?></th>
                            <th style="width:15%;"><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                        </tr>
                        <?php
                            if(!empty($expenseDetails)){
                                $index = 1;
                                $totals = array();
                                foreach($expenseDetails AS $expenseDetail){
                                    if (array_key_exists($expenseDetail['ExpenseDetail']['currency_center_id'], $totals)){
                                        $totals[$expenseDetail['ExpenseDetail']['currency_center_id']]['total'] += $expenseDetail['ExpenseDetail']['total_amount'];
                                    } else {
                                        $totals[$expenseDetail['ExpenseDetail']['currency_center_id']]['total']  = $expenseDetail['ExpenseDetail']['total_amount'];
                                        $totals[$expenseDetail['ExpenseDetail']['currency_center_id']]['symbol'] = $expenseDetail['CurrencyCenter']['symbol'];
                                    }
                        ?>
                        <tr>
                            <td class="first"><?php echo $index; ?></td>
                            <td><?php echo $expenseDetail['ExpenseType']['name']; ?></td>
                            <td><?php echo $expenseDetail['ExpenseDetail']['reference']; ?></td>
                            <td><?php echo $expenseDetail['ExpenseDetail']['description']; ?></td>
                            <td style="text-align: right;"><span style="float: left; width: 20px;"><?php echo $expenseDetail['CurrencyCenter']['symbol']; ?></span><?php echo number_format($expenseDetail['ExpenseDetail']['amount'], 2); ?></td>
                            <td style="text-align: center;"><?php echo number_format($expenseDetail['ExpenseDetail']['qty'], 1); ?></td>
                            <td style="text-align: right;"><span style="float: left; width: 20px;"><?php echo $expenseDetail['CurrencyCenter']['symbol']; ?></span><?php echo number_format($expenseDetail['ExpenseDetail']['total_amount'], 2); ?></td>
                        </tr>
                        <?php
                                $index++;
                            }
                        }
                        ?>
                        <tr>
                            <td colspan="4" style="font-size: 12px; vertical-align: top;">Note: <?php echo nl2br($this->data['Expense']['note']); ?></td>
                            <td style="text-align: right; font-size: 12px; vertical-align: top;">Total</td>
                            <td colspan="2" style="text-align: right; font-size: 14px;">
                                <?php 
                                foreach($totals AS $total){
                                    echo '<span style="float: left; width: 20px;">'.$total['symbol'].'</span>'.number_format($total['total'], 2)."<br/>";
                                }
                                ?>
                                <div style="clear:both"></div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <br />
    <div style="clear:both"></div>
    <div style="float:left;width: 450px">
        <div>
            <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' onClick='window.print();window.close();' class='noprint'>
        </div>
    </div>
    <div style="clear:both"></div>
</div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){

    });
</script>