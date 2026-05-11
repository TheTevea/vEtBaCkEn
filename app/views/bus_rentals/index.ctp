<?php
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;

$dataDest = array();
$sqlDest = mysql_query("SELECT * FROM provinces WHERE is_active = 1");
while($rowDest = mysql_fetch_array($sqlDest)){
    $dataDest[$rowDest['id']] = $rowDest['name'];
}
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableBusRental;
    var tabBusRentalId  = $(".ui-tabs-selected a").attr("href");
    var tabBusRentalReg = '';
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".dvBusRentalChosen").chosen({width: 190});
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableBusRental = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/"+$("#busRentalDestFrom").val()+"/"+$("#busRentalDestTo").val()+"/"+$("#busRentalBus").val()+"/<?php echo date("Y-m-d"); ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1 ]
            }]
        });

        $("#busRentalDateFrom").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        });

        $("#busRentalClearDateFrom").click(function(){
            $('#busRentalDateFrom').val('');
        });

        $("#btnRefreshSearchBusRental").unbind("click").click(function(){
            refreshBusRental();
        });

        $("#<?php echo $btnPrint; ?>").click(function(){
            $(".dataTables_length, .dataTables_info").hide();
            $(".dataTables_filter").hide();
            $(".dataTables_paginate").hide();
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
            $(".dataTables_length, .dataTables_info").show();
            $(".dataTables_filter").show();
            $(".dataTables_paginate").show();
        });

        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/bus_retal<?php echo $user['User']['id']; ?>.csv", "_blank");
        });

        function refreshBusRental(){
            var date = 'all';
            if($("#busRentalDateFrom").val() != ""){
                $("#busRentalDateFrom").datepicker("option", "dateFormat", "yy-mm-dd");
                date = $("#busRentalDateFrom").val();
            }
            $("#btnRefreshSearchBusRental").attr("disabled", true);
            var Tablesetting = oTableBusRental.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#busRentalDestFrom").val()+"/"+$("#busRentalDestTo").val()+"/"+$("#busRentalBus").val()+"/"+date;
            oCache.iCacheLower = -1;
            oTableBusRental.fnDraw(false);
            if($("#busRentalDateFrom").val() != ""){
                $("#busRentalDateFrom").datepicker("option", "dateFormat", "dd/mm/yy");
            }
            $("#btnRefreshSearchBusRental").removeAttr("disabled");
            $("#lblRefreshSearchBusRental").html("<?php echo GENERAL_SEARCH; ?>");
        }
    });
</script>
<div class="leftPanel">
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div style="float: left; width: 300px;">
            <div class="buttons">
                <button type="button" id="<?php echo $btnPrint; ?>" class="positive">
                    <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
                    <?php echo ACTION_PRINT; ?>
                </button>
            </div>
            <div class="buttons">
                <button type="button" id="<?php echo $btnExport; ?>" class="positive">
                    <img src="<?php echo $this->webroot; ?>img/button/csv.png" alt=""/>
                    <?php echo ACTION_EXPORT_TO_EXCEL; ?>
                </button>
            </div>
        </div>
        <div style="float: right; width: 1230px;">
            <table cellpadding="0" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="width: 200px;">
                        <label for="tticketFilterDate"><?php echo TABLE_DATE; ?></label> :
                        <input type="text" style="width: 110px; height: 23px; font-size: 12px;" id="busRentalDateFrom" value="<?php echo date("d/m/Y"); ?>" />
                        <img alt="" src="<?php echo $this->webroot; ?>img/button/clear.png" style="cursor: pointer;" onmouseover="Tip('Clear Date')" id="busRentalClearDateFrom" />
                    </td>
                    <td style="width: 115px"><label for="busRentalDestFrom"><?php echo TABLE_DESTINATION_FROM; ?></label> :</td>
                    <td style="width: 200px">
                        <select id="busRentalDestFrom" class="dvBusRentalChosen" style="width: 190px; height: 35px; font-size: 12px;">
                            <option value="all"><?php echo TABLE_ALL; ?></option>
                            <?php
                            foreach($dataDest AS $key => $val){
                            ?>
                            <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td style="width: 110px"><label for="busRentalDestTo"><?php echo TABLE_DESTINATION_TO; ?></label> :</td>
                    <td style="width: 200px;">
                        <select id="busRentalDestTo" class="dvBusRentalChosen" style="width: 190px; height: 35px; font-size: 12px;">
                            <option value="all"><?php echo TABLE_ALL; ?></option>
                            <?php
                            foreach($dataDest AS $key => $val){
                            ?>
                            <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td style="width: 70px"><label for="busRentalBus"><?php echo MENU_BUS_TYPE; ?></label> :</td>
                    <td style="width: 200px;">
                        <select id="busRentalBus" class="dvBusRentalChosen" style="width: 190px; height: 35px; font-size: 12px;">
                            <option value="all"><?php echo TABLE_ALL; ?></option>
                            <?php
                            $sqlBus = mysql_query("SELECT * FROM bus_types WHERE is_active = 1");
                            while($rowBus = mysql_fetch_array($sqlBus)){
                            ?>
                            <option value="<?php echo $rowBus['id']; ?>"><?php echo $rowBus['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <!-- <td style="width: 70px"><label for="busRentalStatus"><?php echo TABLE_STATUS ?></label> :</td>
                    <td style="width: 90px;">
                        <select id="busRentalStatus" style="width: 90px; height: 35px; font-size: 12px;">
                            <option value="all"><?php echo TABLE_ALL; ?></option>
                            <option value="1"><?php echo "Active"; ?></option>
                            <option value="2"><?php echo "Left"; ?></option>
                        </select>
                    </td> -->
                    <td>
                        <div class="buttons" style="float: right;">
                            <button type="button" class="positive" id="btnRefreshSearchBusRental">
                                <img src="<?php echo $this->webroot; ?>img/button/refresh-active.png" alt=""/>
                                <span id="lblRefreshSearchBusRental"><?php echo GENERAL_SEARCH; ?></span>
                            </button>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div style="clear: both;"></div>
    </div>
    <br />
    <div id="<?php echo $printArea; ?>">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_DATE; ?></th>
                    <th><?php echo TABLE_NAME; ?></th>
                    <th><?php echo TABLE_TELEPHONE; ?></th>
                    <th><?php echo TABLE_DESTINATION_FROM; ?></th>
                    <th><?php echo TABLE_DESTINATION_TO; ?></th>
                    <th><?php echo MENU_BUS_TYPE; ?></th>
                    <th><?php echo "Number of Car"; ?></th>
                    <th><?php echo REPORT_FROM; ?></th>
                    <th><?php echo REPORT_TO; ?></th>
                    <!-- <th><?php //echo ACTION_ACTION; ?></th> -->
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="12" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
</div>
<div class="rightPanel"></div>