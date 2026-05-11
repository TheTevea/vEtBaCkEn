<?php 
echo $this->element('prevent_multiple_submit'); 
$photoPath = PHOTO_PATH."public/travel_package/";
$sqlDest   = mysql_query("SELECT * FROM travel_package_destinations WHERE travel_package_id = ".$this->data['TravelPackages']['id']);
?>
<style type="text/css" media="screen">
    #sortablePhoto {
        list-style-type: none;
        margin: 0; 
        padding: 0;
        margin-right: 10px; 
        width: 100%;
    }    
    #sortablePhoto li { 
        margin: 0px; 
        padding: 0px; 
        font-size: 1.2em; 
        width: 105px; 
        cursor: pointer;
        float: left; 
    }
</style>
<script type="text/javascript">
    var rowTablePromotionPackageDestinationList  = $("#rowListPromotionPackageDestination");
    $(document).ready(function(){
        $("#rowListPromotionPackageDestination").remove();
        // Prevent Key Enter
        preventKeyEnter();
        $("#PromotionPackageBuvaSea, #PromotionPackageInternationalThai, #PromotionPackageInternationalViet, #PromotionPackageInternationalLaos, #PromotionPackageLocal").autoNumeric({mDec: 0, aSep: ','});
        $("#PromotionPackagePrice").autoNumeric({mDec: 2, aSep: ','});
        $("#PromotionPackagePeriodExpired").autoNumeric({mDec: 0, aSep: ','});
        
        $("#PromotionPackageEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#PromotionPackageEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $("#PromotionPackagePrice, #PromotionPackagePeriodExpired").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSavePromotionPackage").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackPromotionPackage").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Transportation Type', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Transportation Type', 'Add', 1, '');
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
        $(".btnBackPromotionPackage").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePromotionPackage.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });

        $("#PromotionPackageBuvaSea, #PromotionPackageInternationalThai, #PromotionPackageInternationalViet, #PromotionPackageInternationalLaos, #PromotionPackageLocal").unbind("keyup").keyup(function(){
            if(replaceNum($(this).val()) > 100){
                $(this).val(100);
            }
        });

        $("#PromotionPackageApplyDestination").unbind("change").change(function(){
            $(".rowListPromotionPackageDestination").remove();
            clonePromotionPackageDestination();
            if($(this).val() == "2"){
                $("#PromotionPackageDestinationFilter").show();
            } else {
                $("#PromotionPackageDestinationFilter").hide();
            }
        });
        <?php
        if(mysql_num_rows($sqlDest)){
        ?>
        $("#PromotionPackageDestinationFilter").show();
        eventKeyPromotionPackageDestination();
        <?php
        } else {
        ?>
        clonePromotionPackageDestination();
        <?php
        }
        ?>

    });

    function deleteOtherImagePromotionPackage(){
        $(".btnDeletePromotionPackageOtherImg").unbind("click");
        $(".btnDeletePromotionPackageOtherImg").click(function(){
            var divImg = $(this).closest("li");
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to delete image?</p>');
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
                    '<?php echo ACTION_YES; ?>': function() {
                        divImg.remove();
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_NO; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
    }

    function clonePromotionPackageDestination(){
        rowIndexJourney = Math.floor((Math.random() * 100000) + 1);
        var tr = rowTablePromotionPackageDestinationList.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find(".PromotionPackageDestinationFrom").attr("id", "PromotionPackageDestinationFrom_"+rowIndexJourney);
        tr.find(".PromotionPackageDestinationTo").attr("id", "PromotionPackageDestinationTo_"+rowIndexJourney);
        $("#rowPromotionPackageDestination").append(tr);
        var LenTr = parseInt($(".rowListPromotionPackageDestination").length);
        if(LenTr == 1){
            $("#rowPromotionPackageDestination").find("tr:eq(0)").find(".btnRemoveRowPromotionPackageDestination").hide();
            $("#rowPromotionPackageDestination").find("tr:eq(0)").find(".btnAddRowPromotionPackageDestination").show();
        } else {
            $("#rowPromotionPackageDestination").find("tr:eq("+(LenTr - 1)+")").find(".btnRemoveRowPromotionPackageDestination").show();
            $("#rowPromotionPackageDestination").find("tr:eq("+(LenTr - 1)+")").find(".btnAddRowPromotionPackageDestination").show();
        }
        eventKeyPromotionPackageDestination();
    }

    function eventKeyPromotionPackageDestination(){
        $(".PromotionPackageDestinationFrom, .PromotionPackageDestinationTo, .btnAddRowPromotionPackageDestination, .btnRemoveRowPromotionPackageDestination").unbind('click').unbind('change');
        $(".PromotionPackageDestinationFrom, .PromotionPackageDestinationTo").chosen({width: 280});
        
        $(".btnAddRowPromotionPackageDestination").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveRowPromotionPackageDestination").show();
            clonePromotionPackageDestination();
        });
        
        $(".btnRemoveRowPromotionPackageDestination").click(function(){
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
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_OK; ?>': function() {
                        obj.closest("tr").remove();
                        var lenTr = parseInt($(".rowListPromotionPackageDestination").length);
                        if(lenTr == 1){
                            $("#rowPromotionPackageDestination").find("tr:eq(0)").find("td .btnRemoveRowPromotionPackageDestination").hide();
                        }
                        $("#rowPromotionPackageDestination").find("tr:eq("+(lenTr - 1)+")").find("td .btnAddRowPromotionPackageDestination").show();
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPromotionPackage">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('PromotionPackage'); ?>
<input type="hidden" name="data[PromotionPackage][id]" value="<?php echo $this->data['TravelPackages']['id']; ?>" />
<fieldset style="width: 47%; float: left; height: 450px;">
    <legend><?php __(MENU_PROMOTION_PACKAGE_INFO); ?></legend>
    <table style="width: 100%;">
        <tr>
            <td style="width: 30%;"><label for="PromotionPackageName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]', 'value' => $this->data['TravelPackages']['name'])); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="PromotionPackagePeriodExpired"><?php echo "Period Use"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('period_expired', array('class'=>'validate[required]', 'value' => $this->data['TravelPackages']['period_expired'])); ?> Months
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 12px; font-weight: bold;">Apply Discount Condition</td>
        </tr>
        <tr>
            <td><label for="PromotionPackageBuvaSea"><?php echo "Buva Sea"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('buva_sea', array('class'=>'validate[required]', 'maxlength' => '4', 'value' => $this->data['TravelPackages']['buva_sea'])); ?> %
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 12px; font-weight: bold;">International Route</td>
        </tr>
        <tr>
            <td><label for="PromotionPackageInternationalThai"><?php echo " - Thailand"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('international_thai', array('class'=>'validate[required]', 'maxlength' => '4', 'value' => $this->data['TravelPackages']['international_thai'])); ?> %
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="PromotionPackageInternationalViet"><?php echo " - Vietname"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('international_viet', array('class'=>'validate[required]', 'maxlength' => '4', 'value' => $this->data['TravelPackages']['international_viet'])); ?> %
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="PromotionPackageInternationalLaos"><?php echo " - Loas"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('international_laos', array('class'=>'validate[required]', 'maxlength' => '4', 'value' => $this->data['TravelPackages']['international_laos'])); ?> %
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="PromotionPackageLocal"><?php echo "Local Route"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('local', array('class'=>'validate[required]', 'maxlength' => '4', 'value' => $this->data['TravelPackages']['local'])); ?> %
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="PromotionPackageLocal"><?php echo "Apply Destination"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[PromotionPackage][destination_apply]" id="PromotionPackageApplyDestination" style="width: 180px;">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1" <?php if($this->data['TravelPackages']['destination_apply'] == 1) { ?>selected="selected"<?php } ?>><?php echo "All"; ?></option>
                        <option value="2" <?php if($this->data['TravelPackages']['destination_apply'] == 2) { ?>selected="selected"<?php } ?>><?php echo "Customize"; ?></option>
                    </select>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<fieldset style="width: 47%; float: left; min-height: 450px; display: none;" id="PromotionPackageDestinationFilter">
    <legend><?php __("Destination"); ?></legend>
    <table class="table" cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <th class="first" style="width: 40%;"><?php echo TABLE_DESTINATION_FROM; ?></th>
            <th style="width: 40%;"><?php echo TABLE_DESTINATION_TO; ?></th>
            <th style="width: 20%;"></th>
        </tr>
        <tbody id="rowPromotionPackageDestination">
            <tr id="rowListPromotionPackageDestination" class="rowListPromotionPackageDestination">
                <td class="first">
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[destination_from_id][]" class="PromotionPackageDestinationFrom" style="width: 95%; height: 25px;" class="validate[required]">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            $sqlJ = mysql_query("SELECT * FROM t_destinations  WHERE offline_project_id = 1 AND is_active = 1 ORDER BY name ASC;");
                            while($rowJ = mysql_fetch_array($sqlJ)){
                            ?>
                            <option value="<?php echo $rowJ['id']; ?>"><?php echo $rowJ['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                    <select name="data[destination_to_id][]" class="PromotionPackageDestinationTo" style="width: 95%; height: 25px;" class="validate[required]">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            $sqlJ = mysql_query("SELECT * FROM t_destinations  WHERE offline_project_id = 1 AND is_active = 1 ORDER BY name ASC;");
                            while($rowJ = mysql_fetch_array($sqlJ)){
                            ?>
                            <option value="<?php echo $rowJ['id']; ?>"><?php echo $rowJ['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="text-align: center;">
                    <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddRowPromotionPackageDestination" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Add')" />
                    &nbsp;&nbsp;<img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowPromotionPackageDestination" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                </td>
            </tr>
            <?php
            
            while($rowDest = mysql_fetch_array($sqlDest)){
                $rnd = rand();
            ?>
            <tr class="rowListPromotionPackageDestination">
                <td class="first">
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[destination_from_id][]" class="PromotionPackageDestinationFrom" id="PromotionPackageDestinationFrom_<?php echo $rnd; ?>" style="width: 95%; height: 25px;" class="validate[required]">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            $sqlJ = mysql_query("SELECT * FROM t_destinations  WHERE offline_project_id = 1 AND is_active = 1 ORDER BY name ASC;");
                            while($rowJ = mysql_fetch_array($sqlJ)){
                            ?>
                            <option value="<?php echo $rowJ['id']; ?>" <?php if($rowDest['destination_from_id'] == $rowJ['id']) { ?>selected="selected"<?php } ?>><?php echo $rowJ['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                    <select name="data[destination_to_id][]" class="PromotionPackageDestinationTo" id="PromotionPackageDestinationTo_<?php echo $rnd; ?>" style="width: 95%; height: 25px;" class="validate[required]">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            $sqlJ = mysql_query("SELECT * FROM t_destinations  WHERE offline_project_id = 1 AND is_active = 1 ORDER BY name ASC;");
                            while($rowJ = mysql_fetch_array($sqlJ)){
                            ?>
                            <option value="<?php echo $rowJ['id']; ?>" <?php if($rowDest['destination_to_id'] == $rowJ['id']) { ?>selected="selected"<?php } ?>><?php echo $rowJ['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="text-align: center;">
                    <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddRowPromotionPackageDestination" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Add')" />
                    &nbsp;&nbsp;<img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowPromotionPackageDestination" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                </td>
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
</fieldset>
<div style="clear: both;"></div>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSavePromotionPackage"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>