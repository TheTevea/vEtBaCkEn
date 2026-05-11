<?php 
$rnd = rand();
$frmName  = "frm" . $rnd;
$dueDate  = "dueDate" . $rnd;
$dateFrom = "dateFrom" . $rnd;
$dateTo   = "dateTo" . $rnd;
$journey     = "journey" . $rnd;
$routeCode   = "routeCode" . $rnd;
$viewBooks   = "viewBookingDate".$rnd;
$viewTravel  = "viewTravelDate".$rnd;
$btnSearchLabel = "txtBtnSearch". $rnd;
$btnSearch   = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$result      = "result" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $journey; ?>").chosen();
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
        $("#<?php echo $dueDate; ?>").change(function(){
            var date = getDateByDateRange($(this).val());
            $('#<?php echo $dateTo; ?>').datepicker( "option", "minDate", date[0]);
            $('#<?php echo $dateFrom; ?>').datepicker("setDate", date[0]);
            $('#<?php echo $dateTo; ?>').datepicker("setDate", date[1]);
        });
        $("#<?php echo $btnSearch; ?>").click(function(){
            var isFormValidated = $("#<?php echo $frmName; ?>").validationEngine('validate');
            if(isFormValidated){
                $("#<?php echo $journey; ?>").removeAttr("name");
                var journey = "";
                if($("#<?php echo $journey; ?>").val() != null){
                    journey = $("#<?php echo $journey; ?>").val();
                }
                $("#salesTicketBySeatJourney").val(journey);
                var link = 'salesTicketVoidResult';
                if($("#<?php echo $routeCode; ?>").val() != ""){
                    var routeCode = $("#<?php echo $routeCode; ?>").val().replace(/:/g, "*");
                    routeCode = routeCode.replace(/\s/g, ']');
                    routeCode = routeCode.replace(/-/g, '[');
                    $("#ticketSeatByRoute").val(routeCode);
                } else {
                    $("#ticketSeatByRoute").val("");
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
                        $("#<?php echo $btnSearch; ?>").removeAttr("disabled");
                        $("#<?php echo $btnSearchLabel; ?>").html("<?php echo GENERAL_SEARCH; ?>");
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                        $("#<?php echo $result; ?>").html(result);
                    }
                });
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
        <?php echo REPORT_SALES_TICKET_VOID; ?> <span class="btnShowHide" id="<?php echo $btnShowHide; ?>">[<?php echo TABLE_HIDE; ?>]</span>
        <div style="clear: both;"></div>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%;"><label for="<?php echo $dueDate; ?>"><?php echo REPORT_DUE_DATE; ?>:</label></td>
                <td style="width: 15%;"><?php echo $this->Form->select($dueDate, $dateRange, null, array('escape' => false, 'empty' => INPUT_SELECT, 'name' => 'due_date')); ?></td>
                <td style="width: 8%;"><label for="<?php echo $dateFrom; ?>"><?php echo REPORT_FROM; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateFrom; ?>" name="date_from" class="validate[required]" />
                    </div>
                </td>
                <td style="width: 5%;"><label for="<?php echo $dateTo; ?>"><?php echo REPORT_TO; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateTo; ?>" name="date_to" class="validate[required]" />
                    </div>
                </td>
                <td style="width: 20%">
                    <input type="radio" name="view_date" checked="" value="1" id="<?php echo $viewBooks; ?>" style="width: auto;"><label for="<?php echo $viewBooks; ?>">Booking Date</label>
                    <input type="radio" name="view_date" value="2" id="<?php echo $viewTravel; ?>" style="width: auto;"><label for="<?php echo $viewTravel; ?>">Travel Date</label>
                    <input type="hidden" name="journey" value="" id="salesTicketBySeatJourney" />
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
                <td style="width: 8%;"><label for="<?php echo $journey; ?>"><?php echo MENU_JOURNEY; ?>:</label></td>
                <td style="width: 60%;">
                    <div class="inputContainer">
                        <?php
                        echo $this->Form->select($journey, $tJourneys, null, array('escape' => false, 'name' => false, 'placeholder' => TABLE_ALL, 'multiple' => true)); 
                        ?>
                    </div>
                </td>
                <td style="width: 5%;"><label for="<?php echo $routeCode; ?>"><?php echo "Route Code"; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="hidden" id="ticketSeatByRoute" name="route_code" />
                        <input type="text" id="<?php echo $routeCode; ?>" />
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>    
</div>
</form>
<div id="<?php echo $result; ?>"></div>