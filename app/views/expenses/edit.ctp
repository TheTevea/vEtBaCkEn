<?php
include("includes/function.php");
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); 
?>
<script type="text/javascript">
    var indexRowExpense = 0;
    var rowExpenseList  =  $("#rowExpense");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#rowExpense").remove();
        $("#ExpenseEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#ExpenseEditForm").ajaxForm({
            dataType: "json",
            beforeSerialize: function($form, options) {
                $("#ExpenseDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float, .qty").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveExpense").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result.error == 0){
                    $(".btnBackExpense").dblclick();
                    var id = result.id;
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoiceExp" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintInvoice"><?php echo ACTION_PRINT_EXPENSE; ?></span></button></div> ');
                    $(".printInvoiceExp").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+id,
                            beforeSend: function(){
                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                            },
                            success: function(printReceiptResult){
                                w=window.open();
                                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                w.document.write(printReceiptResult);
                                w.document.close();
                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            }
                        });
                    });
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        close: function(){
                            $(this).dialog({close: function(){}});
                            $(this).dialog("close");
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }else if(result.error == 1){
                    $(".btnBackExpense").dblclick();
                    // Alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED ?></p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }else if(result.error == 2){
                    // Alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CODE_ALREADY_EXISTS_IN_THE_SYSTEM ?></p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            }
        });
        
        $("#ExpenseDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        $(".btnBackExpense").dblclick(function(event){
            event.preventDefault();
            $('#ExpenseEditForm').validationEngine('hideAll');
            oCache.iCacheLower = -1;
            oTableExpense.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        // Clone Row Expense List
        eventKeyRowExpense();
    });
    
    function cloneLocatinRow(){
        indexRowExpense = Math.floor((Math.random() * 100000) + 1);
        var tr = rowExpenseList.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .reference").val('');
        tr.find("td .reference").attr("id", "reference_"+indexRowExpense);
        tr.find("td .description").val('');
        tr.find("td .description").attr("id", "description_"+indexRowExpense);
        tr.find("td .amount").val('0');
        tr.find("td .amount").attr("id", "amount_"+indexRowExpense);
        tr.find("td .expenseCurrency").attr("id", "expenseCurrency_"+indexRowExpense);
        tr.find("td .qty").val('1');
        tr.find("td .qty").attr("id", "qty_"+indexRowExpense);
        tr.find("td .total_amount").attr("id", "total_amount_"+indexRowExpense);
        tr.find("td .expense_type").val('');
        tr.find("td .expense_type").attr("id", "expense_type"+indexRowExpense);
        $("#tblExpense").append(tr);
        var LenTr = parseInt($(".rowExpense").length);
        if(LenTr == 1){
            $("#tblExpense").find("tr:eq("+LenTr+")").find(".btnAddExpenseRow").show();
            $("#tblExpense").find("tr:eq("+LenTr+")").find(".btnRemoveExpense").hide();
        }
        tr.find("td .name").focus();
        eventKeyRowExpense();
    }
    
    function eventKeyRowExpense(){
        $(".reference, .amount, .qty, .total_amount, .btnAddExpenseRow, .btnRemoveExpense").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        $(".qty").autoNumeric({mDec: 1, aSep: ','});
    
        $(".reference").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
        
        $(".amount, .total_amount, .qty").focus(function(){
            if($(this).val() == "0"){
                $(this).val("");
            }
        });
        
        $(".amount, .total_amount, .qty").blur(function(){
            if($(this).val() == ""){
                $(this).val("0");
            }
            calcTotalAmountEXP();
        });
        
        $(".amount, .qty").keyup(function(){
            calcTotalAmountEXP();
        });
        
        $(".btnAddExpenseRow").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveExpense").show();
            cloneLocatinRow();
        });
        
        $(".btnRemoveExpense").click(function(){
            var obj = $(this);
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete the selected item(s)?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_DELETE; ?>': function() {
                        obj.closest("tr").remove();
                        var lenTr = parseInt($(".rowExpense").length);
                        if(lenTr == 1){
                            $("#tblExpense").find("tr:eq("+lenTr+")").find("td .btnRemoveExpense").hide();
                        }
                        $("#tblExpense").find("tr:eq("+lenTr+")").find("td .btnAddExpenseRow").show();
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    
    function calcTotalAmountEXP(){
        var totalAmount   = 0;
        $(".rowExpense").each(function(){
            var qty = replaceNum($(this).find(".qty").val());
            var amount = replaceNum($(this).find(".amount").val());
            var totalAmt = converDicemalJS(qty * amount);
            $(this).find(".total_amount").val((totalAmt).toFixed(2));
            totalAmount += replaceNum(totalAmt);
        });
        if(isNaN(totalAmount)){
            $("#ExpenseTotalAmount").val('0.00');
        }else{
            $("#ExpenseTotalAmount").val((totalAmount).toFixed(2));
        }
    }
</script>
<?php 
echo $this->Form->create('Expense');
echo $this->Form->input('id'); 
?>
<input type="hidden" name="data[Expense][sys_code]" value="<?php echo $this->data['Expense']['sys_code']; ?>" />
<div style="display: none;">
    <?php
    foreach($expenseTypes AS $expenseType){
    ?>
    <span class="expenseTypeList" data="<?php echo $expenseType['ExpenseType']['id']; ?>" purpose="<?php echo $expenseType['ExpenseType']['purpose_id']; ?>"><?php echo $expenseType['ExpenseType']['name']; ?></span>
    <?php
    }
    ?>
</div>
<fieldset>
    <legend><?php __(MENU_EXPENSE_INFO); ?></legend>
    <table cellpadding="5" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="width: 10%;"><label for="ExpenseDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
            <td style="width: 20%;">
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('date', array('class'=>'validate[required]', 'readonly' => TRUE, 'style' => 'width: 90%;', 'value' => dateShort($this->data['Expense']['date']))); ?>
                </div>
            </td>
            <td style="vertical-align: top;" rowspan="2"><label for="ExpenseNote"><?php echo TABLE_NOTE; ?> :</label></td>
            <td style="vertical-align: top;" rowspan="2">
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->input('note', array('style' => 'width: 90%; height: 30px;', 'label' => false)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="ExpenseBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span> :</label></td>
            <td style="vertical-align: top;">
                <div class="inputContainer" style="width: 100%;">
                    <select name="data[Expense][branch_id]" id="ExpenseBranchId" class="validate[required]" style="width: 95%;">
                        <?php
                        if(count($branches) != 1){
                        ?>
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        }
                        foreach($branches AS $branch){
                            $selected = '';
                            if($branch['Branch']['id'] == $this->data['Expense']['branch_id']){
                                $selected = 'selected="selected"';
                            }
                        ?>
                        <option value="<?php echo $branch['Branch']['id']; ?>" <?php echo $selected; ?>><?php echo $branch['Branch']['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
        </tr>
    </table>
    <table id="tblExpense" class="table" style="width: 100%;">
        <tr>
            <th class="first" style="width: 20%;"><?php echo MENU_EXPENSE_TYPE; ?> <span class="red">*</span></th>
            <th style="width: 9%;"><?php echo TABLE_REFERENCE; ?> <span class="red">*</span></th>
            <th style="width: 25%;"><?php echo GENERAL_DESCRIPTION; ?> <span class="red">*</span></th>
            <th style="width: 8%;"><?php echo GENERAL_AMOUNT; ?> <span class="red">*</span></th>
            <th style="width: 13%;"><?php echo TABLE_CURRENCY; ?> <span class="red">*</span></th>
            <th style="width: 7%;"><?php echo TABLE_QTY; ?> <span class="red">*</span></th>
            <th style="width: 11%;"><?php echo TABLE_TOTAL_AMOUNT; ?> <span class="red">*</span></th>
            <th style="width: 7%;"><?php echo ACTION_ACTION; ?></th>
        </tr>
        <tr id="rowExpense" class="rowExpense" style="visibility: hidden;">
            <td class="first" style="vertical-align: top; height: 50px;">
                <div class="inputContainer" style="width: 100%;">
                    <select name="expense_type[]" style="width: 90%; height: 25px;" id="expense_type" class="expense_type validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        foreach($expenseTypes AS $expenseType){
                        ?>
                        <option value="<?php echo $expenseType['ExpenseType']['id']; ?>"><?php echo $expenseType['ExpenseType']['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
            <td style="vertical-align: top;">
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="reference[]" style="width: 90%; height: 25px;" id="reference" class="reference validate[required]" />
                </div>
            </td>
            <td style="vertical-align: top;">
                <div class="inputContainer" style="width: 100%;">
                    <textarea name="description[]" style="width: 90%; height: 30px;" id="description" class="description validate[required]"></textarea>
                </div>
            </td>
            <td style="vertical-align: top;">
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="amount[]" value="0" style="width: 90%; height: 25px;" id="amount" class="amount float validate[required]" />
                </div>
            </td>
            <td style="vertical-align: top;">
                <div class="inputContainer" style="width: 100%;">
                    <select id="expenseCurrency" style="width: 90%;" name="currency[]" class="validate[required] expenseCurrency">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        $sqlCur = mysql_query("SELECT id, name FROM currency_centers WHERE is_active = 1 AND id IN (1,2,3,4);");
                        while($rowCur = mysql_fetch_array($sqlCur)){
                            $selected = '';
//                            if($rowCur['id'] == 1){
//                                $selected = 'selected="selected"';
//                            }
                        ?>
                        <option value="<?php echo $rowCur['id']; ?>" <?php echo $selected; ?>><?php echo $rowCur['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
            <td style="vertical-align: top;">
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="qty[]" value="1" style="width: 90%; height: 25px;" id="qty" class="qty validate[required]" />
                </div>
            </td>
            <td style="vertical-align: top;">
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="total_amount[]" value="0" style="width: 90%; height: 25px;" id="total_amount" class="total_amount float validate[required]" />
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <img alt="" src="<?php echo $this->webroot.'img/button/plus.png'; ?>" class="btnAddExpenseRow" style="cursor: pointer;" onmouseover="Tip('Add More')" />
                    &nbsp; <img alt="" src="<?php echo $this->webroot.'img/button/cross.png'; ?>" class="btnRemoveExpense" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                </div>
            </td>
        </tr>
        <?php
        if(!empty($expenseDetails)){
            foreach($expenseDetails AS $expenseDetail){
                $rand = rand();
        ?>
        <tr class="rowExpense">
            <td class="first" style="vertical-align: top; height: 50px;">
                <div class="inputContainer" style="width: 100%;">
                    <select name="expense_type[]" style="width: 90%; height: 25px;" id="expense_type" class="expense_type validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        foreach($expenseTypes AS $expenseType){
                        ?>
                        <option <?php if($expenseType['ExpenseType']['id'] == $expenseDetail['ExpenseDetail']['expense_type_id']){ ?>selected="selected"<?php } ?> value="<?php echo $expenseType['ExpenseType']['id']; ?>"><?php echo $expenseType['ExpenseType']['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
            <td style="vertical-align: top;">
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="reference[]" value="<?php echo $expenseDetail['ExpenseDetail']['reference']; ?>" style="width: 90%; height: 25px;" id="reference<?php echo $rand; ?>" class="reference validate[required]" />
                </div>
            </td>
            <td style="vertical-align: top;">
                <div class="inputContainer" style="width: 100%;">
                    <textarea name="description[]" style="width: 90%; height: 30px;" id="description<?php echo $rand; ?>" class="description validate[required]"><?php echo $expenseDetail['ExpenseDetail']['description']; ?></textarea>
                </div>
            </td>
            <td style="vertical-align: top;">
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="amount[]" value="<?php echo number_format($expenseDetail['ExpenseDetail']['amount'], 2); ?>" style="width: 90%; height: 25px;" id="amount<?php echo $rand; ?>" class="amount float validate[required]" />
                </div>
            </td>
            <td style="vertical-align: top;">
                <div class="inputContainer" style="width: 100%;">
                    <select id="expenseCurrency" style="width: 90%;" name="currency[]" class="validate[required] expenseCurrency">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        $sqlCur = mysql_query("SELECT id, name FROM currency_centers WHERE is_active = 1 AND id IN (1,2,3,4);");
                        while($rowCur = mysql_fetch_array($sqlCur)){
                            $selected = '';
                            if($rowCur['id'] == $expenseDetail['ExpenseDetail']['currency_center_id']){
                                $selected = 'selected="selected"';
                            }
                        ?>
                        <option value="<?php echo $rowCur['id']; ?>" <?php echo $selected; ?>><?php echo $rowCur['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
            <td style="vertical-align: top;">
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="qty[]" value="<?php echo number_format($expenseDetail['ExpenseDetail']['qty'], 0); ?>" style="width: 90%; height: 25px;" id="qty<?php echo $rand; ?>" class="qty validate[required]" />
                </div>
            </td>
            <td style="vertical-align: top;">
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="total_amount[]" value="<?php echo number_format($expenseDetail['ExpenseDetail']['total_amount'], 2); ?>" style="width: 90%; height: 25px;" id="total_amount<?php echo $rand; ?>" class="total_amount float validate[required]" />
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <img alt="" src="<?php echo $this->webroot.'img/button/plus.png'; ?>" class="btnAddExpenseRow" style="cursor: pointer;" onmouseover="Tip('Add More')" />
                    &nbsp; <img alt="" src="<?php echo $this->webroot.'img/button/cross.png'; ?>" class="btnRemoveExpense" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                </div>
            </td>
        </tr>
        <?php
            }
        }
        ?>
    </table>
</fieldset>
<br />
<div class="buttons">
    <div style="float: left; width: 19%;">
        <div class="buttons">
            <a href="#" class="positive btnBackExpense">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <button type="submit" class="positive">
            <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
            <span class="txtSaveExpense"><?php echo ACTION_SAVE; ?></span>
        </button>
        <div style="clear: both;"></div>
    </div>
    <div style="float: right; width:30%; display: none;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 30%;"><label for="ExpenseTotalAmount"><?php echo TABLE_TOTAL_AMOUNT; ?>:</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%">
                        <?php echo $this->Form->text('total_amount', array('readonly' => true, 'class' => 'float validate[required]', 'style' => 'width: 200px; font-size:12px; font-weight: bold', 'value' => number_format($this->data['Expense']['total_amount'], 2))); ?> 
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>