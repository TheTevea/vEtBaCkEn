<?php
// Authentication
$this->element('check_access');
$allowAdd=checkAccess($user['User']['id'], $this->params['controller'], 'add');

$dataDest = array();
$sqlDest = mysql_query("SELECT * FROM t_destinations WHERE offline_project_id = 1 AND is_active = 1");
while($rowDest = mysql_fetch_array($sqlDest)){
    $dataDest[$rowDest['id']] = $rowDest['name'];
}
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableBusSchedule;
    var tabBusScheduleId  = $(".ui-tabs-selected a").attr("href");
    var tabBusScheduleReg = '';
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".dvBusScheduleChosen").chosen({width: 190});
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableBusSchedule = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewBusSchedule").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/" + id);
                });

                $(".btnEditBusSchedule").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/" + id);
                });
                
                $(".btnDeleteBusSchedule").click(function(event){
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
                            '<?php echo ACTION_DELETE; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/delete/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableBusSchedule.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                            createSysAct('BusSchedule', 'Delete', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('BusSchedule', 'Delete', 1, '');
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

                $(".btnUpdateNoteBusSchedule").unbind("click").click(function(event){
                    event.preventDefault();
                    var id   = $(this).attr('rel');
                    var note = $(this).attr('note').replace(/{dblquote}/g,'"');
                    $("#dialog").html("<textarea style='width:350px; height: 200px;' id='NoteBusSchedule'>"+note+"</textarea>").dialog({
                        title: '<?php echo TABLE_NOTE; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                $.ajax({
                                    type: "POST",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/updateNote/" + id,
                                    data: "note="+ $("#NoteBusSchedule").val(),
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableBusSchedule.fnDraw(false);
                                    }
                                });
                            }
                        }
                    });
                });

                $(".btnUpdateDelayTimeBusSchedule").unbind("click").click(function(event){
                    event.preventDefault();
                    var id   = $(this).attr('rel');
                    $("#dialog").html('<select style="width:120px; height: 30px;" id="DelayTimeBusSchedule"><option value="5">05</option><option value="10">10</option><option value="15">15</option><option value="20">20</option><option value="25">25</option><option value="30">30</option><option value="45">45</option><option value="60">60</option></select>').dialog({
                        title: '<?php echo ACTION_UPDATE_DELAY; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                $.ajax({
                                    type: "POST",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/updateDelay/" + id + "/" + $("#DelayTimeBusSchedule").val(),
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableBusSchedule.fnDraw(false);
                                    }
                                });
                            }
                        }
                    });
                });

                $(".btnUpdateBusBusSchedule").unbind("click").click(function(event){
                    event.preventDefault();
                    var id   = $(this).attr('rel');
                    $("#dialog").html('<div style="z-index: 999999;"><select style="width:240px; height: 30px;" id="BusScheduleChangeBus">'+$("#busScheduleBus").html()+'</select></div>').dialog({
                        title: '<?php echo "Update Bus Schedule"; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 300,
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $("#BusScheduleChangeBus").chosen({width: 240});
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                if($("#BusScheduleChangeBus").val() != "all"){
                                    $.ajax({
                                        type: "POST",
                                        url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/updateBus/" + id + "/" + $("#BusScheduleChangeBus").val(),
                                        data: "",
                                        beforeSend: function(){
                                            $("#dialog").dialog("close");
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                        },
                                        success: function(result){
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            oCache.iCacheLower = -1;
                                            oTableBusSchedule.fnDraw(false);
                                        }
                                    });
                                }
                            },
                            '<?php echo ACTION_CANCEL; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                });

                $(".BusScheduleLeave").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('data');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo "Do you want to update status left for "; ?> <b>' + name + '</b>?</p>');
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
                            '<?php echo ACTION_YES; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/updateLeave/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableBusSchedule.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                            createSysAct('BusSchedule', 'Delete', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('BusSchedule', 'Delete', 1, '');
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

                $(".btnCloseBusSchedule").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo "Do you want to close for "; ?> <b>' + name + '</b>?</p>');
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
                            '<?php echo ACTION_YES; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/closeSchedule/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableBusSchedule.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                            createSysAct('BusSchedule', 'Close', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('BusSchedule', 'Close', 1, '');
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
                "bSortable": false, "aTargets": [ 0,-1 ]
            }],
            "aaSorting": [[ 2, "asc" ]]
        });

        $("#btnRefreshSearchBusSchedule").unbind("click").click(function(){
            refreshBusSchedule();
        });

        $(".btnAddBusSchedule").click(function(event){
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
        });

        $("#busScheduleFilterDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: 0
        });

        function refreshBusSchedule(){
            $("#btnRefreshSearchBusSchedule").attr("disabled", true);
            var date = $("#busScheduleFilterDate").val().split("/")[2]+"-"+$("#busScheduleFilterDate").val().split("/")[1]+"-"+$("#busScheduleFilterDate").val().split("/")[0];
            var Tablesetting = oTableBusSchedule.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#busScheduleDestFrom").val()+"/"+$("#busScheduleDestTo").val()+"/"+$("#busScheduleBus").val()+"/"+$("#busScheduleStatus").val()+"/"+date;
            oCache.iCacheLower = -1;
            oTableBusSchedule.fnDraw(false);
            $("#btnRefreshSearchBusSchedule").removeAttr("disabled");
            $("#lblRefreshSearchBusSchedule").html("<?php echo GENERAL_SEARCH; ?>");
        }
    });
