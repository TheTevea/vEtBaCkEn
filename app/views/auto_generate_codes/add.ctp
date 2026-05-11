<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#AutoGenerateCodeAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#AutoGenerateCodeAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                if($("#AutoGenerateCodeTTransportationTypeId").val() == ""){
                    alertSelectRequireField();
                    $(".btnSaveAutoGenerateCode").removeAttr('disabled');
                    return false;
                }
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveAutoGenerateCode").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackAutoGenerateCode").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('AutoGenerateCode', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('AutoGenerateCode', 'Add', 1, '');
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
        $(".btnBackAutoGenerateCode").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableAutoGenerateCode.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });

        // Store the original row template (remove ID before cloning to avoid duplicates)
        var rowTemplate = $('#rowListControllerFileds').removeAttr('id').clone();

        // Remove any existing handlers to prevent duplicates
        $(document).off('click', '.btnAddControllerFileds');
        $(document).off('click', '.btnRemoveControllerFileds');

        // Add click handler for adding new rows with debounce
        $(document).on('click', '.btnAddControllerFileds', function() {
            addNewRow();
        });

        // Add click handler for removing rows with debounce
        $(document).on('click', '.btnRemoveControllerFileds', function() {
            var $row = $(this).closest('.rowListControllerFileds');
            $row.remove();
            updateRemoveButtonsVisibility();
        });

        function addNewRow() {
            // Clone the template
            var $newRow = rowTemplate.clone();
            
            // Generate more unique IDs by combining timestamp with random number
            var uniqueId = new Date().getTime() + '_' + Math.floor(Math.random() * 1000);
            
            // Update all field IDs
            $newRow.find('.fields_label').attr('id', 'fields_label_' + uniqueId);
            $newRow.find('.fields_name').attr('id', 'fields_name_' + uniqueId);
            $newRow.find('.fields_type').attr('id', 'fields_type_' + uniqueId);
            $newRow.find('.fields_required').attr('id', 'fields_required_' + uniqueId);
            $newRow.find('.fields_custom_select').attr('id', 'fields_custom_select_' + uniqueId);
            $newRow.find('.fields_options').attr('id', 'fields_options_' + uniqueId);
            $newRow.find('.fields_dashboard').attr('id', 'fields_dashboard_' + uniqueId);
            
            // Append to table body
            $('.tblBodyControllerFileds').append($newRow);
            
            // Update remove buttons visibility
            updateRemoveButtonsVisibility();
        }

        function updateRemoveButtonsVisibility() {
            var $rows = $('.rowListControllerFileds');
            var $removeButtons = $rows.find('.btnRemoveControllerFileds');
            // Hide remove button if only one row exists, otherwise show all
            $removeButtons.toggle($rows.length > 1);
        }

    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackAutoGenerateCode">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('AutoGenerateCode'); ?>
<fieldset>
    <legend><?php __("Controller Information"); ?></legend>
    <table>
        <tr>
            <td style="width: 110px;"><label for="AutoGenerateCodeName"><?php echo "Controller Name"; ?> <span class="red">*</span> :</label></td>
            <td style="width: 260px;">
                <div class="inputContainer">
                    <?php echo $this->Form->text('module_name', array('class'=>'validate[required]', 'style' => 'width: 250px;')); ?>
                </div>
            </td>
            <td style="width: 110px;"><label for="AutoGenerateCodeName"><?php echo "Menu Name"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('menu_name', array('class'=>'validate[required]', 'style' => 'width: 250px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <input type="checkbox" name="data[AutoGenerateCode][has_view]" id="AutoGenerateCodeHasView" value="1" style="width: 16px; height: 16px;" />
                <label for="AutoGenerateCodeHasView">Have View</label> 
                <input type="checkbox" name="data[AutoGenerateCode][has_add]" id="AutoGenerateCodeHasAdd" value="1" style="width: 16px; height: 16px;" />
                <label for="AutoGenerateCodeHasAdd">Have Add</label> 
                <input type="checkbox" name="data[AutoGenerateCode][has_edit]" id="AutoGenerateCodeHasEdit" value="1" style="width: 16px; height: 16px;" />
                <label for="AutoGenerateCodeHasEdit">Have Edit</label>  
                <input type="checkbox" name="data[AutoGenerateCode][has_delete]" id="AutoGenerateCodeHasDelete" value="1" style="width: 16px; height: 16px;" />
                <label for="AutoGenerateCodeHasDelete">Have Delete</label>  
            </td>
        </tr>
    </table>
</fieldset>
<br/>
<fieldset>
    <legend><?php __("Fields Information"); ?></legend>
    <table class="table" cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <th class="first" style="width: 15%;"><?php echo "Label"; ?></th>
            <th style="width: 15%;"><?php echo "Name"; ?></th>
            <th style="width: 10%;"><?php echo "Type"; ?></th>
            <th style="width: 10%;">Required</th>
            <th style="width: 15%;">Custom Select</th>
            <th style="width: 15%;">Options</th>
            <th style="width: 15%;">Show Dashboard</th>
            <th></th>
        </tr>
        <tbody class="tblBodyControllerFileds">
            <tr id="rowListControllerFileds" class="rowListControllerFileds">
                <td class="first">
                    <div class="inputContainer" style="width: 100%;">
                        <input type="text" name="data[fields_label][]" class="fields_label" style="width: 100%; height: 25px;" placeholder="Label" />
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <input type="text" name="data[fields_name][]" class="fields_name" style="width: 100%; height: 25px;" placeholder="Field" />
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[fields_type][]" class="fields_type" style="width: 100%;">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <option value="input">Input</option>
                            <option value="select">Select</option>
                            <option value="textarea">Textarea</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[fields_required][]" class="fields_required" style="width: 100%;">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[fields_custom_select][]" class="fields_custom_select" style="width: 100%;">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <input type="text" name="data[fields_options][]" class="fields_options" style="width: 100%; height: 25px;" placeholder="Options" />
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[fields_dashboard][]" class="fields_dashboard" style="width: 100%;">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </td>
                <td style="text-align: center;">
                    <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddControllerFileds" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Add')" />
                    &nbsp;&nbsp;<img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveControllerFileds" align="absmiddle" style="cursor: pointer; display: none;" onmouseover="Tip('Remove')" />
                </td>
            </tr>
        </tbody>   
    </table>
</fieldset> 
<br />
<div class="buttons">
    <button type="submit" class="positive btnSaveAutoGenerateCode">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveAutoGenerateCode"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>