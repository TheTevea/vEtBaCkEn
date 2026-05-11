<?php
// Authentication
$this->element('check_access');
$allowDiscount = checkAccess($user['User']['id'], $this->params['controller'], 'discount');

include("includes/function.php");
echo $this->element('prevent_multiple_submit');

$journeyPrice = $journey['TJourney']['unit_price'];
$journeyVip   = $journey['TJourney']['membership'];
$journeyFor   = $journey['TJourney']['foreigner_price'];
$sqlPmt = mysql_query("SELECT * FROM t_journey_promotions WHERE t_journey_id = ".$journey['TJourney']['id']." AND start >= '".$date."' AND end <= '".$date."' AND status = 1 ORDER BY id DESC LIMIT 1");
if(mysql_num_rows($sqlPmt)){
    $rowPmt = mysql_fetch_array($sqlPmt);
    $journeyPrice = $rowPmt['unit_price'];
    $journeyVip   = $rowPmt['membership'];
    $journeyFor   = $rowPmt['foreigner_price'];
}
?>
<script type="text/javascript">
    var rowIndexBooking<?php echo $journey['TJourney']['id']; ?> = '';
    var rowTableBooking<?php echo $journey['TJourney']['id']; ?> = $("#tblTicketBookingSeatList<?php echo $journey['TJourney']['id']; ?>");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Remove Table Seat
        $("#tblTicketBookingSeatList<?php echo $journey['TJourney']['id']; ?>").remove();
        // Chosen
        $("#ticketBookingBoardingPoint<?php echo $journey['TJourney']['id']; ?>, #ticketBookingDropOff<?php echo $journey['TJourney']['id']; ?>, #ticketBookingPickUp<?php echo $journey['TJourney']['id']; ?>").chosen({width: 150});
        
        $(".ticketBookingSeatSelect<?php echo $journey['TJourney']['id']; ?>").unbind("click").click(function(){
            var row = $(this).closest("td");
            var val = $(this).val();
            var lbl = $(this).attr("lbl");
            var journeySeat = $(this).attr("joruney");
            if($(this).is(":checked")){
                row.find(".ticketBookingSeatColor<?php echo $journey['TJourney']['id']; ?>").css('background', 'blue');
                addSeatBooking<?php echo $journey['TJourney']['id']; ?>(val, lbl);
            } else {
                row.find(".ticketBookingSeatColor<?php echo $journey['TJourney']['id']; ?>").css('background', 'none');
                $(".tblTicketBookingSeatList<?php echo $journey['TJourney']['id']; ?>").find(".seatNumber<?php echo $journey['TJourney']['id']; ?>").each(function(){
                    if($(this).val() == val){
                        // Remove Seat
                        $(this).closest("tr").remove();
                        calTotalAmtBookingTransit();
                    }
                });
            }
            convertSeatToString<?php echo $journey['TJourney']['id']; ?>(journeySeat);
        });
        
        // Check Price Type
        var price = $("#ticketBookingPrice<?php echo $journey['TJourney']['id']; ?>").attr('price');
        if($("input[name='data[TTicket][price_type]']:checked").val() == '2'){
            price = $("#ticketBookingPrice<?php echo $journey['TJourney']['id']; ?>").attr('vip');
        } else if($("input[name='data[TTicket][price_type]']:checked").val() == '3'){
            price = $("#ticketBookingPrice<?php echo $journey['TJourney']['id']; ?>").attr('foreigner');
        }
        $("#ticketBookingPrice<?php echo $journey['TJourney']['id']; ?>").val(converDicemalJS(price).toFixed(2));
        var journeyType = $(".TTicketType:checked").val();
        if(journeyType == '2'){
            var journeyFirstId = $("#listJourneyTransit").find('div.divTransitJourney').eq(0).attr('joruney');
            var telephone = $("#ticketBookingTelephone"+journeyFirstId).val();
            $("#ticketBookingTelephone<?php echo $journey['TJourney']['id']; ?>").val(telephone).attr('readonly', true);
        }
    });
    
    function convertSeatToString<?php echo $journey['TJourney']['id']; ?>(journeySeat){
        var seatLabel = [];
        var totalSeat = 0;
        $(".ticketBookingSeatSelect<?php echo $journey['TJourney']['id']; ?>").each(function(){
            if($(this).is(':checked')){
                seatLabel.push($(this).attr('lbl'));
                totalSeat++;
            }
        });
        $("#ticketBookingSeatLabel<?php echo $journey['TJourney']['id']; ?>").val(seatLabel);
        $("#ticketBookingSeatSelected<?php echo $journey['TJourney']['id']; ?>").val(totalSeat);
        $("#ticketBookingSeatSpan<?php echo $journey['TJourney']['id']; ?>").text(totalSeat);
        // Total Seat Selected First Journey
        var firstId   = $("#listJourneyTransit").find('div.divTransitJourney').eq(0).attr('joruney');
        var totalSeatFirst = $("#ticketBookingSeatSelected"+firstId).val();
        $("#ticketBookingTotalSelectedSeat").val(totalSeatFirst);
        // Check Total Selected
        if(firstId != journeySeat){
            var disabled = false;
            if(replaceNum(totalSeatFirst) == replaceNum(totalSeat)){
                disabled = true;
            }
            $(".ticketBookingSeatSelect<?php echo $journey['TJourney']['id']; ?>").each(function(){
                if($(this).is(":checked")){

                } else {
                    $(this).attr("disabled", disabled);
                }
            });
        }
    }
    
    function addSeatBooking<?php echo $journey['TJourney']['id']; ?>(seatId, seatLabel){
        rowIndexBooking<?php echo $journey['TJourney']['id']; ?> = Math.floor((Math.random() * 100000) + 1);
        var sysCode = "<?php echo SERVER_ID; ?>SC"+randomString(15);
        var price   = $("#ticketBookingPrice<?php echo $journey['TJourney']['id']; ?>").val();
        var tr      = rowTableBooking<?php echo $journey['TJourney']['id']; ?>.clone(true);
        tr.removeAttr("style").removeAttr("id");
        // Seat
        tr.find("td .seatSysCode<?php echo $journey['TJourney']['id']; ?>").val(sysCode);
        tr.find("td .seatNumber<?php echo $journey['TJourney']['id']; ?>").val(seatId);
        tr.find("td .seatLabel<?php echo $journey['TJourney']['id']; ?>").val(seatLabel);
        tr.find("td .seatSelectedLabel<?php echo $journey['TJourney']['id']; ?>").text(seatLabel);
        // Lable Check
        tr.find("td .seatMale<?php echo $journey['TJourney']['id']; ?>").attr("id", "seatMale"+rowIndexBooking<?php echo $journey['TJourney']['id']; ?>);
        tr.find("td .seatFemale<?php echo $journey['TJourney']['id']; ?>").attr("id", "seatFemale"+rowIndexBooking<?php echo $journey['TJourney']['id']; ?>);
        tr.find("td .seatMale<?php echo $journey['TJourney']['id']; ?>").attr("name", "chkGender"+rowIndexBooking<?php echo $journey['TJourney']['id']; ?>);
        tr.find("td .seatFemale<?php echo $journey['TJourney']['id']; ?>").attr("name", "chkGender"+rowIndexBooking<?php echo $journey['TJourney']['id']; ?>);
        tr.find("td .seatPrice<?php echo $journey['TJourney']['id']; ?>").attr("id", "seatPrice"+rowIndexBooking<?php echo $journey['TJourney']['id']; ?>).val(price);
        tr.find("td .seatDiscount<?php echo $journey['TJourney']['id']; ?>").attr("id", "seatDiscount"+rowIndexBooking<?php echo $journey['TJourney']['id']; ?>);
        tr.find("td .seatTotal<?php echo $journey['TJourney']['id']; ?>").attr("id", "seatTotal"+rowIndexBooking<?php echo $journey['TJourney']['id']; ?>).val(price);
        $("#tblTicketBookingSeat<?php echo $journey['TJourney']['id']; ?>").append(tr);
        eventKeySeatBooking<?php echo $journey['TJourney']['id']; ?>();
        calTotalAmtBookingTransit();
    }
    
    function eventKeySeatBooking<?php echo $journey['TJourney']['id']; ?>(){
        $(".seatDiscount<?php echo $journey['TJourney']['id']; ?>, .chkSeatGender<?php echo $journey['TJourney']['id']; ?>").unbind('click').unbind('blur').unbind('keyup');
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        
        $('.float').unbind("cut copy paste").bind("cut copy paste",function() {
            return false;
        });
        
        $(".seatDiscount<?php echo $journey['TJourney']['id']; ?>").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val("");
            }
        });
        
        $(".seatDiscount<?php echo $journey['TJourney']['id']; ?>").keyup(function(){
            var dis   = replaceNum($(this).val());
            var price = replaceNum($(this).closest("tr").find(".seatPrice<?php echo $journey['TJourney']['id']; ?>").val());
            if(dis > price){
                dis = price;
                $(this).val(converDicemalJS(price).toFixed(2));
            }
            if($("#ticketBookingChkSameDis<?php echo $journey['TJourney']['id']; ?>").is(":checked")){
                $(".tblTicketBookingSeatList<?php echo $journey['TJourney']['id']; ?>").find(".seatDiscount<?php echo $journey['TJourney']['id']; ?>").each(function(){
                    $(this).val(converDicemalJS(dis).toFixed(2));
                });
            }
            calTotalAmtBookingTransit();
        });
        
        $(".seatDiscount<?php echo $journey['TJourney']['id']; ?>").blur(function(){
            if($(this).val() == ''){
                $(this).val('0');
            }
        });
        
        $(".chkSeatGender<?php echo $journey['TJourney']['id']; ?>").click(function(){
            $(this).closest("tr").find(".seatGender<?php echo $journey['TJourney']['id']; ?>").val($(this).val());
        });
    }
