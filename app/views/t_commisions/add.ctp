<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        $(".percent").autoNumeric({mDec: 2, aSep: ',', mNum: 3});
        $("#TCommisionAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TCommisionAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $(".float, .percent").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTCommision").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackTCommision").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Commision', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Commision', 'Add', 1, '');
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
        $(".btnBackTCommision").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTCommision.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
    
    function validate2fieldsTCommision(){
        if($("#TCommisionPercentage").val() =="" && $("#TCommisionAmount").val() == ""){
            $("#TCommisionPercentage, #TCommisionAmount").addClass("validate[required]");
            return "Please fill in one of discount or percentage.";
        } else if($("#TCommisionPercentage").val() !="" && $("#TCommisionAmount").val() != ""){
            $("#TCommisionPercentage, #TCommisionAmount").addClass("validate[required]");
            return "Please fill in one of discount or percentage.";
        }else{
            $("#TCommisionPercentage, #TCommisionAmount").removeClass("validate[required]");
        }
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTCommision">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('TCommision'); ?>
<fieldset>
    <legend><?php __(MENU_COMMISION_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="TCommisionName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TCommisionAmount"><?php echo GENERAL_AMOUNT; ?> <span class="blue">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('amount', array('class'=>'validate[funcCall[validate2fieldsTCommision]] float')); ?> ($)
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TCommisionPercentage"><?php echo TABLE_PERCENT; ?> <span class="blue">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('percentage', array('class'=>'validate[funcCall[validate2fieldsTCommision]] percent')); ?> (%)
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveTCommision"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>