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
                    <th class="first" style="width: 10%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;"><?php echo TABLE_NO; ?></th>
                    <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo "Agency Name"; ?></th>
                    <th style="width: 20%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo "Total Booked"; ?></th>
                    <th style="width: 20%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo GENERAL_AMOUNT; ?></th>
                </tr>
                <?php
                $sqlProvince = mysql_query("SELECT * FROM t_destinations WHERE id IN (SELECT t_destination_id FROM main_branches WHERE id = ".$user['User']['main_branch_id'].") LIMIT 1");
                if(mysql_num_rows($sqlProvince)){
                    $rowProvince = mysql_fetch_array($sqlProvince );
                    $index = 0;
                    $totalAmt = 0;
                    $totalBooked = 0;
                    $datas = array();
                    $sqlTicket = mysql_query("SELECT t_agents.id, t_agents.name AS agency_name, SUM(t_tickets.total_amount + t_tickets.total_vat - t_tickets.discount_amount) AS total_amount, SUM(t_tickets.total_seat) AS total_seat
                                                FROM t_tickets 
                                                INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.type IN (1,3) AND t_agents.id != 106 AND t_agents.id != 55
                                                INNER JOIN t_destinations ON t_destinations.id =  t_tickets.t_destination_from_id AND t_destinations.province_id = ".$rowProvince['province_id']."
                                                WHERE t_tickets.`journey_date` = '".date("Y-m-d")."' AND t_tickets.offline_project_id = 1 AND t_tickets.`status` = 2".$condition." 
                                                GROUP BY t_tickets.t_agent_id");
                    if(mysql_num_rows($sqlTicket)){
                        while($rowTicket = mysql_fetch_array($sqlTicket)){
                            $key = $rowTicket['id'];
                            if(array_key_exists($key, $datas)){
                                $datas[$key]['total_booked'] += $rowTicket['total_seat'];
                                $datas[$key]['total_amount'] += $rowTicket['total_amount'];
                            } else {
                                $datas[$key]['name'] = $rowTicket['agency_name'];
                                $datas[$key]['total_booked'] = $rowTicket['total_seat'];
                                $datas[$key]['total_amount'] = $rowTicket['total_amount'];
                            }
                        }
                    }
                    if(!empty($datas)){
                        foreach($datas AS $data){
                ?>
                <tr>
                    <td style="padding: 5px; font-size: 12px; text-align: center;">
                        <?php 
                        echo ++$index;
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px;">
                        <?php 
                        echo $data['name'];
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px; text-align: right;">
                        <?php 
                        $totalBooked += $data['total_booked'];
                        echo number_format($data['total_booked'], 0); 
                        ?>
                    </td>
                    <td style="padding: 5px; font-size: 12px; text-align: right;">
                        <?php 
                        $totalAmt += $data['total_amount']; 
                        echo "$ ".number_format($data['total_amount'], 2); 
                        ?>
                    </td>
                </tr>
                <?php
                        }
                ?>
                <tr>
                    <td colspan="2" style="text-align: right;"><?php echo TABLE_TOTAL; ?></td>
                    <td style="font-size: 12px; font-weight: bold; text-align: right;"><?php echo number_format($totalBooked, 0); ?></td>
                    <td style="font-size: 12px; font-weight: bold; text-align: right;">$ <?php echo number_format($totalAmt, 2); ?></td>
                </tr>
                <?php
                    } else {
                ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_RECORD; ?></td>
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