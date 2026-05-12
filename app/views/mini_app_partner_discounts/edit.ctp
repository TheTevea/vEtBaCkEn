<?php
include("includes/function.php");
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    $(document).ready(function(){
        preventKeyEnter();
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        $("#MiniAppPartnerDiscountMiniAppPartnerId").chosen({width: 300});
        $("#MiniAppPartnerDiscountEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#MiniAppPartnerDiscountEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $("#MiniAppPartnerDiscountStartDate, #MiniAppPartnerDiscountEndDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveMiniAppPartnerDiscount").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackMiniAppPartnerDiscount").click();
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('MiniAppPartnerDiscount', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('MiniAppPartnerDiscount', 'Edit', 1, '');
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
        $(".btnBackMiniAppPartnerDiscount").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableMiniAppPartnerDiscount.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        $("#MiniAppPartnerDiscountStartDate, #MiniAppPartnerDiscountEndDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackMiniAppPartnerDiscount">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('MiniAppPartnerDiscount'); ?>
<?php echo $this->Form->input('id'); ?>
<fieldset>
    <legend><?php echo MENU_MINI_APP_DISCOUNT_INFO; ?></legend>
    <table>
        <tr>
            <td><label for="MiniAppPartnerDiscountMiniAppPartnerId"><?php echo MENU_MINI_APP_PARTNER; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('mini_app_partner_id', array('label' => false, 'empty' => INPUT_SELECT, 'div' => false, 'class' => 'validate[required]', 'style' => 'width: 300px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="MiniAppPartnerDiscountFixedDiscount"><?php echo TABLE_FIXED_DISCOUNT; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('fixed_discount', array('class'=>'validate[required] float', 'style' => 'width: 250px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="MiniAppPartnerDiscountPercent"><?php echo TABLE_PERCENT; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('percent', array('class'=>'validate[required] float', 'style' => 'width: 250px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="MiniAppPartnerDiscountStartDate"><?php echo TABLE_START_DATE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <input type="text" name="data[MiniAppPartnerDiscount][start_date]" id="MiniAppPartnerDiscountStartDate" class="validate[required]" style="width: 250px;" value="<?php echo dateShort($this->data['MiniAppPartnerDiscount']['start_date'], "d/m/Y"); ?>" readonly="readonly" />
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="MiniAppPartnerDiscountEndDate"><?php echo TABLE_END_DATE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <input type="text" name="data[MiniAppPartnerDiscount][end_date]" id="MiniAppPartnerDiscountEndDate" class="validate[required]" style="width: 250px;" value="<?php echo dateShort($this->data['MiniAppPartnerDiscount']['end_date'], "d/m/Y"); ?>" readonly="readonly" />
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="MiniAppPartnerDiscountStatus"><?php echo TABLE_STATUS; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[MiniAppPartnerDiscount][status]" id="MiniAppPartnerDiscountStatus" class="validate[required]" style="width: 260px; height: 25px;">
                        <option value="1" <?php echo $this->data['MiniAppPartnerDiscount']['status'] == 1 ? 'selected="selected"' : ''; ?>><?php echo TABLE_ACTIVE; ?></option>
                        <option value="0" <?php echo $this->data['MiniAppPartnerDiscount']['status'] == 0 ? 'selected="selected"' : ''; ?>><?php echo TABLE_INACTIVE; ?></option>
                    </select>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveMiniAppPartnerDiscount"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>
