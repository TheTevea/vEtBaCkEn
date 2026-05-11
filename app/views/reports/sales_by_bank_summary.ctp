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
$branch      = "branch" . $rnd;
$chosen      = "chosen" . $rnd;
$destinationFrom = "destinationFrom" . $rnd;
$destinationTo   = "destinationTo" . $rnd;
$createdBy       = "created" . $rnd;
$paymentMethod   = "paymentMethod" . $rnd;
$status          = "status" . $rnd;
$mainBranch      = "mainBranch" . $rnd;
$viewby          = "viewBy". $rnd;
$bookingType     = "bookingType". $rnd;
$province        = "province". $rnd;
$destGroup       = "destGroup". $rnd;
$btnSearchLabel  = "txtBtnSearch". $rnd;
$btnSearch   = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$result      = "result" . $rnd;
$agency      = "agency" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".<?php echo $chosen; ?>").chosen({width: 280});
        $("#<?php echo $destinationFrom; ?>, #<?php echo $destinationTo; ?>, #<?php echo $province; ?>, #<?php echo $destGroup; ?>, #<?php echo $agency; ?>").chosen({width: 220});
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
            var viewBy = $("#<?php echo $viewby; ?>").val();
            var isFormValidated = $("#<?php echo $frmName; ?>").validationEngine('validate');
            var link = 'salesByBankSummaryResult';
            if(isFormValidated){
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
                        $("#<?php echo $btnSearch; ?>").removeAttr("disabled");
                        $("#<?php echo $btnSearchLabel; ?>").html("<?php echo GENERAL_SEARCH; ?>");
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                        $("#<?php echo $result; ?>").html(result);
                    }
                });
            }
        });

        $("#<?php echo $destinationFrom; ?>").change(function(){
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
                $("#<?php echo $destinationTo; ?>").trigger("chosen:updated");
            }
        });
    }
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<div class="legend">
    <div class="legend_title">
        <?php echo REPORT_SALES_BY_BANK_SUMMARY; ?>
        <div style="clear: both;"></div>
    </div>
    <div class="legend_content">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%;"><label for="<?php echo $dueDate; ?>"><?php echo 'Due Date'; ?>:</label></td>
                <td style="width: 15%;"><?php echo $this->Form->select($dueDate, $dateRange, null, array('escape' => false, 'empty' => INPUT_SELECT, 'name' => 'due_date')); ?></td>
                <td style="width: 8%;"><label for="<?php echo $dateFrom; ?>"><?php echo REPORT_FROM; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateFrom; ?>" name="booking_from" />
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $dateTo; ?>"><?php echo REPORT_TO; ?>:</label></td>
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
                <td style="width: 8%;"><label for="<?php echo $destinationFrom; ?>"><?php echo TABLE_DESTINATION_FROM; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php
                        echo $this->Form->select($destinationFrom, $destinations, null, array('escape' => false, 'name' => 'destination_from', 'empty' => TABLE_ALL)); 
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
                <td style="width: 8%;"><label for="<?php echo $destGroup; ?>"><?php echo MENU_DESTINATION_GROUP; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php
                        echo $this->Form->select($destGroup, $origins, null, array('escape' => false, 'name' => 'destination_group', 'empty' => INPUT_SELECT)); 
                        ?>
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $province; ?>"><?php echo MENU_PROVINCE_MANAGEMENT; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php
                        echo $this->Form->select($province, $provinces, null, array('escape' => false, 'name' => 'province', 'empty' => INPUT_SELECT)); 
                        ?>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
</form>
<div id="<?php echo $result; ?>"></div>