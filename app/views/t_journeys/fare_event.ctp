<?php
$tblName = "tbl" . rand();
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        oTableTJourneyFareEvent = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/fareEventAjax/<?php echo $this->data['TJourney']['id']; ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnEditTJourneyFareEvent").unbind('click').click(function(event){
                    event.preventDefault();
                    var id  = $(this).attr('rel');
                    var obj = $(this);
                    $.ajax({
                        type: "GET",
                        dataType: "json",
                        url: "<?php echo $this->base; ?>/t_journeys/viewFareEvent/" + id,
                        beforeSend: function(){
                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
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
                            obj.closest('tr').find('.btnEditTJourneyFareEvent').hide();
                            obj.closest('tr').find('.btnDeleteTJourneyFareEvent').hide();
                        },
                        error: function (result) {
                            $("#dialogModal").dialog("close");
                        },
                        success: function(result){
                            $("#dialogModal").dialog("close");
                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            if(result.error == '0'){
                                var id = result.id;
                                var name = result.name;
                                var start = result.start;
                                var end   = result.end;
                                var mon = result.mon;
                                var tue = result.tue;
                                var wed = result.wed;
                                var thu = result.thu;
                                var fri = result.fri;
                                var sat = result.sat;
                                var sun = result.sun;
                                var price = result.price;
                                var foreigner_price = result.foreigner_price;
                                var membership = result.membership;
                                $("input[name='data[TJourneyFareEvent][id]']").val(id);
                                $("#TJourneyFareEventName").val(name);
                                $("#TJourneyFareEventStart").val(start);
                                $("#TJourneyFareEventEnd").val(end);
                                $("#TJourneyFareEventPrice").val(price);
                                $("#TJourneyFareEventForeignerPrice").val(foreigner_price);
                                $("#TJourneyFareEventMembership").val(membership);
                                $("#TJourneyFareEventStart").datepicker( "option", "maxDate", end );
                                $("#TJourneyFareEventEnd").datepicker( "option", "minDate", start );
                                // Schedule
                                $("input[name='data[TJourneyFareEvent][mon]']").val(mon);
                                $("input[name='data[TJourneyFareEvent][tue]']").val(tue);
                                $("input[name='data[TJourneyFareEvent][wed]']").val(wed);
                                $("input[name='data[TJourneyFareEvent][thu]']").val(thu);
                                $("input[name='data[TJourneyFareEvent][fri]']").val(fri);
                                $("input[name='data[TJourneyFareEvent][sat]']").val(sat);
                                $("input[name='data[TJourneyFareEvent][sun]']").val(sun);
                                checkBoxTjourneyFareEvent(mon, $("#FareEventchkMon"));
                                checkBoxTjourneyFareEvent(tue, $("#FareEventchkTue"));
                                checkBoxTjourneyFareEvent(wed, $("#FareEventchkWed"));
                                checkBoxTjourneyFareEvent(thu, $("#FareEventchkThu"));
                                checkBoxTjourneyFareEvent(fri, $("#FareEventchkFri"));
                                checkBoxTjourneyFareEvent(sat, $("#FareEventchkSat"));
                                checkBoxTjourneyFareEvent(sun, $("#FareEventchkSun"));
                            } else {
                                obj.closest('tr').find('.btnEditTJourneyFareEvent').show();
                                obj.closest('tr').find('.btnDeleteTJourneyFareEvent').show();
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_INVALID; ?></p>');
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
                        }
                    });
                });
                // Delete
                $(".btnDeleteTJourneyFareEvent").unbind('click').click(function(event){
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
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/deleteFareEvent/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
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
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        $("#dialogModal").dialog("close");
                                        oCache.iCacheLower = -1;
                                        oTableTJourneyFareEvent.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                            createSysAct('TJourney', 'Delete', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('TJourney', 'Delete', 1, '');
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
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,1,2,3,4,5,6 ]
            }]
        });
        $("#TJourneyFareEventForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TJourneyFareEventForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $("#TJourneyFareEventStart, #TJourneyFareEventEnd").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTJourneyFareEvent").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
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
                    },
                    buttons: {

                    }
                });
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("#dialogModal").dialog("close");
                $(".txtSaveTJourneyFareEvent").html("<?php echo ACTION_SAVE; ?>").removeAttr('disabled');
                oCache.iCacheLower = -1;
                oTableTJourneyFareEvent.fnDraw(false);
                // Reset Form
                document.getElementById("TJourneyFareEventForm").reset();
                $("input[name='data[TJourneyFareEvent][t_journey_id]").val('<?php echo $this->data['TJourney']['id']; ?>');
                $("input[name='data[TJourneyFareEvent][id]']").val('');
                $("#TJourneyFareEventStart, #TJourneyFareEventEnd").datepicker("option", "dateFormat", "dd/mm/yy");
                $("#TJourneyFareEventStart").datepicker( "option", "maxDate", null );
                $("#TJourneyFareEventStart").datepicker( "option", "minDate", null );
                $("#TJourneyFareEventEnd").datepicker( "option", "maxDate", null );
                $("#TJourneyFareEventEnd").datepicker( "option", "minDate", null );
                // Schedule
                $("input[name='data[TJourneyFareEvent][mon]']").val('1');
                $("input[name='data[TJourneyFareEvent][tue]']").val('1');
                $("input[name='data[TJourneyFareEvent][wed]']").val('1');
                $("input[name='data[TJourneyFareEvent][thu]']").val('1');
                $("input[name='data[TJourneyFareEvent][fri]']").val('1');
                $("input[name='data[TJourneyFareEvent][sat]']").val('1');
                $("input[name='data[TJourneyFareEvent][sun]']").val('1');
                checkBoxTjourneyFareEvent('1', $("#FareEventchkMon"));
                checkBoxTjourneyFareEvent('1', $("#FareEventchkTue"));
                checkBoxTjourneyFareEvent('1', $("#FareEventchkWed"));
                checkBoxTjourneyFareEvent('1', $("#FareEventchkThu"));
                checkBoxTjourneyFareEvent('1', $("#FareEventchkFri"));
                checkBoxTjourneyFareEvent('1', $("#FareEventchkSat"));
                checkBoxTjourneyFareEvent('1', $("#FareEventchkSun"));
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Journey Fare Event', 'Add/Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Journey Fare Event', 'Add/Edit', 1, '');
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
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
        
        $(".btnBackTJourney").unbind('click').click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTJourney.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        var dates = $("#TJourneyFareEventStart, #TJourneyFareEventEnd").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "TJourneyFareEventStart" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
            }
        });
        
        $("#FareEventchkMon, #FareEventchkTue, #FareEventchkWed, #FareEventchkThu, #FareEventchkFri, #FareEventchkSat, #FareEventchkSun").unbind('click').click(function(){
            var id = $(this).attr('id');
            var input = '';
            if(id == 'FareEventchkMon'){
                input = $("input[name='data[TJourneyFareEvent][mon]']");
            } else if (id == 'FareEventchkTue'){
                input = $("input[name='data[TJourneyFareEvent][tue]']");
            } else if (id == 'FareEventchkWed'){
                input = $("input[name='data[TJourneyFareEvent][wed]']");
            } else if (id == 'FareEventchkThu'){
                input = $("input[name='data[TJourneyFareEvent][thu]']");
            } else if (id == 'FareEventchkFri'){
                input = $("input[name='data[TJourneyFareEvent][fri]']");
            } else if (id == 'FareEventchkSat'){
                input = $("input[name='data[TJourneyFareEvent][sat]']");
            } else if (id == 'FareEventchkSun'){
                input = $("input[name='data[TJourneyFareEvent][sun]']");
            }
            if($(this).is(':checked')){
                input.val('1');
            } else {
                input.val('0');
            }
        });
    });
    
    function checkBoxTjourneyFareEvent(val, obj){
        if(val == '1'){
            obj.attr('checked', true);
        } else {
            obj.attr('checked', false);
        }
    }
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
<fieldset>
    <legend><?php __(MENU_JOURNEY_INFO); ?></legend>
        <table style="width: 100%;" cellpadding="5">
            <tr>
                <th style="width:10%;"><?php __(MENU_COMPANY_MANAGEMENT); ?> :</th>
                <td>
                    <?php echo $this->data['Company']['name']; ?>
                </td>
                <th style="width:10%;"><?php __(MENU_BRANCH); ?> :</th>
                <td>
                    <?php echo $this->data['Branch']['name']; ?>
                </td>
                <th style="width:13%;"><?php __(GENERAL_DESCRIPTION); ?> :</th>
                <td>
                    <?php echo $this->data['TJourney']['description']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_DESTINATION_FROM); ?> :</th>
                <td>
                    <?php echo $this->data['TDestination']['name']; ?>
                </td>
                <th><?php __(TABLE_DESTINATION_TO); ?> :</th>
                <td>
                    <?php 
                    $sqlTo = mysql_query("SELECT name FROM t_destinations WHERE id = ".$this->data['TJourney']['t_destination_to_id']);
                    if(mysql_num_rows($sqlTo)){
                        $rowTo = mysql_fetch_array($sqlTo);
                        echo $rowTo[0]; 
                    }
                    ?>
                </td>
                <th><?php __(MENU_TRANSPORTATION_TYPE); ?> :</th>
                <td>
                    <?php echo $this->data['TTransportationType']['name']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_PRICE); ?> :</th>
                <td>
                    <?php echo number_format($this->data['TJourney']['unit_price'], 2)." ".$this->data['CurrencyCenter']['symbol']; ?>
                </td>
                <th><?php __(TABLE_FOREIGNER_PRICE); ?> :</th>
                <td>
                    <?php echo number_format($this->data['TJourney']['foreigner_price'], 2)." ".$this->data['CurrencyCenter']['symbol']; ?>
                </td>
                <th><?php __(TABLE_VIP_CARD); ?> :</th>
                <td colspan="3">
                    <?php echo number_format($this->data['TJourney']['membership'], 2)." ".$this->data['CurrencyCenter']['symbol']; ?>
                </td>
            </tr>
        </table>
 </fieldset>
