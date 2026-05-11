<?php 
echo $this->element('prevent_multiple_submit'); 
$frmName = "frm" . rand();
$dialogPhoto = "dialogPhoto" . rand();
$cropPhoto = "cropPhoto" . rand();
$photoNameHidden = "photoNameHidden" . rand();
?>
<script type="text/javascript">
    var jcrop_api='';
    var x,y,x2,y2,w,h;
    var obj;
    function showCoords(c)
    {
        x=c.x;
        y=c.y;
        x2=c.x2;
        y2=c.y2;
        w=c.w;
        h=c.h;
    };
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#TDestinationProvinceId, #TDestinationTDestinationGroupId").chosen({width: 265});
        // Upload Image
        // From Action Upload Photo
        $("#<?php echo $frmName; ?>").ajaxForm({
            beforeSerialize: function($form, options) {
                extArray = new Array(".jpg",".gif",".png");
                allowSubmit = false;
                file = $("#TDestinationPhoto").val();
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
                var photoFolder = "public/destination_photo/tmp/";
                $("#photoTDestinationDisplay").attr("src", "<?php echo $this->webroot; ?>" + photoFolder + result);
                $("#<?php echo $photoNameHidden; ?>").val(result);
            }
        });
        // Action Submit Upload
        $("#TDestinationPhoto").live('change', function(){
            $("#<?php echo $frmName; ?>").submit();
        });
        
        $("#TDestinationAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TDestinationAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('TDestinationAvbSelected', true);
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTDestination").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackTDestination").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('TDestination', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('TDestination', 'Add', 1, '');
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
        $(".btnBackTDestination").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTDestination.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTDestination">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<form id="<?php echo $frmName; ?>" action="<?php echo $this->base; ?>/t_destinations/upload/" method="post" enctype="multipart/form-data">
    <fieldset>
        <legend><?php __(TABLE_PHOTO); ?></legend>
        <table>
            <tr>
                <td colspan="2">
                    <img id="photoTDestinationDisplay" alt="" style=" max-width: 140px; max-height: 140px;" />
                </td>
            </tr>
            <tr>
                <td><label for="TDestinationPhoto"><?php echo TABLE_PHOTO; ?>:</label></td>
                <td valign="top"><input type="file" id="TDestinationPhoto" name="photo" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<br />
<?php echo $this->Form->create('TDestination'); ?>
<input type="hidden" id="<?php echo $photoNameHidden; ?>" name="data[TDestination][photo]" />
<fieldset style="width: 47%; float: left; height: 237px;">
    <legend><?php __(MENU_DESTINATION_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="TDestinationCountryId"><?php echo TABLE_COUNTRY; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('country_id', array('class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 265px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TDestinationProvinceId"><?php echo MENU_PROVINCE_MANAGEMENT; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('province_id', array('class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 265px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TDestinationTDestinationGroupId"><?php echo "Group"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('t_destination_group_id', array('label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 265px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 30%;"><label for="TDestinationCode"><?php echo TABLE_CODE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('code', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TDestinationNameKh"><?php echo TABLE_NAME; ?> (Khmer) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name_kh', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TDestinationName"><?php echo TABLE_NAME; ?> (English) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TDestinationNameCn"><?php echo TABLE_NAME; ?> (Chinese) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name_cn', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr style="display: none;">
            <td><label for="TDestinationNameCn"><?php echo TABLE_NAME; ?> (Chinese) :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name_cn', array()); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<fieldset style="width: 48%; float: right; height: 237px;">
    <legend><?php __(REPORT_TO); ?></legend>
    <table>
        <tr>
            <th>Available:</th>
            <th></th>
            <th>Members:</th>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <select id="TDestinationAvb" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    if($user['User']['type'] == 2){
                        if(!empty($user['User']['offline_project_id'])){
                            $condition = " AND offline_project_id = ".$user['User']['offline_project_id'];
                        } else {
                            $condition = " AND offline_project_id = 0";
                        }
                    } else {
                        $condition = "";
                    }
                    $querySource=mysql_query("SELECT id,name AS full_name FROM t_destinations WHERE is_active=1".$condition);
                    while($dataSource=mysql_fetch_array($querySource)){
                    ?>
                    <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('TDestinationAvb', 'TDestinationAvbSelected')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('TDestinationAvbSelected', 'TDestinationAvb')" />
            </td>
            <td style="vertical-align: top;">
                <select id="TDestinationAvbSelected" name="data[TDestination][t_destination_to_id][]" multiple="multiple" style="width: 300px; height: 200px;"></select>
            </td>
        </tr>
    </table>
</fieldset>
<div style="clear: both;"></div>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveTDestination"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>
<div id="<?php echo $dialogPhoto; ?>" style="display: none;">
    <img id="<?php echo $cropPhoto; ?>" alt="" />
</div>