<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".busScheduleChosen").chosen({width: 265});
        $("#BusScheduleDeparture").chosen({width: 300});
        // Varaible Delay
        var delayTimeBusSchedule = (function () {
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
        // Click Tab Refresh Form List: Screen, Title, Scroll
        if(tabBusScheduleReg != tabBusScheduleId){
            $("a[href='"+tabBusScheduleId+"']").click(function(){
                if($("#bodyListBusSchedule").html() != '' && $("#bodyListBusSchedule").html() != null){
                    delayTimeBusSchedule(function(){
                        refreshScreenBusSchedule();
                        resizeFormTitleBusSchedule();
                        resizeFornScrollBusSchedule();  
                    }, 500, "Finish");
                }
            });
            tabBusScheduleReg = tabBusScheduleId;
        }

        // Calculate Form Table List
        delayTimeBusSchedule(function(){
              refreshScreenBusSchedule();
              resizeFormTitleBusSchedule();
              resizeFornScrollBusSchedule();  
        }, 500, "Finish");
        
        // Calculate Form Table List After Window Resize
        $(window).resize(function(){
            if(tabBusScheduleReg == $(".ui-tabs-selected a").attr("href")){
                delayTimeBusSchedule(function(){
                    refreshScreenBusSchedule();
                    resizeFormTitleBusSchedule();
                    resizeFornScrollBusSchedule();
                  }, 500, "Finish");
            }
        });

        $("#BusScheduleAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#BusScheduleAddForm").ajaxForm({
            dataType: 'json',
            beforeSerialize: function($form, options) {
                var busSelected = true;
                $(".busScheduleBusIsChosen").each(function(){
                    if($(this).val() == null || $(this).val() == ""){
                        busSelected = false;
                    }
                });
                if(busSelected == false){
                    alertSelectRequireField();
                    $(".btnSaveAddBusSchedule").removeAttr('disabled');
                    return false;
                }
                $("#BusScheduleDate").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveBusSchedule").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            error: function (result) {
                createSysAct('BusSchedule', 'Add', 2, result.responseText);
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
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
                        backMoveTransportation();
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
                createSysAct('BusSchedule', 'Add', 1, '');
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackBusSchedule").click();
                if(result.error == '0'){
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                } else {
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
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
        $(".btnBackBusSchedule").unbind("click").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableBusSchedule.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });

        $("#BusScheduleDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: 0
        });

        $(".btnCheckBusSchedule").unbind("click").click(function(){
            var dateInput = $("#BusScheduleDate").val();
            var destFrom  = $("#BusScheduleTDestinationFromId").val();
            var destTo    = $("#BusScheduleTDestinationToId").val();
            var departure = $("#BusScheduleDeparture").val();
            if(destFrom != "" && destTo != "" && departure != "" && dateInput != ""){
                var destFromName = $("#BusScheduleTDestinationFromId").find("option:selected").text();
                var destToName   = $("#BusScheduleTDestinationToId").find("option:selected").text();
                var dateLbl      = dateInput;
                var date         = $("#BusScheduleDate").val().split("/")[2]+"-"+$("#BusScheduleDate").val().split("/")[1]+"-"+$("#BusScheduleDate").val().split("/")[0];
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/getJourney/"+destFrom+"/"+destTo,
                    data: "departure="+departure+"&date="+date,
                    beforeSend: function(){
                        $("#BusScheduleTDestinationFromId_chosen, #BusScheduleTDestinationToId_chosen, #BusScheduleDeparture_chosen, #BusScheduleDate").hide();
                        $("#BusScheduleTDestinationFromName").text(destFromName);
                        $("#BusScheduleTDestinationToName").text(destToName);
                        $("#BusScheduleDateName").text(dateLbl);
                        $("#BusScheduleDepartureName").text(departure);
                        $(".btnSaveAddBusSchedule").hide();
                        $(".btnCheckBusSchedule").attr("disabled", true);
                        $("#lblCheckBusSchedule").html("<?php echo ACTION_LOADING; ?>");
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                    },
                    success: function(result){
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                        if(result.response != "" && result.error == "0"){
                            $(".btnCheckBusSchedule").hide();
                            $(".btnSaveAddBusSchedule").show();
                            $("#tblBusSchedule").html(result.response);
                            eventKeyBusSchedule();
                        } else {
                            $("#BusScheduleTDestinationFromId_chosen, #BusScheduleTDestinationToId_chosen, #BusScheduleDeparture_chosen, #BusScheduleDate").show();
                            $("#BusScheduleTDestinationFromName, #BusScheduleTDestinationToName, #BusScheduleDepartureName, #BusScheduleDateName").text("");
                            $(".btnCheckBusSchedule").removeAttr("disabled");
                            $("#lblCheckBusSchedule").html("<?php echo GENERAL_SEARCH; ?>");
                        }
                    }
                });
            } else {
                alertSelectRequireField();
            }
        });


        $("#BusScheduleTDestinationFromId, #BusScheduleTDestinationToId").unbind("change").change(function(){
            if($("#BusScheduleTDestinationFromId").val() != "" && $("#BusScheduleTDestinationToId").val() != ""){
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/getDeparture/"+$("#BusScheduleTDestinationFromId").val()+"/"+$("#BusScheduleTDestinationToId").val(),
                    beforeSend: function(){
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                    },
                    success: function(result){
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                        $("#BusScheduleDeparture").html(result.response);
                        $("#BusScheduleDeparture").trigger("chosen:updated");
                    }
                });
            }
        });
    });

    function eventKeyBusSchedule(){
        $(".btnBusScheduleShowDetail, .btnBusScheduleHideDetail").unbind("click");
        $(".busScheduleBusIsChosen").chosen({width: 200});
        $(".btnBusScheduleShowDetail").click(function(){
            var journeyId = $(this).attr("ref");
            $(".journeyBusSub"+journeyId).show();
            $(this).hide();
            $(this).closest("tr").find(".btnBusScheduleHideDetail").show();
        });

        $(".btnBusScheduleHideDetail").click(function(){
            var journeyId = $(this).attr("ref");
            $(".journeyBusSub"+journeyId).hide();
            $(this).hide();
            $(this).closest("tr").find(".btnBusScheduleShowDetail").show();
        });
    }

    // Calculate Form Table List
    function resizeFormTitleBusSchedule(){
        var screen = 5;
        var widthList = $("#bodyListBusSchedule").width();
        var widthTitle = widthList - screen;
        $("#formTitleBusSchedule").css('padding','0px');
        $("#formTitleBusSchedule").css('margin-top','5px');
        $("#formTitleBusSchedule").css('width',widthTitle);
    }
    
    function resizeFornScrollBusSchedule(){
        var tabHeight  = $(tabBusScheduleId).height();
        var formHeader = 0;
        if ($('#headerBusSchedule').is(':hidden')) {
            formHeader = 0;
        } else {
            formHeader = $("#headerBusSchedule").height();
        }
        var formFooter  = $("#footerBusSchedule").height();
        var tableHeader = $("#formTitleBusSchedule").height();
        var spaceRemain = 90;
        var getHeight   = tabHeight - (formHeader + tableHeader + formFooter + spaceRemain);
        $("#bodyListBusSchedule").css('height',getHeight);
        $("#bodyListBusSchedule").css('padding','0px');
        $("#bodyListBusSchedule").css('width','100%');
        $("#bodyListBusSchedule").css('overflow-x','hidden');
        $("#bodyListBusSchedule").css('overflow-y','scroll');
    }
    
    function refreshScreenBusSchedule(){
        $("#formTitleBusSchedule").removeAttr('style');
    }
