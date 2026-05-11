<?php
// Authentication
$this->element('check_access');
$allowDiscount = checkAccess($user['User']['id'], $this->params['controller'], 'discount');

include("includes/function.php");
echo $this->element('prevent_multiple_submit'); 
$journeyTransits = array();
$i = 0;
foreach($transits AS $transit){
    $sqlJourney = mysql_query("SELECT t_journeys.id, t_journeys.description, t_journeys.t_departure_time_id FROM t_journeys WHERE t_journeys.id = ".$transit['TJourneyTransit']['t_journey_departure_id']);
    if(mysql_num_rows($sqlJourney)){
        $rowJourney = mysql_fetch_array($sqlJourney);
        $journeyTransits[$i]['id'] = $rowJourney['id'];
        $journeyTransits[$i]['description'] = $rowJourney['description'];
        $journeyTransits[$i]['departure'] = $rowJourney['t_departure_time_id'];
        $i++;
    }
}
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Load First
        var journeyId = $("#listJourneyTransit").find('div.divTransitJourney').eq(0).attr('joruney');
        var departure = $("#listJourneyTransit").find('div.divTransitJourney').eq(0).attr('departure');
        var date      = $("#listJourneyTransit").find('div.divTransitJourney').eq(0).attr('date');
        loadTransitDetail(journeyId, departure, date);
        $(".TTicketType").unbind('click').click(function(){
            var val = $(".TTicketType:checked").val();
            $(".ticketBookingAgencyDIv").hide();
            $("#ticketBookingTelephone"+journeyId+", #ticketBookingAgency, #ticketBoookingReference").removeClass("validate[required]");
            if(val == '2'){
                $("#ticketBookingTelephone"+journeyId).addClass("validate[required]");
            } else if (val == '3'){
                $(".ticketBookingAgencyDIv").show();
                $("#ticketBookingAgency, #ticketBoookingReference").addClass("validate[required]");
            }
        });
        <?php
        if($isReturn != 1){
        ?>
        $("#ticketBookingCheckReturn").unbind("click").click(function(){
            $("#ticketBookingReturnOpenDate").attr("checked", false);
            if($(this).is(":checked")){
                $("#ticketBookingReturnDate").removeAttr("disabled");
                $(".ticketBookingOpenDateDiv").show();
                $("#ticketIsReturn").val('2');
            } else {
                $(".ticketBookingOpenDateDiv").hide();
                $("#ticketBookingReturnDate").attr("disabled", true);
                $("#ticketIsReturn").val('1');
            }
        });
        
        $("#ticketBookingReturnOpenDate").unbind("click").click(function(){
            if($(this).is(":checked")){
                $("#ticketBookingReturnDate").attr("disabled", true);
                $("#ticketIsOpenDate").val('1');
            } else {
                $("#ticketBookingReturnDate").removeAttr("disabled");
                $("#ticketIsOpenDate").val('0');
            }
        });
        
        $("#ticketBookingReturnDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        });
        $("#ticketBookingReturnDate").datepicker( "option", "minDate", "<?php echo dateShort($date); ?>" );
        <?php
            }
        ?>
        $("input[name='data[TTicket][price_type]']").unbind("click").click(function(){
            var price = replaceNum($("input[name='data["+journeyId+"][TTicket][price]']").attr('price'));
            if($(this).val() == '2'){
                price = replaceNum($("input[name='data["+journeyId+"][TTicket][price]']").attr('vip'));
            } else if($(this).val() == '3'){
                price = replaceNum($("input[name='data["+journeyId+"][TTicket][price]']").attr('foreigner'));
            }
            $("input[name='data["+journeyId+"][TTicket][price]']").val(converDicemalJS(price).toFixed(2));
            // Appy to Seat
            $(".tblTicketBookingSeatList").each(function(){
                $(this).find(".seatPrice").val(converDicemalJS(price).toFixed(2));
                $(this).find(".seatDiscount").val('0');
                $(this).find(".seatTotal").val(converDicemalJS(price).toFixed(2));
            });
            calTotalAmtBookingTransit();
        });
        
        // Choice Discount Type
//        $("input[name='data[TTicket][discount_type]']").unbind("click").click(function(){
//            var val = $(this).val();
//            $("#ticketBookingDiscountBy, #ticketBookingDisOfferReference").hide();
//            $("#ticketBookingDisOfferReference").val('');
//            $("#ticketBookingDiscountBy").find("option").attr("selected", false);
//            $("#ticketBookingDiscountBy").find("option[value='']").attr("selected", true);
//            if (val == '3'){
//                $("#ticketBookingDiscountBy, #ticketBookingDisOfferReference").show();
//            }
//        });
        
        // Discount Offer
//        $("#ticketBookingDiscountBy").unbind("change").change(function(){
//            var percent = $(this).attr('percent');
//            if($(this).val() != ''){
//                $("#ticketBookingDisPercentLbl").text('('+percent+'%)');
//                $("#ticketBookingDisPercent").val(percent);
//            } else {
//                $("#ticketBookingDisPercentLbl").text('');
//                $("#ticketBookingDisPercent").val('0');
//            }
//        });
        
        // Countiue Transit
        $("#btnContinueTransit").unbind('click').click(function(event){
            event.preventDefault();
            var transitIndex = replaceNum($(this).attr('slc'));
            var totalIndex   = replaceNum($(this).attr('sla'));
            var journeyId    = $("#listJourneyTransit").find('div.divTransitJourney').eq(transitIndex).attr('joruney');
            var departure    = $("#listJourneyTransit").find('div.divTransitJourney').eq(transitIndex).attr('departure');
            var date         = $("#listJourneyTransit").find('div.divTransitJourney').eq(transitIndex).attr('date');
            if(transitIndex <= totalIndex){
                var validateBack = $("#ticketBookingTransitForm").validationEngine("validate");
                if(!validateBack){
                    return false;
                }else{
                    // Check Select Seat
                    var divIndex  = $("#listJourneyTransit").find('div.divTransitJourney').eq(transitIndex - 1).attr('joruney');
                    var firstId   = $("#listJourneyTransit").find('div.divTransitJourney').eq(0).attr('joruney');
                    var totalSeat = $("#ticketBookingTotalSelectedSeat").val();
                    var nextIndex = transitIndex + 1;
                    if($("#divTransitJourney"+divIndex).find(".seatNumber").val() == undefined){
                        $("#dialog2").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_SELECT_SEAT_BF_CONT; ?></p>');
                        $("#dialog2").dialog({
                            title: '<?php echo DIALOG_WARNING; ?>',
                            resizable: false,
                            modal: true,
                            width: 'auto',
                            height: 'auto',
                            position:'center',
                            closeOnEscape: true,
                            open: function(event, ui){
                                $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                            },
                            buttons: {
                                '<?php echo ACTION_CLOSE; ?>': function() {
                                    $(this).dialog("close");
                                }
                            }
                        });
                        return false;
                    } else {
                        // Check select total seat with first journey
                        if(firstId != divIndex){
                            var totalSelected = 0;
                            $(".ticketBookingSeatSelect"+journeyId).each(function(){
                                if($(this).is(':checked')){
                                    totalSelected++;
                                }
                            });
                            if(totalSeat != totalSelected){
                                $("#dialog2").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please select '+totalSeat+' seat(s).</p>');
                                $("#dialog2").dialog({
                                    title: '<?php echo DIALOG_WARNING; ?>',
                                    resizable: false,
                                    modal: true,
                                    width: 'auto',
                                    height: 'auto',
                                    position:'center',
                                    closeOnEscape: true,
                                    open: function(event, ui){
                                        $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                                    },
                                    buttons: {
                                        '<?php echo ACTION_CLOSE; ?>': function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                                return false;
                            }
                        }
                        $(this).attr('slc', nextIndex);
                        if(transitIndex == totalIndex){
                            $(this).hide();
                            $("#btnSaveTransit").show();
                            // Disabled Journey Type, Price Type
                            $(".TTicketType, input[name='data[TTicket][price_type]']").attr('disabled', true);
                        }
                        loadTransitDetail(journeyId, departure, date);
                    }
                }
            }
        });
        
        $("#btnSaveTransit").unbind('click').click(function(event){
            event.preventDefault();
            var validateBack = $("#ticketBookingTransitForm").validationEngine("validate");
            if(!validateBack){
                return false;
            }else{
                var transitIndex = replaceNum($("#btnContinueTransit").attr('slc')) - 1;
                var divIndex     = $("#listJourneyTransit").find('div.divTransitJourney').eq(transitIndex).attr('joruney');
                var firstId      = $("#listJourneyTransit").find('div.divTransitJourney').eq(0).attr('joruney');
                var totalSeat    = $("#ticketBookingTotalSelectedSeat").val();
                // Check Select Seat
                if($("#divTransitJourney"+divIndex).find(".seatNumber").val() == undefined){
                    $("#dialog2").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_SELECT_SEAT_BF_SAVE; ?></p>');
                    $("#dialog2").dialog({
                        title: '<?php echo DIALOG_WARNING; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position:'center',
                        closeOnEscape: true,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                    return false;
                } else {
                    // Check select total seat with first journey
                    if(firstId != divIndex){
                        var totalSelected = 0;
                        $(".ticketBookingSeatSelect"+divIndex).each(function(){
                            if($(this).is(':checked')){
                                totalSelected++;
                            }
                        });
                        if(totalSeat != totalSelected){
                            $("#dialog2").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please select '+totalSeat+' seat(s).</p>');
                            $("#dialog2").dialog({
                                title: '<?php echo DIALOG_WARNING; ?>',
                                resizable: false,
                                modal: true,
                                width: 'auto',
                                height: 'auto',
                                position:'center',
                                closeOnEscape: true,
                                open: function(event, ui){
                                    $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                                },
                                buttons: {
                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                        $(this).dialog("close");
                                    }
                                }
                            });
                            return false;
                        }
                    }
                    $("#dialog").dialog("close");
                    $(".float").each(function(){
                        $(this).val($(this).val().replace(/,/g,""));
                    });
                    if($("#ticketIsReturn").val() == '2' && $("#ticketIsOpenDate").val() == '0'){
                        $("#ticketBookingReturnDate").datepicker("option", "dateFormat", "yy-mm-dd");
                    }
                    // Disabled Journey Type, Price Type
                    $(".TTicketType, input[name='data[TTicket][price_type]']").attr('disabled', false);
                    var post = $("#ticketBookingTransitForm").serialize();
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url:  "<?php echo $this->base . "/t_tickets/"; ?>addTransit/<?php echo $journey['TJourney']['id']; ?>/<?php echo $date; ?>",
                        data: post,
                        beforeSend: function(arr, $form, options) {
                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                            // modal box - open
                            $("#dialogModal").html('<p style="text-align: center;"><img alt="" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" /></p>');
                            $("#dialogModal").dialog({
                                title: '<?php echo DIALOG_LOADING; ?>',
                                resizable: false,
                                modal: true,
                                closeOnEscape: false,
                                width: 180,
                                height: 80,
                                open: function(event, ui){
                                    $(".ui-dialog-buttonpane").show();
                                    $(".ui-dialog-titlebar-close").hide();
                                },
                                close: function(event, ui){
                                    $(".ui-dialog-titlebar-close").show();
                                }
                            });
                        },
                        error: function (result) {
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                            $("#dialogModal").dialog("close");
                        },
                        success: function(result){
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                            if(result.error == '0'){
                                // Refresh Schedule
                                $("#ticketBookingSearch").click();
                                var destFromId  = result.dest_from_id;
                                var destToId    = result.dest_to_id;
                                var priceSym    = $("#ticketPriceSymbol").val();
                                var ticketId    = '';
                                <?php
                                $k = 0;
                                foreach($journeyTransits AS $journeyTransit){
                                    if($k > 0){
                                        $isPageBreak = 1;
                                    } else {
                                        $isPageBreak = 0;
                                    }
                                ?>
                                ticketId = result[<?php echo $journeyTransit['id']; ?>]['id'];
                                var reference  = result[<?php echo $journeyTransit['id']; ?>]['sys_code'];
                                var ticketCode = result[<?php echo $journeyTransit['id']; ?>]['ticket_code'];
                                <?php
                                if(SERVER_TYPE != "1"){
                                ?>
                                // Update seats af save ticket to cloud
                                saveSeatBookCloud(reference, ticketCode);
                                <?php
                                }
                                ?>
                                printLayout += generalPrintTransit(result, '<?php echo $journeyTransit['id']; ?>', '<?php echo $isPageBreak; ?>');
                                <?php
                                    $k++;
                                }
                                if($isReturn != 1){
                                ?>
                                if($("#ticketIsReturn").val() == '2' && $("#ticketIsOpenDate").val() == '0'){
                                    // Load Return Schedule
                                    $.ajax({
                                        type: "GET",
                                        url: "<?php echo $this->base . '/'; ?>t_tickets/viewSchedule/"+destToId+"/"+destFromId+"/"+$("#ticketBookingReturnDate").val()+"/1",
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        success: function(result){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            $("#dialogModal").dialog("close");
                                            $("#dialog").html(result);
                                            $("#dialog").dialog({
                                                title: 'Return Schedule',
                                                resizable: false,
                                                closeOnEscape: false,
                                                modal: true,
                                                width: '1024',
                                                height: '680',
                                                open: function(event, ui){
                                                    $(".ui-dialog-titlebar-close").hide();
                                                    $(".ui-dialog-buttonpane").hide();
                                                }
                                            });
                                        }
                                    });
                                } else if($("#ticketIsReturn").val() == '2' && $("#ticketIsOpenDate").val() == '1'){
                                    if(ticketId != ''){
                                        // Save Open Date
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "<?php echo $this->base . '/'; ?>t_tickets/addReturnOpen/"+destToId+"/"+destFromId+"/"+ticketId,
                                            beforeSend: function(){
                                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                            },
                                            success: function(result){
                                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                $("#dialogModal").dialog("close");
                                                if(result.error == '0'){
                                                    // Refresh Schedule
                                                    $("#ticketBookingSearch").click();
                                                    // Get Response
                                                    var companyName = result.company;
                                                    var companyWeb  = result.website;
                                                    var companyType = result.company_type;
                                                    var branchFName = result.branch_from;
                                                    var branchFTel  = result.branch_from_tel;
                                                    var branchTName = result.branch_to;
                                                    var branchTTel  = result.branch_to_tel;
                                                    var destiFName  = result.dest_from;
                                                    var destiTName  = result.dest_to;
                                                    var travelDate  = result.travel_date;
                                                    var createdBy   = result.created_by;
                                                    var ticketTypeC = result.ticket_type;
                                                    var ticketCode  = result.ticket_code;
                                                    var totalSeat   = result.total_seat;
                                                    var printDate   = result.print_date;
                                                    var referenceC  = result.agency_ref;
                                                    var priceType   = result.price_type;
                                                    var seatPrice   = result.price+" "+priceSym;
                                                    var comType     = '';
                                                    var customerType = '';
                                                    if(companyType == '1'){
                                                        comType = 'BUS TICKET';
                                                    } else {
                                                        comType = 'SPEED FERRY TICKET';
                                                    }
                                                    // Customet Type
                                                    if(priceType == '1'){
                                                        customerType = 'Walk In';
                                                    } else if(priceType == '2'){
                                                        customerType = 'Phone Call';
                                                    } else if(priceType == '3'){
                                                        customerType = 'Agency';
                                                    }
                                                    var boarding = '';
                                                    var dropOff  = '';
                                                    // Layout Print
                                                    var direction = destiFName+" -> "+destiTName;
                                                    // Header Seat Print
                                                    printLayout += generalLayoutPrintTicket('breakPage', companyName, comType, companyWeb, ticketTypeC, ticketCode, branchFName, customerType, customerTel, referenceC, travelDate);
                                                    // Seat Info Print
                                                    printLayout += generateLayoutPrintSeatOpen(totalSeat, direction, dropOff, boarding, seatPrice);
                                                    // Footer Print
                                                    printLayout += generateLayoutPrintFooter(branchFName, branchFTel, branchTName, branchTTel, printDate, createdBy);
                                                    if(printLayout != ''){
                                                        var w = window.open();
                                                        w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                                        w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                                        w.document.write('<style type="text/css" media="screen">div.print-footer {display: none;}<\/style> ');
                                                        w.document.write('<style type="text/css" media="print">div.print_doc { width:100%;}#btnDisappearPrint { display: none;}div.print-footer {display: block; width:100%;}.breakPage {page-break-before: always;}<\/style>');
                                                        w.document.write('<div class="print_doc" style="width: 300px;">'+printLayout+'</div>');
                                                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"><\/script>');
                                                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"><\/script>');
                                                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_ticket.js"><\/script>');
                                                        w.document.close();
                                                        printLayout = '';
                                                    }
                                                } else {
                                                    if(printLayout != ''){
                                                        var w = window.open();
                                                        w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                                        w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                                        w.document.write('<style type="text/css" media="screen">div.print-footer {display: none;}<\/style> ');
                                                        w.document.write('<style type="text/css" media="print">div.print_doc { width:100%;}#btnDisappearPrint { display: none;}div.print-footer {display: block; width:100%;}.breakPage {page-break-before: always;}<\/style>');
                                                        w.document.write('<div class="print_doc" style="width: 300px;">'+printLayout+'</div>');
                                                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"><\/script>');
                                                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"><\/script>');
                                                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_ticket.js"><\/script>');
                                                        w.document.close();
                                                        printLayout = '';
                                                    }
                                                    dialogMessage('<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>');
                                                }
                                            }
                                        });
                                    }
                                } else {
                                    $("#dialogModal").dialog("close");
                                    if(printLayout != ''){
                                        var w = window.open();
                                        w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                        w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                        w.document.write('<style type="text/css" media="screen">div.print-footer {display: none;}<\/style> ');
                                        w.document.write('<style type="text/css" media="print">div.print_doc { width:100%;}#btnDisappearPrint { display: none;}div.print-footer {display: block; width:100%;}.breakPage {page-break-before: always;}<\/style>');
                                        w.document.write('<div class="print_doc" style="width: 300px;">'+printLayout+'</div>');
                                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"><\/script>');
                                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"><\/script>');
                                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_ticket.js"><\/script>');
                                        w.document.close();
                                        printLayout = '';
                                    }
                                }
                                <?php
                                } else {
                                ?>
                                $("#dialogModal").dialog("close");
                                if(printLayout != ''){
                                    var w = window.open();
                                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                    w.document.write('<style type="text/css" media="screen">div.print-footer {display: none;}<\/style> ');
                                    w.document.write('<style type="text/css" media="print">div.print_doc { width:100%;}#btnDisappearPrint { display: none;}div.print-footer {display: block; width:100%;}.breakPage {page-break-before: always;}<\/style>');
                                    w.document.write('<div class="print_doc" style="width: 300px;">'+printLayout+'</div>');
                                    w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"><\/script>');
                                    w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"><\/script>');
                                    w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_ticket.js"><\/script>');
                                    w.document.close();
                                    printLayout = '';
                                }
                                <?php
                                }
                                ?>
                            } else {
                                dialogMessage('<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>');
                            }
                        }
                    });
                    return false;
                }
            }
        });
        
        $("#btnCancelTransit").unbind('click').click(function(event){
            event.preventDefault();
            $("#dialog").dialog("close");
            <?php
            if($isReturn == 1){
            ?>
            if(printLayout != ''){
                var w = window.open();
                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                w.document.write('<style type="text/css" media="screen">div.print-footer {display: none;}<\/style> ');
                w.document.write('<style type="text/css" media="print">div.print_doc { width:100%;}#btnDisappearPrint { display: none;}div.print-footer {display: block; width:100%;}.breakPage {page-break-before: always;}<\/style>');
                w.document.write('<div class="print_doc" style="width: 300px;">'+printLayout+'</div>');
                w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"><\/script>');
                w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"><\/script>');
                w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_ticket.js"><\/script>');
                w.document.close();
                printLayout = '';
            }
            <?php
            }
            ?>
        });
    });
    
    function loadTransitDetail(journeyId, departure, date){
        var journeyFirstId = $("#listJourneyTransit").find('div.divTransitJourney').eq(0).attr('joruney');
        $.ajax({
            type: "GET",
            url: "<?php echo $this->base . '/'; ?>t_tickets/transitDetail/"+journeyId+"/"+departure+"/"+date,
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                $("#btnContinueTransit, #btnSaveTransit, #btnCancelTransit").attr('disabled', true);
                $(".spanTransitJourney").css('font-weight', 'normal');
                $(".divTransitJourney").hide();
                $("#divTransitJourney"+journeyId).show();
                // Disabled Journey Type, Price Type
                if(journeyFirstId == journeyId){
                    $(".TTicketType, input[name='data[TTicket][price_type]']").attr('disabled', true);
                }
            },
            success: function(result){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#btnContinueTransit, #btnSaveTransit, #btnCancelTransit").attr('disabled', false);
                $("#spanTransitJourney"+journeyId).css('font-weight', 'bold');
                $("#divTransitJourney"+journeyId).html(result).show();
                // Disabled Journey Type, Price Type
                if(journeyFirstId == journeyId){
                    $(".TTicketType, input[name='data[TTicket][price_type]']").attr('disabled', false);
                }
            }
        });
    }
    
    function calTotalAmtBookingTransit(){
        var totalAmt = 0;
        var totalDis = replaceNum($("#ticketBookingTotalDiscount").val());
        var disPercent = replaceNum($("#ticketBookingDisPercent").val());
        $(".tblTicketBookingSeatList").each(function(){
            var totalSeatPrice = replaceNum($(this).find(".seatPrice").val()) - replaceNum($(this).find(".seatDiscount").val());
            $(this).find(".seatTotal").val(converDicemalJS(totalSeatPrice).toFixed(2));
            totalAmt += totalSeatPrice;
        });
        if(disPercent > 0){
            totalDis = converDicemalJS(converDicemalJS(totalAmt * disPercent) / 100).toFixed(2);
            $("#ticketBookingTotalDiscount").val(totalDis);
        }
        var netAmt = totalAmt - replaceNum(totalDis);
        $("#ticketBookingTotalAmount").val(converDicemalJS(totalAmt).toFixed(2));
        $("#ticketBookingNetAmount").val(converDicemalJS(netAmt).toFixed(2));
    }
    
    function generalPrintTransit(result, indexJourney, isPageBreak){
        var printLayout = '';
        // Get Response
        var companyName = result[indexJourney]['company'];
        var companyWeb  = result[indexJourney]['website'];
        var companyType = result[indexJourney]['company_type'];
        var branchFName = result[indexJourney]['branch_from'];
        var branchFTel  = result[indexJourney]['branch_from_tel'];
        var branchTName = result[indexJourney]['branch_to'];
        var branchTTel  = result[indexJourney]['branch_to_tel'];
        var destiFCode  = result[indexJourney]['dest_from_code'];
        var destiFName  = result[indexJourney]['dest_from'];
        var destiTCode  = result[indexJourney]['dest_to_code'];
        var destiTName  = result[indexJourney]['dest_to'];
        var bookingDate = result[indexJourney]['booking_date'];
        var travelDate  = result[indexJourney]['travel_date'];
        var createdBy   = result[indexJourney]['created_by'];
        var ticketTypeC = result[indexJourney]['ticket_type'];
        var ticketCode  = result[indexJourney]['ticket_code'];
        var transportT  = result[indexJourney]['trans_type'];
        var printDate   = result[indexJourney]['print_date'];
        var printName   = result[indexJourney]['printer_name'];
        var printSilent = result[indexJourney]['printer_silent'];
        // Get From Form
        var customerTel  = $("#ticketBookingTelephone"+indexJourney).val();
        var referenceC   = $("#ticketBoookingReference").val();
        var customerType = '';
        var noPaidLabel  = '';
        // Customet Type
        if($("input[name='data[TTicket][price_type]']:checked").val() == '1'){
            customerType = 'Walk In';
        } else if($("input[name='data[TTicket][price_type]']:checked").val() == '2'){
            customerType = 'Phone Call';
            noPaidLabel  = '(Not Yet Paid)';
        } else if($("input[name='data[TTicket][price_type]']:checked").val() == '3'){
            customerType = 'Agency';
        }
        var boarding = $("#ticketBookingBoardingPoint"+indexJourney).text();
        var dropOff  = $("#ticketBookingDropOff"+indexJourney).text();
        var priceSym = $("#ticketPriceSymbol").val();
        var destinationCode = destiFCode+" -> "+destiTCode;
        var direction = destiFName+" -> "+destiTName;
        if($(".tblTicketBookingSeatList"+indexJourney).find(".seatNumber"+indexJourney).val() != undefined){
            var row = 1;
            var rowCount = $(".tblTicketBookingSeatList"+indexJourney).length;
            printLayout += printTicketSetting(printName, printSilent, '1');
            $(".tblTicketBookingSeatList"+indexJourney).each(function(){
                var seatNumber = $(this).find(".seatLabel"+indexJourney).val();
                var seatPrice  = $(this).find(".seatTotal"+indexJourney).val()+" "+priceSym;
                var brackPage  = '';
                var ticketNo   = ticketCode;
                var comType    = '';
                if(rowCount > 1){
                    ticketNo   = ticketCode+"-"+row;
                }
                if(row > 1 || isPageBreak == '1'){
                    brackPage  = 'breakPage';
                }
                if(companyType == '1'){
                    comType = 'BUS TICKET';
                } else {
                    comType = 'SPEED FERRY TICKET';
                }
                // Header Print
                printLayout += geneateLayoutPrintHead(brackPage, destinationCode, ticketNo, bookingDate, travelDate, customerType, seatNumber, seatPrice, referenceC, createdBy);
                // Header Seat Print
                printLayout += generalLayoutPrintTicket('breakPage', companyName, comType, companyWeb, ticketTypeC, ticketNo, branchFName, customerType, customerTel, referenceC, travelDate);
                // Seat Info Print
                printLayout += generateLayoutPrintSeat(seatNumber, transportT, direction, dropOff, boarding, seatPrice, noPaidLabel);
                // Footer Print
                printLayout += generateLayoutPrintFooter(branchFName, branchFTel, branchTName, branchTTel, printDate, createdBy);
                row++;
            });
        }
        return printLayout;
    }
