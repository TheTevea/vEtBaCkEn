<?php 
echo $this->element('prevent_multiple_submit'); 

$photoPath = PHOTO_PATH."public/travel_package_order/";
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
        $(".PromotionApplyPackageSelected, #PromotionApplyPackageNationality").chosen({width: 280}); 
        $("#PromotionApplyPackageAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#PromotionApplyPackageAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $("#PromotionApplyPackagePrice, #PromotionApplyPackagePeriodExpired").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                $("#PromotionApplyPackagePackageExpired").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSavePromotionApplyPackage").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackPromotionApplyPackage").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>' && result != '<?php echo "Please register user in app first, Before you can create."; ?>'){
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
        $(".btnBackPromotionApplyPackage").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePromotionApplyPackage.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });

        $("#PromotionApplyPackagePackageExpired").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
        });

        // Upload Image
        // From Action Upload Photo
        $("#PromotionApplyPackageFormUploadImage").ajaxForm({
            dataType: "json",
            xhrFields: {
                withCredentials: true
            },
            beforeSerialize: function($form, options) {
                extArray = new Array(".jpeg",".jpg",".png");
                allowSubmit = false;
                file = $("#PromotionApplyPackagePhotoUpload").val();
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
                $("#PromotionApplyPackagePhoto").val(result.img);
                $("#PromotionApplyPackagePhotoDisplay").attr("src", "<?php echo $photoPath; ?>" + result.img);
            }
        });

        $("#PromotionApplyPackageUpload").on("change", function (event) {
            let fileInputElement = document.getElementById('PromotionApplyPackagePhotoUpload');
            fileInputElement.files = event.target.files;
            $("#PromotionApplyPackageFormUploadImage").submit();
        });

        $("#PromotionApplyPackageNationality").unbind("change").change(function(){
            $("#PromotionApplyPackagePassport, #PromotionApplyPackageTelephone").removeClass("validate[required]");
            $(".requiredSymbolTel, .requiredSymbolPass").text("");
            if(replaceNum($(this).val()) != 36){
                $("#PromotionApplyPackagePassport").addClass("validate[required]");
                $(".requiredSymbolPass").text("*");
            } else {
                $("#PromotionApplyPackageTelephone").addClass("validate[required]");
                $(".requiredSymbolTel").text("*");
            }
        });

    });
    
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPromotionApplyPackage">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<form id="PromotionApplyPackageFormUploadImage" action="<?php echo $this->base; ?>/promotion_apply_packages/upload" method="post" enctype="multipart/form-data">
<!-- <form id="PromotionApplyPackageFormUploadImage" action="<?php //echo PHOTO_PATH.'uploads/uploadPhotoTravelPackageOrder'; ?>" method="post" enctype="multipart/form-data"> -->
    <table style="display: none;">
        <tr>
            <td>
                <input type="hidden" name="token" value="wK4lxDowEfgnaEH2k226FppwAJSflRPG" />
                <input type="file" name="photo" id="PromotionApplyPackagePhotoUpload" />
            </td>
        </tr>
    </table>
</form>
<?php echo $this->Form->create('PromotionApplyPackage'); ?>
<input type="hidden" name="data[PromotionApplyPackage][photo_path]" value="<?php echo $photoPath; ?>" />
<fieldset style="width: 47%; float: left; height: 380px;">
    <legend><?php __(MENU_PROMOTION_PACKAGE_APPLY_INFO); ?></legend>
    <table style="width: 100%;">
        <tr>
            <td colspan="2">
                <table>
                    <tr>
                        <td>
                            <input type="hidden" name="data[PromotionApplyPackage][photo]" id="PromotionApplyPackagePhoto" />
                            <img alt="" id="PromotionApplyPackagePhotoDisplay" style="width: 150px; height: 100px;" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Size: 720 * 480 Px
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <input type="file" id="PromotionApplyPackageUpload" />
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td><label for="PromotionApplyPackageNationality"><?php echo "Nationality"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[PromotionApplyPackage][nationality]" id="PromotionApplyPackageNationality" style="width: 180px;" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        $sqlNation = mysql_query("SELECT * FROM nationalities WHERE is_active = 1");
                        while($rowNation = mysql_fetch_array($sqlNation)){
                        ?>
                        <option value="<?php echo $rowNation['id']; ?>"><?php echo $rowNation['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 30%;"><label for="PromotionApplyPackageName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="PromotionApplyPackageTelephone"><?php echo TABLE_TELEPHONE; ?> <span class="red requiredSymbolTel">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('telephone', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="PromotionApplyPackagePassport"><?php echo TABLE_PASSPORT; ?> <span class="red requiredSymbolPass"></span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('passport'); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="PromotionApplyPackageSex"><?php echo TABLE_SEX; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[PromotionApplyPackage][sex]" id="PromotionApplyPackageSex" style="width: 180px;" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1"><?php echo "Male"; ?></option>
                        <option value="2"><?php echo "Female"; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="PromotionApplyPackageSelected"><?php echo MENU_PROMOTION_PACKAGE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[PromotionApplyPackage][travel_package_id]" id="PromotionApplyPackageSelected" style="width: 180px;" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        $sqlPackage = mysql_query("SELECT * FROM travel_packages WHERE status = 1 AND type = 2");
                        while($rowPackage = mysql_fetch_array($sqlPackage)){
                        ?>
                        <option value="<?php echo $rowPackage['id']; ?>"><?php echo $rowPackage['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="PromotionApplyPackagePackageExpired"><?php echo "Expired Date"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('package_expired', array('class'=>'validate[required]')); ?>
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
        <span class="txtSavePromotionApplyPackage"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>