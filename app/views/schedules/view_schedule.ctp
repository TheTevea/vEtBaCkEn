<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableSchedule;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#scheduleDestinationFrom, #scheduleDestinationTo").chosen({width: 150});
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableSchedule = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewScheduleAjax/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $(".btnFindBoatSchedule").find('span').text('<?php echo GENERAL_SEARCH; ?>').attr('disabled', false);
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?>_filter").hide();
                // Print
                $(".btnPrintSchedule").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    $("#scheduleDate").datepicker("option", "dateFormat", "yy-mm-dd");
                    var date = $("#scheduleDate").val();
                    $("#scheduleDate").datepicker("option", "dateFormat", "dd/mm/yy");
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printSchedule/"+id+"/"+date,
                        beforeSend: function(){
                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        },
                        success: function(printInvoiceResult){
                            w=window.open();
                            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                            w.document.write(printInvoiceResult);
                            w.document.close();
                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                        }
                    });
                });
                // View
                $(".btnViewSchedule").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    $("#scheduleDate").datepicker("option", "dateFormat", "yy-mm-dd");
                    var date = $("#scheduleDate").val();
                    $("#scheduleDate").datepicker("option", "dateFormat", "dd/mm/yy");
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/viewPlanSeat/" + id+"/"+date);
                });
                // Block
                $(".btnBlockSeatSchedule").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    $("#scheduleDate").datepicker("option", "dateFormat", "yy-mm-dd");
                    var date = $("#scheduleDate").val();
                    $("#scheduleDate").datepicker("option", "dateFormat", "dd/mm/yy");
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/blockSchedule/" + id+"/"+date);
                });
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1 ]
            }],
            "aaSorting": [[ 3, "asc" ]]
        });
        $("#scheduleBranch").filterOptions('com', '0', '');
        // Company Change
        $("#scheduleCompany").change(function(){
            var companyId = $(this).val();
            $("#scheduleBranch").filterOptions('com', companyId, '');
        });
        // Branch Change
        $("#scheduleBranch").change(function(){
            var branchId = $(this).val();
            $("#scheduleJourneyType").filterOptions('com', branchId, '');
        });
        // Journey Type Change
        $("#scheduleDestinationFrom, #scheduleDestinationTo").change(function(){
            getDepartureSchedule();
        });
        // Date
        $("#scheduleDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        });
        // Find
        $(".btnFindBoatSchedule").click(function(event){
            var routeCode = "all";
            if($("#scheduleRouteCode").val() != ""){
                routeCode = $("#scheduleRouteCode").val().replace(/:/g, "*");
                routeCode = routeCode.replace(/\s/g, ']');
            }
            event.preventDefault();
            $("#scheduleDate").datepicker("option", "dateFormat", "yy-mm-dd");
            $(this).find('span').text('<?php echo ACTION_LOADING; ?>').attr('disabled', true);
            var Tablesetting = oTableSchedule.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/viewScheduleAjax/"+$("#scheduleCompany").val()+"/"+$("#scheduleBranch").val()+"/"+$("#scheduleDestinationFrom").val()+"/"+$("#scheduleDestinationTo").val()+"/"+$("#scheduleDepartureTime").val()+"/"+routeCode+"/"+$("#scheduleDate").val()+"/"+$("#scheduleStatus").val();
            oCache.iCacheLower = -1;
            oTableSchedule.fnDraw(false);
            $("#scheduleDate").datepicker("option", "dateFormat", "dd/mm/yy");
        });
    });
    
    function getDepartureSchedule(){
        if($("#scheduleDestinationFrom").val() != '' && $("#scheduleDestinationTo").val() != ''){
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/'; ?>schedules/getDepartureTime/"+$("#scheduleCompany").val()+"/"+$("#scheduleBranch").val()+"/"+$("#scheduleDestinationFrom").val()+"/"+$("#scheduleDestinationTo").val(),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    $(".btnFindBoatSchedule").attr('disabled', true);
                },
                success: function(departure){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $(".btnFindBoatSchedule").attr('disabled', false);
                    $("#scheduleDepartureTime").html(departure);
                }
            });
        } else {
            $("#scheduleDepartureTime").html('<option value="all"><?php echo TABLE_ALL; ?></option>');
        }
    }
