<?php
include('includes/function.php');
$sqlRiel = mysql_query("SELECT symbol FROM currency_centers WHERE id = 1");
$rowRiel = mysql_fetch_array($sqlRiel);
$totalSales      = 0;
$totalCommission = 0;
$totalMarkup     = 0;
$totalRecord     = 0;
$records         = array();
$condition       = "";
if(!empty($_POST['date_from']) && !empty($_POST['date_to'])){
    $condition .= " AND `date` >= '".dateConvert($_POST['date_from']) ."' AND `date` <= '".dateConvert($_POST['date_to']) ."' ";
}
if(!empty($_POST['traval_from']) && !empty($_POST['traval_to'])){
    $condition .= " AND `journey_date` >= '".dateConvert($_POST['traval_from']) ."' AND `journey_date` <= '".dateConvert($_POST['traval_to']) ."' ";
}
$sqlTicket = mysql_query("SELECT * FROM t_tickets WHERE status > 0 AND main_branch_id = ".$_POST['branch'].$condition);
while($rowTicket = mysql_fetch_array($sqlTicket)){
    $sqlSeat = mysql_query("SELECT COUNT(id) FROM t_ticket_details WHERE t_ticket_id = ".$rowTicket['id']." AND is_active = 1");
    $rowSeat = mysql_fetch_array($sqlSeat);
    $records[$totalRecord]['id']   = $rowTicket['id'];
    $records[$totalRecord]['date'] = $rowTicket['date'];
    $records[$totalRecord]['travel_date'] = $rowTicket['journey_date']." ".$rowTicket['journey_time'];
    $records[$totalRecord]['code'] = $rowTicket['code'];
    $records[$totalRecord]['qty']  = $rowSeat[0];
    $records[$totalRecord]['price'] = $rowTicket['total_amount'];
    $totalSales      += $rowTicket['total_amount'];
    $totalCommission += $rowTicket['commission'];
    $totalMarkup     += $rowTicket['total_markup'];
    $totalRecord++;
}
?>
<script type="text/javascript">
    $(document).ready(function(){
        var tabHeight = $(window).height() - ($("#headReport").height() + 140);
        $("#contentReport").css("min-height", tabHeight);

        $(".salesTicketView").unbind("click").click(function(event){
            event.preventDefault();
            var id = $(this).attr('data');
            var leftPanel=$(this).parent().parent().parent().parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/salesTicketView/" + id);
        });
    });
</script>
<br />
<table style="width: 100%;">
    <tr>
        <td style="font-size: 14px; font-weight: bold; width: 100px; text-align: left;">សរុបការលក់</td>
        <td style="font-size: 14px; font-weight: bold; width: 2%;">:</td>
        <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($totalSales, 2)." ".$rowRiel[0]; ?></td>
    </tr>
</table>
<br />
<div id="contentReport" style="width: 100%; margin: 0px auto; overflow: auto; height: 200px;">
    <table class="table_print" cellspacing="0" style="width: 99%; margin: 0px auto;">
        <thead>
            <tr>
                <th class="first" style="width: 25px; font-size: 9px; font-weight: bold; padding: 5px;">ល.រ</th>
                <th style="width: 40px; font-size: 9px; font-weight: bold; padding: 5px; text-align: left;">ថ្ងៃកក់</th>
                <th style="width: 100px; font-size: 9px; font-weight: bold; padding: 5px; text-align: left;">ថ្ងៃធ្វើដំណើរ</th>
                <th style="font-size: 9px; font-weight: bold; padding: 5px; text-align: left;">លេខកូដ</th>
                <th style="width: 60px; font-size: 9px; font-weight: bold; padding: 5px;">ចំនួនកៅអី</th>
                <th style="width: 60px; font-size: 9px; font-weight: bold; padding: 5px; text-align: right;">តម្លៃលក់</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $index = 0;
            foreach($records AS $data){
            ?>
            <tr>
                <td class="first" style="font-size: 9px; padding: 5px;"><?php echo ++$index; ?></td>
                <td style="font-size: 9px; padding: 5px;"><?php echo dateShort($data['date'], "d/m/Y"); ?></td>
                <td style="font-size: 9px; padding: 5px;"><?php echo dateShort($data['travel_date'], "d/m/Y H:i"); ?></td>
                <td style="font-size: 9px; padding: 5px; text-align: left;"><a class="salesTicketView" href="#" data="<?php echo $data['id']; ?>"><?php echo $data['code']; ?></a></td>
                <td style="font-size: 9px; padding: 5px; text-align: center;"><?php echo number_format($data['qty'], 0); ?></td>
                <td style="font-size: 9px; padding: 5px; text-align: right;"><?php echo number_format($data['price'], 2)." ".$rowRiel[0]; ?></td>
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
</div>
<div style="clear: both;"></div>
<br />