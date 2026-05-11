<?php
include('includes/function.php');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackTJourney").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTJourney.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTJourney">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset style="width: 48%; float: left; height: 800px;">
    <legend><?php __(MENU_JOURNEY_INFO); ?></legend>
        <table style="width: 100%;" cellpadding="5">
            <tr>
                <th style="width:30%;"><?php __(MENU_COMPANY_MANAGEMENT); ?></th>
                <td style="width:1%;">:</td>
                <td>
                    <?php echo $this->data['Company']['name']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(MENU_BRANCH); ?></th>
                <td>:</td>
                <td>
                    <?php echo $this->data['Branch']['name']; ?>
                </td>
            </tr>
<!--            <tr>
                <th style="width:20%;"><?php __(MENU_JOURNEY_TYPE); ?></th>
                <td style="width:1%;">:</td>
                <td>
                    <?php echo $this->data['TJourneyType']['name']; ?>
                </td>
            </tr>-->
            <tr>
                <th><?php __(GENERAL_DESCRIPTION); ?></th>
                <td>:</td>
                <td>
                    <?php echo $this->data['TJourney']['description']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(GENERAL_DESCRIPTION); ?> (Khmer)</th>
                <td>:</td>
                <td>
                    <?php echo $this->data['TJourney']['description_kh']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_VEHICLE_NO); ?></th>
                <td>:</td>
                <td>
                    <?php echo $this->data['TJourney']['vehicle_no']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __("Nation Road"); ?></th>
                <td>:</td>
                <td>
                    <?php echo $this->data['TJourney']['nation_road']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_DESTINATION_FROM); ?></th>
                <td>:</td>
                <td>
                    <?php echo $this->data['TDestination']['name']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_DESTINATION_TO); ?></th>
                <td>:</td>
                <td>
                    <?php 
                    $sqlTo = mysql_query("SELECT name FROM t_destinations WHERE id = ".$this->data['TJourney']['t_destination_to_id']);
                    if(mysql_num_rows($sqlTo)){
                        $rowTo = mysql_fetch_array($sqlTo);
                        echo $rowTo[0]; 
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php __(MENU_TRANSPORTATION_TYPE); ?></th>
                <td>:</td>
                <td>
                    <?php echo $this->data['TTransportationType']['name']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(MENU_ROUTE); ?></th>
                <td>:</td>
                <td>
                    <?php echo $this->data['TRoute']['name']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __("Route Code"); ?></th>
                <td>:</td>
                <td>
                    <?php echo $this->data['TJourney']['route_code']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __("Seat Type"); ?></th>
                <td>:</td>
                <td>
                    <?php 
                    if($this->data['TJourney']['vehicle_type'] == 1){
                        echo "Normal Seat";
                    } else {
                        echo "VIP Seat";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_GENDER_REQUIRED); ?></th>
                <td>:</td>
                <td>
                    <?php 
                    if($this->data['TJourney']['gender_require'] == 0){
                        echo ACTION_NO;
                    } else {
                        echo ACTION_YES;
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="font-size: 14px; font-weight: bold;">Single Trip Price</td>
            </tr>
            <tr>
                <th><?php __("Selling Price"); ?></th>
                <td>:</td>
                <td>
                    <?php echo number_format($this->data['TJourney']['unit_price'], 2)." ".$this->data['CurrencyCenter']['symbol']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __("Selling Price"); ?></th>
                <td>:</td>
                <td>
                    <?php echo number_format($this->data['TJourney']['membership'], 2)." ".$this->data['CurrencyCenter']['symbol']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __("Agency Price"); ?></th>
                <td>:</td>
                <td>
                    <?php echo number_format($this->data['TJourney']['agent_price_amount'], 2)." ".$this->data['CurrencyCenter']['symbol']; ?>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="font-size: 14px; font-weight: bold;">Round Trip Price</td>
            </tr>
            <tr>
                <th><?php __("Round Trip Price"); ?></th>
                <td>:</td>
                <td>
                    <?php echo number_format($this->data['TJourney']['round_price'], 2)." ".$this->data['CurrencyCenter']['symbol']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __("Round Trip Price VIP Card"); ?></th>
                <td>:</td>
                <td>
                    <?php echo number_format($this->data['TJourney']['round_price_vip'], 2)." ".$this->data['CurrencyCenter']['symbol']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __("Agency Round Trip Price"); ?></th>
                <td>:</td>
                <td>
                    <?php echo number_format($this->data['TJourney']['agent_round_price'], 2)." ".$this->data['CurrencyCenter']['symbol']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(MENU_BOARDING_POINT); ?></th>
                <td>:</td>
                <td>
                    <?php
                    $sqlBoardingPoint = mysql_query("SELECT CONCAT(t_boarding_points.name, ' (', t_journey_boarding_points.time, ')')
                                                     FROM t_journey_boarding_points 
                                                     INNER JOIN t_boarding_points ON t_journey_boarding_points.t_boarding_point_id = t_boarding_points.id 
                                                     WHERE t_journey_boarding_points.t_journey_id = ".$this->data['TJourney']['id']." ORDER BY t_journey_boarding_points.time ASC");
                    while($rowBoardingPoint = mysql_fetch_array($sqlBoardingPoint)){
                        echo $rowBoardingPoint[0]."<br>";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php __(MENU_DROP_OFF); ?></th>
                <td>:</td>
                <td>
                    <?php 
                    $sqlDropOff = mysql_query("SELECT CONCAT(t_drop_offs.name, ' (', t_journey_drop_offs.time, ')') 
                                               FROM t_journey_drop_offs 
                                               INNER JOIN t_drop_offs ON t_drop_offs.id = t_journey_drop_offs.t_drop_off_id
                                               WHERE t_journey_drop_offs.t_journey_id = ".$this->data['TJourney']['id']." ORDER BY t_journey_drop_offs.time ASC");
                    while($rowDropOff = mysql_fetch_array($sqlDropOff)){
                        echo $rowDropOff[0]."<br>";
                    }
                    ?>
                </td>
            </tr>
        </table>
 </fieldset>
<fieldset style="width: 48%; float: right; height: 700px;">
    <legend><?php __(TABLE_SCHEDULE); ?></legend>
    <table style="width: 100%;" cellpadding="5">
        <tr>
            <th style="width:30%;"><?php __(MENU_DEPARTURE_TIME); ?></th>
            <td style="width:1%;">:</td>
            <td>
                <?php 
                $departureTimes = explode(":", $this->data['TDepartureTime']['name']);
                $arrivalTime    = explode(":", $this->data['TJourney']['arrival']);
                if(!empty($departureTimes)){
                    echo $departureTimes[0].":".$departureTimes[1];
                }
                ?>
            </td>
        </tr>
        <tr>
            <th><?php __(TABLE_ARRIVAL_TIME); ?></th>
            <td style="width:1%;">:</td>
            <td>
                <?php 
                if(!empty($departureTimes)){
                    echo $arrivalTime[0].":".$arrivalTime[1];
                }
                ?>
            </td>
        </tr>
        <tr>
            <th><?php __(TABLE_DURATION); ?></th>
            <td style="width:1%;">:</td>
            <td>
                <?php 
                if(!empty($this->data['TJourney']['duration'])){
                    $duration = explode(":", $this->data['TJourney']['duration']);
                    echo $duration[0]." Hours and ".$duration[1]." Minutes";
                }
                ?>
            </td>
        </tr>
        <tr>
            <th><?php __(TABLE_ADVANCE_BOOKING); ?></th>
            <td style="width:1%;">:</td>
            <td>
                <?php echo $this->data['TJourney']['advance_booking']; ?> <?php echo TABLE_DAY; ?>
            </td>
        </tr>
        <tr>
            <th><?php __(TABLE_ALLOW_CANCELLATION); ?></th>
            <td style="width:1%;">:</td>
            <td>
                <?php 
                if($this->data['TJourney']['allow_cancellation'] == 0){
                    echo ACTION_NO;
                } else {
                    echo ACTION_YES;
                }
                ?>
            </td>
        </tr>
        <tr>
            <th><?php __(TABLE_REJECT_BEFORE_DEPARTURE); ?></th>
            <td style="width:1%;">:</td>
            <td>
                <?php echo $this->data['TJourney']['reject_before_departure']; ?> <?php echo TABLE_MINUTE; ?>
            </td>
        </tr>
        <tr>
            <th><?php echo TABLE_DELAY_AFTER_DEPARTURE; ?> :</th>
            <td style="width:1%;">:</td>
            <td>
                <div class="inputContainer">
                    <?php if($this->data['TJourney']['delay_af_departure'] == 0){ echo '0'; } ?>
                    <?php if($this->data['TJourney']['delay_af_departure'] == 30){ echo '30 Min'; } ?>
                    <?php if($this->data['TJourney']['delay_af_departure'] == 60){ echo '1 Hour'; } ?>
                    <?php if($this->data['TJourney']['delay_af_departure'] == 90){ echo '1 Hour 30 Min'; } ?>
                    <?php if($this->data['TJourney']['delay_af_departure'] == 120){ echo '2 Hour'; } ?>
                    <?php if($this->data['TJourney']['delay_af_departure'] == 150){ echo '2 Hour 30 Min'; } ?>
                    <?php if($this->data['TJourney']['delay_af_departure'] == 180){ echo '3 Hour'; } ?>
                    <?php if($this->data['TJourney']['delay_af_departure'] == 210){ echo '3 Hour 30 Min'; } ?>
                    <?php if($this->data['TJourney']['delay_af_departure'] == 240){ echo '4 Hour'; } ?>
                </div>
            </td>
        </tr>
        <tr>
            <th style="vertical-align: top;"><?php __(TABLE_WEEKLY_SCHEDULE); ?></th>
            <td style="width:1%; vertical-align: top;">:</td>
            <td>
                <?php 
                $sqlSchedule = mysql_query("SELECT * FROM t_journey_schedules WHERE t_journey_id = ".$this->data['TJourney']['id']);
                $rowSchedule = mysql_fetch_array($sqlSchedule);
                $field = '';   
                if($rowSchedule['sun'] == 1){
                    $field .= TABLE_SUNDAY.'<br/><br/>';
                }
                if($rowSchedule['mon'] == 1){
                    $field .= TABLE_MONDAY.'<br/><br/>';
                }
                if($rowSchedule['tue'] == 1){
                    $field .= TABLE_TUESDAY.'<br/><br/>';
                }
                if($rowSchedule['wed'] == 1){
                    $field .= TABLE_WEDNESDAY.'<br/><br/>';
                }
                if($rowSchedule['thu'] == 1){
                    $field .= TABLE_THURSDAY.'<br/><br/>';
                }
                if($rowSchedule['fri'] == 1){
                    $field .= TABLE_FRIDAY.'<br/><br/>';
                }
                if($rowSchedule['sat'] == 1){
                    $field .= TABLE_SATURDAY.'<br/><br/>';
                }
                echo $field;
                ?>
            </td>
        </tr>
        <tr>
            <th style="vertical-align: top;"><?php echo TABLE_ALLOW_ACCESS; ?></th>
            <td style="width:1%; vertical-align: top;">:</td>
            <td style="vertical-align: top;">
                <div class="inputContainer">
                    <?php 
                    if($this->data['TJourney']['allow_access'] == 0){ 
                        echo "Internal";
                    } else if($this->data['TJourney']['allow_access'] == 1){ 
                        echo "Internal, API";
                    } else if($this->data['TJourney']['allow_access'] == 2){ 
                        echo "Internal, Online";
                    } else if($this->data['TJourney']['allow_access'] == 3){ 
                        echo "Internal, API, Online";
                    }
                    ?>                  
                </div>
            </td>
        </tr>
        <tr>
            <th style="vertical-align: top;"><?php echo TABLE_NOTE; ?></th>
            <td style="width:1%;">:</td>
            <td style="vertical-align: top;">
                <?php 
                echo nl2br($this->data['TJourney']['note']); 
                ?>
            </td>
        </tr>
    </table>
</fieldset>
<div style="clear: both;"></div>
<fieldset style="width: 48%; float: left; height: 400px;">
    <legend><?php __(TABLE_JOURNEY_DIRECTION); ?></legend>
    <table cellpadding="0" cellspacing="0" style="width: 90%;">
        <tr>
            <td style="width: 20%;"><input type="radio" disabled="" <?php if($this->data['TJourney']['type'] == 1){ ?>checked=""<?php } ?> /> Direct</td>
            <td style="width: 20%;"><input type="radio" disabled="" <?php if($this->data['TJourney']['type'] == 2){ ?>checked=""<?php } ?> /> Transit</td>
            <td><input type="radio" disabled="" <?php if($this->data['TJourney']['type'] == 3){ ?>checked=""<?php } ?> /> Direct Multi Route</td>
        </tr>
    </table>
    <br/>
    <table class="table" cellpadding="0" cellspacing="0" style="width: 100%; <?php if($this->data['TJourney']['type'] == 1){ ?>display: none;<?php } ?>">
        <tr>
            <th class="first" style="width: 70%;"><?php echo MENU_JOURNEY; ?></th>
            <th style="width: 30%;"><?php echo TABLE_DEPARTURE_TIME; ?></th>
        </tr>
        <tbody>
            <?php
            foreach($tJourneyTransits AS $tJourneyTransit){
                $rnd = rand(0,200);
            ?>
            <tr>
                <td class="first">
                    <div class="inputContainer" style="width: 100%;">
                        <?php
                        $sqlJ = mysql_query("SELECT t_journeys.*, t_departure_times.name AS departure FROM t_journeys INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id WHERE t_journeys.id = ".$tJourneyTransit['TJourneyTransit']['t_journey_departure_id'].";");
                        $rowJ = mysql_fetch_array($sqlJ);
                        echo $rowJ['description'];
                        ?>
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php
                        $time = explode(":",$rowJ['departure']);
                        echo $time[0].':'.$time[1];
                        ?>
                    </div>
                </td>
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
</fieldset>
<fieldset style="width: 48%; float: left; height: 400px; overflow: auto;">
    <legend><?php __("Edited History"); ?></legend>
    <br/>
    <table class="table" cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <th class="first" style="width: 30%;"><?php echo "Action"; ?></th>
            <th style="width: 30%;"><?php echo "Edited Date"; ?></th>
            <th style="width: 40%;"><?php echo "Edited By"; ?></th>
        </tr>
        <tbody>
            <?php
            $sqlEdit = mysql_query("SELECT t_journey_edit_histories.*, CONCAT_WS(' ',first_name, last_name) AS user FROM t_journey_edit_histories 
                                    INNER JOIN users ON users.id = t_journey_edit_histories.edited_by
                                    WHERE t_journey_edit_histories.offline_project_id= 1 AND t_journey_edit_histories.t_journey_id = ".$this->data['TJourney']['id']." ORDER BY edited_date DESC");
            while($rowEdit = mysql_fetch_array($sqlEdit)){
            ?>
            <tr>
                <td class="first"><?php echo $rowEdit['action']; ?></td>
                <td><?php echo dateShort($rowEdit['edited_date']); ?></td>
                <td><?php echo $rowEdit['user']; ?></td>
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
</fieldset>
<div style="clear: both;"></div>