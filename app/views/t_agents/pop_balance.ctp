<?php 
echo $this->element('prevent_multiple_submit'); 
$tblName = "tbl" . rand(); 
?>
<script type="text/javascript">
    var oTableTopupAgent;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#TAgentAmount").autoNumeric({mDec: 2, aSep: ','});
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableTopupAgent = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/popBalanceAjax/<?php echo $this->data['TAgent']['id']; ?>",
            "fnServerData": fnDataTablesPipeline,
            "iDisplayLength": 15,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?>_filter").find("input").attr('style', 'width: 170px;');
                $("#<?php echo $tblName; ?>_length").hide();
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ -1 ]
            }],
            "aaSorting": [[ 0, "desc" ]]
        });
        
        $("#TAgentPopBalanceForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TAgentPopBalanceForm").ajaxForm({
            dataType: 'json',
            beforeSerialize: function($form, options) {
                $("#TAgentAmount").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTopupAgent").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Agency', 'Topup', 2, result.responseText);
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    position:'center',
                    closeOnEscape: true,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show(); 
                        $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $("meta[http-equiv='refresh']").attr('content','0');
                            $(this).dialog("close");
                        }
                    }
                });
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Agency', 'Topup', 1, '');
                // Reset Form
                document.getElementById("TAgentPopBalanceForm").reset();
                $(".btnSaveTopupAgent").removeAttr("disabled");
                $(".txtSaveTopupAgent").html("<?php echo ACTION_SAVE; ?>");
                // Reload List
                oCache.iCacheLower = -1;
                oTableTopupAgent.fnDraw(false);
                // Message Alert
                if(result.error == 1){
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                } else {
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
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
                // Event Key
                eventKeyTopupAgent();
            }
        });
        
        $(".btnBackTAgent").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTAgent.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        // Event Key
        eventKeyTopupAgent();
    });
    
    function eventKeyTopupAgent(){
        $("#TAgentAmount").unbind("focus").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $("#TAgentAmount").unbind("blur").blur(function(){
            if($(this).val() == ''){
                $(this).val('0');
            }
        });
    }
</script>
<div style="width: 40%; float: left;">
    <?php echo $this->Form->create('TAgent'); ?>
    <input type="hidden" name="data[t_agency_id]" value="<?php echo $this->data['TAgent']['id']; ?>" />
    <fieldset>
        <legend><?php __('Topup Balance'); ?> (<?php echo $this->data['TAgent']['code']." - ".$this->data['TAgent']['name']; ?>)</legend>
        <table cellpadding="5">
            <tr>
                <td><label for="TAgentAmount"><?php echo GENERAL_AMOUNT; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->text('amount', array('class'=>'validate[required]', 'style' => 'width: 270px; height: 30px;', 'id' => 'TAgentAmount', 'name' => 'data[amount]')); ?> $
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="TAgentType"><?php echo TABLE_TYPE; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer">
                        <select id="TAgentType" name="data[type]" style="width: 120px; height: 30px;" class="validate[required]">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <option value="1"><?php echo 'Debit (-)'; ?></option>
                            <option value="2"><?php echo 'Credit (+)'; ?></option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="TAgentNote"><?php echo TABLE_NOTE; ?> :</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->text('note', array('style' => 'width: 400px; height: 30px;', 'id' => 'TAgentNote', 'name' => 'data[note]')); ?>
                    </div>
                </td>
            </tr>
        </table>
        <br />
        <div class="buttons">
            <a href="" class="positive btnBackTAgent">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div class="buttons">
            <button type="submit" class="positive btnSaveTopupAgent">
                <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                <span class="txtSaveTopupAgent"><?php echo ACTION_SAVE; ?></span>
            </button>
        </div>
        <div style="clear: both;"></div>
    </fieldset>
    <?php echo $this->Form->end(); ?>
</div>
<div style="width: 59%; float: right;">
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first" style="width: 90px !important;"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_CODE; ?></th>
                    <th><?php echo GENERAL_AMOUNT; ?> ($)</th>
                    <th><?php echo TABLE_TYPE; ?></th>
                    <th><?php echo TABLE_NOTE; ?></th>
                    <th><?php echo TABLE_CREATED; ?></th>
                    <th><?php echo TABLE_CREATED_BY; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="8" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div style="clear: both;"></div>