<?php
include('includes/function.php');
?>
<script type="text/javascript">
    $(document).ready(function(){
        
    });
</script>
<div class="leftPanel">
    <?php
    $condition = "";
    if($agency != 'all' && $agency != '') {
        $condition .= " AND t_tickets.t_agent_id = ".$agency;
    }
    ?>
    <div id="dynamic">
        <table class="table_print" cellspacing="0">
            <tbody>
                <tr>
                    <th class="first" style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;"><?php echo TABLE_NO; ?></th>
                    <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo "Agency Name"; ?></th>
                    <th style="width: 7%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo "Ticket Code"; ?></th>
                    <th style="width: 7%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo "Transaction ID"; ?></th>
                    <th style="width: 7%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo "Reference"; ?></th>
                    <th style="width: 7%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo "Seat #"; ?></th>
                    <th style="width: 10%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo "From"; ?></th>
                    <th style="width: 10%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo "To"; ?></th>
                    <th style="width: 10%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo "Departure"; ?></th>
                    <th style="width: 7%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo "Telephone"; ?></th>
                    <th style="width: 7%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo "Email"; ?></th>
                    <th style="width: 8%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo GENERAL_AMOUNT; ?></th>
                </tr>
                <?php
                $sqlProvince = mysql_query("SELECT * FROM t_destinations WHERE id IN (SELECT t_destination_id FROM main_branches WHERE id = ".$user['User']['main_branch_id'].") LIMIT 1");
                if(mysql_num_rows($sqlProvince)){
                    $rowProvince = mysql_fetch_array($sqlProvince );
                    $index = 0;
                    $datas = array();
                    $sqlTicket = mysql_query("SELECT 
                                                t_agents.id, t_agents.name AS agency_name, 
                                                t_tickets.id,
                                                (t_tickets.total_amount + t_tickets.total_vat - t_tickets.discount_amount) AS total_amount, 
                                                t_tickets.code,
                                                t_tickets.agt_refer_code,
                                                t_tickets.code,
                                                t_tickets.telephone,
                                                t_tickets.email,
                                                t_tickets.journey_date,
                                                t_tickets.journey_time,
                                                online_orders.code AS transaction_code,
                                                t_destinations.name AS dest_from,
                                                destTo.name AS date_to
                                                FROM t_tickets 
                                                INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.type IN (1,3) AND t_agents.id != 106 AND t_agents.id != 55
                                                INNER JOIN t_destinations ON t_destinations.id =  t_tickets.t_destination_from_id AND t_destinations.province_id = ".$rowProvince['province_id']."
                                                INNER JOIN t_destinations AS destTo ON destTo.id =  t_tickets.t_destination_to_id
                                                LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id
                                                WHERE t_tickets.`journey_date` = '".date("Y-m-d")."' AND t_tickets.offline_project_id = 1 AND t_tickets.`status` = 2".$condition."
                                                ORDER BY t_agents.name ASC");
                    if(mysql_num_rows($sqlTicket)){
                        while($rowTicket = mysql_fetch_array($sqlTicket)){
                            $sqlSeat = mysql_query("SELECT GROUP_CONCAT(label_number) FROM t_ticket_details WHERE t_ticket_id = ".$rowTicket['id']);
                            $rowSeat = mysql_fetch_array($sqlSeat);
                ?>
                <tr>
                    <td style="padding: 5px; font-size: 12px; text-align: center;">
                        <?php 
                        echo ++$index;
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowTicket['agency_name'];
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowTicket['code'];
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowTicket['transaction_code'];
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowTicket['agt_refer_code'];
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowSeat[0]; 
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowTicket['dest_from'];
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowTicket['date_to'];
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo dateShort($rowTicket['journey_date'])." ".$rowTicket['journey_time'];
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowTicket['telephone'];
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $rowTicket['email'];
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px; text-align: right;">
                        <?php 
                        echo "$ ".number_format($rowTicket['total_amount'], 2); 
                        ?>
                    </td>
                </tr>
                <?php
                        }
                    } else {
                ?>
                <tr>
                    <td colspan="12" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_RECORD; ?></td>
                </tr>
                <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>
<div class="rightPanel"></div>