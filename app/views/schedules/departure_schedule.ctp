<?php
$sqlDest = mysql_query("SELECT * FROM t_destinations WHERE id = (SELECT t_destination_id FROM main_branches WHERE id = ".$user['User']['main_branch_id']." LIMIT 1)");
$rowDest = mysql_fetch_array($sqlDest);
?>
<div class="leftPanel">
    <div id="dynamic">
        <section class="table_body">
            <div class="banner">
                <table cellpadding="0" cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style="width: 40%; padding: 0px;">
                            <div class="main_departure" style="padding-bottom: 10px;">
                                <div class="custom_departure">
                                    <div style="padding: 0px; font-size: 20px; color: #fff; font-weight: bold; font-family: KhBattambang;">សាខា <?php echo $rowDest['name_kh']; ?> <span style="font-size: 17px; color: #fff; font-weight: bold; font-family: KhBattambang;"><?php echo $rowDest['name']; ?> Branch</span></div>
                                    <div style="padding: 0px; font-size: 20px; color: #fff; font-weight: bold; font-family: 'Times New Roman';">តារាងម៉ោងចេញដំណើរ <span style="font-size: 17px; color: #fff; font-weight: bold; font-family: KhBattambang;">Departure Time</span></div>
                                </div>
                                <div class="empty_div"></div>
                            </div>
                        </td>
                        <td style="width: 20%; text-align: center; font-size: 20px; font-weight: bold; font-family: 'Times New Roman'; color: #fff;"></td>
                        <td style="width: 40%;">
                            <div style="padding-top: 10px; padding-bottom: 10px; padding-right: 10px;">
                                <div style="width: 100%; text-align: right; margin-top: 16px; font-size: 20px; color: #fff; font-weight: bold; font-family: 'Times New Roman';">Hotline: 081 911 911</div>
                                <div style="width: 100%; text-align: right; font-size: 17px; color: #fff; font-weight: bold; font-family: 'Times New Roman';"><span style="font-size: 20px; color: #fff; font-weight: bold; font-family: KhBattambang;">ថ្ងៃខែ</span> / <span style="font-size: 20px; color: #fff; font-weight: bold; font-family: 'Times New Roman';">Date</span>: <?php echo date("d-m-Y"); ?> <span class="date_time" style="font-size: 17px; color: #fff;"><?php echo date("H:i:s"); ?></span></div>
                            </div>
                        </td>
                    </tr>
                </table>
                <div style="clear: both;"></div>
            </div>
            <table class="custom-table-departure-schedule">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center; font-size: 12px; font-family: 'Times New Roman'; height: 45px;">
                            <div style="padding: 0px; font-size: 15px; color: #de5d0a; font-weight: bold; font-family: KhBattambang; line-height: 28px;">ក្រុមហ៊ុន</div>
                            COMPANY
                        </th>
                        <th style="width: 280px; font-size: 12px; font-family: 'Times New Roman';">
                            <div style="padding: 0px; font-size: 15px; color: #de5d0a; font-weight: bold; font-family: KhBattambang; line-height: 28px;">ទិសដៅ​​</div>
                            DESTINATION
                        </th>
                        <th style="width: 170px; font-size: 12px; font-family: 'Times New Roman';">
                            <div style="padding: 0px; font-size: 15px; color: #de5d0a; font-weight: bold; font-family: KhBattambang; line-height: 28px;">ប្រភេទរថយន្ត​</div>
                            VEHICLE TYPE
                        </th>
                        <th style="width: 180px; font-size: 12px; font-family: 'Times New Roman';">
                            <div style="padding: 0px; font-size: 15px; color: #de5d0a; font-weight: bold; font-family: KhBattambang; line-height: 28px;">លេខរថយន្ត​</div>
                            VEHICLE NO.
                        </th>
                        <th style="width: 100px; text-align: center; font-size: 12px; font-family: 'Times New Roman';">
                            <div style="padding: 0px; font-size: 15px; color: #de5d0a; font-weight: bold; font-family: KhBattambang; line-height: 28px;">ម៉ោង​ចេញ</div>
                            DEPARTURE
                        </th>
                        <th style="width: 60px; text-align: center; font-size: 12px; font-family: 'Times New Roman';">

                        </th>
                        <th style="width: 100px; text-align: center; font-size: 12px; font-family: 'Times New Roman';">
                            <div style="padding: 0px; font-size: 15px; color: #de5d0a; font-weight: bold; font-family: KhBattambang; line-height: 28px;">ម៉ោងប្តូរ​</div>
                            REVISED 
                        </th>
                        <th style="text-align: left; font-size: 12px; font-family: 'Times New Roman'; padding-left: 20px;">
                            <div style="padding: 0px; font-size: 15px; color: #de5d0a; font-weight: bold; font-family: KhBattambang; line-height: 28px;">សំគាល់​</div>
                            REMARKS
                        </th>
                    </tr>
                <thead>
                <tbody id="bodyShceduleDisplayList">
                    <?php
                    $i = 1;
                    $date = date("Y-m-d");
                    if(date("H") >= 0 && date("H") <= 3){
                        $date = date("Y-m-d", strtotime("-1 day", strtotime(date("Y-m-d"))));
                    }
                    $timeDisplay = date('H:i',strtotime('+1 hours',strtotime(date("H:i:s"))));
                    $sqlJourney  = mysql_query("SELECT t_journeys.id, t_journeys.company_id, t_destinations.name AS destUs, t_destinations.name_kh AS destKh, bus_types.name AS busType, t_transportation_types.number_of_seat AS numOfSeat, CONCAT(buses.code, ' (', buses.name, ')') AS busName, t_departure_times.name AS departure, bus_schedules.delay_time AS delayTime, bus_schedules.note
                                               FROM t_journeys 
                                               INNER JOIN t_destinations ON t_destinations.id = t_journeys.t_destination_to_id
                                               INNER JOIN t_transportation_types ON t_transportation_types.id = t_journeys.t_transportation_type_id
                                               INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id
                                               INNER JOIN bus_schedule_details ON bus_schedule_details.t_journey_id = t_journeys.id
                                               INNER JOIN bus_schedules ON bus_schedules.id = bus_schedule_details.bus_schedule_id
                                               INNER JOIN buses ON buses.id = bus_schedules.bus_id
                                               INNER JOIN bus_types ON bus_types.id = buses.bus_type_id
                                               WHERE t_journeys.status = 5 AND t_journeys.t_destination_from_id = (SELECT t_destination_id FROM main_branches WHERE id = ".$user['User']['main_branch_id']." LIMIT 1) AND bus_schedules.date = '".$date."' AND TIME(t_departure_times.name) >= TIME(DATE_ADD(now(), interval -30 minute)) AND TIME(t_departure_times.name) <= TIME(DATE_ADD(now(),interval 1 hour))");
                    while($rowJourney = mysql_fetch_array($sqlJourney)){
                        $rowBg = "#2f2f58";
                        $rowType = 1;
                        if($i == 2){
                            $rowBg = "#463c3c";
                            $rowType = 2;
                            $i = 1;
                        } else {
                            $i++;
                        }
                    ?>
                    <tr class="tr_hover" style="background: <?php echo $rowBg; ?>;">
                        <td class="bg_color_td" style="width: 100px; max-width: 100px; height: 70px; text-align: center;">
                            <?php
                            if($rowJourney['company_id'] == 6){ // Buva Sea
                                $comImg = "buvaSea.png";
                            } else if($rowJourney['company_id'] == 7){ // Air Bus
                                $comImg = "airBus.png";
                            } else {
                                $comImg = "vetBus.png";
                            }
                            ?>
                            <input type="hidden" class="rowType" value="<?php echo $rowType; ?>" />
                            <input type="hidden" class="rowId" value="<?php echo $rowJourney['id']; ?>" />
                            <img style="width: 30px;" src="<?php echo $this->webroot; ?>img/<?php echo $comImg; ?>" alt="" />
                        </td>
                        <td class="bg_color_td" style="width: 250px;">
                            <span class="color__fff" style="font-size: 16px;"><?php echo $rowJourney['destKh']; ?></span> <br>
                            <span class="th_color" style="font-size: 13px;"> <?php echo $rowJourney['destUs']; ?></span>
                        </td>
                        <td class="bg_color_td color__fff" style="width: 200px;">
                            <span class="th_color" style="text-align: center; font-size: 15px;"><?php echo $rowJourney['busType']; ?></span>
                        </td>
                        <td class="bg_color_td color__fff" style="width: 200px;"><?php echo $rowJourney['busName']; ?></td>
                        <td class="bg_color_td color__fff" style="width: 100px; text-align: center; font-size: 18px !important;">
                            <?php 
                            $departure = explode(":", $rowJourney['departure']);
                            echo $departure[0].":".$departure[1]; ?>
                        </td>
                        <td style="width: 100px;max-width: 100px; text-align: center;">
                            <?php
                            if(!empty($rowJourney['delayTime'])){
                            ?>
                            <img style="width: 40px;" src="<?php echo $this->webroot; ?>img/arrow_red.png" alt=""/>
                            <?php
                            }
                            ?>
                        </td>
                        <td class="bg_color_td color__fff" style="width: 100px; text-align: center; font-size: 18px !important;">
                            <?php 
                            if(!empty($rowJourney['delayTime'])){
                                $delayTime = date('H:i',strtotime('+'.$rowJourney['delayTime'].' minutes',strtotime($rowJourney['departure'])));
                                echo $delayTime;
                            }
                            ?>
                        </td>
                        <td class="bg_color_td color__fff" style="text-align: left;">
                            <?php echo $rowJourney['note']; ?>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>
</div>
<style>
.table_body .banner {
    background: #de5d0a;
}
.main_departure {
    display: flex;
}
.main_departure .custom_departure {
    width: 70%;
    padding: 15px 16px 0px 16px;
}
.main_departure .custom_departure .title_kh {
    font-size: 30px;
    font-weight: bold;
    color: #fff;
}
.main_departure .custom_departure .title_en {
    font-size: 23px;
    font-weight: bold;
    color: #fff;
}
.main_departure .empty_div {
    width: 30%;
} 
.date_parture {
    display: flex;
}
.date_parture .emptys {
    width: 70%;
    padding-left: 39px;
}
.date_parture .container_date {
    width: 30%;
    display: flex;
    padding-bottom: 5px;
}
.date_parture .container_date .date_class {
    width: 80%;
    font-weight: 500;
    font-size: 22px;
    color: #fff;
    text-align: end;
}
.date_parture .container_date .date_time {
    width: 30%;
    font-weight: 500;
    font-size: 14px;
    color: #fff;
    text-align: center;
}



.custom-table-departure-schedule {
    border-spacing: 1;
    border-collapse: collapse;
    background: white;
    overflow: hidden;
    width: 100%;
    margin: 0 auto;
    position: relative;
    font-family: KhBattambang;
}
table * {
    position: relative;
}
table td,
table th {
    padding-left: 8px;
}
.bg_color_td {
    font-weight: bold;
    font-size: 15px;
}
table thead tr {
    height: 60px;
    font-size: 14px;
}
table tbody tr {
    height: 48px;
}
table tbody tr:last-child {
    border: 0;
}
table td,
table th {
    text-align: left;
}
table .th_color {
    color: #de5d0a;
    font-weight: bold;
    font-size: 15px;
}
table td.l,
table th.l {
    text-align: right;
}
table td.c,
table th.c {
    text-align: center;
}
table td.r,
table th.r {
    text-align: center;
}
.color__fff {
    color: #fff;
}
@media screen and (max-width: 35.5em) {
  table {
    display: block;
  }
  table > *,
    table tr,
    table td,
    table th {
        display: block;
    }
    table thead {
        display: none;
    }
    table tbody tr {
        height: auto;
        padding: 8px 0;
    }
    table tbody tr td {
        padding-left: 45%;
        margin-bottom: 12px;
    }
    table tbody tr td:last-child {
        margin-bottom: 0;
    }
    table tbody tr td:before {
        position: absolute;
        font-weight: 700;
        width: 40%;
        left: 10px;
        top: 0;
    }
    table tbody tr td:nth-child(1):before {
        content: "Code";
    }
    table tbody tr td:nth-child(2):before {
        content: "Stock";
    }
    table tbody tr td:nth-child(3):before {
        content: "Cap";
    }
    table tbody tr td:nth-child(4):before {
        content: "Inch";
    }
    table tbody tr td:nth-child(5):before {
        content: "Box Type";
    }
}
</style>
<script type="text/javascript">
    var time = 25000;
    var loop = 1;
    const screenTimeout = [];
    var waitForFinalConection = (function () {
        var timers = {};
        return function (callback, ms, uniqueId) {
            if (!uniqueId) {
              uniqueId = "Don't call this twice without a uniqueId";
            }
            if (timers[uniqueId]) {
              clearTimeout (timers[uniqueId]);
            }
            timers[uniqueId] = setTimeout(callback, ms);
        };
    })();

    var animationTimeout = (function () {
        var timers = {};
        return function (callback, ms, uniqueId) {
            if (!uniqueId) {
              uniqueId = "Don't call this twice without a uniqueId";
            }
            if (timers[uniqueId]) {
              clearTimeout (timers[uniqueId]);
            }
            timers[uniqueId] = setTimeout(callback, ms);
        };
    })();

    $(document).ready(function(){
        var serverClock = $(".date_time");
        if (serverClock.length > 0) {
            showServerTime(serverClock, serverClock.text());
        }
        getScheduleList();
    });

    function displayLoop(){
        var i = 0;
        var index = 1;
        var display = [];
        var screen  = "";
        $(".busScheduleList").each(function(){
            screen += '<tr class="tr_hover busScheduleList" style="'+$(this).attr("style")+'">';
            screen += $(this).html();
            screen += '</tr>';
            if(i == 9 || index == $(".busScheduleList").length){
                i = 0;
                display.push(screen);
                screen = "";
            } else {
                i++;
            }
            index++;
        });
        if(screenTimeout.length > 0){
            $.each(screenTimeout , function(index, val) {
                if(screenTimeout[index] != undefined){
                    clearTimeout(screenTimeout[index]);
                }
            });
        }
        if(display.length > 1){
            loop = 1;
            $("#bodyShceduleDisplayList").html(display[0]);
            displayAnimation(display);
        }
    }   

    function showDisplay(screen){
        $("#bodyShceduleDisplayList").html(screen);
    }

    function displayAnimation(display){
        $.each(display , function(index, val) {
            var timeout = time * loop;
            screenTimeout[loop] = setTimeout(function() {
                showDisplay(val);
            }, timeout);
            loop++;
        });
        var timeout = time * loop;
        screenTimeout[loop] = setTimeout(function() {
            displayAnimation(display);
        }, timeout);
    }

    function showServerTime(obj, time) {
        var parts = time.split(":"),
        newTime   = new Date();
        newTime.setHours(parseInt(parts[0], 10));
        newTime.setMinutes(parseInt(parts[1], 10));
        newTime.setSeconds(parseInt(parts[2], 10));
        
        var timeDifference  = new Date().getTime() - newTime.getTime();

        var methods = {
            displayTime: function () {

                var now = new Date(new Date().getTime() - timeDifference);

                obj.text([
                    methods.leadZeros(now.getHours(), 2),
                    methods.leadZeros(now.getMinutes(), 2),
                    methods.leadZeros(now.getSeconds(), 2)
                ].join(":"));
                setTimeout(methods.displayTime, 500);
            },
            leadZeros: function (time, width) {
                while (String(time).length < width) {
                    time = "0" + time;
                }
                return time;
            }
        }
        methods.displayTime();
    }

    function getScheduleList(){
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "<?php echo $this->base . '/'; ?>schedules/getScheduleDisplay/",
            error: function() {
                waitForFinalConection(function(){
                    // Recheck Conection
                    getScheduleList();
                }, 120000, "Finish");
            },
            beforeSend: function(){
                
            },
            success: function(response){
                $("#bodyShceduleDisplayList").html(response.result);
                displayLoop();
                waitForFinalConection(function(){
                    // Recheck Conection
                    getScheduleList();
                }, 120000, "Finish");
            }
        });
    }
</script>