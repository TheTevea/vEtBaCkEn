<?php
// Authentication
$this->element('check_access');
$defaultSelect = "";
if($user['User']['main_branch_id']){
    $sqlDesFrom = mysql_query("SELECT id FROM t_destinations WHERE id = (SELECT t_destination_id FROM main_branches WHERE id = ".$user['User']['main_branch_id']." LIMIT 1)");
    $rowDesFrom = mysql_fetch_array($sqlDesFrom);
    $defaultSelect = $rowDesFrom[0];
}
$allowViewHistory = checkAccess($user['User']['id'], $this->params['controller'], 'viewTicketHistory');
$time = date("H");
if($time >= 0 && $time <= 4){
    $dateNow    = date("Y-m-d");
    $dateSelect = date('d/m/Y', strtotime('-1 day', strtotime($dateNow)));
} else {
    $dateSelect = date("d/m/Y");
}
?>
<script type="text/javascript">
    var printLayout = '';
    $(document).ready(function(){
        $("#ticketBookingBranch").filterOptions('com', '', '');
         $(".ui-dialog").remove();
        $("#dialog, #dialog1").html("");
        // Chosen
        $("#ticketBookingDestinationFrom, #ticketBookingDestinationTo").chosen({width: 250});
        <?php
        if($user['User']['type'] == 2){
        ?>
        $("#ticketBookingCompany, #ticketBookingBranch").chosen({width: 250});
        $("#ticketBookingDepartureTime").chosen({width: 160});
        $("#ticketBookingCompany").unbind('change').change(function(){
            var obj = $(this);
            $("#ticketBookingBranch").filterOptions('com', obj.val(), '');
            $("#ticketBookingBranch").trigger("chosen:updated");
        });
        <?php
        }
        ?>
        // Destination From 
        $("#ticketBookingDestinationFrom").unbind('change').change(function(){
            var obj = $(this);
            if(obj.val() != ''){
                $.ajax({
                    type: "GET",
                    url: "<?php echo $this->base . '/'; ?>t_tickets/getDestinationTo/"+obj.val(),
                    beforeSend: function(){
                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        $("#divDestinationTo").html('<img src="<?php echo $this->webroot; ?>img/ajax-loader.gif" alt="" style="width:128px; height: 15px; margin-left: 450px;" />');
                    },
                    success: function(result){
                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                        $("#ticketBookingDestinationTo").html(result);
                        $("#ticketBookingDestinationTo").trigger("chosen:updated");
                    }
                });
            } else {
                $("#ticketBookingDestinationTo").html('<option value=""><?php echo REPORT_TO; ?></option>');
            }
        });
        <?php
        if(COUNT($tDestinationFroms) == 1 || $defaultSelect != ""){
        ?>
        $("#ticketBookingDestinationFrom").change();
        <?php
        }
        ?>
        var tabWidth = $(".ui-layout-center").height() - 210;
        $("#journeyScheduleList").css("height", tabWidth);
        $("#journeyScheduleList").niceScroll({cursorborder:"", cursorcolor:"#0063dc", boxzoom:false});
        // Date
        $("#ticketBookingDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        });
        $("#ticketBookingDate").datepicker( "option", "minDate", '<?php echo $dateSelect; ?>');
        // Search Schedule
        $("#ticketBookingSearch").unbind('click').click(function(){
            var from = $("#ticketBookingDestinationFrom").find("option:selected").val();
            var to   = $("#ticketBookingDestinationTo").find("option:selected").val();
            var date = $("#ticketBookingDate").val().toString().split("/")[2]+"-"+$("#ticketBookingDate").val().toString().split("/")[1]+"-"+$("#ticketBookingDate").val().toString().split("/")[0];;
            var com  = $("#ticketBookingCompany").val();
            var bra  = $("#ticketBookingBranch").val();
            var time = $("#ticketBookingDepartureTime").val();
            var obj  = $(this);
            if(from != '' && to != '' && date != ''){
                $.ajax({
                    type: "GET",
                    url: "<?php echo $this->base . '/'; ?>t_tickets/viewSchedule/"+from+"/"+to+"/"+date+"/0?company="+com+"&branch="+bra+"&departure="+time,
                    beforeSend: function(){
                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        $("#journeyScheduleList").html('<img src="<?php echo $this->webroot; ?>img/ajax-loader.gif" alt="" style="width:128px; height: 15px; position:absolute; left:50%; top:50%;  margin-left:-64px; margin-top:-7.5px;" />');
                        obj.attr('disabled', true);
                    },
                    success: function(result){
                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                        obj.attr('disabled', false);
                        $("#journeyScheduleList").html(result);
                    }
                });
            } else {
                dialogMessage('<?php echo MESSAGE_SELECT_DESTINATIO_AND_DATE; ?>');
            }
        });
        // View History
        $("#btnViewTicketHistory").unbind("click").click(function(event){
            event.preventDefault();
            var obj   = $(this);
            var found = false;
            $('#tabs a').not("[href=#]").each(function() {
                if(obj.find('span').text()=="<?php echo MENU_DASHBOARD; ?>"){
                    found=true;
                    $("#tabs").tabs("select", 0);
                }else if(obj.attr("href") == $.data(this, 'href.tabs')){
                    found=true;
                    $("#tabs").tabs("select", $(this).attr("href"));
                }
            });
            if(found==false){
                $("#tabs").tabs("add", $(this).attr("href"), $(this).find('span').text());
            }
        });
        // Find Booking
        $("#btnFindTicketBooked").unbind("click").click(function(event){
            event.preventDefault();
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/findBooks/",
                data: "",
                beforeSend: function(){
                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
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
                success: function(result){
                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                    $("#dialogModal").dialog("close");
                    $("#dialog").html(result);
                    $("#dialog").dialog({
                        title: '<?php echo ACTION_FIND_TICKET_PHONE; ?>',
                        resizable: false,
                        modal: true,
                        width: 1024,
                        height: 600,
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        });
    });
    
    function ticketEvent(){
        // Booking 
        $(".btnTicketBooking").unbind('click').click(function(){
            bookingTicket($(this), 0);
        });
        
        $(".btnTicketBookingTransit").unbind('click').click(function(){
            bookingTransit($(this), 0);
        });
        
        $(".btnViewTicketPlan").unbind("click").click(function(event){
            event.preventDefault();
            var id   = $(this).attr('rel');
            var date = $("#ticketBookingDate").val().toString().split("/")[2]+"-"+$("#ticketBookingDate").val().toString().split("/")[1]+"-"+$("#ticketBookingDate").val().toString().split("/")[0];
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/schedules'; ?>/printSchedule/"+id+"/"+date,
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
    }
    
    function bookingTicket(obj, delay){
        var journeyId   = obj.attr('j-id');
        var departureId = obj.attr('t-id');
        var isReturn    = obj.attr('is-return');
        var title = obj.attr('act');
        var date  = obj.attr('date');
        $(".ui-dialog").remove();
        $("#dialog, #dialog1").html("");
        $.ajax({
            type: "GET",
            url: "<?php echo $this->base . '/'; ?>t_tickets/booking/"+journeyId+"/"+departureId+"/"+date+"/"+isReturn,
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                if(delay == 0){
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
                }
            },
            success: function(result){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#dialogModal").dialog("close");
                $("#dialog").html(result);
                $("#dialog").dialog({
                    title: title,
                    resizable: false,
                    closeOnEscape: false,
                    modal: true,
                    width: '1024',
                    height: '740',
                    open: function(event, ui){
                        $(".ui-dialog-titlebar-close").hide();
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        '<?php echo ACTION_SAVE; ?>': function() {
                            // Remove all whitespace from telephone before validation
                            $("#ticketBookingTelephone").val($("#ticketBookingTelephone").val().replace(/\s+/g, ''));
                            var validateBack = $("#ticketBookingForm").validationEngine("validate");
                            var ticketType   = $(".TTicketType:checked").val();
                            if(!validateBack){
                                return false;
                            }else{
                                if(ticketType == '3' && $("#ticketBookingAgency").val() == ""){
                                    $("#dialog2").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PLEASE_SELECT_VENDOR; ?></p>');
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
                                    // Check Select Seat
                                    if($(".tblTicketBookingSeatList").find(".seatNumber").val() == undefined){
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
                                    }else{
                                        $(this).dialog("close");
                                        $(".float").each(function(){
                                            $(this).val($(this).val().replace(/,/g,""));
                                        });
                                        if($("#ticketIsReturn").val() == '2' && $("#ticketIsOpenDate").val() == '0'){
                                            $("#ticketBookingReturnDate").datepicker("option", "dateFormat", "yy-mm-dd");
                                        }
                                        // Passenger DOB
                                        $(".seatDob").datepicker("option", "dateFormat", "yy-mm-dd");
                                        var post = $("#ticketBookingForm").serialize();
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url:  "<?php echo $this->base . "/t_tickets/"; ?>add/"+journeyId+"/"+departureId+"/"+date,
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
                                                createSysAct('Ticket Booking', 'Add', 2, result.responseText);
                                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                                $("#dialogModal").dialog("close");
                                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                                $("#dialog").dialog({
                                                    title: '<?php echo DIALOG_INFORMATION; ?>',
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
                                                            $("meta[http-equiv='refresh']").attr('content','0');
                                                            $(this).dialog("close");
                                                        }
                                                    }
                                                });
                                            },
                                            success: function(result){
                                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                                if(result.error == '0'){
                                                    // Refresh Schedule
                                                    $("#ticketBookingSearch").click();
                                                    var priceSym    = $("#ticketPriceSymbol").val();
                                                    // Get Response
                                                    var ticketId    = result.id;
                                                    var companyName = result.company;
                                                    var companyWeb  = result.website;
                                                    var companyType = result.company_type;
                                                    var branchFName = result.branch_from;
                                                    var branchFTel  = result.branch_from_tel;
                                                    var branchTName = result.branch_to;
                                                    var branchTTel  = result.branch_to_tel;
                                                    var destFromId  = result.dest_from_id;
                                                    var destiFCode  = result.dest_from_code;
                                                    var destiFName  = result.dest_from;
                                                    var destToId    = result.dest_to_id;
                                                    var destiTCode  = result.dest_to_code;
                                                    var destiTName  = result.dest_to;
                                                    var bookingDate = result.booking_date;
                                                    var travelDate  = result.travel_date;
                                                    var createdBy   = result.created_by;
                                                    var ticketTypeC = result.ticket_type;
                                                    var ticketCode  = result.ticket_code;
                                                    var transportT  = result.trans_type;
                                                    var printDate   = result.print_date;
                                                    var printName   = result.printer_name;
                                                    var printSilent = result.printer_silent;
                                                    var unitPrice   = result.unit_price+" "+priceSym;
                                                    var totalVat    = result.total_vat+" "+priceSym;
                                                    var totalAmt    = result.total_amount+" "+priceSym;
                                                    var totalDis    = result.total_dis+" "+priceSym;
                                                    var totalExtra  = result.extra_price+" "+priceSym;
                                                    var totalUsd    = result.total_usd+" "+priceSym;
                                                    var totalRiel   = result.total_riel+" ៛";
                                                    // Get From Form
                                                    var customerTel  = $("#ticketBookingTelephone").val().replace(/\s+/g, '');
                                                    var referenceC   = $("#ticketBoookingReference").val();
                                                    var customerType = '';
                                                    var noPaidLabel  = '';
                                                    // Customet Type
                                                    if($("input[name='data[TTicket][type]']:checked").val() == '1'){
                                                        customerType = 'Walk In';
                                                    } else if($("input[name='data[TTicket][type]']:checked").val() == '2'){
                                                        customerType = 'Phone Call';
                                                        noPaidLabel  = '(Not Yet Paid)';
                                                    } else if($("input[name='data[TTicket][type]']:checked").val() == '3'){
                                                        customerType = 'Agency';
                                                    }
                                                    var boarding = result.boarding_point;
                                                    var dropOff  = result.dropoff_point;
                                                    // Layout Print
                                                    var destinationCode = destiFCode+" -> "+destiTCode;
                                                    var direction = destiFName+" -> "+destiTName;
                                                    // $.ajax({
                                                    //     type: "POST",
                                                    //     url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printVatInvoice/"+ticketId,
                                                    //     beforeSend: function(){
                                                    //         $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                                    //     },
                                                    //     success: function(printInvoiceResult){
                                                    //         w=window.open();
                                                    //         w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                                    //         w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                                    //         w.document.write(printInvoiceResult);
                                                    //         w.document.close();
                                                    //         $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                    //     }
                                                    // });
                                                    if(companyType == 1){
                                                        var seatLabel = "";
                                                        var seatQty = 0;
                                                        if(result.item_label != undefined){
                                                            seatLabel = result.item_label;
                                                            seatQty   = result.total_seat;
                                                        } else {
                                                            $(".tblTicketBookingSeatList").each(function(){
                                                                if(seatLabel != ""){
                                                                    seatLabel += ",";
                                                                }
                                                                seatLabel += $(this).find(".seatLabel").val();
                                                                seatQty += 1;
                                                            });
                                                        }
                                                        printLayout += printTicketSetting(printName, printSilent, '1');
                                                        printLayout += generalInvoiceVatPrint("", ticketCode, customerType, customerTel, bookingDate, travelDate, direction, boarding, branchFTel, dropOff, branchTTel, seatLabel, seatQty, unitPrice, totalAmt, totalDis, totalVat, totalUsd, totalRiel, totalExtra);
                                                        if($(".tblTicketBookingSeatList").find(".seatNumber").val() != undefined){
                                                            $(".tblTicketBookingSeatList").each(function(){
                                                                var seatNo = $(this).find(".seatLabel").val();
                                                                var qrCode = ticketCode+"_"+seatNo;
                                                                printLayout += generalLayoutPrintSeatVat(ticketCode, qrCode, direction, seatNo, customerType, bookingDate, travelDate);    
                                                            });
                                                            if(replaceNum(result.extra_price) > 0){
                                                                $(".tblTicketBookingSeatList").each(function(){
                                                                    var seatNo = $(this).find(".seatLabel").val();
                                                                    printLayout += generalLayoutPrintLuckyTicket(ticketCode, customerTel, direction, seatNo, customerType, bookingDate, travelDate);
                                                                });
                                                            }
                                                        }
                                                    } else { // Buva Sea
                                                        if($(".tblTicketBookingSeatList").find(".seatNumber").val() != undefined){
                                                            var row = 1;
                                                            var rowCount = $(".tblTicketBookingSeatList").length;
                                                            printLayout += printTicketSetting(printName, printSilent, '1');
                                                            $(".tblTicketBookingSeatList").each(function(){
                                                                var seatNumber = $(this).find(".seatLabel").val();
                                                                var seatPrice  = $(this).find(".seatTotal").val()+" "+priceSym;
                                                                var brackPage  = '';
                                                                var ticketNo   = ticketCode;
                                                                var comType    = '';
                                                                if(rowCount > 1){
                                                                    ticketNo   = ticketCode+"-"+row;
                                                                }
                                                                if(row > 1 || isReturn == '1'){
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
                                                    }
                                                    if(isReturn != '1'){ // is Ticket Return = 0
                                                        if($("#ticketIsReturn").val() == '2' && $("#ticketIsOpenDate").val() == '0'){ // Check Return & Open Date False
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
                                                        } else if($("#ticketIsReturn").val() == '2' && $("#ticketIsOpenDate").val() == '1'){ // Check Return & Open Date True
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
                                                                        var bookingDate = result.booking_date;
                                                                        var travelDate  = result.travel_date;
                                                                        var createdBy   = result.created_by;
                                                                        var ticketTypeC = result.ticket_type;
                                                                        var ticketCode  = result.ticket_code;
                                                                        var totalSeat   = result.total_seat;
                                                                        var printDate   = result.print_date;
                                                                        var referenceC  = result.agency_ref;
                                                                        var priceType   = result.type;
                                                                        var seatPrice   = result.price+" "+priceSym;
                                                                        var comType     = '';
                                                                        var unitPrice   = result.unit_price+" "+priceSym;
                                                                        var totalVat    = result.total_vat+" "+priceSym;
                                                                        var totalAmt    = result.total_amount+" "+priceSym;
                                                                        var totalDis    = result.total_dis+" "+priceSym;
                                                                        var totalUsd    = result.total_usd+" "+priceSym;
                                                                        var totalRiel   = result.total_riel+" ៛";
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
                                                                            noPaidLabel  = '(Not Yet Paid)';
                                                                        } else if(priceType == '3'){
                                                                            customerType = 'Agency';
                                                                        }
                                                                        var boarding = '';
                                                                        var dropOff  = '';
                                                                        // Layout Print
                                                                        var direction = destiFName+" -> "+destiTName;
                                                                        <?php
                                                                        $dateNow = date("Y-m-d");
                                                                        $dateVat = "2022-12-01";
                                                                        if(strtotime($dateNow) < strtotime($dateVat)){
                                                                        ?>
                                                                        companyType = 0;
                                                                        <?php
                                                                        }
                                                                        ?>
                                                                        if(companyType == 1){ // BUS & AIR BUS
                                                                            printLayout += generalInvoiceVatPrint("breakPage", ticketCode, customerType, customerTel, bookingDate, "Open Date", direction, boarding, branchFTel, dropOff, branchTTel, "Open", totalSeat, unitPrice, totalAmt, totalDis, totalVat, totalUsd, totalRiel);
                                                                        } else { // Buva Sea
                                                                            // Header Seat Print
                                                                            printLayout += generalLayoutPrintTicket('breakPage', companyName, comType, companyWeb, ticketTypeC, ticketCode, branchFName, customerType, customerTel, referenceC, travelDate);
                                                                            // Seat Info Print
                                                                            printLayout += generateLayoutPrintSeatOpen(totalSeat, direction, dropOff, boarding, seatPrice);
                                                                            // Footer Print
                                                                            printLayout += generateLayoutPrintFooter(branchFName, branchFTel, branchTName, branchTTel, printDate, createdBy);
                                                                        }
                                                                        if(printLayout != ''){
                                                                            var w = window.open();
                                                                            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                                                            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css?1" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css?323234" media="print" />');
                                                                            w.document.write('<style type="text/css" media="screen">div.print-footer {display: none;}<\/style> ');
                                                                            w.document.write('<style type="text/css" media="print">div.print_doc { width:100%;}#btnDisappearPrint { display: none;}div.print-footer {display: block; width:100%;}.breakPage {page-break-before: always;}<\/style>');
                                                                            w.document.write('<div class="print_doc" style="width: 300px;">'+printLayout+'</div>');
                                                                            w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"><\/script>');
                                                                            w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.qrcode.min.js"><\/script>');
                                                                            w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"><\/script>');
                                                                            w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_ticket-v1.js?1"><\/script>');
                                                                            w.document.close();
                                                                            printLayout = '';
                                                                        }
                                                                    } else {
                                                                        if(printLayout != ''){
                                                                            var w = window.open();
                                                                            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                                                            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css?1" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css?323234" media="print" />');
                                                                            w.document.write('<style type="text/css" media="screen">div.print-footer {display: none;}<\/style> ');
                                                                            w.document.write('<style type="text/css" media="print">div.print_doc { width:100%;}#btnDisappearPrint { display: none;}div.print-footer {display: block; width:100%;}.breakPage {page-break-before: always;}<\/style>');
                                                                            w.document.write('<div class="print_doc" style="width: 300px;">'+printLayout+'</div>');
                                                                            w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"><\/script>');
                                                                            w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.qrcode.min.js"><\/script>');
                                                                            w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"><\/script>');
                                                                            w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_ticket-v1.js?1"><\/script>');
                                                                            w.document.close();
                                                                            printLayout = '';
                                                                        }
                                                                        dialogMessage('<?php echo "It cloud not been save ticket return."; ?>');
                                                                    }
                                                                }
                                                            });
                                                        } else {
                                                            // Print
                                                            $("#dialogModal").dialog("close");
                                                            <?php
                                                            if($user['User']['type'] == 3 || $user['User']['type'] == 4){ // Agency
                                                            ?>
                                                            if(printLayout != ''){
                                                                var w = window.open();
                                                                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                                                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css?1" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css?323234" media="print" />');
                                                                w.document.write('<style type="text/css" media="screen">div.print-footer {display: none;}<\/style> ');
                                                                w.document.write('<style type="text/css" media="print">div.print_doc { width:100%;}#btnDisappearPrint { display: none;}div.print-footer {display: block; width:100%;}.breakPage {page-break-before: always;}<\/style>');
                                                                w.document.write('<div class="print_doc" style="width: 300px;">'+printLayout+'</div>');
                                                                w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"><\/script>');
                                                                w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.qrcode.min.js"><\/script>');
                                                                w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"><\/script>');
                                                                w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_ticket-v1.js?1"><\/script>');
                                                                w.document.close();
                                                                printLayout = '';
                                                            }
                                                            <?php
                                                            } else {
                                                            ?>
                                                            if(customerType == 'Phone Call' || customerType == 'Agency'){
                                                                printLayout = '';
                                                                dialogMessage('<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>');
                                                            } else {
                                                                if(printLayout != ''){
                                                                    var w = window.open();
                                                                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                                                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css?1" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css?323234" media="print" />');
                                                                    w.document.write('<style type="text/css" media="screen">div.print-footer {display: none;}<\/style> ');
                                                                    w.document.write('<style type="text/css" media="print">div.print_doc { width:100%;}#btnDisappearPrint { display: none;}div.print-footer {display: block; width:100%;}.breakPage {page-break-before: always;}<\/style>');
                                                                    w.document.write('<div class="print_doc" style="width: 300px;">'+printLayout+'</div>');
                                                                    w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"><\/script>');
                                                                    w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.qrcode.min.js"><\/script>');
                                                                    w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"><\/script>');
                                                                    w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_ticket-v1.js?1"><\/script>');
                                                                    w.document.close();
                                                                    printLayout = '';
                                                                }
                                                            }
                                                            <?php
                                                            }
                                                            ?>
                                                        }
                                                    } else {
                                                        $("#dialogModal").dialog("close");
                                                        <?php
                                                        if($user['User']['type'] == 3 || $user['User']['type'] == 4){ // Agency
                                                        ?>
                                                        if(printLayout != ''){
                                                            var w = window.open();
                                                            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                                            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css?1" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css?323234" media="print" />');
                                                            w.document.write('<style type="text/css" media="screen">div.print-footer {display: none;}<\/style> ');
                                                            w.document.write('<style type="text/css" media="print">div.print_doc { width:100%;}#btnDisappearPrint { display: none;}div.print-footer {display: block; width:100%;}.breakPage {page-break-before: always;}<\/style>');
                                                            w.document.write('<div class="print_doc" style="width: 300px;">'+printLayout+'</div>');
                                                            w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"><\/script>');
                                                            w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.qrcode.min.js"><\/script>');
                                                            w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"><\/script>');
                                                            w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_ticket-v1.js?1"><\/script>');
                                                            w.document.close();
                                                            printLayout = '';
                                                        }
                                                        <?php
                                                        } else {
                                                        ?>
                                                        if(customerType == 'Phone Call' || customerType == 'Agency'){
                                                            printLayout = '';
                                                            dialogMessage('<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>');
                                                        } else {
                                                            if(printLayout != ''){
                                                                var w = window.open();
                                                                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                                                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css?1" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css?323234" media="print" />');
                                                                w.document.write('<style type="text/css" media="screen">div.print-footer {display: none;}<\/style> ');
                                                                w.document.write('<style type="text/css" media="print">div.print_doc { width:100%;}#btnDisappearPrint { display: none;}div.print-footer {display: block; width:100%;}.breakPage {page-break-before: always;}<\/style>');
                                                                w.document.write('<div class="print_doc" style="width: 300px;">'+printLayout+'</div>');
                                                                w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"><\/script>');
                                                                w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.qrcode.min.js"><\/script>');
                                                                w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"><\/script>');
                                                                w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_ticket-v1.js?1"><\/script>');
                                                                w.document.close();
                                                                printLayout = '';
                                                            }
                                                        }
                                                        <?php
                                                        }
                                                        ?>
                                                    }
                                                } else {
                                                    $("#dialogModal").dialog("close");
                                                    if(result.error == '3'){
                                                        <?php
                                                        if($user['User']['type'] == 2){
                                                        ?>
                                                        dialogMessage('Agency booking balance more than max balance');
                                                        <?php
                                                        } else {
                                                        ?>
                                                        dialogMessage('<?php echo MESSAGE_SORRY_YOUR_BALANCE_NOT_ENOUGH; ?>');
                                                        <?php
                                                        }
                                                        ?>
                                                    } else {
                                                        dialogMessage('<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>');
                                                    }
                                                }
                                            }
                                        });
                                    }
                                }
                            }
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                            if(isReturn == '1'){
                                if(printLayout != ''){
                                    var w = window.open();
                                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css?323234" media="print" />');
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
                        }
                    }
                });
            }
        });
    }
    
    function bookingTransit(obj, delay){
        var journeyId   = obj.attr('j-id');
        var departureId = obj.attr('t-id');
        var isReturn    = obj.attr('is-return');
        var title = obj.attr('act');
        var date  = obj.attr('date');
        $(".ui-dialog").remove();
        $("#dialog, #dialog1").html("");
        $.ajax({
            type: "GET",
            url: "<?php echo $this->base . '/'; ?>t_tickets/bookingTransit/"+journeyId+"/"+departureId+"/"+date+"/"+isReturn,
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                if(delay == 0){
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
                }
            },
            success: function(result){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#dialogModal").dialog("close");
                $("#dialog").html(result);
                $("#dialog").dialog({
                    title: title,
                    resizable: false,
                    modal: true,
                    width: '1024',
                    height: '680',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").hide();
                    }
                });
            }
        });
    }
    
    function printTicketSetting(printName, printSilent, printCopy){
        var layout = '';
        layout += '<input type="hidden" id="printerSettingSilent" value="'+printName+'" />';
        layout += '<input type="hidden" id="printerSettingName" value="'+printSilent+'" />';
        layout += '<input type="hidden" id="printerSettingNumCopy" value="'+printCopy+'" />';
        return layout;
    }

    function generalInvoiceVatPrint(breakPage, invoiceCode, customerType, telephone, issuedDate, journeyDate, direction, boardingPoint, boardingPointTel, dropOff, dropOffTel, seatLabel, seatQty, unitPrice, totalAmt, totalDis, totalVat, total, totalRiel, totalExtraPrice){
        var layout = '';
        layout += '<div style="width: 100%;" class="'+breakPage+'">';
        layout += '<table style="width: 100%;">';
        layout += '<tr><td style="width: 15%;"><img src="<?php echo $this->webroot; ?>img/logo-print.png" style="width: 45px;" /></td>';
        layout += '<td><table cellpadding="0" cellspacing="0" style="width: 100%;">';
        layout += '<tr><td style="vertical-align: top; text-align: center; width: 100px; font-size: 12px;"><b style="font-size: 14px;">វិរៈ ប៊ុនថាំ អេចប្រេស</b><br/>Vireak Buntham Express Co.,Ltd</td></tr>';
        layout += '<tr><td style="text-align: center; font-size: 10px;">VATTIN: L001-360000304</td></tr>';
        layout += '<tr><td style="text-align: center; font-size: 10px;">#ដីឡូត៍លេខ C ផ្លូវ ភូមិគៀនឃ្លាំង សង្កាត់ជ្រោយចង្វារ ខណ្ឌជ្រោយចង្វារ រាជធានីភ្នំពេញ</td></tr>';
        layout += '</table></td><td style="width: 10%; font-size: 26px; font-weight: bold;"></td></tr>';
        layout += '</table>';
        layout += '<table style="width: 100%;">';
        layout += '<tr><td style="width: 48%; font-size: 10px; text-align: right;">លេខរៀងវិក្កយបត្រ/Invoice No.:</td>';
        layout += '<td style="font-size: 10px;">'+invoiceCode+'</td></tr>';
        layout += '<tr><td style="font-size: 10px; text-align: right;">អតិថិជន/Customer:</td>';
        layout += '<td style="font-size: 10px;">'+customerType+'</td></tr>';
        layout += '<tr><td style="font-size: 10px; text-align: right;">លេខទូរស័ព្ទ/Telephone No:</td>';
        layout += '<td style="font-size: 10px;">'+telephone+'</td></tr>';
        layout += '<tr><td style="font-size: 10px; text-align: right;">ថ្ងៃទិញ/Issued Date:</td>';
        layout += '<td style="font-size: 10px;">'+issuedDate+'</td></tr>';
        layout += '<tr><td style="font-size: 10px; text-align: right;">ថ្ងៃ​ធ្វើ​ដំណើរ/Journey Date:</td>';
        layout += '<td style="font-size: 10px;">'+journeyDate+'</td></tr>';
        layout += '<tr><td style="font-size: 10px; text-align: right; vertical-align: text-top;">ទិសដៅ/Direction:</td>';
        layout += '<td style="font-size: 10px; vertical-align: text-top;">'+direction+'</td></tr></table>';
        layout += '<table style="width: 100%;" cellpadding="0" cellspacing="0">';
        layout += '<tr><td style="font-size: 10px; width: 35%; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000;">លេខកៅអី<br/>Seat No.</td>';
        layout += '<td style="font-size: 10px; width: 15%; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000;">បរិមាណ<br/>Qty</td>';
        layout += '<td style="font-size: 10px; width: 20%; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;">ថ្លៃឯកតា<br/>Unit Price</td>';
        layout += '<td style="font-size: 10px; width: 20%; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;">តម្លៃ<br/>Amount</td></tr>';
        layout += '<tr><td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000;">'+seatLabel+'</td>';
        layout += '<td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000;">'+seatQty+'</td>';
        layout += '<td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;">'+unitPrice+'</td>';
        layout += '<td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;">'+totalAmt+'</td></tr>';
        layout += '<tr><td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;" colspan="2">តម្លៃសរុប/Total</td>';
        layout += '<td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;" colspan="2">'+totalAmt+'</td></tr>';
        layout += '<tr><td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;" colspan="2">បញ្ចុះ តម្លៃ/Discount</td>';
        layout += '<td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;" colspan="2">'+totalDis+'</td></tr>';
        layout += '<tr><td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;" colspan="2">តម្លៃបន្ថែម/Extra Price</td>';
        layout += '<td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;" colspan="2">'+totalExtraPrice+'</td></tr>';
        layout += '<tr style="display: none;"><td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;" colspan="2">អាករលើតម្លៃបន្ថែម/VAT (10%)</td>';
        layout += '<td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;" colspan="2">'+totalVat+'</td></tr>';
        layout += '<tr><td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;" colspan="2">សរុបចុងក្រោយ/Grand Total USD</td>';
        layout += '<td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;" colspan="2">'+total+'</td></tr>';
        layout += '<tr><td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-bottom: 1px solid #000; text-align: right;" colspan="2">សរុបចុងក្រោយ/Grand Total Riel</td>';
        layout += '<td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: 1px solid #000; text-align: right;" colspan="2">'+totalRiel+'</td></tr></table>';
        layout += '<table style="width: 100%;">';
        layout += '<tr><td style="font-size: 10px;">តម្លៃសំបុត្របូកបញ្ចូលពន្ធអាករលើតម្លៃបន្ថែមរួចជាស្រេច/VAT INCLUDED</td></tr>';
        layout += '<tr><td style="font-size: 10px;">អត្រាប្តូរប្រាក់/Exchange Rate: 4,100៛</td></tr>';
        layout += '<tr><td style="font-size: 10px;">- ទីតាំងឡើង&លេខទូរស័ព្ទ/Boarding Point & Tel: '+boardingPoint+' '+boardingPointTel+'</td></tr>';
        layout += '<tr><td style="font-size: 10px;">- ទីតាំងចុះ&លេខទូរស័ព្ទ/Drop Off Point & Tel: '+dropOff+' '+dropOffTel+'</td></tr>';
        layout += '<tr><td style="font-size: 10px;">ល័ក្ខខ័ណ្ឌ/Term & Condition: </td></tr>';
        layout += '<tr><td style="font-size: 10px;">';
        if(journeyDate == "Open Date"){
            layout += 'សូមអតិថិជនទាំងអស់ធ្វើការបញ្ជាក់សំបុត្រត្រលប់1ថ្ងៃមុនចេញដំណើរ។ ';
        }
        layout += 'សូមអញ្ជើញមកដល់យ៉ាងហោចណាស់30នាទីមុនពេលការចេញដំណើរ។ សំបុត្រទិញហើយមិនអាចដូរយកប្រាក់វិញបានទេ។ អរគុណចំពោះការប្រើប្រាស់សេវាកម្មយើងខ្ញុំ។</td></tr>';
        layout += '<tr><td style="font-size: 10px;">';
        if(journeyDate == "Open Date"){
            layout += 'Please confirm your return ticket one day in advance. ';
        }
        layout += 'Please arrive at least 30 minutes before departure time. Ticket sold cannot be refund. Thank you for using our service.</td></tr></table>';
        layout += '</div>';
        return layout;
    }

    function generalLayoutPrintSeatVat(invoiceCode, qrCode, direction, seatNo, customerType, issuedDate, journeyDate){
        var layout = '';
        layout += '<div style="width: 100%;" class="breakPage">';
        layout += '<table style="width: 100%;" cellpadding="0" cellspacing="3">';
        layout += '<tr><td style="width: 60%; font-size: 10px;">លេខរៀងវិក្កយបត្រ/Invoice No.:<br/>'+invoiceCode+'</td>';
        layout += '<td rowspan="6"><input type="hidden" class="qrCodeTicket" value="'+qrCode+'" /><div class="cardQRCode"></div></td></tr>';
        layout += '<tr><td style="font-size: 10px;">ទិសដៅ/Direction:<br/>'+direction+'</td></tr>';
        layout += '<tr><td style="font-size: 10px;">លេខកៅអី/Seat No.: '+seatNo+'</td></tr>';
        layout += '<tr><td style="font-size: 10px;">អតិថិជន/Customer: '+customerType+'</td></tr>';
        layout += '<tr><td style="font-size: 10px;">ថ្ងៃទិញ/Issued Date: '+issuedDate+'</td></tr>';
        layout += '<tr><td style="font-size: 10px;">ថ្ងៃ​ធ្វើ​ដំណើរ/Journey Date:<br/>'+journeyDate+'</td></tr></table>';
        layout += '</div>';
        return layout;
    }

    function generalLayoutPrintLuckyTicket(invoiceCode, telephone, direction, seatNo, customerType, issuedDate, journeyDate){
        var layout = '';
        layout += '<div style="width: 100%;" class="breakPage">';
        layout += '<table style="width: 100%;">';
        layout += '<tr><td style="width: 20%;"><img src="<?php echo $this->webroot; ?>img/logo-print.png" style="width: 45px;" /></td>';
        layout += '<td><b style="font-size: 14px;">វិរៈប៊ុនថាំ អេចប្រេស</b><br/>Vireak Buntham Express Co.,Ltd</td></tr>';
        layout += '</table>';
        layout += '<table style="width: 100%;" cellpadding="3" cellspacing="3">';
        layout += '<tr><td style="font-size: 12px; font-weight: bold;">LUCKY TICKET</td></tr>';
        layout += '<tr><td style="font-size: 10px;">លេខទូរស័ព្ទ/Telephone No.: '+telephone+'</td></tr>';
        layout += '<tr><td style="font-size: 10px;">លេខរៀងវិក្កយបត្រ/Invoice No.: '+invoiceCode+'</td></tr>';
        layout += '<tr><td style="font-size: 10px;">ទិសដៅ/Direction: '+direction+'</td></tr>';
        layout += '<tr><td style="font-size: 10px;">លេខកៅអី/Seat No.: '+seatNo+'</td></tr>';
        layout += '<tr><td style="font-size: 10px;">អតិថិជន/Customer: '+customerType+'</td></tr>';
        layout += '<tr><td style="font-size: 10px;">ថ្ងៃទិញ/Issued Date: '+issuedDate+'</td></tr>';
        layout += '<tr><td style="font-size: 10px;">ថ្ងៃ​ធ្វើ​ដំណើរ/Journey Date: '+journeyDate+'</td></tr></table>';
        layout += '</div>';
        return layout;
    }
    
    function geneateLayoutPrintHead(brackPage, destination, ticketNo, ticketDate, departure, customerType, seatNo, price, refCode, createBy){
        var layout = '';
        layout += '<div style="width: 100%;" class="'+brackPage+'">';
        layout += '<table style="width: 100%;" cellpadding="0" cellspacing="3"><tr><td style="width: 18%; font-size: 10px;">Ticket No</td>';
        layout += '<td style="font-size: 10px;" colspan="3">: '+ticketNo;
        layout += '<div style="float: right; width: 100px; font-size: 10px;">'+destination+'</div></td></tr>';
        layout += '<tr><td style="width: 18%; font-size: 10px;">Ticket Date</td><td style="width: 22%; font-size: 10px;">: '+ticketDate+'</td>';
        layout += '<td style="width: 18%; font-size: 10px;">Departure</td><td style="font-size: 10px;">: '+departure+'</td>';
        layout += '</tr></table>';
        layout += '<table style="width: 100%;" cellpadding="0" cellspacing="3">';
        layout += '<tr><td style="width: 18%; font-size: 10px;">Customer</td>';
        layout += '<td style="width: 22%; font-size: 10px;">: '+customerType+'</td>';
        layout += '<td style="width: 18%; font-size: 10px;">Seat No</td>';
        layout += '<td style="width: 10%; font-size: 10px;">: '+seatNo+'</td>';
        layout += '<td style="width: 12%; font-size: 10px;">Price :</td>';
        layout += '<td style="font-size: 10px;">'+price+'</td></tr>';
        layout += '<tr><td style="font-size: 10px;" colspan="2">Agency Ref :</td>';
        layout += '<td style="font-size: 10px;" colspan="2">'+refCode+'</td>';
        layout += '<td style="font-size: 10px;" colspan="3">'+createBy+'</td></tr></table></div>';
        return layout;
    }
    
    function generalLayoutPrintTicket(brackPage, companyName, companyType, website, ticketSymbol, ticketNo, branchName, customerType, cusTel, refCode, travelDate){
        var layout = '';
        // Header
        layout += '<table style="width: 100%;" class="'+brackPage+'">';
        layout += '<tr><td style="width: 15%;"></td>';
        layout += '<td><table cellpadding="0" cellspacing="0" style="width: 100%;"><tr>';
        layout += '<td style="vertical-align: top; text-align: center; width: 100px; font-size: 14px;">'+companyName+'<div style="font-size: 10px;">'+website+'</div></td></tr>';
        layout += '<tr><td style="text-align: center; font-size: 10px; font-weight: bold;">'+companyType+'</td></tr></table></td>';
        layout += '<td style="width: 15%; font-size: 26px; font-weight: bold;">'+ticketSymbol+'</td></tr></table>';
        // Body
        layout += '<table style="width: 100%; margin-top: 5px;">';
        layout += '<tr><td style="font-size: 10px; text-align: center;">'+branchName+'</td></tr>';
        layout += '<tr><td style="font-size: 10px;">លេខសំបុត្រ/Ticket No. : <b>'+ticketNo+'</b></td></tr>';
        layout += '<tr><td style="font-size: 10px;">ប្រភេទអតិថិជន/Type Of Customer : '+customerType+'</td><div style="width: 80px; float: right; text-align: right;">'+cusTel+'</div><div style="clear: both;"></div></tr>';
        if(refCode != ''){
            layout += '<tr><td style="font-size: 10px;">លេខយោង/Reference Code : '+refCode+'</td></tr>';
        }
        layout += '<tr><td style="font-size: 10px;">ថ្ងៃ​ធ្វើ​ដំណើរ/Journey Date : '+travelDate+'</td></tr></table>';
        return layout;
    }
    
    function generateLayoutPrintSeat(seatNo, transportaionType, direction, dropOff, boarding, price, noPaidLabel){
        var layout = '';
        // Seat
        layout += '<table style="width: 100%; margin-top: 5px;" cellspacing="0">';
        layout += '<tr><td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; vertical-align: top; font-size: 10px;" colspan="2">លេខកៅអី/Seat No. : '+seatNo+'<div style="width: 100px; float: right; text-align: right;">'+transportaionType+'</div></td></tr>';
        layout += '<tr><td style="width: 45%; border-top: 1px solid #000; border-left: 1px solid #000; font-size: 10px;">ទិសដៅ/Direction</td>';
        layout += '<td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding-left: 5px; font-size: 10px;">'+direction+'</td></tr>';
        layout += '<tr><td style="border-top: 1px solid #000; border-left: 1px solid #000; font-size: 10px;">ទីតាំងឡើង/Boarding Point</td>';
        layout += '<td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding-left: 5px; font-size: 10px;">'+boarding+'</td></tr>';
        layout += '<tr><td style="border-top: 1px solid #000; border-left: 1px solid #000; font-size: 10px;">ទីតាំងចុះ/Drop Off Point</td>';
        layout += '<td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding-left: 5px; font-size: 10px;">'+dropOff+'</td></tr>';
        layout += '<tr><td style="border-top: 1px solid #000; border-left: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px;">តម្លៃ/Amount <span style=" font-size: 10px; font-weight: bold;">'+noPaidLabel+'</span></td>';
        layout += '<td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: 1px solid #000; padding-left: 5px; font-size: 10px;">'+price+'</td></tr></table>';
        return layout;
    }
    
    function generateLayoutPrintSeatOpen(totalSeat, direction, dropOff, boarding, price){
        var layout = '';
        // Seat
        layout += '<table style="width: 100%; margin-top: 5px;" cellspacing="0">';
        layout += '<tr><td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; vertical-align: top; font-size: 10px;" colspan="2">ចំនួនកៅអីកក់/Seat Booked. : '+totalSeat+'</td></tr>';
        layout += '<tr><td style="width: 45%; border-top: 1px solid #000; border-left: 1px solid #000; font-size: 10px;">ទិសដៅ/Direction</td>';
        layout += '<td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding-left: 5px; font-size: 10px;">'+direction+'</td></tr>';
        layout += '<tr><td style="border-top: 1px solid #000; border-left: 1px solid #000; font-size: 10px;">ទីតាំងចុះ/Drop Off Point</td>';
        layout += '<td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding-left: 5px; font-size: 10px;">'+dropOff+'</td></tr>';
        layout += '<tr><td style="border-top: 1px solid #000; border-left: 1px solid #000; font-size: 10px;">ទីតាំងឡើង/Boarding Point</td>';
        layout += '<td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding-left: 5px; font-size: 10px;">'+boarding+'</td></tr>';
        layout += '<tr><td style="border-top: 1px solid #000; border-left: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px;">តម្លៃ/Amount</td>';
        layout += '<td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: 1px solid #000; padding-left: 5px; font-size: 10px;">'+price+'</td></tr></table>';
        return layout;
    }
    
    function generateLayoutPrintFooter(branchFName, branchFTel, branchTName, branchTTel, printDate, createdBy){
        var layout = '';
        // Footer
        layout += '<table style="width: 100%; margin-top: 5px;" cellspacing="0">';
        layout += '<tr><td colspan="2" style="font-size: 8px; text-decoration: underline;">ល័ក្ខខ័ណ្ឌផ្សេងៗ/Term & Condition</td></tr>';
        layout += '<tr><td colspan="2" style="font-size: 8px;">សូមអតិថិជនទាំងអស់ ធ្វើការបញ្ជាក់សំបុត្រត្រលប់ 1ថ្ងៃមុនចេញដំណើរ។<br/>សូមអញ្ជើញមកដល់យ៉ាងហោចណាស់ 30 នាទីមុនពេលការចេញដំណើរ។</td></tr>';
        layout += '<tr><td colspan="2" style="font-size: 8px;">Please confirm your return ticket one day in advance.<br/>Please arrive at least 30 minutes before departure time.</td></tr>';
        layout += '<tr><td style="font-size: 8px; padding-top: 5px;">សំបុត្រទិញហើយមិនអាចដូរយកប្រាក់វិញបានទេ។</td>';
        layout += '<td style="font-size: 8px; padding-top: 5px; text-align: right;">Ticket sold cannot be refunded.</td></tr>';
        layout += '<tr><td style="font-size: 7px; padding-top: 5px;">អរគុណចំពោះការប្រើប្រាស់សេវាកម្មយើងខ្ញុំ។<br/>Thank you for using our servive.<br/>'+branchFName+'<br/>H/P:'+branchFTel+'<br/>'+branchTName+'<br/>H/P:'+branchTTel+'</td>';
        layout += '<td style="font-size: 7px; text-align: right; padding-top: 5px; vertical-align: top;">បោះពុម្ព/Print '+printDate+'<br/>'+createdBy+'</td></tr>';
        layout += '<tr><td colspan="2" style="font-size: 7px; text-align: center;">Developed by UDAYA Technology Co.,Ltd.</td></tr></table>';
        return layout;
    }
