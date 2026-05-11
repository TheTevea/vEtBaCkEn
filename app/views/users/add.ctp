<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#UserMainBranchId").chosen({width: 250});
        $("#UserGroupId").chosen({width: 424});
        $("#UserAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#UserAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('userBranchSelected', true);
                listbox_selectall('userCompanySelected', true);
                listbox_selectall('userReportMainBranchSelected', true);
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtUserSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result == "<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>"){
                    $(".txtUserSave").html("<?php echo ACTION_SAVE; ?>");
                    $(".btnSaveUser").removeAttr("disabled");
                } else {
                    $(".btnBackUser").click();
                }
                // alert message
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
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
        
        $(".btnBackUser").unbind("click").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableUser.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        <?php
        if($user['User']['type'] == 1){
        ?>
        $("#divUserRight").hide();
        $("#UserType").unbind("click").click(function(){
            $("#divProject, #divIsAdmin, #divUserRight").hide();
            $("#UserOfflineProjectId, #UserIsAdmin").removeClass("validate[required]");
            if($(this).val() == "2"){
                $("#divProject, #divIsAdmin").show();
                $("#UserOfflineProjectId, #UserIsAdmin").addClass("validate[required]");
            } else if ($(this).val() == "1"){
                $("#divUserRight").show();
            }
        });
        <?php
        }
        ?>
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackUser">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('User');
echo $this->Form->hidden('type', array('value' => 2));
?>
<fieldset>
    <legend><?php __(USER_USER_INFO); ?></legend>
    <table style="width: 90%;">
        <?php
        if($user['User']['type'] != 1){
        ?>
        <tr>
            <td><label for="UserMainBranchId"><?php echo MENU_MAIN_BRANCH; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('main_branch_id', array('empty' => INPUT_SELECT, 'div' => false, 'label' => false)); ?>
                </div>
            </td>
        </tr>
        <?php
        }
        ?>
        <tr>
            <td style="width: 12%;"><label for="UserFirstName"><?php echo TABLE_FIRST_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('first_name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserLastName"><?php echo TABLE_LAST_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('last_name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserTelephone"><?php echo TABLE_TELEPHONE; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('telephone'); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserEmail"><?php echo TABLE_EMAIL; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('email', array('class'=>'validate[optional,custom[email]]')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php __(USER_LOGIN_INFO); ?></legend>
    <?php
        if($user['User']['type'] != 1){
            echo $this->Form->hidden('offline_project_id', array('value' => $user['User']['offline_project_id']));
        }
    ?>
    <table style="width: 90%;">
        <tr>
            <td style="width: 12%;"><label for="UserUsername"><?php echo USER_USER_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('username', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserPassword"><?php echo USER_PASSWORD; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->password('password', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserConfirmPassword"><?php echo USER_CONFIRM_PASSWORD; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->password('confirm_password', array('class'=>'validate[required,equals[UserPassword]]', 'name' => 'data[confirm_password]')); ?>
                </div>
            </td>
        </tr>
        <?php
        if($user['User']['type'] == 1){
        ?>
        <tr id="divProject" style="display: none;">
            <td><label for="UserOfflineProjectId"><?php echo 'Project'; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('offline_project_id', array('label' => false, 'empty' => INPUT_SELECT, 'div' => false)); ?>
                </div>
            </td>
        </tr>
        <?php
        }
        ?>
        <tr <?php if($user['User']['type'] == 1){ ?>id="divUserRight"<?php } ?>>
            <td><label for="UserGroupId"><?php echo USER_GROUP; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('group_id', array('label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class'=>'chzn-select', 'style' => 'width: 424px;')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset style="width: 48%; float: left;">
    <legend><?php __(MENU_COMPANY_MANAGEMENT); ?></legend>
    <table>
        <tr>
            <th>Available:</th>
            <th></th>
            <th>Member of:</th>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <select id="userCompany" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    if($user['User']['type'] != 1){
                        $queryCom = mysql_query("SELECT id, name FROM companies WHERE is_active = 1 AND offline_project_id = ".$user['User']['offline_project_id'].";");
                    } else {
                        $queryCom = mysql_query("SELECT id, name FROM companies WHERE is_active = 1;");
                    }
                    while($dataCom=mysql_fetch_array($queryCom)){
                    ?>
                    <option value="<?php echo $dataCom['id']; ?>"><?php echo $dataCom['name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('userCompany', 'userCompanySelected')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('userCompanySelected', 'userCompany')" />
            </td>
            <td style="vertical-align: top;">
                <select id="userCompanySelected" name="data[User][company_id][]" multiple="multiple" style="width: 300px; height: 200px;"></select>
            </td>
        </tr>
    </table>
</fieldset>
<fieldset style="width: 48%; float: left;">
    <legend><?php __(MENU_BRANCH_INFO); ?></legend>
    <table>
        <tr>
            <th>Available:</th>
            <th></th>
            <th>Member of:</th>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <select id="userBranch" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    if($user['User']['type'] != 1){
                        $queryBranch = mysql_query("SELECT id,name FROM branches WHERE is_active = 1 AND offline_project_id = ".$user['User']['offline_project_id'].";");
                    } else {
                        $queryBranch = mysql_query("SELECT id,name FROM branches WHERE is_active=1");
                    }
                    while($dataBranch = mysql_fetch_array($queryBranch)){
                    ?>
                    <option value="<?php echo $dataBranch['id']; ?>"><?php echo $dataBranch['name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('userBranch', 'userBranchSelected')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('userBranchSelected', 'userBranch')" />
            </td>
            <td style="vertical-align: top;">
                <select id="userBranchSelected" name="data[User][branch_id][]" multiple="multiple" style="width: 300px; height: 200px;"></select>
            </td>
        </tr>
    </table>
</fieldset>
<div style="clear: both;"></div>
<br />
<fieldset>
    <legend><?php __("Report Location Branch"); ?></legend>
    <table>
        <tr>
            <th>Available:</th>
            <th></th>
            <th>Member of:</th>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <select id="userReportMainBranch" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $queryMainBranch = mysql_query("SELECT id,name FROM main_branches WHERE is_active = 1 AND offline_project_id = 1;");
                    while($dataMainBranch = mysql_fetch_array($queryMainBranch)){
                    ?>
                    <option value="<?php echo $dataMainBranch['id']; ?>"><?php echo $dataMainBranch['name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('userReportMainBranch', 'userReportMainBranchSelected')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('userReportMainBranchSelected', 'userReportMainBranch')" />
            </td>
            <td style="vertical-align: top;">
                <select id="userReportMainBranchSelected" name="data[User][report_main_branch_id][]" multiple="multiple" style="width: 300px; height: 200px;"></select>
            </td>
        </tr>
    </table>
</fieldset>
<div class="buttons">
    <button type="submit" class="positive btnSaveUser">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtUserSave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>