<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackBusSchedule").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableBusSchedule.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });

        $(".btnUpdateUnScanNote").unbind("click").click(function(){
            var seatNumber  = $(this).attr("data");
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/".$this->params['controller']."/updateUnscanNote/".$this->data['BusSchedule']['id']; ?>/"+seatNumber,
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
                        height: 100,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $(".ui-dialog-titlebar-close").hide();
                        },
                        close: function(event, ui){
                            $(".ui-dialog-titlebar-close").show();
                        }
                    });
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialogModal").dialog("close");
                    $("#dialog").html(msg).dialog({
                        title: 'Update Un-Scaned Note',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName = "#BusScheduleUpdateUnscanNoteForm";
                                var validateBack = $(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                } else {
                                    $.ajax({
                                        dataType: "json",
                                        type: "POST",
                                        url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/saveUpdateUnscanNote/<?php echo $this->data['BusSchedule']['id']; ?>/"+seatNumber,
                                        data: $(formName).serialize(),
                                        beforeSend: function(){
                                            $("#dialog").dialog("close");
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                            // modal box - open
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
                                                }
                                            });
                                        },
                                        error: function(jqXHR, exception) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            $("#dialogModal").dialog("close");
                                            refreshViewBusSchedule();
                                            createSysAct('Journey Bus', 'Update Note', 2, jqXHR.responseText);
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
                                                close: function(){
                                                    $(this).dialog({close: function(){}});
                                                    $(this).dialog("close");
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
                                            refreshViewBusSchedule();
                                            createSysAct('Journey Bus', 'Update Note', 1, '');
                                            if(result.error == "0"){
                                                // Alert Message
                                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                                            } else if(result.error == "1"){
                                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
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
    });

    function refreshViewBusSchedule(){
        var rightPanel = $(".btnBackBusSchedule").parent().parent().parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $this->data['BusSchedule']['id']; ?>");
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackBusSchedule">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php
$dataDest = array();
$sqlDest = mysql_query("SELECT * FROM t_destinations WHERE id IN (".$this->data['BusSchedule']['t_destination_from_id'].", ".$this->data['BusSchedule']['t_destination_to_id'].")");
while($rowDest = mysql_fetch_array($sqlDest)){
    $dataDest[$rowDest['id']] = $rowDest['name'];
}
?>
<fieldset style="width: 60%; float: left; min-height: 550px;">
    <legend><?php __(MENU_JOURNEY_BUS_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="width: 20%; font-size: 12px;"><?php __(TABLE_DESTINATION_FROM); ?> :</th>
            <td style="font-size: 12px;"><?php echo $dataDest[$this->data['BusSchedule']['t_destination_from_id']]; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_DESTINATION_TO); ?> :</th>
            <td style="font-size: 12px;"><?php echo $dataDest[$this->data['BusSchedule']['t_destination_to_id']]; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_DEPARTURE); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['BusSchedule']['departure']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(MENU_BUS); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Bus']['name']; ?></td>
        </tr>
    </table>
    <br/>
    <table class="table" cellspacing="0" style="padding:0px; width: 98%;">
        <tr>
            <th class="first" style="width:10%;"><?php echo TABLE_NO; ?></th>
            <th style="width:40%;"><?php echo GENERAL_DESCRIPTION; ?></th>
            <th style="width:20%;"><?php echo REPORT_FROM; ?></th>
            <th style="width:20%;"><?php echo REPORT_TO; ?></th>
            <th style="width:10%;"><?php echo TABLE_DEPARTURE; ?></th>
        </tr>
        <?php
        $index = 0;
        $sqlJourney = mysql_query("SELECT t_journeys.*, t_departure_times.name AS departure,  t_destinations.name AS destFromName, destTo.name AS destToName
                                   FROM bus_schedule_details
                                   INNER JOIN t_journeys ON t_journeys.id = bus_schedule_details.t_journey_id
                                   INNER JOIN t_destinations ON t_destinations.id = t_journeys.t_destination_from_id
                                   INNER JOIN t_destinations AS destTo ON destTo.id = t_journeys.t_destination_to_id
                                   INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id
                                   WHERE bus_schedule_details.bus_schedule_id = ".$this->data['BusSchedule']['id']);
        while($rowJourney = mysql_fetch_array($sqlJourney)){
        ?>
            <tr>
                <td class="first" style="width:10%; height: 30px;"><?php echo ++$index; ?></td>
                <td style="width:40%;"><?php echo $rowJourney['description']; ?></td>
                <td style="width:20%;"><?php echo $rowJourney['destFromName']; ?></td>
                <td style="width:20%;"><?php echo $rowJourney['destToName']; ?></td>
                <td style="width:10%;"><?php echo $rowJourney['departure']; ?></td>
            </tr>
        <?php
        }
        ?>
    </table>
</fieldset>
<fieldset style="width: 36%; float: right; min-height: 550px;">
    <legend><?php __("Seat Information"); ?></legend>
    <?php
    $routeId = "";
    $transportaionId = "";
    $sqlTransportation = mysql_query("SELECT t_journeys.id AS journey_id, t_journeys.t_route_id, t_journeys.t_transportation_type_id, t_journeys.type
                                      FROM t_journeys
                                      INNER JOIN bus_schedule_details ON bus_schedule_details.t_journey_id = t_journeys.id
                                      WHERE t_journeys.type IN (1,3) AND bus_schedule_details.bus_schedule_id = ".$this->data['BusSchedule']['id']." ORDER BY bus_schedule_details.id DESC LIMIT 1;");
    if(mysql_num_rows($sqlTransportation)){
        $rowTransportation = mysql_fetch_array($sqlTransportation);
        if($rowTransportation['type'] == 3){    
            $sqlTransit = mysql_query("SELECT t_journeys.t_transportation_type_id, t_journeys.t_route_id, t_journeys.id AS journey_id, t_journeys.type
                                    FROM t_journeys
                                    INNER JOIN t_journey_transits ON t_journey_transits.t_journey_departure_id = t_journeys.id
                                    WHERE t_journey_transits.t_journey_id = ".$rowTransportation['journey_id']."
                                    GROUP BY t_journey_transits.t_journey_departure_id LIMIT 1;");
            if(mysql_num_rows($sqlTransit)){
                $rowTransit = mysql_fetch_array($sqlTransit);
                $routeId = $rowTransit['t_route_id'];
                $transportaionId = $rowTransit['t_transportation_type_id'];
            }
        } else {
            $routeId = $rowTransportation['t_route_id'];
            $transportaionId = $rowTransportation['t_transportation_type_id'];
        }
    }
    if(!empty($transportaionId) && !empty($routeId)){
        $sqlBoat = mysql_query("SELECT * FROM t_transportation_types WHERE id = ".$transportaionId);
        $rowBoat = mysql_fetch_array($sqlBoat);
        $layouts = json_decode($rowBoat['layout'], true);
        $tableLayout = '';
        $tableWeight = 105;
        $totalCol = 0;
        $seatInactive = array();
        $sqlSeatC = mysql_query("SELECT t_seat_controls.status, t_seat_controls.seat_number, t_seat_controls.is_pickup, t_tickets.code, t_tickets.price_type, t_tickets.t_agent_id, t_tickets.agt_refer_code, t_tickets.confirm_by, t_tickets.created_by, t_tickets.t_destination_from_id, t_tickets.t_destination_to_id, t_tickets.t_journey_transit_id FROM t_seat_controls INNER JOIN t_tickets ON t_tickets.id = t_seat_controls.t_ticket_id WHERE t_seat_controls.t_transportation_type_id = ".$transportaionId." AND t_seat_controls.t_route_id = ".$routeId." AND t_seat_controls.journey_date = '".$this->data['BusSchedule']['date']."' AND t_seat_controls.status IN (1,2,3)");
        while($rowSeatC = mysql_fetch_array($sqlSeatC)){
            $status = $rowSeatC['status'];
            if($status == 2){
                $status = 1;
            } else if($status == 1) {
                $status = 2;
            } else {
                $status = 3;
            }
            $username = "";
            if($rowSeatC['confirm_by'] != ''){
                $createdBy = $rowSeatC['confirm_by'];
            } else {
                $createdBy = $rowSeatC['created_by'];
            }
            if(!empty($createdBy)){
                $sqlUser = mysql_query("SELECT first_name, last_name FROM users WHERE id = ".$createdBy);
                $rowUser = mysql_fetch_array($sqlUser);
                $username = $rowUser['first_name']." ".$rowUser['last_name'];
            }
            // Destination From
            $destFrom  = '';
            if(!empty($rowSeatC['t_destination_from_id'])){
                $transit = '';
                if($rowSeatC['t_journey_transit_id'] != ''){
                    $transit = '(Transit)';
                }
                $sqlDest = mysql_query("SELECT code FROM t_destinations WHERE id = ".$rowSeatC['t_destination_from_id']);
                $rowDest = mysql_fetch_array($sqlDest);
                $destFrom  = $rowDest[0].' '.$transit.'';
            }
            // Destination To
            $destTo  = '';
            if(!empty($rowSeatC['t_destination_to_id'])){
                $transit = '';
                if($rowSeatC['t_journey_transit_id'] != ''){
                    $transit = '(Transit)';
                }
                $sqlDest = mysql_query("SELECT code FROM t_destinations WHERE id = ".$rowSeatC['t_destination_to_id']);
                $rowDest = mysql_fetch_array($sqlDest);
                $destTo  = $rowDest[0].' '.$transit.'';
            }
            $seatInactive[$rowSeatC['seat_number']]['status'] = $status;
            $seatInactive[$rowSeatC['seat_number']]['code'] = $rowSeatC['code'];
            $seatInactive[$rowSeatC['seat_number']]['user'] = $username;
            $seatInactive[$rowSeatC['seat_number']]['dest_from'] = $destFrom;
            $seatInactive[$rowSeatC['seat_number']]['dest'] = $destTo;
            if($rowSeatC['price_type'] == 3){
                $seatInactive[$rowSeatC['seat_number']]['type'] = '(VIP)';
            } else {
                $seatInactive[$rowSeatC['seat_number']]['type'] = '';
            }
            if($rowSeatC['t_agent_id'] != ''){
                $sqlAg = mysql_query("SELECT code, name FROM t_agents WHERE id = ".$rowSeatC['t_agent_id']);
                $rowAg = mysql_fetch_array($sqlAg);
                $seatInactive[$rowSeatC['seat_number']]['agency'] = $rowAg['code']." - ".$rowAg['name'];
                $seatInactive[$rowSeatC['seat_number']]['ref'] = $rowSeatC['agt_refer_code'];
            } else {
                $seatInactive[$rowSeatC['seat_number']]['agency'] = '';
                $seatInactive[$rowSeatC['seat_number']]['ref'] = '';
            }
            $seatInactive[$rowSeatC['seat_number']]['isPicked']     = $rowSeatC['is_pickup'];
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
                        $picked = "";
                        if($seatInactive[$value]['isPicked'] > 0){
                            $picked = '<br/><img src="'.$this->webroot.'img/button/active.png" style="width: 12px;" /> Scaned';
                        } else {
                            $note = "";
                            $sqlNote = mysql_query("SELECT * FROM bus_schedule_seat_notes WHERE bus_schedule_id = ".$this->data['BusSchedule']['id']." AND seat_number = '".$value."' AND is_active = 1 LIMIT 1");
                            if(mysql_num_rows($sqlNote)){
                                $rowNote = mysql_fetch_array($sqlNote);
                                $note = $rowNote['note'];
                            }
                            $picked = '<br/><img src="'.$this->webroot.'img/button/cross.png" style="width: 12px;" /> Un-Scaned <img style="cursor: pointer;" class="btnUpdateUnScanNote" data="'.$value.'" src="'.$this->webroot.'img/button/note.png" style="width: 12px;" /><br/>Note: '.$note;
                        }
                        $ticket  = '<br/>'.$seatInactive[$value]['code'].' '.$seatInactive[$value]['type'].'<br/>DF:'.$seatInactive[$value]['dest_from'].'<br/>DT:'.$seatInactive[$value]['dest'].'<br/>'.$seatInactive[$value]['user'].'<br/>AG:'.$seatInactive[$value]['agency'].'<br/>Ref:'.$seatInactive[$value]['ref'].$picked;
                    }
                    $tableLayout .= '<img src="'.$this->webroot.'img/button/'.$seatImg.'" style="width: 12px;" /> '.$label.$ticket;
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
    }
    ?>
    <table cellpadding="0" cellspacing="0" style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="width: 80%; vertical-align: top;">
                <table cellpadding="5" cellspacing="0" style="width: <?php echo $totalTableWeight; ?>px;">
                    <?php echo $tableLayout; ?>
                </table>
            </td>
            <td style="vertical-align: top;">
                <table cellpadding="5" cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style="text-align: center;">
                            <img src="<?php echo $this->webroot; ?>img/button/seating-active-25.png" style="width: 30px;" /> <br/>Available
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <img src="<?php echo $this->webroot; ?>img/button/seat-booked.png" style="width: 30px;" /> <br/>Booked
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <img src="<?php echo $this->webroot; ?>img/button/seat-sold.png" style="width: 30px;" /> <br/>Sold
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</fieldset>
<div style="clear: both;"></div>