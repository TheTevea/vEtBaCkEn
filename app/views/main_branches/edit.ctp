<?php 
echo $this->element('prevent_multiple_submit'); 
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#MainBranchEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#MainBranchEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('userMainBranchSelected', true);
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveMainBranch").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                // alert message
                if(result != '<?php echo MESSAGE_DATA_INVALID; ?>' && result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    $(".btnBackMainBranch").click();
                    createSysAct('Main Branch', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Main Branch', 'Edit', 1, '');
                    if(result == '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' || result == '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                        $(".btnSaveMainBranch").removeAttr("disabled");
                        $(".txtSaveMainBranch").html("<?php echo ACTION_SAVE; ?>");
                    } else {
                        $(".btnBackMainBranch").click();
                    }
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
        $(".btnBackMainBranch").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableMainBranch.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
    
    function alertSelectUserMainBranch(){
        $(".btnSaveMainBranch").removeAttr('disabled');
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
        <a href="" class="positive btnBackMainBranch">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('MainBranch'); 
echo $this->Form->input('id'); 
echo $this->Form->hidden('sys_code');
?>
<table cellpadding="5" cellspacing="0" style="width: 100%;">
    <tr>
        <td style="width: 50%; vertical-align: top;">
            <fieldset>
                <legend><?php __(MENU_MAIN_BRANCH_INFO); ?></legend>
                <table>
                    <tr>
                        <td style="width: 120px;"><label for="MainBranchName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('name', array('class'=>'validate[required]', 'label' => false, 'div' => false, 'style' => 'width: 340px; height: 25px;')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="MainBranchTDestinationId"><?php echo TABLE_ORIGIN; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->input('t_destination_id', array('class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 350px; height: 35px;')); ?>
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
                            <select id="userMainBranch" multiple="multiple" style="width: 300px; height: 200px;">
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
                                $querySource=mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1 AND (main_branch_id IS NULL OR main_branch_id = '')".$userCon);
                                while($dataSource=mysql_fetch_array($querySource)){
                                ?>
                                <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td style="vertical-align: middle;">
                            <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('userMainBranch', 'userMainBranchSelected')" />
                            <br /><br />
                            <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('userMainBranchSelected', 'userMainBranch')" />
                        </td>
                        <td style="vertical-align: top;">
                            <select id="userMainBranchSelected" name="data[MainBranch][user_id][]" multiple="multiple" style="width: 300px; height: 200px;">
                                <?php
                                $queryDestination=mysql_query("SELECT id, CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE main_branch_id = ".$this->data['MainBranch']['id'].$userCon);
                                while($dataDestination=mysql_fetch_array($queryDestination)){
                                ?>
                                <option value="<?php echo $dataDestination['id']; ?>"><?php echo $dataDestination['full_name']; ?></option>
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
    <button type="submit" class="positive btnSaveMainBranch">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveMainBranch"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>