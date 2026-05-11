<?php 
$rnd = rand();
$frmName  = "frm" . $rnd;
$dueDate  = "dueDate" . $rnd;
$dateFrom = "dateFrom" . $rnd;
$dateTo   = "dateTo" . $rnd;
$company  = "company" . $rnd;
$branch   = "branch" . $rnd;
$userLabel  = "user" . $rnd;
$viewBy     = "viewBy". $rnd;
$mainBranch = "mainBranch". $rnd;
$btnSearchLabel = "txtBtnSearch". $rnd;
$btnSearch   = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$result = "result" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Hide Branch
        // $("#<?php echo $branch; ?>").filterOptions('com', '0', '');
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
//                setTimeout(function(){
//                    $("#<?php echo $branch; ?>").change();
//                }, 500);
            }
        });
        $("#<?php echo $dueDate; ?>").change(function(){
            var date = getDateByDateRange($(this).val());
            $('#<?php echo $dateTo; ?>').datepicker( "option", "minDate", date[0]);
            $('#<?php echo $dateFrom; ?>').datepicker("setDate", date[0]);
            $('#<?php echo $dateTo; ?>').datepicker("setDate", date[1]);
//            $("#<?php echo $branch; ?>").change();
        });
        $("#<?php echo $btnSearch; ?>").click(function(){
            var viewBy = $("#<?php echo $viewBy; ?>").val();
            var isFormValidated = $("#<?php echo $frmName; ?>").validationEngine('validate');
            var link = '';
            if(viewBy == 1){
                link = 'collectByUserResult';
            } else if(viewBy == 2) {
                if($("#<?php echo $mainBranch; ?>").val() == ""){ // Filter All Main Branch
                    link = 'collectByUserSummaryAll';
                } else {
                    link = 'collectByUserSummary';
                }
            } else if(viewBy == 3) {
                link = 'collectByUserDeparture';
            }
            if(isFormValidated){
                // $("#collectByUserCompany").val($("#<?php echo $company; ?>").val());
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
        $("#<?php echo $userLabel; ?>, #<?php echo $mainBranch; ?>, #<?php echo $branch; ?>").chosen();
        $("#<?php echo $userLabel; ?>").change(function(){
            if($(this).val() == ''){
                $(".collectByUserView").show();
            } else {
                $(".collectByUserView").hide();
                $("#<?php echo $viewBy; ?>").find("option[value='1']").attr('selected', true);
            }
        });
        // Change Branch
//        $("#<?php echo $branch; ?>").change(function(){
//            var branchId     = $(this).val();
//            var mainBranchId = $("#<?php echo $mainBranch; ?>").val();
//            var from = $("#<?php echo $dateFrom; ?>").val().toString().split("/")[2]+"-"+$("#<?php echo $dateFrom; ?>").val().toString().split("/")[1]+"-"+$("#<?php echo $dateFrom; ?>").val().toString().split("/")[0];
//            var to   = $("#<?php echo $dateTo; ?>").val().toString().split("/")[2]+"-"+$("#<?php echo $dateTo; ?>").val().toString().split("/")[1]+"-"+$("#<?php echo $dateTo; ?>").val().toString().split("/")[0];
//            if(branchId != '' && from != '' && to != ''){
//                $.ajax({
//                    type: "GET",
//                    url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/getUserByBranch/"+branchId+"/"+mainBranchId,
//                    data: "from="+from+"&to="+to,
//                    beforeSend: function(){
//                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
//                        $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").attr("disabled", true);
//                        $("#<?php echo $btnSearch; ?>").hide();
//                        $("#<?php echo $userLabel; ?>").html('<option value=""><?php echo TABLE_ALL; ?></option>');
//                        $("#<?php echo $userLabel; ?>").trigger("liszt:updated");
//                    },
//                    success: function(result){
//                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
//                        $("#<?php echo $btnSearch; ?>").show();
//                        $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").attr("disabled", false);
//                        $("#<?php echo $userLabel; ?>").html(result);
//                        $("#<?php echo $userLabel; ?>").trigger("liszt:updated");
//                    }
//                });
//            }
//        });
        // Company Change
        // $("#<?php echo $company; ?>").change(function(){
        //     if($(this).val() != ''){
        //         $("#<?php echo $branch; ?>").filterOptions('com', $(this).val(), '');
        //     } else {
        //         $("#<?php echo $branch; ?>").filterOptions('com', $(this).val(), '');
        //     }
        // });
        <?php
        // if(COUNT($companies) == 1){
        ?>
        // $("#<?php echo $company; ?>").change();
        <?php
        // }
        ?>
    });
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<!-- <input type="hidden" name="company" id="collectByUserCompany" /> -->
<div class="legend">
    <div class="legend_title">
        <?php echo REPORT_COLLECT_BY_USER; ?> <span class="btnShowHide" id="<?php echo $btnShowHide; ?>">[<?php echo TABLE_HIDE; ?>]</span>
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
                <td style="width: 25%;">
                    <div class="inputContainer">
                        <?php 
                        // $comEmpty = false;
                        // if(count($companies) != 1){
                        //     $comEmpty = TABLE_ALL;
                        // }
                        // echo $this->Form->select($company, $companies, null, array('escape' => false, 'data-placeholder' => $comEmpty, 'width' => '200', 'multiple' => true)); 
                        ?>
                        <select name="company" id="<?php echo $company; ?>" style="width: 200px;">
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
                <td style="width: 8%;"><label for="<?php echo $branch; ?>"><?php echo TABLE_DESTINATION_FROM; ?>:</label></td>
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
                <td style="width: 8%;"><label for="<?php echo $userLabel; ?>"><?php echo USER_USER_NAME; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select name="user_select" id="<?php echo $userLabel; ?>">
                            <option value=""><?php echo TABLE_ALL; ?></option>
                            <?php
                            foreach($users AS $userList){
                            ?>
                            <option value="<?php echo $userList['User']['id']; ?>"><?php echo $userList['User']['first_name'].' '.$userList['User']['last_name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="width: 8%;" class="collectByUserView"><label for="<?php echo $viewBy; ?>"><?php echo TABLE_VIEW_BY; ?>:</label></td>
                <td style="width: 25%;" class="collectByUserView">
                    <div class="inputContainer">
                        <select id="<?php echo $viewBy; ?>">
                            <option value="1"><?php echo 'Detail'; ?></option>
                            <option value="2" selected=""><?php echo 'Summary'; ?></option>
                            <!-- <option value="3"><?php echo 'Summary By Departure'; ?></option> -->
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