</script>
<br />
<div style="width: 995px;">
    <form id="ticketBookingTransitForm" method="post" action="#">
        <input type="hidden" id="ticketPriceSymbol" value="<?php echo $journey['CurrencyCenter']['symbol']; ?>" />
        <input type="hidden" name="data[TTicket][round_trip]" id="ticketIsReturn" value="1" />
        <input type="hidden" name="data[TTicket][return]" value="<?php echo $isReturn; ?>" />
        <input type="hidden" id="ticketIsOpenDate" name="data[TTicket][is_open_date]" value="0" />
        <table cellpadding="5" cellspacing="0" style="width: 100%;">
            <tr>
                <td colspan="2">
                    <?php
                    if($user['User']['type'] != 3 || $user['User']['type'] != 4){
                    ?>
                    <input type="radio" checked="" name="data[TTicket][type]" id="TTicketWalkIn" class="TTicketType" value="1" /><label for="TTicketWalkIn">Walk In</label>
                    <input type="radio" name="data[TTicket][type]" id="TTicketBooking" class="TTicketType" value="2" /><label for="TTicketBooking">Phone Call</label>
                    <input type="radio" name="data[TTicket][type]" id="TTicketAgency" class="TTicketType" value="3" /><label for="TTicketAgency">Agency</label>
                    <?php
                    } else {
                    ?>
                    <input type="radio" name="data[TTicket][type]" id="TTicketAgency" class="TTicketType" value="3" /><label for="TTicketAgency">Agency</label>
                    <?php
                    }
                    ?>
                </td>
                <td style="width: 9%;"><?php echo TABLE_TRAVEL_DATE; ?> :</td>
                <td style="width: 15%;">
                    <?php echo dateShort($date)." ".date("h:i A", strtotime($departure['TDepartureTime']['name'])); ?>
                </td>
                <td style="width: 11%;">
                    <?php
                    if($isReturn != 1){
                    ?>
                    <label for="ticketBookingReturnDate"><?php echo TABLE_ROUND_TRIP; ?></label> <input type="checkbox" id="ticketBookingCheckReturn" />:
                    <?php
                    }
                    ?>
                </td>
                <td style="width: 15%;">
                    <?php
                    if($isReturn != 1){
                    ?>
                    <input type="text" style="width: 70px; height: 20px;" id="ticketBookingReturnDate" placeholder="<?php echo TABLE_RETURN_DATE; ?>" disabled="" /> 
                    <input type="checkbox" id="ticketBookingReturnOpenDate" class="ticketBookingOpenDateDiv" style="display: none;" /><label style="display: none;" for="ticketBookingReturnOpenDate" class="ticketBookingOpenDateDiv">Open</label>
                    <?php
                    }
                    ?>
                </td>
                <td style="width: 5%;">Note :</td>
                <td rowspan="2" style="vertical-align: top;">
                    <textarea style="height: 50px; width: 90%;" name="data[TTicket][note]"></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="height: 30px;">
                    <?php echo TABLE_PRICE_TYPE; ?> : 
                    <input type="radio" name="data[TTicket][price_type]" value="1" id="ticketBookingPriceTypeDef" checked="" /> <label for="ticketBookingPriceTypeDef">Def</label>
                    <input type="radio" name="data[TTicket][price_type]" value="2" id="ticketBookingPriceTypeVip" /> <label for="ticketBookingPriceTypeVip">VIP</label>
                    <input type="radio" name="data[TTicket][price_type]" value="3" id="ticketBookingPriceTypeFgn" /> <label for="ticketBookingPriceTypeFgn">Foreigner</label>
                </td>
                <td><label for="ticketBookingAgency" class="ticketBookingAgencyDIv" style="display: none;"><?php echo MENU_AGENT; ?> :</label></td>
                <td>
                    <div class="inputContainer ticketBookingAgencyDIv" style="width: 100%; display: none;">
                        <select style="width: 95%; height: 25px;" id="ticketBookingAgency" name="data[TTicket][t_agent_id]">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            $sqlAg = mysql_query("SELECT t_agents.id, t_agents.name FROM t_agents WHERE t_agents.is_active = 1 AND t_agents.type = 2");
                            while($rowAg = mysql_fetch_array($sqlAg)){
                            ?>
                            <option value="<?php echo $rowAg['id']; ?>"><?php echo $rowAg['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td colspan="2">
                    <div class="inputContainer ticketBookingAgencyDIv" style="width: 100%; display: none;">
                        <input type="text" name="data[TTicket][agt_refer_code]" style="width: 95%; height: 15px;" id="ticketBoookingReference" placeholder="<?php echo TABLE_AGENT_REFERENCE; ?>" /> 
                    </div>
                </td>
            </tr>
        </table>
        <br/>
        <fieldset style="height: 400px;" id="listJourneyTransit">
            <legend>
                <?php
                $j = 0;
                $totalJourney = COUNT($journeyTransits) - 1;
                foreach($journeyTransits AS $journeyTransit){
                ?>
                <span class="spanTransitJourney" id="spanTransitJourney<?php echo $journeyTransit['id']; ?>" style="padding: 3px; background: #CCCCCC; <?php if($j == 0){ ?>font-weight: bold;<?php } ?>"><?php echo $journeyTransit['description']; ?></span>
                <?php
                    $j++;
                }
                ?>
            </legend>
            <?php
            foreach($journeyTransits AS $journeyTransit){
                $sqlTJ = mysql_query("SELECT name FROM t_departure_times WHERE id = (SELECT t_departure_time_id FROM t_journeys WHERE id = ".$journeyTransit['id']." LIMIT 1)");
                $rowTJ = mysql_fetch_array($sqlTJ);
                if(strtotime($rowTJ['name']) < strtotime($journey['TDepartureTime']['name'])){
                    $travelDate = date('Y-m-d', strtotime($date . ' +1 day'));
                } else {
                    $travelDate = $date;
                }
            ?>
            <div class="divTransitJourney" id="divTransitJourney<?php echo $journeyTransit['id']; ?>" joruney="<?php echo $journeyTransit['id']; ?>" departure="<?php echo $journeyTransit['departure']; ?>" date="<?php echo $travelDate; ?>" style="width: 100%; display: none;">
                <img src="<?php echo $this->webroot; ?>img/ajax-loader.gif" alt="" style="width:128px; height: 15px; position:absolute; left:50%; top:50%;  margin-left:-64px; margin-top:-7.5px;" />
            </div>
            <?php
            }
            ?>
        </fieldset>
        <table cellpadding="3" cellspacing="0" style="width: 100%;">
            <tr>
                <td colspan="4" style="height: 25px;">
                    <div style="display: none;">
                        <input type="radio" name="data[TTicket][discount_type]" value="1" id="discountTypeNone" checked="" /> <label for="discountTypeNone"><?php echo INPUT_NONE; ?></label>
                        <input type="radio" name="data[TTicket][discount_type]" value="3" id="discountTypeCard" /> <label for="discountTypeCard"><?php echo TABLE_DISCOUNT_OFFER; ?></label> 
                        <select style="height: 25px; width: 145px; display: none;" id="ticketBookingDiscountBy" name="data[TTicket][discount_offer_id]">
                            <option value="" percent=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            $sqlDis = mysql_query("SELECT id, name, percent FROM discount_offers WHERE is_active = 1");
                            while($rowDis = mysql_fetch_array($sqlDis)){
                            ?>
                            <option value="<?php echo $rowDis['id']; ?>" percent="<?php echo $rowDis['percent']; ?>"><?php echo $rowDis['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <input type="text" name="data[TTicket][discount_offer_refer]" style="width: 220px; height: 12px; display: none;" id="ticketBookingDisOfferReference" placeholder="<?php echo TABLE_DISCOUNT_REFERENCE; ?>" />
                    </div>
                </td>
                <td><?php echo TABLE_TOTAL_SEAT; ?> :</td>
                <td>
                    <input type="text" style="width: 80%; height: 15px; border: none; font-size: 14px; font-weight: bold;" value='0' id="ticketBookingTotalSelectedSeat" class="float" readonly="" />
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_TOTAL_AMOUNT; ?> :</td>
                <td>
                    <input type="text" style="width: 80%; height: 20px; border: none; font-size: 14px; font-weight: bold;" value='0' id="ticketBookingTotalAmount" class="float" readonly="" /> <?php echo $journey['CurrencyCenter']['symbol']; ?>
                </td>
                <td><?php echo GENERAL_DISCOUNT; ?> <span id="ticketBookingDisPercentLbl"></span> :</td>
                <td>
                    <input type="hidden" name="data[discount_percent]" id="ticketBookingDisPercent" class="float" value="0" />
                    <input type="text" name="data[discount_amount]" style="width: 80%; height: 20px; border: none; font-size: 14px; font-weight: bold;" value='0' id="ticketBookingTotalDiscount" class="float" readonly="" /> <?php echo $journey['CurrencyCenter']['symbol']; ?>
                </td>
                <td><?php echo TABLE_NET_AMOUNT; ?> :</td>
                <td>
                    <input type="text" style="width: 80%; height: 20px; border: none; font-size: 14px; font-weight: bold;" value='0' id="ticketBookingNetAmount" class="float" readonly="" /> <?php echo $journey['CurrencyCenter']['symbol']; ?>
                </td>
            </tr>
        </table>
        <hr style="width: 100%; background-color: #CCCCCC;" />
        <button style="width: 90px; height: 28px; cursor: pointer; float: right; margin-left: 5px;" id="btnCancelTransit">
            Cancel
        </button>
        <button style="width: 90px; height: 28px; cursor: pointer; float: right; margin-left: 5px;" id="btnContinueTransit" slc="1" sla="<?php echo $totalJourney; ?>">
            Continue
        </button>
        <button style="width: 90px; height: 28px; cursor: pointer; float: right; display: none;" id="btnSaveTransit">
            Save
        </button>
        <div style="clear: both;"></div>
    </form>
</div>
<div style="clear: both;"></div>