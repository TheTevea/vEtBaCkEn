<?php 
$rnd = rand();
$frmName = "frm" . $rnd;
$dueDate = "dueDate" . $rnd;
$dateFrom = "dateFrom" . $rnd;
$dateTo = "dateTo" . $rnd;
$dateYear = "year" . $rnd;
$dateMonth = "month" . $rnd;
$company  = "company" . $rnd;
$branch = "branch" . $rnd;
$viewDay = "viewDay" . $rnd;
$viewMonth = "viewMonth" . $rnd;
$viewBy = "viewBy". $rnd;
$viewByMonth = "viewByMonth". $rnd;
$incomeReference = "incomeReference". $rnd;
$incomeReferenceReceipt = "incomeReferenceReceipt". $rnd;
$incomeReferenceVan = "incomeReferenceVan". $rnd;
$btnSearchLabel = "txtBtnSearch". $rnd;
$btnSearch = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$result = "result" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Hide Branch
        $("#<?php echo $branch; ?>").filterOptions('com', '0', '');
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
                var reference = $(".incomeReference:checked").val();
                var link = 'netProfitResult';
                $("#<?php echo $incomeReference; ?>").val(1);
                if($('.viewBy:checked').val() == 2){
                    link = 'netProfitMonthResult';
                }
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/"+link,
                    data: $("#<?php echo $frmName; ?>").serialize(),
                    beforeSend: function(){
                        $("#<?php echo $btnSearch; ?>").attr("disabled", true);
                        $("#<?php echo $btnSearchLabel; ?>").html("<?php echo ACTION_LOADING; ?>");
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                        $(".incomeReference").removeAttr('name');
                    },
                    success: function(result){
                        $("#<?php echo $btnSearch; ?>").removeAttr("disabled");
                        $("#<?php echo $btnSearchLabel; ?>").html("<?php echo GENERAL_SEARCH; ?>");
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                        $("#<?php echo $result; ?>").html(result);
                        $(".incomeReference").attr('name', 'incomeReference');
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
        
        $(".viewBy").click(function(){
            var viewBy = $('.viewBy:checked').val();
            if(viewBy == 1){
                $(".<?php echo $viewByMonth; ?>").hide();
                $(".<?php echo $viewBy; ?>").show();
                $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").addClass("validate[required]");
            } else {
                $(".<?php echo $viewByMonth; ?>").show();
                $(".<?php echo $viewBy; ?>").hide();
                $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").removeClass("validate[required]");
            }
        });
        // Company Change
        $("#<?php echo $company; ?>").change(function(){
            if($(this).val() != ''){
                $("#<?php echo $branch; ?>").filterOptions('com', $(this).val(), '');
            } else {
                $("#<?php echo $branch; ?>").filterOptions('com', $(this).val(), '');
            }
        });
    });
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<div class="legend">
    <div class="legend_title">
        <?php echo REPORT_NET_PROFIT; ?> <span class="btnShowHide" id="<?php echo $btnShowHide; ?>">[<?php echo TABLE_HIDE; ?>]</span>
        <div style="clear: both;"></div>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 6%;"><?php echo TABLE_VIEW_BY; ?>:</td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="radio" name="viewBy" value="1" id="<?php echo $viewDay; ?>" class="viewBy" style="width: auto;" checked=""><label for="<?php echo $viewDay; ?>"><?php echo TABLE_DAY; ?></label> <input type="radio" name="viewBy" value="2" id="<?php echo $viewMonth; ?>" class="viewBy" style="width: auto;"><label for="<?php echo $viewMonth; ?>"><?php echo TABLE_MONTH; ?></label>
                    </div>
                </td>
                <td style="width: 6%;" class="<?php echo $viewBy; ?>"><label for="<?php echo $dueDate; ?>"><?php echo REPORT_DUE_DATE; ?>:</label></td>
                <td style="width: 15%;" class="<?php echo $viewBy; ?>"><?php echo $this->Form->select($dueDate, $dateRange, null, array('escape' => false, 'empty' => INPUT_SELECT, 'name' => 'due_date')); ?></td>
                <td style="width: 8%;" class="<?php echo $viewBy; ?>"><label for="<?php echo $dateFrom; ?>"><?php echo REPORT_FROM; ?>:</label></td>
                <td style="width: 15%;" class="<?php echo $viewBy; ?>">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateFrom; ?>" name="date_from" class="validate[required]" />
                    </div>
                </td>
                <td style="width: 8%;" class="<?php echo $viewBy; ?>"><label for="<?php echo $dateTo; ?>"><?php echo REPORT_TO; ?>:</label></td>
                <td style="width: 15%;" class="<?php echo $viewBy; ?>">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateTo; ?>" name="date_to" class="validate[required]" />
                    </div>
                </td>
                <td style="width: 8%; display: none;" class="<?php echo $viewByMonth; ?>"><label for="<?php echo $dateYear; ?>"><?php echo TABLE_YEAR; ?>:</label></td>
                <td style="width: 15%; display: none;" class="<?php echo $viewByMonth; ?>">
                    <div class="inputContainer">
                        <select name="year" id="<?php echo $dateYear; ?>" style="width: 90%;">
                            <?php
                            for($i=2016; $i<2041; $i++){
                                $selected = '';
                                if($i == date('Y')){
                                    $selected = 'selected="selected"';
                                }
                            ?>
                            <option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="width: 8%; display: none;" class="<?php echo $viewByMonth; ?>"><label for="<?php echo $dateMonth; ?>"><?php echo TABLE_MONTH; ?>:</label></td>
                <td style="width: 15%; display: none;" class="<?php echo $viewByMonth; ?>">
                    <div class="inputContainer">
                        <select name="month" id="<?php echo $dateMonth; ?>" style="width: 90%;">
                            <?php
                            for($i=1; $i<12; $i++){
                                $selected = '';
                                if($i == date('m')){
                                    $selected = 'selected="selected"';
                                }
                                $month = $i;
                                if($i < 10){
                                    $month = '0'.$i;
                                }
                            ?>
                            <option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $month; ?></option>
                            <?php
                            }
                            ?>
                        </select>
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
                <td style="width: 6%;"><label for="<?php echo $company; ?>"><?php echo MENU_COMPANY_MANAGEMENT; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php echo $this->Form->select($company, $companies, null, array('escape' => false, 'name' => 'company', 'empty' => TABLE_ALL)); ?>
                    </div>
                </td>
                <td style="width: 6%;"><label for="<?php echo $branch; ?>"><?php echo MENU_BRANCH; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select name="branch" id="<?php echo $branch; ?>">
                            <option value="" com=""><?php echo TABLE_ALL; ?></option>
                            <?php
                            foreach($branches AS $value){
                            ?>
                            <option value="<?php echo $value['Branch']['id']; ?>" com="<?php echo $value['Branch']['company_id']; ?>"><?php echo $value['Branch']['name']; ?></option>
                            <?php
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