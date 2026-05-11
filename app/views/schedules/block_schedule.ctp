<?php
include("includes/function.php");
$checkExisted = false;
$blockSeatId  = "";
$type  = 2;
$start = "";
$end   = "";
$sqlBk = mysql_query("SELECT * FROM t_journey_seat_blocks WHERE t_journey_id = ".$tJourney['TJourney']['id']." AND t_departure_time_id = ".$tJourney['TDepartureTime']['id']." AND start <= '".$date."' AND end >= '".$date."' AND is_active = 1 LIMIT 1");
if(mysql_num_rows($sqlBk)){
    $rowBk = mysql_fetch_array($sqlBk);
    $checkExisted = true;
    $blockSeatId = $rowBk['id'];
    $type = $type;
    if($rowBk['start'] != '' && $rowBk['start'] != ''){
        $start = dateShort($rowBk['start']);
    }
    if($rowBk['end'] != '' && $rowBk['end'] != ''){
        $end = dateShort($rowBk['end']);
    }
}
?>
<script type="text/javascript">
    var rowIndexBlock = '';
    var rowTableBlock  = $("#rowBlockSeatList");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Remove Table Block
        $("#rowBlockSeatList").remove();
        $(".btnBackTTicket").unbind('click').click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableSchedule.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        // Seat Select
        $(".blockSeatSelect").unbind('click').click(function(){
            var seatId    = $(this).val();
            var seatLabel = $(this).attr('lbl');
            if($(this).is(":checked")){
                addBlockSeat(seatId, seatLabel);
            } else {
                $(".blockSeatId[value='"+seatId+"']").closest("tr").remove();
            }
        });
        
        $("#BlockSeatForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        
        $("#BlockSeatForm").ajaxForm({
            dataType: "json",
            beforeSerialize: function($form, options) {
                $("#blockSeatStart, #blockSeatEnd").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveBlockSeat").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                $("#dialogModal").html('<p style="text-align: center;"><img alt="" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" /></p>');
                $("#dialogModal").dialog({
                    title: '<?php echo DIALOG_LOADING; ?>',
                    resizable: false,
                    modal: true,
                    closeOnEscape: false,
                    width: 180,
                    height: 100,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                        $(".ui-dialog-titlebar-close").hide();
                    },
                    close: function(event, ui){
                        $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {

                    }
                });
            },
            error: function (result) {
                $("#dialogModal").dialog("close");
                $(".txtSaveBlockSeat").html("<?php echo ACTION_SAVE; ?>");
                $("#blockSeatStart, #blockSeatEnd").datepicker("option", "dateFormat", "dd/mm/yy");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Block Seat', 'Add/Edit', 2, result.responseText);
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
            success: function(result) {
                $(".txtSaveBlockSeat").html("<?php echo ACTION_SAVE; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("#blockSeatStart, #blockSeatEnd").datepicker("option", "dateFormat", "dd/mm/yy");
                $("#dialogModal").dialog("close");
                // alert message
                createSysAct('Block Seat', 'Add/Edit', 1, '');
                if(result.error == '1'){
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                    $(".btnSaveBlockSeat").removeAttr('disabled');
                }else {
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                    $("input[name='data[TJourneySeatBlock][id]']").val(result.id);
                    $(".btnSaveBlockSeat").hide();
                    $(".btnEditBlockSeat").show().removeAttr('disabled');
                    $(".btnUnblockBlockSeat").show().removeAttr('disabled');
                    $("#BlockSeatForm").find("input").attr('disabled', true);
                    $("#BlockSeatForm").find("select").attr('disabled', true);
                    $(".btnRemoveBlockSeat").hide();
                    // Disable Check Seat
                    $(".rowBlockSeatList").each(function(){
                        var bkSeatLbl = $(this).find(".blockSeatLabel").text();
                        $(".blockSeatSelect[lbl='"+bkSeatLbl+"']").attr('checked', true);
                        $(".blockSeatSelect[lbl='"+bkSeatLbl+"']").attr('disabled', true);
                    });
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
        
        var dates = $("#blockSeatStart, #blockSeatEnd").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "blockSeatStart" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
                $("#BlockSeatForm").validationEngine("hideAll");
            }
        });
        
        $(".rdiSeatBlock").unbind("click").click(function(){
            $("#blockSeatStart, #blockSeatEnd").val('').removeClass("validate[required]");
            $("#blockSeatStart").datepicker( "option", "maxDate", null );
            $("#blockSeatStart").datepicker( "option", "minDate", null );
            $("#blockSeatEnd").datepicker( "option", "maxDate", null );
            $("#blockSeatEnd").datepicker( "option", "minDate", null );
            if($(".rdiSeatBlock:checked").val() == '1'){
                $("#divNoneSeatBlock").show();
                $("#divCustomizeSeatBlock").hide();
            } else if($(".rdiSeatBlock:checked").val() == '2'){
                $("#divNoneSeatBlock").hide();
                $("#divCustomizeSeatBlock").show();
                $("#blockSeatStart, #blockSeatEnd").addClass("validate[required]");
            } else {
                $("#divNoneSeatBlock").hide();
                $("#divCustomizeSeatBlock").hide();
            }
        });
        
//        $(".btnEditBlockSeat").unbind('click').click(function(){
//            $(".btnSaveBlockSeat, .btnUnblockBlockSeat").show().removeAttr('disabled');
//            $(".btnEditBlockSeat").hide().attr('disabled', true);
//            $("#BlockSeatForm").find("input").attr('disabled', false);
//            $("#BlockSeatForm").find("select").attr('disabled', false);
//            $(".btnRemoveBlockSeat").show();
//            // Disable Check Seat
//            $(".rowBlockSeatList").each(function(){
//                var bkSeatLbl = $(this).find(".blockSeatLabel").text();
//                $(".blockSeatSelect[lbl='"+bkSeatLbl+"']").attr('disabled', false);
//            });
//        });
        
        $(".btnSaveBlockSeat").unbind('click').click(function(){
            var validateBack =$("#BlockSeatForm").validationEngine("validate");
            if(!validateBack){
                return false;
            } else{
                if($(".rowBlockSeatList").find(".blockSeatId").val() == undefined){
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please select seat to list.</p>');
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
                                $(this).dialog("close");
                            }
                        }
                    });
                    return false;
                }else{
                    return true;
                }
            }
        });
        
        $(".btnUnblockBlockSeat").unbind('click').click(function(event){
            event.preventDefault();
            var id = $("input[name='data[TJourneySeatBlock][id]']").val();
            if(id != ''){
                $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DO_YOU_WANT_UNBLOCK ." ". $tJourney['TJourney']['description']." (".date("h:i A", strtotime($tJourney['TDepartureTime']['name'])).")"; ?> ?</p>');
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
                                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/removeBlockSeat/" + id,
                                data: "",
                                beforeSend: function(){
                                    $("#dialog").dialog("close");
                                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    $(".btnEditBlockSeat, .btnUnblockBlockSeat").hide().attr('disabled', true);
                                    $("#dialogModal").html('<p style="text-align: center;"><img alt="" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" /></p>');
                                    $("#dialogModal").dialog({
                                        title: '<?php echo DIALOG_LOADING; ?>',
                                        resizable: false,
                                        modal: true,
                                        closeOnEscape: false,
                                        width: 180,
                                        height: 100,
                                        open: function(event, ui){
                                            $(".ui-dialog-buttonpane").show();
                                            $(".ui-dialog-titlebar-close").hide();
                                        },
                                        close: function(event, ui){
                                            $(".ui-dialog-titlebar-close").show();
                                        },
                                        buttons: {

                                        }
                                    });
                                },
                                success: function(result){
                                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                    $("#dialogModal").dialog("close");
                                    // Alert Message
                                    createSysAct('Block Seat', 'Unblock', 1, '');
                                    if(result.error == '1'){
                                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                                        $(".btnEditBlockSeat, .btnUnblockBlockSeat").show().removeAttr('disabled');
                                    }else {
                                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?></p>');
                                        $("#rowBlockSeatBody").html('');
                                        $(".rdiSeatBlock").attr('checked', false);
                                        $(".rdiSeatBlock[value='2']").attr('checked', true).click();
                                        $("#blockReleaseTime").find("option[value='00']").attr('selected', true);
                                        $(".btnSaveBlockSeat").show().removeAttr('disabled');
                                        $(".btnEditBlockSeat, .btnUnblockBlockSeat").hide().attr('disabled', true);
                                        $("#BlockSeatForm").find("input").attr('disabled', false);
                                        $("#BlockSeatForm").find("select").attr('disabled', false);
                                        // Disable Check Seat
                                        $(".blockSeatSelect").attr('checked', false);
                                        $(".blockSeatSelect").attr('disabled', false);
                                        // Remove Block Seat ID
                                        $("input[name='data[TJourneySeatBlock][id]']").val("");
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
            }
        });
        <?php
        if($checkExisted == true){
        ?>
        eventKeyBlockSeat();
        // Check Seat
        $(".rowBlockSeatList").each(function(){
            var bkSeatLbl = $(this).find(".blockSeatLabel").text();
            $(".blockSeatSelect[lbl='"+bkSeatLbl+"']").attr('checked', true);
            $(".blockSeatSelect[lbl='"+bkSeatLbl+"']").attr('disabled', true);
        });
        // Disable Button
        $(".btnSaveBlockSeat").hide().attr('disabled', true);
        $(".btnEditBlockSeat").show().removeAttr('disabled');
        $(".btnUnblockBlockSeat").show().removeAttr('disabled');
        <?php
        }
        ?>
    });
    
    function addBlockSeat(seatId, seatLabel){
        rowIndexBlock = Math.floor((Math.random() * 100000) + 1);
        var tr = rowTableBlock.clone(true);
        tr.removeAttr("style").removeAttr("id");
        // Seat
        tr.find("td .blockSeatId").val(seatId);
        tr.find("td .blockSeatLabel").text(seatLabel);
        tr.find("td input[name='data[TJourneySeatBlock][seat_label][]']").val(seatLabel);
        // Check
        tr.find("td .blockMaleCheck").attr("id", "blockMaleCheck"+rowIndexBlock);
        tr.find("td .blockFemaleCheck").attr("id", "blockFemaleCheck"+rowIndexBlock);
        tr.find("td .blockSlaveCheck").attr("id", "blockSlaveCheck"+rowIndexBlock);
        tr.find("td .blockEticketCheck").attr("id", "blockEticketCheck"+rowIndexBlock);
        tr.find("td .blockApiCheck").attr("id", "blockApiCheck"+rowIndexBlock);
        tr.find("td .blockAgencyCheck").attr("id", "blockAgencyCheck"+rowIndexBlock);
        tr.find("td .blockMaleCheck").attr("name", "checkBlock"+rowIndexBlock);
        tr.find("td .blockFemaleCheck").attr("name", "checkBlock"+rowIndexBlock);
        tr.find("td .blockSlaveCheck").attr("name", "checkBlock"+rowIndexBlock);
        tr.find("td .blockEticketCheck").attr("name", "checkBlock"+rowIndexBlock);
        tr.find("td .blockApiCheck").attr("name", "checkBlock"+rowIndexBlock);
        tr.find("td .blockAgencyCheck").attr("name", "checkBlock"+rowIndexBlock);
        // Lable Check
        tr.find("td .lblMaleCheck").attr("for", "blockMaleCheck"+rowIndexBlock);
        tr.find("td .lblFemaleCheck").attr("for", "blockFemaleCheck"+rowIndexBlock);
        tr.find("td .lblSlaveCheck").attr("for", "blockSlaveCheck"+rowIndexBlock);
        tr.find("td .lblEticketCheck").attr("for", "blockEticketCheck"+rowIndexBlock);
        tr.find("td .lblApiCheck").attr("for", "blockApiCheck"+rowIndexBlock);
        tr.find("td .lblAgtCheck").attr("for", "blockAgencyCheck"+rowIndexBlock);
        $("#rowBlockSeatBody").append(tr);
        eventKeyBlockSeat();
    }
    
    function eventKeyBlockSeat(){
        $(".btnRemoveBlockSeat, .blockMaleCheck, .blockFemaleCheck, .blockSlaveCheck, .blockEticketCheck, .blockApiCheck, .blockAgencyCheck").unbind('click').unbind('change');
        
        $(".btnRemoveBlockSeat").click(function(){
            var obj = $(this);
            var tr  = obj.closest("tr");
            var bkSeatLbl = tr.find(".blockSeatLabel").text();
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to remove the selected seat?</p>');
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
                        tr.remove();
                        $(".blockSeatSelect[lbl='"+bkSeatLbl+"']").attr('disabled', false);
                        $(".blockSeatSelect[lbl='"+bkSeatLbl+"']").attr('checked', false);
                        $(this).dialog("close");
                    }
                }
            });
        });
        
        $(".blockMaleCheck").click(function(){
            if($(this).is(':checked')){
                $(this).closest("tr").find(".blockSeatMale").val('1');
            } else {
                $(this).closest("tr").find(".blockSeatMale").val('0');
            }
        });
        
        $(".blockFemaleCheck").click(function(){
            if($(this).is(':checked')){
                $(this).closest("tr").find(".blockSeatFemale").val('1');
            } else {
                $(this).closest("tr").find(".blockSeatFemale").val('0');
            }
        });
        
        $(".blockSlaveCheck").click(function(){
            if($(this).is(':checked')){
                $(this).closest("tr").find(".blockSeatSlave").val('1');
            } else {
                $(this).closest("tr").find(".blockSeatSlave").val('0');
            }
        });
        
        $(".blockEticketCheck").click(function(){
            if($(this).is(':checked')){
                $(this).closest("tr").find(".blockSeatEticket").val('1');
            } else {
                $(this).closest("tr").find(".blockSeatEticket").val('0');
            }
        });
        
        $(".blockApiCheck").click(function(){
            if($(this).is(':checked')){
                $(this).closest("tr").find(".blockSeatApi").val('1');
            } else {
                $(this).closest("tr").find(".blockSeatApi").val('0');
            }
        });
        
        $(".blockAgencyCheck").click(function(){
            if($(this).is(':checked')){
                $(this).closest("tr").find(".blockSeatAgency").val('1');
            } else {
                $(this).closest("tr").find(".blockSeatAgency").val('0');
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTTicket">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_JOURNEY_INFO); ?></legend>
    <table style="width: 100%;" cellpadding="5">
        <tr>
            <th style="width:10%;"><?php __(MENU_BRANCH); ?> :</th>
            <td>
                <?php echo $tJourney['Branch']['name']; ?>
            </td>
            <th style="width:10%;"><?php __(GENERAL_DESCRIPTION); ?> :</th>
            <td>
                <?php echo $tJourney['TJourney']['description']; ?>
            </td>
            <th style="width:10%;"><?php __(TABLE_DIRECTION); ?> :</th>
            <td>
                <?php
                $destFrom = '';
                $destTo   = '';
                $sqlDes = mysql_query("SELECT id, name FROM t_destinations WHERE id IN (".$tJourney['TJourney']['t_destination_from_id'].", ".$tJourney['TJourney']['t_destination_to_id'].")");
                while($rowDes = mysql_fetch_array($sqlDes)){
                    if($rowDes['id'] == $tJourney['TJourney']['t_destination_from_id']){
                        $destFrom = $rowDes['name'];
                    } else {
                        $destTo   = $rowDes['name'];
                    }
                }
                echo $destFrom." to ".$destTo;
                ?>
            </td>
            
        </tr>
        <tr>
            <th><?php __(TABLE_JOURNEY_DATE) ?> :</th>
            <td>
                <?php echo dateShort($date); ?>
            </td>
            <th><?php __(MENU_DEPARTURE_TIME); ?> :</th>
            <td>
                <?php echo date("h:i A", strtotime($tJourney['TDepartureTime']['name'])); ?>
            </td>
            <th><?php __(MENU_TRANSPORTATION_TYPE); ?> :</th>
            <td>
                <?php echo $tJourney['TTransportationType']['name']; ?>
            </td>
        </tr>
    </table>
</fieldset>
<fieldset style="width: 35%; float: left; min-height: 300px;">
    <legend><?php __(TABLE_SEAT_INFORMATION); ?></legend>
    <?php
    $layouts = json_decode($tBoat['TTransportationType']['layout'], true);
    $tableLayout = '';
    $tableWeight = 105;
    $totalCol = 0;
    $seatInactive = array();
    // Get Seat In Active
    foreach($tSeatControlls AS $tSeatControll){
        if(strtotime($date) == strtotime(date("Y-m-d"))){
            $status = $tSeatControll['TSeatControl']['status'];
        } else {
            $status = $tSeatControll['TSeatControl']['status'];
        }
        if($status == 2){
            $status = 1;
        } else if($status == 1) {
            $status = 2;
        } else {
            $status = 3;
        }
        if(empty($tSeatControll['TTicket']['id'])){
            $sqlTck = mysql_query("SELECT * FROM t_ticket_3months WHERE id = ".$tSeatControll['TSeatControl']['t_ticket_id']);
            $rowTck = mysql_fetch_array($sqlTck);
            $tSeatControll['TTicket']['id'] = $rowTck['id'];
            $tSeatControll['TTicket']['confirm_by'] = $rowTck['confirm_by'];
            $tSeatControll['TTicket']['created_by'] = $rowTck['created_by'];
            $tSeatControll['TTicket']['t_destination_to_id'] = $rowTck['t_destination_to_id'];
            $tSeatControll['TTicket']['code'] = $rowTck['code'];
            $tSeatControll['TTicket']['price_type'] = $rowTck['price_type'];
            $tSeatControll['TTicket']['t_agent_id'] = $rowTck['t_agent_id'];
            $tSeatControll['TTicket']['agt_refer_code'] = $rowTck['agt_refer_code'];
        }   
        if($tSeatControll['TTicket']['confirm_by'] != ''){
            $createdBy = $tSeatControll['TTicket']['confirm_by'];
        } else {
            $createdBy = $tSeatControll['TTicket']['created_by'];
        }
        $username = "";
        if(!empty($createdBy)){
            $sqlUser = mysql_query("SELECT first_name, last_name FROM users WHERE id = ".$createdBy);
            $rowUser = mysql_fetch_array($sqlUser);
            $username = $rowUser['first_name']." ".$rowUser['last_name'];
        }
        // Destination To
        $destTo  = '';
        if(!empty($tSeatControll['TTicket']['t_destination_to_id'])){
            $sqlDest = mysql_query("SELECT code FROM t_destinations WHERE id = ".$tSeatControll['TTicket']['t_destination_to_id']);
            $rowDest = mysql_fetch_array($sqlDest);
            $destTo  = $rowDest[0];
        }
        $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['status'] = $status;
        $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['code'] = $tSeatControll['TTicket']['code'];
        $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['user'] = $username;
        $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['dest'] = $destTo;
        if($tSeatControll['TTicket']['price_type'] == 3){
            $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['type'] = '(VIP)';
        } else {
            $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['type'] = '';
        }
        if(!empty($tSeatControll['TTicket']['t_agent_id'])){
            $sqlAg = mysql_query("SELECT code, name FROM t_agents WHERE id = ".$tSeatControll['TTicket']['t_agent_id']);
            $rowAg = mysql_fetch_array($sqlAg);
            $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['agency'] = $rowAg['code']." - ".$rowAg['name'];
            $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['ref'] = $tSeatControll['TTicket']['agt_refer_code'];
        } else {
            $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['agency'] = '';
            $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['ref'] = '';
        }
    }
    // List Seat
    foreach($layouts AS $layout){
        $cols = $layout['col'];
        $tableLayout .= '<tr>';
        $totalCol = 0;
        foreach($cols AS $col){
            $colspan = $col['attr']['colspan'];
            $value   = $col['value'];
            $label   = $value;
            if (array_key_exists("label", $col)) {
                $label = $col['label'];
            }
            $attrCol = '';
            if($colspan != ''){
                $attrCol = 'colspan="'.$colspan.'"';
                $totalCol = $totalCol + $colspan;
            } else {
                $totalCol++;
            }
            if(is_numeric($value)){
                $tableLayout .= '<td '.$attrCol.' style="height: 40px; width: '.$tableWeight.'px; text-align: left; vertical-align: middle; font-size: 10px;">';
                $seatImg = 'seating-active-25.png';
                $ticket  = '';
                if(!empty($seatInactive[$value])){
                    if($seatInactive[$value]['status'] == 1){
                        $seatImg = 'seat-sold.png';
                    } else if ($seatInactive[$value]['status'] == 2) {
                        $seatImg = 'seat-booked.png';
                    }
                    $ticket  = '<br/>'.$seatInactive[$value]['code'].' '.$seatInactive[$value]['type'].'<br/>DT:'.$seatInactive[$value]['dest'].'<br/>'.$seatInactive[$value]['user'].'<br/>'.$seatInactive[$value]['agency'].'<br/>'.$seatInactive[$value]['ref'];
                    $tableLayout .= '<img src="'.$this->webroot.'img/button/'.$seatImg.'" style="width: 12px;" /> '.$label.$ticket;
                } else {
                    $tableLayout .= '<input type="checkbox" lbl="'.$label.'" class="blockSeatSelect" value="'.$value.'" style="cursor: pointer;" />'.'<img src="'.$this->webroot.'img/button/'.$seatImg.'" style="width: 12px;" /> '.$label;
                }
            } else {
                $tableLayout .= '<td '.$attrCol.' style="height: 20px; width: '.$tableWeight.'px; text-align: center; vertical-align: middle;">';
                if($label == 'Open1' || $label == 'Open2') {
                    $tableLayout .= 'Open Air Seat';
                } else {
                    $tableLayout .= $label;
                }
            }
            $tableLayout .= '</td>';
        }
        $tableLayout .= '</tr>';
    }
    $totalTableWeight = $tableWeight * $totalCol;
    ?>
    <table cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="vertical-align: top;">
                <table cellpadding="5" cellspacing="0" style="width: <?php echo $totalTableWeight; ?>px;">
                    <?php echo $tableLayout; ?>
                </table>
            </td>
        </tr>
    </table>
</fieldset>
<fieldset style="width: 60%; float: right; min-height: 300px;">
    <legend><?php __(TABLE_BLOCK_SEAT_DETAIL); ?></legend>
    <form id="BlockSeatForm" method="post" action="<?php echo $this->base . '/' . $this->params['controller']; ?>/saveBlockSeat" accept-charset="utf-8">
        <input type="hidden" name="data[TJourneySeatBlock][id]" value="<?php echo $blockSeatId; ?>" />
        <input type="hidden" name="data[TJourneySeatBlock][t_journey_id]" value="<?php echo $tJourney['TJourney']['id']; ?>" />
        <input type="hidden" name="data[TJourneySeatBlock][t_departure_time_id]" value="<?php echo $tJourney['TDepartureTime']['id']; ?>" />
        <table cellpadding="5" style="width: 100%;">
            <tr>
                <td style="width: 90px;">
                    <div class="inputContainer" style="width: 100%;">
                        <input type="radio" class="rdiSeatBlock" value="2" name="data[TJourneySeatBlock][type]" id="rdiCustomizeSeatBlock" <?php if($type == 2){ ?>checked=""<?php } ?> <?php if($blockSeatId != ''){ ?>disabled=""<?php } ?> /><label for="rdiCustomizeSeatBlock"><?php echo TABLE_CUSTOMIZE; ?></label>
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <!--<input type="radio" class="rdiSeatBlock" value="3" name="data[TJourneySeatBlock][type]" id="rdiPermanentSeatBlock" <?php if($type == 3){ ?>checked=""<?php } ?> /><label for="rdiPermanentSeatBlock"><?php echo TABLE_PERMANENT; ?></label>-->
                    </div>
                </td>
                <td style="width: 60px;">
                    <div class="inputContainer" style="width: 100%;">
                        <!--<input type="radio" class="rdiSeatBlock" value="1" name="data[TJourneySeatBlock][type]" id="rdiNoneSeatBlock" <?php if($type == 1){ ?>checked=""<?php } ?> /><label for="rdiNoneSeatBlock"><?php echo TABLE_NONE; ?></label>-->
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <div id="divNoneSeatBlock" style="width: 100%; display: none;">
                        <?php echo TABLE_RELEASE_TIME; ?> (Hours) 
                        <select id="blockReleaseTime" style="width: 50px;" name="data[TJourneySeatBlock][release_date]">
                            <?php
                            for($h=0; $h<24; $h++){
                            ?>
                            <option value="<?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?>"><?php echo str_pad($h,2,"0",STR_PAD_LEFT); ?></option>
                            <?php
                            }
                            ?>
                        </select> (before departure)
                    </div>
                    <div id="divCustomizeSeatBlock" style="width: 100%; <?php if($type != 2){ ?>display: none;<?php } ?>">
                        <div class="inputContainer" style="width: 100%;">
                            <?php echo TABLE_START_DATE; ?> : <input type="text" id="blockSeatStart" name="data[TJourneySeatBlock][start]" style="width: 100px; height: 25px;" value="<?php echo $start; ?>" class="validate[required]" <?php if($blockSeatId != ''){ ?>disabled=""<?php } ?> />
                            <?php echo TABLE_END_DATE; ?> : <input type="text" id="blockSeatEnd" name="data[TJourneySeatBlock][end]" style="width: 100px; height: 25px;" value="<?php echo $end; ?>" class="validate[required]" <?php if($blockSeatId != ''){ ?>disabled=""<?php } ?> />
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <table class="table" cellpadding="0" style="width: 100%;">
            <thead>
                <tr>
                    <th class="first" style="width: 10%;"><?php echo TABLE_SEAT; ?></th>
                    <th><?php echo TABLE_APPLY_TO; ?></th>
                    <th style="width: 10%;"><?php echo TABLE_TYPE; ?></th>
                    <th style="width: 10%;"></th>
                </tr>
            </thead>
            <tbody id="rowBlockSeatBody">
                <tr class="rowBlockSeatList" id="rowBlockSeatList">
                    <td class="first" style="text-align: center;">
                        <input type="hidden" class="blockSeatId" name="data[TJourneySeatBlock][seat_number][]" />
                        <input type="hidden" class="blockSeatLbl" name="data[TJourneySeatBlock][seat_label][]" />
                        <input type="hidden" value="1" class="blockSeatMale" name="data[TJourneySeatBlock][bk_male][]" />
                        <input type="hidden" value="1" class="blockSeatFemale" name="data[TJourneySeatBlock][bk_female][]" />
                        <input type="hidden" value="1" class="blockSeatSlave" name="data[TJourneySeatBlock][bk_slave][]" />
                        <input type="hidden" value="1" class="blockSeatEticket" name="data[TJourneySeatBlock][bk_eticket][]" />
                        <input type="hidden" value="1" class="blockSeatApi" name="data[TJourneySeatBlock][bk_api][]" />
                        <input type="hidden" value="1" class="blockSeatAgency" name="data[TJourneySeatBlock][bk_agency][]" />
                        <span class="blockSeatLabel"></span>
                    </td>
                    <td>
                        <div class="inputContainer" style="width: 100%;">
                            <input type="checkbox" class="blockMaleCheck validate[minCheckbox[1]]" checked="" /> <label class="lblMaleCheck">Male</label>
                            <input type="checkbox" class="blockFemaleCheck validate[minCheckbox[1]]" checked="" /> <label class="lblFemaleCheck">Female</label>
                            <input type="checkbox" class="blockSlaveCheck validate[minCheckbox[1]]" checked="" /> <label class="lblSlaveCheck">Slave</label>
                            <input type="checkbox" class="blockEticketCheck validate[minCheckbox[1]]" checked="" /> <label class="lblEticketCheck">E-Tkt</label>
                            <input type="checkbox" class="blockApiCheck validate[minCheckbox[1]]" checked="" /> <label class="lblApiCheck">API</label>
                            <input type="checkbox" class="blockAgencyCheck validate[minCheckbox[1]]" checked="" /> <label class="lblAgtCheck">Agt</label>
                        </div>
                    </td>
                    <td>
                        
                    </td>
                    <td>
                        <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveBlockSeat" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                    </td>
                </tr>
                <?php
                if($blockSeatId != ''){
                    $sqlD = mysql_query("SELECT * FROM t_journey_seat_block_details WHERE t_journey_seat_block_id = ".$blockSeatId);
                    while($rowD = mysql_fetch_array($sqlD)){
                ?>
                <tr class="rowBlockSeatList">
                    <td class="first" style="text-align: center;">
                        <input type="hidden" class="blockSeatId" name="data[TJourneySeatBlock][seat_number][]" value="<?php echo $rowD['seat_number']; ?>" />
                        <input type="hidden" class="blockSeatLbl" name="data[TJourneySeatBlock][seat_label][]" value="<?php echo $rowD['seat_label']; ?>" />
                        <input type="hidden" value="0" class="blockSeatMale" name="data[TJourneySeatBlock][bk_male][]" value="<?php echo $rowD['bk_male']; ?>" />
                        <input type="hidden" value="0" class="blockSeatFemale" name="data[TJourneySeatBlock][bk_female][]" value="<?php echo $rowD['bk_female']; ?>" />
                        <input type="hidden" value="0" class="blockSeatSlave" name="data[TJourneySeatBlock][bk_slave][]" value="<?php echo $rowD['bk_slave']; ?>" />
                        <input type="hidden" value="0" class="blockSeatEticket" name="data[TJourneySeatBlock][bk_eticket][]" value="<?php echo $rowD['bk_eticket']; ?>" />
                        <input type="hidden" value="0" class="blockSeatApi" name="data[TJourneySeatBlock][bk_api][]" value="<?php echo $rowD['bk_api']; ?>" />
                        <input type="hidden" value="0" class="blockSeatAgency" name="data[TJourneySeatBlock][bk_agency][]" value="<?php echo $rowD['bk_agency']; ?>" />
                        <span class="blockSeatLabel"><?php echo $rowD['seat_label']; ?></span>
                    </td>
                    <td>
                        <div class="inputContainer" style="width: 100%;">
                            <input type="checkbox" disabled="" class="blockMaleCheck" <?php if($rowD['bk_male'] == 1){ ?>checked=""<?php } ?> /> <label class="lblMaleCheck">Male</label>
                            <input type="checkbox" disabled="" class="blockFemaleCheck" <?php if($rowD['bk_female'] == 1){ ?>checked=""<?php } ?> /> <label class="lblFemaleCheck">Female</label>
                            <input type="checkbox" disabled="" class="blockSlaveCheck" <?php if($rowD['bk_slave'] == 1){ ?>checked=""<?php } ?> /> <label class="lblSlaveCheck">Slave</label>
                            <input type="checkbox" disabled="" class="blockEticketCheck" <?php if($rowD['bk_eticket'] == 1){ ?>checked=""<?php } ?> /> <label class="lblEticketCheck">E-Tkt</label>
                            <input type="checkbox" disabled="" class="blockApiCheck" <?php if($rowD['bk_api'] == 1){ ?>checked=""<?php } ?> /> <label class="lblApiCheck">API</label>
                            <input type="checkbox" disabled="" class="blockAgencyCheck" <?php if($rowD['bk_agency'] == 1){ ?>checked=""<?php } ?> /> <label class="lblAgtCheck">Agt</label>
                        </div>
                    </td>
                    <td>
                        <?php
                        if($rowBk['type'] == 2){
                            echo TABLE_CUSTOMIZE;
                        } else if($rowBk['type'] == 3) {
                            echo TABLE_PERMANENT;
                        }
                        ?>
                    </td>
                    <td>
                        <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveBlockSeat" align="absmiddle" style="cursor: pointer; display: none;" onmouseover="Tip('Remove')" />
                    </td>
                </tr>
                <?php
                    }
                }
                ?>
            </tbody>
        </table>
        <div style="width: 100%; margin-top: 10px;">
            <div class="buttons">
                <button type="submit" class="positive btnSaveBlockSeat">
                    <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                    <span class="txtSaveBlockSeat"><?php echo ACTION_SAVE; ?></span>
                </button>
            </div>
<!--            <div class="buttons">
                <button type="button" class="positive btnEditBlockSeat" style="display: none;" disabled="">
                    <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                    <span class="txtEditBlockSeat"><?php echo ACTION_EDIT; ?></span>
                </button>
            </div>-->
            <div class="buttons">
                <button type="button" class="positive btnUnblockBlockSeat" style="display: none;" disabled="">
                    <img src="<?php echo $this->webroot; ?>img/button/stop.png" alt=""/>
                    <span class="txtUnblockBlockSeat"><?php echo ACTION_UNBLOCK; ?></span>
                </button>
            </div>
            <div style="clear: both;"></div>
        </div>
    </form>
</fieldset>
<div style="clear: both;"></div>