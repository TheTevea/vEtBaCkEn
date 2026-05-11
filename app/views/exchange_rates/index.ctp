<?php
$this->element('check_access');
$allowAdd = checkAccess($user['User']['id'], $this->params['controller'], 'add');
$tblName = "tbl" . rand(); 
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnAddExchangeRate").click(function(event){
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
        });
        $("#companyExchangeRate").change(function(){
            loadExchnage();
        });
        loadExchnage();
    });
    
    function loadExchnage(){
        var companyId = $("#companyExchangeRate").find("option:selected").val();
        $.ajax({
            type: "POST",
            url:    "<?php echo $this->base . "/".$this->params['controller']."/ajax"; ?>/"+companyId,
            beforeSend: function() {
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner.gif');
                $("#divExchange").html('<tr><td colspan="9" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td></tr>');
            },
            success: function(result) {
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#divExchange").html(result);
                <?php
                if($allowAdd){
                ?>
                keyEventExchange();
                <?php
                }
                ?>
            }
        });
    }
    <?php
    if($allowAdd){
    ?>
    function keyEventExchange(){
        
        $(".btnViewExchangeHistory").unbind("keyup").unbind("focus").unbind("blur").unbind("click");
        $(".rateSell, .rateChange").unbind("keyup").unbind("focus").unbind("blur");
        $(".rateSell, .rateChange").focus(function(){
            var value = replaceNum($(this).val());
            if(value == '0'){
                $(this).val('');
            }
        });
        
        $(".rateSell, .rateChange").blur(function(){
            var value = $(this).val();
            if(value == ''){
                $(this).val('0');
            }
            
            saveExchange($(this));
        });
        
        $(".btnViewExchangeHistory").click(function(event){            
            event.preventDefault();
            var comId = $(this).attr('com-id');
            var currencyCenterId = $(this).attr('currency-center-id');
            $.ajax({
                type: "POST",
                url:    "<?php echo $this->base . "/".$this->params['controller']."/view"; ?>/"+ comId + "/" +currencyCenterId,
                beforeSend: function() {
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(result) {
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
                    $("#dialog").html(result);
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_CONFIRMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: '90%',
                        height: '500',
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
                }
            });
        });
    }
    
    function saveExchange(obj){
        var comCurrencyId = obj.attr("exrate");
        var rateSell      = obj.closest("tr").find(".rateSell").val();
        var rateChange    = obj.closest("tr").find(".rateChange").val();
        $.ajax({
            type: "POST",
            url:    "<?php echo $this->base . "/".$this->params['controller']."/add"; ?>/"+comCurrencyId,
            data:   "rate_sell="+rateSell+"&rate_change="+rateChange,
            beforeSend: function() {
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner.gif');
                obj.closest("tr").find(".rateSell").attr("readonly", true);
                obj.closest("tr").find(".rateChange").attr("readonly", true);
            },
            success: function() {
                // alert message
                createSysAct('Exchange Rate', 'Add', 1, '');
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                obj.closest("tr").find(".rateSell").attr("readonly", false);
                obj.closest("tr").find(".rateChange").attr("readonly", false);
            }
        });
    }
    <?php
    }
    ?>
</script>
<div class="leftPanel">
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div style="float:left;">
            <?php echo TABLE_COMPANY; ?> :
            <select id="companyExchangeRate" style="width:200px; height: 30px;">
                <?php
                $sqlCom = mysql_query("SELECT id, name FROM companies WHERE is_active = 1;");
                while($rowCom = mysql_fetch_array($sqlCom)){
                ?>
                <option value="<?php echo $rowCom['id']; ?>"><?php echo $rowCom['name']; ?></option>
                <?php
                }
                ?>
            </select>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_COMPANY; ?></th>
                    <th><?php echo REPORT_FROM; ?></th>
                    <th><?php echo TABLE_RATE; ?></th>
                    <th><?php echo REPORT_TO; ?></th>
                    <th><?php echo TABLE_RATE_FOR_SELL; ?></th>
                    <th><?php echo TABLE_RATE_FOR_CHANGE; ?></th>
                    <th><?php echo TABLE_MODIFIED; ?></th>
                    <th style="width: 100px;"><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody id="divExchange"></tbody>
        </table>
    </div>
    <br />
</div>
<div class="rightPanel"></div>