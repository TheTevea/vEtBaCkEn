<?php
// Authentication
$this->element('check_access');
$allowAdd=checkAccess($user['User']['id'], $this->params['controller'], 'add');
$allowAddAgency=checkAccess($user['User']['id'], $this->params['controller'], 'addAgent');
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableTTicket;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableTTicket = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewTTicket").unbind('click').click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/" + id);
                });
                $(".btnEditTTicket").unbind('click').click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/editOpen/" + id);
                });
                $(".btnDeleteTTicket").unbind('click').click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_DELETE; ?> <b>' + name + '</b>?</p>');
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
                            '<?php echo ACTION_VOID; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/void/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableTTicket.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_VOID; ?>'){
                                            createSysAct('Ticket', 'Delete', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Ticket', 'Delete', 1, '');
                                            // alert message
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                        }
                                        $("#dialog").dialog({
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
                            },
                            '<?php echo ACTION_CANCEL; ?>': function() {
                                $(this).dialog("close");
                            }
			            }
                    });
                });
                // Print
                $(".btnPrintTTicket").unbind('click').click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var comType = $(this).attr('print-type');
                    var url = 'printVatInvoice';
                    if(comType == 6){ // Buva Sea
                        url = 'printAward';
                    }
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/"+url+"/"+id,
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
                });
                // Cancel
                $(".btnCancelTTicket").unbind('click').click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_CANCEL; ?> <b>' + name + '</b>?</p>');
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
                            '<?php echo ACTION_YES; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/cancelTicket/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableTTicket.fnDraw(false);
                                        // Alert Message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                                            createSysAct('Ticket', 'Cancel Ticket', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Ticket', 'Cancel Ticket', 1, '');
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                        }
                                        $("#dialog").dialog({
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
                            },
                            '<?php echo ACTION_NO; ?>': function() {
                                $(this).dialog("close");
                            }
			            }
                    });
                });
                // Note
                $(".btnUpdaetNoteTTicket").unbind('click').click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var note = $(this).attr('note').replace(/{dblquote}/g,'"');
                    $("#dialog").html("<textarea style='width:350px; height: 200px;' id='NoteTicketView'>" + note + "</textarea>").dialog({
                        title: '<?php echo TABLE_NOTE; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/saveNote/" + id + "/" + $("#NoteTicketView").val(),
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableTTicket.fnDraw(false);
                                    }
                                });
                            },
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                });
                // Print
                $(".btnPrintTTicketLucky").unbind('click').click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printLucky/"+id,
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
                });
                // Add Lucky Ticket
                $(".btnAddTTicketLucky").unbind('click').click(function(event){
                    event.preventDefault();
                    var id   = $(this).attr('rel');
                    var name = $(this).attr('name');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to add Lucky Ticket for <b>' + name + '</b>?</p>');
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
                            '<?php echo ACTION_YES; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    dataType: "json",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/addLuckyTicket/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        createSysAct('Ticket', 'Add Lucky Ticket', 1, '');
                                        oCache.iCacheLower = -1;
                                        oTableTTicket.fnDraw(false);
                                        // Alert Message
                                        if(result.error == '1'){
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                                        } else {
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                                            $.ajax({
                                                type: "POST",
                                                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printLucky/"+id,
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
                                        }
                                        $("#dialog").dialog({
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
                "bSortable": false, "aTargets": [ -1 ]
            }],
            "aaSorting": [[ 0, "desc" ]],
            
        });
        
        // Find
        $("#tticketOpenDate").change(function(event){
            event.preventDefault();
            filterTTicket();
        });
        
        $("#tticketFilterDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        });
        
        $("#tticketShowRecord").change(function(){
            if($(this).val() == 1){
                $("#tticketDivFilterDate").hide();
            } else {
                $("#tticketDivFilterDate").show();
            }
            $("#tticketClearDate").click();
        });
        
        $("#tticketClearDate").click(function(){
            $('#tticketFilterDate').val('');
        });

        $("#btnRefreshSearchTicket").unbind().click(function(){
            filterTTicket();
        });
    });
    
    function filterTTicket(){
        $("#btnRefreshSearchTicket").attr("disabled", true);
        $("#lblRefreshSearchTicket").html("<?php echo ACTION_LOADING; ?>");
        var date = 'all';
        if($("#tticketFilterDate").val() != ""){
            $("#tticketFilterDate").datepicker("option", "dateFormat", "yy-mm-dd");
            date = $("#tticketFilterDate").val();
        }
        var Tablesetting = oTableTTicket.fnSettings();
        var searchVal = $("#tticketFilterSearch").val().replace(/\s+/g, '');
        Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#tticketOpenDate").val()+"/"+$("#tticketFilterStatus").val()+"/"+$("#tticketShowRecord").val()+"/"+date+"/"+searchVal;
        oCache.iCacheLower = -1;
        oTableTTicket.fnDraw(false);
        if($("#tticketFilterDate").val() != ""){
            $("#tticketFilterDate").datepicker("option", "dateFormat", "dd/mm/yy");
        }
        $("#btnRefreshSearchTicket").removeAttr("disabled");
        $("#lblRefreshSearchTicket").html("<?php echo GENERAL_SEARCH; ?>");
    }
</script>
<div class="leftPanel">
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div style="float: right; width: 1000px;">
            <table cellpadding="0" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="width: 250px"><input type="text" style="width: 230px; height: 23px; font-size: 12px;" id="tticketFilterSearch" placeholder="Scan/Search Code, Telephone, Email" /></td>
                    <td style="width: 70px"><label for="tticketFilterStatus"><?php echo TABLE_STATUS; ?></label> :</td>
                    <td style="width: 120px">
                        <select id="tticketFilterStatus" style="width: 100px; height: 35px; font-size: 12px;">
                            <option value="all"><?php echo TABLE_ALL; ?></option>
                            <option value="1">Pending</option>
                            <option value="2">Completed</option>
                        </select>
                    </td>
                    <td style="width: 100px"><label for="tticketShowRecord" style="margin-left: 5px;"><?php echo TABLE_SHOW_RECORD ?></label> :</td>
                    <td style="width: 120px;">
                        <select id="tticketShowRecord" style="width: 90px; height: 35px; font-size: 12px;">
                            <option value="1"><?php echo TABLE_TODAY; ?></option>
                            <option value="2"><?php echo TABLE_ALL; ?></option>
                        </select>
                    </td>
                    <td style="width: 200px; display: none;" id="tticketDivFilterDate">
                        <label for="tticketFilterDate"><?php echo TABLE_DATE; ?></label> :
                        <input type="text" style="width: 110px; height: 23px; font-size: 12px;" id="tticketFilterDate" />
                        <img alt="" src="<?php echo $this->webroot; ?>img/button/clear.png" style="cursor: pointer;" onmouseover="Tip('Clear Date')" id="tticketClearDate" />
                    </td>
                    <td>
                        <div class="buttons" style="float: right;">
                            <button type="button" class="positive" id="btnRefreshSearchTicket">
                                <img src="<?php echo $this->webroot; ?>img/button/refresh-active.png" alt=""/>
                                <span id="lblRefreshSearchTicket"><?php echo GENERAL_SEARCH; ?></span>
                            </button>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div style="width: 250px; float: left;">
            <!-- Filter Open Date -->
            <label for="tticketOpenDate">Open Date</label> :
            <select id="tticketOpenDate" style="width: 90px; height: 35px; font-size: 12px;">
                <option value="all">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
        <div style="clear: both;"></div>
    </div>
    <br />
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 190px !important;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 190px !important;"><?php echo TABLE_CODE; ?></th>
                    <th style="width: 190px !important;"><?php echo "Internal ID"; ?></th>
                    <th style="width: 190px !important;"><?php echo TABLE_REFERENCE; ?></th>
                    <th style="width: 170px !important;"><?php echo TABLE_DEPARTURE_TIME; ?></th>
                    <th><?php echo GENERAL_DESCRIPTION; ?></th>
                    <th style="width: 100px !important;"><?php echo TABLE_TELEPHONE; ?></th>
                    <th style="width: 100px !important;"><?php echo TABLE_EMAIL; ?></th>
                    <th style="width: 100px !important;"><?php echo GENERAL_AMOUNT; ?></th>
                    <th style="width: 100px !important;"><?php echo "Payment"; ?></th>
                    <th style="width: 110px !important;"><?php echo "Travel Package Type"; ?></th>
                    <th style="width: 110px !important;"><?php echo TABLE_TYPE; ?></th>
                    <th style="width: 110px !important;"><?php echo "Boarding"; ?></th>
                    <th style="width: 100px !important;"><?php echo TABLE_STATUS; ?></th>
                    <th style="width: 140px !important;"><?php echo ACTION_ACTION; ?></th>
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