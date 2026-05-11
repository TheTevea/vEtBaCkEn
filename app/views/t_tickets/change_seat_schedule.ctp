<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        <?php
        if($isReturn == 1){
        ?>
        $("#journeyScheduleListReturn").niceScroll({cursorborder:"", cursorcolor:"#0063dc", boxzoom:false});
        <?php
        }
        ?>
        ticketEvent();
    });
</script>
<?php
if($isReturn == 1){
?>
<table cellpadding="5" cellspacing="0" style="width: 100%;">
    <tr>
        <td style="width: 10%;"><?php echo TABLE_RETURN_DATE; ?> :</td>
        <td><?php echo dateShort($date); ?></td>
        <td style="width: 10%;"><?php echo REPORT_FROM; ?> :</td>
        <td><?php echo $destFrom['TDestination']['name']; ?></td>
        <td style="width: 10%;"><?php echo REPORT_TO; ?> :</td>
        <td><?php echo $destTo['TDestination']['name']; ?></td>
    </tr>
</table>
<div style="width: 1000px; margin-top: 10px;">
    <div style="width: 100%; margin: 0px auto; height: 540px; overflow: auto;" id="journeyScheduleListReturn">
<?php
}
    if(!empty($journeys)){
?>
        <table cellpadding="0" cellspacing="0" style="width: 100%; border-bottom: 2px solid #eeeef1;">
            <tr>
                <th style="width: 12%; height: 30px; text-align: center; font-size: 14px; font-weight: bold; color: #000;"><?php echo TABLE_DEPARTURE; ?></th>
                <th style="width: 35%; height: 30px; text-align: left; font-size: 14px; font-weight: bold; color: #000; padding-left: 7px;"><?php echo GENERAL_DESCRIPTION; ?></th>
                <th style="width: 22%; height: 30px; text-align: left; font-size: 14px; font-weight: bold; color: #000;"><?php echo TABLE_TRANSPORTATION_TYPE; ?></th>
                <th style="width: 13%; height: 30px; text-align: center; font-size: 14px; font-weight: bold; color: #000;"><?php echo TABLE_PRICE; ?></th>
                <th></th>
            </tr>
        </table>
<?php
        $travelDate = $date;
        $checkMidNight = false;
        $showSchedule  = true;
        if(strtotime($date) >= strtotime("2021-12-07")){
            $showSchedule  = false;
        }
        // Departure > 01:00
        foreach($journeys AS $journey){
            $depare = explode(":", $journey['TDepartureTime']['name']);
            $depatureTime = (int) $depare[0];
            if($depatureTime != 0 || $showSchedule == true){
                $isActive = false;
                if(!empty($journey['TJourney']['active_start']) && !empty($journey['TJourney']['active_end']) && $journey['TJourney']['active_start'] != '0000-00-00' && $journey['TJourney']['active_end'] != '0000-00-00'){
                    if(strtotime($journey['TJourney']['active_start']) <= strtotime($date) && strtotime($journey['TJourney']['active_end']) >= strtotime($date)){
                        $isActive = true;
                    }
                } else {
                    $isActive = true;
                }
                if($isActive == true){
                    // Check Block Day
                    $block = false;
                    $blockDeparture = false;
                    if($journey['TJourney']['block_start'] != '0000-00-00' && $journey['TJourney']['block_start'] != '' && $journey['TJourney']['block_end'] != '0000-00-00' && $journey['TJourney']['block_end'] != ''){
                        $timeBlockStart = strtotime($journey['TJourney']['block_start']);
                        $timeBlockEnd   = strtotime($journey['TJourney']['block_end']);
                        $departureTime  = strtotime($date);
                        if($departureTime >= $timeBlockStart && $departureTime <= $timeBlockEnd){
                            $block = true;
                            $blockDeparture = true;
                        }
                    }
                    // Check Block By Weekly
                    $nameOfDay = date('D', strtotime($date));
                    $sqlBW = mysql_query("SELECT * FROM t_journey_schedules WHERE t_journey_id = ".$journey['TJourney']['id']." AND `".strtolower($nameOfDay)."` = 1");
                    if(!mysql_num_rows($sqlBW)){
                        $block = true;
                    }
                    if($block == false){
                        $dateNow = strtotime(date("Y-m-d"));
                        $depare = explode(":", $journey['TDepartureTime']['name']);
                        $depatureTime = (int) $depare[0];
                        // Get Date Journey follow branch working hour
                        if(checkDateFrom($journey['TJourney']['branch_id'], $depatureTime) == 0){
                            $departure = strtotime(date("Y-m-d", strtotime("+1 day", strtotime($date))));
                        } else {
                            $departure = strtotime($date);  
                        }
                        if($dateNow == $departure){
                            $delayTime = 0;
                            if(!empty($journey['TJourney']['delay_af_departure'])){
                                $delayTime = $journey['TJourney']['delay_af_departure'] * 60;
                            }
                            $timeJourney = strtotime(date("Y-m-d", $departure)." ".$journey['TDepartureTime']['name']);
                            $timeNow     = strtotime(date("Y-m-d H:i:s")) - $delayTime; // Delay One Hour
                            if($timeNow > $timeJourney){
                                $blockDeparture = true;
                            }
                        }
                    }
                    // Check Price 
                    if($journey['TJourney']['type'] == 2){ // Transit
                    ?>
                    <div style="font-size: 16px; font-weight: bold; text-align: left; margin-bottom: 15px; margin-top: 10px;">
                        <?php 
                        echo $journey['TJourney']['description'].' (Transit) <span style="color: red; font-size: 16px; font-weight: bold;">'.number_format($journey['TJourney']['unit_price'], 2)." ".$journey['CurrencyCenter']['symbol'].'</span>';
                        ?>
                    </div>
                    <?php
                        $titleTransit = "";
                        $j = 0;
                        $sqlTranJourney = mysql_query("SELECT t_journeys.*, t_journey_transits.is_next_day FROM t_journeys INNER JOIN t_journey_transits ON t_journey_transits.t_journey_departure_id = t_journeys.id AND t_journey_transits.t_journey_id = ".$journey['TJourney']['id']." WHERE t_journeys.status = 1 ORDER BY t_journey_transits.id");
                        while($rowTranJourney = mysql_fetch_array($sqlTranJourney)){
                            $price = $rowTranJourney['unit_price'];
                            $date  = $travelDate;
                            if($rowTranJourney['is_next_day'] == 1){
                                $date = date("Y-m-d", strtotime("+1 day", strtotime($travelDate)));
                            }
                            // Destination
                            $destTo   = "";
                            $destFrom = "";
                            $sqlDest  = mysql_query("SELECT * FROM t_destinations WHERE id IN (".$rowTranJourney['t_destination_from_id'].",".$rowTranJourney['t_destination_to_id'].")");
                            while($rowDest = mysql_fetch_array($sqlDest)){
                                if($rowDest['id'] == $rowTranJourney['t_destination_from_id']){
                                    $destFrom = $rowDest['name'];
                                } else {
                                    $destTo   = $rowDest['name'];
                                }
                            }
                            if($rowTranJourney['type'] == 3){
                                $seatBooked = array();
                                $sqlTransit = mysql_query("SELECT t_transportation_type_id, t_route_id, t_journeys.id AS journey_id FROM t_journeys WHERE id IN (SELECT t_journey_departure_id FROM t_journey_transits WHERE t_journey_id = ".$rowTranJourney['id']." GROUP BY t_journey_departure_id)");
                                while($rowTransit = mysql_fetch_array($sqlTransit)){
                                    // Check Transportation Type Change
                                    $trasportationId   = $rowTransit['t_transportation_type_id'];
                                    // Get Seat Booked
                                    $sqlSeat  = mysql_query("SELECT seat_number FROM t_seat_controls WHERE t_transportation_type_id = ".$trasportationId." AND t_route_id = ".$rowTransit['t_route_id']." AND journey_date = '".$date."' AND status IN (1,2,3)");
                                    while($rowSeat  = mysql_fetch_array($sqlSeat)){
                                        if (!array_key_exists($rowSeat['seat_number'], $seatBooked)) {
                                            $seatBooked[$rowSeat['seat_number']] = 1;
                                        }
                                    }
                                }
                                $rowSeat[0] = count($seatBooked);
                                // Transportation Type
                                $sqlT = mysql_query("SELECT name, number_of_seat FROM t_transportation_types WHERE id = ".$trasportationId);
                                $rowT = mysql_fetch_array($sqlT);
                                $trasportationName = $rowT['name'];
                                $totalSeat         = $rowT['number_of_seat'];
                            } else {
                                // Check Transportation Type Change
                                $sqlCT = mysql_query("SELECT t_journey_change_transportations.t_transportation_type_id, t_transportation_types.name, t_transportation_types.number_of_seat FROM t_journey_change_transportations INNER JOIN t_transportation_types ON t_transportation_types.id = t_journey_change_transportations.t_transportation_type_id WHERE t_journey_change_transportations.offline_project_id = ".$user['User']['offline_project_id']." AND t_journey_change_transportations.status = 1 AND t_journey_change_transportations.start >= '".$date."' AND t_journey_change_transportations.end <= '".$date."' AND t_journey_change_transportations.t_journey_id = ".$rowTranJourney['id']." ORDER BY t_journey_change_transportations.id DESC LIMIT 1");
                                if(mysql_num_rows($sqlCT)){
                                    $rowCT = mysql_fetch_array($sqlCT);
                                    $trasportationId   = $rowCT['t_transportation_type_id'];
                                    $trasportationName = $rowCT['name'];
                                    $totalSeat         = $rowCT['number_of_seat'];
                                } else {
                                    $trasportationId   = $rowTranJourney['t_transportation_type_id'];
                                    $sqlT = mysql_query("SELECT name, number_of_seat FROM t_transportation_types WHERE id = ".$trasportationId);
                                    $rowT = mysql_fetch_array($sqlT);
                                    $trasportationName = $rowT['name'];
                                    $totalSeat         = $rowT['number_of_seat'];
                                }
                                // Get Seat Booked
                                $sqlSeat = mysql_query("SELECT COUNT(*) FROM t_seat_controls WHERE t_transportation_type_id = ".$trasportationId." AND t_route_id = ".$rowTranJourney['t_route_id']." AND journey_date = '".$date."' AND status IN (1,2,3)");
                                $rowSeat = mysql_fetch_array($sqlSeat);
                            }
                            // Check Default Price
                            $sqlPD = mysql_query("SELECT * FROM t_journey_price_defaults WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$rowTranJourney['t_destination_from_id']." AND destination_to_id = ".$rowTranJourney['t_destination_to_id']." AND t_transportation_type_id = ".$rowTranJourney['t_transportation_type_id']." AND status = 1 AND main_branch_id = ".$user['User']['main_branch_id']." ORDER BY id DESC LIMIT 1");
                            if(mysql_num_rows($sqlPD)){
                                $rowPD = mysql_fetch_array($sqlPD);
                                $price = $rowPD['price'];
                            } else {
                                $sqlPDA = mysql_query("SELECT * FROM t_journey_price_defaults WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$rowTranJourney['t_destination_from_id']." AND destination_to_id = ".$rowTranJourney['t_destination_to_id']." AND t_transportation_type_id = ".$rowTranJourney['t_transportation_type_id']." AND status = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
                                if(mysql_num_rows($sqlPDA)){
                                    $rowPDA = mysql_fetch_array($sqlPDA);
                                    $price  = $rowPDA['price'];
                                }
                            }
                            // Check Price in Period
                            $sqlPrice = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$rowTranJourney['t_destination_from_id']." AND destination_to_id = ".$rowTranJourney['t_destination_to_id']." AND t_transportation_type_id = ".$rowTranJourney['t_transportation_type_id']." AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND main_branch_id = ".$user['User']['main_branch_id']." ORDER BY id DESC LIMIT 1");
                            if(mysql_num_rows($sqlPrice)){
                                $rowPrice = mysql_fetch_array($sqlPrice);
                                if($rowPrice['price_type'] == 1){
                                    $price = $rowPrice['price'];
                                } else {
                                    $price = $price + $rowPrice['price'];
                                }
                            } else {
                                $sqlPA = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$rowTranJourney['t_destination_from_id']." AND destination_to_id = ".$rowTranJourney['t_destination_to_id']." AND t_transportation_type_id = ".$rowTranJourney['t_transportation_type_id']." AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
                                if(mysql_num_rows($sqlPA)){
                                    $rowPAPrice = mysql_fetch_array($sqlPA);
                                    if($rowPAPrice['price_type'] == 1){
                                        $price = $rowPAPrice['price'];
                                    } else {
                                        $price = $price + $rowPAPrice['price'];
                                    }
                                }
                            }
                            // Check Block Seat
                            $totalBlock = 0;
                            $sqlBlock = mysql_query("SELECT t_journey_seat_blocks.id, t_journey_seat_blocks.type, t_journey_seat_blocks.start, t_journey_seat_blocks.end, t_journey_seat_block_details.seat_number FROM t_journey_seat_blocks INNER JOIN t_journey_seat_block_details ON t_journey_seat_block_details.t_journey_seat_block_id = t_journey_seat_blocks.id WHERE t_journey_seat_blocks.start <= '".$date."' AND t_journey_seat_blocks.end >= '".$date."' AND t_journey_seat_blocks.t_journey_id = ".$rowTranJourney['id']." AND t_journey_seat_blocks.t_departure_time_id = ".$rowTranJourney['t_departure_time_id']." AND t_journey_seat_blocks.is_active = 1");
                            while($rowBlock = mysql_fetch_array($sqlBlock)){
                                if($rowBlock['type'] == 2){
                                    if(strtotime($rowBlock['start']) >= strtotime($date) && strtotime($date) <= strtotime($rowBlock['end'])){
                                        $totalBlock++;
                                    } else {
                                        $totalBlock++;
                                    }
                                } else if($rowBlock['type'] == 3){
                                    $totalBlock++;
                                }
                            }
                            $sqlDeprt = mysql_query("SELECT * FROM t_departure_times WHERE id = ".$rowTranJourney['t_departure_time_id']);
                            $rowDeprt = mysql_fetch_array($sqlDeprt);
                            $sqlCur   = mysql_query("SELECT * FROM currency_centers WHERE id = ".$rowTranJourney['currency_center_id']);
                            $rowCur   = mysql_fetch_array($sqlCur);
                            if($titleTransit != $destFrom){
                                if($j >  0){
                            ?>
                            <div style="font-size: 16px; font-weight: bold; text-align: center;">
                                តជើង / Transit
                            </div>
                            <?php
                                }
                            ?>
                            <div style="font-size: 14px; font-weight: bold; text-align: left;">
                                <?php 
                                echo $destFrom." - ".$destTo;
                                ?>
                            </div>
                            <?php
                                $j++;
                            }
                            ?>
                            <div style="width: 98%; height: 60px; border-bottom: 2px solid #eeeef1; background: #fff; margin-bottom: 5px; padding: 5px;">
                                <table cellpadding="5" cellspacing="0" style="width: 100%;">
                                    <tr class="viewScheduleList">
                                        <td style="width: 12%; font-size: 22px; font-weight: bold; height: 50px; text-align: left;">
                                            <div style="width: 100%; font-size: 22px; height: 20px; padding-top: 5px;">
                                                <i class="fa fa-clock-o"></i> <?php echo date("H:i", strtotime($rowDeprt['name'])); ?>
                                            </div>
                                            <div style="width: 100%; font-size: 14px; font-weight: normal; height: 20px; padding-top: 5px; text-align: center;">
                                                <?php echo dateShort($date); ?>
                                            </div>
                                        </td>
                                        <td style="width: 35%; font-size: 15px; vertical-align: top;">
                                            <?php
                                            $journeyType = ' (Direct)';
                                            echo $rowTranJourney['description'].$journeyType;
                                            ?>
                                        </td>
                                        <td style="width: 22%;">
                                            <div style="width: 100%; font-size: 14px; height: 20px; padding-top: 5px;">
                                                <i class="fa fa-table" style="font-size: 14px; margin-right: 5px;"></i><?php echo TABLE_SEAT; ?>: <b style="font-size: 18px;"><?php echo ($rowSeat[0] + $totalBlock); ?> / <?php echo $totalSeat; ?></b>
                                            </div>
                                            <div style="width: 100%; font-size: 14px; height: 20px; padding-top: 5px;">
                                                <i class="fa fa-bus" style="font-size: 14px; margin-right: 5px;"></i><?php echo $trasportationName; ?>
                                            </div>
                                        </td>
                                        <td style="width: 13%; text-align: center; font-size: 20px; color: red;"><?php echo number_format($price, 2); ?> <?php echo $rowCur['symbol']; ?></td>
                                        <td style="text-align: center;">
                                            <?php
                                            if($block == false && $blockDeparture == false){
                                                $btnLabel = ACTION_BOOKING;
                                                $btnClass = "";
                                                if(($rowSeat[0] + $totalBlock) == $totalSeat){
                                                    $btnLabel = TABLE_FULL;
                                                }
                                                if($editId == 0){
                                                    if($rowTranJourney['type'] == 1 || $rowTranJourney['type'] == 3){
                                                        $btnClass = "btnTicketBooking";
                                                    } else {
                                                        $btnClass = "btnTicketBookingTransit";
                                                    }
                                                } else {
                                                    if($rowTranJourney['type'] == 1 || $rowTranJourney['type'] == 3){
                                                        $btnClass = "btnEditOpen";
                                                    } else {
                                                        $btnClass = "btnTicketBookingTransit";
                                                    }
                                                }
                                                ?>
                                                <button class="button3 <?php echo $btnClass; ?>" is-return="<?php echo $isReturn; ?>" j-id="<?php echo $rowTranJourney['id']; ?>" t-id="<?php echo $rowTranJourney['t_departure_time_id']; ?>" date="<?php echo $date; ?>" act="<?php echo $rowTranJourney['description'].' ('.dateShort($date).' '.date("h:i A", strtotime($rowDeprt['name'])).') - '.$trasportationName; ?>"><i class="fa fa-ticket" style="font-size: 14px; margin-right: 5px;"></i> <span><?php echo $btnLabel; ?></span></button>
                                                <?php
                                            } else {
                                                if($block == true){
                                                    $lblBlock = ACTION_BLOCK;
                                                } else {
                                                    $lblBlock = TABLE_LEAVING;
                                                }
                                            ?>
                                            <button class="button4"><i class="fa fa-ban" style="font-size: 14px; margin-right: 5px;"></i> <span><?php echo $lblBlock; ?></span></button>
                                            <?php
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                    <?php
                            $titleTransit = $destFrom;
                        } // End Loop Journey in Transit
                    ?>
                    <hr style="width: 100%; border: 1px solid #000;" />
                    <?php
                    } else { // Direct
                        $price = $journey['TJourney']['unit_price'];
                        if($journey['TJourney']['type'] == 3){
                            $seatBooked = array();
                            $sqlTransit = mysql_query("SELECT t_transportation_type_id, t_route_id, t_journeys.id AS journey_id FROM t_journeys WHERE id IN (SELECT t_journey_departure_id FROM t_journey_transits WHERE t_journey_id = ".$journey['TJourney']['id']." GROUP BY t_journey_departure_id)");
                            while($rowTransit = mysql_fetch_array($sqlTransit)){
                                // Check Transportation Type Change
                                $trasportationId   = $rowTransit['t_transportation_type_id'];
                                // Get Seat Booked
                                $sqlSeat  = mysql_query("SELECT seat_number FROM t_seat_controls WHERE t_transportation_type_id = ".$trasportationId." AND t_route_id = ".$rowTransit['t_route_id']." AND journey_date = '".$date."' AND status IN (1,2,3)");
                                while($rowSeat  = mysql_fetch_array($sqlSeat)){
                                    if (!array_key_exists($rowSeat['seat_number'], $seatBooked)) {
                                        $seatBooked[$rowSeat['seat_number']] = 1;
                                    }
                                }
                            }
                            $rowSeat[0] = count($seatBooked);
                            // Transportation Type
                            $sqlT = mysql_query("SELECT name, number_of_seat FROM t_transportation_types WHERE id = ".$trasportationId);
                            $rowT = mysql_fetch_array($sqlT);
                            $trasportationName = $rowT['name'];
                            $totalSeat         = $rowT['number_of_seat'];
                        } else {
                            // Check Transportation Type Change
                            $sqlCT = mysql_query("SELECT t_journey_change_transportations.t_transportation_type_id, t_transportation_types.name, t_transportation_types.number_of_seat FROM t_journey_change_transportations INNER JOIN t_transportation_types ON t_transportation_types.id = t_journey_change_transportations.t_transportation_type_id WHERE t_journey_change_transportations.offline_project_id = ".$user['User']['offline_project_id']." AND t_journey_change_transportations.status = 1 AND t_journey_change_transportations.start >= '".$date."' AND t_journey_change_transportations.end <= '".$date."' AND t_journey_change_transportations.t_journey_id = ".$journey['TJourney']['id']." ORDER BY t_journey_change_transportations.id DESC LIMIT 1");
                            if(mysql_num_rows($sqlCT)){
                                $rowCT = mysql_fetch_array($sqlCT);
                                $trasportationId   = $rowCT['t_transportation_type_id'];
                                $trasportationName = $rowCT['name'];
                                $totalSeat         = $rowCT['number_of_seat'];
                            } else {
                                $trasportationId   = $journey['TJourney']['t_transportation_type_id'];
                                $trasportationName = $journey['TTransportationType']['name'];
                                $totalSeat         = $journey['TTransportationType']['number_of_seat'];
                            }
                            // Get Seat Booked
                            $sqlSeat = mysql_query("SELECT COUNT(*) FROM t_seat_controls WHERE t_transportation_type_id = ".$trasportationId." AND t_route_id = ".$journey['TJourney']['t_route_id']." AND journey_date = '".$date."' AND status IN (1,2,3)");
                            $rowSeat = mysql_fetch_array($sqlSeat);
                        }
                        // Check Default Price
                        $sqlPD = mysql_query("SELECT * FROM t_journey_price_defaults WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$journey['TJourney']['t_destination_from_id']." AND destination_to_id = ".$journey['TJourney']['t_destination_to_id']." AND t_transportation_type_id = ".$journey['TJourney']['t_transportation_type_id']." AND status = 1 AND main_branch_id = ".$user['User']['main_branch_id']." ORDER BY id DESC LIMIT 1");
                        if(mysql_num_rows($sqlPD)){
                            $rowPD = mysql_fetch_array($sqlPD);
                            $price = $rowPD['price'];
                        } else {
                            $sqlPDA = mysql_query("SELECT * FROM t_journey_price_defaults WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$journey['TJourney']['t_destination_from_id']." AND destination_to_id = ".$journey['TJourney']['t_destination_to_id']." AND t_transportation_type_id = ".$journey['TJourney']['t_transportation_type_id']." AND status = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
                            if(mysql_num_rows($sqlPDA)){
                                $rowPDA = mysql_fetch_array($sqlPDA);
                                $price  = $rowPDA['price'];
                            }
                        }
                        // Check Price in Period
                        $sqlPrice = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$journey['TJourney']['t_destination_from_id']." AND destination_to_id = ".$journey['TJourney']['t_destination_to_id']." AND t_transportation_type_id = ".$journey['TJourney']['t_transportation_type_id']." AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND main_branch_id = ".$user['User']['main_branch_id']." ORDER BY id DESC LIMIT 1");
                        if(mysql_num_rows($sqlPrice)){
                            $rowPrice = mysql_fetch_array($sqlPrice);
                            if($rowPrice['price_type'] == 1){
                                $price = $rowPrice['price'];
                            } else {
                                $price = $price + $rowPrice['price'];
                            }
                        } else {
                            $sqlPA = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$journey['TJourney']['t_destination_from_id']." AND destination_to_id = ".$journey['TJourney']['t_destination_to_id']." AND t_transportation_type_id = ".$journey['TJourney']['t_transportation_type_id']." AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
                            if(mysql_num_rows($sqlPA)){
                                $rowPAPrice = mysql_fetch_array($sqlPA);
                                if($rowPAPrice['price_type'] == 1){
                                    $price = $rowPAPrice['price'];
                                } else {
                                    $price = $price + $rowPAPrice['price'];
                                }
                            }
                        }
                        // Check Block Seat
                        $totalBlock = 0;
                        $sqlBlock = mysql_query("SELECT t_journey_seat_blocks.id, t_journey_seat_blocks.type, t_journey_seat_blocks.start, t_journey_seat_blocks.end, t_journey_seat_block_details.seat_number FROM t_journey_seat_blocks INNER JOIN t_journey_seat_block_details ON t_journey_seat_block_details.t_journey_seat_block_id = t_journey_seat_blocks.id WHERE t_journey_seat_blocks.start <= '".$date."' AND t_journey_seat_blocks.end >= '".$date."' AND t_journey_seat_blocks.t_journey_id = ".$journey['TJourney']['id']." AND t_journey_seat_blocks.t_departure_time_id = ".$journey['TJourney']['t_departure_time_id']." AND t_journey_seat_blocks.is_active = 1");
                        while($rowBlock = mysql_fetch_array($sqlBlock)){
                            if($rowBlock['type'] == 2){
                                if(strtotime($rowBlock['start']) >= strtotime($date) && strtotime($date) <= strtotime($rowBlock['end'])){
                                    $totalBlock++;
                                } else {
                                    $totalBlock++;
                                }
                            } else if($rowBlock['type'] == 3){
                                $totalBlock++;
                            }
                        }
            ?>
                    <div style="width: 98%; height: 60px; border-bottom: 2px solid #eeeef1; background: #fff; margin-bottom: 5px; padding: 5px;">
                        <table cellpadding="5" cellspacing="0" style="width: 100%;">
                            <tr class="viewScheduleList">
                                <td style="width: 12%; font-size: 22px; font-weight: bold; height: 50px; text-align: left;">
                                    <div style="width: 100%; font-size: 22px; height: 20px; padding-top: 5px;">
                                        <i class="fa fa-clock-o"></i> <?php echo date("H:i", strtotime($journey['TDepartureTime']['name'])); ?>
                                    </div>
                                    <div style="width: 100%; font-size: 14px; font-weight: normal; height: 20px; padding-top: 5px; text-align: center;">
                                        <?php echo dateShort($date); ?>
                                    </div>
                                </td>
                                <td style="width: 35%; font-size: 15px; vertical-align: top;">
                                    <?php
                                    $journeyType = ' (Direct)';
                                    if($journey['TJourney']['type'] == 2){
                                        $journeyType = ' (Transit)';
                                    }
                                    echo $journey['TJourney']['description'].$journeyType;
                                    ?>
                                </td>
                                <td style="width: 22%;">
                                    <div style="width: 100%; font-size: 14px; height: 20px; padding-top: 5px;">
                                        <i class="fa fa-table" style="font-size: 14px; margin-right: 5px;"></i><?php echo TABLE_SEAT; ?>: <b style="font-size: 18px;"><?php echo ($rowSeat[0] + $totalBlock); ?> / <?php echo $totalSeat; ?></b>
                                    </div>
                                    <div style="width: 100%; font-size: 14px; height: 20px; padding-top: 5px;">
                                        <i class="fa fa-bus" style="font-size: 14px; margin-right: 5px;"></i><?php echo $trasportationName; ?>
                                    </div>
                                </td>
                                <td style="width: 13%; text-align: center; font-size: 20px; color: red;"><?php echo number_format($price, 2); ?> <?php echo $journey['CurrencyCenter']['symbol']; ?></td>
                                <td style="text-align: center;">
                                    <?php
                                    if($block == false && $blockDeparture == false){
                                        $btnLabel = ACTION_BOOKING;
                                        $btnClass = "";
                                        if(($rowSeat[0] + $totalBlock) == $totalSeat){
                                            $btnLabel = TABLE_FULL;
                                        }
                                        if($editId == 0){
                                            if($journey['TJourney']['type'] == 1 || $journey['TJourney']['type'] == 3){
                                                $btnClass = "btnTicketBooking";
                                            } else {
                                                $btnClass = "btnTicketBookingTransit";
                                            }
                                        } else {
                                            if($journey['TJourney']['type'] == 1 || $journey['TJourney']['type'] == 3){
                                                $btnClass = "btnEditOpen";
                                            } else {
                                                $btnClass = "btnTicketBookingTransit";
                                            }
                                        }
                                        ?>
                                        <button class="button3 <?php echo $btnClass; ?>" is-return="<?php echo $isReturn; ?>" j-id="<?php echo $journey['TJourney']['id']; ?>" t-id="<?php echo $journey['TJourney']['t_departure_time_id']; ?>" date="<?php echo $date; ?>" act="<?php echo $journey['TJourney']['description'].' ('.dateShort($date).' '.date("h:i A", strtotime($journey['TDepartureTime']['name'])).') - '.$journey['TTransportationType']['name']; ?>"><i class="fa fa-ticket" style="font-size: 14px; margin-right: 5px;"></i> <span><?php echo $btnLabel; ?></span></button>
                                        <?php
                                    } else {
                                        if($block == true){
                                            $lblBlock = ACTION_BLOCK;
                                        } else {
                                            $lblBlock = TABLE_LEAVING;
                                        }
                                    ?>
                                    <button class="button4"><i class="fa fa-ban" style="font-size: 14px; margin-right: 5px;"></i> <span><?php echo $lblBlock; ?></span></button>
                                    <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
            <?php
                    }
                }
            } else {
                $checkMidNight = true;
            }
        }
        // Departure > 00:00
        if($checkMidNight == true && strtotime($date) >= strtotime("2021-12-07")){
        ?>
        <div style="width: 100%; font-size: 18px; font-weight: bold; margin-top: 30px;">ចេញដំណើររំលងអធ្រាត្រ យប់ថ្ងៃទី <?php echo dateShort($date); ?> / Traveling over midnight of <?php echo dateShort($date); ?></div>
        <?php
            $travalDate = $date;
            foreach($journeys AS $journey){
                $depare = explode(":", $journey['TDepartureTime']['name']);
                $depatureTime = (int) $depare[0];
                if($depatureTime == 0){
                    $date = date("Y-m-d", strtotime("+1 day", strtotime($travalDate)));
                    $isActive = false;
                    if(!empty($journey['TJourney']['active_start']) && !empty($journey['TJourney']['active_end']) && $journey['TJourney']['active_start'] != '0000-00-00' && $journey['TJourney']['active_end'] != '0000-00-00'){
                        if(strtotime($journey['TJourney']['active_start']) <= strtotime($date) && strtotime($journey['TJourney']['active_end']) >= strtotime($date)){
                            $isActive = true;
                        }
                    } else {
                        $isActive = true;
                    }
                    if($isActive == true){
                        // Check Block Day
                        $block = false;
                        $blockDeparture = false;
                        if($journey['TJourney']['block_start'] != '0000-00-00' && $journey['TJourney']['block_start'] != '' && $journey['TJourney']['block_end'] != '0000-00-00' && $journey['TJourney']['block_end'] != ''){
                            $timeBlockStart = strtotime($journey['TJourney']['block_start']);
                            $timeBlockEnd   = strtotime($journey['TJourney']['block_end']);
                            $departureTime  = strtotime($date);
                            if($departureTime >= $timeBlockStart && $departureTime <= $timeBlockEnd){
                                $block = true;
                                $blockDeparture = true;
                            }
                        }
                        // Check Block By Weekly
                        $nameOfDay = date('D', strtotime($date));
                        $sqlBW = mysql_query("SELECT * FROM t_journey_schedules WHERE t_journey_id = ".$journey['TJourney']['id']." AND `".strtolower($nameOfDay)."` = 1");
                        if(!mysql_num_rows($sqlBW)){
                            $block = true;
                        }
                        if($block == false){
                            $dateNow = strtotime(date("Y-m-d"));
                            $depare = explode(":", $journey['TDepartureTime']['name']);
                            $depatureTime = (int) $depare[0];
                            // Get Date Journey follow branch working hour
                            if(checkDateFrom($journey['TJourney']['branch_id'], $depatureTime) == 0){
                                $departure = strtotime(date("Y-m-d", strtotime("+1 day", strtotime($date))));
                            } else {
                                $departure = strtotime($date);  
                            }
                            if($dateNow == $departure){
                                $delayTime = 0;
                                if(!empty($journey['TJourney']['delay_af_departure'])){
                                    $delayTime = $journey['TJourney']['delay_af_departure'] * 60;
                                }
                                $timeJourney = strtotime(date("Y-m-d", $departure)." ".$journey['TDepartureTime']['name']);
                                $timeNow     = strtotime(date("Y-m-d H:i:s")) - $delayTime; // Deplay One Hour
                                if($timeNow > $timeJourney){
                                    $blockDeparture = true;
                                }
                            }
                        }
                        // Check Price 
                        if($journey['TJourney']['type'] == 2){ // Transit
                        ?>
                        <div style="font-size: 16px; font-weight: bold; text-align: left; margin-bottom: 15px; margin-top: 10px;">
                            <?php 
                            echo $journey['TJourney']['description'].' (Transit) <span style="color: red; font-size: 16px; font-weight: bold;">'.number_format($journey['TJourney']['unit_price'], 2)." ".$journey['CurrencyCenter']['symbol'].'</span>';
                            ?>
                        </div>
                        <?php
                            $titleTransit = "";
                            $j = 0;
                            $sqlTranJourney = mysql_query("SELECT t_journeys.*, t_journey_transits.is_next_day FROM t_journeys INNER JOIN t_journey_transits ON t_journey_transits.t_journey_departure_id = t_journeys.id AND t_journey_transits.t_journey_id = ".$journey['TJourney']['id']." WHERE t_journeys.status = 1 ORDER BY t_journey_transits.id");
                            while($rowTranJourney = mysql_fetch_array($sqlTranJourney)){
                                $price = $rowTranJourney['unit_price'];
                                $date  = $travelDate;
                                if($rowTranJourney['is_next_day'] == 1){
                                    $date = date("Y-m-d", strtotime("+1 day", strtotime($travelDate)));
                                }
                                // Destination
                                $destTo   = "";
                                $destFrom = "";
                                $sqlDest  = mysql_query("SELECT * FROM t_destinations WHERE id IN (".$rowTranJourney['t_destination_from_id'].",".$rowTranJourney['t_destination_to_id'].")");
                                while($rowDest = mysql_fetch_array($sqlDest)){
                                    if($rowDest['id'] == $rowTranJourney['t_destination_from_id']){
                                        $destFrom = $rowDest['name'];
                                    } else {
                                        $destTo   = $rowDest['name'];
                                    }
                                }
                                if($rowTranJourney['type'] == 3){
                                    $seatBooked = array();
                                    $sqlTransit = mysql_query("SELECT t_transportation_type_id, t_route_id, t_journeys.id AS journey_id FROM t_journeys WHERE id IN (SELECT t_journey_departure_id FROM t_journey_transits WHERE t_journey_id = ".$rowTranJourney['id']." GROUP BY t_journey_departure_id)");
                                    while($rowTransit = mysql_fetch_array($sqlTransit)){
                                        // Check Transportation Type Change
                                        $trasportationId   = $rowTransit['t_transportation_type_id'];
                                        // Get Seat Booked
                                        $sqlSeat  = mysql_query("SELECT seat_number FROM t_seat_controls WHERE t_transportation_type_id = ".$trasportationId." AND t_route_id = ".$rowTransit['t_route_id']." AND journey_date = '".$date."' AND status IN (1,2,3)");
                                        while($rowSeat  = mysql_fetch_array($sqlSeat)){
                                            if (!array_key_exists($rowSeat['seat_number'], $seatBooked)) {
                                                $seatBooked[$rowSeat['seat_number']] = 1;
                                            }
                                        }
                                    }
                                    $rowSeat[0] = count($seatBooked);
                                    // Transportation Type
                                    $sqlT = mysql_query("SELECT name, number_of_seat FROM t_transportation_types WHERE id = ".$trasportationId);
                                    $rowT = mysql_fetch_array($sqlT);
                                    $trasportationName = $rowT['name'];
                                    $totalSeat         = $rowT['number_of_seat'];
                                } else {
                                    // Check Transportation Type Change
                                    $sqlCT = mysql_query("SELECT t_journey_change_transportations.t_transportation_type_id, t_transportation_types.name, t_transportation_types.number_of_seat FROM t_journey_change_transportations INNER JOIN t_transportation_types ON t_transportation_types.id = t_journey_change_transportations.t_transportation_type_id WHERE t_journey_change_transportations.offline_project_id = ".$user['User']['offline_project_id']." AND t_journey_change_transportations.status = 1 AND t_journey_change_transportations.start >= '".$date."' AND t_journey_change_transportations.end <= '".$date."' AND t_journey_change_transportations.t_journey_id = ".$rowTranJourney['id']." ORDER BY t_journey_change_transportations.id DESC LIMIT 1");
                                    if(mysql_num_rows($sqlCT)){
                                        $rowCT = mysql_fetch_array($sqlCT);
                                        $trasportationId   = $rowCT['t_transportation_type_id'];
                                        $trasportationName = $rowCT['name'];
                                        $totalSeat         = $rowCT['number_of_seat'];
                                    } else {
                                        $trasportationId   = $rowTranJourney['t_transportation_type_id'];
                                        $sqlT = mysql_query("SELECT name, number_of_seat FROM t_transportation_types WHERE id = ".$trasportationId);
                                        $rowT = mysql_fetch_array($sqlT);
                                        $trasportationName = $rowT['name'];
                                        $totalSeat         = $rowT['number_of_seat'];
                                    }
                                    // Get Seat Booked
                                    $sqlSeat = mysql_query("SELECT COUNT(*) FROM t_seat_controls WHERE t_transportation_type_id = ".$trasportationId." AND t_route_id = ".$rowTranJourney['t_route_id']." AND journey_date = '".$date."' AND status IN (1,2,3)");
                                    $rowSeat = mysql_fetch_array($sqlSeat);
                                }
                                // Check Default Price
                                $sqlPD = mysql_query("SELECT * FROM t_journey_price_defaults WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$rowTranJourney['t_destination_from_id']." AND destination_to_id = ".$rowTranJourney['t_destination_to_id']." AND t_transportation_type_id = ".$rowTranJourney['t_transportation_type_id']." AND status = 1 AND main_branch_id = ".$user['User']['main_branch_id']." ORDER BY id DESC LIMIT 1");
                                if(mysql_num_rows($sqlPD)){
                                    $rowPD = mysql_fetch_array($sqlPD);
                                    $price = $rowPD['price'];
                                } else {
                                    $sqlPDA = mysql_query("SELECT * FROM t_journey_price_defaults WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$rowTranJourney['t_destination_from_id']." AND destination_to_id = ".$rowTranJourney['t_destination_to_id']." AND t_transportation_type_id = ".$rowTranJourney['t_transportation_type_id']." AND status = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
                                    if(mysql_num_rows($sqlPDA)){
                                        $rowPDA = mysql_fetch_array($sqlPDA);
                                        $price  = $rowPDA['price'];
                                    }
                                }
                                // Check Price in Period
                                $sqlPrice = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$rowTranJourney['t_destination_from_id']." AND destination_to_id = ".$rowTranJourney['t_destination_to_id']." AND t_transportation_type_id = ".$rowTranJourney['t_transportation_type_id']." AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND main_branch_id = ".$user['User']['main_branch_id']." ORDER BY id DESC LIMIT 1");
                                if(mysql_num_rows($sqlPrice)){
                                    $rowPrice = mysql_fetch_array($sqlPrice);
                                    if($rowPrice['price_type'] == 1){
                                        $price = $rowPrice['price'];
                                    } else {
                                        $price = $price + $rowPrice['price'];
                                    }
                                } else {
                                    $sqlPA = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$rowTranJourney['t_destination_from_id']." AND destination_to_id = ".$rowTranJourney['t_destination_to_id']." AND t_transportation_type_id = ".$rowTranJourney['t_transportation_type_id']." AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
                                    if(mysql_num_rows($sqlPA)){
                                        $rowPAPrice = mysql_fetch_array($sqlPA);
                                        if($rowPAPrice['price_type'] == 1){
                                            $price = $rowPAPrice['price'];
                                        } else {
                                            $price = $price + $rowPAPrice['price'];
                                        }
                                    }
                                }
                                // Check Block Seat
                                $totalBlock = 0;
                                $sqlBlock = mysql_query("SELECT t_journey_seat_blocks.id, t_journey_seat_blocks.type, t_journey_seat_blocks.start, t_journey_seat_blocks.end, t_journey_seat_block_details.seat_number FROM t_journey_seat_blocks INNER JOIN t_journey_seat_block_details ON t_journey_seat_block_details.t_journey_seat_block_id = t_journey_seat_blocks.id WHERE t_journey_seat_blocks.start <= '".$date."' AND t_journey_seat_blocks.end >= '".$date."' AND t_journey_seat_blocks.t_journey_id = ".$rowTranJourney['id']." AND t_journey_seat_blocks.t_departure_time_id = ".$rowTranJourney['t_departure_time_id']." AND t_journey_seat_blocks.is_active = 1");
                                while($rowBlock = mysql_fetch_array($sqlBlock)){
                                    if($rowBlock['type'] == 2){
                                        if(strtotime($rowBlock['start']) >= strtotime($date) && strtotime($date) <= strtotime($rowBlock['end'])){
                                            $totalBlock++;
                                        } else {
                                            $totalBlock++;
                                        }
                                    } else if($rowBlock['type'] == 3){
                                        $totalBlock++;
                                    }
                                }
                                $sqlDeprt = mysql_query("SELECT * FROM t_departure_times WHERE id = ".$rowTranJourney['t_departure_time_id']);
                                $rowDeprt = mysql_fetch_array($sqlDeprt);
                                $sqlCur   = mysql_query("SELECT * FROM currency_centers WHERE id = ".$rowTranJourney['currency_center_id']);
                                $rowCur   = mysql_fetch_array($sqlCur);
                                if($titleTransit != $destFrom){
                                    if($j >  0){
                                ?>
                                <div style="font-size: 16px; font-weight: bold; text-align: center;">
                                    តជើង / Transit
                                </div>
                                <?php
                                    }
                                ?>
                                <div style="font-size: 14px; font-weight: bold; text-align: left;">
                                    <?php 
                                    echo $destFrom." - ".$destTo;
                                    ?>
                                </div>
                                <?php
                                    $j++;
                                }
                                ?>
                                <div style="width: 98%; height: 60px; border-bottom: 2px solid #eeeef1; background: #fff; margin-bottom: 5px; padding: 5px;">
                                    <table cellpadding="5" cellspacing="0" style="width: 100%;">
                                        <tr class="viewScheduleList">
                                            <td style="width: 12%; font-size: 22px; font-weight: bold; height: 50px; text-align: left;">
                                                <div style="width: 100%; font-size: 22px; height: 20px; padding-top: 5px;">
                                                    <i class="fa fa-clock-o"></i> <?php echo date("H:i", strtotime($rowDeprt['name'])); ?>
                                                </div>
                                                <div style="width: 100%; font-size: 14px; font-weight: normal; height: 20px; padding-top: 5px; text-align: center;">
                                                    <?php echo dateShort($date); ?>
                                                </div>
                                            </td>
                                            <td style="width: 35%; font-size: 15px; vertical-align: top;">
                                                <?php
                                                $journeyType = ' (Direct)';
                                                echo $rowTranJourney['description'].$journeyType;
                                                ?>
                                            </td>
                                            <td style="width: 22%;">
                                                <div style="width: 100%; font-size: 14px; height: 20px; padding-top: 5px;">
                                                    <i class="fa fa-table" style="font-size: 14px; margin-right: 5px;"></i><?php echo TABLE_SEAT; ?>: <b style="font-size: 18px;"><?php echo ($rowSeat[0] + $totalBlock); ?> / <?php echo $totalSeat; ?></b>
                                                </div>
                                                <div style="width: 100%; font-size: 14px; height: 20px; padding-top: 5px;">
                                                    <i class="fa fa-bus" style="font-size: 14px; margin-right: 5px;"></i><?php echo $trasportationName; ?>
                                                </div>
                                            </td>
                                            <td style="width: 13%; text-align: center; font-size: 20px; color: red;"><?php echo number_format($price, 2); ?> <?php echo $rowCur['symbol']; ?></td>
                                            <td style="text-align: center;">
                                                <?php
                                                if($block == false && $blockDeparture == false){
                                                    $btnLabel = ACTION_BOOKING;
                                                    $btnClass = "";
                                                    if(($rowSeat[0] + $totalBlock) == $totalSeat){
                                                        $btnLabel = TABLE_FULL;
                                                    }
                                                    if($editId == 0){
                                                        if($rowTranJourney['type'] == 1 || $rowTranJourney['type'] == 3){
                                                            $btnClass = "btnTicketBooking";
                                                        } else {
                                                            $btnClass = "btnTicketBookingTransit";
                                                        }
                                                    } else {
                                                        if($rowTranJourney['type'] == 1 || $rowTranJourney['type'] == 3){
                                                            $btnClass = "btnEditOpen";
                                                        } else {
                                                            $btnClass = "btnTicketBookingTransit";
                                                        }
                                                    }
                                                    ?>
                                                    <button class="button3 <?php echo $btnClass; ?>" is-return="<?php echo $isReturn; ?>" j-id="<?php echo $rowTranJourney['id']; ?>" t-id="<?php echo $rowTranJourney['t_departure_time_id']; ?>" date="<?php echo $date; ?>" act="<?php echo $rowTranJourney['description'].' ('.dateShort($date).' '.date("h:i A", strtotime($rowDeprt['name'])).') - '.$trasportationName; ?>"><i class="fa fa-ticket" style="font-size: 14px; margin-right: 5px;"></i> <span><?php echo $btnLabel; ?></span></button>
                                                    <?php
                                                } else {
                                                    if($block == true){
                                                        $lblBlock = ACTION_BLOCK;
                                                    } else {
                                                        $lblBlock = TABLE_LEAVING;
                                                    }
                                                ?>
                                                <button class="button4"><i class="fa fa-ban" style="font-size: 14px; margin-right: 5px;"></i> <span><?php echo $lblBlock; ?></span></button>
                                                <?php
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                        <?php
                                $titleTransit = $destFrom;
                            } // End Loop Journey in Transit
                        ?>
                        <hr style="width: 100%; border: 1px solid #000;" />
                        <?php
                        } else { // Direct
                            $price = $journey['TJourney']['unit_price'];
                            if($journey['TJourney']['type'] == 3){
                                $seatBooked = array();
                                $sqlTransit = mysql_query("SELECT t_transportation_type_id, t_route_id, t_journeys.id AS journey_id FROM t_journeys WHERE id IN (SELECT t_journey_departure_id FROM t_journey_transits WHERE t_journey_id = ".$journey['TJourney']['id']." GROUP BY t_journey_departure_id)");
                                while($rowTransit = mysql_fetch_array($sqlTransit)){
                                    // Check Transportation Type Change
                                    $trasportationId   = $rowTransit['t_transportation_type_id'];
                                    // Get Seat Booked
                                    $sqlSeat  = mysql_query("SELECT seat_number FROM t_seat_controls WHERE t_transportation_type_id = ".$trasportationId." AND t_route_id = ".$rowTransit['t_route_id']." AND journey_date = '".$date."' AND status IN (1,2,3)");
                                    while($rowSeat  = mysql_fetch_array($sqlSeat)){
                                        if (!array_key_exists($rowSeat['seat_number'], $seatBooked)) {
                                            $seatBooked[$rowSeat['seat_number']] = 1;
                                        }
                                    }
                                }
                                $rowSeat[0] = count($seatBooked);
                                // Transportation Type
                                $sqlT = mysql_query("SELECT name, number_of_seat FROM t_transportation_types WHERE id = ".$trasportationId);
                                $rowT = mysql_fetch_array($sqlT);
                                $trasportationName = $rowT['name'];
                                $totalSeat         = $rowT['number_of_seat'];
                            } else {
                                // Check Transportation Type Change
                                $sqlCT = mysql_query("SELECT t_journey_change_transportations.t_transportation_type_id, t_transportation_types.name, t_transportation_types.number_of_seat FROM t_journey_change_transportations INNER JOIN t_transportation_types ON t_transportation_types.id = t_journey_change_transportations.t_transportation_type_id WHERE t_journey_change_transportations.offline_project_id = ".$user['User']['offline_project_id']." AND t_journey_change_transportations.status = 1 AND t_journey_change_transportations.start >= '".$date."' AND t_journey_change_transportations.end <= '".$date."' AND t_journey_change_transportations.t_journey_id = ".$journey['TJourney']['id']." ORDER BY t_journey_change_transportations.id DESC LIMIT 1");
                                if(mysql_num_rows($sqlCT)){
                                    $rowCT = mysql_fetch_array($sqlCT);
                                    $trasportationId   = $rowCT['t_transportation_type_id'];
                                    $trasportationName = $rowCT['name'];
                                    $totalSeat         = $rowCT['number_of_seat'];
                                } else {
                                    $trasportationId   = $journey['TJourney']['t_transportation_type_id'];
                                    $trasportationName = $journey['TTransportationType']['name'];
                                    $totalSeat         = $journey['TTransportationType']['number_of_seat'];
                                }
                                // Get Seat Booked
                                $sqlSeat = mysql_query("SELECT COUNT(*) FROM t_seat_controls WHERE t_transportation_type_id = ".$trasportationId." AND t_route_id = ".$journey['TJourney']['t_route_id']." AND journey_date = '".$date."' AND status IN (1,2,3)");
                                $rowSeat = mysql_fetch_array($sqlSeat);
                            }
                            // Check Default Price
                            $sqlPD = mysql_query("SELECT * FROM t_journey_price_defaults WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$journey['TJourney']['t_destination_from_id']." AND destination_to_id = ".$journey['TJourney']['t_destination_to_id']." AND t_transportation_type_id = ".$journey['TJourney']['t_transportation_type_id']." AND status = 1 AND main_branch_id = ".$user['User']['main_branch_id']." ORDER BY id DESC LIMIT 1");
                            if(mysql_num_rows($sqlPD)){
                                $rowPD = mysql_fetch_array($sqlPD);
                                $price = $rowPD['price'];
                            } else {
                                $sqlPDA = mysql_query("SELECT * FROM t_journey_price_defaults WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$journey['TJourney']['t_destination_from_id']." AND destination_to_id = ".$journey['TJourney']['t_destination_to_id']." AND t_transportation_type_id = ".$journey['TJourney']['t_transportation_type_id']." AND status = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
                                if(mysql_num_rows($sqlPDA)){
                                    $rowPDA = mysql_fetch_array($sqlPDA);
                                    $price  = $rowPDA['price'];
                                }
                            }
                            // Check Price in Period
                            $sqlPrice = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$journey['TJourney']['t_destination_from_id']." AND destination_to_id = ".$journey['TJourney']['t_destination_to_id']." AND t_transportation_type_id = ".$journey['TJourney']['t_transportation_type_id']." AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND main_branch_id = ".$user['User']['main_branch_id']." ORDER BY id DESC LIMIT 1");
                            if(mysql_num_rows($sqlPrice)){
                                $rowPrice = mysql_fetch_array($sqlPrice);
                                if($rowPrice['price_type'] == 1){
                                    $price = $rowPrice['price'];
                                } else {
                                    $price = $price + $rowPrice['price'];
                                }
                            } else {
                                $sqlPA = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = ".$user['User']['offline_project_id']." AND destination_from_id = ".$journey['TJourney']['t_destination_from_id']." AND destination_to_id = ".$journey['TJourney']['t_destination_to_id']." AND t_transportation_type_id = ".$journey['TJourney']['t_transportation_type_id']." AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
                                if(mysql_num_rows($sqlPA)){
                                    $rowPAPrice = mysql_fetch_array($sqlPA);
                                    if($rowPAPrice['price_type'] == 1){
                                        $price = $rowPAPrice['price'];
                                    } else {
                                        $price = $price + $rowPAPrice['price'];
                                    }
                                }
                            }
                            // Check Block Seat
                            $totalBlock = 0;
                            $sqlBlock = mysql_query("SELECT t_journey_seat_blocks.id, t_journey_seat_blocks.type, t_journey_seat_blocks.start, t_journey_seat_blocks.end, t_journey_seat_block_details.seat_number FROM t_journey_seat_blocks INNER JOIN t_journey_seat_block_details ON t_journey_seat_block_details.t_journey_seat_block_id = t_journey_seat_blocks.id WHERE t_journey_seat_blocks.start <= '".$date."' AND t_journey_seat_blocks.end >= '".$date."' AND t_journey_seat_blocks.t_journey_id = ".$journey['TJourney']['id']." AND t_journey_seat_blocks.t_departure_time_id = ".$journey['TJourney']['t_departure_time_id']." AND t_journey_seat_blocks.is_active = 1");
                            while($rowBlock = mysql_fetch_array($sqlBlock)){
                                if($rowBlock['type'] == 2){
                                    if(strtotime($rowBlock['start']) >= strtotime($date) && strtotime($date) <= strtotime($rowBlock['end'])){
                                        $totalBlock++;
                                    } else {
                                        $totalBlock++;
                                    }
                                } else if($rowBlock['type'] == 3){
                                    $totalBlock++;
                                }
                            }
                ?>
                        <div style="width: 98%; height: 60px; border-bottom: 2px solid #eeeef1; background: #fff; margin-bottom: 5px; padding: 5px;">
                            <table cellpadding="5" cellspacing="0" style="width: 100%;">
                                <tr class="viewScheduleList">
                                    <td style="width: 12%; font-size: 22px; font-weight: bold; height: 50px; text-align: left;">
                                        <div style="width: 100%; font-size: 22px; height: 20px; padding-top: 5px;">
                                            <i class="fa fa-clock-o"></i> <?php echo date("H:i", strtotime($journey['TDepartureTime']['name'])); ?>
                                        </div>
                                        <div style="width: 100%; font-size: 14px; font-weight: normal; height: 20px; padding-top: 5px; text-align: center;">
                                            <?php echo dateShort($date); ?>
                                        </div>
                                    </td>
                                    <td style="width: 35%; font-size: 15px; vertical-align: top;">
                                        <?php
                                        $journeyType = ' (Direct)';
                                        if($journey['TJourney']['type'] == 2){
                                            $journeyType = ' (Transit)';
                                        }
                                        echo $journey['TJourney']['description'].$journeyType;
                                        ?>
                                    </td>
                                    <td style="width: 22%;">
                                        <div style="width: 100%; font-size: 14px; height: 20px; padding-top: 5px;">
                                            <i class="fa fa-table" style="font-size: 14px; margin-right: 5px;"></i><?php echo TABLE_SEAT; ?>: <b style="font-size: 18px;"><?php echo ($rowSeat[0] + $totalBlock); ?> / <?php echo $totalSeat; ?></b>
                                        </div>
                                        <div style="width: 100%; font-size: 14px; height: 20px; padding-top: 5px;">
                                            <i class="fa fa-bus" style="font-size: 14px; margin-right: 5px;"></i><?php echo $trasportationName; ?>
                                        </div>
                                    </td>
                                    <td style="width: 13%; text-align: center; font-size: 20px; color: red;"><?php echo number_format($price, 2); ?> <?php echo $journey['CurrencyCenter']['symbol']; ?></td>
                                    <td style="text-align: center;">
                                        <?php
                                        if($block == false && $blockDeparture == false){
                                            $btnLabel = ACTION_BOOKING;
                                            $btnClass = "";
                                            if(($rowSeat[0] + $totalBlock) == $totalSeat){
                                                $btnLabel = TABLE_FULL;
                                            }
                                            if($editId == 0){
                                                if($journey['TJourney']['type'] == 1 || $journey['TJourney']['type'] == 3){
                                                    $btnClass = "btnTicketBooking";
                                                } else {
                                                    $btnClass = "btnTicketBookingTransit";
                                                }
                                            } else {
                                                if($journey['TJourney']['type'] == 1 || $journey['TJourney']['type'] == 3){
                                                    $btnClass = "btnEditOpen";
                                                } else {
                                                    $btnClass = "btnTicketBookingTransit";
                                                }
                                            }
                                            ?>
                                            <button class="button3 <?php echo $btnClass; ?>" is-return="<?php echo $isReturn; ?>" j-id="<?php echo $journey['TJourney']['id']; ?>" t-id="<?php echo $journey['TJourney']['t_departure_time_id']; ?>" date="<?php echo $date; ?>" act="<?php echo $journey['TJourney']['description'].' ('.dateShort($date).' '.date("h:i A", strtotime($journey['TDepartureTime']['name'])).') - '.$journey['TTransportationType']['name']; ?>"><i class="fa fa-ticket" style="font-size: 14px; margin-right: 5px;"></i> <span><?php echo $btnLabel; ?></span></button>
                                            <?php
                                        } else {
                                            if($block == true){
                                                $lblBlock = ACTION_BLOCK;
                                            } else {
                                                $lblBlock = TABLE_LEAVING;
                                            }
                                        ?>
                                        <button class="button4"><i class="fa fa-ban" style="font-size: 14px; margin-right: 5px;"></i> <span><?php echo $lblBlock; ?></span></button>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                <?php
                        }
                    }
                }
            }
        }    
    } else {
    ?>
        <div style="width: 98%; height: 40px; border: 2px solid #eee; background: #eee; margin-bottom: 5px; padding-top: 20px; text-align: center; font-size: 20px;">
            <?php echo TABLE_NO_SCHEDULE; ?>
        </div>
    <?php
        
    }
if($isReturn == 1){
?>
    </div>
</div>
<?php
}
?>