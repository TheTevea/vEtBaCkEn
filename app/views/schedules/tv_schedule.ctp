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
                        <td style="width: 550px; padding: 0px;">
                            <div class="main_departure" style="padding-bottom: 10px; width: 100%;">
                                <div class="custom_departure" style="width: 100%;">
                                    <div style="padding: 0px; font-size: 20px; color: #fff; font-weight: bold; font-family: KhBattambang; width: 100%;">សាខា <?php echo $rowDest['name_kh']; ?> <span style="font-size: 17px; color: #fff; font-weight: bold; font-family: KhBattambang;"><?php echo $rowDest['name']; ?> Branch</span></div>
                                    <div style="padding: 0px; font-size: 20px; color: #fff; font-weight: bold; font-family: 'Times New Roman'; width: 100%;">តារាងម៉ោងចេញដំណើរ <span style="font-size: 17px; color: #fff; font-weight: bold; font-family: KhBattambang;">Departure Time</span></div>
                                </div>
                                <div class="empty_div"></div>
                            </div>
                        </td>
                        <td>
                            <div style="padding-top: 10px; padding-bottom: 10px; padding-right: 10px;">
                                <div style="width: 100%; text-align: left; margin-top: 16px; font-size: 20px; color: #fff; font-weight: bold; font-family: 'Times New Roman';">Hotline: 081 911 911</div>
                                <div style="width: 100%; text-align: left; font-size: 17px; color: #fff; font-weight: bold; font-family: 'Times New Roman';"><span style="font-size: 20px; color: #fff; font-weight: bold; font-family: KhBattambang;">ថ្ងៃខែ</span> / <span style="font-size: 20px; color: #fff; font-weight: bold; font-family: 'Times New Roman';">Date</span>: <?php echo date("d-m-Y"); ?> <span class="date_time" style="font-size: 17px; color: #fff;"><?php echo date("H:i:s"); ?></span></div>
                            </div>
                        </td>
                    </tr>
                </table>
                <div style="clear: both;"></div>
            </div>
            <div style="padding-bottom: 5px; padding-top: 8px; background: #28303A;">
                <table class="custom-table-departure-schedule">
                    <thead>
                        <tr>
                            <th style="width: 100px; text-align: center; font-size: 12px; font-family: 'Times New Roman';">
                                <div style="padding: 0px; font-size: 18px; color: #fff; font-weight: bold; font-family: KhBattambang; line-height: 30px;" class="wordSwitch" kh="ម៉ោង​ចេញ" en="DEPARTURE" chw="ម៉ោង​ចេញ">DEPARTURE</div>
                            </th>
                            <th style="width: 250px; font-size: 12px; font-family: 'Times New Roman';">
                                <div style="padding: 0px; font-size: 18px; color: #fff; font-weight: bold; font-family: KhBattambang; line-height: 30px;" class="wordSwitch" kh="ទិសដៅ​" en="DESTINATION​" chw="ទិសដៅ​">DESTINATION​</div>
                            </th>
                            <th style="width: 180px; font-size: 12px; font-family: 'Times New Roman'; padding: 0px;">
                                <div style="padding: 0px; font-size: 18px; color: #fff; font-weight: bold; font-family: KhBattambang; line-height: 30px;" class="wordSwitch" kh="ប្រភេទរថយន្ត" en="VEHICLE TYPE​" chw="ប្រភេទរថយន្ត">VEHICLE TYPE​</div>
                            </th>
                            <th style="width: 180px; font-size: 12px; font-family: 'Times New Roman'; padding: 0px;">
                                <div style="padding: 0px; font-size: 18px; color: #fff; font-weight: bold; font-family: KhBattambang; line-height: 30px;" class="wordSwitch" kh="លេខរថយន្ត" en="VEHICLE NO." chw="លេខរថយន្ត">VEHICLE NO.​</div>
                            </th>
                            <th style="text-align: left; font-size: 12px; font-family: 'Times New Roman'; padding-left: 8px;">
                                <div style="padding: 0px; font-size: 18px; color: #fff; font-weight: bold; font-family: KhBattambang; line-height: 30px;" class="wordSwitch" kh="សំគាល់​" en="STATUS" chw="សំគាល់​">STATUS</div>
                            </th>
                        </tr>
                    <thead>
                    <tbody id="bodyShceduleDisplayList"></tbody>
                </table>
            </div>
        </section>
    </div>
</div>
<style>
.table_body .banner {
    background: #F15A28;
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
    background: #28303A;
    overflow: hidden;
    width: 100%;
    margin: 0 auto;
    position: relative;
    font-family: KhBattambang;
    color: #fff;
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
    height: 30px;
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
    const wordTimeout = [];
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

    function setWordLang(){
        $(".wordSwitch").each(function(){
            var wordKh = $(this).attr("kh");
            var wordEn = $(this).attr("en");
            $(this).text(wordEn).attr("chw", wordKh);
        });
    }

    function wordLoop(){
        var index = 1;
        if(wordTimeout.length > 0){
            $.each(wordTimeout , function(index, val) {
                if(wordTimeout[index] != undefined){
                    clearTimeout(wordTimeout[index]);
                }
            });
        }
        getWordtoReplace(index);
    }

    function getWordtoReplace(index){
        $(".wordSwitch").each(function(i, val){
            var orgWord = $(this).text();
            var chgWord = $(this).attr("chw");
            $(this).text(chgWord).attr("chw", orgWord);
        });
        wordTimeout[index] = setTimeout(function() {
            wordLoop();
        }, 8000);
    }

    function displayLoop(){
        var i = 0;
        var index = 1;
        var display = [];
        var screen  = "";
        $(".busScheduleList").each(function(){
            screen += '<tr class="tr_hover busScheduleList" style="'+$(this).attr("style")+'">';
            screen += $(this).html();
            screen += '</tr>';
            if(i == 7 || index == $(".busScheduleList").length){
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
        // Word Switch
        setWordLang();
        wordLoop();
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
            url: "<?php echo $this->base . '/'; ?>schedules/getTvScheduleDisplay/",
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
                // Word Switch
                setWordLang();
                wordLoop();
                waitForFinalConection(function(){
                    // Recheck Conection
                    getScheduleList();
                }, 120000, "Finish");
            }
        });
    }
</script>