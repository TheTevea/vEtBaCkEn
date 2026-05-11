<?php 
include("includes/websiteUpload.php");
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); 
$frmName = "frm" . rand();
$photoNameHidden = "photoNameHidden" . rand();
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#WebsiteGalleryAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        
        $("#WebsiteGalleryAddForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveWebsiteGallery").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackWebsiteGallery").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('WebsiteGallery', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('WebsiteGallery', 'Add', 1, '');
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
        
        // Action Photo Submit
        $("#WebsiteGalleryPhoto").unbind('change').change(function(event){
            var formData = new FormData($("#<?php echo $frmName; ?>").get(0));
            var pathUrl  = "<?php echo WEBSITE_BUS_UPLOAD_URL; ?>";
            if($("#WebsiteGalleryWebsiteType").val() == "2"){
                pathUrl  = "<?php echo WEBSITE_BUVASEA_UPLOAD_URL; ?>";
            }
            var action = pathUrl+"uploads/uploadPhotoTravelPackage";
            $.ajax({
                type: "POST",
                url: action,
                crossDomain: true,
                data: formData,
                dataType: "json",
                contentType: "multipart/form-data",
                processData: false,
                contentType: false,
                headers: {
                    "Accept": "application/json"
                }
            }).done(function(response){
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                // var photoFolder = pathUrl+"images/gallery/";
                // $("#WebsiteGalleryPhotoDisplay").attr("src", photoFolder + response);
                // $("#<?php echo $photoNameHidden; ?>").val(photoFolder +response);
            }).fail(function(){
                // Here you should treat the http errors (e.g., 403, 404)
            }).always(function(){
                // alert("AJAX request finished!");
            });
        });

        $("#WebsiteGalleryWebsiteType").unbind("change").change(function(event){
            event.preventDefault();
            $("#<?php echo $photoNameHidden; ?>").val('');
            $("#WebsiteGalleryPhotoDisplay").attr("src", "");
            var $el = $("#WebsiteGalleryPhoto");
            $el.wrap('<form>').closest('form').get(0).reset();
            $el.unwrap();
            $("#WebsiteGalleryPhoto").attr("disabled", false);
            if($(this).val() == ""){
                $("#WebsiteGalleryPhoto").attr("disabled", true);
            }
        });

        $(".btnBackWebsiteGallery").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableWebsiteGallery.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackWebsiteGallery">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<form id="<?php echo $frmName; ?>" action="#" method="post" enctype="multipart/form-data" onsubmit="return false">
    <table style="width: 100%">
        <tr>
            <td style="width: 7%;"><label for="WebsiteGalleryPhoto"><?php echo TABLE_PHOTO; ?>:</label></td>
            <td valign="top">
                <input type="hidden" name="token" value="wK4lxDowEfgnaEH2k226FppwAJSflRPG" />
                <input type="file" id="WebsiteGalleryPhoto" name="photo" disabled="" />
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: left;">
                <img id="WebsiteGalleryPhotoDisplay" src="" style=" max-width: 140px; max-height: 140px;" />
            </td>
        </tr>
    </table>
</form>
<br />
<?php echo $this->Form->create('WebsiteGallery'); ?>
<input type="hidden" id="<?php echo $photoNameHidden; ?>" name="data[WebsiteGallery][photo]" />
<fieldset>
    <legend><?php __(MENU_WEBSITE_GALLERY_ADD); ?></legend>
    <table cellpadding="5">
        <tr>
            <td><label for="WebsiteGalleryWebsiteType"><?php echo TABLE_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[WebsiteGallery][website_type]" id="WebsiteGalleryWebsiteType" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1"><?php echo "Vireak Buntham"; ?></option>
                        <option value="2"><?php echo "Buva Sea"; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="WebsiteGalleryName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveWebsiteGallery"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>