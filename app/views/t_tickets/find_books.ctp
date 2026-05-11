<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableFindTTicket;
    var printLayoutConfirm = '';
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableFindTTicket = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/findBooksAjax/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                eventFindBook();
                $(".btnCancelFindBooksTTicket").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    $("#dialog1").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_CANCEL; ?> <b>' + name + '</b>?</p><br/>Reason: <span style="color: red;">(Please input reason before save.)</span><br/><br/><textarea id="phoneCallReason" style="width: 100%; height: 50px;"></textarea>');
                    $("#dialog1").dialog({
                        title: '<?php echo DIALOG_CONFIRMATION; ?>',
			            resizable: false,
			            modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $("#phoneCallReason").val("");
                        },
			            buttons: {
                            '<?php echo ACTION_YES; ?>': function() {
                                var reason = $("#phoneCallReason").val().replace(/ /g, '');
                                if(reason != ""){
                                    $.ajax({
                                        type: "POST",
                                        url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/cancelTicket/" + id,
                                        data: "reason="+$("#phoneCallReason").val(),
                                        beforeSend: function(){
                                            $("#dialog1").dialog("close");
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                        },
                                        success: function(result){
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            oCache.iCacheLower = -1;
                                            oTableFindTTicket.fnDraw(false);
                                            // Alert Message
                                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                                                createSysAct('Ticket', 'Cancel Ticket', 2, result);
                                                $("#dialog1").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                            }else {
                                                createSysAct('Ticket', 'Cancel Ticket', 1, '');
                                                $("#dialog1").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                            }
                                            $("#dialog1").dialog({
                                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                                resizable: false,
                                                modal: true,
                                                width: 'auto',
                                                height: 'auto',
                                                buttons: {
                                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                                        $(this).dialog("close");
                                                    }
                                                }
                                            });
                                        }
                                    });
                                } else {
                                    $("#phoneCallReason").val("");
                                    $("#phoneCallReason").focus();
                                }
                            },
                            '<?php echo ACTION_NO; ?>': function() {
                                $(this).dialog("close");
                            }
			            }
                    });
                });
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1 ]
            }]
        });
    });
    
    function eventFindBook(){
        $(".btnComfirmTTicket").unbind('click').click(function(event){
            event.preventDefault();
            var journeyId   = $(this).attr('j-id');
            var departureId = $(this).attr('t-id');
            var isReturn    = $(this).attr('is-return');
            var title    = $(this).attr('act');
            var date     = $(this).attr('date');
            var ticketId = $(this).attr('ticket');
            $("#dialog1").html("");
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base . '/'; ?>t_tickets/booking/"+journeyId+"/"+departureId+"/"+date+"/"+isReturn+"/"+ticketId,
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
                    $("#dialog1").html(result);
                    $("#dialog1").dialog({
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
                                            url:  "<?php echo $this->base . "/t_tickets/"; ?>add/"+journeyId+"/"+departureId+"/"+date+"/"+ticketId,
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
                                                createSysAct('Ticket Booking', 'Confirm Booked', 2, result.responseText);
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
                                                oCache.iCacheLower = -1;
                                                oTableFindTTicket.fnDraw(false);
                                                if(result.error == '0'){
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
                                                    var customerTel  = $("#ticketBookingTelephone").val();
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
                                                    var boarding = result.boarding_point;
                                                    var dropOff  = result.dropoff_point;
                                                    // Layout Print
                                                    var destinationCode = destiFCode+" -> "+destiTCode;
                                                    var direction = destiFName+" -> "+destiTName;
                                                    if(companyType == 1){
                                                        var seatLabel = "";
                                                        var seatQty = 0;
                                                        $(".tblTicketBookingSeatList").each(function(){
                                                            if(seatLabel != ""){
                                                                seatLabel += ",";
                                                            }
                                                            seatLabel += $(this).find(".seatLabel").val();
                                                            seatQty += 1;
                                                        });
                                                        printLayoutConfirm += printTicketSetting(printName, printSilent, '1');
                                                        printLayoutConfirm += generalInvoiceVatPrint("", ticketCode, customerType, customerTel, bookingDate, travelDate, direction, boarding, branchFTel, dropOff, branchTTel, seatLabel, seatQty, unitPrice, totalAmt, totalDis, totalVat, totalUsd, totalRiel, totalExtra);
                                                        if($(".tblTicketBookingSeatList").find(".seatNumber").val() != undefined){
                                                            $(".tblTicketBookingSeatList").each(function(){
                                                                var seatNo = $(this).find(".seatLabel").val();
                                                                var qrCode = ticketCode+"_"+seatNo;
                                                                printLayoutConfirm += generalLayoutPrintSeatVat(ticketCode, qrCode, direction, seatNo, customerType, bookingDate, travelDate);   
                                                            });
                                                            if(replaceNum(result.extra_price) > 0){
                                                                $(".tblTicketBookingSeatList").each(function(){
                                                                    var seatNo = $(this).find(".seatLabel").val();
                                                                    printLayoutConfirm += generalLayoutPrintLuckyTicket(ticketCode, customerTel, direction, seatNo, customerType, bookingDate, travelDate);
                                                                });
                                                            }
                                                        }
                                                    } else { // Buva Sea
                                                        if($(".tblTicketBookingSeatList").find(".seatNumber").val() != undefined){
                                                            var row = 1;
                                                            var rowCount = $(".tblTicketBookingSeatList").length;
                                                            printLayoutConfirm += printTicketSetting(printName, printSilent, '1');
                                                            $(".tblTicketBookingSeatList").each(function(){
                                                                var seatNumber = $(this).find(".seatLabel").val();
                                                                var seatPrice  = $(this).find(".seatTotal").val()+" "+priceSym;
                                                                var brackPage  = '';
                                                                var ticketNo   = ticketCode;
                                                                var comType    = '';
                                                                if(rowCount > 1){
                                                                    ticketNo   = ticketCode+"-"+row;
                                                                }
                                                                if(row > 1){
                                                                    brackPage  = 'breakPage';
                                                                }
                                                                if(companyType == '1'){
                                                                    comType = 'BUS TICKET';
                                                                } else {
                                                                    comType = 'SPEED FERRY TICKET';
                                                                }
                                                                // Header Print
                                                                printLayoutConfirm += geneateLayoutPrintHead(brackPage, destinationCode, ticketNo, bookingDate, travelDate, customerType, seatNumber, seatPrice, referenceC, createdBy);
                                                                // Header Seat Print
                                                                printLayoutConfirm += generalLayoutPrintTicket('breakPage', companyName, comType, companyWeb, ticketTypeC, ticketNo, branchFName, customerType, customerTel, referenceC, travelDate);
                                                                // Seat Info Print
                                                                printLayoutConfirm += generateLayoutPrintSeat(seatNumber, transportT, direction, dropOff, boarding, seatPrice, noPaidLabel);
                                                                // Footer Print
                                                                printLayoutConfirm += generateLayoutPrintFooter(branchFName, branchFTel, branchTName, branchTTel, printDate, createdBy);
                                                                row++;
                                                            });
                                                        }   
                                                    }
                                                    if(printLayoutConfirm != ''){
                                                        var w = window.open();
                                                        w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                                        w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                                        w.document.write('<style type="text/css" media="screen">div.print-footer {display: none;}<\/style> ');
                                                        w.document.write('<style type="text/css" media="print">div.print_doc { width:100%;}#btnDisappearPrint { display: none;}div.print-footer {display: block; width:100%;}.breakPage {page-break-before: always;}<\/style>');
                                                        w.document.write('<div class="print_doc" style="width: 300px;">'+printLayoutConfirm+'</div>');
                                                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"><\/script>');
                                                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.qrcode.min.js"><\/script>');
                                                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"><\/script>');
                                                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_ticket-v1.js?1"><\/script>');
                                                        w.document.close();
                                                        printLayoutConfirm = '';
                                                    }
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
<div class="leftPanel">
    <br />
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 140px !important;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 140px !important;"><?php echo TABLE_CODE; ?></th>
                    <th><?php echo MENU_JOURNEY; ?></th>
                    <th style="width: 160px !important;"><?php echo REPORT_FROM; ?></th>
                    <th style="width: 160px !important;"><?php echo REPORT_TO; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_TELEPHONE; ?></th>
                    <th style="width: 90px !important;"><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="11" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
</div>
<div class="rightPanel"></div>