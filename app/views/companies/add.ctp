<?php 
echo $this->element('prevent_multiple_submit'); 
$frmName = "frm" . rand();
$dialogPhoto = "dialogPhoto" . rand();
$cropPhoto = "cropPhoto" . rand();
$photoNameHidden = "photoNameHidden" . rand();
?>
<script type="text/javascript">
    var fieldRequire = ['BranchCountryId'];
    var fieldRequereMulti = ['BranchTDestinationId'];
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
        $("#BranchTDestinationId").chosen({width: 430});
        $("#BranchCountryId").chosen({width: 265});
        // Upload Image
        // From Action Upload Photo
        $("#<?php echo $frmName; ?>").ajaxForm({
            beforeSerialize: function($form, options) {
                extArray = new Array(".jpg",".gif",".png");
                allowSubmit = false;
                file = $("#CompanyPhoto").val();
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
                var photoFolder = "public/company_photo/tmp/";
                $("#photoCompanyDisplay").attr("src", "<?php echo $this->webroot; ?>" + photoFolder + result);
                $("#<?php echo $photoNameHidden; ?>").val(result);
            }
        });
        // Action Submit Upload
        $("#CompanyPhoto").live('change', function(){
            $("#<?php echo $frmName; ?>").submit();
        });
        
        $("#CompanyAddForm").validationEngine();
        $("#CompanyAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                if(checkRequireField(fieldRequire) == false){
                    alertSelectRequireField();
                    $(".btnSaveCompany").removeAttr('disabled');
                    return false;
                }
                if(checkRequireFieldMulti(fieldRequereMulti) == false){
                    alertSelectRequireBranch();
                    $(".btnSaveCompany").removeAttr('disabled');
                    return false;
                }
                if($("#<?php echo $photoNameHidden; ?>").val() == ''){
                    alertUploadPhotoCompany();
                    return false;
                }
                listbox_selectall('userCompanySelected', true);
                if($("#userCompanySelected").val() == null){
                    alertSelectUserCompany();
                    return false;
                }
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveCompany").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackCompany").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Company', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Company', 'Add', 1, '');
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
        $(".btnBackCompany").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableCompany.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        $('#BranchWorkStart, #BranchWorkEnd').timepicker();
    });
    function alertSelectUserCompany(){
        $(".btnSaveCompany").removeAttr('disabled');
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_COMFIRM_SELECT_USER; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            closeOnEscape: false,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
    
    function alertUploadPhotoCompany(){
        $(".btnSaveCompany").removeAttr('disabled');
        $("#dialog").html('<p style="color:red; font-size:14px;">Please Upload Photo!</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            closeOnEscape: false,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackCompany">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<form id="<?php echo $frmName; ?>" action="<?php echo $this->base; ?>/companies/upload/" method="post" enctype="multipart/form-data">
    <fieldset>
        <legend><?php __(TABLE_PHOTO); ?></legend>
        <table>
            <tr>
                <td colspan="2">
                    <img id="photoCompanyDisplay" alt="" style=" max-width: 140px; max-height: 140px;" />
                </td>
            </tr>
            <tr>
                <td><label for="CompanyPhoto"><?php echo TABLE_PHOTO; ?>:</label></td>
                <td valign="top"><input type="file" id="CompanyPhoto" name="photo" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<br />
<?php echo $this->Form->create('Company'); ?>
<input type="hidden" id="<?php echo $photoNameHidden; ?>" name="data[Company][photo]" />
<fieldset>
    <legend><?php __(MENU_COMPANY_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <td style="width: 120px;"><label for="CompanyName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?></td>
        </tr>
        <tr>
            <td><label for="CompanyCurrencyCenterId"><?php echo TABLE_BASE_CURRENCY; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->input('currency_center_id', array('class'=>'validate[required]', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 250px;')); ?></td>
        </tr>
        <tr>
            <td><label for="CompanyWebsite"><?php echo TABLE_WEBSITE; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('website', array('class' => 'validate[optional,custom[url]]')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php __(MENU_BRANCH_HEAD); ?></legend>
    <table>
        <tr>
            <td style="width: 120px;"><label for="BranchCode"><?php echo TABLE_CODE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('code', array('name' => 'data[Branch][code]', 'class'=>'validate[required]', 'id' => 'BranchCode')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 120px;"><label for="BranchName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('name' => 'data[Branch][name]', 'class'=>'validate[required]', 'id' => 'BranchName')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchTelephone"><?php echo TABLE_TELEPHONE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('telephone', array('name' => 'data[Branch][telephone]', 'id' => 'BranchTelephone', 'class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchFaxNumber"><?php echo TABLE_FAX; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('fax_number', array('name' => 'data[Branch][fax_number]', 'id' => 'BranchFaxNumber')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchEmailAddress"><?php echo TABLE_EMAIL; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('email_address', array('name' => 'data[Branch][email_address]', 'id' => 'BranchEmailAddress', 'class' => 'validate[optional,custom[email]]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchWorkStart"><?php echo TABLE_WORKING_HOUR; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('work_start', array('name' => 'data[Branch][work_start]', 'id' => 'BranchWorkStart', 'class'=>'validate[required]', 'style' => 'width: 170px;', 'placeholder' => TABLE_TIME_START)); ?>
                    <?php echo $this->Form->text('work_end', array('name' => 'data[Branch][work_end]', 'id' => 'BranchWorkEnd', 'class'=>'validate[required]', 'style' => 'width: 170px;', 'placeholder' => TABLE_TIME_END)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchTDestinationId"><?php echo TABLE_ORIGIN; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('t_destination_id', array('name' => 'data[Branch][t_destination_id]', 'class'=>'validate[required]', 'label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'style' => 'width: 430px;', 'id' => 'BranchTDestinationId')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchCountryId"><?php echo TABLE_COUNTRY; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('country_id', array('name' => 'data[Branch][country_id]', 'class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 265px;', 'id' => 'BranchCountryId')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchLongs"><?php echo TABLE_LONG; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('longs', array('name' => 'data[Branch][longs]', 'id' => 'BranchLongs')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchLats"><?php echo TABLE_LAT; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('lats', array('name' => 'data[Branch][lats]', 'id' => 'BranchLats')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="BranchAddress"><?php echo TABLE_ADDRESS; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('address', array('class'=>'validate[required]', 'name' => 'data[Branch][address]', 'id' => 'BranchAddress')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php __(USER_USER_INFO); ?></legend>
    <table>
        <tr>
            <th>Available:</th>
            <th></th>
            <th>Members:</th>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <select id="userCompany" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    if($user['User']['type'] == 2){
                        if(!empty($user['User']['offline_project_id'])){
                            $userCon = " AND offline_project_id = ".$user['User']['offline_project_id'];
                        } else {
                            $userCon = " AND offline_project_id = 0";
                        }
                    } else {
                        $userCon = "";
                    }
                    $querySource=mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active = 1".$userCon);
                    while($dataSource=mysql_fetch_array($querySource)){
                    ?>
                    <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('userCompany', 'userCompanySelected')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('userCompanySelected', 'userCompany')" />
            </td>
            <td style="vertical-align: top;">
                <select id="userCompanySelected" name="data[Company][user_id][]" multiple="multiple" style="width: 300px; height: 200px;"></select>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive btnSaveCompany">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveCompany"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>
<div id="<?php echo $dialogPhoto; ?>" style="display: none;">
    <img id="<?php echo $cropPhoto; ?>" alt="" />
</div>