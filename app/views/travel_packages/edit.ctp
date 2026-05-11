<?php 
echo $this->element('prevent_multiple_submit'); 

$photoPath = PHOTO_PATH."public/travel_package/";
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
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#TravelPackageBuvaSea, #TravelPackageInternationalThai, #TravelPackageInternationalViet, #TravelPackageInternationalLaos, #TravelPackageLocal").autoNumeric({mDec: 0, aSep: ','});
        $("#TravelPackageAmenityId").chosen({width: 400});
        $("#TravelPackagePeriodExpired").autoNumeric({mDec: 0, aSep: ','});
        
        $("#TravelPackageEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TravelPackageEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $("#TravelPackagePrice, #TravelPackagePeriodExpired").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTravelPackage").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackTravelPackage").click();
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
        $(".btnBackTravelPackage").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTravelPackage.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });

        // Upload Image
        // From Action Upload Photo
        $("#TravelPackageFormUploadImage").ajaxForm({
            dataType: "json",
            beforeSerialize: function($form, options) {
                extArray = new Array(".bmp",".jpg",".gif",".tif",".png");
                allowSubmit = false;
                file = $("#TravelPackagePhotoUpload").val();
                if (!file) return;
                while (file.indexOf("\\") != -1)
                    file = file.slice(file.indexOf("\\") + 1);
                ext = file.slice(file.indexOf(".")).toLowerCase();
                for (var i = 0; i < extArray.length; i++) {
                    if (extArray[i] == ext) { allowSubmit = true; break; }
                }
                if (!allowSubmit){
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please only upload files that end in types: <b>' + (extArray.join("  ")) + '</b>. Please select a new file to upload again.</p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position: 'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                    return false;
                }
            },
            beforeSend: function() {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("#TravelPackagePhoto").val(result.img);
                $("#TravelPackagePhotoDisplay").attr("src", "<?php echo $photoPath; ?>" + result.img);
            }
        });

        $("#TravelPackageFormUploadOtherImage").ajaxForm({
            dataType: "json",
            beforeSerialize: function($form, options) {
                extArray = new Array(".bmp",".jpg",".gif",".tif",".png");
                allowSubmit = false;
                file = $("#TravelPackagePhotoUploadOther").val();
                if (!file) return;
                while (file.indexOf("\\") != -1)
                    file = file.slice(file.indexOf("\\") + 1);
                ext = file.slice(file.indexOf(".")).toLowerCase();
                for (var i = 0; i < extArray.length; i++) {
                    if (extArray[i] == ext) { allowSubmit = true; break; }
                }
                if (!allowSubmit){
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please only upload files that end in types: <b>' + (extArray.join("  ")) + '</b>. Please select a new file to upload again.</p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position: 'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                    return false;
                }
            },
            beforeSend: function() {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                var index = Math.floor((Math.random() * 100000) + 1);
                var imgDiv = '<li id="sortPhoto'+index+'"><div style="float: left; width: 100px; margin-left: 3px; margin-bottom: 3px;"><img class="btnDeleteTravelPackageOtherImg" src="<?php echo $this->webroot; ?>img/button/delete.png" style="z-index: 99999; width: 18px; cursor: pointer;"/><img src="<?php echo $photoPath; ?>'+ result.img+'" style="width: 100px; height: 65px;" /><input type="hidden" value="'+ result.img+'" name="data[photo_other][]" /></div></li>';
                $("#sortablePhoto").append(imgDiv);
                deleteOtherImageTravelPackage();
            }
        });

        $("#TravelPackageUpload").on("change", function (event) {
            let fileInputElement = document.getElementById('TravelPackagePhotoUpload');
            fileInputElement.files = event.target.files;
            $("#TravelPackageFormUploadImage").submit();
        });

        $("#TravelPackageUploadOtherImage").on("change", function (event) {
            let fileInputElement = document.getElementById('TravelPackagePhotoUploadOther');
            fileInputElement.files = event.target.files;
            $("#TravelPackageFormUploadOtherImage").submit();
        });

        $("#sortablePhoto").sortable({
            revert: true
        });

        $("#TravelPackageBuvaSea, #TravelPackageInternationalThai, #TravelPackageInternationalViet, #TravelPackageInternationalLaos, #TravelPackageLocal").unbind("keyup").keyup(function(){
            if(replaceNum($(this).val()) > 100){
                $(this).val(100);
            }
        });

        deleteOtherImageTravelPackage();

    });

    function deleteOtherImageTravelPackage(){
        $(".btnDeleteTravelPackageOtherImg").unbind("click");
        $(".btnDeleteTravelPackageOtherImg").click(function(){
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
    
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTravelPackage">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<form id="TravelPackageFormUploadImage" action="<?php echo $this->base; ?>/travel_packages/upload" method="post" enctype="multipart/form-data">
    <table style="display: none;">
        <tr>
            <td>
                <input type="file" name="photo" id="TravelPackagePhotoUpload" />
            </td>
        </tr>
    </table>
</form>
<form id="TravelPackageFormUploadOtherImage" action="<?php echo $this->base; ?>/travel_packages/upload" method="post" enctype="multipart/form-data">
    <table style="display: none;">
        <tr>
            <td>
                <input type="file" name="photo" id="TravelPackagePhotoUploadOther" />
            </td>
        </tr>
    </table>
</form>
<?php 
echo $this->Form->create('TravelPackage'); 
echo $this->Form->input('id');
?>
<input type="hidden" name="data[TravelPackage][photo_path]" value="<?php echo $photoPath; ?>" />
<fieldset style="width: 47%; float: left; height: 1450px;">
    <legend><?php __(MENU_TRAVEL_PACKAGE_INFO); ?></legend>
    <table style="width: 100%;">
        <tr>
            <td colspan="2" style="width: 110px;">
                <table>
                    <tr>
                        <td>
                            <?php
                            $img = "";
                            if(!empty($this->data['TravelPackage']['photo'])){
                                $img = $this->data['TravelPackage']['photo_path'].$this->data['TravelPackage']['photo'];
                            }
                            ?>
                            <input type="hidden" name="data[TravelPackage][photo]" id="TravelPackagePhoto" value="<?php echo $this->data['TravelPackage']['photo']; ?>" />
                            <img src="<?php echo $img; ?>" id="TravelPackagePhotoDisplay" style="width: 150px; height: 100px;" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Size: 720 * 480 Px
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <input type="file" id="TravelPackageUpload" />
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="width: 30%;"><label for="TravelPackageNameKh"><?php echo TABLE_NAME; ?> (Khmer) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name_kh', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 30%;"><label for="TravelPackageName"><?php echo TABLE_NAME; ?> (English) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 30%;"><label for="TravelPackageNameCn"><?php echo TABLE_NAME; ?> (Chinese) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name_cn', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackagePrice"><?php echo TABLE_PRICE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('price', array('class'=>'validate[required]', 'value' => number_format($this->data['TravelPackage']['price'], 2))); ?> $
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackageDiscount"><?php echo GENERAL_DISCOUNT; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('discount', array('class'=>'validate[required]', 'value' => number_format($this->data['TravelPackage']['discount'], 2))); ?> $
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackagePeriodExpired"><?php echo "Period Use"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('period_expired', array('class'=>'validate[required]', 'value' => number_format($this->data['TravelPackage']['period_expired'], 0))); ?> Months
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackageDescriptionKh"><?php echo GENERAL_DESCRIPTION; ?> (Khmer) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('description_kh', array('style'=>'height: 70px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackageDescription"><?php echo GENERAL_DESCRIPTION; ?> (English) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('description', array('style'=>'height: 70px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackageDescriptionCn"><?php echo GENERAL_DESCRIPTION; ?> (Chinese) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('description_cn', array('style'=>'height: 70px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackageTermConditionKh"><?php echo "Term and Conditions"; ?> (Khmer) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('term_condition_kh', array('style'=>'height: 200px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackageTermCondition"><?php echo "Term and Conditions"; ?> (English) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('term_condition', array('style'=>'height: 200px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackageTermConditionCn"><?php echo "Term and Conditions"; ?> (Chinese) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('term_condition_cn', array('style'=>'height: 200px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 12px; font-weight: bold;">Apply Discount Condition</td>
        </tr>
        <tr>
            <td><label for="TravelPackageBuvaSea"><?php echo "Buva Sea"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('buva_sea', array('class'=>'validate[required]', 'maxlength' => '4', 'value' => number_format($this->data['TravelPackage']['buva_sea'], 0))); ?> %
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 12px; font-weight: bold;">International Route</td>
        </tr>
        <tr>
            <td><label for="TravelPackageInternationalThai"><?php echo " - Thailand"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('international_thai', array('class'=>'validate[required]', 'maxlength' => '4', 'value' => number_format($this->data['TravelPackage']['international_thai'], 0))); ?> %
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackageInternationalViet"><?php echo " - Vietname"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('international_viet', array('class'=>'validate[required]', 'maxlength' => '4', 'value' => number_format($this->data['TravelPackage']['international_viet'], 0))); ?> %
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackageInternationalLaos"><?php echo " - Loas"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('international_laos', array('class'=>'validate[required]', 'maxlength' => '4', 'value' => number_format($this->data['TravelPackage']['international_laos'], 0))); ?> %
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TravelPackageLocal"><?php echo "Local Route"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('local', array('class'=>'validate[required]', 'maxlength' => '4', 'value' => number_format($this->data['TravelPackage']['local'], 0))); ?> %
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<fieldset style="width: 49%; float: right; height: 450px;">
    <legend><?php __("Other Photo"); ?></legend>
    <table style="width: 100%;" cellpadding="3">
        <tr>
            <td>Upload Other Image for Slide (Size: 720 * 480 Px)</td>
        </tr>
        <tr>
            <td style="border-bottom: 2px solid #000; padding-bottom: 15px;">
                <input type="file" id="TravelPackageUploadOtherImage" />
            </td>
        </tr>
        <tr>
            <td valign="top">
                <ul id="sortablePhoto">
                    <?php
                    $sqlOtherPhoto = mysql_query("SELECT * FROM travel_package_photos WHERE travel_package_id = ".$this->data['TravelPackage']['id']);
                    while($rowOtherPhoto = mysql_fetch_array($sqlOtherPhoto)){
                    ?>
                        <li id="sortPhoto<?php echo $rowOtherPhoto['id']; ?>">
                            <img class="btnDeleteTravelPackageOtherImg" src="<?php echo $this->webroot; ?>img/button/delete.png" style="z-index: 99999; width: 18px; cursor: pointer;"/>
                            <img src="<?php echo $rowOtherPhoto['photo_path'].$rowOtherPhoto['photo']; ?>" style="width: 100px; height: 65px;" />
                            <input type="hidden" value="<?php echo $rowOtherPhoto['photo']; ?>" name="data[photo_other][]" />
                        </li>
                    <?php
                    }
                    ?>
                </ul>
            </td>
        </tr>
    </table>
</fieldset>
<div style="clear: both;"></div>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveTravelPackage"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>