</script>
<br />
<?php 
$sqlDestUser = mysql_query("SELECT * FROM t_destinations WHERE id = (SELECT t_destination_id FROM main_branches WHERE id = ".$user['User']['main_branch_id']." LIMIT 1)");
$rowDestUser = mysql_fetch_array($sqlDestUser);

$dataDest = array();
$sqlDest = mysql_query("SELECT * FROM t_destinations WHERE offline_project_id = 1 AND is_active = 1");
while($rowDest = mysql_fetch_array($sqlDest)){
    $dataDest[$rowDest['id']] = $rowDest['name'];
}
echo $this->Form->create('BusSchedule'); 
?>
<fieldset id="headerBusSchedule">
    <legend><?php __(MENU_JOURNEY_BUS_INFO); ?></legend>
    <table cellpadding="2" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="width: 15%;"><label for="BusScheduleDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
            <td style="width: 25%;"><label for="BusScheduleTDestinationFromId"><?php echo TABLE_DESTINATION_FROM; ?> <span class="red">*</span> :</label></td>
            <td style="width: 25%;"><label for="BusScheduleTDestinationToId"><?php echo TABLE_DESTINATION_TO; ?> <span class="red">*</span> :</label></td>
            <td style="width: 20%;"><label for="BusScheduleDeparture"><?php echo TABLE_DEPARTURE; ?> <span class="red">*</span> :</label></td>
            <td></td>
        </tr>
        <tr>
            <td>
                <?php echo $this->Form->text('date', array('class'=>'validate[required]', 'style' => 'width: 80%; height: 20px;', 'autocomplete' => 'off', 'value' => date("d/m/Y"))); ?>
                <span id="BusScheduleDateName"></span>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select name="data[BusSchedule][t_destination_from_id]" id="BusScheduleTDestinationFromId" style="width: 90%;" class="busScheduleChosen">
                        <?php
                        if($user['User']['is_admin'] == 1){
                        ?>
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        }
                        foreach($dataDest AS $key => $val){
                            if($user['User']['is_admin'] == 0){
                                if($rowDestUser['id'] == $key){
                        ?>
                        <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
                        <?php
                                }
                            } else {
                        ?>
                        <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
                        <?php   
                            }
                        }
                        ?>
                    </select>
                    <span id="BusScheduleTDestinationFromName"></span>
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select name="data[BusSchedule][t_destination_to_id]" id="BusScheduleTDestinationToId" style="width: 90%;" class="busScheduleChosen">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        foreach($dataDest AS $key => $val){
                        ?>
                        <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <span id="BusScheduleTDestinationToName"></span>
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select name="data[BusSchedule][departure]" id="BusScheduleDeparture" style="width: 200px; height: 35px;">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                    </select>
                    <span id="BusScheduleDepartureName"></span>
                </div>
            </td>
            <td>
                <div class="buttons">
                    <a href="#" class="positive btnCheckBusSchedule">
                        <img src="<?php echo $this->webroot; ?>img/button/search.png" alt=""/>
                        <span id="lblCheckBusSchedule"><?php echo GENERAL_SEARCH; ?></span>
                    </a>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<table id="formTitleBusSchedule" class="table" cellspacing="0" style="padding:0px; width:99%;">
    <tr>
        <th class="first" style="width:5%;"><?php echo TABLE_NO; ?></th>
        <th style="width:20%;"><?php echo GENERAL_DESCRIPTION; ?></th>
        <th style="width:20%;"><?php echo REPORT_FROM; ?></th>
        <th style="width:20%;"><?php echo REPORT_TO; ?></th>
        <th style="width:10%;"><?php echo TABLE_DEPARTURE; ?></th>
        <th style="width:10%;"><?php echo "Route Code"; ?></th>
        <th style="width:15%;"><?php echo MENU_BUS; ?> <span class="red">*</span></th>
    </tr>
</table>
<div id="bodyListBusSchedule" class="divListBusSchedule">
    <table id="tblBusSchedule" class="table" cellspacing="0" style="padding:0px;"></table>
</div>
<br />
<div style="width:100%; padding: 0px;" id="footerBusSchedule">
    <div class="buttons">
        <a href="" class="positive btnBackBusSchedule">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div class="buttons">
        <button type="submit" class="positive btnSaveAddBusSchedule" style="display: none;">
            <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
            <span class="txtSaveBusSchedule"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); 
?>