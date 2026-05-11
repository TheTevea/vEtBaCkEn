<script type="text/javascript">
    var serverInterval;
    $(document).ready(function(){
        clearInterval(serverInterval);
        // Set Interval
        serverInterval = setInterval(refreshServerMonitor, 30000);
    });
    
    function refreshServerMonitor(){
        $.ajax({
            type: "GET",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/refresh",
            success: function(result){
                if($("#serverDiv").html() == null){
                    clearInterval(serverInterval);
                } else {
                    $("#serverDiv").html(result);
                }
            }
        });
    }
</script>
<div id="serverDiv">
<?php
include("includes/function.php");
$sqlServer = mysql_query("SELECT * FROM offline_servers WHERE offline_project_id IN (SELECT offline_project_id FROM users WHERE id = ".$user['User']['id'].") AND is_main = 0");
while($rowServer = mysql_fetch_array($sqlServer)){
    // Check Time Compare
    $statusColor = 'background: #00F;';
    $current = strtotime("now");
    $server  = strtotime('+2 minutes', strtotime($rowServer['last_connect']));
    if($server < $current){
        $statusColor = 'background: #FF0000;';
    }
?>
    <div style="width: 250px; height: 180px; float: left; margin-bottom: 5px; margin-right: 5px;<?php echo $statusColor; ?>">
        <table cepadding="5" cellspacing="0" style="width: 90%; margin-left: 10px; margin-top: 10px;">
            <tr>
                <td colspan="2" style="text-align: center; color: #fff; height: 30px; font-size: 12px; font-weight: bold;"><?php echo $rowServer['name']; ?></td>
            </tr>
            <tr>
                <td style="width: 40%; height: 20px; font-size: 12px; color: #fff;">IP Address :</td>
                <td style="font-size: 12px; color: #fff;">
                    <?php echo $rowServer['last_ip']; ?>
                </td>
            </tr>
            <tr>
                <td style="width: 40%; height: 20px; font-size: 12px; color: #fff;">Last Update :</td>
                <td style="font-size: 12px; color: #fff;">
                    <?php 
                    if($rowServer['last_connect'] != '' && $rowServer['last_connect'] != '0000-00-00 00:00:00'){
                        echo dateShort($rowServer['last_connect'], "d/m/Y H:i:s");
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td style="width: 40%; height: 20px; font-size: 12px; color: #fff;">Will Send :</td>
                <td style="font-size: 12px; color: #fff;">
                    <?php 
                    echo number_format($rowServer['total_send'], 0);
                    ?>
                </td>
            </tr>
            <tr>
                <td style="width: 40%; height: 20px; font-size: 12px; color: #fff;">Total Sent :</td>
                <td style="font-size: 12px; color: #fff;">
                    <?php 
                    echo number_format($rowServer['total_sent'], 0);
                    ?>
                </td>
            </tr>
            <tr>
                <td style="width: 40%; height: 20px; font-size: 12px; color: #fff;">Will Receive :</td>
                <td style="font-size: 12px; color: #fff;">
                    <?php 
                    echo number_format($rowServer['will_receive'], 0);
                    ?>
                </td>
            </tr>
            <tr>
                <td style="width: 40%; height: 20px; font-size: 12px; color: #fff;">Total Received :</td>
                <td style="font-size: 12px; color: #fff;">
                    <?php 
                    echo number_format($rowServer['total_received'], 0);
                    ?>
                </td>
            </tr>
        </table>
    </div>
<?php
}
?>
    <div style="clear: both;"></div>
</div>