<?php 
$rnd = rand();
$frmName  = "frm" . $rnd;
$dueDate  = "dueDate" . $rnd;
$dateFrom = "dateFrom" . $rnd;
$dateTo   = "dateTo" . $rnd;
$travelFrom = "travelFrom" . $rnd;
$travelTo = "travelTo" . $rnd;
$company  = "company" . $rnd;
$branch   = "branch" . $rnd;
$destinationFrom = "destinationFrom" . $rnd;
$destinationTo = "destinationTo" . $rnd;
$createdBy     = "created" . $rnd;
$paymentMethod = "paymentMethod" . $rnd;
$status        = "status" . $rnd;
$type          = "type" . $rnd;
$btnSearchLabel = "txtBtnSearch". $rnd;
$btnSearch = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$result = "result" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Hide Branch
        $("#<?php echo $company; ?>").chosen({width: 350});
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
        
        $("#<?php echo $btnSearch; ?>").click(function(){
            var isFormValidated = $("#<?php echo $frmName; ?>").validationEngine('validate');
            if(isFormValidated){
                var link = 'salesTicketVatResult';
                $("input[name='status']").val($(".StatusBy:checked").val());
                var company = "";
                if($("#<?php echo $company; ?>").val() != null){
                    company = $("#<?php echo $company; ?>").val();
                }
                $("#<?php echo $company; ?>Val").val(company);
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
        
        $("#<?php echo $branch; ?>").change(function(){
            getDestinationSaleTicket();
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
            }
        });
    }
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<div class="legend">
    <div class="legend_title">
        <?php echo REPORT_SALES_TICKET_BRANCH; ?> (VAT) <!-- <span class="btnShowHide" id="<?php echo $btnShowHide; ?>">[<?php echo TABLE_SHOW; ?>]</span> -->
        <div style="clear: both;"></div>
    </div>
    <div class="legend_content">
        <table style="width: 100%;">
            <tr>
                <td style="width: 7%;"><label for="<?php echo $dateFrom; ?>"><?php echo TABLE_BOOKING_FROM; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateFrom; ?>" name="booking_from" class="validate[required]" />
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $dateTo; ?>"><?php echo TABLE_BOOKING_TO; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateTo; ?>" name="booking_to" class="validate[required]" />
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $travelFrom; ?>"><?php echo TABLE_TRAVELING_FROM; ?>:</label></td>
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
                <td style="width: 7%;"><label for="<?php echo $company; ?>"><?php echo MENU_COMPANY_MANAGEMENT; ?>:</label></td>
                <td style="width: 25%;">
                    <div class="inputContainer">
                        <input type="hidden" name="company" id="<?php echo $company; ?>Val" />
                        <?php 
                        echo $this->Form->select($company, $companies, null, array('escape' => false, 'name' => '', 'empty' => false, 'multiple' => true, 'data-placeholder' => 'All')); 
                        ?>
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $branch; ?>"><?php echo MENU_BRANCH; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select name="branch" id="<?php echo $branch; ?>">
                            <?php
                            if(count($branches) != 1){
                            ?>
                            <option value="" com=""><?php echo TABLE_ALL; ?></option>
                            <?php
                            }
                            foreach($branches AS $value){
                            ?>
                            <option value="<?php echo $value['Branch']['id']; ?>" com="<?php echo $value['Branch']['company_id']; ?>"><?php echo $value['Branch']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $destinationFrom; ?>"><?php echo TABLE_DESTINATION_FROM; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php
                        echo $this->Form->select($destinationFrom, $destinations, null, array('escape' => false, 'name' => 'destination_from', 'empty' => INPUT_SELECT)); 
                        ?>
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $destinationTo; ?>"><?php echo TABLE_DESTINATION_TO; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $destinationTo; ?>" name="destination_to">
                            <option value=""><?php echo TABLE_ALL; ?></option>
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
                <td style="width: 7%;"><label for="<?php echo $type; ?>"><?php echo TABLE_TYPE; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $type; ?>" name="type">
                            <option value=""><?php echo TABLE_ALL; ?></option>
                            <option value="1"><?php echo "Walk In"; ?></option>
                            <option value="2"><?php echo "APP"; ?></option>
                            <option value="3"><?php echo "Website"; ?></option>
                            <option value="4"><?php echo "API"; ?></option>
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