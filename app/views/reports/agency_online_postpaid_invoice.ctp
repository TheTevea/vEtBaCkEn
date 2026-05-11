<?php 
$rnd      = rand();
$frmName  = "frm" . $rnd;
$dueDate  = "dueDate" . $rnd;
$dateFrom = "dateFrom" . $rnd;
$dateTo   = "dateTo" . $rnd;
$agency         = "agency" . $rnd;
$agencyGroup    = "agencyGroup" . $rnd;
$mainBranch     = "mainBranch". $rnd;
$btnSearchLabel = "txtBtnSearch". $rnd;
$btnSearch   = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$result      = "result" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $frmName; ?>").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#<?php echo $agency; ?>, #<?php echo $agencyGroup; ?>, #<?php echo $mainBranch; ?>").chosen({width: 220});
        var dates = $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "<?php echo $dateFrom; ?>" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
            }
        });

        $("#<?php echo $dueDate; ?>").change(function(){
            var date = getDateByDateRange($(this).val());
            $('#<?php echo $dateTo; ?>').datepicker( "option", "minDate", date[0]);
            $('#<?php echo $dateFrom; ?>').datepicker("setDate", date[0]);
            $('#<?php echo $dateTo; ?>').datepicker("setDate", date[1]);
        });
        
        $("#<?php echo $btnSearch; ?>").click(function(){
            var isFormValidated = $("#<?php echo $frmName; ?>").validationEngine('validate');
            if(isFormValidated){
                var issueDateFrom   = $("#<?php echo $dateFrom; ?>").val();
                var issueDateTo     = $("#<?php echo $dateTo; ?>").val();
                if((issueDateFrom != "" && issueDateTo != "")){
                    var link   = 'agencyOnlinePostpaidInvoiceResult';
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/"+link,
                        data: $("#<?php echo $frmName; ?>").serialize(),
                        beforeSend: function(){
                            $("#<?php echo $btnSearch; ?>").attr("disabled", true);
                            $("#<?php echo $btnSearchLabel; ?>").html("<?php echo ACTION_LOADING; ?>");
                            $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                        },
                        success: function(result){
                            $("#<?php echo $btnSearch; ?>").removeAttr("disabled");
                            $("#<?php echo $btnSearchLabel; ?>").html("<?php echo GENERAL_SEARCH; ?>");
                            $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                            $("#<?php echo $result; ?>").html(result);
                        }
                    });
                } else {
                    alert("Please select Issued Date or Travel Date");
                }
            }
        });
        // Button Show Hide
        $("#<?php echo $btnShowHide; ?>").click(function(){
            var text = $(this).text();
            var formFilter = $(".<?php echo $formFilter; ?>");
            if(text == "[<?php echo TABLE_SHOW; ?>]"){
                formFilter.show();
                $(this).text("[<?php echo TABLE_HIDE; ?>]");
            }else{
                formFilter.hide();
                $(this).text("[<?php echo TABLE_SHOW; ?>]");
            }
        });
    });
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<div class="legend">
    <div class="legend_title">
        <?php echo REPORT_SALES_TICKET_AGENCY_ONLINE_POSTPAID; ?> (Invoice)
        <div style="clear: both;"></div>
    </div>
    <div class="legend_content">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%;"><label for="<?php echo $dueDate; ?>"><?php echo 'Due Issued Date'; ?>:</label></td>
                <td style="width: 15%;"><?php echo $this->Form->select($dueDate, $dateRange, null, array('escape' => false, 'empty' => INPUT_SELECT, 'name' => 'due_date')); ?></td>
                <td style="width: 8%;"><label for="<?php echo $dateFrom; ?>"><?php echo REPORT_FROM; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateFrom; ?>" name="from" />
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $dateTo; ?>"><?php echo REPORT_TO; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateTo; ?>" name="to" />
                    </div>
                </td>
                <td>
                    <div class="buttons">
                        <button type="button" id="<?php echo $btnSearch; ?>" class="positive" style="width: 130px;">
                            <img src="<?php echo $this->webroot; ?>img/button/search.png" alt=""/>
                            <span id="<?php echo $btnSearchLabel; ?>"><?php echo GENERAL_SEARCH; ?></span>
                        </button>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%;"><label for="<?php echo $agency; ?>"><?php echo MENU_AGENT; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php
                        echo $this->Form->select($agency, $tAgents, null, array('escape' => false, 'name' => 'agency', 'empty' => TABLE_ALL)); 
                        ?>
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $agencyGroup; ?>"><?php echo "Agency Group"; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php
                        echo $this->Form->select($agencyGroup, $tAgentTypes, null, array('escape' => false, 'name' => 'agency_group', 'empty' => TABLE_ALL)); 
                        ?>
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $mainBranch; ?>"><?php echo MENU_MAIN_BRANCH; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php
                        $empMainBranch = false;
                        if($user['User']['is_admin'] == 1){
                            $empMainBranch = TABLE_ALL;
                        }
                        echo $this->Form->select($mainBranch, $mainBranches, null, array('escape' => false, 'name' => 'main_branch', 'empty' => $empMainBranch)); 
                        ?>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
</div>
</form>
<div id="<?php echo $result; ?>"></div>