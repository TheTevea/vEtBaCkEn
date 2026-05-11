<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#TAgentMainBranchId").chosen({width: 250});
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        $("#TAgentAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TAgentAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('companySelectedTagent', true);
                listbox_selectall('branchSelectedTagent', true);
                if($("#companySelectedTagent").val() == null){
                    alertSelectCompanyTAgent();
                    return false;
                }
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTAgent").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackTAgent").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('TAgent', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('TAgent', 'Add', 1, '');
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
        $(".btnBackTAgent").unbind("click").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTAgent.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        $("#TAgentType").unbind("change").change(function(){
            $(".tAgentCommissionAgency, #divTAgentCommission, .tAgentCommissionBuvaSeaAgency, #divTAgentCommissionBuvaSea").show();
            $("#divTAgentCommissionFixAmount, #divTAgentCommissionBuvaSeaFixAmount").hide();
            $(".tAgentCommissionType[value='1']").attr("checked", true);
            $(".tAgentCommissionBuvaSeaType[value='1']").attr("checked", true);
            $("#TAgentCommission, #TAgentCommissionFixAmount").val('0');
            $("#TAgentCommissionBuvaSea, #TAgentCommissionBuvaSeaFixAmount").val('0');
            if($(this).val() == '3'){
                $(".tAgentCommissionAgency, .tAgentCommissionBuvaSeaAgency").hide();
                $("#divTAgentCommission, #divTAgentCommissionBuvaSea").show();
                $("#TAgentCommission, #TAgentCommissionBuvaSea").val('10');
            }
            showHideBonus();
        });
        
        $(".tAgentCommissionType").unbind('click').click(function(){
            $("#divTAgentCommission, #divTAgentCommissionFixAmount").hide();
            $("#TAgentCommission, #TAgentCommissionFixAmount").val('');
            var val = $(".tAgentCommissionType:checked").val();
            if(val == '1'){
                $("#divTAgentCommission").show();
                $("#TAgentCommission").val('0');
            } else if(val == '3'){
                $("#divTAgentCommissionFixAmount").show();
                $("#TAgentCommissionFixAmount").val('0');
            }
        });

        $(".tAgentCommissionBuvaSeaType").unbind('click').click(function(){
            $("#divTAgentCommissionBuvaSea, #divTAgentCommissionBuvaSeaFixAmount").hide();
            $("#TAgentCommissionBuvaSea, #TAgentCommissionBuvaSeaFixAmount").val('');
            var val = $(".tAgentCommissionBuvaSeaType:checked").val();
            if(val == '1'){
                $("#divTAgentCommissionBuvaSea").show();
                $("#TAgentCommissionBuvaSea").val('0');
            } else if(val == '3'){
                $("#divTAgentCommissionBuvaSeaFixAmount").show();
                $("#TAgentCommissionBuvaSeaFixAmount").val('0');
            }
        });

        $("#TAgentCommission, #TAgentCommissionFixAmount, #TAgentCommissionBuvaSea, #TAgentCommissionBuvaSeaFixAmount").unbind("focus").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val("");
            }
        });

        $("#TAgentCommission, #TAgentCommissionFixAmount, #TAgentCommissionBuvaSea, #TAgentCommissionBuvaSeaFixAmount").unbind("blur").blur(function(){
            if($(this).val() == ""){
                $(this).val("0");
            }
        });

        $("#TAgentPayment").unbind("change").change(function(){
            $("#dvAgentMaxBalance").hide();
            $("#TAgentMaxBalance").removeClass("validate[required]");
            $("#TAgentMaxBalance").val("");
            if($(this).val() == "2"){
                $("#dvAgentMaxBalance").show();
                $("#TAgentMaxBalance").addClass("validate[required]");
            }
            showHideBonus();
        });

        $("#TAgentMaxBalance").unbind("focus").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val("");
            }
        });

        $("#TAgentMaxBalance").unbind("blur").blur(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val("");
            }
        });

        $("#TAgentApplyBonus").unbind("change").change(function(){
            resetBonus();
            if($(this).val() == "1"){
                $("#dvAgentBonusAmt").show();
                $("#TAgentBonus").val("0.5");
                $("#TAgentBonus").addClass("validate[required]");
            }
        });
    });

    function showHideBonus(){
        $("#dvAgentApplyBonus").hide();
        $("#TAgentApplyBonus").find("option[value='']").attr("selected", true);
        resetBonus();
        if($("#TAgentType").val() == 1 && $("#TAgentPayment").val() == 1){
            $("#dvAgentApplyBonus").show();
        }
    }

    function resetBonus(){
        $("#dvAgentBonusAmt").hide();
        $("#TAgentBonus").removeClass("validate[required]");
        $("#TAgentBonus").val("");
    }
    
    function alertSelectCompanyTAgent(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_SELECT_COMPANY; ?></p>');
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
                    $(".btnSaveChartAcc").removeAttr('disabled');
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTAgent">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('TAgent'); ?>
<fieldset style="width: 45%; float: left; height: 520px;">
    <legend><?php __(MENU_AGENT_INFO); ?></legend>
    <table>
        <tr>
            <td style="width: 170px;"><label for="TAgentType"><?php echo TABLE_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[TAgent][type]" id="TAgentType" style="width: 210px;" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1"><?php echo TABLE_ONLINE; ?></option>
                        <option value="2"><?php echo TABLE_OFFLINE; ?></option>
                        <option value="3"><?php echo TABLE_API; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TAgentPayment"><?php echo TABLE_PAYMENT; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[TAgent][payment]" id="TAgentPayment" style="width: 210px;" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1"><?php echo 'Prepaid'; ?></option>
                        <option value="2"><?php echo 'Postpaid'; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr id="dvAgentMaxBalance" style="display: none;">
            <td><label for="TAgentMaxBalance"><?php echo 'Max Balance'; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('max_balance', array('class'=> 'float', 'style' => 'width: 200px;')); ?> ($)
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TAgentMainBranchId"><?php echo MENU_MAIN_BRANCH; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('main_branch_id', array('class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 210px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TAgentTAgentType"><?php echo MENU_AGENT_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('t_agent_type_id', array('class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 210px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TAgentCode"><?php echo TABLE_CODE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('code', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TAgentName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TAgentTelephone"><?php echo TABLE_TELEPHONE; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('telephone', array()); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TAgentEMail"><?php echo TABLE_EMAIL; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('e_mail', array()); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TAgentAddress"><?php echo TABLE_ADDRESS; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('address', array('style'=>'height: 50px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TAgentUseDefaultPrice"><?php echo "Use Price Default"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[TAgent][use_default_price]" id="TAgentUseDefaultPrice" style="width: 210px;" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1"><?php echo 'Khmer'; ?></option>
                        <option value="2"><?php echo 'Foreigner'; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TAgentCommision"><?php echo MENU_COMMISION; ?> (Bus) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <input type="radio" value="1" name="data[TAgent][commission_type]" checked="" class="tAgentCommissionType" id="tAgentCommissionType1" /> <label for="tAgentCommissionType1">By Commission (%)</label> <input type="radio" value="2" name="data[TAgent][commission_type]" class="tAgentCommissionAgency tAgentCommissionType" id="tAgentCommissionType2" /> <label class="tAgentCommissionAgency" for="tAgentCommissionType2">By Agency Price</label> <input type="radio" value="3" name="data[TAgent][commission_type]" class="tAgentCommissionAgency tAgentCommissionType" id="tAgentCommissionType3" /> <label class="tAgentCommissionAgency" for="tAgentCommissionType3">Fix Amount</label>
                </div>
            </td>
        </tr>
        <tr id="divTAgentCommission">
            <td></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('commission', array('class'=>'validate[required] float', 'style' => 'width: 200px;', 'value' => 0)); ?> (%)
                </div>
            </td>
        </tr>
        <tr id="divTAgentCommissionFixAmount" style="display: none;">
            <td></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('commission_fix_amount', array('class'=>'validate[required] float', 'style' => 'width: 200px;', 'value' => 0)); ?> ($)
                </div>
            </td>
        </tr>
        <tr id="divTAgentCommissionFixAmount" style="display: none;">
            <td></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('commission_fix_amount', array('class'=>'validate[required] float', 'style' => 'width: 200px;', 'value' => 0)); ?> ($)
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TAgentCommisionBuvaSea"><?php echo MENU_COMMISION; ?> (Buva Sea) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <input type="radio" value="1" name="data[TAgent][commission_buva_sea_type]" checked="" class="tAgentCommissionBuvaSeaType" id="tAgentCommissionBuvaSeaType1" /> <label for="tAgentCommissionBuvaSeaType1">By Commission (%)</label> <input type="radio" value="2" name="data[TAgent][commission_buva_sea_type]" class="tAgentCommissionBuvaSeaAgency tAgentCommissionBuvaSeaType" id="tAgentCommissionBuvaSeaType2" /> <label class="tAgentCommissionBuvaSeaAgency" for="tAgentCommissionBuvaSeaType2">By Agency Price</label> <input type="radio" value="3" name="data[TAgent][commission_buva_sea_type]" class="tAgentCommissionBuvaSeaAgency tAgentCommissionBuvaSeaType" id="tAgentCommissionBuvaSeaType3" /> <label class="tAgentCommissionBuvaSeaAgency" for="tAgentCommissionBuvaSeaType3">Fix Amount</label>
                </div>
            </td>
        </tr>
        <tr id="divTAgentCommissionBuvaSea">
            <td></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('commission_buva_sea', array('class'=>'validate[required] float', 'style' => 'width: 200px;', 'value' => 0)); ?> (%)
                </div>
            </td>
        </tr>
        <tr id="divTAgentCommissionBuvaSeaFixAmount" style="display: none;">
            <td></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('commission_buva_sea_fix_amount', array('class'=>'validate[required] float', 'style' => 'width: 200px;', 'value' => 0)); ?> ($)
                </div>
            </td>
        </tr>
        <tr id="dvAgentApplyBonus" style="display: none;">
            <td><label for="TAgentApplyBonus"><?php echo "Apply Bonus"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[TAgent][apply_bonus]" id="TAgentApplyBonus" style="width: 210px;" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1"><?php echo ACTION_YES; ?></option>
                        <option value="0"><?php echo ACTION_NO; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr id="dvAgentBonusAmt" style="display: none;">
            <td><label for="TAgentBonus"><?php echo "Bonus Amount"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('bonus', array('class'=>'validate[required] float', 'style' => 'width: 200px;', 'value' => 0)); ?> ($)
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<fieldset style="width: 45%; float: left; height: 520px;">
    <legend><?php __(MENU_USERS); ?></legend>
    <table style="width: 90%;">
        <tr>
            <td style="width: 25%;"><label for="UserUsername"><?php echo USER_USER_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('username', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TAgentPassword"><?php echo USER_PASSWORD; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->password('password', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TAgentConfirmPassword"><?php echo USER_CONFIRM_PASSWORD; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->password('confirm_password', array('class'=>'validate[required,equals[TAgentPassword]]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TAgentGroupId"><?php echo USER_GROUP; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('group_id', array('label' => false, 'empty' => INPUT_SELECT, 'class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<div style="clear: both;"></div>
<br />
<fieldset style="width: 45%; float: left;">
    <legend><?php __(MENU_COMPANY_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <th>Available:</th>
            <th></th>
            <th>Member of:</th>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <select id="companyTagent" multiple="multiple" style="width: 270px; height: 200px;">
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
                    $querySource=mysql_query("SELECT id,name FROM companies WHERE is_active=1".$userCon);
                    while($dataSource=mysql_fetch_array($querySource)){
                    ?>
                    <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" class="moveRightCompanyTagent" onclick="listbox_moveacross('companyTagent', 'companySelectedTagent')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" class="moveLeftCompanyTagent" onclick="listbox_moveacross('companySelectedTagent', 'companyTagent')" />
            </td>
            <td style="vertical-align: top;">
                <select id="companySelectedTagent" name="data[TAgent][company_id][]" multiple="multiple" style="width: 270px; height: 200px;"></select>
            </td>
        </tr>
    </table>
</fieldset>
<fieldset style="width: 45%; float: left;">
    <legend><?php __(MENU_BRANCH_INFO); ?></legend>
    <table>
        <tr>
            <th>Available:</th>
            <th></th>
            <th>Member of:</th>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <select id="branchTagent" multiple="multiple" style="width: 270px; height: 200px;">
                    <?php
                    $querySource=mysql_query("SELECT id,name FROM branches WHERE is_active=1".$userCon);
                    while($dataSource=mysql_fetch_array($querySource)){
                    ?>
                    <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('branchTagent', 'branchSelectedTagent')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('branchSelectedTagent', 'branchTagent')" />
            </td>
            <td style="vertical-align: top;">
                <select id="branchSelectedTagent" name="data[TAgent][branch_id][]" multiple="multiple" style="width: 270px; height: 200px;"></select>
            </td>
        </tr>
    </table>
</fieldset>
<div style="clear: both;"></div>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveTAgent"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>