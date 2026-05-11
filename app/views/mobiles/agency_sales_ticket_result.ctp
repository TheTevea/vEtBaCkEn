<?php
include('includes/function.php');
$sqlRiel = mysql_query("SELECT symbol FROM currency_centers WHERE id = 1");
$rowRiel = mysql_fetch_array($sqlRiel);
$totalSales      = 0;
$totalCommission = 0;
$totalMarkup     = 0;
$totalVat        = 0;
$totalBonus      = 0;
$totalRecord     = 0;
$records         = array();
$condition       = "";
$dateDisplay     = 1;
if(!empty($_POST['date_from']) && !empty($_POST['date_to'])){
    $condition .= " AND `date` >= '".dateConvert($_POST['date_from']) ."' AND `date` <= '".dateConvert($_POST['date_to']) ."' ";
}
if(!empty($_POST['traval_from']) && !empty($_POST['traval_to'])){
    $dateDisplay = 2;
    $condition .= " AND `journey_date` >= '".dateConvert($_POST['traval_from']) ."' AND `journey_date` <= '".dateConvert($_POST['traval_to']) ."' ";
}
$sqlAgent = mysql_query("SELECT * FROM t_agents WHERE id = ".$_POST['branch']);
$rowAgent = mysql_fetch_array($sqlAgent);
$sqlTicket = mysql_query("SELECT * FROM t_tickets WHERE status > 0 AND t_agent_id = ".$_POST['branch'].$condition);
while($rowTicket = mysql_fetch_array($sqlTicket)){
    $sqlAgency = mysql_query("SELECT * FROM t_agents WHERE id = ".$rowTicket['t_agent_id']);
    $rowAgency = mysql_fetch_array($sqlAgency);
    $sqlSeat   = mysql_query("SELECT nationally FROM t_ticket_details WHERE t_ticket_id = ".$rowTicket['id']." LIMIT 1");
    $rowSeat   = mysql_fetch_array($sqlSeat);
    $sellPrice = $rowTicket['total_amount'];
    $agePrice  = $rowTicket['total_amount'];
    if($rowAgency['commission_type'] == 1){ // Set Percentage
        $commission = replaceThousand(number_format((($rowTicket['total_amount'] + $rowTicket['total_vat']) * $rowAgency['commission']) / 100, 2));
    } else if($rowAgency['commission_type'] == 2) { // Agency Price
        $sqlJou = mysql_query("SELECT * FROM t_journeys WHERE id = ".$rowTicket['t_journey_id']);
        $rowJou = mysql_fetch_array($sqlJou);
        $agePrice  = $rowJou['agent_price_amount'] * $rowTicket['total_seat'];
        if($rowSeat['nationally'] == 2){
            $agePrice  = $rowJou['agetn_price_percent'] * $rowTicket['total_seat'];
        }
        // Check Price in Period
        $sqlPA = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = 1 AND destination_from_id = ".$rowJou['t_destination_from_id']." AND destination_to_id = ".$rowJou['t_destination_to_id']." AND t_transportation_type_id = ".$rowJou['t_transportation_type_id']." AND start <= '".$rowTicket['date']."' AND end >= '".$rowTicket['date']."' AND status = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
        if(mysql_num_rows($sqlPA)){
            $rowPAPrice = mysql_fetch_array($sqlPA);
            if($rowPAPrice['price_type'] == 1){
                if($rowSeat['nationally'] == 2){    
                    $agePrice  = $rowPAPrice['agency_price_foreigner'] * $rowTicket['total_seat'];
                } else {
                    $agePrice  = $rowPAPrice['agency_price'] * $rowTicket['total_seat'];
                }
            } else {
                if($rowSeat['nationally'] == 2){  
                    $agePrice  = $agePrice + ($rowPAPrice['agency_price_foreigner'] * $rowTicket['total_seat']);
                } else {
                    $agePrice  = $agePrice + ($rowPAPrice['agency_price'] * $rowTicket['total_seat']);   
                }
            }
        }
        $commission  = $sellPrice - $agePrice;
    } else { // Fixed Amount
        $commission  = $rowAgency['commission'];
    }
    $sqlTotal  = mysql_query("SELECT COUNT(t_ticket_details.id) AS total FROM t_ticket_details WHERE t_ticket_id = ".$rowTicket['id']);
    $totalSeat = mysql_fetch_array($sqlTotal);
    $records[$totalRecord]['id']    = $rowTicket['id'];
    $records[$totalRecord]['date']  = $rowTicket['date'];
    $records[$totalRecord]['journey_date']  = $rowTicket['journey_date'];
    $records[$totalRecord]['code']          = $rowTicket['code'];
    $records[$totalRecord]['qty']           = $totalSeat['total'];
    $records[$totalRecord]['sell_price']    = $sellPrice;
    $records[$totalRecord]['com']           = $commission;
    $records[$totalRecord]['mark']          = $rowTicket['total_markup'];
    $records[$totalRecord]['vat']           = $rowTicket['total_vat'];
    $records[$totalRecord]['bonus']         = $rowTicket['total_bonus'];

    $totalSales      += $rowTicket['total_amount'] + $rowTicket['total_vat'];
    $totalCommission += $commission;
    $totalMarkup     += $rowTicket['total_markup'];
    $totalVat        += $rowTicket['total_vat'];
    $totalBonus      += $rowTicket['total_bonus'];
    $totalRecord++;
}
?>
<script type="text/javascript">
    $(document).ready(function(){
        var tabHeight = $(window).height() - ($("#headReport").height() + 140);
        $("#contentReport").css("min-height", tabHeight);

        $(".agentSalesTicketView").click(function(event){
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
        <td style="font-size: 14px; font-weight: bold; width: 180px; text-align: left;">សរុបតម្លៃលក់</td>
        <td style="font-size: 14px; font-weight: bold; width: 2%;">:</td>
        <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($totalSales, 2)." ".$rowRiel[0]; ?></td>
    </tr>
    <tr>
        <td style="font-size: 14px; font-weight: bold; text-align: left;">សរុប Commission</td>
        <td style="font-size: 14px; font-weight: bold;">:</td>
        <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($totalCommission, 2)." ".$rowRiel[0]; ?></td>
    </tr>
    <tr>
        <td style="font-size: 14px; font-weight: bold; text-align: left;">សរុប Bonus</td>
        <td style="font-size: 14px; font-weight: bold;">:</td>
        <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($totalBonus, 2)." ".$rowRiel[0]; ?></td>
    </tr>
    <tr>
        <td style="font-size: 14px; font-weight: bold; text-align: left;">សរុបចុងក្រោយ</td>
        <td style="font-size: 14px; font-weight: bold;">:</td>
        <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($totalSales - $totalCommission - $totalBonus, 2)." ".$rowRiel[0]; ?></td>
    </tr>
    <tr>
        <td style="font-size: 14px; font-weight: bold; text-align: left;">សរុប Markup</td>
        <td style="font-size: 14px; font-weight: bold;">:</td>
        <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($totalMarkup, 2)." ".$rowRiel[0]; ?></td>
    </tr>
</table>
<br />
<div id="contentReport" style="min-width: 330px; margin: 0px auto; overflow: auto; height: 200px; text-align: left;">
    <table class="table_print" cellspacing="0" style="width: 550px; margin: 0px auto;">
        <thead>
            <tr>
                <th class="first" style="width: 25px; font-size: 9px; font-weight: bold; padding: 5px;">ល.រ</th>
                <th style="width: 60px; font-size: 9px; font-weight: bold; padding: 5px; text-align: left;">
                    <?php
                    if($dateDisplay == 1){
                        echo "ថ្ងៃកក់";
                    } else {
                        echo "ថ្ងៃធ្វើដំណើរ";
                    }
                    ?>
                </th>
                <th style="font-size: 9px; font-weight: bold; padding: 5px; text-align: left;">លេខកូដ</th>
                <th style="width: 60px; font-size: 9px; font-weight: bold; padding: 5px;">ចំនួនកៅអី</th>
                <th style="width: 60px; font-size: 9px; font-weight: bold; padding: 5px;">តម្លៃលក់</th>
                <th style="width: 60px; font-size: 9px; font-weight: bold; padding: 5px; text-align: right;">កំរៃជើងសារ</th>
                <th style="width: 60px; font-size: 9px; font-weight: bold; padding: 5px; text-align: right;">Bonus</th>
                <th style="width: 60px; font-size: 9px; font-weight: bold; padding: 5px; text-align: right;">Markup</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $index = 0;
            foreach($records AS $data){
            ?>
            <tr>
                <td class="first" style="font-size: 9px; padding: 5px;"><?php echo ++$index; ?></td>
                <td style="font-size: 9px; padding: 5px;">
                    <?php 
                    if($dateDisplay == 1){
                        echo dateShort($data['date'], "d/m/Y"); 
                    } else {
                        echo dateShort($data['journey_date'], "d/m/Y"); 
                    }
                    ?>
                </td>
                <td style="font-size: 9px; padding: 5px; text-align: left;"><a class="agentSalesTicketView" href="#" data="<?php echo $data['id']; ?>"><?php echo $data['code']; ?></a></td>
                <td style="font-size: 9px; padding: 5px; text-align: center;"><?php echo number_format($data['qty'], 0); ?></td>
                <td style="font-size: 9px; padding: 5px; text-align: right;"><?php echo number_format($data['sell_price'] + $data['vat'], 2)." ".$rowRiel[0]; ?></td>
                <td style="font-size: 9px; padding: 5px; text-align: right;"><?php echo number_format($data['com'], 2)." ".$rowRiel[0]; ?></td>
                <td style="font-size: 9px; padding: 5px; text-align: right;"><?php echo number_format($data['bonus'], 2)." ".$rowRiel[0]; ?></td>
                <td style="font-size: 9px; padding: 5px; text-align: right;"><?php echo number_format($data['mark'], 2)." ".$rowRiel[0]; ?></td>
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
</div>
<div style="clear: both;"></div>
<br />