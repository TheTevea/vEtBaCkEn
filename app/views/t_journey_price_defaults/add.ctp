<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); 
$sqlSym = mysql_query("SELECT symbol FROM currency_centers WHERE id = (SELECT currency_center_id FROM branches WHERE id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].") LIMIT 1);");
$rowSym = mysql_fetch_array($sqlSym);
$symbol = $rowSym[0];
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        $("#TJourneyPriceDefaultAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TJourneyPriceDefaultAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTJourneyPriceDefault").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackTJourneyPriceDefault").click();
                // alert message
//                if(result != "<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>" && result != "<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>" && result != "<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>"){
//                    createSysAct('TJourneyPriceDefault', 'Add', 2, result);
//                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
//                }else {
                    createSysAct('TJourneyPriceDefault', 'Add', 1, '');
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
//                }
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
        
        $(".btnBackTJourneyPriceDefault").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTJourneyPriceDefault.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        $("#TJourneyPriceDefaultPrice, #TJourneyPriceDefaultForeignerPrice, #TJourneyPriceDefaultMembership").unbind("focus").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val("");
            }
        });
        
        $("#TJourneyPriceDefaultPrice, #TJourneyPriceDefaultForeignerPrice, #TJourneyPriceDefaultMembership").unbind("blur").blur(function(){
            if($(this).val() == ""){
                $(this).val("0");
            }
        });
        
        $("#TJourneyPriceDefaultApplyTo").unbind("change").change(function(){
            $("#divTJourneyPriceDefaultMainBranchId").hide();
            $("#TJourneyPriceDefaultMainBranchId").removeClass("validate[required]");
            if($(this).val() == "2"){
                $("#divTJourneyPriceDefaultMainBranchId").show();
                $("#TJourneyPriceDefaultMainBranchId").addClass("validate[required]");
            }
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTJourneyPriceDefault">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('TJourneyPriceDefault'); ?>
<fieldset>
    <legend><?php __(MENU_SET_PRICE_DEFAULT_INFO); ?></legend>
    <table>
        <tr>
            <td style="width: 150px;"><label for="TJourneyPriceDefaultApplyTo"><?php echo TABLE_APPLY_TO; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[TJourneyPriceDefault][apply_to]" id="TJourneyPriceDefaultApplyTo" style="width: 210px; height: 35px;" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1"><?php echo TABLE_ALL; ?></option>
                        <option value="2"><?php echo MENU_MAIN_BRANCH; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr id="divTJourneyPriceDefaultMainBranchId" style="display: none;">
            <td><label for="TJourneyPriceDefaultMainBranchId"><?php echo MENU_MAIN_BRANCH; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('main_branch_id', array('style' => 'width: 260px; height: 35px;', 'label' => false, 'empty' => INPUT_SELECT)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPriceDefaultDestinationFromId"><?php echo TABLE_DESTINATION_FROM; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('destination_from_id', array('class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'div' => false, 'style' => 'width: 260px; height: 35px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPriceDefaultDestinationToId"><?php echo TABLE_DESTINATION_TO; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('destination_to_id', array('class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'div' => false, 'style' => 'width: 260px; height: 35px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPriceDefaultTTransportationTypeId"><?php echo MENU_TRANSPORTATION_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('t_transportation_type_id', array('class'=>'validate[required] journeySelectChz', 'label' => false, 'empty' => INPUT_SELECT, 'div' => false, 'style' => 'width: 260px; height: 35px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPriceDefaultPrice"><?php echo TABLE_PRICE." ".TABLE_NORMAL; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('price', array('class'=>'validate[required] float', 'style' => 'width: 200px; height: 20px;')); ?> <?php echo $symbol; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPriceDefaultForeignerPrice"><?php echo TABLE_PRICE." ".TABLE_FOREIGNER; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('foreigner_price', array('class'=>'validate[required] float', 'style' => 'width: 200px; height: 20px;')); ?> <?php echo $symbol; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPriceDefaultMembership"><?php echo TABLE_PRICE." VIP Card"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('membership', array('class'=>'validate[required] float', 'style' => 'width: 200px; height: 20px;')); ?> <?php echo $symbol; ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveTJourneyPriceDefault"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); 
?>