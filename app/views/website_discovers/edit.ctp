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
        $("#WebsiteDiscoverEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#WebsiteDiscoverEditForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveWebsiteDiscover").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackWebsiteDiscover").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('WebsiteDiscover', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('WebsiteDiscover', 'Edit', 1, '');
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
        $("#WebsiteDiscoverPhoto").live('change', function(){
            var formData = new FormData($("#<?php echo $frmName; ?>").get(0));
            var pathUrl  = "<?php echo WEBSITE_BUS_UPLOAD_URL; ?>";
            if($("#WebsiteDiscoverWebsiteType").val() == "2"){
                pathUrl  = "<?php echo WEBSITE_BUVASEA_UPLOAD_URL; ?>";
            }
            $.ajax({
                url : pathUrl+"upload.php",
                type : "POST",
                data : formData,
                // both 'contentType' and 'processData' parameters are
                // required so that all data are correctly transferred
                contentType : false,
                processData : false
            }).done(function(response){
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                var photoFolder = pathUrl+"images/slider/";
                $("#WebsiteDiscoverPhotoDisplay").attr("src", photoFolder + response);
                $("#<?php echo $photoNameHidden; ?>").val(photoFolder + response);
            }).fail(function(){
                // Here you should treat the http errors (e.g., 403, 404)
            }).always(function(){
                // alert("AJAX request finished!");
            });
        });

        $("#WebsiteDiscoverWebsiteType").unbind("change").change(function(event){
            event.preventDefault();
            $("#<?php echo $photoNameHidden; ?>").val('');
            $("#WebsiteDiscoverPhotoDisplay").attr("src", "");
            var $el = $("#WebsiteDiscoverPhoto");
            $el.wrap('<form>').closest('form').get(0).reset();
            $el.unwrap();
            $("#WebsiteDiscoverPhoto").attr("disabled", false);
            if($(this).val() == ""){
                $("#WebsiteDiscoverPhoto").attr("disabled", true);
            }
        });


        $(".btnBackWebsiteDiscover").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableWebsiteDiscover.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackWebsiteDiscover">
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
            <td style="width: 7%;"><label for="WebsiteDiscoverPhoto"><?php echo TABLE_PHOTO; ?>:</label></td>
            <td valign="top">
                <input type="hidden" name="secret" value="4602e63c-1b0c-11ee-be56-0242ac120002" />
                <input type="hidden" name="path" value="slider" />
                <input type="file" id="WebsiteDiscoverPhoto" name="photo" />
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: left;">
                <?php 
                if($this->data['WebsiteDiscover']['photo'] != ''){
                    $photo = $this->data['WebsiteDiscover']['photo'];
                }else{
                    $photo = $this->webroot."img/no-images.png";
                }
                ?>
                <img id="WebsiteDiscoverPhotoDisplay" src="<?php echo $photo; ?>" style=" max-width: 140px; max-height: 140px;" />
            </td>
        </tr>
    </table>
</form>
<br />
<?php echo $this->Form->create('WebsiteDiscover'); ?>
<?php echo $this->Form->input('id'); ?>
<input type="hidden" id="<?php echo $photoNameHidden; ?>" name="data[WebsiteDiscover][photo]" value="<?php echo $this->data['WebsiteDiscover']['photo']; ?>" />
<fieldset>
    <legend><?php __(MENU_WEBSITE_DISCOVER_EDIT); ?></legend>
    <table cellpadding="5">
        <tr>
            <td><label for="WebsiteDiscoverWebsiteType"><?php echo TABLE_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[WebsiteDiscover][website_type]" id="WebsiteDiscoverWebsiteType" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1" <?php if($this->data['WebsiteDiscover']['website_type'] == 1){ ?>selected=""<?php } ?>><?php echo "Vireak Buntham"; ?></option>
                        <option value="2" <?php if($this->data['WebsiteDiscover']['website_type'] == 2){ ?>selected=""<?php } ?>><?php echo "Buva Sea"; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="WebsiteDiscoverName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
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
        <span class="txtSaveWebsiteDiscover"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>