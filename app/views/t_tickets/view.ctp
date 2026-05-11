<?php
include("includes/function.php");
// Authentication
$this->element('check_access');
$allowDelete      = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowFullDelete  = checkAccess($user['User']['id'], $this->params['controller'], 'fullDelete');
$allowReleaseSeat = checkAccess($user['User']['id'], $this->params['controller'], 'releaseSeat');

$sqlTicket = mysql_query("SELECT t_tickets.*, currency_centers.symbol, companies.name AS company_name, branches.name AS branch_name, branches.telephone AS branch_telephone, t_boarding_points.name AS boarding, t_drop_offs.name AS drop_off, t_agents.code AS agency_code, t_agents.name AS agency_name, t_agents.type AS agency_type, t_agents.payment AS agency_payment FROM  
                          (
                          SELECT * FROM t_tickets WHERE id = ".$id."
                          UNION ALL
                          SELECT * FROM t_ticket_3months WHERE id = ".$id."
                          ) AS t_tickets
                          INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id
                          INNER JOIN companies ON companies.id = t_tickets.company_id
                          INNER JOIN branches ON branches.id = t_tickets.branch_id
                          LEFT JOIN t_boarding_points ON t_boarding_points.id = t_tickets.t_boarding_point_id
                          LEFT JOIN t_drop_offs ON t_drop_offs.id = t_tickets.t_drop_off_id
                          LEFT JOIN t_agents ON t_agents.id = t_tickets.t_agent_id
                          WHERE 1");
$rowTicket = mysql_fetch_array($sqlTicket);
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
        $(".btnBackTTicket").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTTicket.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });

        $(".btnDeleteTTicketDetail").unbind("click").click(function(event){
            event.preventDefault();
            var id   = $(this).attr('rel');
            var name = $(this).attr('name');
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_DELETE; ?> Seat # <b>' + name + '</b>?</p>');
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
                    '<?php echo ACTION_VOID; ?>': function() {
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/voidSeat/" + id,
                            data: "",
                            beforeSend: function(){
                                $("#dialog").dialog("close");
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                            },
                            success: function(result){
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                var rightPanel = $(".btnBackTTicket").parent().parent().parent();
                                rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $rowTicket['id']; ?>");
                                // alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_VOID; ?>'){
                                    createSysAct('Ticket', 'Void Seat', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Ticket', 'Void Seat', 1, '');
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

        $(".btnReleaseTTicketDetail").unbind("click").click(function(event){
            event.preventDefault();
            var id   = $(this).attr('rel');
            var name = $(this).attr('name');
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DO_YOU_WANT_RELEASE_SEAT; ?> # <b>' + name + '</b>?</p>'+
                               '<div style="margin-top:10px;"><?php echo TABLE_NOTE; ?>:<br/>'+
                               '<textarea id="release_note" style="width: 300px; height: 70px;"></textarea></div>');
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
                        var note = $("#release_note").val();
                        if($.trim(note) === ''){
                            alert('Note is required');
                            return;
                        }
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/releaseSeat/" + id,
                            data: { note: note },
                            beforeSend: function(){
                                $("#dialog").dialog("close");
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                            },
                            success: function(result){
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                var rightPanel = $(".btnBackTTicket").parent().parent().parent();
                                rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $rowTicket['id']; ?>");
                                // alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_VOID; ?>'){
                                    createSysAct('Ticket', 'Release Seat', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Ticket', 'Release Seat', 1, '');
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

        $('.qrCodeTicket').each(function(){
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
        <a href="" class="positive btnBackTTicket">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_SELL_TICKET_INFO); ?></legend>
    <div style="width: 32%; float: left;">
        <table style="width: 100%;" cellpadding="5">
            <tr>
                <th style="width:25%;"><?php __(MENU_COMPANY_MANAGEMENT); ?></th>
                <td style="width:1%;">:</td>
                <td>
                    <?php echo $rowTicket['company_name']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(MENU_BRANCH); ?></th>
                <td>:</td>
                <td>
                    <?php echo $rowTicket['branch_name']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_DATE); ?></th>
                <td>:</td>
                <td>
                    <?php echo dateShort($rowTicket['date']); ?>
                </td>
            </tr>
            <?php
            if(!empty($rowTicket['online_order_id'])){
            ?>
            <tr>
                <th><?php __("Internal ID"); ?></th>
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
            if(!empty($rowTicket['api_bank_ref'])){
            ?>
            <tr>
                <th><?php __("Bank Reference"); ?></th>
                <td>:</td>
                <td>
                    <?php 
                    echo $rowTicket['api_bank_ref'];
                    ?>
                </td>
            </tr>
            <?php
            }
            if($rowTicket['travel_package_order_id'] > 0){
                $sqlTravelPackage = mysql_query("SELECT * FROM travel_package_orders WHERE id = ".$rowTicket['travel_package_order_id']);
                
            ?>
            <tr>
                <th style="color: red;" colspan="3">** Apply Travel Package</th>
            </tr>
            <?php
                if(mysql_num_rows($sqlTravelPackage)){
                    $rowTravelPackage = mysql_fetch_array($sqlTravelPackage);
            ?>
            <tr>
                <th colspan="3">
                    <img src="<?php echo $rowTravelPackage['photo_path'].$rowTravelPackage['photo']; ?>" style="width: 100px;" />
                </th>
            </tr>
            <?php
                }
            }
            ?>
            <tr>
                <th><?php __(TABLE_CODE); ?></th>
                <td>:</td>
                <td>
                    <?php echo $rowTicket['code']; ?>
                </td>
            </tr>
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
                <th><?php __("Lucky Ticket Fee"); ?></th>
                <td>:</td>
                <td>
                    <?php echo number_format($rowTicket['lucky_draw_fee'], 2).' '.$rowTicket['symbol']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __("Coupon"); ?></th>
                <td>:</td>
                <td>
                    <?php 
                    echo number_format($rowTicket['coupon_amount'], 2).' '.$rowTicket['symbol']; 
                    if($rowTicket['coupon_id'] != ''){
                        $sqlCoupon = mysql_query("SELECT code FROM coupons WHERE id = ". $rowTicket['coupon_id']);
                        $rowCoupon = mysql_fetch_array($sqlCoupon);
                        echo ' ('.$rowCoupon['code'].')';
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
                                            FROM t_ticket_details WHERE t_ticket_id = ".$rowTicket['id']."
                                            UNION ALL
                                            SELECT GROUP_CONCAT(label_number) 
                                            FROM t_ticket_detail_3months WHERE t_ticket_id = ".$rowTicket['id']);
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
                <th><?php __(MENU_AGENT); ?></th>
                <td>:</td>
                <td>
                    <?php echo $rowTicket['agency_name']; ?>
                </td>
            </tr>
            <tr>
                <th><?php __(TABLE_AGENT_REFERENCE); ?></th>
                <td>:</td>
                <td>
                    <?php 
                    if($rowTicket['type'] != 5 && $rowTicket['type'] != 11){
                        echo $rowTicket['agt_refer_code']; 
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php __("Journey Type"); ?></th>
                <td>:</td>
                <td>
                    <?php
                    if($rowTicket['is_round_trip'] == 1){
                        echo "Round Trip";
                    } else {
                        echo "Single Trip";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php __("Booked Date"); ?></th>
                <td>:</td>
                <td>
                    <?php echo dateShort($rowTicket['created'], "d/m/Y H:i:s"); ?>
                </td>
            </tr>
            <tr>
                <th><?php __("Booked by"); ?></th>
                <td>:</td>
                <td>
                    <?php
                    if(!empty($rowTicket['created_by'])){
                        $sqlUser = mysql_query("SELECT * FROM users WHERE id = ".$rowTicket['created_by']);
                        $rowUser = mysql_fetch_array($sqlUser);
                        echo $rowUser['username'];
                    }
                    ?>
                </td>
            </tr>
            <?php
            if($rowTicket['status'] == 0 || $rowTicket['status'] == -1){
                if(!empty($rowTicket['modified']) && $rowTicket['modified'] != "0000-00-00 00:00:00" && !empty($rowTicket['modified_by'])){
            ?>
            <tr>
                <th><?php __("Void/Cancelled Date"); ?></th>
                <td>:</td>
                <td>
                    <?php echo dateShort($rowTicket['modified'], "d/m/Y H:i:s"); ?>
                </td>
            </tr>
            <tr>
                <th><?php __("Void/Cancelled by"); ?></th>
                <td>:</td>
                <td>
                    <?php
                    if(!empty($rowTicket['modified_by'])){
                        $sqlUser = mysql_query("SELECT * FROM users WHERE id = ".$rowTicket['modified_by']);
                        $rowUser = mysql_fetch_array($sqlUser);
                        echo $rowUser['username'];
                    }
                    ?>
                </td>
            </tr>
            <?php
                }
            }
            ?>
        </table>
    </div>
    <div style="width: 67%; float: right;">
        <div class="print_doc" style="width: 320px; float: left; border: 1px solid #000; padding-left: 5px; padding-right: 5px;">
            <div class="bgtext">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 15%;"><img src="<?php echo $this->webroot; ?>img/logo-print.png" style="width: 45px;" /></td>
                        <td>
                            <table cellpadding="0" cellspacing="0" style="width: 100%;">
                                <tr>
                                    <td style="vertical-align: top; text-align: center; width: 100px; font-size: 12px;">
                                        <b style="font-size: 14px;">វិរៈប៊ុនថាំ អេចប្រេស</b><br/>Vireak Buntham Express Co.,Ltd
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-size: 10px;">
                                        VATTIN: L001-360000304
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-size: 10px;">
                                        #ដីឡូត៍លេខ C ផ្លូវ ភូមិគៀនឃ្លាំង សង្កាត់ជ្រោយចង្វារ ខណ្ឌជ្រោយចង្វារ រាជធានីភ្នំពេញ
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="width: 10%; font-size: 26px; font-weight: bold;"></td>
                    </tr>
                </table>
                <?php
                $customerType = '';
                $priceType    = '';
                if($rowTicket['price_type'] == 1){
                    $priceType = '(Khmer)';
                } else if($rowTicket['price_type'] == 2){
                    $priceType = '(VIP Card)';
                } else if($rowTicket['price_type'] == 3){
                    $priceType = '(Foreigner)';
                } else if($rowTicket['price_type'] == 4){
                    $priceType = 'Ticket 10 Free 1';
                }
                if($rowTicket['type'] == 1){
                    $customerType = 'Walk In '.$priceType;
                } else if ($rowTicket['type'] == 2) {
                    $customerType = 'Phone Call '.$priceType;
                } else if($rowTicket['type'] == 5 || $rowTicket['type'] == 11){
                    if(empty($rowTicket['t_agent_id'])){
                        $customerType = 'App';
                    } else {
                        if($rowTicket['t_agent_id'] == 55){
                            $customerType = 'Website';
                        } else {
                            $customerType = 'Mini App';
                        }
                    }
                } else if($rowTicket['type'] == 10){
                    $customerType = 'Terminal';
                } else {
                    if(!empty($rowTicket['t_agent_id'])){
                        if($rowTicket['agency_type'] == 3){
                            $customerType = 'Agency APi ('.$rowTicket['agency_name'].')';
                        } else {
                            if($rowTicket['agency_type'] == 1){
                                $customerType = 'Agency Online';
                            } else {
                                $customerType = 'Agency Offline';
                            }
                            if($rowTicket['agency_payment'] == 1){
                                $customerType .= ' Prepaid';
                            } else {
                                $customerType .= ' Postpaid';
                            }
                            $customerType .= ' ('.$rowTicket['agency_name'].')';
                        } 
                        $customerType .= "<br/>Price Type: ".$priceType;
                    }
                }
                
                // Destination
                $sqlFrom = mysql_query("SELECT code, name FROM t_destinations WHERE id = ".$rowTicket['t_destination_from_id']);
                $rowFrom = mysql_fetch_array($sqlFrom);
                $sqlTo   = mysql_query("SELECT code, name FROM t_destinations WHERE id = ".$rowTicket['t_destination_to_id']);
                $rowTo   = mysql_fetch_array($sqlTo);
                $destinationFromCode = $rowFrom[0];
                $destinationFromName = $rowFrom[1];
                $destinationToCode = $rowTo[0];
                $destinationToName = $rowTo[1];
                ?>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 48%; font-size: 10px; text-align: right;">លេខរៀងវិក្កយបត្រ/Invoice No.:</td>
                        <td style="font-size: 10px;">
                            <?php echo $rowTicket['code']; ?>
                        </td>
                    </tr>
                    <tr><td style="font-size: 10px; text-align: right;">អតិថិជន/Customer:</td>
                    <td style="font-size: 10px;"><?php echo $customerType; ?></td></tr>
                    <tr><td style="font-size: 10px; text-align: right;">លេខទូរស័ព្ទ/Telephone No:</td>
                    <td style="font-size: 10px;"><?php echo $rowTicket['telephone']; ?></td></tr>
                    <tr><td style="font-size: 10px; text-align: right;">ថ្ងៃទិញ/Issued Date:</td>
                    <td style="font-size: 10px;">
                        <?php 
                        echo dateShort($rowTicket['date']); 
                        if($rowTicket['lucky_draw_fee'] > 0){
                            echo " (<b>Lucky Draw</b>)";
                        }
                        ?>
                    </td></tr>
                    <tr><td style="font-size: 10px; text-align: right;">ថ្ងៃ​ធ្វើ​ដំណើរ/Journey Date:</td>
                        <td style="font-size: 10px;">
                            <?php 
                            $depare  = explode(":", $rowTicket['journey_time']);
                            $depareureTime = (int) $depare[0];
                            if(strtotime($rowTicket['journey_date']) >= strtotime("2021-12-07")){
                                echo dateShort($rowTicket['journey_date'])." ".date('h:i A', strtotime($rowTicket['journey_time']));  
                            } else {
                                if(checkDateFrom($rowTicket['branch_id'], $depareureTime) == 1){
                                    echo dateShort($rowTicket['journey_date'])." ".date('h:i A', strtotime($rowTicket['journey_time']));  
                                } else {
                                    echo date("d/m/Y", strtotime("+1 day", strtotime($rowTicket['journey_date'])))." ".date('h:i A', strtotime($rowTicket['journey_time']));  
                                }
                            }
                            ?>
                        </td>
                    </tr>
                    <tr><td style="font-size: 10px; text-align: right; vertical-align: text-top;">ទិសដៅ/Direction:</td>
                        <td style="font-size: 10px; vertical-align: text-top;">
                            <?php echo $destinationFromName." -> ".$destinationToName; ?>
                        </td>
                    </tr>
                </table>
                <table style="width: 100%;" cellpadding="0" cellspacing="0">
                    <tr><td style="font-size: 10px; width: 35%; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000;">លេខកៅអី<br/>Seat No.</td>
                    <td style="font-size: 10px; width: 15%; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000;">បរិមាណ<br/>Qty</td>
                    <td style="font-size: 10px; width: 20%; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;">ថ្លៃឯកតា<br/>Unit Price</td>
                    <td style="font-size: 10px; width: 20%; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;">តម្លៃ<br/>Amount</td></tr>
                    <?php
                    $qty  = 0;
                    $item = "";
                    $unitPrice   = 0;
                    $totalAmount = 0;
                    $seatRecords = array();
                    $sqlSeat = mysql_query("SELECT IFNULL(label_number, seat_number) AS seat, unit_price, vat_price, markup, discount, total_amount, is_free 
                                            FROM t_ticket_details 
                                            WHERE t_ticket_id = ".$rowTicket['id']."
                                            UNION ALL
                                            SELECT IFNULL(label_number, seat_number) AS seat, unit_price, vat_price, markup, discount, total_amount, is_free 
                                            FROM t_ticket_detail_3months 
                                            WHERE t_ticket_id = ".$rowTicket['id']."");
                    while($rowSeat = mysql_fetch_array($sqlSeat)){
                        $qty += 1;
                        if($item != ""){
                            $item .= ",";
                        }
                        $item .= $rowSeat['seat'];
                        if($rowSeat['is_free'] == 1){
                            $item .= "(Free)";
                        }
                        $unitPrice   = $rowSeat['unit_price'] + $rowSeat['markup'] + $rowSeat['vat_price'];
                        $totalAmount += $rowSeat['unit_price'] + $rowSeat['markup'] + $rowSeat['vat_price'];
                        $seatRecords[$qty]['number'] = $rowSeat['seat'];
                    }
                    ?>
                    <tr><td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000;"><?php echo $item; ?></td>
                        <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000;"><?php echo number_format($qty, 0); ?></td>
                        <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;">
                            <?php echo number_format($unitPrice, 2)." $"; ?>
                        </td>
                        <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;"><?php echo number_format($totalAmount, 2); ?> $</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;" colspan="2">តម្លៃសរុប/Total</td>
                        <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;" colspan="2"><?php echo number_format($totalAmount, 2); ?> $</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;" colspan="2">បញ្ចុះ តម្លៃ/Discount USD</td>
                        <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;" colspan="2">
                        <?php
                        if($totalAmount > 0){
                            echo number_format($rowTicket['discount_amount'] + $rowTicket['total_change'] + $rowTicket['coupon_amount'], 2); 
                        } else {
                            echo '0';
                        }
                        ?> $
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;" colspan="2">តម្លៃបន្ថែម/Extra Price</td>
                        <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;" colspan="2">
                        <?php echo number_format($rowTicket['lucky_draw_fee'], 2); ?> $
                        </td>
                    </tr>
                    <tr style="display: none;">
                        <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;" colspan="2">អាករលើតម្លៃបន្ថែម/VAT (10%)</td>
                        <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;" colspan="2"><?php echo number_format($rowTicket['total_vat'], 2); ?> $</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;" colspan="2">សរុបចុងក្រោយ/Grand Total USD</td>
                        <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;" colspan="2">
                        <?php 
                        $grandTotalUs   = $totalAmount + $rowTicket['lucky_draw_fee'] - $rowTicket['discount_amount'] - $rowTicket['total_change'] - $rowTicket['coupon_amount'];
                        $grandTotalRiel = $grandTotalUs * 4100;
                        if($grandTotalUs > 0){
                            echo number_format($grandTotalUs, 2);  
                        } else {
                            echo '0.00';
                        }
                        ?> $
                        </td>
                    </tr>
                    <tr><td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-bottom: 1px solid #000; text-align: right;" colspan="2">សរុបចុងក្រោយ/Grand Total Riel</td>
                        <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: 1px solid #000; text-align: right;" colspan="2">
                            <?php echo number_format($grandTotalRiel, 0); ?> ៛
                        </td>
                    </tr>
                </table>
                <table style="width: 100%;">
                    <tr><td style="font-size: 10px;">តម្លៃសំបុត្របូកបញ្ចូលពន្ធអាករលើតម្លៃបន្ថែមរួចជាស្រេច/VAT INCLUDED</td></tr>
                    <tr><td style="font-size: 10px;">អត្រាប្តូរប្រាក់/Exchange Rate: 4,100៛</td></tr>
                    <tr><td style="font-size: 10px;">
                        - ទីតាំងឡើង&លេខទូរស័ព្ទ/Boarding Point & Tel: 
                        <?php 
                        $boardingPointTime = "";
                        if(!empty($rowTicket['t_boarding_point_id'])){
                            $sqlBoardingTime = mysql_query("SELECT CONCAT(HOUR(time),':',MINUTE(time)) AS time FROM t_journey_boarding_points WHERE t_journey_id = ".$rowTicket['t_journey_id']." AND t_boarding_point_id = ".$rowTicket['t_boarding_point_id']);
                        } else {
                            $sqlBoardingTime = mysql_query("SELECT CONCAT(HOUR(time),':',MINUTE(time)) AS time FROM t_journey_boarding_points WHERE t_journey_id = ".$rowTicket['t_journey_id']." LIMIT 1");
                        }
                        if(mysql_num_rows($sqlBoardingTime)){
                            $rowBoardingTime   = mysql_fetch_array($sqlBoardingTime);
                            $boardingPointTime = date('h:i A', strtotime(date("Y-m-d")." ".$rowBoardingTime['time']));
                            // $expTime = explode(":",$rowBoardingTime['time']);
                            // $boardingPointTime = $expTime[0].":".$expTime[1];
                        }
                        echo $rowTicket['boarding'];  ?> (<?php echo $boardingPointTime; ?>) <?php echo $rowTicket['branch_telephone']; ?></td></tr>
                    <tr>
                        <td style="font-size: 10px;">
                            - ទីតាំងចុះ&លេខទូរស័ព្ទ/Drop Off Point & Tel:
                            <?php
                            $dropOffTime = "";
                            if(!empty($rowTicket['t_drop_off_id'])){
                                $sqlDropOffTime = mysql_query("SELECT CONCAT(HOUR(time),':',MINUTE(time)) AS time FROM t_journey_drop_offs WHERE t_journey_id = ".$rowTicket['t_journey_id']." AND t_drop_off_id = ".$rowTicket['t_drop_off_id']);
                            } else {
                                $sqlDropOffTime = mysql_query("SELECT CONCAT(HOUR(time),':',MINUTE(time)) AS time FROM t_journey_drop_offs WHERE t_journey_id = ".$rowTicket['t_journey_id']." LIMIT 1");
                            }
                            if(mysql_num_rows($sqlDropOffTime)){
                                $rowDropOffTime  = mysql_fetch_array($sqlDropOffTime);
                                $dropOffTime     = date('h:i A', strtotime(date("Y-m-d")." ".$rowDropOffTime['time']));
                                // $expTime = explode(":",$rowDropOffTime['time']);
                                // $dropOffTime = $expTime[0].":".$expTime[1];
                            }
                            $sqlBranchTo = mysql_query("SELECT name, telephone FROM branches WHERE id IN (SELECT branch_id FROM branch_destinations WHERE t_destination_id = ".$rowTicket['t_destination_to_id'].") AND company_id = ".$rowTicket['company_id']);
                            $rowBranchTo = mysql_fetch_array($sqlBranchTo);
                            echo $rowTicket['drop_off']." (".$dropOffTime.") ".$rowBranchTo[1];
                            ?>
                        </td>
                    </tr>
                    <tr><td style="font-size: 10px;">ល័ក្ខខ័ណ្ឌ/Term & Condition:</td></tr>
                    <tr><td style="font-size: 10px;">
                        <?php
                        if($rowTicket['is_open_date'] == 1){
                        ?>
                        សូមអតិថិជនទាំងអស់ធ្វើការបញ្ជាក់សំបុត្រត្រលប់1ថ្ងៃមុនចេញដំណើរ។ 
                        <?php
                        }
                        ?>
                        សូមអញ្ជើញមកដល់យ៉ាងហោចណាស់30នាទីមុនពេលការចេញដំណើរ។ សំបុត្រទិញហើយមិនអាចដូរយកប្រាក់វិញបានទេ។ អរគុណចំពោះការប្រើប្រាស់សេវាកម្មយើងខ្ញុំ។</td></tr>
                    <tr><td style="font-size: 10px;">
                        <?php
                        if($rowTicket['is_open_date'] == 1){
                        ?>
                        Please confirm your return ticket one day in advance.
                        <?php
                        }
                        ?>
                        Please arrive at least 30 minutes before departure time. Ticket sold cannot be refund. Thank you for using our service.</td></tr>
                </table>
            </div>
        </div>
        <div style="width: 63%; float: right;">
        <?php
        foreach($seatRecords AS $data){
        ?>
            <div style="width: 47%; float: left; padding-left: 5px; padding-right: 5px; border: 1px solid #000; margin-bottom: 3px; margin-right: 3px;">
                <div class="bgtext">
                    <table style="width: 100%;" cellpadding="0" cellspacing="3">
                        <tr><td style="width: 60%; font-size: 10px;">លេខរៀងវិក្កយបត្រ/Invoice No.:<br/><?php echo $rowTicket['code']."_".$data['number']; ?></td>
                            <td rowspan="6"><input type="hidden" class="qrCodeTicket" value="<?php echo $rowTicket['code']."_".$data['number']; ?>" /><div class="cardQRCode"></div></td></tr>
                        <tr><td style="font-size: 10px;">ទិសដៅ/Direction:<br/><?php echo $destinationFromName." -> ".$destinationToName; ?></td></tr>
                        <tr><td style="font-size: 10px;">លេខកៅអី/Seat No.: <?php echo $data['number']; ?></td></tr>
                        <tr><td style="font-size: 10px;">អតិថិជន/Customer: <?php echo $customerType; ?></td></tr>
                        <tr><td style="font-size: 10px;">ថ្ងៃទិញ/Issued Date: <?php echo dateShort($rowTicket['date']); ?></td></tr>
                        <tr><td style="font-size: 10px;">ថ្ងៃ​ធ្វើ​ដំណើរ/Journey Date:<br/>
                            <?php 
                            $depare  = explode(":", $rowTicket['journey_time']);
                            $depareureTime = (int) $depare[0];
                            if(strtotime($rowTicket['journey_date']) >= strtotime("2021-12-07")){
                                echo dateShort($rowTicket['journey_date'])." ".date('h:i A', strtotime($rowTicket['journey_time']));  
                            } else {
                                if(checkDateFrom($rowTicket['branch_id'], $depareureTime) == 1){
                                    echo dateShort($rowTicket['journey_date'])." ".date('h:i A', strtotime($rowTicket['journey_time']));  
                                } else {
                                    echo date("d/m/Y", strtotime("+1 day", strtotime($rowTicket['journey_date'])))." ".date('h:i A', strtotime($rowTicket['journey_time']));  
                                }
                            }
                            ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php
        }
        ?>
            <div style="clear: both;"></div>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div style="clear: both;"></div>
    <br />
    <?php
    if($rowTicket['is_open_date'] == 0){
    ?>
    <fieldset>
        <legend><?php __(TABLE_SEAT_INFORMATION); ?></legend>
        <table cellpadding="5" cellspacing="0" style="width: 100%;" class="table">
            <tr>
                <th class="first" style="width: 6%;">Seat #</th>
                <th>Name</th>
                <th>Gender</th>
                <th>Price</th>
                <th>Dis</th>
                <th>Locked</th>
                <th>Void Date</th>
                <th>Void By</th>
                <th>Release Date</th>
                <th>Release By</th>
                <?php
                if($rowTicket['is_change'] == 1){
                ?>
                <th>Change To</th>
                <?php
                } else  {
                ?>
                <th>Change From</th>
                <?php
                }
                ?>
                <th>Change Date</th>
                <th>Change By</th>
                <th>Status</th>
                <th></th>
            </tr>
            <?php
            $sqlSeat = mysql_query("SELECT * FROM t_ticket_details WHERE t_ticket_id = ".$rowTicket['id']."
                                    UNION ALL
                                    SELECT * FROM t_ticket_detail_3months WHERE t_ticket_id = ".$rowTicket['id']);
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
                <td>
                    <?php 
                    if($rowSeat['is_sync'] == 0){
                        echo 'Locked';
                    } else {
                        echo 'Release';
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if(!empty($rowSeat['modified']) && $rowSeat['modified'] != "0000-00-00 00:00:00" && !empty($rowSeat['modified_by'])){
                        echo dateShort($rowSeat['modified'], "d/m/Y H:i:s"); 
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if(!empty($rowSeat['modified_by'])){
                        $sqlUser = mysql_query("SELECT * FROM users WHERE id = ".$rowSeat['modified_by']);
                        $rowUser = mysql_fetch_array($sqlUser);
                        echo $rowUser['username'];
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if(!empty($rowSeat['release_date']) && $rowSeat['release_date'] != "0000-00-00 00:00:00"){
                        echo dateShort($rowSeat['release_date'], "d/m/Y H:i:s"); 
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if(!empty($rowSeat['release_by'])){
                        $sqlUser = mysql_query("SELECT * FROM users WHERE id = ".$rowSeat['release_by']);
                        $rowUser = mysql_fetch_array($sqlUser);
                        echo $rowUser['username'].' <span onmouseover="Tip(\'' . htmlspecialchars($rowSeat['note'], ENT_QUOTES, 'UTF-8') . '\')"><img alt="Note" src="' . $this->webroot . 'img/button/note.png" /></a></span>';
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if(!empty($rowSeat['change_reference'])){
                        $sqlChange = mysql_query("SELECT * FROM t_tickets WHERE id = ".$rowSeat['change_reference']."
                                                  UNION ALL
                                                  SELECT * FROM t_ticket_3months WHERE id = ".$rowSeat['change_reference']);
                        if(mysql_num_rows($sqlChange)){
                            $rowChange = mysql_fetch_array($sqlChange);
                            echo $rowChange['code'];
                        }
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if(!empty($rowSeat['change_date']) && $rowSeat['change_date'] != "0000-00-00 00:00:00"){
                        echo dateShort($rowSeat['change_date'], "d/m/Y H:i:s"); 
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if(!empty($rowSeat['change_by'])){
                        $sqlUser = mysql_query("SELECT * FROM users WHERE id = ".$rowSeat['change_by']);
                        $rowUser = mysql_fetch_array($sqlUser);
                        echo $rowUser['username'];
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if($rowTicket['status'] > 0){
                        if($rowSeat['is_active'] == 1){
                            if(!empty($rowSeat['change_date'])){
                                echo "Changed Shift";
                            } else {
                                echo "Active";
                            }
                        } else {
                            echo "Void";
                        }
                    } else {
                        if($rowTicket['status'] == -1){
                            echo "Cancelled";
                        } else {
                            echo "Void";
                        }
                    }
                    ?>
                </td>
                <td>
                <?php
                if(empty($rowTicket['t_agent_id'])){
                    if(($allowFullDelete && $rowTicket['status'] > 0 && $rowSeat['is_active'] == 1 && $rowSeat['is_change'] == 0) || ($allowDelete && $rowTicket['status'] > 0 && $rowSeat['is_active'] == 1 && $rowSeat['is_change'] == 0)){
                        echo '<a href="" class="btnDeleteTTicketDetail" rel="' . $rowSeat['id'] . '" name="' . $rowSeat['seat_number'] . '"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>';
                    }
                }
                if($allowReleaseSeat && $rowTicket['status'] > 0 && $rowSeat['is_sync'] == 0 && $rowSeat['is_change'] == 0 && empty($rowSeat['release_date'])){
                    echo ' <a href="" class="btnReleaseTTicketDetail" rel="' . $rowSeat['id'] . '" name="' . $rowSeat['seat_number'] . '"><img alt="Release Seat" onmouseover="Tip(\'Release Seat\')" src="' . $this->webroot . 'img/button/refresh-active.png" /></a>';
                }   
                ?>
                </td>
            </tr>
            <?php
            }
            ?>
        </table>
    </fieldset>
    <?php
    }
    if($user['User']['id'] == 2 && !empty($rowTicket['online_order_id'])){
        $sqlOnline = mysql_query("SELECT api_logs.data_post
                                  FROM online_orders 
                                  INNER JOIN api_logs ON online_orders.api_log_code = api_logs.log_code
                                  WHERE online_orders.id = ".$rowTicket['online_order_id']);
        if(mysql_num_rows($sqlOnline)){
            $rowOnline = mysql_fetch_array($sqlOnline);
    ?>
    <fieldset>
        <legend><?php __("APi Request Data"); ?></legend>
        <div>
            <?php echo $rowOnline['data_post']; ?>
        </div>
    </fieldset>
    <?php
        }
    }
    ?>
 </fieldset>