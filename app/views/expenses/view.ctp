<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackExpense").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableExpense.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackExpense">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_EXPENSE_INFO); ?></legend>
    <table width="100%">
        <tr>
            <td style="width: 10%; font-size: 12px; text-transform: uppercase;"><?php echo TABLE_CODE; ?> :</td>
            <td style="font-size: 12px; text-align: left;"><?php echo $this->data['Expense']['code']; ?></td>
            <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_DATE; ?> :</td>
            <td style="font-size: 12px; text-align: left;"><?php echo dateShort($this->data['Expense']['date']); ?></td>
        </tr>
        <tr>
            <td style="vertical-align: top; font-size: 12px;"><?php echo TABLE_NOTE; ?> :</td>
            <td style="font-size: 12px;"><?php echo nl2br($this->data['Expense']['note']); ?></td>
        </tr>
    </table>
    <br/>
    <div>
        <table class="table">
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
                <td colspan="4" style="font-size: 12px; vertical-align: top;"></td>
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
    </div>
</fieldset>