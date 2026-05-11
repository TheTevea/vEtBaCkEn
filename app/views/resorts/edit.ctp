<?php 
echo $this->element('prevent_multiple_submit'); 
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $link = "https";
else
      $link = "http";
     
// Here append the common URL characters.
$link .= "://";
     
// Append the host(domain name, ip) to the URL.
$link .= $_SERVER['HTTP_HOST'];
     
// Append the requested resource location to the URL
$link .= str_replace("/resorts/edit/".$this->data['Resort']['id'],"/public/resort/",$_SERVER['REQUEST_URI']);
?>
<script type="text/javascript">
    var rowTableResortDestinationList  = $("#rowListResortDestination");
    $(document).ready(function(){
        $("#rowListResortDestination").remove();
        // Prevent Key Enter
        preventKeyEnter();
        $("#ResortEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#ResortEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $("#ResortPrice, #ResortPeriodExpired").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveResort").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackResort").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Resort', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Resort', 'Add', 1, '');
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
        $(".btnBackResort").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableResort.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });

        $("#ResortUpload").on("change", function (event) {
            let fileInputElement = document.getElementById('ResortUploadPhotoUpload');
            fileInputElement.files = event.target.files;
            $("#ResortFormUploadImage").submit();
        });

        // Upload Image
        // From Action Upload Photo
        $("#ResortFormUploadImage").ajaxForm({
            dataType: "json",
            beforeSerialize: function($form, options) {
                extArray = new Array(".bmp",".jpg",".gif",".tif",".png");
                allowSubmit = false;
                file = $("#ResortUploadPhotoUpload").val();
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
                $("#ResortPhoto").val(result.img);
                $("#ResortPhotoDisplay").attr("src", "<?php echo $this->webroot; ?>public/resort/" + result.img);
            }
        });

    });
    
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackResort">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<form id="ResortFormUploadImage" action="<?php echo $this->base; ?>/resorts/upload" method="post" enctype="multipart/form-data">
    <table style="display: none;">
        <tr>
            <td>
                <input type="file" name="photo" id="ResortUploadPhotoUpload" />
            </td>
        </tr>
    </table>
</form>
<?php echo $this->Form->create('Resort'); ?>
<input type="hidden" name="data[Resort][id]" value="<?php echo $this->data['Resort']['id']; ?>" />
<input type="hidden" name="data[Resort][photo_path]" value="<?php echo $link; ?>" />
<fieldset style="height: 450px;">
    <legend><?php __(MENU_RESORT_INFO); ?></legend>
    <table style="width: 100%;">
        <tr>
            <td colspan="2">
                <table>
                    <tr>
                        <td>
                            <?php
                            $img = "";
                            if(!empty($this->data['Resort']['photo'])){
                                $img = $this->data['Resort']['photo_path'].$this->data['Resort']['photo'];
                            }
                            ?>
                            <input type="hidden" name="data[Resort][photo]" id="ResortPhoto" value="<?php echo $this->data['Resort']['photo']; ?>" />
                            <img src="<?php echo $img; ?>" alt="" id="ResortPhotoDisplay" style="width: 150px; height: 100px;" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Size: 720 * 480 Px
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <input type="file" id="ResortUpload" />
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="width: 10%;"><label for="ResortName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="ResortPrice"><?php echo "Price"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('price', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="ResortLink"><?php echo "URL Link"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('link', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        </tr>
    </table>
</fieldset>
<div style="clear: both;"></div>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveResort"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>