<?php 
$rnd = rand();
$frmName  = "frm" . $rnd;
$dueDate  = "dueDate" . $rnd;
$dateFrom = "dateFrom" . $rnd;
$dateTo   = "dateTo" . $rnd;
$branch   = "branch" . $rnd;
$company  = "company" . $rnd;
$agent    = "agent" . $rnd;
$departureTime = "departureTime". $rnd;
$showAll  = "showAll" . $rnd;
$showUnpaid = "showUnpaid" . $rnd;
$showPaid   = "showPaid" . $rnd;
$viewBooks   = "viewBookingDate".$rnd;
$viewTravel  = "viewTravelDate".$rnd;
$btnSearchLabel = "txtBtnSearch". $rnd;
$btnSearch = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$result = "result" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $agent; ?>").chosen();
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
            searchAgency();
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
        
        // Company Change
        $("#<?php echo $company; ?>").change(function(){
            $("#<?php echo $branch; ?>").filterOptions('com', $(this).val(), '');
            $("#<?php echo $agent; ?>").filterOptions('com', $(this).val(), '');
            $("#<?php echo $agent; ?>").trigger("liszt:updated");
        });
        // Branch Change
        $("#<?php echo $branch; ?>").change(function(){
            $("#<?php echo $agent; ?>").filterOptions('ban', $(this).val(), '');
            $("#<?php echo $agent; ?>").trigger("liszt:updated");
        });
        <?php
        if(COUNT($companies) == 1){
        ?>
        $("#<?php echo $company; ?>").change();
        <?php
        }
        if(COUNT($branches) == 1){
        ?>
        $("#<?php echo $branch; ?>").change();
        <?php
        }
        ?>
    });
    
    function searchAgency(){
        var isFormValidated = $("#<?php echo $frmName; ?>").validationEngine('validate');
        if(isFormValidated){
            var link = 'agencyResult';
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
    }
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<div class="legend">
    <div class="legend_title">
        <?php echo MENU_REPORT_AGENCY; ?> <span class="btnShowHide" id="<?php echo $btnShowHide; ?>">[<?php echo TABLE_HIDE; ?>]</span>
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
                <td style="width: 8%;"><label for="<?php echo $dateTo; ?>"><?php echo REPORT_TO; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateTo; ?>" name="date_to" class="validate[required]" />
                        <input type="hidden" name="status" value="" />
                    </div>
                </td>
                <td style="width: 20%">
                    <input type="radio" name="view_date" checked="" value="1" id="<?php echo $viewBooks; ?>" style="width: auto;"><label for="<?php echo $viewBooks; ?>">Booking Date</label>
                    <input type="radio" name="view_date" value="2" id="<?php echo $viewTravel; ?>" style="width: auto;"><label for="<?php echo $viewTravel; ?>">Travel Date</label>
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
                <td style="width: 8%;"><label for="<?php echo $departureTime; ?>"><?php echo MENU_DEPARTURE_TIME; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select name="departure_time" id="<?php echo $departureTime; ?>">
                            <option value=""><?php echo TABLE_ALL; ?></option>
                            <?php
                            foreach($tDepartureTimes AS $departure){
                            ?>
                            <option><?php echo $departure['TDepartureTime']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $company; ?>"><?php echo MENU_COMPANY_MANAGEMENT; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php 
                        $comEmpty = false;
                        if(count($companies) != 1){
                            $comEmpty = TABLE_ALL;
                        }
                        echo $this->Form->select($company, $companies, null, array('escape' => false, 'name' => 'company', 'empty' => $comEmpty));
                        ?>
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $branch; ?>"><?php echo MENU_BRANCH; ?>:</label></td>
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
                <td></td>
            </tr>
        </table>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%;"><label for="<?php echo $agent; ?>"><?php echo MENU_AGENT; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select name="agent" id="<?php echo $agent; ?>" style="width: 300px;">
                            <option value=""><?php echo TABLE_ALL; ?></option>
                            <?php
                            foreach($agents AS $val){
                                $sqlCom = mysql_query("SELECT GROUP_CONCAT(company_id) FROM t_agent_companies WHERE t_agent_id = ".$val['TAgent']['id']);
                                $rowCom = mysql_fetch_array($sqlCom);
                                $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM t_agent_branches WHERE t_agent_id = ".$val['TAgent']['id']);
                                $rowBranch = mysql_fetch_array($sqlBranch);
                            ?>
                            <option com="<?php echo $rowCom[0]; ?>" ban="<?php echo $rowBranch[0]; ?>" value="<?php echo $val['TAgent']['id']; ?>"><?php echo $val['TAgent']['first_name']." ".$val['TAgent']['last_name']." ".$rowBranch[0]; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="width: 5%;"><?php echo TABLE_SHOW; ?>:</td>
                <td>
                    <input type="radio" name="show" value="" id="<?php echo $showAll; ?>" style="width: auto;" checked=""><label for="<?php echo $showAll; ?>"><?php echo TABLE_ALL; ?></label> 
                    <input type="radio" name="show" value="1" id="<?php echo $showPaid; ?>" style="width: auto;"><label for="<?php echo $showPaid; ?>">Paid</label>
                    <input type="radio" name="show" value="0" id="<?php echo $showUnpaid; ?>" style="width: auto;"><label for="<?php echo $showUnpaid; ?>">Unpaid</label>
                </td>
            </tr>
        </table>
    </div>
</div>
</form>
<div id="<?php echo $result; ?>"></div>