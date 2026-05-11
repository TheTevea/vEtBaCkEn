<?php 
$sqlSales = mysql_query("SELECT id FROM t_tickets WHERE branch_id = ".$this->data['Branch']['id']." AND status > 0 LIMIT 1");
$branchUsed = 0;
if(mysql_num_rows($sqlSales)){
    $branchUsed = 1;
}
echo $this->element('prevent_multiple_submit'); 
?>
<script type="text/javascript">
    var fieldRequire = ['BranchCountryId'];
    var fieldRequereMulti = ['BranchTDestinationId'];
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#BranchTDestinationId").chosen({width: 430});
        $("#BranchCountryId, #BranchProvinceId").chosen({width: 265});
        $(".chosenBranch").chosen();
        $("#BranchEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#BranchEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                if(checkRequireField(fieldRequire) == false){
                    alertSelectRequireField();
                    $(".btnSaveBranch").removeAttr('disabled');
                    return false;
                }
                if(checkRequireFieldMulti(fieldRequereMulti) == false){
                    alertSelectRequireField();
                    $(".btnSaveBranch").removeAttr('disabled');
                    return false;
                }
                listbox_selectall('userBranchSelected', true);
                if($("#userBranchSelected").val() == null){
                    alertSelectUserBranch();
                    $(".btnSaveBranch").removeAttr('disabled');
                    return false;
                }
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveBranch").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackBranch").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Branch', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Branch', 'Edit', 1, '');
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
        $(".btnBackBranch").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableBranch.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        $('#BranchWorkStart, #BranchWorkEnd').timepicker();
    });
    
    function alertSelectUserBranch(){
        $(".btnSaveBranch").removeAttr('disabled');
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
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackBranch">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('Branch'); 
echo $this->Form->input('id'); 
echo $this->Form->hidden('sys_code');
?>
<table cellpadding="5" cellspacing="0" style="width: 100%;">
    <tr>
        <td style="width: 50%; vertical-align: top;">
            <fieldset>
                <legend><?php __(MENU_BRANCH_INFO); ?></legend>
                <table>
                    <tr>
                        <td style="width: 120px;"><label for="BranchCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php 
                                if($branchUsed == 0){
                                    $emptySelect = INPUT_SELECT;
                                    if(COUNT($companies) == 1){
                                        $emptySelect = false;
                                    }
                                    echo $this->Form->input('company_id', array('class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 250px;'));
                                } else {
                                    echo $this->data['Company']['name'];
                                    echo $this->Form->hidden('company_id');
                                }
                                ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 120px;"><label for="BranchCode"><?php echo TABLE_CODE; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('code', array('class'=>'validate[required]')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 120px;"><label for="BranchName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="BranchTelephone"><?php echo TABLE_TELEPHONE; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('telephone', array('class'=>'validate[required]')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="BranchFaxNumber"><?php echo TABLE_FAX; ?>:</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('fax_number', array()); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="BranchEmailAddress"><?php echo TABLE_EMAIL; ?>:</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('email_address', array('class' => 'validate[optional,custom[email]]')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="BranchWorkStart"><?php echo TABLE_WORKING_HOUR; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('work_start', array('class'=>'validate[required]', 'style' => 'width: 170px;', 'placeholder' => TABLE_TIME_START, 'value' => date("H:i",  strtotime($this->data['Branch']['work_start'])))); ?>
                                <?php echo $this->Form->text('work_end', array('class'=>'validate[required]', 'style' => 'width: 170px;', 'placeholder' => TABLE_TIME_END, 'value' => date("H:i",  strtotime($this->data['Branch']['work_end'])))); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="BranchTDestinationId"><?php echo TABLE_ORIGIN; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php 
                                $destinatinos = array();
                                $sqlBranchDest = mysql_query("SELECT * FROM branch_destinations WHERE branch_id = ".$this->data['Branch']['id']);
                                while($rowBranchDest = mysql_fetch_array($sqlBranchDest)){
                                    $destinatinos[] = $rowBranchDest['t_destination_id'];
                                }
                                echo $this->Form->input('t_destination_id', array('class'=>'validate[required]', 'label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'style' => 'width: 420px;', 'selected' => $destinatinos)); 
                                ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="BranchCountryId"><?php echo TABLE_COUNTRY; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->input('country_id', array('class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 265px;')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="BranchProvinceId"><?php echo MENU_PROVINCE_MANAGEMENT; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->input('province_id', array('class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 265px;')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="BranchLongs"><?php echo TABLE_LONG; ?> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('longs', array()); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="BranchLats"><?php echo TABLE_LAT; ?> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('lats', array()); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;"><label for="BranchAddress"><?php echo TABLE_ADDRESS; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->textarea('address', array('class'=>'validate[required]')); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
        <td style="vertical-align: top;">
            <fieldset style="height: 415px;">
                <legend><?php __(USER_USER_INFO); ?></legend>
                <table>
                    <tr>
                        <th>Available:</th>
                        <th></th>
                        <th>Members:</th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">
                            <select id="userBranch" multiple="multiple" style="width: 300px; height: 200px;">
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
                                $querySource=mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1 AND id NOT IN (SELECT user_id FROM user_branches WHERE branch_id=".$this->data['Branch']['id'].")".$userCon);
                                while($dataSource=mysql_fetch_array($querySource)){
                                ?>
                                <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td style="vertical-align: middle;">
                            <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('userBranch', 'userBranchSelected')" />
                            <br /><br />
                            <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('userBranchSelected', 'userBranch')" />
                        </td>
                        <td style="vertical-align: top;">
                            <select id="userBranchSelected" name="data[Branch][user_id][]" multiple="multiple" style="width: 300px; height: 200px;">
                            <!-- <select id="userBranchSelected" multiple="multiple" style="width: 300px; height: 200px;"> -->
                                <?php
                                $queryDestination=mysql_query("SELECT DISTINCT user_id,(SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id = user_branches.user_id".$userCon.") AS full_name FROM user_branches WHERE branch_id = ".$this->data['Branch']['id']);
                                while($dataDestination=mysql_fetch_array($queryDestination)){
                                ?>
                                <option value="<?php echo $dataDestination['user_id']; ?>"><?php echo $dataDestination['full_name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
<br />
<div class="buttons">
    <button type="submit" class="positive btnSaveBranch">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveBranch"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>