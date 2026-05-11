<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#AmenityAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#AmenityAddForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveAmenity").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackAmenity").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Amenity', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Amenity', 'Add', 1, '');
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

        $(".btnBackAmenity").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableAmenity.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });

        $("#AmenityFormUploadImage").ajaxForm({
            dataType: "json",
            beforeSerialize: function($form, options) {
                extArray = new Array(".bmp",".jpg",".gif",".tif",".png");
                allowSubmit = false;
                file = $("#AmenityPhotoUpload").val();
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
                $("#AmenityPhoto").val(result.img);
                $("#AmenityPhotoDisplay").attr("src", "<?php echo $this->webroot; ?>public/amenities/" + result.img);
            }
        });

        $("#AmenityUpload").on("change", function (event) {
            let fileInputElement = document.getElementById('AmenityPhotoUpload');
            fileInputElement.files = event.target.files;
            $("#AmenityFormUploadImage").submit();
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackAmenity">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<form id="AmenityFormUploadImage" action="<?php echo $this->base; ?>/amenities/upload" method="post" enctype="multipart/form-data">
    <table style="display: none;">
        <tr>
            <td>
                <input type="file" name="photo" id="AmenityPhotoUpload" />
            </td>
        </tr>
    </table>
</form>
<?php echo $this->Form->create('Amenity'); ?>
<fieldset>
    <legend><?php __(MENU_AMENITY_INFO); ?></legend>
    <table cellpadding="5">
        <tr>
            <td colspan="2">
                <input type="hidden" name="data[Amenity][photo]" id="AmenityPhoto" />
                <img alt="" id="AmenityPhotoDisplay" style="width: 128px; height: 128px;" />
            </td>
        </tr>
        <tr>
            <td colspan="2">
                Size: 128 * 128 Px
            </td>
        </tr>
        <tr>
            <td colspan="2" valign="top">
                <input type="file" id="AmenityUpload" />
            </td>
        </tr>
        <tr>
            <td><label for="AmenityName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]', 'style' => 'width: 250px;')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive btnSaveAmenity">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveAmenity"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>