</script>
<div class="leftPanel">
    <div style="width: 100%; background: #0063dc; padding-top: 10px; padding-bottom: 10px; border: 1px solid #b000c3;">
        <?php
        if($user['User']['type'] == 2){
            $tableHeight = 40;
        ?>
        <div style="width: 1024px; height: 40px; padding: 5px; color: #fff; margin: 0px auto;">
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td style="width: 253px; text-align: left;">
                        <!-- Company -->
                        <select id="ticketBookingCompany">
                            <option value=""><?php echo TABLE_COMPANY; ?> (ALL)</option>
                            <?php
                            foreach($companies AS $company){
                            ?>
                            <option value="<?php echo $company['Company']['id']; ?>"><?php echo $company['Company']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td style="width: 253px; text-align: left;">
                        <!-- Branch -->
                        <select id="ticketBookingBranch">
                            <option value=""><?php echo TABLE_BRANCH; ?> (ALL)</option>
                            <?php
                            foreach($branches AS $branch){
                            ?>
                            <option com="<?php echo $branch['Branch']['company_id']; ?>" value="<?php echo $branch['Branch']['id']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td style="width: 165px;">
                        <!-- Departure Time -->
                        <select id="ticketBookingDepartureTime">
                            <option value=""><?php echo TABLE_DEPARTURE; ?> (ALL)</option>
                            <?php
                            foreach($tDepartureTimes AS $tDepartureTime){
                            ?>
                            <option value="<?php echo $tDepartureTime['TDepartureTime']['id']; ?>"><?php echo $tDepartureTime['TDepartureTime']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <button class="button2" id="btnFindTicketBooked"><i class="fa fa-phone" style="font-size: 14px; margin-right: 5px;"></i> <span><?php echo ACTION_FIND_TICKET_PHONE; ?></span></button>
                        <?php
                        if($allowViewHistory){
                        ?>
                        <button class="button2" id="btnViewTicketHistory" href="<?php echo $this->webroot; ?>t_tickets/viewTicket"><i class="fa fa-history" style="font-size: 14px; margin-right: 5px;"></i> <span><?php echo ACTION_TICKET_HISTORY; ?></span></button>
                        <?php
                        }
                        ?>
                        <div style="clear: both;"></div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
        } else {
            $tableHeight = 70;
        ?>
        <input type="hidden" value="" id="ticketBookingCompany" />
        <input type="hidden" value="" id="ticketBookingBranch" />
        <input type="hidden" value="" id="ticketBookingDepartureTime" />
        <?php
        }
        ?>
        <div style="width: 1024px; height: <?php echo $tableHeight; ?>px; padding: 5px; color: #fff; margin-top: 2px; margin: 0px auto;">
            <table cellpadding="0" cellspacing="0">
                <?php
                if($user['User']['type'] != 2){
                ?>
                <tr>
                    <td style="width: 253px; text-align: left; font-size: 16px; font-weight: bold;">
                        <?php echo REPORT_FROM; ?>
                    </td>
                    <td style="width: 253px; text-align: left; font-size: 16px; font-weight: bold;">
                        <?php echo REPORT_TO; ?>
                    </td>
                    <td style="width: 165px; text-align: left; font-size: 16px; font-weight: bold;">
                        <?php echo TABLE_DATE; ?>
                    </td>
                    <td></td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td style="width: 253px; text-align: left;">
                        <!-- Destination From -->
                        <select id="ticketBookingDestinationFrom" style="width: 250px; height: 40px; font-size: 14px; border: none;">
                            <?php
                            if(COUNT($tDestinationFroms) != 1){
                            ?>
                            <option value=""><?php echo REPORT_FROM; ?></option>
                            <?php
                            }
                            foreach($tDestinationFroms AS $tDestination){
                                $selected = '';
                                if($tDestination['TDestination']['id'] == $defaultSelect){
                                    $selected = 'selected="selected"';
                                }
                            ?>
                            <option value="<?php echo $tDestination['TDestination']['id']; ?>" <?php echo $selected; ?>><?php echo $tDestination['TDestination']['name']; ?></option>
                            <?php
                                // if($tDestination['TDestination']['id'] == 1){
                                //     $sqlBoarding = mysql_query("SELECT t_boarding_points.id, t_boarding_points.name FROM t_boarding_points
                                //                             INNER JOIN branch_destinations ON branch_destinations.branch_id = t_boarding_points.branch_id
                                //                             WHERE branch_destinations.t_destination_id = ".$tDestination['TDestination']['id']);
                                //     while($rowBoarding = mysql_fetch_array($sqlBoarding)){
                            ?>
                            <!-- <option value="<?php //echo $tDestination['TDestination']['id']; ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php //echo $rowBoarding['name']; ?></option> -->
                            <?php
                                    // }
                                // }
                            ?>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td style="width: 253px; text-align: left;">
                        <!-- Destination To -->
                        <select id="ticketBookingDestinationTo" style="width: 250px; height: 40px; font-size: 14px; border: none;">
                            <option value=""><?php echo REPORT_TO; ?></option>
                        </select>
                    </td>
                    <td style="width: 165px;">
                        <!-- Date -->
                        <input type="text" readonly="" id="ticketBookingDate" style="width: 150px; height: 25px; font-size: 14px;  border: none;" value="<?php echo $dateSelect; ?>" />
                    </td>
                    <td style="vertical-align: top; padding: 0px;">
                        <button class="button2" id="ticketBookingSearch"><i class="fa fa-search" style="font-size: 14px; margin-right: 5px;"></i> <span><?php echo GENERAL_SEARCH; ?></span></button>
                        <?php
                        if($allowViewHistory && $user['User']['type'] != 2){
                        ?>
                        <button class="button2" id="btnViewTicketHistory" href="<?php echo $this->webroot; ?>t_tickets/viewTicket"><i class="fa fa-history" style="font-size: 14px; margin-right: 5px;"></i> <span><?php echo ACTION_TICKET_HISTORY; ?></span></button>
                        <div style="clear: both;"></div>
                        <?php
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div style="width: 100%; margin-top: 10px;">
        <div style="width: 1400px; margin: 0px auto; overflow: auto;" id="journeyScheduleList"></div>
    </div>
</div>