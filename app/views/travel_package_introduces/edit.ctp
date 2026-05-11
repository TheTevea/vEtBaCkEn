<?php 
echo $this->element('prevent_multiple_submit'); 
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        
        $("#TravelPackageIntroduceEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TravelPackageIntroduceEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $("#TravelPackageIntroducePrice, #TravelPackageIntroducePeriodExpired").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTravelPackageIntroduce").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackTravelPackageIntroduce").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('travel Package Introduce', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('travel Package Introduce', 'Add', 1, '');
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
        $(".btnBackTravelPackageIntroduce").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTravelPackageIntroduce.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });

    });
    
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTravelPackageIntroduce">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('TravelPackageIntroduce'); 
echo $this->Form->input('id');
?>
<fieldset style="width: 99%;">
    <legend><?php __(MENU_TRAVEL_PACKAGE_INTRODUCT_INFO); ?></legend>
    <table style="width: 100%;">
        <tr>
            <td style="width: 150px;"><label for="TravelPackageIntroduceTitleKh"><?php echo "Title"; ?> (Khmer) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('title_kh', array('style'=>'width: 400px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackageIntroduceTitleEn"><?php echo "Title"; ?> (English) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('title_en', array('style'=>'width: 400px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackageIntroduceTitleCn"><?php echo "Title"; ?> (Chinese) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('title_cn', array('style'=>'width: 400px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackageIntroduceDescKh"><?php echo GENERAL_DESCRIPTION; ?> (Khmer) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('desc_kh', array('style'=>'height: 200px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackageIntroduceDescEn"><?php echo GENERAL_DESCRIPTION; ?> (English) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('desc_en', array('style'=>'height: 200px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackageIntroduceDescCn"><?php echo GENERAL_DESCRIPTION; ?> (Chinese) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('desc_cn', array('style'=>'height: 200px')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<div style="clear: both;"></div>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveTravelPackageIntroduce"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>