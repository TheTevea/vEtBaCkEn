<?php
include("includes/function.php");
?>
<style type="text/css" media="screen">
    .bgtext {
        position: relative;
        width: 100%;
        background-image: url('../img/view.png') !important;
        background-repeat: repeat !important;
        /* background-position: center !important; */
    }
    
</style>
<!-- QR Code -->
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.qrcode.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackOnlineOrder").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableOnlineOrder.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });

        $('.qrCodeOnlineOrder').each(function(){
            var qrCode = $(this).val();
            var obj    = $(this).closest("tr").find(".cardQRCode");
            obj.qrcode({
                width	: "90",
                height	: "90",
                text	: qrCode
            }); 
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackOnlineOrder">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php
$sqlTicket = mysql_query("SELECT t_tickets.*, currency_centers.symbol, companies.name AS company_name, branches.name AS branch_name, branches.telephone AS branch_telephone, t_boarding_points.name AS boarding, t_drop_offs.name AS drop_off FROM  
                          t_ticket_api_tmps AS t_tickets
                          INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id
                          INNER JOIN companies ON companies.id = t_tickets.company_id
                          INNER JOIN branches ON branches.id = t_tickets.branch_id
                          LEFT JOIN t_boarding_points ON t_boarding_points.id = t_tickets.t_boarding_point_id
                          LEFT JOIN t_drop_offs ON t_drop_offs.id = t_tickets.t_drop_off_id
                          WHERE t_tickets.online_order_id = ".$id);
while($rowTicket = mysql_fetch_array($sqlTicket)){
?>
<fieldset>
    <legend><?php __(MENU_SELL_TICKET_INFO); ?></legend>
    <div style="width: 100%;">
        <table style="width: 100%;" cellpadding="5">
            <tr>
                <th style="width: 100px;"><?php __(TABLE_DATE); ?></th>
                <td style="width: 10px;">:</td>
                <td>
                    <?php echo dateShort($rowTicket['date']); ?>
                </td>
            </tr>
            <?php
            if(!empty($rowTicket['online_order_id'])){
            ?>
            <tr>
                <th><?php __("Transaction ID"); ?></th>
                <td>:</td>
                <td>
                    <?php 
                    $sqlOnlineOrder = mysql_query("SELECT * FROM online_orders WHERE id = ".$rowTicket['online_order_id']); 
                    $rowOnlineOrder = mysql_fetch_array($sqlOnlineOrder);
                    echo $rowOnlineOrder['code'];
                    ?>
                </td>
            </tr>
            <?php
            }
            ?>
            <tr>
                <th><?php __(TABLE_JOURNEY_DATE); ?></th>
                <td>:</td>
                <td>
                    <?php 
                    if($rowTicket['is_open_date'] == 0){
                        echo dateShort($rowTicket['journey_date']); 
                    } else {
                        echo 'Open';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_DEPARTURE_TIME); ?></th>
                <td>:</td>
                <td>
                    <?php 
                    if($rowTicket['is_open_date'] == 0){
                        echo $rowTicket['journey_time']; 
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_DESTINATION_FROM); ?></th>
                <td>:</td>
                <td>
                    <?php 
                    $sqlFrom = mysql_query("SELECT name FROM t_destinations WHERE id = ".$rowTicket['t_destination_from_id']);
                    $rowFrom = mysql_fetch_array($sqlFrom);
                    echo $rowFrom[0];
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_DESTINATION_TO); ?></th>
                <td>:</td>
                <td>
                    <?php 
                    $sqlTo = mysql_query("SELECT name FROM t_destinations WHERE id = ".$rowTicket['t_destination_to_id']);
                    $rowTo = mysql_fetch_array($sqlTo);
                    echo $rowTo[0];
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_TOTAL_AMOUNT); ?></th>
                <td>:</td>
                <td>
                    <?php echo number_format($rowTicket['total_amount'] + $rowTicket['total_vat'], 2).' '.$rowTicket['symbol']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(GENERAL_DISCOUNT); ?></th>
                <td>:</td>
                <td>
                    <?php
                    if($rowTicket['discount_amount'] != ''){
                        echo number_format($rowTicket['discount_amount'], 2).' '.$rowTicket['symbol']; 
                    } else {
                        echo '0';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_TOTAL); ?></th>
                <td>:</td>
                <td>
                    <?php echo number_format($rowTicket['total_amount'] - $rowTicket['discount_amount'] + $rowTicket['total_vat'] + $rowTicket['lucky_draw_fee'], 2).' '.$rowTicket['symbol']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(MENU_BOARDING_POINT); ?></th>
                <td>:</td>
                <td>
                    <?php echo $rowTicket['boarding']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(MENU_DROP_OFF); ?></th>
                <td>:</td>
                <td>
                    <?php echo $rowTicket['drop_off']; ?>
                </td>
            </tr>
            <?php
            if($rowTicket['is_open_date'] == 0){
            ?>
            <tr>
                <th><?php __(TABLE_SEAT_NUMBER); ?></th>
                <td>:</td>
                <td>
                    <?php 
                    $sqlSeat = mysql_query("SELECT GROUP_CONCAT(label_number) 
                                            FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = ".$rowTicket['id']);
                    $rowSeat = mysql_fetch_array($sqlSeat);
                    echo $rowSeat[0]; 
                    ?>
                </td>
            </tr>
            <?php
            } else {
            ?>
            <tr>
                <th>Total Seat</th>
                <td>:</td>
                <td>
                    <?php echo $rowTicket['total_seat']; ?>
                </td>
            </tr>
            <?php
            }
            ?>
            <tr>
                <th><?php __(TABLE_CUSTOMER_NAME); ?></th>
                <td>:</td>
                <td>
                    <?php echo $rowTicket['customer_name']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_EMAIL); ?></th>
                <td>:</td>
                <td>
                    <?php 
                    if($rowTicket['email'] != 'user@gmail.com' && $rowTicket['email'] != 'minapp@gmail.com'){
                        echo $rowTicket['email']; 
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_TELEPHONE); ?></th>
                <td>:</td>
                <td>
                    <?php echo $rowTicket['telephone']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_NOTE); ?></th>
                <td>:</td>
                <td>
                    <?php echo $rowTicket['note']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __("Booked Date"); ?></th>
                <td>:</td>
                <td>
                    <?php echo dateShort($rowTicket['created'], "d/m/Y H:i:s"); ?>
                </td>
            </tr>
        </table>
    </div>
    <br />
    <fieldset>
        <legend><?php __(TABLE_SEAT_INFORMATION); ?></legend>
        <table cellpadding="5" cellspacing="0" style="width: 100%;" class="table">
            <tr>
                <th class="first" style="width: 6%;">Seat #</th>
                <th>Name</th>
                <th>Gender</th>
                <th>Price</th>
                <th>Dis</th>
                <th>Status</th>
                <th></th>
            </tr>
            <?php
            $sqlSeat = mysql_query("SELECT * FROM t_ticket_detail_api_tmps WHERE t_ticket_api_tmp_id = ".$rowTicket['id']);
            while($rowSeat = mysql_fetch_array($sqlSeat)){
            ?>
            <tr>
                <td class="first">
                    <?php 
                    echo $rowSeat['label_number']; 
                    if($rowSeat['is_free'] == 1){
                        echo " (Free)";
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    echo $rowSeat['name'];
                    if(!empty($rowSeat['passport'])){
                        echo "<br/>Passport:".$rowSeat['passport'];
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if($rowSeat['gender'] == 1){
                        echo "Male";
                    } else if($rowSeat['gender'] == 2){
                        echo "Female";
                    }
                    if(!empty($rowSeat['dob'])){
                        echo "<br/>DOB:".dateShort($rowSeat['dob']);
                    }
                    ?>
                </td>
                <td><?php echo number_format($rowSeat['total_amount'] + $rowSeat['vat_price'], 2)." $"; ?></td>
                <td><?php echo number_format($rowSeat['discount'], 2)." $"; ?></td>
            </tr>
            <?php
            }
            ?>
        </table>
    </fieldset>
 </fieldset>
<?php
}
?>