<?php 
$rnd = rand();
$frmName  = "frm" . $rnd;
$dueDate  = "dueDate" . $rnd;
$dateFrom = "dateFrom" . $rnd;
$dateTo   = "dateTo" . $rnd;
$bues     = "bus" . $rnd;
$busType  = "busType" . $rnd;
$type     = "type" . $rnd;
$btnSearchLabel = "txtBtnSearch". $rnd;
$btnSearch      = "btnSearch" . $rnd;
$btnShowHide    = "btnShowHide". $rnd;
$formFilter     = "formFilter".$rnd;
$result         = "result" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $bues; ?>, #<?php echo $busType; ?>").chosen({width: 230});
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
                var link = 'userFeedbackResult';
                var access = true;
                if($("#<?php echo $type; ?>").val() == "2"){
                    link = 'userFeedbackSummary';
                }
                if(link == 'userFeedbackResult'){
                    if($("#<?php echo $bues; ?>").val() == ""){
                        access = false;
                    }
                }
                if(access){
                    $("input[name='status']").val($(".StatusBy:checked").val());
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
                    alert("Please select bus!");
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
        <?php echo REPORT_SURVEY; ?>
        <div style="clear: both;"></div>
    </div>
    <div class="legend_content">
        <table style="width: 100%;">
            <tr>
                <td style="width: 7%;"><label for="<?php echo $dueDate; ?>"><?php echo REPORT_DUE_DATE; ?>:</label></td>
                <td style="width: 15%;"><?php echo $this->Form->select($dueDate, $dateRange, null, array('escape' => false, 'empty' => INPUT_SELECT, 'name' => 'due_date')); ?></td>
                <td style="width: 7%;"><label for="<?php echo $dateFrom; ?>"><?php echo REPORT_FROM; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateFrom; ?>" name="date_from" class="validate[required]" />
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $dateTo; ?>"><?php echo REPORT_TO; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateTo; ?>" name="date_to" class="validate[required]" />
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
                <td style="width: 7%;"><label for="<?php echo $busType; ?>"><?php echo MENU_BUS_TYPE; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select name="bus_type" id="<?php echo $busType; ?>">
                            <option value=""><?php echo TABLE_ALL; ?></option>
                            <?php
                            $sqlBusType = mysql_query("SELECT * FROM bus_types WHERE is_active = 1 ORDER BY name");
                            while($rowBusType = mysql_fetch_array($sqlBusType)){
                            ?>
                            <option value="<?php echo $rowBusType['id']; ?>"><?php echo $rowBusType['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $bues; ?>"><?php echo MENU_BUS; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select name="bus" id="<?php echo $bues; ?>">
                            <option value=""><?php echo TABLE_ALL; ?></option>
                            <?php
                            $sqlBuses = mysql_query("SELECT * FROM buses WHERE is_active = 1 ORDER BY code");
                            while($rowBuses = mysql_fetch_array($sqlBuses)){
                            ?>
                            <option value="<?php echo $rowBuses['id']; ?>"><?php echo $rowBuses['code']." (".$rowBuses['name'].")"; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $type; ?>"><?php echo TABLE_TYPE; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $type; ?>">
                            <option value="1"><?php echo "Detail"; ?></option>
                            <option value="2"><?php echo "Summary"; ?></option>
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