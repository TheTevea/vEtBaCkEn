<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#UserMainBranchId").chosen({width: 250});
        $("#UserEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#UserEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('userBranchSelected', true);
                listbox_selectall('userCompanySelected', true);
                listbox_selectall('userReportMainBranchSelected', true);
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                var rightPanel=$("#UserEditForm").parent();
                var leftPanel=rightPanel.parent().find(".leftPanel");
                rightPanel.hide();rightPanel.html("");
                leftPanel.show("slide", { direction: "left" }, 500);
                oCache.iCacheLower = -1;
                oTableUser.fnDraw(false);
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
        $(".btnBackUser").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableUser.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
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
echo $this->Form->input('id'); 
echo $this->Form->hidden('sys_code');
?>
<fieldset>
    <legend><?php __(USER_USER_INFO); ?></legend>
    <table style="width: 90%;">
        <?php
        if($this->data['User']['type'] == 2 && $user['User']['type'] != 1){
        ?>
        <tr>
            <td><label for="UserMainBranchId"><?php echo MENU_MAIN_BRANCH; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('main_branch_id', array('class'=>'validate[required]', 'empty' => INPUT_SELECT, 'div' => false, 'label' => false)); ?>
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
                    $queryCom = mysql_query("SELECT id, name FROM companies WHERE is_active = 1 AND offline_project_id = 1 AND id NOT IN (SELECT company_id FROM user_companies WHERE user_id=".$this->data['User']['id'].")");
                    while($dataCom = mysql_fetch_array($queryCom)){
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
                <select id="userCompanySelected" name="data[User][company_id][]" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $queryCom = mysql_query("SELECT companies.id AS id, companies.name AS name FROM user_companies INNER JOIN companies ON companies.id = user_companies.company_id WHERE companies.is_active = 1 AND user_companies.user_id=".$this->data['User']['id']." GROUP BY user_companies.company_id;");
                    while($dataCom = mysql_fetch_array($queryCom)){
                    ?>
                    <option value="<?php echo $dataCom['id']; ?>"><?php echo $dataCom['name']; ?></option>
                    <?php } ?>
                </select>
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
                    $querySource = mysql_query("SELECT id,name FROM branches WHERE is_active=1 AND offline_project_id = 1 AND id NOT IN (SELECT branch_id FROM user_branches WHERE user_id=".$this->data['User']['id'].")");
                    while($dataSource=mysql_fetch_array($querySource)){
                    ?>
                    <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('userBranch', 'userBranchSelected')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('userBranchSelected', 'userBranch')" />
            </td>
            <td style="vertical-align: top;">
                <select id="userBranchSelected" name="data[User][branch_id][]" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $queryBranch = mysql_query("SELECT DISTINCT branch_id,(SELECT name FROM branches WHERE id=user_branches.branch_id) AS company_name FROM user_branches WHERE branch_id NOT IN (SELECT id FROM branches WHERE is_active !=1 AND offline_project_id = 1) AND user_id=".$this->data['User']['id']);
                    while($dataBranch = mysql_fetch_array($queryBranch)){
                    ?>
                    <option value="<?php echo $dataBranch['branch_id']; ?>"><?php echo $dataBranch['company_name']; ?></option>
                    <?php } ?>
                </select>
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
                    $queryMainBranch = mysql_query("SELECT id,name FROM main_branches WHERE is_active = 1 AND offline_project_id = 1 AND id NOT IN (SELECT main_branch_id FROM user_report_main_branches WHERE user_id=".$this->data['User']['id'].")");
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
                <select id="userReportMainBranchSelected" name="data[User][report_main_branch_id][]" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $queryMainBranchSelected = mysql_query("SELECT DISTINCT main_branch_id, (SELECT name FROM main_branches WHERE id=user_report_main_branches.main_branch_id) AS company_name FROM user_report_main_branches WHERE main_branch_id NOT IN (SELECT id FROM main_branches WHERE is_active != 1 AND offline_project_id = 1) AND user_id=".$this->data['User']['id']);
                    while($dataMainBranchSelected = mysql_fetch_array($queryMainBranchSelected)){
                    ?>
                    <option value="<?php echo $dataMainBranchSelected['main_branch_id']; ?>"><?php echo $dataMainBranchSelected['company_name']; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
    </table>
</fieldset>
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>