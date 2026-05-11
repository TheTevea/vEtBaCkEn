<?php
// Authentication
$this->element('check_access');
$allowAdd = checkAccess($user['User']['id'], $this->params['controller'], 'add');
?>
<script type="text/javascript">
    var rowTableSeatProtect  = $("#rowListSeatProtect");
    $(document).ready(function(){
        $("#rowListSeatProtect").remove();
        $(".btnBackTTransportationType").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTTransportationType.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        <?php
        if($allowAdd){
        ?>
        $(".btnAddSeatProtect").unbind("click").click(function(event){
            event.preventDefault();
            var lblSeat1 = "";
            var valSeat1 = "";
            var lblSeat2 = "";
            var valSeat2 = "";
            var i = 0;
            $(".transportationSeatCheck").each(function(){
                if($(this).is(":checked")){
                    if($(this).attr("disabled") == undefined){
                        if(i == 0){
                            lblSeat1 = $(this).attr("lbl");
                            valSeat1 = $(this).attr("value");
                        } else {
                            lblSeat2 = $(this).attr("lbl");
                            valSeat2 = $(this).attr("value");
                        }
                        i++;
                    }
                }
            });
            if(i == 2){
                if(lblSeat1 != "" && valSeat1 != "" && lblSeat2 != "" && valSeat2 != ""){
                    $.ajax({
                        dataType: "json",
                        type: "POST",
                        url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/saveSeatProtectGender",
                        data: "data[t_transportation_type_id]=<?php echo $this->data['TTransportationType']['id']; ?>&data[seat1_number]="+valSeat1+"&data[seat1_lbl]="+lblSeat1+"&data[seat2_number]="+valSeat2+"&data[seat2_lbl]="+lblSeat2,
                        beforeSend: function(){
                            $(".btnAddSeatProtect").attr("disabled", true);
                            $("#lblAddSeatProtect").text("<?php echo ACTION_LOADING; ?>");
                        },
                        success: function(result){
                            $(".btnAddSeatProtect").attr("disabled", false);
                            $("#lblAddSeatProtect").text("Add Seat Protect");
                            if(result.status == "1"){
                                cloneSeatProtect(result.id, lblSeat1, lblSeat2);
                            } else {
                                alert("Add failed");
                            }
                        }
                    });
                }
            } else {
                alert("Please select two seats to protect");
            }
        });
        eventKeySeatProtect()
        <?php
        }
        ?>
    });
    <?php
    if($allowAdd){
    ?>
    function cloneSeatProtect(seatProtectId, seatLbl1, seatLbl2){
        var tr = rowTableSeatProtect.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .seatProtectId").val(seatProtectId);
        tr.find("td .lblSeat1").text(seatLbl1);
        tr.find("td .lblSeat2").text(seatLbl2);
        $("#tbSeatProtect").append(tr);
        $(".transportationSeatCheck[lbl='"+seatLbl1+"']").attr("disabled", true);
        $(".transportationSeatCheck[lbl='"+seatLbl2+"']").attr("disabled", true);
        eventKeySeatProtect();
    }

    function eventKeySeatProtect(){
        $(".btnRemoveRowSeatProtect").unbind('click').unbind('change');
        $(".btnRemoveRowSeatProtect").click(function(){
            var obj = $(this);
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete the selected item(s)?</p>');
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
                        var id = obj.closest("tr").find(".seatProtectId").val();
                        var lblSeat1 = obj.closest("tr").find(".lblSeat1").text();
                        var lblSeat2 = obj.closest("tr").find(".lblSeat2").text();
                        $(".transportationSeatCheck[lbl='"+lblSeat1+"']").attr("disabled", false);
                        $(".transportationSeatCheck[lbl='"+lblSeat2+"']").attr("disabled", false);
                        $(".transportationSeatCheck[lbl='"+lblSeat1+"']").attr("checked", false);
                        $(".transportationSeatCheck[lbl='"+lblSeat2+"']").attr("checked", false);
                        $.ajax({
                            dataType: "json",
                            type: "POST",
                            url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/deleteSeatProtectGender/"+id,
                            beforeSend: function(){
                                obj.closest("tr").hide();
                            },
                            success: function(result){
                                if(result.status == "1"){
                                    obj.closest("tr").remove();
                                } else {
                                    obj.closest("tr").show();
                                    alert("Remove seat failed");
                                }
                            }
                        });
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    <?php
    }
    ?>
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTTransportationType">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<div style="float: left; width: 49%;">
    <fieldset style="height: 120px;">
        <legend><?php __(MENU_TRANSPORTATION_TYPE_INFO); ?></legend>
        <table width="100%" cellpadding="5">
            <tr>
                <td rowspan="4" style="width: 160px;">
                    <?php
                    $img = "";
                    if(!empty($this->data['TTransportationType']['photo'])){
                        $img = $this->data['TTransportationType']['photo_path'].$this->webroot."public/transportation_type/".$this->data['TTransportationType']['photo'];
                    }
                    ?>
                    <img src="<?php echo $img; ?>" style="width: 150px; height: 100px;" />
                </td>
                <th style="width: 10%; font-size: 12px;"><?php __(TABLE_NAME); ?></th>
                <td style="font-size: 12px;"><?php echo $this->data['TTransportationType']['name']; ?></td>
            </tr>
            <tr>
                <th style="width: 10%; font-size: 12px;"><?php __(TABLE_TOTAL_SEAT); ?></th>
                <td style="font-size: 12px;"><?php echo $this->data['TTransportationType']['number_of_seat']; ?></td>
            </tr>
            <tr>
                <th style="width: 10%; font-size: 12px;"><?php __("Seat Type"); ?></th>
                <td style="font-size: 12px;">
                    <?php 
                    if($this->data['TTransportationType']['seat_type'] == 1) {
                        echo "Sitting";
                    } else if($this->data['TTransportationType']['seat_type'] == 2) {
                        echo "Sleeping";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th style="width: 10%; font-size: 12px;"><?php __(GENERAL_DESCRIPTION); ?></th>
                <td style="font-size: 12px;"><?php echo nl2br($this->data['TTransportationType']['description']); ?></td>
            </tr>
        </table>
    </fieldset>
    <br/>
    <fieldset>
        <legend><?php __('Seat Protect Gender'); ?></legend>
        <table cellpadding="0" cellspacing="0" style="width: 100%;" class="table">
            <tr>
                <th class="first" style="width: 33%;">Seat1 #</th>
                <th>Seat2 #</th>
                <th>Action</th>
            </tr>
            <tbody id="tbSeatProtect">
                <tr id="rowListSeatProtect" class="rowListSeatProtect">
                    <td class="first">
                        <input type="hidden" class="seatProtectId" />
                        <span class="lblSeat1"></span>
                    </td>
                    <td>
                        <span class="lblSeat2"></span>
                    </td>
                    <td style="height: 30px;">
                        <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowSeatProtect" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                    </td>
                </tr>
                <?php
                $seatProtect = array();
                $sqlSeatProtect = mysql_query("SELECT * FROM seat_protect_genders WHERE t_transportation_type_id = ".$this->data['TTransportationType']['id']);
                while($rowSeatProtect = mysql_fetch_array($sqlSeatProtect)){
                    $seatProtect[$rowSeatProtect['seat1_number']] = 1;
                    $seatProtect[$rowSeatProtect['seat2_number']] = 1;
                ?>
                <tr class="rowListSeatProtect">
                    <td class="first">
                        <input type="hidden" class="seatProtectId" value="<?php echo $rowSeatProtect['id']; ?>" />
                        <span class="lblSeat1"><?php echo $rowSeatProtect['seat1_lbl']; ?></span>
                    </td>
                    <td>
                        <span class="lblSeat2"><?php echo $rowSeatProtect['seat2_lbl']; ?></span>
                    </td>
                    <td style="height: 30px;">
                        <?php
                        if($allowAdd){
                        ?>
                        <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRowSeatProtect" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                        <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </fieldset> 
</div>
<div style="float: right; width: 49%;">
    <fieldset style="min-height: 120px;">
        <legend><?php __("Other Phto"); ?></legend>
        <table width="100%" cellpadding="5">
            <tr>
                <td valign="top">
                    <?php
                    $sqlOtherPhoto = mysql_query("SELECT * FROM t_transportation_type_photos WHERE t_transportation_type_id = ".$this->data['TTransportationType']['id']);
                    while($rowOtherPhoto = mysql_fetch_array($sqlOtherPhoto)){
                    ?>
                    <div style="float: left; width: 100px; margin-left: 3px; margin-bottom: 3px;">
                        <img src="<?php echo $rowOtherPhoto['photo_path'].$this->webroot; ?>public/transportation_type/<?php echo $rowOtherPhoto['photo']; ?>" style="width: 100px; height: 65px;" />
                    </div>
                    <?php
                    }
                    ?>
                </td>
            </tr>
        </table>
    </fieldset>
    <br/>
    <fieldset>
        <legend>Layout</legend>
        <?php
        $layouts  = json_decode($this->data['TTransportationType']['layout'], true);
        $tableLayout = '';
        $seatImg     = 'seat-sitting-32.png';
        $tableWidth  = 32;
        $tableHeight = 32;
        $seatChkMargin = 10;
        if($this->data['TTransportationType']['seat_type'] == 2){
            $seatImg = 'seat-sleeper-32.png';
            $tableHeight = 60;
            $seatChkMargin = 25;
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
                    $tableLayout .= '<td '.$attrCol.' style="height: '.$tableHeight.'px; width: '.$tableWidth.'px; text-align: center; vertical-align: middle; font-size: 10px;">';
                    if (array_key_exists($value, $seatProtect)){
                        $tableLayout .= '<div style="width: '.$tableWidth.'px; height: '.$tableHeight.'px; background: url(../img/button/'.$seatImg.') center no-repeat;"><input type="checkbox" disabled="" checked="" lbl="'.$label.'" class="transportationSeatCheck" value="'.$value.'" style="cursor: pointer; margin-top: '.$seatChkMargin.'px;" /></div>'.$label;
                    } else {
                        $tableLayout .= '<div style="width: '.$tableWidth.'px; height: '.$tableHeight.'px; background: url(../img/button/'.$seatImg.') center no-repeat;"><input type="checkbox" lbl="'.$label.'" class="transportationSeatCheck" value="'.$value.'" style="cursor: pointer; margin-top: '.$seatChkMargin.'px;" /></div>'.$label;
                    }
                } else {
                    $tableLayout .= '<td '.$attrCol.' style="height: '.$tableHeight.'px; width: '.$tableWidth.'px; text-align: center; vertical-align: middle;">';
                    if($label == 'Open1' || $label == 'Open2') {
                        $tableLayout .= '<span style="font-size: 11px;">Open Air Seat</span>';
                    } else if($label == 'Capitain'){
                        $tableLayout .= '<img src="'.$this->webroot.'img/button/captain.png" alt="" style="width: 24px;" />';
                    } else if($label == 'Hostess'){
                        $tableLayout .= '<img src="'.$this->webroot.'img/button/hostess.png" alt="" style="width: 32px;" />';
                    // } else if($label == 'Toilet'){
                    //     $tableLayout .= '<span style="font-size: 10px;">WC</span>';
                    } else {
                        $tableLayout .= '<span style="font-size: 11px;">'.$label.'</span>';
                    }
                }
                $tableLayout .= '</td>';
            }
            $tableLayout .= '</tr>';
        }
        $totalTableWeight = $tableWidth * $totalCol;
        if($allowAdd){
        ?>
        <div class="buttons">
            <a href="" class="positive btnAddSeatProtect">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <span id="lblAddSeatProtect"><?php echo 'Add Seat Protect'; ?></span>
            </a>
        </div>
        <?php
        }
        ?>
        <div style="clear: both;"></div>
        <div style="width: 64%; margin-top: 10px; float: left;">
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
    </fieldset>
</div>