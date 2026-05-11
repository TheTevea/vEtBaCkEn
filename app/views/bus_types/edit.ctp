<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#BusTypeTTransportationTypeId").chosen({width: 260});
        $("#BusTypeEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#BusTypeEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                if($("#BusTypeTTransportationTypeId").val() == ""){
                    alertSelectRequireField();
                    $(".btnSaveBusType").removeAttr('disabled');
                    return false;
                }
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveBusType").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackBusType").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('BusType', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('BusType', 'Edit', 1, '');
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
        $(".btnBackBusType").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableBusType.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackBusType">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('BusType'); ?>
<?php echo $this->Form->input('id'); ?>
<fieldset>
    <legend><?php __(MENU_BUS_TYPE_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="BusTypeTTransportationTypeId"><?php echo MENU_TRANSPORTATION_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                <?php echo $this->Form->input('t_transportation_type_id', array('class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'div' => false, 'style' => 'width: 260px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BusTypeName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]', 'style' => 'width: 250px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BusTypeNumberOfSeat"><?php echo "Number of Seat"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('number_of_seat', array('class'=>'validate[required]', 'style' => 'width: 250px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BusTypeApplyRent"><?php echo "Apply Rent"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[BusType][apply_rent]" id="BusTypeApplyRent">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1" <?php if($this->data['BusType']['apply_rent'] == 1){ ?>selected=""<?php } ?>><?php echo ACTION_YES; ?></option>
                        <option value="0" <?php if($this->data['BusType']['apply_rent'] == 0){ ?>selected=""<?php } ?>><?php echo ACTION_NO; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BusTypeApplyRent"><?php echo GENERAL_DESCRIPTION; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('description', array('style' => 'width: 250px;')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveBusType"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>