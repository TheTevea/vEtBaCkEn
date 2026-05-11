<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnRefreshSYNC").unbind("click").click(function(event){
            event.preventDefault();
            var panel = $("#divSYNCMonitor").parent();
            panel.html("<?php echo ACTION_LOADING; ?>");
            panel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/index/");
        });
        
        $(".btnSettingSYNC").unbind("click").click(function(event){
            event.preventDefault();
            var name = $(this).attr("data");
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo 'Do you want to switch sync network for '; ?> <b>' + name + '</b>?</p>');
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
                    '<?php echo 'Moden'; ?>': function() {
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/changeSyncSetting/" + name+"/1",
                            success: function(){
                                $(".btnRefreshSYNC").click();
                            }
                        });
                        $(this).dialog("close");
                    },
                    '<?php echo 'ISP'; ?>': function() {
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/changeSyncSetting/" + name+"/2",
                            success: function(){
                                $(".btnRefreshSYNC").click();
                            }
                        });
                        $(this).dialog("close");
                    }
                }
            });
        });
        
        $(".btnSYNC").unbind("click").click(function(event){
            event.preventDefault();
            var name = $(this).attr("data");
            var obj  = $(this);
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo 'Do you want to sycn data for '; ?> <b>' + name + '</b>?</p>');
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
                        if(name == 'Transaction'){
                            getTransaction("1", obj);
                        } else if(name == 'Setting') {
                            getSettingFromCloud("1", obj);
                        }
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_NO; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
    });
    
    function getTransaction(status, obj){
        $.ajax({
            type: "POST",
            dataType: "json",
            url:  "<?php echo $this->base . '/'; ?>sync_monitors/sendTransactionToCloud/"+status,
            beforeSend: function(){
                obj.find(".lblSYNC").text("Loading..");
                obj.attr("disabled", true);
            },
            error: function(){
                obj.find(".lblSYNC").text("SYNC");
                obj.attr("disabled", false);
            },
            success: function(msg){
                var pid   = msg.pid;
                var total = msg.total;
                var token = msg.token;
                if(msg.status == '1'){
                    var i;
                    var datas = msg.data;
                    for (i = 0; i < datas.length; i++) {
                        var id = datas[i]['id'];
                        var synCode = datas[i]['synCode'];
                        var content = datas[i]['content'];
                        var totalSend = total - 1;
                        sendTransactionAPI(id, token, synCode, content, totalSend);
                    } 
                }
                // Update Process
                updateEndProcess(pid);
                // Update Button SYNC
                obj.find(".lblSYNC").text("SYNC");
                obj.attr("disabled", false);
            },
            timeout: 60000 // Timeout 1 minute
        });
    }
    
    function updateEndProcess(pid){
        $.ajax({
            type: "GET",
            url:  "<?php echo $this->base . '/'; ?>sync_monitors/updateEndProcess/"+pid,
            timeout: 20000 // Timeout 20 seconds
        });
    }
    
    function sendTransactionAPI(id, token, synCode, content, totalSend){
        $.ajax({
            type: "POST",
            url:  "<?php echo SERVER_API; ?>setting/save",
            data: "token="+token+"&synCode="+synCode+"&contents="+content+"&total="+totalSend,
            success: function(msg){
                var update = false;
                if(msg.header.result == true){
                    if(msg.body.status == "1"){
                        update = true;
                    }
                }
                if(update == true){
                    updateSendTransaction(id);
                }
            },
            timeout: 30000 // Timeout 30 senconds
        });
    }
    
    function updateSendTransaction(id){
        $.ajax({
            type: "GET",
            url:  "<?php echo $this->base . '/'; ?>sync_monitors/updateTransaction/"+id,
            timeout: 20000 // Timeout 20 seconds
        });
    }
    
    function getSettingFromCloud(status, obj){
        $.ajax({
            type: "POST",
            dataType: "json",
            url:  "<?php echo $this->base . '/'; ?>sync_monitors/getSettingRequest/"+status,
            beforeSend: function(){
                obj.find(".lblSYNC").text("Loading..");
                obj.attr("disabled", true);
            },
            error: function(){
                obj.find(".lblSYNC").text("SYNC");
                obj.attr("disabled", false);
            },
            success: function(msg){
                var request  = msg.request;
                var response = msg.response;
                var token    = msg.token;
                var pid      = msg.pid;
                if(response == ""){
                    response = "NULL";
                } 
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url:  "<?php echo SERVER_API; ?>setting/get/"+token+"/"+request+"/"+response,
                    error: function(){
                        // Update Button SYNC
                        obj.find(".lblSYNC").text("SYNC");
                        obj.attr("disabled", false);
                        // Update Process
                        updateEndProcess(pid);
                    },
                    success: function(msg){
                        if(msg.header.result == true){
                            if(msg.body.status == "1"){
                                var total   = msg.body.total;
                                var receive = JSON.stringify(msg.body.settings); 
                                updateSettingReceive(receive, total);
                            }
                            updateSettingRequest(request);
                            if(response != "NULL" && response != ""){
                                deleteSettingRequest(response);
                            }
                        }
                        // Update Button SYNC
                        obj.find(".lblSYNC").text("SYNC");
                        obj.attr("disabled", false);
                        // Update Process
                        updateEndProcess(pid);
                    },
                    timeout: 60000 // Timeout 20 seconds
                });
            },
            timeout: 20000 // Timeout 20 seconds
        });
    }
    
    function updateSettingReceive(receive, total){
        $.ajax({
            type: "POST",
            url:  "<?php echo $this->base . '/'; ?>sync_monitors/receiveSetting",
            data: "data[receive]="+receive+"&data[total]="+total,
            timeout: 20000 // Timeout 20 seconds
        });
    }
    
    function updateSettingRequest(request){
        $.ajax({
            type: "GET",
            url:  "<?php echo $this->base . '/'; ?>sync_monitors/updateSettingRequest/"+request,
            timeout: 20000 // Timeout 20 seconds
        });
    }
    
    function deleteSettingRequest(request){
        $.ajax({
            type: "GET",
            url:  "<?php echo $this->base . '/'; ?>sync_monitors/deleteSettingRequest/"+request,
            timeout: 20000 // Timeout 20 seconds
        });
    }