</script>
<br />
<div style="width: 99%;">
    <input type="hidden" name="data[<?php echo $journey['TJourney']['id']; ?>][t_sys_code]" value="<?php echo $sysCode; ?>" />
    <table cellpadding="2" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="width: 12%;"><?php echo TABLE_BOARDING_POINT; ?> :</td>
            <td style="width: 16%;">
                <select style="width: 150px; height: 25px;" id="ticketBookingBoardingPoint<?php echo $journey['TJourney']['id']; ?>" name="data[<?php echo $journey['TJourney']['id']; ?>][t_boarding_point_id]">
                    <?php
                    $sqlBP = mysql_query("SELECT t_boarding_points.id, t_boarding_points.name FROM t_journey_boarding_points INNER JOIN t_boarding_points ON t_boarding_points.id = t_journey_boarding_points.t_boarding_point_id WHERE t_journey_boarding_points.t_journey_id = ".$journey['TJourney']['id']);
                    while($rowBP = mysql_fetch_array($sqlBP)){
                    ?>
                    <option value="<?php echo $rowBP['id']; ?>"><?php echo $rowBP['name']; ?></option>
                    <?php
                    }
                    ?>
                </select>
            </td>
            <td><?php echo TABLE_DROP_OFF; ?> :</td>
            <td>
                <select style="width: 150px; height: 25px;" id="ticketBookingDropOff<?php echo $journey['TJourney']['id']; ?>" name="data[<?php echo $journey['TJourney']['id']; ?>][t_drop_off_id]">
                    <?php
                    $sqlDF = mysql_query("SELECT t_drop_offs.id, t_drop_offs.name FROM t_journey_drop_offs INNER JOIN t_drop_offs ON t_drop_offs.id = t_journey_drop_offs.t_drop_off_id WHERE t_journey_drop_offs.t_journey_id = ".$journey['TJourney']['id']);
                    while($rowDF = mysql_fetch_array($sqlDF)){
                    ?>
                    <option value="<?php echo $rowDF['id']; ?>"><?php echo $rowDF['name']; ?></option>
                    <?php
                    }
                    ?>
                </select>
            </td>
            <td><?php echo MENU_PICK_UP; ?> :</td>
            <td>
                <select style="width: 150px; height: 25px;" id="ticketBookingPickUp<?php echo $journey['TJourney']['id']; ?>" name="data[<?php echo $journey['TJourney']['id']; ?>][t_pick_up_id]">
                    <option value=""><?php echo INPUT_SELECT; ?></option>
                    <?php
                    $sqlPK = mysql_query("SELECT t_pick_ups.id, t_pick_ups.name FROM t_pick_ups WHERE is_active = 1");
                    while($rowPK = mysql_fetch_array($sqlPK)){
                    ?>
                    <option value="<?php echo $rowPK['id']; ?>"><?php echo $rowPK['name']; ?></option>
                    <?php
                    }
                    ?>
                </select>
            </td>
            <td><?php echo TABLE_PRICE; ?> :</td>
            <td>
                <input type="text" name="data[<?php echo $journey['TJourney']['id']; ?>][t_price]" style="width: 40px; height: 20px;" id="ticketBookingPrice<?php echo $journey['TJourney']['id']; ?>" class="float" price="<?php echo number_format($journeyPrice, 2); ?>" vip="<?php echo number_format($journeyVip, 2); ?>" foreigner="<?php echo number_format($journeyFor, 2); ?>" value="<?php echo number_format($journeyPrice, 2); ?>" readonly="" /> <?php echo $journey['CurrencyCenter']['symbol']; ?>
            </td>
        </tr>
    </table>
    <br/>
    <div style="width: 49%; float: left;">
        <fieldset style="height: 310px; overflow-x: hidden; overflow-y: scroll;">
            <legend><?php __(TABLE_SEAT_INFORMATION); ?></legend>
            Selected <input type="text" style="width: 250px; height: 20px;" id="ticketBookingSeatLabel<?php echo $journey['TJourney']['id']; ?>" readonly="" /> Total <input type="text" name="data[<?php echo $journey['TJourney']['id']; ?>][total_seat]" id="ticketBookingSeatSelected<?php echo $journey['TJourney']['id']; ?>" style="width: 50px; height: 20px; border: none; font-size: 14px;" value="0" readonly="" />
            <?php
            $totalSAvbl   = 0;
            $totalSBooked = 0;
            $totalSSold   = 0;
            $totalSBusy   = 0;
            $layouts      = json_decode($tTransportaion['TTransportationType']['layout'], true);
            $tableLayout  = '';
            $tableWidth   = 28;
            $totalCol     = 0;
            $seatInactive = array();
            $destBooked   = array();
            // Get Seat In Active
            if($journey['TJourney']['type'] == 3){
                $seatBooked = array();
                $sqlTransit = mysql_query("SELECT t_transportation_type_id, t_route_id, t_journeys.id AS journey_id FROM t_journeys WHERE id IN (SELECT t_journey_departure_id FROM t_journey_transits WHERE t_journey_id = ".$journey['TJourney']['id']." GROUP BY t_journey_departure_id)");
                while($rowTransit = mysql_fetch_array($sqlTransit)){
                    // Get Seat Booked
                    $sqlSeat = mysql_query("SELECT seat_number, t_ticket_id, status, gender FROM t_seat_controls WHERE t_transportation_type_id = ".$rowTransit['t_transportation_type_id']." AND t_route_id = ".$rowTransit['t_route_id']." AND journey_date = '".$date."' AND status IN (1,2,3)");
                    while($rowSeat = mysql_fetch_array($sqlSeat)){
                        if (!array_key_exists($rowSeat['seat_number'], $seatBooked)) {
                            $seatBooked[$rowSeat['seat_number']]['ticket_id'] = $rowSeat['t_ticket_id'];
                            $seatBooked[$rowSeat['seat_number']]['status'] = $rowSeat['status'];
                            $seatBooked[$rowSeat['seat_number']]['gender'] = $rowSeat['gender'];
                        }
                    }
                }
                foreach($seatBooked AS $key => $tSeatControll){
                    $destId     = '';
                    $destTo     = '';
                    $mainBranch = '';
                    $agencyName = '';
                    $username   = '';
                    $ticketCode = '';
                    $ticketTel  = '';
                    $agencyRef  = '';
                    $note = '';
                    if(!empty($tSeatControll['ticket_id'])){
                        $sqlTicket = mysql_query("SELECT * FROM t_tickets WHERE id = ".$tSeatControll['ticket_id']);
                        $rowTicket = mysql_fetch_array($sqlTicket);
                        $ticketCode = $rowTicket['code'];
                        $ticketTel  = $rowTicket['telephone'];
                        $agencyRef  = $rowTicket['agt_refer_code'];
                        $note = $rowTicket['note'];
                        // Destination To
                        if(!empty($rowTicket['t_destination_to_id'])){
                            $sqlDest = mysql_query("SELECT id, name FROM t_destinations WHERE id = ".$rowTicket['t_destination_to_id']);
                            $rowDest = mysql_fetch_array($sqlDest);
                            $destId  = $rowDest['id'];
                            $destTo  = $rowDest['name'];
                        }
                        // Main Branch
                        if(!empty($rowTicket['main_branch_id'])){
                            $sqlMain = mysql_query("SELECT name FROM main_branches WHERE id = ".$rowTicket['main_branch_id']);
                            $rowMain = mysql_fetch_array($sqlMain);
                            $mainBranch  = $rowMain[0];
                        }
                        // Agency
                        if(!empty($rowTicket['t_agent_id'])){
                            $sqlAgt = mysql_query("SELECT name, type FROM t_agents WHERE id = ".$rowTicket['t_agent_id']);
                            $rowAgt = mysql_fetch_array($sqlAgt);
                            $agencyName = $rowAgt['name'];
                            if($rowAgt['type'] == 2){ // Offline
                                $seatInactive[$key]['status'] = 6;
                            } else {
                                $seatInactive[$key]['status'] = 7;
                            }
                            $username = $rowAgt['name'];
                        } else {
                            if($tSeatControll['status'] != 3){
                                // User
                                if($rowTicket['confirm_by'] != ''){
                                    $createdBy = $rowTicket['confirm_by'];
                                } else {
                                    $createdBy = $rowTicket['created_by'];
                                }
                                $sqlUser = mysql_query("SELECT first_name, last_name FROM users WHERE id = ".$createdBy);
                                $rowUser = mysql_fetch_array($sqlUser);
                                $username = $rowUser['first_name']." ".$rowUser['last_name'];
                            } else {
                                $username = "";
                            }
                            $seatInactive[$key]['status'] = $tSeatControll['status'];
                        }
                    }
                    if(!empty($tTicket)){
                        if($tTicket['TTicket']['status'] == 1 && $tTicket['TTicket']['type'] == 2){
                            if($tSeatControll['ticket_id'] == $tTicket['TTicket']['id']){
                                $seatInactive[$key]['status'] = 4; // Phone Call (Checked)
                            }
                        }
                    }
                    if($tSeatControll['gender'] == 1){
                        $seatInactive[$key]['gender'] = '('.TABLE_M.')';
                    } else if ($tSeatControll['gender'] == 2){
                        $seatInactive[$key]['gender'] = '('.TABLE_F.')';
                    } else {
                        $seatInactive[$key]['gender'] = '';
                    }
                    $seatInactive[$key]['code']   = $ticketCode;
                    $seatInactive[$key]['tel']    = $ticketTel;
                    $seatInactive[$key]['user']   = $username;
                    $seatInactive[$key]['dest']   = $destTo;
                    $seatInactive[$key]['branch'] = $mainBranch;
                    $seatInactive[$key]['agency'] = $agencyName;
                    $seatInactive[$key]['reference'] = $agencyRef;
                    $seatInactive[$key]['note'] = $note;
                    if($destId != ''){
                        if (!array_key_exists($destId, $destBooked)) {
                            $destBooked[$destId]['name']  = $destTo;
                            $destBooked[$destId]['total'] = 1;
                        } else {
                            $destBooked[$destId]['total'] += 1;
                        }
                    }
                }
            } else {
                foreach($tSeatControlls AS $tSeatControll){
                    // User
                    if($tSeatControll['TTicket']['confirm_by'] != ''){
                        $createdBy = $tSeatControll['TTicket']['confirm_by'];
                    } else {
                        $createdBy = $tSeatControll['TTicket']['created_by'];
                    }
                    $sqlUser = mysql_query("SELECT first_name, last_name FROM users WHERE id = ".$createdBy);
                    $rowUser = mysql_fetch_array($sqlUser);
                    // Destination To
                    $destTo  = '';
                    if(!empty($tSeatControll['TTicket']['t_destination_to_id'])){
                        $sqlDest = mysql_query("SELECT name FROM t_destinations WHERE id = ".$tSeatControll['TTicket']['t_destination_to_id']);
                        $rowDest = mysql_fetch_array($sqlDest);
                        $destTo  = $rowDest[0];
                    }
                    $mainBranch = '';
                    if(!empty($tSeatControll['TTicket']['main_branch_id'])){
                        $sqlMain = mysql_query("SELECT name FROM main_branches WHERE id = ".$tSeatControll['TTicket']['main_branch_id']);
                        $rowMain = mysql_fetch_array($sqlMain);
                        $mainBranch  = $rowMain[0];
                    }
                    $agencyName = '';
                    if(!empty($tSeatControll['TTicket']['t_agent_id'])){
                        $sqlAgt = mysql_query("SELECT name FROM t_agents WHERE id = ".$tSeatControll['TTicket']['t_agent_id']);
                        $rowAgt = mysql_fetch_array($sqlAgt);
                        $agencyName = $rowAgt[0];
                        $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['status'] = 6;
                    } else {
                        $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['status'] = $tSeatControll['TSeatControl']['status'];
                    }
                    if(!empty($tTicket)){
                        if($tTicket['TTicket']['status'] == 1 && $tTicket['TTicket']['type'] == 2){
                            if($tSeatControll['TSeatControl']['t_ticket_id'] == $tTicket['TTicket']['id']){
                                $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['status'] = 4; // Phone Call (Checked)
                            }
                        }
                    }
                    if($tSeatControll['TSeatControl']['gender'] == 1){
                        $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['gender'] = '('.TABLE_M.')';
                    } else if ($tSeatControll['TSeatControl']['gender'] == 2){
                        $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['gender'] = '('.TABLE_F.')';
                    } else {
                        $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['gender'] = '';
                    }
                    $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['code']   = $tSeatControll['TTicket']['code'];
                    $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['tel']    = $tSeatControll['TTicket']['telephone'];
                    $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['user']   = $rowUser['first_name']." ".$rowUser['last_name'];
                    $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['dest']   = $destTo;
                    $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['branch'] = $mainBranch;
                    $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['agency'] = $agencyName;
                    $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['reference'] = $tSeatControll['TTicket']['agt_refer_code'];
                    $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['note'] = $tSeatControll['TTicket']['note'];
                }
            }
            // Check Seat Block
            $sqlBlock = mysql_query("SELECT t_journey_seat_blocks.id, t_journey_seat_blocks.type, t_journey_seat_blocks.start, t_journey_seat_blocks.end, t_journey_seat_block_details.seat_number FROM t_journey_seat_blocks INNER JOIN t_journey_seat_block_details ON t_journey_seat_block_details.t_journey_seat_block_id = t_journey_seat_blocks.id WHERE t_journey_seat_blocks.start <= '".$date."' AND t_journey_seat_blocks.end >= '".$date."' AND t_journey_seat_blocks.t_journey_id = ".$journey['TJourney']['id']." AND t_journey_seat_blocks.t_departure_time_id = ".$journey['TJourney']['t_departure_time_id']." AND t_journey_seat_blocks.is_active = 1");
            while($rowBlock = mysql_fetch_array($sqlBlock)){
                if($rowBlock['type'] == 2){
                    if(strtotime($rowBlock['start']) >= strtotime($date) && strtotime($date) <= strtotime($rowBlock['end'])){
                        $seatInactive[$rowBlock['seat_number']]['status'] = 3; // Busy (Block)
                        $seatInactive[$rowBlock['seat_number']]['gender'] = '';
                        $seatInactive[$rowBlock['seat_number']]['code']   = "";
                        $seatInactive[$rowBlock['seat_number']]['tel']    = "";
                        $seatInactive[$rowBlock['seat_number']]['user']   = "";
                        $seatInactive[$rowBlock['seat_number']]['dest']   = "";
                        $seatInactive[$rowBlock['seat_number']]['branch'] = "";
                        $seatInactive[$rowBlock['seat_number']]['agency'] = "";
                        $seatInactive[$rowBlock['seat_number']]['reference'] = "";
                        $seatInactive[$rowBlock['seat_number']]['note'] = "";
                    } else {
                        if(strtotime($rowBlock['end']) < strtotime(date("Y-m-d"))){
                            // Update Expired
                            mysql_query("UPDATE t_journey_seat_blocks SET is_active = 3 WHERE id = ".$rowBlock['id']);
                        }
                    }
                } else if($rowBlock['type'] == 3){
                    $seatInactive[$rowBlock['seat_number']]['status'] = 3; // Busy (Block)
                    $seatInactive[$rowBlock['seat_number']]['gender'] = '';
                    $seatInactive[$rowBlock['seat_number']]['code']   = "";
                    $seatInactive[$rowBlock['seat_number']]['tel']    = "";
                    $seatInactive[$rowBlock['seat_number']]['user']   = "";
                    $seatInactive[$rowBlock['seat_number']]['dest']   = "";
                    $seatInactive[$rowBlock['seat_number']]['branch'] = "";
                    $seatInactive[$rowBlock['seat_number']]['agency'] = "";
                    $seatInactive[$rowBlock['seat_number']]['reference'] = "";
                    $seatInactive[$rowBlock['seat_number']]['note'] = "";
                }
            }
            // List Seat
            foreach($layouts AS $layout){
                $cols = $layout['col'];
                $tableLayout .= '<tr>';
                $totalCol = 0;
                $totalSAgOff = 0;
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
                        $tableLayout .= '<td '.$attrCol.' style="height: 30px; width: '.$tableWidth.'px; text-align: center; vertical-align: middle; font-size: 10px;">';
                        $seatColor = '';
                        $ticket    = '';
                        $checked   = '';
                        if(!empty($seatInactive[$value])){
                            if($seatInactive[$value]['status'] == 2){ // Sold
                                $seatColor = 'background: green;';
                                $totalSSold++;
                            } else if ($seatInactive[$value]['status'] == 1) { // Phone Call
                                $seatColor = 'background: yellow;';
                                $totalSBooked++;
                            } else if ($seatInactive[$value]['status'] == 3) { // Busy
                                $seatColor = 'background: red;';
                                $totalSBusy++;
                            } else if($seatInactive[$value]['status'] == 4){
                                $checked   = '1';
                                $seatColor = 'background: yellow;';
                            } else if ($seatInactive[$value]['status'] == 6) { // Agency Offline
                                $seatColor = 'background: greenyellow;';
                                $totalSAgOff++;
                            }
                            if($checked != ''){
                                $tableLayout .= '<div style="width: 22px; height: 25px; background: url(../img/button/seat.png) center no-repeat;"><input type="checkbox" checked="" disabled="" lbl="'.$label.'" class="ticketBookingSeatSelect'.$journey['TJourney']['id'].'" joruney="'.$journey['TJourney']['id'].'" value="'.$value.'" style="cursor: pointer; margin-top: 8px;" /></div><hr style="width: 20px; height: 5px; padding: 0px; margin: 0px; border: 1px solid #000;'.$seatColor.'" />'.$label;
                            } else {
                                if(!empty($seatInactive[$value]['agency'])){
                                    $mouseOver = TABLE_TICKET_CODE.':<br/>'.$seatInactive[$value]['code'].'<br/>'.TABLE_DESTINATION_TO.':<br/>'.$seatInactive[$value]['dest'].'<br/>'.TABLE_TELEPHONE.':<br/>'.$seatInactive[$value]['tel'].'<br/>'.TABLE_SOLD_BY.':<br/>'.$seatInactive[$value]['user'].'<br/>'.TABLE_BRANCH.':<br/>'.$seatInactive[$value]['branch'].'<br/>'.MENU_AGENT.':<br/>'.$seatInactive[$value]['agency'].'<br/>'.TABLE_AGENT_REFERENCE.':<br/>'.$seatInactive[$value]['reference'].'<br/>'.TABLE_NOTE.':<br/>'.$seatInactive[$value]['note'];
                                } else {
                                    $mouseOver = TABLE_TICKET_CODE.':<br/>'.$seatInactive[$value]['code'].'<br/>'.TABLE_DESTINATION_TO.':<br/>'.$seatInactive[$value]['dest'].'<br/>'.TABLE_TELEPHONE.':<br/>'.$seatInactive[$value]['tel'].'<br/>'.TABLE_SOLD_BY.':<br/>'.$seatInactive[$value]['user'].'<br/>'.TABLE_BRANCH.':<br/>'.$seatInactive[$value]['branch'].'<br/>'.TABLE_NOTE.':<br/>'.$seatInactive[$value]['note'];
                                }
                                $tableLayout .= '<div onmouseover="Tip(\''.$mouseOver. '\')" style="width: 17px; height: 18px; background: url(../img/button/seat.png) center no-repeat; padding-top: 9px; padding-left: 7px;"><div style="width: 10px; height: 10px;'.$seatColor.'"></div></div><hr style="width: 20px; height: 5px; padding: 0px; margin: 0px; border: 1px solid #000;'.$seatColor.'" />'.$label.$seatInactive[$value]['gender'];
                            }
                        } else {
                            $tableLayout .= '<div style="width: 22px; height: 25px; background: url(../img/button/seat.png) center no-repeat;"><input type="checkbox" lbl="'.$label.'" class="ticketBookingSeatSelect'.$journey['TJourney']['id'].'" joruney="'.$journey['TJourney']['id'].'" value="'.$value.'" style="cursor: pointer; margin-top: 8px;" /></div><hr style="width: 20px; height: 5px; padding: 0px; margin: 0px; border: 1px solid #000;" class="ticketBookingSeatColor" />'.$label;
                            $totalSAvbl++;
                        }
                    } else {
                        $tableLayout .= '<td '.$attrCol.' style="height: 20px; width: '.$tableWidth.'px; text-align: center; vertical-align: middle;">';
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
            $totalTableWeight = $tableWidth * $totalCol;
            ?>
            <div style="clear: both;"></div>
            <div style="width: 78%; margin-top: 10px; float: left;">
                <table cellpadding="0" cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style="vertical-align: top;">
                            <table cellpadding="5" cellspacing="0" style="width: <?php echo $totalTableWeight; ?>px;">
                                <?php echo $tableLayout; ?>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="width: 20%; margin-top: 10px; float: right;">
                <hr style="width: 20px; height: 5px; padding: 0px; margin: 0px; border: 1px solid #000;" /> <?php echo $totalSAvbl; ?> <?php echo TABLE_AVAILABLE; ?>
                <hr style="width: 20px; height: 5px; padding: 0px; margin: 0px; border: 1px solid #000; background: blue; margin-top: 10px;" /> <span id="ticketBookingSeatSpan">0</span> <?php echo TABLE_SELECTED; ?>
                <hr style="width: 20px; height: 5px; padding: 0px; margin: 0px; border: 1px solid #000; background: green; margin-top: 10px;" /> <?php echo $totalSSold; ?> <?php echo TABLE_SOLD; ?>
                <hr style="width: 20px; height: 5px; padding: 0px; margin: 0px; border: 1px solid #000; background: greenyellow; margin-top: 10px;" /> <?php echo $totalSAgOff; ?> <?php echo TABLE_AGENCY_OFFLINE; ?>
                <hr style="width: 20px; height: 5px; padding: 0px; margin: 0px; border: 1px solid #000; background: yellow; margin-top: 10px;" /> <?php echo $totalSBooked; ?> <?php echo TABLE_PHONE_CALL; ?>
                <hr style="width: 20px; height: 5px; padding: 0px; margin: 0px; border: 1px solid #000; background: red; margin-top: 10px;" /> <?php echo $totalSBusy; ?> <?php echo TABLE_BUSY; ?><br />
                <?php
                $sqlCT = mysql_query("SELECT main_branches.name, COUNT(t_seat_controls.id) AS total FROM t_seat_controls INNER JOIN t_tickets ON t_tickets.id = t_seat_controls.t_ticket_id INNER JOIN main_branches ON main_branches.id = t_tickets.main_branch_id WHERE t_seat_controls.t_transportation_type_id = ".$trasportationId." AND t_seat_controls.t_route_id = ".$journey['TJourney']['t_route_id']." AND t_seat_controls.journey_date = '".$date."' AND t_seat_controls.status IN (1,2,3) GROUP BY t_tickets.main_branch_id");
                if(mysql_num_rows($sqlCT)){
                ?>
                <hr style="width: 100%; border-top: 1px solid #A5A8B8;" />
                <?php
                    while($rowCT = mysql_fetch_array($sqlCT)){
                ?>
                <div style="font-size: 12px; width: 100%;">
                    <span style="font-weight: bold;"><?php echo $rowCT['name']; ?></span><br /><?php echo $rowCT['total']; ?> <?php echo TABLE_TICKET; ?>
                </div>
                <?php
                    }
                }
                $sqlDt = mysql_query("SELECT t_destinations.name, COUNT(t_seat_controls.id) AS total FROM t_seat_controls INNER JOIN t_tickets ON t_tickets.id = t_seat_controls.t_ticket_id INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_to_id WHERE t_seat_controls.t_transportation_type_id = ".$trasportationId." AND t_seat_controls.t_route_id = ".$journey['TJourney']['t_route_id']." AND t_seat_controls.journey_date = '".$date."' AND t_seat_controls.status IN (1,2,3) GROUP BY t_tickets.t_destination_to_id");
                if(mysql_num_rows($sqlDt)){
                ?>
                <hr style="width: 100%; border-top: 1px solid #A5A8B8;" /> <span style="font-size: 14px; text-decoration: underline;">To :</span>
                <?php
                    while($rowDT = mysql_fetch_array($sqlDt)){
                ?>
                <div style="font-size: 12px; width: 100%;">
                    <span style="font-weight: bold;"><?php echo $rowDT['name']; ?></span><br /><?php echo $rowDT['total']; ?> <?php echo TABLE_TICKET; ?>
                </div>
                <?php
                    }
                }
                ?>
            </div>
            <div style="clear: both;"></div>
        </fieldset>
    </div>
    <div style="width: 49%; float: right;">
        <fieldset style="height: 310px;">
            <legend><?php __(TABLE_PASSENGER_DETAIL); ?></legend>
            <table cellpadding='5' cellspacing='0' style="width: 100%;">
                <tr>
                    <td style="width: 33%;"><input type="text" name="data[<?php echo $journey['TJourney']['id']; ?>][customer_name]" style="width: 90%; height: 20px;" placeholder="<?php echo TABLE_NAME; ?>" /></td>
                    <td style="width: 33%;"><input type="text" name="data[<?php echo $journey['TJourney']['id']; ?>][telephone]" class="validate[custom[phone]]" style="width: 90%; height: 20px;" id="ticketBookingTelephone<?php echo $journey['TJourney']['id']; ?>" placeholder="<?php echo TABLE_TELEPHONE; ?>" /></td>
                    <td><input type="text" name="data[<?php echo $journey['TJourney']['id']; ?>][email]" class="validate[custom[email]]" style="width: 90%; height: 20px;" placeholder="<?php echo TABLE_EMAIL; ?>" /></td>
                </tr>
            </table>
            <table cellpadding='2' cellspacing='0' style="width: 448px; padding: 0px;" class="table">
                <tr>
                    <th class="first" style="width: 15%;"><?php echo TABLE_SEAT; ?></th>
                    <th style="width: 33%;"><?php echo TABLE_GENDER; ?></th>
                    <th style="width: 17%;"><?php echo TABLE_PRICE; ?></th>
                    <th style="width: 18%;"><?php echo TABLE_DIS; ?></th>
                    <th style="width: 17%;"><?php echo TABLE_TOTAL; ?></th>
                </tr>
            </table>
            <div id="divTblTicketBookingSeat<?php echo $journey['TJourney']['id']; ?>" style="padding: 0px; width: 465px; height: 240px; overflow: auto;">
                <table id="tblTicketBookingSeat<?php echo $journey['TJourney']['id']; ?>" class="table" cellspacing="0" style="padding: 0px; width: 448px;">
                    <tr id="tblTicketBookingSeatList<?php echo $journey['TJourney']['id']; ?>" class="tblTicketBookingSeatList<?php echo $journey['TJourney']['id']; ?> tblTicketBookingSeatList" style="visibility: hidden;">
                        <td class="first" style="width: 15%;">
                            <input type="hidden" name="data[<?php echo $journey['TJourney']['id']; ?>][sys_code][]" class="seatSysCode<?php echo $journey['TJourney']['id']; ?>" />
                            <input type="hidden" name="data[<?php echo $journey['TJourney']['id']; ?>][seat_number][]" class="seatNumber<?php echo $journey['TJourney']['id']; ?> seatNumber" />
                            <input type="hidden" name="data[<?php echo $journey['TJourney']['id']; ?>][label_number][]" class="seatLabel<?php echo $journey['TJourney']['id']; ?>" />
                            <input type="hidden" name="data[<?php echo $journey['TJourney']['id']; ?>][is_special][]" class="seatSpecial<?php echo $journey['TJourney']['id']; ?>" value="0" />
                            <input type="hidden" name="data[<?php echo $journey['TJourney']['id']; ?>][gender][]" class="seatGender<?php echo $journey['TJourney']['id']; ?>" value="0" />
                            <span class="seatSelectedLabel<?php echo $journey['TJourney']['id']; ?>"></span>
                        </td>
                        <td style="width: 33%;">
                            <div class="inputContainer" style="width: 100%;">
                                <input type="radio" value="1" class="seatMale<?php echo $journey['TJourney']['id']; ?> chkSeatGender<?php echo $journey['TJourney']['id']; ?><?php if($journey['TJourney']['gender_require'] == 1){ ?> validate[required]<?php } ?>" /> <?php echo "M"; ?>
                                <input type="radio" value="2" class="seatFemale<?php echo $journey['TJourney']['id']; ?> chkSeatGender<?php echo $journey['TJourney']['id']; ?><?php if($journey['TJourney']['gender_require'] == 1){ ?> validate[required]<?php } ?>" /> <?php echo "F"; ?>
                            </div>
                        </td>
                        <td style="width: 17%;"> 
                            <div class="inputContainer" style="width: 100%;">
                                <input type="text" name="data[<?php echo $journey['TJourney']['id']; ?>][price][]" value="0" class="seatPrice<?php echo $journey['TJourney']['id']; ?> seatPrice float" style="width: 90%; height: 20px;" readonly="" />
                            </div>
                        </td>
                        <td style="width: 18%;">
                            <div class="inputContainer" style="width: 100%;">
                                <input type="text" name="data[<?php echo $journey['TJourney']['id']; ?>][discount][]" value="0" class="seatDiscount<?php echo $journey['TJourney']['id']; ?> seatDiscount float" style="width: 90%; height: 20px;" <?php if(!$allowDiscount){ ?>readonly=""<?php } ?> />
                            </div>
                        </td>
                        <td style="width: 17%;">
                            <div class="inputContainer" style="width: 100%;">
                                <input type="text" name="data[<?php echo $journey['TJourney']['id']; ?>][total][]" value="0" class="seatTotal<?php echo $journey['TJourney']['id']; ?> seatTotal float" style="width: 90%; height: 20px;" readonly="" />
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </fieldset>
    </div>
    <div style="clear: both;"></div>
</div>