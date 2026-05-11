<?php 
$rnd = rand();
$frmName  = "frm" . $rnd;
$dueDate  = "dueDate" . $rnd;
$dueDateTrav  = "dueDateTravel" . $rnd;
$dateFrom = "dateFrom" . $rnd;
$dateTo   = "dateTo" . $rnd;
$travelFrom = "travelFrom" . $rnd;
$travelTo = "travelTo" . $rnd;
$destinationFrom = "destinationFrom" . $rnd;
$destinationTo   = "destinationTo" . $rnd;
$createdBy       = "created" . $rnd;
$paymentMethod   = "paymentMethod" . $rnd;
$status          = "status" . $rnd;
$type            = "type" . $rnd;
$bookingType     = "bookType" . $rnd;
$company         = "company" . $rnd;
$btnSearchLabel  = "txtBtnSearch". $rnd;
$btnSearch = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$result = "result" . $rnd;

?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $paymentMethod; ?>, #<?php echo $type; ?>, #<?php echo $company; ?>").chosen({width: 350});
        $("#<?php echo $destinationFrom; ?>, #<?php echo $destinationTo; ?>").chosen({width: 220});
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
            if(isFormValidated){
                var issueDateFrom   = $("#<?php echo $dateFrom; ?>").val();
                var issueDateTo     = $("#<?php echo $dateTo; ?>").val();
                var travelDateFrom  = $("#<?php echo $travelFrom; ?>").val();
                var travelDateTo    = $("#<?php echo $travelTo; ?>").val();
                if((issueDateFrom != "" && issueDateTo != "") || (travelDateFrom != "" && travelDateTo != "")){
                    var link = 'salesTicketOnlineResult';
                    $("#salesTicketOnlinePaymentType").val($("#<?php echo $paymentMethod; ?>").val());
                    $("#salesTicketOnlineCompany").val($("#<?php echo $company; ?>").val());
                    $("#salesTicketOnlineType").val($("#<?php echo $type; ?>").val());
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

        getDestinationSaleTicket();
    });
    
    function getDestinationSaleTicket(){
        var destinationFrom = $("#<?php echo $destinationFrom; ?>").val();
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base; ?>/reports/getDestinationTo/"+destinationFrom,
            data: $("#<?php echo $frmName; ?>").serialize(),
            beforeSend: function(){
                $("#<?php echo $destinationTo; ?>").html('');
                $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result){
                $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("#<?php echo $destinationTo; ?>").html(result);
                $("#<?php echo $destinationTo; ?>").trigger("chosen:updated");
            }
        });
    }
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<div class="legend">
    <div class="legend_title">
        <?php echo REPORT_SALES_TICKET_ONLINE; ?> <!--<span class="btnShowHide" id="<?php echo $btnShowHide; ?>">[<?php echo TABLE_SHOW; ?>]</span>-->
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
                <td style="width: 8%;"><label for="<?php echo $destinationFrom; ?>"><?php echo TABLE_DESTINATION_FROM; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php
                        echo $this->Form->select($destinationFrom, $destinations, null, array('escape' => false, 'name' => 'destination_from', 'empty' => TABLE_ALL)); 
                        ?>
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $destinationTo; ?>"><?php echo TABLE_DESTINATION_TO; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $destinationTo; ?>" name="destination_to">
                            <option value=""><?php echo TABLE_ALL; ?></option>
                        </select>
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $status; ?>"><?php echo TABLE_STATUS; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $status; ?>" name="status">
                            <option value=""><?php echo TABLE_ALL; ?></option>
                            <option value="2" selected="selected">Completed</option>
                            <option value="0">Void</option>
                        </select>
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $type; ?>"><?php echo TABLE_TYPE; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                    <input type="hidden" name="type" id="salesTicketOnlineType" />
                        <select id="<?php echo $type; ?>" multiple="" data-placeholder="<?php echo TABLE_ALL; ?>">
                            <option value="1">App</option>
                            <option value="2">Website</option>
                            <option value="3">Mini App</option>
                            <option value="4">Terminal</option>
                            <option value="5">Phone Call Payment</option>
                        </select>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%;"><label for="<?php echo $paymentMethod; ?>"><?php echo "Payment Type"; ?>:</label></td>
                <td style="width: 25%;">
                    <div class="inputContainer">
                        <input type="hidden" name="payment_method" id="salesTicketOnlinePaymentType" />
                        <select id="<?php echo $paymentMethod; ?>" multiple="" data-placeholder="<?php echo TABLE_ALL; ?>">
                            <?php
                            $sqlPayMethod = mysql_query("SELECT * FROM payment_methods WHERE is_active = 1 AND id IN (4,5,6,7,8) ORDER BY name ASC");
                            while($rowPayMethod = mysql_fetch_array($sqlPayMethod)){
                            ?>
                            <option value="<?php echo $rowPayMethod['id']; ?>"><?php echo $rowPayMethod['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="width: 5%;"><label for="<?php echo $bookingType; ?>"><?php echo MENU_COMPANY_MANAGEMENT; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="hidden" name="company" id="salesTicketOnlineCompany" />
                        <select id="<?php echo $company; ?>" style="width: 200px;" data-placeholder="<?php echo TABLE_ALL; ?>" multiple="">
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
                <td></td>
            </tr>
        </table>
    </div>
</div>
</form>
<div id="<?php echo $result; ?>"></div>