</script>
<div class="leftPanel">
    <div style="width: 93%; float: left;">
        <table cellpadding="0" cellspacing="0" style="width: 100%;">
            <tr>
                <td style="width: 80px;"><label for="scheduleCompany"><?php echo MENU_COMPANY_MANAGEMENT; ?></label> :</td>
                <td style="width: 155px;">
                    <select id="scheduleCompany" style="width: 150px; height: 30px; font-size: 12px;">
                        <option value="all"><?php echo INPUT_SELECT; ?></option>
                        <?php
                        foreach($companies AS $company){
                        ?>
                        <option value="<?php echo $company['Company']['id']; ?>"><?php echo $company['Company']['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
                <td style="width: 80px;"><label for="scheduleBranch"><?php echo MENU_BRANCH; ?></label> :</td>
                <td style="width: 155px;">
                    <select id="scheduleBranch" style="width: 150px; height: 30px; font-size: 12px;">
                        <option value="all"><?php echo INPUT_SELECT; ?></option>
                        <?php
                        foreach($branches AS $branch){
                        ?>
                        <option com="<?php echo $branch['Branch']['company_id']; ?>" value="<?php echo $branch['Branch']['id']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
                <td style="width: 50px;"><label for="scheduleDestinationFrom"><?php echo REPORT_FROM; ?></label> :</td>
                <td style="width: 155px;">
                    <select id="scheduleDestinationFrom" style="width: 150px; height: 30px; font-size: 12px;">
                        <option value="all"><?php echo INPUT_SELECT; ?></option>
                        <?php
                        foreach($tDestinations AS $tDestination){
                        ?>
                        <option value="<?php echo $tDestination['TDestination']['id']; ?>"><?php echo $tDestination['TDestination']['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
                <td style="width: 50px;"><label for="scheduleDestinationTo"><?php echo REPORT_TO; ?></label> :</td>
                <td style="width: 155px;">
                    <select id="scheduleDestinationTo" style="width: 150px; height: 30px; font-size: 12px;">
                        <option value="all"><?php echo INPUT_SELECT; ?></option>
                        <?php
                        foreach($tDestinations AS $tDestination){
                        ?>
                        <option value="<?php echo $tDestination['TDestination']['id']; ?>"><?php echo $tDestination['TDestination']['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
                <td style="width: 80px;"><label for="scheduleDepartureTime" style="margin-left: 5px;"><?php echo TABLE_DEPARTURE; ?></label> :</td>
                <td style="width: 140px;">
                    <select id="scheduleDepartureTime" style="width: 110px; height: 30px; font-size: 12px;">
                        <option value="all"><?php echo TABLE_ALL; ?></option>
                    </select>
                </td>
                <td style="width: 50px;"><label for="scheduleDate" style="margin-left: 5px;"><?php echo TABLE_DATE; ?></label> :</td>
                <td style="width: 100px;">
                    <input id="scheduleDate" style="width: 90px; height: 25px; font-size: 12px;" value="<?php echo date("d/m/Y"); ?>" />
                </td>
                <td style="width: 50px;"><label for="scheduleStatus" style="margin-left: 5px;"><?php echo "Status"; ?></label> :</td>
                <td style="width: 95px;">
                    <select id="scheduleStatus" style="width: 88px; height: 30px; font-size: 12px;">
                        <option value="all"><?php echo TABLE_ALL; ?></option>
                        <option value="1" selected=""><?php echo "Active"; ?></option>
                        <option value="2"><?php echo "Inactive"; ?></option>
                    </select>
                </td>
                <td style="width: 120px;">
                    <input id="scheduleRouteCode" placeholder="Route Code" style="width: 100px; height: 25px; font-size: 12px;" value="" />
                </td>
            </tr>
        </table>
    </div>
    <!-- Button Find -->
    <div class="buttons" style="float: left;">
        <a href="#" class="positive btnFindBoatSchedule">
            <img src="<?php echo $this->webroot; ?>img/button/search.png" alt=""/>
            <span><?php echo GENERAL_SEARCH; ?></span>
        </a>
    </div>
    <div style="clear: both;"></div>
    <br />
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 220px !important;"><?php echo MENU_BRANCH; ?></th>
                    <th style="width: 130px !important;"><?php echo TABLE_TRAVEL_DATE; ?></th>
                    <th><?php echo GENERAL_DESCRIPTION; ?></th>
                    <th style="width: 140px !important;"><?php echo TABLE_DEPARTURE; ?></th>
                    <th style="width: 180px !important;"><?php echo MENU_TRANSPORTATION_TYPE; ?></th>
                    <th style="width: 140px !important;"><?php echo "Route Code"; ?></th>
                    <th style="width: 160px !important;"><?php echo "Seats"; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_TYPE; ?></th>
                    <th style="width: 160px !important;"><?php echo TABLE_CUSTOMER; ?></th>
                    <th style="width: 160px !important;"><?php echo MENU_JOURNEY_BUS; ?></th>
                    <th style="width: 160px !important;"><?php echo "Scanned"; ?></th>
                    <th style="width: 160px !important;"><?php echo TABLE_STATUS; ?></th>
                    <th style="width: 150px !important;"><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="14" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
</div>
<div class="rightPanel"></div>