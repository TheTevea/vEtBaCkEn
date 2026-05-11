<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#TDropOffEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TDropOffEditForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTDropOff").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackTDropOff").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('TDropOff', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('TDropOff', 'Edit', 1, '');
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
        $(".btnBackTDropOff").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTDropOff.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTDropOff">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('TDropOff'); 
echo $this->Form->input('id'); 
echo $this->Form->hidden('sys_code');
?>
<fieldset>
    <legend><?php __(MENU_EDIT_DROP_OFF); ?></legend>
    <table>
        <tr>
            <td><label for="TDropOffBranchId"><?php echo TABLE_BRANCH; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('branch_id', array('class'=>'validate[required]', 'div' => false, 'label' => false, 'empty' => INPUT_SELECT)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TDropOffNameKh"><?php echo TABLE_NAME; ?> (Khmer) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name_kh', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TDropOffName"><?php echo TABLE_NAME; ?> (English) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TDropOffNameCh"><?php echo TABLE_NAME; ?> (Chinese) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name_ch', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TDropOffContact"><?php echo TABLE_CONTACT_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('contact', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TDropOffTelephone"><?php echo TABLE_TELEPHONE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('telephone', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TDropOffAddressKh"><?php echo TABLE_ADDRESS; ?> (Khmer) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('address_kh', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TDropOffAddress"><?php echo TABLE_ADDRESS; ?> (English) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('address', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TDropOffAddressCh"><?php echo TABLE_ADDRESS; ?> (Chinese) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('address_ch', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TDropOffLongs"><?php echo TABLE_LONG; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('longs', array()); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TDropOffLats"><?php echo TABLE_LAT; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('lats', array()); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveTDropOff"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>