<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    var indexRowExpenseType = 0;
    var rowExpenseTypeList  =  $("#rowExpenseType");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#rowExpenseType").remove();
        $("#ExpenseTypeAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#ExpenseTypeAddForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveExpenseType").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackExpenseType").click();
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
        $(".btnBackExpenseType").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableExpenseType.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        // Clone Row Location List
        cloneExpenseTypeRow();
    });
    
    function cloneExpenseTypeRow(){
        if($(".rowExpenseType:last").find(".name").attr("id") == undefined){
            indexRowExpenseType = 1;
        }else{
            indexRowExpenseType = parseInt($(".rowExpenseType:last").find(".name").attr("id").split("_")[1]) + 1;
        }
        var tr    = rowExpenseTypeList.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .name").val('');
        tr.find("td .name").attr("id", "name_"+indexRowExpenseType);
        tr.find("td .is_for_sale").attr("id", "is_for_sale_"+indexRowExpenseType);
        $("#tblLocation").append(tr);
        var LenTr = parseInt($(".rowExpenseType").length);
        if(LenTr == 1){
            $("#tblLocation").find("tr:eq("+LenTr+")").find(".btnAddLocationRow").show();
            $("#tblLocation").find("tr:eq("+LenTr+")").find(".btnRemoveLocation").hide();
        }
        tr.find("td .name").focus();
        eventKeyRowExpenseType();
    }
    
    function eventKeyRowExpenseType(){
        $(".name, .btnAddLocationRow, .btnRemoveLocation").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        
        $(".name").blur(function(){
            var curId   = $(this).attr('id');
            var curName = $(this).val();
            var ready   = false;
            var obj     = $(this);
            $(".name").each(function(){
                var id   = $(this).attr('id');
                var name = $(this).val();
                if(id != curId){
                    if(curName == name){
                        ready = true;
                    }
                }
            });
            if(ready == true){
                $("#dialog").html('<p><?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?></p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_WARNING; ?>',
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
                            obj.select().focus();
                        }
                    }
                });
            }
        });
        
        $(".name").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
        
        $(".btnAddLocationRow").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveLocation").show();
            cloneExpenseTypeRow();
        });
        $(".btnRemoveLocation").click(function(){
            var obj = $(this);
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete the selected item(s)?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_DELETE; ?>': function() {
                        obj.closest("tr").remove();
                        var lenTr = parseInt($(".rowExpenseType").length);
                        if(lenTr == 1){
                            $("#tblLocation").find("tr:eq("+lenTr+")").find("td .btnRemoveLocation").hide();
                        }
                        $("#tblLocation").find("tr:eq("+lenTr+")").find("td .btnAddLocationRow").show();
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackExpenseType">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('ExpenseType'); ?>
<fieldset>
    <legend><?php __(MENU_EXPENSE_TYPE_INFO); ?></legend>
    <table id="tblLocation" class="table" style="width: 70%;">
        <tr>
            <th class="first" style="width: 80%;"><?php echo TABLE_NAME; ?></th>
            <th><?php echo ACTION_ACTION; ?></th>
        </tr>
        <tr id="rowExpenseType" class="rowExpenseType" style="visibility: hidden;">
            <td class="first">
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="data[name][]" style="width: 90%; height: 30px;" id="name" class="name validate[required]" />
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <img alt="" src="<?php echo $this->webroot.'img/button/plus.png'; ?>" class="btnAddLocationRow" style="cursor: pointer;" onmouseover="Tip('Add More')" />
                    &nbsp; <img alt="" src="<?php echo $this->webroot.'img/button/cross.png'; ?>" class="btnRemoveLocation" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveExpenseType"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>