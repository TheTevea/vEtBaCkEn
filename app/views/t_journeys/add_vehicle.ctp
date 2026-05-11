<script type="text/javascript">
    var tblProductFit  = $("#rowVehicleTjourney");
    $(document).ready(function(){
        $("#rowVehicleTjourney").remove();
        // Prevent Key Enter
        preventKeyEnter();
        <?php
        if(empty($tJourneyVehicles)){
        ?>
        addNewVehicle();
        <?php
        }
        ?>
    });
    
    function addNewVehicle(){
        var index = Math.floor((Math.random() * 100000) + 1);
        var tr = tblProductFit.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find(".departureTimeVehicle").attr("id", "departureTimeVehicle"+index);
        tr.find(".vehicleJourney").attr("id", "vehicleJourney"+index).val('');
        tr.find(".vehicleLableJourney").attr("id", "vehicleLableJourney"+index).val('');
        tr.find(".addRowVehicle").show();
        if($(".rowVehicleTjourney").length == 0 || $(".rowVehicleTjourney").length == undefined){
            tr.find(".deleteRowVehicle").hide();
        }else{
            tr.find(".deleteRowVehicle").show();
        }
        $("#dvVehicleTjourney").append(tr);
        eventKeyVehicle();
    }

    function eventKeyVehicle(){
        $(".vehicleJourney, .addRowVehicle, .deleteRowVehicle").unbind("click").unbind("focus").unbind("blur").unbind("change");

        $(".vehicleJourney").change(function(){
            var label = $(this).find("option:selected").html();
            if(label == '<?php echo INPUT_SELECT; ?>'){
                label = '';
            }
            $(this).closest("tr").find(".vehicleLableJourney").val(label);
        });
        
        $(".addRowVehicle").click(function(){
            addNewVehicle();
        });

        $(".deleteRowVehicle").click(function(){
            var currentTr = $(this).closest("tr");
            removeVehicle(currentTr);
        });
    }

    function removeVehicle(currentTr){
        currentTr.remove();
        var tblLength = $(".rowVehicleTjourney").length;
        if(tblLength == 0 || tblLength == undefined){
            addNewVehicle();
        }
    }
</script>
<form id="frmAddVehicle">
<fieldset>
    <legend><?php __(TABLE_ADD_MORE_VEHICLE); ?></legend>
    <table style="width: 800px;" cellpadding="0" class="table">
        <tr>
            <th class="first" style="width: 20%;"><?php echo TABLE_DEPARTURE_TIME; ?></th>
            <th style="width: 35%;"><?php echo TABLE_BOAT; ?></th>
            <th style="width: 35%;"><?php echo TABLE_LABEL; ?></th>
            <th style="width: 10%;"></th>
        </tr>
        <tbody id="dvVehicleTjourney">
            <tr class="rowVehicleTjourney" id="rowVehicleTjourney">
                <td class="first">
                    <div class="inputContainer" style="width: 100%;">
                        <select class="departureTimeVehicle validate[required]" name="data[departure][]" id="departureTimeVehicle" style="width: 90%; height: 30px;">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php 
                            foreach($tDepartureTimes AS $tDepartureTime){
                            ?>
                            <option value="<?php echo $tDepartureTime['TDepartureTime']['id']; ?>"><?php echo $tDepartureTime['TDepartureTime']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <select class="vehicleJourney validate[required]" name="data[vehicle][]" id="vehicleJourney" style="width: 90%; height: 30px;">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php 
                            foreach($tBoats AS $tBoat){
                            ?>
                            <option value="<?php echo $tBoat['TBoat']['id']; ?>"><?php echo $tBoat['TBoat']['code']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <input type="text" class="vehicleLableJourney validate[required]" name="data[label][]" id="vehicleLableJourney" style="width: 90%; height: 25px;" />
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <img alt="Add Row" align="absmiddle" src="<?php echo $this->webroot; ?>img/button/plus.png" class="addRowVehicle" style="cursor: pointer;" onmouseover="Tip('<?php echo ACTION_ADD; ?>')" />
                        <img alt="Delete Row" align="absmiddle" src="<?php echo $this->webroot; ?>img/button/delete.png" class="deleteRowVehicle" style="display: none; cursor: pointer;" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" />
                    </div>
                </td>
            </tr>
            <?php
            foreach($tJourneyVehicles AS $tJourneyVehicle){
            ?>
            <tr class="rowVehicleTjourney">
                <td class="first">
                    <div class="inputContainer" style="width: 100%;">
                        <select class="departureTimeVehicle validate[required]" id="departureTimeVehicle" style="width: 90%; height: 30px;">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php 
                            foreach($tDepartureTimes AS $tDepartureTime){
                            ?>
                            <option <?php if($tJourneyVehicle['TJourneyVehicle']['t_departure_time_id'] == $tDepartureTime['TDepartureTime']['id']){ ?>selected="selected"<?php } ?> value="<?php echo $tDepartureTime['TDepartureTime']['id']; ?>"><?php echo $tDepartureTime['TDepartureTime']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <select class="vehicleJourney validate[required]" id="vehicleJourney" style="width: 90%; height: 30px;">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php 
                            foreach($tBoats AS $tBoat){
                            ?>
                            <option <?php if($tJourneyVehicle['TJourneyVehicle']['t_boat_id'] == $tBoat['TBoat']['id']){ ?>selected="selected"<?php } ?> value="<?php echo $tBoat['TBoat']['id']; ?>"><?php echo $tBoat['TBoat']['code']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <input type="text" value="<?php echo $tJourneyVehicle['TJourneyVehicle']['label']; ?>" class="vehicleLableJourney validate[required]" id="vehicleLableJourney" style="width: 90%; height: 25px;" />
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <img alt="Add Row" align="absmiddle" src="<?php echo $this->webroot; ?>img/button/plus.png" class="addRowVehicle" style="cursor: pointer;" onmouseover="Tip('<?php echo ACTION_ADD; ?>')" />
                        <img alt="Delete Row" align="absmiddle" src="<?php echo $this->webroot; ?>img/button/delete.png" class="deleteRowVehicle" style="display: none; cursor: pointer;" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" />
                    </div>
                </td>
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
</fieldset>
<div style="clear: both;"></div>
</form>