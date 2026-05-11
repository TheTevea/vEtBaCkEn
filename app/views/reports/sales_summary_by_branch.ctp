<?php 
$rnd = rand();
$frmName     = "frm" . $rnd;
$dueDate     = "dueDate" . $rnd;
$dueDateTrav = "dueDateTravel" . $rnd;
$dateFrom    = "dateFrom" . $rnd;
$dateTo      = "dateTo" . $rnd;
$travelFrom  = "travelFrom" . $rnd;
$travelTo    = "travelTo" . $rnd;
$company     = "company" . $rnd;
$companyRes  = "companyRes" . $rnd;
$chosen      = "chosen" . $rnd;
$status          = "status" . $rnd;
$mainBranch      = "mainBranch" . $rnd;
$agency          = "agency". $rnd;
$btnSearchLabel  = "txtBtnSearch". $rnd;
$btnSearch   = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$result      = "result" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".<?php echo $chosen; ?>").chosen({width: 280});
        $("#<?php echo $mainBranch; ?>, #<?php echo $agency; ?>").chosen({width: 220});
        $("#<?php echo $frmName; ?>").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
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
        
        var dateTravels = $("#<?php echo $travelFrom; ?>, #<?php echo $travelTo; ?>").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "<?php echo $travelFrom; ?>" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    dateT = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dateTravels.not( this ).datepicker( "option", option, dateT );
            }
        });

        $("#<?php echo $dueDate; ?>").change(function(){
            var date = getDateByDateRange($(this).val());
            $('#<?php echo $dateTo; ?>').datepicker( "option", "minDate", date[0]);
            $('#<?php echo $dateFrom; ?>').datepicker("setDate", date[0]);
            $('#<?php echo $dateTo; ?>').datepicker("setDate", date[1]);
        });

        $("#<?php echo $dueDateTrav; ?>").change(function(){
            var date = getDateByDateRange($(this).val());
            $('#<?php echo $travelTo; ?>').datepicker( "option", "minDate", date[0]);
            $('#<?php echo $travelFrom; ?>').datepicker("setDate", date[0]);
            $('#<?php echo $travelTo; ?>').datepicker("setDate", date[1]);
        });
        
        $("#<?php echo $btnSearch; ?>").click(function(){
            var isFormValidated = $("#<?php echo $frmName; ?>").validationEngine('validate');
            var link = 'salesSummaryByBranchResult';
            if(isFormValidated){
                var issueDateFrom   = $("#<?php echo $dateFrom; ?>").val();
                var issueDateTo     = $("#<?php echo $dateTo; ?>").val();
                var travelDateFrom  = $("#<?php echo $travelFrom; ?>").val();
                var travelDateTo    = $("#<?php echo $travelTo; ?>").val();
                if((issueDateFrom != "" && issueDateTo != "") || (travelDateFrom != "" && travelDateTo != "")){
                    if($("#<?php echo $company; ?>").val() == null){
                        $("#<?php echo $companyRes; ?>").val("");
                    } else {
                        $("#<?php echo $companyRes; ?>").val($("#<?php echo $company; ?>").val());
                    }
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
                            $(".StatusBy").attr("name", "StatusBy");
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
    });
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<div class="legend">
    <div class="legend_title">
        <?php echo REPORT_SALES_TICKET_BRANCH." Summary"; ?>
        <div style="clear: both;"></div>
    </div>
    <div class="legend_content">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%;"><label for="<?php echo $dueDate; ?>"><?php echo 'Due Issued Date'; ?>:</label></td>
                <td style="width: 15%;"><?php echo $this->Form->select($dueDate, $dateRange, null, array('escape' => false, 'empty' => INPUT_SELECT, 'name' => 'due_date')); ?></td>
                <td style="width: 8%;"><label for="<?php echo $dateFrom; ?>"><?php echo TABLE_BOOKING_FROM; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateFrom; ?>" name="booking_from" />
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $dateTo; ?>"><?php echo TABLE_BOOKING_TO; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateTo; ?>" name="booking_to" />
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
                <td style="width: 8%;"><label for="<?php echo $dueDateTrav; ?>"><?php echo 'Due Travel Date'; ?>:</label></td>
                <td style="width: 15%;"><?php echo $this->Form->select($dueDateTrav, $dateRange, null, array('escape' => false, 'empty' => INPUT_SELECT, 'name' => 'due_date_travel')); ?></td>
                <td style="width: 8%;"><label for="<?php echo $travelFrom; ?>"><?php echo TABLE_TRAVELING_FROM; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $travelFrom; ?>" name="traveling_from" />
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $travelTo; ?>"><?php echo TABLE_TRAVELING_TO; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $travelTo; ?>" name="traveling_to" />
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%;"><label for="<?php echo $company; ?>"><?php echo MENU_COMPANY_MANAGEMENT; ?>:</label></td>
                <td style="width: 20%;">
                    <div class="inputContainer">
                        <input type="hidden" id="<?php echo $companyRes; ?>" name="company" />
                        <select id="<?php echo $company; ?>" style="width: 200px;" data-placeholder="<?php echo TABLE_ALL; ?>" class="<?php echo $chosen; ?>" multiple="">
                            <option value=""><?php echo TABLE_ALL; ?></option>
                            <option value="1,2">VET Ticket</option>
                            <?php
                            foreach($companies AS $com){
                                if($com['Company']['id'] != 1 && $com['Company']['id'] != 2){
                            ?>
                            <option value="<?php echo $com['Company']['id']; ?>"><?php echo $com['Company']['name']; ?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $status; ?>"><?php echo TABLE_SHOW; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $status; ?>" name="show">
                            <option value=""><?php echo TABLE_ALL; ?></option>
                            <option value="1">Branch</option>
                            <option value="2">Agency</option>
                        </select>
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
                <td style="width: 7%;"><label for="<?php echo $agency; ?>"><?php echo MENU_AGENT; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php
                        if($user['User']['type'] == 2){
                            $empty = TABLE_ALL;
                        } else {
                            $empty = false;
                        }
                        echo $this->Form->select($agency, $tAgents, null, array('escape' => false, 'name' => 'agency', 'empty' => $empty)); 
                        ?>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
</form>
<div id="<?php echo $result; ?>"></div>