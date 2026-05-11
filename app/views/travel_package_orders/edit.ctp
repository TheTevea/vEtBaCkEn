<?php 
include("includes/function.php");
echo $this->element('prevent_multiple_submit'); 
$photoPath = PHOTO_PATH."public/travel_package_order/";
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#TravelPackageOrderEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TravelPackageOrderEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTravelPackageOrder").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackTravelPackageOrder").click();
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
        $(".btnBackTravelPackageOrder").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTravelPackageOrder.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });

        // Upload Image
        // From Action Upload Photo
        $("#TravelPackageOrderFormUploadImage").ajaxForm({
            dataType: "json",
            xhrFields: {
                withCredentials: true
            },
            beforeSerialize: function($form, options) {
                extArray = new Array(".jpeg",".jpg",".png");
                allowSubmit = false;
                file = $("#TravelPackageOrderPhotoUpload").val();
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
                alert(2);
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("#TravelPackageOrderPhoto").val(result.img);
                $("#TravelPackageOrderPhotoDisplay").attr("src", "<?php echo $photoPath; ?>" + result.img);
            }
        });

        $("#TravelPackageOrderUpload").on("change", function (event) {
            let fileInputElement = document.getElementById('TravelPackageOrderPhotoUpload');
            fileInputElement.files = event.target.files;
            $("#TravelPackageOrderFormUploadImage").submit();
        });

    });
    
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTravelPackageOrder">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<form id="TravelPackageOrderFormUploadImage" action="<?php echo $this->base; ?>/travel_package_orders/upload" method="post" enctype="multipart/form-data">
    <table style="display: none;">
        <tr>
            <td>
                <input type="file" name="photo" id="TravelPackageOrderPhotoUpload" />
            </td>
        </tr>
    </table>
</form>
<?php echo $this->Form->create('TravelPackageOrder'); ?>
<input type="hidden" name="data[TravelPackageOrderEdit][photo_path]" value="<?php echo $photoPath; ?>" />
<input type="hidden" name="data[TravelPackageOrder][id]" value="<?php echo $this->data['TravelPackageOrder']['id']; ?>" />
<fieldset style="width: 47%; float: left; height: 400px;">
    <legend><?php __(MENU_TRAVEL_PACKAGE_CUSTOMER_INFO); ?></legend>
    <table style="width: 100%;" cellpadding="5">
        <tr>
            <td colspan="2">
                <table>
                    <tr>
                        <td>
                            <?php
                            $img = "";
                            if(!empty($this->data['TravelPackageOrder']['photo'])){
                                $img = $this->data['TravelPackageOrder']['photo_path'].$this->data['TravelPackageOrder']['photo'];
                            }
                            ?>
                            <input type="hidden" name="data[TravelPackageOrderEdit][photo]" id="TravelPackageOrderPhoto" />
                            <img src="<?php echo $img; ?>" id="TravelPackageOrderPhotoDisplay" style="width: 150px; height: 100px;" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Size: 720 * 480 Px
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <input type="file" id="TravelPackageOrderUpload" />
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <th style="width: 20%; font-size: 12px;"><?php __(TABLE_TELEPHONE); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['TravelPackageOrder']['telephone']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_EMAIL); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['TravelPackageOrder']['email']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_SEX); ?></th>
            <td style="font-size: 12px;">
                <?php
                if($this->data['TravelPackageOrder']['sex'] == 1){
                    echo "Male"; 
                } else if($this->data['TravelPackageOrder']['sex'] == 2){
                    echo "Female"; 
                }
                ?>
            </td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __("DOB"); ?></th>
            <td style="font-size: 12px;">
                <?php  
                if(!empty($this->data['TravelPackageOrder']['dob'])){
                    echo dateShort($this->data['TravelPackageOrder']['dob']);
                }
                ?>
            </td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __("Package Date"); ?></th>
            <td style="font-size: 12px;"><?php echo dateShort($this->data['TravelPackageOrder']['package_date']); ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __("Package Code"); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['TravelPackageOrder']['package_code']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __("Package Price"); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['TravelPackageOrder']['package_price'], 2); ?> $</td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __("Package Expired"); ?></th>
            <td style="font-size: 12px;"><?php echo dateShort($this->data['TravelPackageOrder']['package_expired']); ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __("Address"); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['TravelPackageOrder']['address']; ?></td>
        </tr>
    </table>
</fieldset>
<div style="clear: both;"></div>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveTravelPackageOrder"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>