</script>
<div class="leftPanel">
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <?php if($allowAdd){ ?>
        <div class="buttons">
            <a href="" class="positive btnAddBusSchedule">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_JOURNEY_BUS_ADD; ?>
            </a>
        </div>
        <?php } ?>
        <div style="float: right; width: 1380px;">
            <table cellpadding="0" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="width: 60px"><label for="busScheduleFilterDate"><?php echo TABLE_DATE; ?></label> :</td>
                    <td style="width: 110px">
                        <input type="text" id="busScheduleFilterDate" autocomplete="off" style="width: 100px; height: 25px;" value="<?php echo date("d/m/Y"); ?>" />
                    </td>
                    <td style="width: 115px"><label for="busScheduleDestFrom"><?php echo TABLE_DESTINATION_FROM; ?></label> :</td>
                    <td style="width: 200px">
                        <select id="busScheduleDestFrom" class="dvBusScheduleChosen" style="width: 190px; height: 35px; font-size: 12px;">
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
                    <td style="width: 110px"><label for="busScheduleDestTo"><?php echo TABLE_DESTINATION_TO ?></label> :</td>
                    <td style="width: 200px;">
                        <select id="busScheduleDestTo" class="dvBusScheduleChosen" style="width: 190px; height: 35px; font-size: 12px;">
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
                    <td style="width: 70px"><label for="busScheduleBus"><?php echo MENU_BUS ?></label> :</td>
                    <td style="width: 200px;">
                        <select id="busScheduleBus" class="dvBusScheduleChosen" style="width: 190px; height: 35px; font-size: 12px;">
                            <option value="all"><?php echo TABLE_ALL; ?></option>
                            <?php
                            $sqlBus = mysql_query("SELECT * FROM buses WHERE is_active = 1");
                            while($rowBus = mysql_fetch_array($sqlBus)){
                            ?>
                            <option value="<?php echo $rowBus['id']; ?>"><?php echo $rowBus['code']." (".$rowBus['name'].")"; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td style="width: 70px"><label for="busScheduleStatus"><?php echo TABLE_STATUS ?></label> :</td>
                    <td style="width: 90px;">
                        <select id="busScheduleStatus" style="width: 90px; height: 35px; font-size: 12px;">
                            <option value="all"><?php echo TABLE_ALL; ?></option>
                            <option value="1"><?php echo "Active"; ?></option>
                            <option value="2"><?php echo "Left"; ?></option>
                        </select>
                    </td>
                    <td>
                        <div class="buttons" style="float: right;">
                            <button type="button" class="positive" id="btnRefreshSearchBusSchedule">
                                <img src="<?php echo $this->webroot; ?>img/button/refresh-active.png" alt=""/>
                                <span id="lblRefreshSearchBusSchedule"><?php echo GENERAL_SEARCH; ?></span>
                            </button>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div style="clear: both;"></div>
    </div>
    <br />
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_DATE; ?></th>
                    <th><?php echo TABLE_DEPARTURE_TIME; ?></th>
                    <th><?php echo TABLE_DESTINATION_FROM; ?></th>
                    <th><?php echo TABLE_DESTINATION_TO; ?></th>
                    <th><?php echo MENU_BUS; ?></th>
                    <th><?php echo TABLE_DELAY_TIME; ?></th>
                    <th><?php echo "Left Date"; ?></th>
                    <th><?php echo TABLE_SEAT; ?></th>
                    <th><?php echo TABLE_STATUS; ?></th>
                    <th><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="11" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
    <?php if($allowAdd){ ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnAddBusSchedule">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_JOURNEY_BUS_ADD; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
</div>
<div class="rightPanel"></div>