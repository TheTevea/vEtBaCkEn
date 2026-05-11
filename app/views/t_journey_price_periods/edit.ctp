<?php 
include("includes/function.php");
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
        $("#TJourneyPricePeriodDestinationFromId, #TJourneyPricePeriodDestinationToId, #TJourneyPricePeriodTTransportationTypeId").chosen({width: 260});
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        $("#TJourneyPricePeriodEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TJourneyPricePeriodEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $("#TJourneyPricePeriodStart, #TJourneyPricePeriodEnd").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTJourneyPricePeriod").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackTJourneyPricePeriod").click();
                // alert message
//                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
//                    createSysAct('TJourneyPricePeriod', 'Edit', 2, result);
//                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
//                }else {
                    createSysAct('TJourneyPricePeriod', 'Edit', 1, '');
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
        
        $(".btnBackTJourneyPricePeriod").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTJourneyPricePeriod.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        var dates = $("#TJourneyPricePeriodStart, #TJourneyPricePeriodEnd").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "TJourneyPricePeriodStart" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
            }
        });
        
        $("#TJourneyPricePeriodPrice, #TJourneyPricePeriodForeignerPrice, #TJourneyPricePeriodMembership").unbind("focus").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val("");
            }
        });
        
        $("#TJourneyPricePeriodPrice, #TJourneyPricePeriodForeignerPrice, #TJourneyPricePeriodMembership").unbind("blur").blur(function(){
            if($(this).val() == ""){
                $(this).val("0");
            }
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTJourneyPricePeriod">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('TJourneyPricePeriod'); ?>
<?php echo $this->Form->input('id'); ?>
<fieldset>
    <legend><?php __(MENU_SET_PRICE_PERIOD_INFO); ?></legend>
    <table>
        <tr>
            <td style="width: 200px;"><label for="TJourneyPricePeriodApplyTo"><?php echo TABLE_APPLY_TO; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[TJourneyPricePeriod][apply_to]" id="TJourneyPricePeriodApplyTo" style="height: 25px;" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1" <?php if($this->data['TJourneyPricePeriod']['apply_to'] == 1){ ?>selected=""<?php } ?>><?php echo TABLE_ALL; ?></option>
                        <option value="2" <?php if($this->data['TJourneyPricePeriod']['apply_to'] == 2){ ?>selected=""<?php } ?>><?php echo MENU_MAIN_BRANCH; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr id="divTJourneyPricePeriodMainBranchId" <?php if($this->data['TJourneyPricePeriod']['apply_to'] == 1){ ?>style="display: none;"<?php } ?>>
            <td><label for="TJourneyPricePeriodMainBranchId"><?php echo MENU_MAIN_BRANCH; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php 
                    $filedReq = false;
                    if($this->data['TJourneyPricePeriod']['apply_to'] == 2){
                        $filedReq = "validate[required]";
                    }
                    echo $this->Form->input('main_branch_id', array('class' => $filedReq, 'style' => 'height: 20px;', 'label' => false, 'empty' => INPUT_SELECT)); 
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodName"><?php echo GENERAL_DESCRIPTION; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]', 'style' => 'height: 20px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodDestinationFromId"><?php echo TABLE_DESTINATION_FROM; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('destination_from_id', array('class'=>'validate[required] journeySelectChz', 'label' => false, 'empty' => INPUT_SELECT, 'div' => false, 'style' => 'width: 260px; height: 35px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodDestinationToId"><?php echo TABLE_DESTINATION_TO; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('destination_to_id', array('class'=>'validate[required] journeySelectChz', 'label' => false, 'empty' => INPUT_SELECT, 'div' => false, 'style' => 'width: 260px; height: 35px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodTTransportationTypeId"><?php echo MENU_TRANSPORTATION_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('t_transportation_type_id', array('class'=>'validate[required] journeySelectChz', 'label' => false, 'empty' => INPUT_SELECT, 'div' => false, 'style' => 'width: 260px; height: 35px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodStart"><?php echo TABLE_START_DATE; ?></label> <span class="red">*</span> :</td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="data[TJourneyPricePeriod][start]" id="TJourneyPricePeriodStart" class="validate[required]" style="width: 200px; height: 20px;" value="<?php echo dateShort($this->data['TJourneyPricePeriod']['start']); ?>" />
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodEnd"><?php echo TABLE_END_DATE; ?></label> <span class="red">*</span> :</td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="data[TJourneyPricePeriod][end]" id="TJourneyPricePeriodEnd" class="validate[required]" style="width: 200px; height: 20px;" value="<?php echo dateShort($this->data['TJourneyPricePeriod']['end']); ?>" />
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodPriceType"><?php echo TABLE_PRICE_TYPE; ?></label> <span class="red">*</span> :</td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select name="data[TJourneyPricePeriod][price_type]" id="TJourneyPricePeriodPriceType" style="width: 210px; height: 30px;" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1" <?php if($this->data['TJourneyPricePeriod']['price_type'] == 1){ ?>selected=""<?php } ?>><?php echo TABLE_FIX_AMOUNT; ?></option>
                        <option value="2" <?php if($this->data['TJourneyPricePeriod']['price_type'] == 2){ ?>selected=""<?php } ?>><?php echo TABLE_MARKUP_AMOUNT; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodPrice"><?php echo "Selling Price ".TABLE_NORMAL; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('price', array('class'=>'validate[required] float', 'style' => 'width: 200px; height: 20px;')); ?> <?php echo $symbol; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodForeignerPrice"><?php echo "Selling Price ".TABLE_FOREIGNER; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('foreigner_price', array('class'=>'validate[required] float', 'style' => 'width: 200px; height: 20px;')); ?> <?php echo $symbol; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodMembership"><?php echo "Selling Price VIP Card"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('membership', array('class'=>'validate[required] float', 'style' => 'width: 200px; height: 20px;')); ?> <?php echo $symbol; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodMembership"><?php echo "Agency Price (Khmer)"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('agency_price', array('class'=>'validate[required] float', 'style' => 'width: 200px; height: 20px;')); ?> <?php echo $symbol; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodMembership"><?php echo "Agency Price (Foreigner)"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('agency_price_foreigner', array('class'=>'validate[required] float', 'style' => 'width: 200px; height: 20px;')); ?> <?php echo $symbol; ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveTJourneyPricePeriod"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>