<fieldset style="width: 30%; float: left;">
    <legend><?php __(TABLE_EVENT_INFORMATION); ?></legend>
    <form id="TJourneyFareEventForm" method="post" action="<?php echo $this->base . '/' . $this->params['controller']; ?>/saveFareEvent" accept-charset="utf-8">
        <input type="hidden" name="data[TJourneyFareEvent][t_journey_id]" value="<?php echo $this->data['TJourney']['id']; ?>" />
        <input type="hidden" name="data[TJourneyFareEvent][id]" />
        <table style="width: 100%;" cellpadding="5">
            <tr>
                <td><label for="TJourneyFareEventName"><?php echo TABLE_EVENT_NAME; ?></label> <span class="red">*</span> :</td>
                <td>
                    <input type="hidden" name="data[TJourneyFareEvent][mon]" value="1" />
                    <input type="hidden" name="data[TJourneyFareEvent][tue]" value="1" />
                    <input type="hidden" name="data[TJourneyFareEvent][wed]" value="1" />
                    <input type="hidden" name="data[TJourneyFareEvent][thu]" value="1" />
                    <input type="hidden" name="data[TJourneyFareEvent][fri]" value="1" />
                    <input type="hidden" name="data[TJourneyFareEvent][sat]" value="1" />
                    <input type="hidden" name="data[TJourneyFareEvent][sun]" value="1" />
                    <div class="inputContainer" style="width: 100%;">
                        <input type="text" name="data[TJourneyFareEvent][name]" id="TJourneyFareEventName" class="validate[required]" style="width: 200px; height: 20px;" />
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="TJourneyFareEventStart"><?php echo TABLE_START_DATE; ?></label> <span class="red">*</span> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <input type="text" name="data[TJourneyFareEvent][start]" id="TJourneyFareEventStart" class="validate[required]" style="width: 200px; height: 20px;" />
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="TJourneyFareEventEnd"><?php echo TABLE_END_DATE; ?></label> <span class="red">*</span> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <input type="text" name="data[TJourneyFareEvent][end]" id="TJourneyFareEventEnd" class="validate[required]" style="width: 200px; height: 20px;" />
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="inputContainer" style="width: 100%;">
                        <input type="checkbox" id="FareEventchkMon" name="checkEvent" class="validate[maxCheckbox[2]]" value="" checked="" /> <label for="FareEventchkMon">Mon</label>
                        <input type="checkbox" id="FareEventchkTue" name="checkEvent" class="validate[maxCheckbox[2]]" value="" checked="" /> <label for="FareEventchkTue">Tue</label>
                        <input type="checkbox" id="FareEventchkWed" name="checkEvent" class="validate[maxCheckbox[2]]" value="" checked="" /> <label for="FareEventchkWed">Wed</label>
                        <input type="checkbox" id="FareEventchkThu" name="checkEvent" class="validate[maxCheckbox[2]]" value="" checked="" /> <label for="FareEventchkThu">Thu</label>
                        <input type="checkbox" id="FareEventchkFri" name="checkEvent" class="validate[maxCheckbox[2]]" value="" checked="" /> <label for="FareEventchkFri">Fri</label>
                        <input type="checkbox" id="FareEventchkSat" name="checkEvent" class="validate[maxCheckbox[2]]" value="" checked="" /> <label for="FareEventchkSat">Sat</label>
                        <input type="checkbox" id="FareEventchkSun" name="checkEvent" class="validate[maxCheckbox[2]]" value="" checked="" /> <label for="FareEventchkSun">Sun</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="TJourneyFareEventPrice"><?php echo TABLE_PRICE; ?></label> <span class="red">*</span> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <input type="text" name="data[TJourneyFareEvent][price]" id="TJourneyFareEventPrice" class="float validate[required]" style="width: 200px; height: 20px;" /> $
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="TJourneyFareEventForeignerPrice"><?php echo TABLE_FOREIGNER_PRICE; ?></label> <span class="red">*</span> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <input type="text" name="data[TJourneyFareEvent][foreigner_price]" id="TJourneyFareEventForeignerPrice" class="float validate[required]" style="width: 200px; height: 20px;" /> $
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="TJourneyFareEventMembership"><?php echo TABLE_VIP_CARD; ?></label> <span class="red">*</span> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <input type="text" name="data[TJourneyFareEvent][membership]" id="TJourneyFareEventMembership" class="float validate[required]" style="width: 200px; height: 20px;" /> $
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="buttons">
                        <button type="submit" class="positive btnSaveTJourneyFareEvent">
                            <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                            <span class="txtSaveTJourneyFareEvent"><?php echo ACTION_SAVE; ?></span>
                        </button>
                    </div>
                </td>
            </tr>
        </table>
    </form>
</fieldset>
<fieldset style="width: 65%; float: right;">
    <legend><?php __(TABLE_EVENT_HISTORY); ?></legend>
    <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
        <thead>
            <tr>
                <th class="first" style="width: 200px !important;"><?php echo TABLE_EVENT_NAME; ?></th>
                <th style="width: 110px !important;"><?php echo REPORT_FROM; ?></th>
                <th style="width: 110px !important;"><?php echo REPORT_TO; ?></th>
                <th><?php echo TABLE_SCHEDULE; ?></th>
                <th style="width: 90px !important;"><?php echo TABLE_PRICE; ?></th>
                <th style="width: 100px !important;"><?php echo 'Fgn Price'; ?></th>
                <th style="width: 90px !important;"><?php echo 'VIP'; ?></th>
                <th style="width: 90px !important;"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="8" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
            </tr>
        </tbody>
    </table>
</fieldset>
<div style="clear: both;"></div>