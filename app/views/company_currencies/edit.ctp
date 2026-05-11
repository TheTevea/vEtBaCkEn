<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#CompanyCurrencyEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#CompanyCurrencyEditForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveCompanyCurrency").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackCompanyCurrency").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Company Currency', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Company Currency', 'Edit', 1, '');
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                }
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
        });
        $(".btnBackCompanyCurrency").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableCompanyCurrency.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        $("#CompanyCurrencyCompanyId").change(function(){
            checkCurrencyCompany();
        });
        
        checkCurrencyCompany();
    });
    
    function checkCurrencyCompany(){
        var dCurrency = $("#CompanyCurrencyCompanyId").find("option:selected").attr("dcur");
        if(dCurrency != ''){
            $("#CompanyCurrencyCurrencyCenterId").find("option[value='"+dCurrency+"']").hide();
            $("#CompanyCurrencyCurrencyCenterId").attr("disabled", false);
        } else{
            $("#CompanyCurrencyCurrencyCenterId").find("option[value='']").attr("selected", true);
            $("#CompanyCurrencyCurrencyCenterId").attr("disabled", true);
        }
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackCompanyCurrency">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('CompanyCurrency', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<?php echo $this->Form->input('id'); ?>
<fieldset>
    <legend><?php __(MENU_COMPANY_CURRENCY_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="CompanyCurrencyCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[CompanyCurrency][company_id]" id="CompanyCurrencyCompanyId" class="validate[required]">
                        <?php
                        if(count($companies) != 1){
                        ?>
                        <option value="" dcur=""><?php echo INPUT_SELECT; ?></option>
                        <?php 
                        }
                        foreach($companies AS $company){  
                        ?>
                        <option <?php if($company['Company']['id'] == $this->data['CompanyCurrency']['company_id']){ ?>selected="selected"<?php } ?> value="<?php echo $company['Company']['id']; ?>" dcur="<?php echo $company['Company']['currency_center_id']; ?>"><?php echo $company['Company']['name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="CompanyCurrencyCurrencyCenterId"><?php echo MENU_CURRENCY; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('currency_center_id', array('class' => 'validate[required]', 'empty' => INPUT_SELECT)); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSaveCompanyCurrency"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>