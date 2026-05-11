<?php
include('includes/function.php');
$destFrom = '';
$destTo = '';
foreach($TDestinations AS $TDestination){
    if($TDestination['TDestination']['id'] == $TTicket['TTicket']['t_destination_from_id']){
        $destFrom = $TDestination['TDestination']['name'];
    } else {
        $destTo = $TDestination['TDestination']['name'];
    }
}
?>
<script type="text/javascript">
    var printLayoutEdit = '';
    $(document).ready(function(){
        var tabWidth = $(".ui-layout-center").height() - 175;
        $("#editOpenScheduleList").css("height", tabWidth);
        $("#editOpenScheduleList").niceScroll({cursorborder:"", cursorcolor:"#0063dc", boxzoom:false});
        $("#editOpenTravelDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        });
        $("#editOpenTravelDate").datepicker( "option", "minDate", 0 ); 
        // Search Schedule
        $("#btnEditOpenSearchShedule").unbind('click').click(function(){
            var from = '<?php echo $TTicket['TTicket']['t_destination_from_id']; ?>';
            var to   = '<?php echo $TTicket['TTicket']['t_destination_to_id']; ?>';
            var date = $("#editOpenTravelDate").val().toString().split("/")[2]+"-"+$("#editOpenTravelDate").val().toString().split("/")[1]+"-"+$("#editOpenTravelDate").val().toString().split("/")[0];;
            var com  = '';
            var bra  = '';
            var time = '';
            var obj  = $(this);
            if(from != '' && to != '' && date != ''){
                $.ajax({
                    type: "GET",
                    url: "<?php echo $this->base . '/'; ?>t_tickets/viewSchedule/"+from+"/"+to+"/"+date+"/0/1?company="+com+"&branch="+bra+"&departure="+time,
                    beforeSend: function(){
                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        $("#editOpenScheduleList").html('<img src="<?php echo $this->webroot; ?>img/ajax-loader.gif" alt="" style="width:128px; height: 15px; position:absolute; left:50%; top:50%;  margin-left:-64px; margin-top:-7.5px;" />');
                        obj.attr('disabled', true);
                    },
                    success: function(result){
                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                        obj.attr('disabled', false);
                        $("#editOpenScheduleList").html(result);
                        eventBookingOpen();
                    }
                });
            }
        });
        // Back
        $("#btnEditOpenBackShedule").unbind('click').click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTTicket.fnDraw(false);
            var rightPanel = $("#editOpenDiv").parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
    
    function eventBookingOpen(){
        $(".btnEditOpen").unbind('click').click(function(){
            var journeyId = $(this).attr('j-id');
            var departureId = $(this).attr('t-id');
            var isReturn = $(this).attr('is-return');
            var title = $(this).attr('act');
            var date  = $(this).attr('date');
            $("#dialog").dialog("close");
            $(".ui-dialog").remove();
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base . '/'; ?>t_tickets/booking/"+journeyId+"/"+departureId+"/"+date+"/"+isReturn+"/<?php echo $TTicket['TTicket']['id']; ?>",
                beforeSend: function(){
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
                        height: '680',
                        open: function(event, ui){
                            $(".ui-dialog-titlebar-close").hide();
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var validateBack = $("#ticketBookingForm").validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
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
                                        if($("#ticketIsReturn").val() == '2' && $("#ticketIsOpenDate").val() == '0'){
                                            $("#ticketBookingReturnDate").datepicker("option", "dateFormat", "yy-mm-dd");
                                        }
                                        var post = $("#ticketBookingForm").serialize();
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url:  "<?php echo $this->base . "/t_tickets/"; ?>add/"+journeyId+"/"+departureId+"/"+date+"/<?php echo $TTicket['TTicket']['id']; ?>",
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
                                                createSysAct('Ticket Booking', 'Edit Open Date', 2, result.responseText);
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
                                                $("#dialogModal").dialog("close");
                                                $("#btnEditOpenBackShedule").click();
                                                if(result.error == '0'){
                                                    var ticketId    = result.id;
                                                    var companyType = result.company_type;
                                                    var url         = 'printVatInvoice';
                                                    if(companyType == 2){ // Buva Sea
                                                        url = 'printAward';
                                                    }
                                                    $.ajax({
                                                        type: "POST",
                                                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/"+url+"/"+ticketId,
                                                        beforeSend: function(){
                                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                                        },
                                                        success: function(printInvoiceResult){
                                                            w=window.open();
                                                            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                                            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css?2329980" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css?879182167" media="print" />');
                                                            w.document.write(printInvoiceResult);
                                                            w.document.close();
                                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                        }
                                                    });
                                                    // Get Response
                                                    // var companyName = result.company;
                                                    // var companyWeb  = result.website;
                                                    // var companyType = result.company_type;
                                                    // var branchFName = result.branch_from;
                                                    // var branchFTel  = result.branch_from_tel;
                                                    // var branchTName = result.branch_to;
                                                    // var branchTTel  = result.branch_to_tel;
                                                    // var destiFCode  = result.dest_from_code;
                                                    // var destiFName  = result.dest_from;
                                                    // var destiTCode  = result.dest_to_code;
                                                    // var destiTName  = result.dest_to;
                                                    // var bookingDate = result.booking_date;
                                                    // var travelDate  = result.travel_date;
                                                    // var createdBy   = result.created_by;
                                                    // var ticketTypeC = result.ticket_type;
                                                    // var ticketCode  = result.ticket_code;
                                                    // var transportT  = result.trans_type;
                                                    // var printDate   = result.print_date;
                                                    // var printName   = result.printer_name;
                                                    // var printSilent = result.printer_silent;
                                                    // // Get From Form
                                                    // var customerTel  = $("#ticketBookingTelephone").val();
                                                    // var referenceC   = $("#ticketBoookingReference").val();
                                                    // var customerType = '';
                                                    // var noPaidLabel  = '';
                                                    // // Customet Type
                                                    // if($("input[name='data[TTicket][price_type]']:checked").val() == '1'){
                                                    //     customerType = 'Walk In';
                                                    // } else if($("input[name='data[TTicket][price_type]']:checked").val() == '2'){
                                                    //     customerType = 'Phone Call';
                                                    //     noPaidLabel  = '(Not Yet Paid)';
                                                    // } else if($("input[name='data[TTicket][price_type]']:checked").val() == '3'){
                                                    //     customerType = 'Agency';
                                                    // }
                                                    // var boarding = $("#ticketBookingDropOff").text();
                                                    // var dropOff  = $("#ticketBookingBoardingPoint").text();
                                                    // var priceSym = $("#ticketPriceSymbol").val();
                                                    // // Layout Print
                                                    // var destinationCode = destiFCode+" -> "+destiTCode;
                                                    // var direction = destiFName+" -> "+destiTName;
                                                    // if($(".tblTicketBookingSeatList").find(".seatNumber").val() != undefined){
                                                    //     var row = 1;
                                                    //     var rowCount = $(".tblTicketBookingSeatList").length;
                                                    //     printLayoutEdit += printTicketSetting(printName, printSilent, '1');
                                                    //     $(".tblTicketBookingSeatList").each(function(){
                                                    //         var seatNumber = $(this).find(".seatLabel").val();
                                                    //         var seatPrice  = $(this).find(".seatTotal").val()+" "+priceSym;
                                                    //         var brackPage  = '';
                                                    //         var ticketNo   = ticketCode;
                                                    //         var comType    = '';
                                                    //         if(rowCount > 1){
                                                    //             ticketNo   = ticketCode+"-"+row;
                                                    //         }
                                                    //         if(row > 1){
                                                    //             brackPage  = 'breakPage';
                                                    //         }
                                                    //         if(companyType == '1'){
                                                    //             comType = 'BUS TICKET';
                                                    //         } else {
                                                    //             comType = 'SPEED FERRY TICKET';
                                                    //         }
                                                    //         // Header Print
                                                    //         printLayoutEdit += geneateLayoutPrintHead(brackPage, destinationCode, ticketNo, bookingDate, travelDate, customerType, seatNumber, seatPrice, referenceC, createdBy);
                                                    //         // Header Seat Print
                                                    //         printLayoutEdit += generalLayoutPrintTicket('breakPage', companyName, comType, companyWeb, ticketTypeC, ticketNo, branchFName, customerType, customerTel, referenceC, travelDate);
                                                    //         // Seat Info Print
                                                    //         printLayoutEdit += generateLayoutPrintSeat(seatNumber, transportT, direction, dropOff, boarding, seatPrice, noPaidLabel);
                                                    //         // Footer Print
                                                    //         printLayoutEdit += generateLayoutPrintFooter(branchFName, branchFTel, branchTName, branchTTel, printDate, createdBy);
                                                    //         row++;
                                                    //     });
                                                    // }
                                                    // if(printLayoutEdit != ''){
                                                    //     var w = window.open();
                                                    //     w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                                    //     w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                                    //     w.document.write('<style type="text/css" media="screen">div.print-footer {display: none;}<\/style> ');
                                                    //     w.document.write('<style type="text/css" media="print">div.print_doc { width:100%;}#btnDisappearPrint { display: none;}div.print-footer {display: block; width:100%;}.breakPage {page-break-before: always;}<\/style>');
                                                    //     w.document.write('<div class="print_doc" style="width: 300px;">'+printLayoutEdit+'</div>');
                                                    //     w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"><\/script>');
                                                    //     w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"><\/script>');
                                                    //     w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_ticket.js"><\/script>');
                                                    //     w.document.close();
                                                    //     printLayoutEdit = '';
                                                    // }
                                                } else {
                                                    dialogMessage('<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>');
                                                }
                                            }
                                        });
                                    }
                                }
                            },
                            '<?php echo ACTION_CANCEL; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        });
    }
</script>
<div style="width: 100%; background: #0063dc;" id="editOpenDiv">
    <div style="width: 1024px; height: 80px; padding: 5px; color: #fff; margin: 0px auto;">
        <table cellpadding="5" cellspacing="0" style="width: 100%;">
            <tr>
                <td><?php echo TABLE_DATE; ?> :</td>
                <td><?php echo dateShort($TTicket['TTicket']['date']); ?></td>
                <td><?php echo TABLE_TICKET_CODE; ?> :</td>
                <td><?php echo $TTicket['TTicket']['code']; ?></td>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td><?php echo REPORT_FROM; ?> :</td>
                <td><?php echo $destFrom; ?></td>
                <td><?php echo REPORT_TO; ?> :</td>
                <td><?php echo $destTo; ?></td>
                <td><?php echo TABLE_TRAVEL_DATE; ?></td>
                <td>
                    <!-- Date -->
                    <input id="editOpenTravelDate" style="width: 150px; height: 38px; font-size: 14px;  border: none;" value="" placeholder="<?php echo TABLE_OPEN_DATE; ?>" />
                </td>
                <td>
                    <input type="button" value="<?php echo GENERAL_SEARCH; ?>" id="btnEditOpenSearchShedule" style="width: 150px; height: 42px; font-size: 14px; cursor: pointer;" />
                    <button style="width: 150px; height: 42px; cursor: pointer;" id="btnEditOpenBackShedule">
                        <span style="font-size: 14px;">Back History</span>
                    </button>
                </td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>
</div>
<div style="width: 100%; margin-top: 10px;">
    <div style="width: 1024px; margin: 0px auto; overflow: auto;" id="editOpenScheduleList"></div>
</div>