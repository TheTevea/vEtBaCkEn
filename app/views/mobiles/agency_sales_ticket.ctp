<?php 
$rnd      = rand();
$frmName  = "frm" . $rnd;
$dueDate  = "dueDate" . $rnd;
$dateFrom = "dateFrom" . $rnd;
$dateTo   = "dateTo" . $rnd;
$travelFrom = "travelFrom" . $rnd;
$travelTo   = "travelTo" . $rnd;
$agent      = "agent" . $rnd;
$divWarn    = "alerWarn" . $rnd;
$btnSearchLabel = "txtBtnSearch". $rnd;
$btnSearch   = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$result      = "result" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>, #<?php echo $travelFrom; ?>, #<?php echo $travelTo; ?>").val("");
        var dates = $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "<?php echo $dateFrom; ?>" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
                $("#<?php echo $travelFrom; ?>, #<?php echo $travelTo; ?>").val("");
            }
        });

        var dateFroms = $("#<?php echo $travelFrom; ?>, #<?php echo $travelTo; ?>").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "<?php echo $travelFrom; ?>" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dateFroms.not( this ).datepicker( "option", option, date );
                $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").val("");
            }
        });

        $("#<?php echo $btnSearch; ?>").click(function(){
            var dateFrom   = $("#<?php echo $dateFrom; ?>").val();
            var dateTo     = $("#<?php echo $dateTo; ?>").val();
            var travelFrom = $("#<?php echo $travelFrom; ?>").val();
            var travelTo   = $("#<?php echo $travelTo; ?>").val();
            var agency     = $("#<?php echo $agent; ?>").val();
            if(((dateFrom != "" && dateTo != "") || (travelFrom != "" && travelTo != "")) && agency != ""){
                $("#<?php echo $divWarn; ?>").hide();
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/agencySalesTicketResult",
                    data: $("#<?php echo $frmName; ?>").serialize(),
                    beforeSend: function(){
                        $("#<?php echo $btnSearch; ?>").attr("disabled", true);
                        $("#<?php echo $btnSearchLabel; ?>").html("<?php echo 'កំពុងដំណើរការ'; ?>");
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                    },
                    success: function(result){
                        $("#<?php echo $btnSearch; ?>").removeAttr("disabled");
                        $("#<?php echo $btnSearchLabel; ?>").html("<?php echo 'ស្វែងរក'; ?>");
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                        $("#<?php echo $result; ?>").html(result);
                    }
                });
            } else {
                $("#<?php echo $divWarn; ?>").show();
            }
        });
    });
</script>
<div style="width: 99%; margin: 0px auto; height: 800px; background: #fff; position: relative;">
    <form id="<?php echo $frmName; ?>" action="" method="post" style="width: 100%; ">
        <div class="legend" style="border-bottom: 1px solid #274a60; width: 100%;" id="headReport">
            <div class="legend_content <?php echo $formFilter; ?>" style="border: none;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 90px;"><label for="<?php echo $dateFrom; ?>" style="font-size: 14px;"><?php echo 'ថ្ងៃកក់'; ?>:</label></td>
                        <td style="width: 120px;">
                            <div class="inputContainer">
                                <input type="text" id="<?php echo $dateFrom; ?>" name="date_from" style="height: 28px;" />
                            </div>
                        </td>
                        <td style="width: 20px; text-align: center;"><?php echo '-'; ?></td>
                        <td style="width: 120px;">
                            <div class="inputContainer">
                                <input type="text" id="<?php echo $dateTo; ?>" name="date_to" style="height: 28px;" />
                            </div>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="width: 90px;"><label for="<?php echo $travelFrom; ?>" style="font-size: 14px;"><?php echo 'ថ្ងៃធ្វើដំណើរ'; ?>:</label></td>
                        <td style="width: 120px;">
                            <div class="inputContainer">
                                <input type="text" id="<?php echo $travelFrom; ?>" name="traval_from" style="height: 28px;" />
                            </div>
                        </td>
                        <td style="width: 20px; text-align: center;"><?php echo '-'; ?></td>
                        <td style="width: 120px;">
                            <div class="inputContainer">
                                <input type="text" id="<?php echo $travelTo; ?>" name="traval_to" style="height: 28px;" />
                            </div>
                        </td>
                        <td></td>
                    </tr>
                </table>
            </div>
            <div class="legend_content <?php echo $formFilter; ?>" style="border: none;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 90px;"><label for="<?php echo $agent; ?>" style="font-size: 14px;"><?php echo 'ភ្នាក់ងារ'; ?>:</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php 
                                $empty = false;
                                if(COUNT($agents) > 1){
                                    $empty = INPUT_SELECT;
                                }
                                echo $this->Form->select($agent, $agents, null, array('escape' => false, 'name' => 'branch', 'empty' => $empty, 'style' => 'height: 40px; width: 270px')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center; padding-top: 10px;">
                            <div class="buttons" style="margin: 0px auto; float: none; width: 100%;">
                                <button type="button" id="<?php echo $btnSearch; ?>" class="positive" style="width: 98%; height: 50px; background-color: #f48539; float: none; margin: 0px auto;">
                                    <span id="<?php echo $btnSearchLabel; ?>" style="color: #fff; font-size: 18px;"><?php echo 'ស្វែងរក'; ?></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </form>
    <div id="<?php echo $divWarn; ?>" style="font-size: 14px; text-align: center; color: red; padding-top: 10px; display: none;">សូមជ្រើសរើស ថ្ងៃកក់នឹងភ្នាក់ងារ</div>
    <div class="leftPanel" id="<?php echo $result; ?>" style="width: 100%;"></div>
    <div class="rightPanel"></div>
</div>