</script>
<fieldset style="width: 98%;" id="divSYNCMonitor">
    <legend><?php __(MENU_SYNC_MONITORING); ?></legend>
    <br />
    <table cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="font-size: 12px; font-weight: bold;"><?php echo TABLE_SYNC_INFORMATION; ?></td>
            <td style="text-align: right; width: 120px; height: 40px;">
                <div class="buttons">
                    <a href="#" class="positive btnRefreshSYNC" style="float: right;">
                        <img src="<?php echo $this->webroot; ?>img/button/refresh-active.png" alt=""/>
                        <span><?php echo ACTION_REFRESH; ?></span>
                    </a>
                </div>
                <div style="clear: both;"></div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border-top: 1px solid #000;">
                <table class="table" style="width: 100%;">
                    <tr>
                        <th class="first" style="width: 14%;"><?php echo TABLE_NAME; ?></th>
                        <th style="width: 15%;"><?php echo TABLE_DATE_START; ?></th>
                        <th style="width: 15%;"><?php echo TABLE_DATE_END; ?></th>
                        <th><?php echo TABLE_DURATION; ?> (s)</th>
                        <th><?php echo 'Total Send'; ?></th>
                        <th><?php echo 'Total Sent'; ?></th>
                        <th><?php echo 'Will Receive'; ?></th>
                        <th><?php echo 'Total Received'; ?></th>
                        <th style="width: 6%;"><?php echo TABLE_STATUS; ?></th>
                        <th style="width: 6%;"><?php echo 'SYNC'; ?></th>
                        <th style="width: 15%;"><?php echo ACTION_ACTION; ?></th>
                    </tr>
                    <?php
                    $totalWillReceive = 0;
                    $sqlSync = mysql_query("SELECT * FROM offline_processes WHERE 1;");
                    while($rowSync = mysql_fetch_array($sqlSync)){
                        $totalWillReceive = $rowSync['total_will_receive'];
                    ?>
                    <tr>
                        <td class="first">
                            <?php
                            echo $rowSync['name'];
                            ?>
                        </td>
                        <td>
                            <?php
                            if(!empty($rowSync['start']) && $rowSync['start'] != '0000-00-00'){
                                echo dateShort($rowSync['start'], "d/m/Y H:i:s");
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if(!empty($rowSync['end']) && $rowSync['end'] != '0000-00-00'){
                                echo dateShort($rowSync['end'], "d/m/Y H:i:s");
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if(!empty($rowSync['start']) && !empty($rowSync['end'])){
                                $duration = strtotime($rowSync['end']) - strtotime($rowSync['start']);
                                echo $duration;
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            echo number_format($rowSync['total_will_send'], 0);
                            ?>
                        </td>
                        <td>
                            <?php
                            echo number_format($rowSync['total_sent'], 0);
                            ?>
                        </td>
                        <td>
                            <?php
                            echo number_format($rowSync['total_will_receive'], 0);
                            ?>
                        </td>
                        <td>
                            <?php
                            echo number_format($rowSync['total_received'], 0);
                            ?>
                        </td>
                        <td>
                            <?php
                            if($rowSync['status'] == 1){
                                echo 'On';
                            } else {
                                echo 'Off';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if($rowSync['sync_by'] == 1){
                                echo 'Modern';
                            } else if($rowSync['sync_by'] == 2){
                                echo 'ISP';
                            } else {
                                echo 'Auto';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if($rowSync['name'] != 'Seat'){
                            ?>
                            <div class="buttons">
                                <a href="#" class="positive btnSYNC" data="<?php echo $rowSync['name']; ?>" style="float: left;">
                                    <img src="<?php echo $this->webroot; ?>img/button/refresh-active.png" alt=""/>
                                    <span class="lblSYNC"><?php echo 'SYNC'; ?></span>
                                </a>
                            </div>
                            <div class="buttons">
                                <a href="#" class="positive btnSettingSYNC" data="<?php echo $rowSync['name']; ?>" style="float: left;">
                                    <img src="<?php echo $this->webroot; ?>img/button/setting-active.png" alt=""/>
                                    <span><?php echo 'Setting'; ?></span>
                                </a>
                            </div>
                            <?php
                            }
                            ?>
                            <div style="clear: both;"></div>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                </table>
            </td>
        </tr>
    </table>
</fieldset>