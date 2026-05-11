<?php 
include('includes/function.php');
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    var rowTableAgent       =  $("#rowListAgent");
    var rowIndexJourney     = 0;
    var rowTableRouteMulti  = $("#rowListRouteMulti");
    var rowTableTransit     = $("#rowListTransit");
    var rowTableTranDetail  = $("#rowListTransitDetail");
    var rowBoardingPoint    = $("#rowListBoardingPoint");
    var rowDropOff          = $("#rowListDropOff");
    var fieldJourneyRequire = ['TJourneyTDestinationFromId', 'TJourneyTDestinationToId', 'TJourneyTTransportationTypeId', 'TJourneyTBoatId'];
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#rowListAgent, #rowListRouteMulti, #rowListTransit, #rowListTransitDetail, #rowListBoardingPoint, #rowListDropOff").remove();
        $(".journeySelectChz").chosen({width: 260});
        $(".interger").autoNumeric({mDec: 0, aSep: ','});
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        // Hide Branch
        $("#TJourneyBranchId").filterOptions('com', '<?php echo $this->data['TJourney']['company_id']; ?>', '<?php echo $this->data['TJourney']['branch_id']; ?>');
        $("#TJourneyBranchId").trigger("chosen:updated");
        getSymbolTJourney();
        $("#TJourneyEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TJourneyEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                if(checkRequireField(fieldJourneyRequire) == false){
                    alertSelectRequireField();
                    $(".btnSaveTJourney").removeAttr('disabled');
                    return false;
                }
                // Check Boarding and Drop Off Point
                var checkLocPoint = true;
                $(".JourneyBoardingPoint").each(function(){
                    if($(this).val() == ""){
                        checkLocPoint = false;
                    }
                });
                $(".JourneyDropOff").each(function(){
                    if($(this).val() == ""){
                        checkLocPoint = false;
                    }
                });
                if(checkLocPoint == false){
                    alertSelectRequireField();
                    $(".btnSaveTJourney").removeAttr('disabled');
                    return false;
                }
                $(".float, .interger").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                $("#TJourneyBlockStart, #TJourneyBlockEnd").datepicker("option", "dateFormat", "yy-mm-dd");
                $("#TJourneyActiveStart, #TJourneyActiveEnd").datepicker("option", "dateFormat", "yy-mm-dd");
                $("#TJourneyDiscountFrom, #TJourneyDiscountTo").datepicker("option", "dateFormat", "yy-mm-dd");
                $("#TJourneyPricePeriodFrom, #TJourneyPricePeriodTo").datepicker("option", "dateFormat", "yy-mm-dd");
                $("#TJourneyPricePeriodFromInternal, #TJourneyPricePeriodToInternal").datepicker("option", "dateFormat", "yy-mm-dd");
                $("#TJourneyPromotionDisStart, #TJourneyPromotionDisEnd").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTJourney").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackTJourney").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Journey', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Journey', 'Add', 1, '');
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                }
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        $(".btnBackTJourney").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTJourney.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        $("#TJourneyCompanyId").change(function(){
            var obj = $(this);
            $("#TJourneyBranchId").filterOptions('com', obj.val(), '');
            $("#TJourneyBranchId").trigger("chosen:updated");
        });
        
        $("#TJourneyBranchId").change(function(){
            getSymbolTJourney();
        });
        
        $("#TJourneyAgentPriceAmount, #TJourneyAgentRoundPrice, #TJourneyUnitPrice, #TJourneyMembership, #TJourneyRoundPrice, #TJourneyRoundPriceVip").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $("#TJourneyAgentPriceAmount, #TJourneyAgentRoundPrice, #TJourneyUnitPrice, #TJourneyMembership, #TJourneyRoundPrice, #TJourneyRoundPriceVip").blur(function(){
            if($(this).val() == ''){
                $(this).val('0');
            }
        });
        
        $("#searchAgency").autocomplete("<?php echo $this->base . "/t_journeys/searchAgent"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[1];
            }
        }).result(function(event, value){
            var agentId   = value.toString().split(".*")[0];
            var agentName = value.toString().split(".*")[1];
            addToAgentList(agentId, agentName);
        });
        
        // Direction Click
        $("#JourneyDirection").unbind('click').click(function(){
            $("#divRouteMultiJourney").hide();
            $("#rowRouteMultiList").html('');
            $("#divTransitJourney").hide();
            $("#rowTransitList").html('');
            // Price Reanonly (False)
            $("#TJourneyUnitPrice, #TJourneyMembership, #TJourneyRoundPrice, #TJourneyRoundPriceVip").attr('readonly', false).val('');
            // Allow Outside
            $("#dvAllowAccessOutside").show();
        });
        
        $("#JourneyTransit").unbind('click').click(function(){
            $("#divTransitJourney").show();
            $("#rowTransitList").html('');
            $("#divRouteMultiJourney").hide();
            $("#rowRouteMultiList").html('');
            cloneTransit();
            // Price Reanonly
            $("#TJourneyUnitPrice, #TJourneyMembership, #TJourneyRoundPrice, #TJourneyRoundPriceVip").attr('readonly', true).val('');
            // Allow Outside
            $("#dvAllowAccessOutside").hide();
            $("input[name='data[TJourney][allow_access]']").val("0");
            $("#TJourneyAllowAPI, #TJourneyAllowOnline").removeAttr("checked");
        });
        
        $("#JourneyDirectionMultiRoute").unbind('click').click(function(){
            $("#divRouteMultiJourney").show();
            $("#rowRouteMultiList").html('');
            $("#divTransitJourney").hide();
            $("#rowTransitList").html('');
            cloneRouteMulti();
            // Price Reanonly (False)
            $("#TJourneyUnitPrice, #TJourneyMembership, #TJourneyRoundPrice, #TJourneyRoundPriceVip").attr('readonly', false).val('');
            // Allow Outside
            $("#dvAllowAccessOutside").show();
        });
        
        var dates = $("#TJourneyBlockStart, #TJourneyBlockEnd").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "TJourneyBlockStart" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
            }
        });
        $("#TJourneyClearBlockDate").click(function(){
            $("#TJourneyBlockStart, #TJourneyBlockEnd").val('');
        });
        
        var dateActive = $("#TJourneyActiveStart, #TJourneyActiveEnd").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "TJourneyActiveStart" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dateActive.not( this ).datepicker( "option", option, date );
            }
        });
        
        $("#TJourneyClearActiveDate").click(function(){
            $("#TJourneyActiveStart, #TJourneyActiveEnd").val('');
        });
        // Departure Time, Arrival Time
        $("#TJourneyTDepartureTimeHour, #TJourneyArrivalHour").unbind('change').change(function(){
            if($("#TJourneyTDepartureTimeHour").find("option:selected").val() != "" && $("#TJourneyArrivalHour").find("option:selected").val() != ""){
                var departureHour = replaceNum($("#TJourneyTDepartureTimeHour").find("option:selected").val());
                var arrivalHour   = replaceNum($("#TJourneyArrivalHour").find("option:selected").val());
                var durationHour  = 0;
                if(arrivalHour > departureHour){
                    durationHour = (arrivalHour - departureHour);
                } else {
                    var remain = (arrivalHour - departureHour);
                    durationHour = remain + 24;
                }
                var label = "0"+durationHour;
                if(durationHour > 10){
                    label = durationHour;
                }
                $("#TJourneyDurationHour").find("option[value='"+label+"']").attr("selected", true);
            } else {
                $("#TJourneyDurationHour").find("option[value='']").attr("selected", true);
            }
        });
        
        $("#TJourneyTDepartureTimeMinute, #TJourneyArrivalMinute").unbind('change').change(function(){
            if($("#TJourneyTDepartureTimeMinute").find("option:selected").val() != "" && $("#TJourneyArrivalMinute").find("option:selected").val() != ""){
                var departureMin = replaceNum($("#TJourneyTDepartureTimeMinute").find("option:selected").val());
                var arrivalMin   = replaceNum($("#TJourneyArrivalMinute").find("option:selected").val());
                var durationMin  = 0;
                var reduceHour   = 0;
                if(arrivalMin >= departureMin){
                    durationMin = (arrivalMin - departureMin);
                } else {
                    var remain  = (arrivalMin - departureMin);
                    durationMin = remain + 60;
                    reduceHour  = 1;
                }
                var label = "0"+durationMin;
                if(durationMin > 10){
                    label = durationMin;
                }
                $("#TJourneyDurationMinute").find("option[value='"+label+"']").attr("selected", true);
                // Update Hour
                $("#TJourneyTDepartureTimeHour").change();
                if(reduceHour == 1 && $("#TJourneyDurationHour").val() != ""){
                    var durationHour = replaceNum($("#TJourneyDurationHour").find("option:selected").val()) - 1;
                    var label = "0"+durationHour;
                    if(durationHour > 10){
                        label = durationHour;
                    }
                    $("#TJourneyDurationHour").find("option[value='"+label+"']").attr("selected", true);
                }
            } else {
                $("#TJourneyTDepartureTimeHour").change();
                $("#TJourneyDurationMinute").find("option[value='']").attr("selected", true);
            }
        });
        
        // Change Duration
        $("#TJourneyDurationHour").unbind("change").change(function(){
            if($("#TJourneyTDepartureTimeHour").find("option:selected").val() != ""){
                var durationHour  = replaceNum($("#TJourneyDurationHour").find("option:selected").val());
                var departureHour = replaceNum($("#TJourneyTDepartureTimeHour").find("option:selected").val());
                var arrivalHour   = departureHour + durationHour;
                if(arrivalHour > 24){
                    arrivalHour = arrivalHour - 24;
                }
                var label = "0"+arrivalHour;
                if(arrivalHour > 10){
                    label = arrivalHour;
                }
                $("#TJourneyArrivalHour").find("option[value='"+label+"']").attr("selected", true);
            }
        });
        
        $("#TJourneyDurationMinute").unbind("change").change(function(){
            if($("#TJourneyTDepartureTimeMinute").find("option:selected").val() != ""){
                var durationHour  = replaceNum($("#TJourneyDurationMinute").find("option:selected").val());
                var departureHour = replaceNum($("#TJourneyTDepartureTimeMinute").find("option:selected").val());
                var arrivalHour   = departureHour + durationHour;
                var label = "0"+arrivalHour;
                if(arrivalHour > 10){
                    label = arrivalHour;
                }
                $("#TJourneyArrivalMinute").find("option[value='"+label+"']").attr("selected", true);
            }
        });
        
        // Allow Access
        $(".TJourneyAllowAccess").unbind("click").click(function(){
            var api = false;
            var online = false;
            var allowAccess = 0;
            if($("#TJourneyAllowAPI").is(':checked')){
                api = true;
            }
            if($("#TJourneyAllowOnline").is(':checked')){
                online = true;
            }
            if(api == false && online == false){
                allowAccess = 0;
            } else if(api == true && online == true){
                allowAccess = 3;
            } else if(api == true){
                allowAccess = 1;
            } else if(online == true){
                allowAccess = 2;
            }
            $("input[name='data[TJourney][allow_access]']").val(allowAccess);
        });

        var dates = $("#TJourneyDiscountFrom, #TJourneyDiscountTo").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                $("#TJourneyDiscountFrom, #TJourneyDiscountTo, #TJourneyDiscount").addClass("validate[required]");
                var option = this.id == "TJourneyDiscountFrom" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
            }
        });

        $("#tJourneyClearPromotion").unbind("click").click(function(){
            $("#TJourneyDiscountFrom, #TJourneyDiscountTo").val("");
            $("#TJourneyDiscount").val(0);
            $("#TJourneyDiscountFrom, #TJourneyDiscountTo, #TJourneyDiscount").removeClass("validate[required]");
        });

        $("#TJourneyDiscount").unbind("focus").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val("");
            }
        });

        $("#TJourneyDiscount").unbind("blur").blur(function(){
            if(replaceNum($(this).val()) > 100){
                $(this).val(100);
            } else if($(this).val() == ""){
                $(this).val(0);
            }
        });

        var datesPeriod = $("#TJourneyPricePeriodFrom, #TJourneyPricePeriodTo").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                $("#TJourneyPricePeriodFrom, #TJourneyPricePeriodTo, #TJourneyPricePeriodPrice, #TJourneyPricePeriodMembership, #TJourneyPricePeriodAgencyPrice, #TJourneyPricePeriodRoundPrice, #TJourneyPricePeriodRoundPriceVip, #TJourneyPricePeriodAgencyRoundPrice").addClass("validate[required]");
                var option = this.id == "TJourneyPricePeriodFrom" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                datesPeriod.not( this ).datepicker( "option", option, date );
            }
        });

        $("#tJourneyClearPricePeriod").unbind("click").click(function(){
            $("#TJourneyPricePeriodFrom, #TJourneyPricePeriodTo").val("");
            $("#TJourneyPricePeriodPrice, #TJourneyPricePeriodMembership, #TJourneyPricePeriodAgencyPrice, #TJourneyPricePeriodRoundPrice, #TJourneyPricePeriodRoundPriceVip, #TJourneyPricePeriodAgencyRoundPrice").val(0);
            $("#TJourneyPricePeriodFrom, #TJourneyPricePeriodTo, #TJourneyPricePeriodPrice, #TJourneyPricePeriodMembership, #TJourneyPricePeriodAgencyPrice, #TJourneyPricePeriodRoundPrice, #TJourneyPricePeriodRoundPriceVip, #TJourneyPricePeriodAgencyRoundPrice").removeClass("validate[required]");
        });

        $("#TJourneyPricePeriodPrice, #TJourneyPricePeriodMembership, #TJourneyPricePeriodAgencyPrice, #TJourneyPricePeriodRoundPrice, #TJourneyPricePeriodRoundPriceVip, #TJourneyPricePeriodAgencyRoundPrice").unbind("focus").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val("");
            }
        });

        $("#TJourneyPricePeriodPrice, #TJourneyPricePeriodMembership, #TJourneyPricePeriodAgencyPrice, #TJourneyPricePeriodRoundPrice, #TJourneyPricePeriodRoundPriceVip, #TJourneyPricePeriodAgencyRoundPrice").unbind("blur").blur(function(){
            if($(this).val() == ""){
                $(this).val(0);
            }
        });


        var datesPeriodInternal = $("#TJourneyPricePeriodFromInternal, #TJourneyPricePeriodToInternal").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                $("#TJourneyPricePeriodFromInternal, #TJourneyPricePeriodToInternal, #TJourneyPricePeriodPriceInternal, #TJourneyPricePeriodRoundPriceInternal").addClass("validate[required]");
                var option = this.id == "TJourneyPricePeriodFromInternal" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                datesPeriodInternal.not( this ).datepicker( "option", option, date );
            }
        });

        $("#tJourneyClearPricePeriodInternal").unbind("click").click(function(){
            $("#TJourneyPricePeriodFromInternal, #TJourneyPricePeriodToInternal").val("");
            $("#TJourneyPricePeriodPriceInternal, #TJourneyPricePeriodRoundPriceInternal").val(0);
            $("#TJourneyPricePeriodFromInternal, #TJourneyPricePeriodToInternal, #TJourneyPricePeriodPriceInternal, #TJourneyPricePeriodRoundPriceInternal").removeClass("validate[required]");
        });

        $("#TJourneyPricePeriodPriceInternal, #TJourneyPricePeriodRoundPriceInternal").unbind("focus").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val("");
            }
        });

        $("#TJourneyPricePeriodPriceInternal, #TJourneyPricePeriodRoundPriceInternal").unbind("blur").blur(function(){
            if($(this).val() == ""){
                $(this).val(0);
            }
        });
        
        // Check Event Transit
        <?php 
        if($this->data['TJourney']['type'] != 1){ 
            if(!empty($tJourneyTransits)){
                if($this->data['TJourney']['type'] == 3){
        ?>
            eventKeyRouteMulti();
        <?php 
                } else {
        ?>
            eventKeyTransit();
            eventKeyTransitDetail();
        <?php
                }
            } 
        } 
        ?>
        keyEventAgent();
        // Boarding Point and Drop Off
        <?php
        $sqlDropOff = mysql_query("SELECT * FROM t_journey_drop_offs WHERE t_journey_id = ".$this->data['TJourney']['id']." ORDER BY t_journey_drop_offs.time ASC");
        if(mysql_num_rows($sqlDropOff)){
        ?>
        eventKeyDropOff();
        <?php
        } else {
        ?>
        cloneDropOff();
        <?php    
        }
        $sqlBoardingPoint = mysql_query("SELECT * FROM t_journey_boarding_points WHERE t_journey_id = ".$this->data['TJourney']['id']." ORDER BY t_journey_boarding_points.time ASC");
        if(mysql_num_rows($sqlBoardingPoint)){
        ?>
        eventKeyBoardingPoint();
        <?php    
        } else {
        ?>
        cloneBoardingPoint();
        <?php    
        }
        ?>
    });
    
    function getSymbolTJourney(){
        var symbol = $("#TJourneyBranchId").find("option:selected").attr("symbol");
        $(".TJourneySymbolCurrency").html(symbol);
    }
    
    function cloneRouteMulti(){
        rowIndexJourney = Math.floor((Math.random() * 100000) + 1);
        var tr = rowTableRouteMulti.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .JourneyRouteMulti").attr("id", "JourneyRouteMulti"+rowIndexJourney);
        tr.find("td .JourneyRouteMultiDeparture").attr("id", "JourneyRouteMultiDeparture"+rowIndexJourney);
        $("#rowRouteMultiList").append(tr);
        var LenTr = parseInt($(".rowListRouteMulti").length);
        if(LenTr == 1){
            $("#rowRouteMultiList").find("tr:eq(0)").find(".btnRemoveRowRouteMulti").hide();
            $("#rowRouteMultiList").find("tr:eq(0)").find(".btnAddRowRouteMulti").show();
        } else {
            $("#rowRouteMultiList").find("tr:eq("+(LenTr - 1)+")").find(".btnRemoveRowRouteMulti").show();
            $("#rowRouteMultiList").find("tr:eq("+(LenTr - 1)+")").find(".btnAddRowRouteMulti").show();
        }
        eventKeyRouteMulti();
    }

    function cloneTransit(){
        rowIndexJourney = Math.floor((Math.random() * 100000) + 1);
        var tr = rowTableTransit.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .DestinationTransitJourney").attr("id", "DestinationTransitJourney"+rowIndexJourney);
        $("#rowTransitList").append(tr);
        var LenTr = parseInt($(".rowListTransit").length);
        if(LenTr == 1){
            $("#rowTransitList").find("tr:eq(0)").find(".btnRemoveRowTransit").hide();
            $("#rowTransitList").find("tr:eq(0)").find(".btnAddRowTransit").show();
        } else {
            $("#rowTransitList").find("tr:eq("+(LenTr - 1)+")").find(".btnRemoveRowTransit").show();
            $("#rowTransitList").find("tr:eq("+(LenTr - 1)+")").find(".btnAddRowTransit").show();
        }
        eventKeyTransit();
        cloneTransitDetail(tr);
    }

    function cloneTransitDetail(obj){
        // Journey Detail
        var rowJourneyDetail = Math.floor((Math.random() * 100000) + 1);
        var trJourney  = rowTableTranDetail.clone(true);
        var journeyOpt = obj.find(".DefaultTransitJourney").html();
        trJourney.removeAttr("style").removeAttr("id");
        trJourney.find("td .JourneyTransit").attr("id", "JourneyTransit"+rowJourneyDetail).html(journeyOpt);
        trJourney.find("td .JourneyTransitDeparture").attr("id", "JourneyTransitDeparture"+rowJourneyDetail);
        trJourney.find("td .JourneyTransitDate").attr("id", "JourneyTransitDate"+rowJourneyDetail);
        obj.find(".rowTransitListDetail").append(trJourney);
        $(".JourneyTransit").trigger("chosen:updated");
        var LenTr = parseInt($(".rowListTransitDetail").length);
        if(LenTr == 1){
            obj.find(".rowTransitListDetail").find("tr:eq(0)").find(".btnRemoveRowScheduleTransit").hide();
            obj.find(".rowTransitListDetail").find("tr:eq(0)").find(".btnAddRowScheduleTransit").show();
        } else {
            obj.find(".rowTransitListDetail").find("tr:eq("+(LenTr - 1)+")").find(".btnRemoveRowScheduleTransit").show();
            obj.find(".rowTransitListDetail").find("tr:eq("+(LenTr - 1)+")").find(".btnAddRowScheduleTransit").show();
        }
        eventKeyTransitDetail();
    }
    
    function addToAgentList(agentId, agentName){
        rowIndexJourney = Math.floor((Math.random() * 100000) + 1);
        var tr = rowTableAgent.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("input[name='agent_id[]']").val(agentId);
        tr.find(".agentName").attr("id", "agentName_"+rowIndexJourney).val(agentName).attr('readonly', true);
        tr.find("input[name='agent_amount[]']").attr("id", "agentAmount_"+rowIndexJourney).val(0);
        tr.find("input[name='agent_percent[]']").attr("id", "agentPercent_"+rowIndexJourney).val(0);
        $("#rowAgnetList").append(tr);
        keyEventAgent();
        $("#searchAgency").val('');
    }
    
    function cloneBoardingPoint(){
        rowIndexJourney = Math.floor((Math.random() * 100000) + 1);
        var tr = rowBoardingPoint.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .JourneyBoardingPoint").attr("id", "JourneyBoardingPoint"+rowIndexJourney);
        tr.find("td .JourneyBoardingPointHour").attr("id", "JourneyBoardingPointHour"+rowIndexJourney);
        tr.find("td .JourneyBoardingPointMinute").attr("id", "JourneyBoardingPointMinute"+rowIndexJourney);
        $("#rowBoardingPointList").append(tr);
        var LenTr = parseInt($(".rowListBoardingPoint").length);
        if(LenTr == 1){
            $("#rowBoardingPointList").find("tr:eq(0)").find(".btnRemoveRowBoardingPoint").hide();
            $("#rowBoardingPointList").find("tr:eq(0)").find(".btnAddRowBoardingPoint").show();
        } else {
            $("#rowBoardingPointList").find("tr:eq("+(LenTr - 1)+")").find(".btnRemoveRowBoardingPoint").show();
            $("#rowBoardingPointList").find("tr:eq("+(LenTr - 1)+")").find(".btnAddRowBoardingPoint").show();
        }
        eventKeyBoardingPoint();
    }
    
    function cloneDropOff(){
        rowIndexJourney = Math.floor((Math.random() * 100000) + 1);
        var tr = rowDropOff.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .JourneyDropOff").attr("id", "JourneyDropOff"+rowIndexJourney);
        tr.find("td .JourneyDropOffHour").attr("id", "JourneyDropOffHour"+rowIndexJourney);
        tr.find("td .JourneyDropOffMinute").attr("id", "JourneyDropOffMinute"+rowIndexJourney);
        $("#rowDropOffList").append(tr);
        var LenTr = parseInt($(".rowListDropOff").length);
        if(LenTr == 1){
            $("#rowDropOffList").find("tr:eq(0)").find(".btnRemoveRowDropOff").hide();
            $("#rowDropOffList").find("tr:eq(0)").find(".btnAddRowDropOff").show();
        } else {
            $("#rowDropOffList").find("tr:eq("+(LenTr - 1)+")").find(".btnRemoveRowDropOff").show();
            $("#rowDropOffList").find("tr:eq("+(LenTr - 1)+")").find(".btnAddRowDropOff").show();
        }
        eventKeyDropOff();
    }
    
    function keyEventAgent(){
        $(".agentAmount, .agentPercent, .btnRemoveRowAgent").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        
        $(".agentAmount, .agentPercent").focus(function(){
            if($(this).val() == '0' || $(this).val() == '0.00'){
                $(this).val('');
            }
        });
        
        $(".agentAmount, .agentPercent").blur(function(){
            if($(this).val() == ''){
                $(this).val('0');
            }
            if($(this).attr('class') == 'agentAmount validate[required] float'){
                $(this).closest("tr").find(".agentPercent").val(0);
            } else {
                $(this).closest("tr").find(".agentAmount").val(0);
            }
        });
        
        $(".btnRemoveRowAgent").click(function(){
            var currentTr = $(this).closest("tr");
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Are you sure to remove this order?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_INFORMATION; ?>',
                resizable: false,
                modal: true,
                width: '300',
                height: 'auto',
                position:'center',
                closeOnEscape: true,
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                },
                buttons: {
                    '<?php echo ACTION_OK; ?>': function() {
                        currentTr.remove();
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    
    function eventKeyRouteMulti(){
        $(".JourneyRouteMulti, .btnAddRowRouteMulti, .btnRemoveRowRouteMulti").unbind('click').unbind('change');
        $(".JourneyRouteMulti").chosen({width: 350});
        $(".JourneyRouteMulti").change(function(){
            var obj = $(this);
            if($(this).val() != ''){
                // Get Departure Time
                var departure = $(this).find("option:selected").attr('departure');
                obj.closest("tr").find(".JourneyRouteMultiDeparture").html(departure);
            } else {
                obj.closest("tr").find(".JourneyRouteMultiDeparture").html('');
            }
            if($("input[name='data[TJourney][type]']:checked").val() == "2"){
                calculateTransitPrice();
            }
        });
        
        $(".btnAddRowRouteMulti").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveRowRouteMulti").show();
            cloneRouteMulti();
        });
        
        $(".btnRemoveRowRouteMulti").click(function(){
            var obj = $(this);
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete the selected item(s)?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_OK; ?>': function() {
                        obj.closest("tr").remove();
                        var lenTr = parseInt($(".rowListRouteMulti").length);
                        if(lenTr == 1){
                            $("#rowRouteMultiList").find("tr:eq(0)").find("td .btnRemoveRowRouteMulti").hide();
                        }
                        $("#rowRouteMultiList").find("tr:eq("+(lenTr - 1)+")").find("td .btnAddRowRouteMulti").show();
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    
    function eventKeyBoardingPoint(){
        $(".JourneyBoardingPoint, .btnAddRowBoardingPoint, .btnRemoveRowBoardingPoint").unbind('click').unbind('change');
        $(".JourneyBoardingPoint").chosen({width: 260});
        $(".JourneyBoardingPoint").change(function(){
            var obj = $(this);
            // Reset Disable
            var cacheSelect = $(this).closest("tr").find(".cacheJourneyBoardingPoint").val();
            if(cacheSelect != ''){
                $(".JourneyBoardingPoint").each(function(){
                    var boardingPointId = $(this).attr('id');
                    if(boardingPointId != obj.attr('id')){
                        $(this).showHideDropdownOptions(cacheSelect, true);
                    }
                });
            }
            if($(this).val() != ''){
                // Disable Other Row
                $(".JourneyBoardingPoint").each(function(){
                    var boardingPointId = $(this).attr('id');
                    if(boardingPointId != obj.attr('id')){
                        $(this).showHideDropdownOptions(obj.val(), false);
                    }
                });
                // Add Hour and Minute Require
                obj.closest("tr").find(".JourneyBoardingPointHour").addClass("validate[required]");
                obj.closest("tr").find(".JourneyBoardingPointMinute").addClass("validate[required]");
            } else {
                // Remove Hour and Minute Require
                obj.closest("tr").find(".JourneyBoardingPointHour").removeClass("validate[required]");
                obj.closest("tr").find(".JourneyBoardingPointMinute").removeClass("validate[required]");
            }
            // Set Cache
            obj.closest("tr").find(".cacheJourneyBoardingPoint").val(obj.val());
        });
        
        $(".btnAddRowBoardingPoint").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveRowBoardingPoint").show();
            cloneBoardingPoint();
        });
        
        $(".btnRemoveRowBoardingPoint").click(function(){
            var obj = $(this);
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete the selected item(s)?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_OK; ?>': function() {
                        obj.closest("tr").remove();
                        var lenTr = parseInt($(".rowListBoardingPoint").length);
                        if(lenTr == 1){
                            $("#rowBoardingPointList").find("tr:eq(0)").find("td .btnRemoveRowBoardingPoint").hide();
                        }
                        $("#rowBoardingPointList").find("tr:eq("+(lenTr - 1)+")").find("td .btnAddRowBoardingPoint").show();
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    
    function eventKeyDropOff(){
        $(".JourneyDropOff, .btnAddRowDropOff, .btnRemoveRowDropOff").unbind('click').unbind('change');
        $(".JourneyDropOff").chosen({width: 260});
        $(".JourneyDropOff").change(function(){
            var obj = $(this);
            // Reset Disable
            var cacheSelect = $(this).closest("tr").find(".cacheJourneyDropOff").val();
            if(cacheSelect != ''){
                $(".JourneyDropOff").each(function(){
                    var dropOffId = $(this).attr('id');
                    if(dropOffId != obj.attr('id')){
                        $(this).showHideDropdownOptions(cacheSelect, true);
                    }
                });
            }
            if($(this).val() != ''){
                // Disable Other Row
                $(".JourneyDropOff").each(function(){
                    var dropOffId = $(this).attr('id');
                    if(dropOffId != obj.attr('id')){
                        $(this).showHideDropdownOptions(obj.val(), false);
                    }
                });
                // Add Hour and Minute Require
                obj.closest("tr").find(".JourneyDropOffHour").addClass("validate[required]");
                obj.closest("tr").find(".JourneyDropOffMinute").addClass("validate[required]");
            } else {
                // Remove Hour and Minute Require
                obj.closest("tr").find(".JourneyDropOffHour").removeClass("validate[required]");
                obj.closest("tr").find(".JourneyDropOffMinute").removeClass("validate[required]");
            }
            // Set Cache
            obj.closest("tr").find(".cacheJourneyDropOff").val(obj.val());
        });
        
        $(".btnAddRowDropOff").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveRowDropOff").show();
            cloneDropOff();
        });
        
        $(".btnRemoveRowDropOff").click(function(){
            var obj = $(this);
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete the selected item(s)?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_OK; ?>': function() {
                        obj.closest("tr").remove();
                        var lenTr = parseInt($(".rowListDropOff").length);
                        if(lenTr == 1){
                            $("#rowDropOffList").find("tr:eq(0)").find("td .btnRemoveRowDropOff").hide();
                        }
                        $("#rowDropOffList").find("tr:eq("+(lenTr - 1)+")").find("td .btnAddRowDropOff").show();
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    
    function eventKeyTransit(){
        $(".DestinationTransitJourney, .btnAddRowTransit, .btnRemoveRowTransit").unbind('click').unbind('change');
        $(".DestinationTransitJourney").chosen({width: 350});
        $(".DestinationTransitJourney").change(function(){
            var id  = $(this).val();
            var obj = $(this).closest("tr").closest("table");
            var row = $(this).closest("tr");
            $.ajax({
                dataType: "json",
                type: "POST",
                url: "<?php echo $this->base . '/t_journeys'; ?>/getJourneyByDestination/"+id,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(result){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    obj.find(".JourneyTransit").html(result.option);
                    $(".JourneyTransit").trigger("chosen:updated");
                    row.find(".DefaultTransitJourney").html(result.option);
                }
            });
        });

        $(".btnAddRowTransit").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveRowTransit").show();
            cloneTransit();
        });
        
        $(".btnRemoveRowTransit").click(function(){
            var obj = $(this);
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete the selected item(s)?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_OK; ?>': function() {
                        obj.closest("tr").closest("table").closest("tr").remove();
                        var lenTr = parseInt($(".rowListTransit").length);
                        if(lenTr == 1){
                            $("#rowTransitList").find("tr:eq(0)").find("td .btnRemoveRowTransit").hide();
                        }
                        $("#rowTransitList").find("tr:eq("+(lenTr - 1)+")").find("td .btnAddRowTransit").show();
                        $(this).dialog("close");
                    }
                }
            });
        });
    }

    function eventKeyTransitDetail(){
        $(".JourneyTransit, .btnAddRowScheduleTransit, .btnRemoveRowScheduleTransit").unbind('click').unbind('change');
        $(".JourneyTransit").chosen({width: 350});
        $(".JourneyTransit").change(function(){
            var obj = $(this);
            if($(this).val() != ''){
                // Get Departure Time
                var departure = $(this).find("option:selected").attr('departure');
                obj.closest("tr").find(".JourneyTransitDeparture").html(departure);
            } else {
                obj.closest("tr").find(".JourneyTransitDeparture").html('');
            }
            calculateTransitPrice();
        });
        
        $(".btnAddRowScheduleTransit").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveRowScheduleTransit").show();
            var obj = $(this).closest("tr").closest("table").closest("tr").closest("table").closest("tr");
            cloneTransitDetail(obj);
        });
        
        $(".btnRemoveRowScheduleTransit").click(function(){
            var obj = $(this);
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete the selected item(s)?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_OK; ?>': function() {
                        var table = obj.closest("table");
                        obj.closest("tr").remove();
                        var lenTr = parseInt(table.find(".rowListTransitDetail").length);
                        if(lenTr == 1){
                            table.find(".rowTransitListDetail").find("tr:eq(0)").find("td .btnRemoveRowScheduleTransit").hide();
                        }
                        table.find(".rowTransitListDetail").find("tr:eq("+(lenTr - 1)+")").find("td .btnAddRowScheduleTransit").show();
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    
    function calculateTransitPrice(){
        var totalPrice = 0;
        var totalVip = 0;
        $(".rowListTransit").each(function(){
            var journeySelect = $(this).find(".rowListTransitDetail").eq(0).find(".JourneyTransit");
            totalPrice += replaceNum(journeySelect.find("option:selected").attr('price'));
            totalVip += replaceNum(journeySelect.find("option:selected").attr('vip'));
        });
        // Set Price
        $("#TJourneyUnitPrice").val(converDicemalJS(totalPrice).toFixed(2));
        $("#TJourneyMembership").val(converDicemalJS(totalVip).toFixed(2));
    }
</script>
<div style="padding: 5px; border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTJourney">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <?php
    $checkSave = true;
    // $sqlChkTicket = mysql_query("SELECT id FROM t_tickets WHERE t_journey_id = ".$this->data['TJourney']['id']." AND status > 0 LIMIT 1");
    // if(mysql_num_rows($sqlChkTicket)){
    //     $checkSave = false;
    ?>
    <!-- <div style="width: 550px; font-size: 14px; font-weight: bold; margin-left: 10px; float: left; padding-top: 5px;">
        Sorry, you cannot edit this journey because it have transaction.
    </div> -->
    <?php
    // }
    ?>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('TJourney'); 
echo $this->Form->input('id');
echo $this->Form->hidden('sys_code');
?>
<fieldset style="width: 48%; float: left; min-height: 1050px;">
    <legend><?php __(MENU_JOURNEY_INFO); ?></legend>
    <table cellpadding="3">
        <tr>
            <td><label for="TJourneyCompanyId"><?php echo MENU_COMPANY_MANAGEMENT; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('company_id', array('class'=>'validate[required] journeySelectChz', 'label' => false, 'empty' => INPUT_SELECT, 'div' => false, 'style' => 'width: 250px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[TJourney][branch_id]" id="TJourneyBranchId" style="width: 250px;" class="validate[required] journeySelectChz">
                        <option com="" symbol="" value=""><?php echo INPUT_SELECT; ?></option>
                        <?php foreach($branches AS $branch){ ?>
                        <option com="<?php echo $branch['Branch']['company_id']; ?>" symbol="<?php echo $branch['CurrencyCenter']['symbol']; ?>" value="<?php echo $branch['Branch']['id']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </td>
        </tr>
<!--        <tr>
            <td><label for="TJourneyTJourneyTypeId"><?php echo MENU_JOURNEY_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('t_journey_type_id', array('class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'div' => false, 'style' => 'width: 250px')); ?>
                </div>
            </td>
        </tr>-->
        <tr>
            <td><label for="TJourneyDescription"><?php echo GENERAL_DESCRIPTION; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('description', array('class'=>'validate[required]', 'style' => 'width: 330px; height: 25px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyDescriptionKh"><?php echo GENERAL_DESCRIPTION; ?> (Khmer) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('description_kh', array('class'=>'validate[required]', 'style' => 'width: 330px; height: 25px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyVehicleNo"><?php echo TABLE_VEHICLE_NO; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('vehicle_no', array('class'=>'validate[required]', 'style' => 'width: 330px; height: 25px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyNationRoad"><?php echo "Nation Road"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('nation_road', array('class'=>'validate[required]', 'style' => 'width: 330px; height: 25px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyTDestinationFromId"><?php echo TABLE_DESTINATION_FROM; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('t_destination_from_id', array('class'=>'validate[required] journeySelectChz', 'label' => false, 'empty' => INPUT_SELECT, 'div' => false, 'style' => 'width: 260px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyTDestinationToId"><?php echo TABLE_DESTINATION_TO; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('t_destination_to_id', array('class'=>'validate[required] journeySelectChz', 'label' => false, 'empty' => INPUT_SELECT, 'div' => false, 'style' => 'width: 260px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyTTransportationTypeId"><?php echo MENU_TRANSPORTATION_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('t_transportation_type_id', array('class'=>'validate[required] journeySelectChz', 'label' => false, 'empty' => INPUT_SELECT, 'div' => false, 'style' => 'width: 260px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyTRouteId"><?php echo TABLE_MULTI_ROUTE; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[TJourney][t_route_id]" id="TJourneyTRouteId" style="width: 260px;" class="journeySelectChz">
                        <option value=""><?php echo "Single Route"; ?></option>
                        <?php foreach($tRoutes AS $tRoute){ ?>
                        <option value="<?php echo $tRoute['TRoute']['id']; ?>" <?php if($tRoute['TRoute']['id'] == $this->data['TJourney']['t_route_id']){ ?>selected=""<?php } ?>><?php echo $tRoute['TRoute']['name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyRouteCode"><?php echo "Route Code"; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('route_code', array('style' => 'width: 330px; height: 25px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyVehicleType"><?php echo "Seat Type"; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[TJourney][vehicle_type]" style="width: 120px;" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1" <?php if($this->data['TJourney']['vehicle_type'] == 1){ ?>selected=""<?php } ?>><?php echo "Normal Seat"; ?></option>
                        <option value="2" <?php if($this->data['TJourney']['vehicle_type'] == 2){ ?>selected=""<?php } ?>><?php echo "VIP Seat"; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyGenderRequire"><?php echo TABLE_GENDER_REQUIRED; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[TJourney][gender_require]" id="TJourneyGenderRequire" style="width: 120px;" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1" <?php if($this->data['TJourney']['gender_require'] == 1){ ?>selected=""<?php } ?>><?php echo ACTION_YES; ?></option>
                        <option value="0" <?php if($this->data['TJourney']['gender_require'] == 0){ ?>selected=""<?php } ?>><?php echo ACTION_NO; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><?php echo "Price Include VAT"; ?> <span class="red">*</span> :</td>
            <td>
                <div class="inputContainer">
                    <select id="TJourneyAllowPricePeriod" name="data[TJourney][allow_price_period]" style="width: 120px; height: 25px;">
                        <option value="0" <?php if($this->data['TJourney']['allow_price_period'] == 0){ ?>selected=""<?php } ?>><?php echo ACTION_NO; ?></option>
                        <option value="1" <?php if($this->data['TJourney']['allow_price_period'] == 1){ ?>selected=""<?php } ?>><?php echo ACTION_YES; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 14px; font-weight: bold;">Single Trip Price</td>
        </tr>
        <tr>
            <td><label for="TJourneyUnitPrice"><?php echo "Selling Price"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php 
                    $readonly = false;
                    if($this->data['TJourney']['type'] == 2){
                        $readonly = true;
                    }
                    echo $this->Form->text('unit_price', array('readonly' => $readonly, 'class'=>'validate[required] float', 'style' => 'width: 240px', 'value' => number_format($this->data['TJourney']['unit_price'], 2))); ?> <span class="TJourneySymbolCurrency"></span>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyMembership"><?php echo "Selling Price VIP Card"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php 
                    echo $this->Form->text('membership', array('readonly' => $readonly, 'class'=>'validate[required] float', 'style' => 'width: 240px', 'value' => number_format($this->data['TJourney']['membership'], 2))); ?> <span class="TJourneySymbolCurrency"></span>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyAgentPriceAmount"><?php echo "Agency Price"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('agent_price_amount', array('readonly' => $readonly, 'class'=>'validate[required] float', 'style' => 'width: 240px', 'value' => number_format($this->data['TJourney']['agent_price_amount'], 2))); ?> <span class="TJourneySymbolCurrency"></span>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 14px; font-weight: bold;">Round Trip Price</td>
        </tr>
        <tr>
            <td><label for="TJourneyRoundPrice"><?php echo "Round Trip Price"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('round_price', array('readonly' => $readonly, 'class'=>'validate[required] float', 'style' => 'width: 240px', 'value' => number_format($this->data['TJourney']['round_price'], 2))); ?> <span class="TJourneySymbolCurrency"></span>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyRoundPriceVip"><?php echo "Round Trip Price VIP Card"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('round_price_vip', array('readonly' => $readonly, 'class'=>'validate[required] float', 'style' => 'width: 240px', 'value' => number_format($this->data['TJourney']['round_price_vip'], 2))); ?> <span class="TJourneySymbolCurrency"></span>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyAgentRoundPrice"><?php echo "Agency Round Trip Price"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('agent_round_price', array('readonly' => $readonly, 'class'=>'validate[required]float', 'style' => 'width: 240px', 'value' => number_format($this->data['TJourney']['agent_round_price'], 2))); ?> <span class="TJourneySymbolCurrency"></span>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table class="table" cellpadding="0" cellspacing="0" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class="first" style="width: 55%;"><?php echo MENU_BOARDING_POINT; ?> <span class="red">*</span></th>
                            <th style="width: 30%;" colspan="2"><?php echo TABLE_TIME; ?> <span class="red">*</span></th>
                            <th style="width: 15%;"></th>
                        </tr>
                    </thead>
                    <tbody id="rowBoardingPointList">
                        <tr id="rowListBoardingPoint" class="rowListBoardingPoint">
                            <td class="first">
                                <div class="inputContainer" style="width: 100%;">
                                    <input type="hidden" class="cacheJourneyBoardingPoint" />
                                    <select class="JourneyBoardingPoint" name="boarding_point[]" style="width: 95%; height: 25px;">
                                        <option value=""><?php echo INPUT_SELECT; ?></option>
                                        <?php
                                        foreach($tBoardingPoints AS $tBoardingPoint){
                                        ?>
                                        <option value="<?php echo $tBoardingPoint['TBoardingPoint']['id']; ?>"><?php echo $tBoardingPoint['TBoardingPoint']['name']; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width: 100%;">
                                    <select class="JourneyBoardingPointHour" name="boarding_point_hour[]" style="width: 90%;">
                                        <option value="">HH</option>
                                        <?php
                                        for($h=0; $h<24; $h++){
                                        ?>
                                        <option value="<?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?>"><?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width: 100%;">
                                    <select class="JourneyBoardingPointMinute" name="boarding_point_min[]" style="width: 90%;">
                                        <option value="">MM</option>
                                        <?php
                                        for($m=0; $m<60; $m=($m+5)){
                                        ?>
                                        <option value="<?php echo str_pad($m,2,"0",STR_PAD_LEFT); ?>"><?php echo str_pad($m,2,"0",STR_PAD_LEFT); ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddRowBoardingPoint" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Add')" />
                                &nbsp;&nbsp;<img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowBoardingPoint" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                            </td>
                        </tr>
                        <?php
                        $ib = 1;
                        $boardingCount = mysql_num_rows($sqlBoardingPoint);
                        while($rowBoardingPoint = mysql_fetch_array($sqlBoardingPoint)){
                            $bp = rand();
                            if(!empty($rowBoardingPoint['time'])){
                                $time = explode(":", $rowBoardingPoint['time']);
                            } else {
                                $time[] = 'N/A';
                                $time[] = 'N/A';
                            }
                        ?>
                        <tr class="rowListBoardingPoint">
                            <td class="first">
                                <div class="inputContainer" style="width: 100%;">
                                    <input type="hidden" class="cacheJourneyBoardingPoint" value="<?php echo $rowBoardingPoint['t_boarding_point_id']; ?>" />
                                    <select class="JourneyBoardingPoint" name="boarding_point[]" style="width: 95%; height: 25px;">
                                        <option value=""><?php echo INPUT_SELECT; ?></option>
                                        <?php
                                        foreach($tBoardingPoints AS $tBoardingPoint){
                                        ?>
                                        <option value="<?php echo $tBoardingPoint['TBoardingPoint']['id']; ?>" <?php if($rowBoardingPoint['t_boarding_point_id'] == $tBoardingPoint['TBoardingPoint']['id']){ ?>selected=""<?php } ?>><?php echo $tBoardingPoint['TBoardingPoint']['name']; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width: 100%;">
                                    <select class="JourneyBoardingPointHour validate[required]" id="JourneyBoardingPointHour<?php echo $bp; ?>" name="boarding_point_hour[]" style="width: 90%;">
                                        <option value="">HH</option>
                                        <?php
                                        for($h=0; $h<24; $h++){
                                        ?>
                                        <option value="<?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?>" <?php if(str_pad($h,2,"0",STR_PAD_LEFT) == $time[0]){ ?>selected=""<?php } ?>><?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width: 100%;">
                                    <select class="JourneyBoardingPointMinute validate[required]" id="JourneyBoardingPointMinute<?php echo $bp; ?>" name="boarding_point_min[]" style="width: 90%;">
                                        <option value="">MM</option>
                                        <?php
                                        for($m=0; $m<60; $m=($m+5)){
                                        ?>
                                        <option value="<?php echo str_pad($m,2,"0",STR_PAD_LEFT); ?>" <?php if(str_pad($m,2,"0",STR_PAD_LEFT) == $time[1]){ ?>selected=""<?php } ?>><?php echo str_pad($m,2,"0",STR_PAD_LEFT); ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddRowBoardingPoint" align="absmiddle" style="cursor: pointer; <?php if($boardingCount != $ib){ ?>display: none;<?php } ?>" onmouseover="Tip('Add')" />
                                &nbsp;&nbsp;<img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowBoardingPoint" align="absmiddle" style="cursor: pointer; <?php if($ib == 1){ ?>display: none;<?php } ?>" onmouseover="Tip('Remove')" />
                            </td>
                        </tr>
                        <?php
                            $ib++;
                        }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table class="table" cellpadding="0" cellspacing="0" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class="first" style="width: 55%;"><?php echo MENU_DROP_OFF; ?> <span class="red">*</span></th>
                            <th style="width: 30%;" colspan="2"><?php echo TABLE_TIME; ?> <span class="red">*</span></th>
                            <th style="width: 15%;"></th>
                        </tr>
                    </thead>
                    <tbody id="rowDropOffList">
                        <tr id="rowListDropOff" class="rowListDropOff">
                            <td class="first">
                                <div class="inputContainer" style="width: 100%;">
                                    <input type="hidden" class="cacheJourneyDropOff" />
                                    <select class="JourneyDropOff" name="drop_off[]" style="width: 95%; height: 25px;">
                                        <option value=""><?php echo INPUT_SELECT; ?></option>
                                        <?php
                                        foreach($tDropOffs AS $tDropOff){
                                        ?>
                                        <option value="<?php echo $tDropOff['TDropOff']['id']; ?>"><?php echo $tDropOff['TDropOff']['name']; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width: 100%;">
                                    <select class="JourneyDropOffHour" name="drop_off_hour[]" style="width: 90%;">
                                        <option value="">HH</option>
                                        <?php
                                        for($h=0; $h<24; $h++){
                                        ?>
                                        <option value="<?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?>"><?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width: 100%;">
                                    <select class="JourneyDropOffMinute" name="drop_off_min[]" style="width: 90%;">
                                        <option value="">MM</option>
                                        <?php
                                        for($m=0; $m<60; $m=($m+5)){
                                        ?>
                                        <option value="<?php echo str_pad($m,2,"0",STR_PAD_LEFT); ?>"><?php echo str_pad($m,2,"0",STR_PAD_LEFT); ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddRowDropOff" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Add')" />
                                &nbsp;&nbsp;<img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowDropOff" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                            </td>
                        </tr>
                        <?php
                        $io = 1;
                        $dropOffCount = mysql_num_rows($sqlDropOff);
                        while($rowDropOff = mysql_fetch_array($sqlDropOff)){
                            $do = rand();
                            if(!empty($rowDropOff['time'])){
                                $time = explode(":", $rowDropOff['time']);
                            } else {
                                $time[] = 'N/A';
                                $time[] = 'N/A';
                            }
                        ?>
                        <tr class="rowListDropOff">
                            <td class="first">
                                <div class="inputContainer" style="width: 100%;">
                                    <input type="hidden" class="cacheJourneyDropOff" />
                                    <select class="JourneyDropOff" name="drop_off[]" value="<?php echo $rowDropOff['t_drop_off_id']; ?>" style="width: 95%; height: 25px;">
                                        <option value=""><?php echo INPUT_SELECT; ?></option>
                                        <?php
                                        foreach($tDropOffs AS $tDropOff){
                                        ?>
                                        <option value="<?php echo $tDropOff['TDropOff']['id']; ?>" <?php if($rowDropOff['t_drop_off_id'] == $tDropOff['TDropOff']['id']){ ?>selected=""<?php } ?>><?php echo $tDropOff['TDropOff']['name']; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width: 100%;">
                                    <select class="JourneyDropOffHour" id="JourneyDropOffHour<?php echo $do; ?>" name="drop_off_hour[]" style="width: 90%;">
                                        <option value="">HH</option>
                                        <?php
                                        for($h=0; $h<24; $h++){
                                        ?>
                                        <option value="<?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?>" <?php if(str_pad($h,2,"0",STR_PAD_LEFT) == $time[0]){ ?>selected=""<?php } ?>><?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width: 100%;">
                                    <select class="JourneyDropOffMinute" id="JourneyDropOffMinute<?php echo $do; ?>" name="drop_off_min[]" style="width: 90%;">
                                        <option value="">MM</option>
                                        <?php
                                        for($m=0; $m<60; $m=($m+5)){
                                        ?>
                                        <option value="<?php echo str_pad($m,2,"0",STR_PAD_LEFT); ?>" <?php if(str_pad($m,2,"0",STR_PAD_LEFT) == $time[1]){ ?>selected=""<?php } ?>><?php echo str_pad($m,2,"0",STR_PAD_LEFT); ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddRowDropOff" align="absmiddle" style="cursor: pointer; <?php if($dropOffCount != $io){ ?>display: none;<?php } ?>" onmouseover="Tip('Add')" />
                                &nbsp;&nbsp;<img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowDropOff" align="absmiddle" style="cursor: pointer; <?php if($io == 1){ ?>display: none;<?php } ?>" onmouseover="Tip('Remove')" />
                            </td>
                        </tr>
                        <?php
                            $io++;
                        }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</fieldset>
<fieldset style="width: 48%; float: right; min-height: 1050px;">
    <legend><?php __(TABLE_SCHEDULE); ?></legend>
    <table cellpadding="3">
        <tr>
            <td><label for="TJourneyTDepartureTimeHour"><?php echo MENU_DEPARTURE_TIME; ?> <span class="red">*</span> :</label></td>
            <td>
                <?php 
                    $departureTimes = explode(":", $this->data['TDepartureTime']['name']);
                    $arrivalTime    = explode(":", $this->data['TJourney']['arrival']);
                ?>
                <div class="inputContainer" style="width: 60px; float: left;">
                    <select id="TJourneyTDepartureTimeHour" class="validate[required]" name="data[TJourney][t_journey_departure_hour]" style="width: 90%;">
                        <option value="">HH</option>
                        <?php
                        for($h=0; $h<24; $h++){
                        ?>
                        <option value="<?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?>" <?php if(str_pad($h,2,"0",STR_PAD_LEFT) == $departureTimes[0]){ ?>selected=""<?php } ?>><?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="inputContainer" style="width: 60px; float: left;">
                    <select id="TJourneyTDepartureTimeMinute" class="validate[required]" name="data[TJourney][t_journey_departure_min]" style="width: 90%;">
                        <option value="">MM</option>
                        <?php
                        for($m=0; $m<60; $m=($m+5)){
                        ?>
                        <option value="<?php echo str_pad($m,2,"0",STR_PAD_LEFT); ?>" <?php if(str_pad($m,2,"0",STR_PAD_LEFT) == $departureTimes[1]){ ?>selected=""<?php } ?>><?php echo str_pad($m,2,"0",STR_PAD_LEFT); ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                <div style="clear: both;"></div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyArrivalHour"><?php echo TABLE_ARRIVAL_TIME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 60px; float: left;">
                    <select id="TJourneyArrivalHour" class="validate[required]" name="data[TJourney][t_journey_arrival_hour]" style="width: 90%;">
                        <option value="">HH</option>
                        <?php
                        for($h=0; $h<24; $h++){
                        ?>
                        <option value="<?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?>" <?php if(str_pad($h,2,"0",STR_PAD_LEFT) == $arrivalTime[0]){ ?>selected=""<?php } ?>><?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="inputContainer" style="width: 60px; float: left;">
                    <select id="TJourneyArrivalMinute" class="validate[required]" name="data[TJourney][t_journey_arrival_min]" style="width: 90%;">
                        <option value="">MM</option>
                        <?php
                        for($m=0; $m<60; $m=($m+5)){
                        ?>
                        <option value="<?php echo str_pad($m,2,"0",STR_PAD_LEFT); ?>" <?php if(str_pad($m,2,"0",STR_PAD_LEFT) == $arrivalTime[1]){ ?>selected=""<?php } ?>><?php echo str_pad($m,2,"0",STR_PAD_LEFT); ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                <div style="clear: both;"></div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyDurationHour"><?php echo TABLE_DURATION; ?> <span class="red">*</span> :</label></td>
            <td>
                <?php 
                if(!empty($this->data['TJourney']['duration'])){
                   $duration = explode(":", $this->data['TJourney']['duration']); 
                } else {
                    $duration[] = 'N/A';
                    $duration[] = 'N/A';
                }
                ?>
                <div class="inputContainer" style="width: 60px; float: left;">
                    <select id="TJourneyDurationHour" class="validate[required]" name="data[TJourney][t_journey_duration_hour]" style="width: 90%;">
                        <option value="">HH</option>
                        <?php
                        for($h=0; $h<24; $h++){
                        ?>
                        <option value="<?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?>" <?php if(str_pad($h,2,"0",STR_PAD_LEFT) == $duration[0]){ ?>selected=""<?php } ?>><?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="inputContainer" style="width: 60px; float: left;">
                    <select id="TJourneyDurationMinute" class="validate[required]" name="data[TJourney][t_journey_duration_min]" style="width: 90%;">
                        <option value="">MM</option>
                        <?php
                        for($m=0; $m<60; $m=($m+5)){
                        ?>
                        <option value="<?php echo str_pad($m,2,"0",STR_PAD_LEFT); ?>" <?php if(str_pad($m,2,"0",STR_PAD_LEFT) == $duration[1]){ ?>selected=""<?php } ?>><?php echo str_pad($m,2,"0",STR_PAD_LEFT); ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                <div style="clear: both;"></div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyAdvanceBooking"><?php echo TABLE_ADVANCE_BOOKING; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('advance_booking', array('class'=>'validate[required] interger', 'style' => 'width: 240px')); ?> <?php echo TABLE_DAY; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyAllowCancellation"><?php echo TABLE_ALLOW_CANCELLATION; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->input('allow_cancellation', array('class'=>'validate[required]', 'options' => $answers, 'empty' => INPUT_SELECT, 'label' => false, 'div' => false, 'style' => 'width: 115px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyRejectBeforeDeparture"><?php echo TABLE_REJECT_BEFORE_DEPARTURE; ?> (Phone Call) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('reject_before_departure', array('class'=>'validate[required] interger', 'style' => 'width: 240px')); ?> <?php echo TABLE_MINUTE; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyRejectBeforeDepartureSchedule"><?php echo TABLE_REJECT_BEFORE_DEPARTURE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('reject_before_departure_schedule', array('class'=>'validate[required] interger', 'style' => 'width: 240px')); ?> <?php echo TABLE_MINUTE; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><?php echo TABLE_REJECT_BEFORE_DEPARTURE." Apply To "; ?></td>
            <td colspan="2">
                <input type="checkbox" value="1" name="data[TJourney][reject_bf_dep_sch_walk_in]" id="TJourneyAllowRejectDepScheduleWalkIn" <?php if($this->data['TJourney']['reject_bf_dep_sch_walk_in'] == 1){ ?>checked=""<?php } ?> style="margin-bottom: 10px;" /> <label for="TJourneyAllowRejectDepScheduleWalkIn">Walk-In</label>
                <input type="checkbox" value="1" name="data[TJourney][reject_bf_dep_sch_online]" id="TJourneyAllowRejectDepScheduleOnline" <?php if($this->data['TJourney']['reject_bf_dep_sch_online'] == 1){ ?>checked=""<?php } ?> style="margin-bottom: 10px;" /> <label for="TJourneyAllowRejectDepScheduleOnline">Online</label>
                <input type="checkbox" value="1" name="data[TJourney][reject_bf_dep_sch_api]" id="TJourneyAllowRejectDepScheduleAPI" <?php if($this->data['TJourney']['reject_bf_dep_sch_api'] == 1){ ?>checked=""<?php } ?> style="margin-bottom: 10px;" /> <label for="TJourneyAllowRejectDepScheduleAPI">API</label>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyDelayAfDeparture"><?php echo TABLE_DELAY_AFTER_DEPARTURE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select id="TJourneyDelayAfDeparture" name="data[TJourney][delay_af_departure]" style="width: 120px; height: 25px;">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="0" <?php if($this->data['TJourney']['delay_af_departure'] == 0){ ?>selected=""<?php } ?>><?php echo '0'; ?></option>
                        <option value="30" <?php if($this->data['TJourney']['delay_af_departure'] == 30){ ?>selected=""<?php } ?>><?php echo '30 Min'; ?></option>
                        <option value="60" <?php if($this->data['TJourney']['delay_af_departure'] == 60){ ?>selected=""<?php } ?>><?php echo '1 Hour'; ?></option>
                        <option value="90" <?php if($this->data['TJourney']['delay_af_departure'] == 90){ ?>selected=""<?php } ?>><?php echo '1 Hour 30 Min'; ?></option>
                        <option value="120" <?php if($this->data['TJourney']['delay_af_departure'] == 120){ ?>selected=""<?php } ?>><?php echo '2 Hour'; ?></option>
                        <option value="150" <?php if($this->data['TJourney']['delay_af_departure'] == 150){ ?>selected=""<?php } ?>><?php echo '2 Hour 30 Min'; ?></option>
                        <option value="180" <?php if($this->data['TJourney']['delay_af_departure'] == 180){ ?>selected=""<?php } ?>><?php echo '3 Hour'; ?></option>
                        <option value="210" <?php if($this->data['TJourney']['delay_af_departure'] == 210){ ?>selected=""<?php } ?>><?php echo '3 Hour 30 Min'; ?></option>
                        <option value="240" <?php if($this->data['TJourney']['delay_af_departure'] == 240){ ?>selected=""<?php } ?>><?php echo '4 Hour'; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><?php echo TABLE_WEEKLY_SCHEDULE; ?> <span class="red">*</span> :</td>
            <td>
                <div class="inputContainer">
                    <?php
                    $sqlSchedule = mysql_query("SELECT * FROM t_journey_schedules WHERE t_journey_id = ".$this->data['TJourney']['id']);
                    $rowSchedule = mysql_fetch_array($sqlSchedule);
                    for($i=7;$i>=1;$i--){
                        $field = '';
                        $checked = '';
                        if($i == 7){
                            $field = TABLE_SUNDAY;
                            if($rowSchedule['sun'] == 1){
                                $checked = 'checked="checked"';
                            }
                        } else if($i == 6){
                            $field = TABLE_SATURDAY;
                            if($rowSchedule['sat'] == 1){
                                $checked = 'checked="checked"';
                            }
                        } else if($i == 5){
                            $field = TABLE_FRIDAY;
                            if($rowSchedule['fri'] == 1){
                                $checked = 'checked="checked"';
                            }
                        } else if($i == 4){
                            $field = TABLE_THURSDAY;
                            if($rowSchedule['thu'] == 1){
                                $checked = 'checked="checked"';
                            }
                        } else if($i == 3){
                            $field = TABLE_WEDNESDAY;
                            if($rowSchedule['wed'] == 1){
                                $checked = 'checked="checked"';
                            }
                        } else if($i == 2){
                            $field = TABLE_TUESDAY;
                            if($rowSchedule['tue'] == 1){
                                $checked = 'checked="checked"';
                            }
                        } else if($i == 1){
                            $field = TABLE_MONDAY;
                            if($rowSchedule['mon'] == 1){
                                $checked = 'checked="checked"';
                            }
                        }
                    ?>
                    <input type="checkbox" <?php echo $checked; ?> name="journey_schedule[]" class="validate[required, minCheckbox[1]]" value="<?php echo $i; ?>" id="TJourneySchedule<?php echo $field; ?>" style="margin-bottom: 10px;" /> <label for="TJourneySchedule<?php echo $field; ?>"><?php echo $field; ?></label><br/>
                    <?php
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><?php echo TABLE_ALLOW_ACCESS; ?> :</td>
            <td style="vertical-align: top;">
                <div class="inputContainer" id="dvAllowAccessOutside" <?php if($this->data['TJourney']['type'] == 2){ ?>style="display: none;"<?php } ?>>
                    <input type="hidden" value="<?php echo $this->data['TJourney']['allow_access']; ?>" name="data[TJourney][allow_access]" />
                    <input type="checkbox" id="TJourneyAllowAPI" class="TJourneyAllowAccess" <?php if($this->data['TJourney']['allow_access'] == 1 || $this->data['TJourney']['allow_access'] == 3){ ?>checked=""<?php } ?> style="margin-bottom: 10px;" /> <label for="TJourneyAllowAPI">API</label>
                    <input type="checkbox" id="TJourneyAllowOnline" class="TJourneyAllowAccess" <?php if($this->data['TJourney']['allow_access'] == 2 || $this->data['TJourney']['allow_access'] == 3){ ?>checked=""<?php } ?> style="margin-bottom: 10px;" /> <label for="TJourneyAllowOnline">Online</label><br/>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyActiveStart"><?php echo 'Active Start Date'; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php
                    $activeStart = '';
                    if($this->data['TJourney']['active_start'] != '' && $this->data['TJourney']['active_start'] != '0000-00-00'){
                        $activeStart = dateShort($this->data['TJourney']['active_start']);
                    }
                    echo $this->Form->text('active_start', array('style' => 'width: 240px', 'value' => $activeStart)); ?> <img alt="" src="<?php echo $this->webroot; ?>img/button/clear.png" style="cursor: pointer;" onmouseover="Tip('Clear Active Date')" id="TJourneyClearActiveDate" />
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyActiveEnd"><?php echo 'Active End Date'; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php
                    $activeEnd = '';
                    if($this->data['TJourney']['active_end'] != '' && $this->data['TJourney']['active_end'] != '0000-00-00'){
                        $activeEnd = dateShort($this->data['TJourney']['active_end']);
                    }
                    echo $this->Form->text('active_end', array('style' => 'width: 240px', 'value' => $activeEnd)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyBlockStart"><?php echo TABLE_BLOCK_START; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php
                    $blockStart = '';
                    if($this->data['TJourney']['block_start'] != '' && $this->data['TJourney']['block_start'] != '0000-00-00'){
                        $blockStart = dateShort($this->data['TJourney']['block_start']);
                    }
                    echo $this->Form->text('block_start', array('style' => 'width: 240px', 'value' => $blockStart)); ?> <img alt="" src="<?php echo $this->webroot; ?>img/button/clear.png" style="cursor: pointer;" onmouseover="Tip('Clear Block Date')" id="TJourneyClearBlockDate" />
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyBlockEnd"><?php echo TABLE_BLOCK_END; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php 
                    $blockEnd = '';
                    if($this->data['TJourney']['block_end'] != '' && $this->data['TJourney']['block_end'] != '0000-00-00'){
                        $blockEnd = dateShort($this->data['TJourney']['block_end']);
                    }
                    echo $this->Form->text('block_end', array('style' => 'width: 240px', 'value' => $blockEnd)); ?>
                </div>
            </td>
        </tr>
        <?php
        $pricePeriodFrom   = "";
        $pricePeriodTo     = "";
        $pricePeriodPrice  = "";
        $pricePeriodMember = "";
        $pricePeriodAgencyKh = "";
        $pricePeriodRoundPrice = "";
        $pricePeriodRoundPriceVip = "";
        $pricePeriodRoundPriceAgency = "";
        $sqlPricePeriod = mysql_query("SELECT * FROM t_journey_price_periods WHERE t_journey_id = ".$this->data['TJourney']['id']." AND status > 0 AND apply_type = 1 LIMIT 1;");
        if(mysql_num_rows($sqlPricePeriod)){
            $rowPricePeriod      = mysql_fetch_array($sqlPricePeriod);
            $pricePeriodFrom     = dateShort($rowPricePeriod['start']);
            $pricePeriodTo       = dateShort($rowPricePeriod['end']);
            $pricePeriodPrice    = number_format($rowPricePeriod['price'], 2);
            $pricePeriodMember   = number_format($rowPricePeriod['membership'], 2);
            $pricePeriodAgencyKh = number_format($rowPricePeriod['agency_price'], 2);
            $pricePeriodRoundPrice = number_format($rowPricePeriod['round_price'], 2);
            $pricePeriodRoundPriceVip = number_format($rowPricePeriod['round_membership'], 2);
            $pricePeriodRoundPriceAgency = number_format($rowPricePeriod['round_agency_price'], 2);
        }
        ?>
        <tr>
            <td colspan="2">
                <span style="font-size: 16px; font-weight: bold;">Promotion Price Period</span> <img alt="" src="<?php echo $this->webroot; ?>img/button/clear.png" style="cursor: pointer;" onmouseover="Tip('Clear Date')" id="tJourneyClearPricePeriod" />
             </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><?php echo REPORT_FROM; ?> :</td>
            <td style="vertical-align: top;">
                <div class="inputContainer">
                    <?php echo $this->Form->text('price_period_from', array('style' => 'width: 240px', 'readonly' => true, 'autocomplete' => false, 'value' => $pricePeriodFrom)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><?php echo REPORT_TO; ?> :</td>
            <td style="vertical-align: top;">
                <div class="inputContainer">
                    <?php echo $this->Form->text('price_period_to', array('style' => 'width: 240px', 'readonly' => true, 'autocomplete' => false, 'value' => $pricePeriodTo)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 14px; font-weight: bold;">Single Trip Price</td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodPrice"><?php echo "Selling Price"; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('price_period_price', array('class'=>'float', 'style' => 'width: 200px; height: 20px;', 'autocomplete' => 'off', 'value' => $pricePeriodPrice)); ?> <span class="TJourneySymbolCurrency"></span>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodMembership"><?php echo "Selling Price VIP Card"; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('price_period_membership', array('class'=>'float', 'style' => 'width: 200px; height: 20px;', 'autocomplete' => 'off', 'value' => $pricePeriodMember)); ?> <span class="TJourneySymbolCurrency"></span>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodMembership"><?php echo "Agency Price"; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('price_period_agency_price', array('class'=>'float', 'style' => 'width: 200px; height: 20px;', 'autocomplete' => 'off', 'value' => $pricePeriodAgencyKh)); ?> <span class="TJourneySymbolCurrency"></span>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 14px; font-weight: bold;">Round Trip Price</td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodRoundPrice"><?php echo "Round Trip Price"; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('price_period_round_price', array('class'=>'float', 'style' => 'width: 200px; height: 20px;', 'autocomplete' => 'off', 'value' => $pricePeriodRoundPrice)); ?> <span class="TJourneySymbolCurrency"></span>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodRoundPriceVip"><?php echo "Round Trip Price VIP Card"; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('price_period_round_price_vip', array('class'=>'float', 'style' => 'width: 200px; height: 20px;', 'autocomplete' => 'off', 'value' => $pricePeriodRoundPriceVip)); ?> <span class="TJourneySymbolCurrency"></span>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodAgencyRoundPrice"><?php echo "Agency Round Trip Price"; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('price_period_agency_round_price', array('class'=>'float', 'style' => 'width: 200px; height: 20px;', 'autocomplete' => 'off', 'value' => $pricePeriodRoundPriceAgency)); ?> <span class="TJourneySymbolCurrency"></span>
                </div>
            </td>
        </tr>
        <?php
        $pricePeriodFromInternal     = "";
        $pricePeriodToInternal       = "";
        $pricePeriodPriceInternal    = "";
        $pricePeriodRoundInternal    = "";
        $sqlPricePeriodInternal = mysql_query("SELECT * FROM t_journey_price_periods WHERE t_journey_id = ".$this->data['TJourney']['id']." AND status > 0 AND apply_type = 2 LIMIT 1;");
        if(mysql_num_rows($sqlPricePeriodInternal)){
            $rowPricePeriodInternal      = mysql_fetch_array($sqlPricePeriodInternal);
            $pricePeriodFromInternal     = dateShort($rowPricePeriodInternal['start']);
            $pricePeriodToInternal       = dateShort($rowPricePeriodInternal['end']);
            $pricePeriodPriceInternal    = number_format($rowPricePeriodInternal['price'], 2);
            $pricePeriodRoundInternal    = number_format($rowPricePeriodInternal['round_price'], 2);
        }
        ?>
        <tr>
            <td colspan="2" style="border-top: 1px solid #ccc;">
                <span style="font-size: 16px; font-weight: bold;">Price Period Internal</span> <img alt="" src="<?php echo $this->webroot; ?>img/button/clear.png" style="cursor: pointer;" onmouseover="Tip('Clear Date')" id="tJourneyClearPricePeriodInternal" />
             </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><?php echo REPORT_FROM; ?> :</td>
            <td style="vertical-align: top;">
                <div class="inputContainer">
                    <?php echo $this->Form->text('price_period_from_internal', array('style' => 'width: 240px', 'readonly' => true, 'autocomplete' => false, 'value' => $pricePeriodFromInternal)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><?php echo REPORT_TO; ?> :</td>
            <td style="vertical-align: top;">
                <div class="inputContainer">
                    <?php echo $this->Form->text('price_period_to_internal', array('style' => 'width: 240px', 'readonly' => true, 'autocomplete' => false, 'value' => $pricePeriodToInternal)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodPriceInternal"><?php echo "Selling Price"; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('price_period_price_internal', array('class'=>'float', 'style' => 'width: 200px; height: 20px;', 'autocomplete' => 'off', 'value' => $pricePeriodPriceInternal)); ?> <span class="TJourneySymbolCurrency"></span>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TJourneyPricePeriodRoundPriceInternal"><?php echo "Round Trip Price"; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('price_period_round_price_internal', array('class'=>'float', 'style' => 'width: 200px; height: 20px;', 'autocomplete' => 'off', 'value' => $pricePeriodRoundInternal)); ?> <span class="TJourneySymbolCurrency"></span>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<div style="clear: both;"></div>
<fieldset style="width: 48%; float: left; height: 400px; overflow-x: hidden; overflow-y: scroll;">
    <legend><?php __(TABLE_JOURNEY_DIRECTION); ?></legend>
    <table cellpadding="0" cellspacing="0" style="width: 90%;">
        <tr>
            <td style="width: 20%;">Transport Route Display: </td>
            <td>
                <?php 
                $transportDisplay = "";
                if(empty($this->data['TJourney']['transport_route_display'])){
                    if($this->data['TJourney']['type'] == 2){
                        $transportDisplay = "Transit";
                    } else {
                        $transportDisplay = "Direct";
                    }
                } else {
                    $transportDisplay = $this->data['TJourney']['transport_route_display'];
                }
                echo $this->Form->text('transport_route_display', array('style' => 'width: 300px; height: 20px;', 'autocomplete' => 'off', 'value' => $transportDisplay)); ?>
            </td>
        </tr>
    </table>
    <br />
    <table cellpadding="0" cellspacing="0" style="width: 90%;">
        <tr>
            <td style="width: 20%;"><input type="radio" name="data[TJourney][type]" value="1" <?php if($this->data['TJourney']['type'] == 1){ ?>checked=""<?php } ?> id="JourneyDirection" /> <label for="JourneyDirection">Direct</label></td>
            <td style="width: 20%;"><input type="radio" name="data[TJourney][type]" value="2" <?php if($this->data['TJourney']['type'] == 2){ ?>checked=""<?php } ?> id="JourneyTransit" /> <label for="JourneyTransit">Transit</label></td>
            <td><input type="radio" name="data[TJourney][type]" value="3" <?php if($this->data['TJourney']['type'] == 3){ ?>checked=""<?php } ?> id="JourneyDirectionMultiRoute" /> <label for="JourneyDirectionMultiRoute">Direct Multi Route</label></td>
        </tr>
    </table>
    <br/>
    <table class="table" cellpadding="0" cellspacing="0" style="width: 100%; <?php if($this->data['TJourney']['type'] != 3){ ?>display: none;<?php } ?>" id="divRouteMultiJourney">
        <tr>
            <th class="first" style="width: 50%;"><?php echo MENU_JOURNEY; ?></th>
            <th style="width: 20%;"><?php echo TABLE_DEPARTURE_TIME; ?></th>
            <th style="width: 20%;"><?php echo TABLE_DATE; ?></th>
            <th style="width: 10%;"></th>
        </tr>
        <tbody id="rowRouteMultiList">
            <tr id="rowListRouteMulti" class="rowListRouteMulti">
                <td class="first">
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[journey_transit_id][]" class="JourneyRouteMulti validate[required]" style="width: 95%; height: 25px;">
                            <option departure="" price="" vip="" foreigner="" branch="" value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            $sqlJ = mysql_query("SELECT t_journeys.*, t_departure_times.name AS departure FROM t_journeys INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id WHERE t_journeys.status IN (1,2) AND t_journeys.type IN (1,3) AND t_journeys.offline_project_id = ".$user['User']['offline_project_id']." ORDER BY t_journeys.description ASC;");
                            while($rowJ = mysql_fetch_array($sqlJ)){
                                $departure = date("h:i A", strtotime($rowJ['departure']));
                                if($rowJ['route_code']){
                                    $departure .= "<br/>".$rowJ['route_code'];
                                }
                            ?>
                            <option departure="<?php echo $departure; ?>" price="<?php echo number_format($rowJ['unit_price'], 2); ?>" vip="<?php echo number_format($rowJ['membership'], 2); ?>" foreigner="<?php echo number_format($rowJ['foreigner_price'], 2); ?>" branch="<?php echo $rowJ['branch_id']; ?>" value="<?php echo $rowJ['id']; ?>"><?php echo $rowJ['description']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="inputContainer JourneyRouteMultiDeparture" style="width: 100%;"></div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[journey_transit_date][]" class="JourneyRouteMultiDate" class="validate[required]">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <option value="0"><?php echo TABLE_SAME_DATE; ?></option>
                            <option value="1"><?php echo TABLE_NEXT_DATE; ?></option>
                        </select>
                    </div>
                </td>
                <td style="text-align: center;">
                    <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddRowRouteMulti" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Add')" />
                    &nbsp;&nbsp;<img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowRouteMulti" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                </td>
            </tr>
            <?php
            if($this->data['TJourney']['type'] == 3){
                foreach($tJourneyTransits AS $tJourneyTransit){
                    $rnd = rand(0,200);
            ?>
            <tr class="rowListRouteMulti">
                <td class="first">
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[journey_transit_id][]" class="JourneyRouteMulti validate[required]" id="JourneyRouteMulti<?php echo $rnd; ?>" style="width: 95%; height: 25px;">
                            <option departure="" price="" vip="" foreigner="" branch="" value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            $departureSelected = '';
                            $sqlJ = mysql_query("SELECT t_journeys.*, t_departure_times.name AS departure FROM t_journeys INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id WHERE t_journeys.status IN (1,2) AND t_journeys.type IN (1,3) AND t_journeys.offline_project_id = ".$user['User']['offline_project_id']." ORDER BY t_journeys.description ASC;");
                            while($rowJ = mysql_fetch_array($sqlJ)){
                                $departure = date("h:i A", strtotime($rowJ['departure']));
                                if($rowJ['route_code']){
                                    $departure .= "<br/>".$rowJ['route_code'];
                                }
                                if($tJourneyTransit['TJourneyTransit']['t_journey_departure_id'] == $rowJ['id']){
                                    $departureSelected = $departure;
                                }
                            ?>
                            <option departure="<?php echo $departure; ?>" price="<?php echo number_format($rowJ['unit_price'], 2); ?>" vip="<?php echo number_format($rowJ['membership'], 2); ?>" foreigner="<?php echo number_format($rowJ['foreigner_price'], 2); ?>" branch="<?php echo $rowJ['branch_id']; ?>" value="<?php echo $rowJ['id']; ?>" <?php if($tJourneyTransit['TJourneyTransit']['t_journey_departure_id'] == $rowJ['id']){ ?>selected=""<?php } ?>><?php echo $rowJ['description']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="inputContainer JourneyRouteMultiDeparture" style="width: 100%;">
                        <?php echo $departureSelected; ?>
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[journey_transit_date][]" class="JourneyRouteMultiDate" class="validate[required]">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <option value="0" <?php if($tJourneyTransit['TJourneyTransit']['is_next_day'] == 0){ ?>selected=""<?php } ?>><?php echo TABLE_SAME_DATE; ?></option>
                            <option value="1" <?php if($tJourneyTransit['TJourneyTransit']['is_next_day'] == 1){ ?>selected=""<?php } ?>><?php echo TABLE_NEXT_DATE; ?></option>
                        </select>
                    </div>
                </td>
                <td style="text-align: center;">
                    <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddRowRouteMulti" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Add')" />
                    &nbsp;&nbsp;<img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowRouteMulti" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                </td>
            </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
    <!-- Transit -->
    <table cellpadding="0" cellspacing="0" style="width: 100%; <?php if($this->data['TJourney']['type'] != 2){ ?>display: none;<?php } ?>" id="divTransitJourney">
        <tbody id="rowTransitList">
            <tr id="rowListTransit" class="rowListTransit">
                <td style="border-bottom: 1px solid #000; padding-bottom: 5px; padding-top: 5px;">
                    <table cellpadding="0" cellspacing="0" style="width: 100%;">
                        <tr>
                            <td><?php echo TABLE_DESTINATION_FROM; ?> :</td>
                            <td>
                                <select class="DestinationTransitJourney" style="width: 300px; height: 20px;">
                                    <option value=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    $sqlDesTo = mysql_query("SELECT * FROM t_destinations WHERE is_active = 1 AND offline_project_id = 1");
                                    while($rowDesTo = mysql_fetch_array($sqlDesTo)){
                                    ?>
                                    <option value="<?php echo $rowDesTo['id']; ?>"><?php echo $rowDesTo['name']; ?></option>
                                    <?php
                                    }
                                    ?>   
                                </select>             
                            </td>
                            <td>
                                <select class="DefaultTransitJourney" style="display: none"></select>
                                <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddRowTransit" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Add')" />
                                &nbsp;&nbsp;<img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowTransit" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <table class="table" cellpadding="0" cellspacing="0" style="width: 100%;">
                                    <tr>
                                        <th class="first" style="width: 50%;"><?php echo MENU_JOURNEY; ?></th>
                                        <th style="width: 20%;"><?php echo TABLE_DEPARTURE_TIME; ?></th>
                                        <th style="width: 20%;"><?php echo TABLE_DATE; ?></th>
                                        <th style="width: 10%;"></th>
                                    </tr>
                                    <tbody class="rowTransitListDetail">
                                        <tr id="rowListTransitDetail" class="rowListTransitDetail">
                                            <td class="first">
                                                <div class="inputContainer" style="width: 100%;">
                                                    <input type="hidden" class="cacheJourneyTransit" />
                                                    <select name="data[journey_transit_id][]" class="JourneyTransit validate[required]" style="width: 95%; height: 25px;"></select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inputContainer JourneyTransitDeparture" style="width: 100%;"></div>
                                            </td>
                                            <td>
                                                <div class="inputContainer" style="width: 100%;">
                                                    <select name="data[journey_transit_date][]" class="JourneyTransitDate" class="validate[required]">
                                                        <option value=""><?php echo INPUT_SELECT; ?></option>
                                                        <option value="0"><?php echo TABLE_SAME_DATE; ?></option>
                                                        <option value="1"><?php echo TABLE_NEXT_DATE; ?></option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddRowScheduleTransit" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Add')" />
                                                &nbsp;&nbsp;<img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowScheduleTransit" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                                            </td>
                                        </tr>
                                    </tbody>   
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <?php
            if($this->data['TJourney']['type'] == 2){
                $transitByDest = array();
                $i = 0;
                foreach($tJourneyTransits AS $tJourneyTransit){
                    $keyTran = $tJourneyTransit['TJourneyTransit']['t_destination_from_id'];
                    if (array_key_exists($keyTran, $transitByDest)){
                        $i++;
                        $transitByDest[$keyTran]['transit'][$i]['journey'] = $tJourneyTransit['TJourneyTransit']['t_journey_departure_id'];
                        $transitByDest[$keyTran]['transit'][$i]['isNext']  = $tJourneyTransit['TJourneyTransit']['is_next_day'];
                    } else {
                        $i = 0;
                        $transitByDest[$keyTran]['transit'][$i]['journey'] = $tJourneyTransit['TJourneyTransit']['t_journey_departure_id'];
                        $transitByDest[$keyTran]['transit'][$i]['isNext']  = $tJourneyTransit['TJourneyTransit']['is_next_day'];
                    }
                } 
                if(!empty($transitByDest)){
                    foreach($transitByDest AS $key => $transit){
            ?>
            <tr class="rowListTransit">
                <td style="border-bottom: 1px solid #000; padding-bottom: 5px; padding-top: 5px;">
                    <table cellpadding="0" cellspacing="0" style="width: 100%;">
                        <tr>
                            <td><?php echo TABLE_DESTINATION_FROM; ?> :</td>
                            <td>
                                <select class="DestinationTransitJourney" style="width: 300px; height: 20px;">
                                    <option value=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    $sqlDesTo = mysql_query("SELECT * FROM t_destinations WHERE is_active = 1 AND offline_project_id = 1");
                                    while($rowDesTo = mysql_fetch_array($sqlDesTo)){
                                    ?>
                                    <option <?php if($rowDesTo['id'] == $key){ ?>selected=""<?php } ?> value="<?php echo $rowDesTo['id']; ?>"><?php echo $rowDesTo['name']; ?></option>
                                    <?php
                                    }
                                    ?>   
                                </select>             
                            </td>
                            <td>
                                <select class="DefaultTransitJourney" style="display: none">
                                    <option departure="" price="" vip="" foreigner="" branch="" value=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    $sqlJouTra = mysql_query("SELECT t_journeys.*, t_departure_times.name AS departure FROM t_journeys INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id WHERE t_journeys.status IN (1,2) AND t_journeys.type IN (1,3) AND t_journeys.t_destination_from_id = ".$key." AND t_journeys.offline_project_id = ".$user['User']['offline_project_id']." ORDER BY t_journeys.description ASC;");
                                    while($rowJouTra = mysql_fetch_array($sqlJouTra)){
                                        $departure = date("h:i A", strtotime($rowJouTra['departure']));
                                    ?>
                                    <option departure="<?php echo $departure; ?>" price="<?php echo number_format($rowJouTra['unit_price'], 2); ?>" vip="<?php echo number_format($rowJouTra['membership'], 2); ?>" foreigner="<?php echo number_format($rowJouTra['foreigner_price'], 2); ?>" branch="<?php echo $rowJouTra['branch_id']; ?>" value="<?php echo $rowJouTra['id']; ?>"><?php echo $rowJouTra['description']; ?></option>
                                    <?php    
                                    }
                                    ?>
                                </select>
                                <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddRowTransit" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Add')" />
                                &nbsp;&nbsp;<img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowTransit" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <table class="table" cellpadding="0" cellspacing="0" style="width: 100%;">
                                    <tr>
                                        <th class="first" style="width: 50%;"><?php echo MENU_JOURNEY; ?></th>
                                        <th style="width: 20%;"><?php echo TABLE_DEPARTURE_TIME; ?></th>
                                        <th style="width: 20%;"><?php echo TABLE_DATE; ?></th>
                                        <th style="width: 10%;"></th>
                                    </tr>
                                    <tbody class="rowTransitListDetail">
                                        <?php
                                        foreach($transit['transit'] AS $journeyTran){
                                            $rnd = rand(0,200);
                                        ?>
                                        <tr class="rowListTransitDetail">
                                            <td class="first">
                                                <div class="inputContainer" style="width: 100%;">
                                                    <select name="data[journey_transit_id][]" class="JourneyTransit validate[required]" id="JourneyTransit<?php echo $rnd; ?>" style="width: 95%; height: 25px;">
                                                        <option departure="" price="" vip="" foreigner="" branch="" value=""><?php echo INPUT_SELECT; ?></option>
                                                        <?php
                                                        $departureSelected = '';
                                                        $sqlJ = mysql_query("SELECT t_journeys.*, t_departure_times.name AS departure FROM t_journeys INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id WHERE t_journeys.status IN (1,2) AND t_journeys.type IN (1,3) AND t_journeys.offline_project_id = ".$user['User']['offline_project_id']." ORDER BY t_journeys.description ASC;");
                                                        while($rowJ = mysql_fetch_array($sqlJ)){
                                                            $departure = date("h:i A", strtotime($rowJ['departure']));
                                                            if($journeyTran['journey'] == $rowJ['id']){
                                                                $departureSelected = $departure;
                                                            }
                                                        ?>
                                                        <option departure="<?php echo $departure; ?>" price="<?php echo number_format($rowJ['unit_price'], 2); ?>" vip="<?php echo number_format($rowJ['membership'], 2); ?>" foreigner="<?php echo number_format($rowJ['foreigner_price'], 2); ?>" branch="<?php echo $rowJ['branch_id']; ?>" value="<?php echo $rowJ['id']; ?>" <?php if($journeyTran['journey'] == $rowJ['id']){ ?>selected=""<?php } ?>><?php echo $rowJ['description']; ?></option>
                                                        <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inputContainer JourneyTransitDeparture" style="width: 100%;">
                                                    <?php echo $departureSelected; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inputContainer" style="width: 100%;">
                                                    <select name="data[journey_transit_date][]" class="JourneyTransitDate validate[required]" id="JourneyTransitDate<?php echo $rnd; ?>">
                                                        <option value=""><?php echo INPUT_SELECT; ?></option>
                                                        <option value="0" <?php if($journeyTran['isNext'] == 0){ ?>selected=""<?php } ?>><?php echo TABLE_SAME_DATE; ?></option>
                                                        <option value="1" <?php if($journeyTran['isNext'] == 1){ ?>selected=""<?php } ?>><?php echo TABLE_NEXT_DATE; ?></option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddRowScheduleTransit" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Add')" />
                                                &nbsp;&nbsp;<img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowScheduleTransit" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                                            </td>
                                        </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>   
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <?php
                    }
                }
            }
            ?>
        </tbody>
    </table>
</fieldset>
<fieldset style="width: 48%; float: right; height: 400px; display: none;">
    <legend><?php __(MENU_AGENT_INFO); ?></legend>
    <table cellpadding="3" style="width: 90%;">
        <tr>
            <td><?php echo TABLE_APPLY_TO_ALL_AGENT; ?> <span class="red">*</span> :</td>
            <td>
                <div class="inputContainer">
                    (KH) <?php //echo $this->Form->text('agent_price_amount', array('class'=>'validate[required] float', 'style' => 'width: 120px', 'value' => number_format($this->data['TJourney']['agent_price_amount'], 2))); ?> <span class="TJourneySymbolCurrency"></span>
                    (FG) <?php //echo $this->Form->text('agetn_price_percent', array('class'=>'validate[required] float', 'style' => 'width: 120px', 'value' => number_format($this->data['TJourney']['agetn_price_percent'], 2))); ?> <span class="TJourneySymbolCurrency"></span>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border-bottom: 2px solid #000;"><?php echo TABLE_PRICE_EACH_AGENCY; ?></td>
        </tr>
        <tr>
            <td colspan="2">
                <div style="width: 100%;">
                    <input type="text" style="width: 300px;" id="searchAgency" placeholder="<?php echo TABLE_SEARCH_AGENCY; ?>" />
                </div>
                <table class="table" cellpadding="0" cellspacing="0" style="width: 100%;">
                    <tr>
                        <th class="first" style="width: 50%;"><?php echo TABLE_NAME; ?></th>
                        <th style="width: 22%;">(KH) <?php echo GENERAL_AMOUNT; ?> (<span class="TJourneySymbolCurrency"></span>)</th>
                        <th style="width: 22%;">(FG) <?php echo TABLE_PERCENT; ?> (<span class="TJourneySymbolCurrency"></span>)</th>
                        <th style="width: 6%;"></th>
                    </tr>
                    <tbody id="rowAgnetList">
                        <tr id="rowListAgent" class="rowListAgent" style="visibility: hidden;">
                            <td class="first">
                                <input type="hidden" name="agent_id[]" class="agentId" />
                                <input type="text" class="agentName" style="width: 90%;" />
                            </td>
                            <td style="text-align: center;">
                                <input type="text" name="agent_amount[]" class="agentAmount validate[required] float" style="width: 90%;" />
                            </td>
                            <td style="text-align: center;">
                                <input type="text" name="agent_percent[]" class="agentPercent validate[required] float" style="width: 90%;" />
                            </td>
                            <td style="text-align: center;">
                                <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowAgent" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                            </td>
                        </tr>
                        <?php
                        foreach($tJourneyAgentPrices AS $tJourneyAgentPrice){
                        ?>
                        <tr class="rowListAgent">
                            <td class="first">
                                <input type="hidden" name="agent_id[]" class="agentId" value="<?php echo $tJourneyAgentPrice['TJourneyAgentPrice']['t_agent_id']; ?>" />
                                <input type="text" class="agentName" style="width: 90%;" value="<?php echo $tJourneyAgentPrice['TAgent']['code']."-".$tJourneyAgentPrice['TAgent']['name']." (".$tJourneyAgentPrice['TAgent']['telephone'].")"; ?>" />
                            </td>
                            <td style="text-align: center;">
                                <input type="text" name="agent_amount[]" value="<?php echo number_format($tJourneyAgentPrice['TJourneyAgentPrice']['amount'], 2); ?>" class="agentAmount validate[required] float" style="width: 90%;" />
                            </td>
                            <td style="text-align: center;">
                                <input type="text" name="agent_percent[]" value="<?php echo number_format($tJourneyAgentPrice['TJourneyAgentPrice']['percent'], 2); ?>" class="agentPercent validate[required] float" style="width: 90%;" />
                            </td>
                            <td style="text-align: center;">
                                <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowAgent" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                            </td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</fieldset>
<div style="clear: both;"></div>
<br />
<?php
if($checkSave){
?>
<div class="buttons">
    <button type="submit" class="positive btnSaveTJourney">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveTJourney"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<?php
}
?>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>