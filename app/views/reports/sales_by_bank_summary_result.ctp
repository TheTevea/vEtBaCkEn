    <?php
include('includes/function.php');
$rnd       = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport  = "btnExport" . $rnd;

$filename = "public/report/sales_by_bank_summary_result" . $user['User']['id'] . ".csv";
$fp = fopen($filename,"wb");
$excelContent  = REPORT_SALES_TICKET_BRANCH . " (SUMMARY)\n\n";
$excelContent .= "\nName"."\t"."VET Travel"."\t"."VET Air Bus"."\t"."BUVA SEA"."\t"."Balance";
$csvIndex = 0;
$companyList[1] = "1,2,8,9,10,15,16";
$companyList[2] = "7,12,13,14";
$companyList[3] = "6,17";
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $btnPrint; ?>").click(function(){
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
        });
        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/sales_by_bank_summary_result<?php echo $user['User']['id']; ?>.csv", "_blank");
        });
    });
</script>
<div class="leftPanel">
    <div id="<?php echo $printArea; ?>">
        <?php
        $msg = '<b style="font-size: 18px;">'.REPORT_SALES_BY_BANK_SUMMARY.'</b><br /><br />';
        $condFilter = " AND t_tickets.offline_project_id = 1";
        $condAgent  = " AND t_tickets.offline_project_id = 1";
        $condPhoneCall = " AND t_tickets.offline_project_id = 1";
        if(!empty($_POST['booking_from'])) {
            $msg .= REPORT_FROM.': '.$_POST['booking_from'];
            $condFilter .= " AND t_tickets.date >= '".dateConvert($_POST['booking_from'])."'";
            $condAgent  .= " AND t_tickets.journey_date >= '".dateConvert($_POST['booking_from'])."'";
            $condPhoneCall .= " AND t_tickets.pay_date >= '".dateConvert($_POST['booking_from'])."'";
        }
        if(!empty($_POST['booking_to'])) {
            $msg .= ' '.REPORT_TO.': '.$_POST['booking_to'];
            $condFilter .= " AND t_tickets.date <= '".dateConvert($_POST['booking_to'])."'";
            $condAgent  .= " AND t_tickets.journey_date <= '".dateConvert($_POST['booking_to'])."'";
            $condPhoneCall .= " AND t_tickets.pay_date <= '".dateConvert($_POST['booking_to'])."'";
        }
        
        $condPackage = "";
        $condTopUp   = "";
        if(!empty($_POST['booking_from'])) {
            $condPackage .= " AND package_date >= '".dateConvert($_POST['booking_from'])."'";
            $condTopUp   .= " AND `date` >= '".dateConvert($_POST['booking_from'])."'";
        }
        if(!empty($_POST['booking_to'])) {
            $condPackage .= " AND package_date <= '".dateConvert($_POST['booking_to'])."'";
            $condTopUp   .= " AND `date` <= '".dateConvert($_POST['booking_to'])."'";
        }
        // Build condition string for queries
        $condition = "t_tickets.status = 2";
        if($_POST['company']!='') {
            if($_POST['company'] == "1,2"){
                $msg .= '<br/>'.MENU_COMPANY_MANAGEMENT.': VET Ticket';
            } else {
                $company = "";
                $comCon  = $_POST['company'];
                if (strpos($_POST['company'], "1,2") !== false) {
                    $company = "VET Ticket,";
                    $comCon  = str_replace("1,2,", "", $_POST['company']);
                }
                $sqlCompany = mysql_query("SELECT GROUP_CONCAT(name) FROM companies WHERE id IN (".$comCon.")");
                $rowCompany = mysql_fetch_array($sqlCompany);
                $company .= $rowCompany[0];
                $msg .= '<br/>'.MENU_COMPANY_MANAGEMENT.': '.$company;
            }
            $condition .= " AND t_tickets.company_id IN (".$_POST['company'].")";
        } else {
            $condition .= " AND t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '" . $user['User']['id']. "')";
        }

        if($_POST['destination_from']!='') {
            $sqlDesFrom = mysql_query("SELECT name FROM t_destinations WHERE id = ".$_POST['destination_from']);
            $rowDesFrom = mysql_fetch_array($sqlDesFrom);
            $msg .= '<br/>'.TABLE_DESTINATION_FROM.': '.$rowDesFrom[0];
            $condition .= " AND t_tickets.t_destination_from_id = ".$_POST['destination_from'];
        }

        if($_POST['destination_to']!='') {
            $sqlDesTo = mysql_query("SELECT name FROM t_destinations WHERE id = ".$_POST['destination_to']);
            $rowDesTo = mysql_fetch_array($sqlDesTo);
            $msg .= '<br/>'.TABLE_DESTINATION_TO.': '.$rowDesTo[0];
            $condition .= " AND t_tickets.t_destination_to_id = ".$_POST['destination_to'];
        }

        if($_POST['destination_group']!='') {
            $condition .= " AND t_destinations.t_destination_group_id = ".$_POST['destination_group'];
        }

        if($_POST['province']!='') {
            $condition .= " AND t_destinations.province_id = ".$_POST['province'];
        }
        echo $this->element('/print/header-report',array('msg'=>$msg));
        ?>
        <div id="dynamic">
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <th class="first" style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: center;"><?php echo "Name"; ?></th>
                        <th style="width: 20%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: center;"><?php echo "VET Travel"; ?></th>
                        <th style="width: 15%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: center;"><?php echo "VET Air Bus"; ?></th>
                        <th style="width: 15%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: center;"><?php echo "BUVA SEA"; ?></th>
                        <th style="width: 15%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: center;"><?php echo "Balance"; ?></th>
                    </tr>
                    <?php
                    $dataBanks = array();
                    // APP
                    $sqlTicketApp = mysql_query("SELECT t_tickets.company_id, IFNULL(t_tickets.payment_method_id, online_orders.payment_method_id) AS payment_method_id,
                                            SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) AS total_amount
                                            FROM 
                                            (
                                                SELECT * FROM t_tickets WHERE t_tickets.status = 2 AND t_tickets.type IN (5,10,11) AND t_tickets.t_agent_id IS NULL AND t_tickets.terminal_id IS NULL".$condFilter."
                                                UNION ALL
                                                SELECT * FROM t_ticket_3months AS t_tickets WHERE t_tickets.status = 2 AND t_tickets.type IN (5,10,11) AND t_tickets.t_agent_id IS NULL AND t_tickets.terminal_id IS NULL".$condFilter."
                                            ) AS t_tickets 
                                            INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                                            LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id
                                            WHERE ".$condition." AND IFNULL(t_tickets.payment_method_id, online_orders.payment_method_id) IN (4,5,6,7,8) GROUP BY payment_method_id, t_tickets.company_id ORDER BY payment_method_id, t_tickets.company_id");
                    if(mysql_num_rows($sqlTicketApp)){
                        while($rowTicketApp = mysql_fetch_array($sqlTicketApp)){
                            $compayIndex = 0;
                            foreach ($companyList as $idx => $list) {
                                $companies = explode(',', $list);
                                if (in_array((string)$rowTicketApp['company_id'], $companies)) { $compayIndex = $idx; break; }
                            }
                            if(array_key_exists($rowTicketApp['payment_method_id'], $dataBanks)){
                                if(array_key_exists($compayIndex, $dataBanks[$rowTicketApp['payment_method_id']])){
                                    $dataBanks[$rowTicketApp['payment_method_id']][$compayIndex] += $rowTicketApp['total_amount'];
                                } else {
                                    $dataBanks[$rowTicketApp['payment_method_id']][$compayIndex] = $rowTicketApp['total_amount'];
                                }
                            } else {
                                $dataBanks[$rowTicketApp['payment_method_id']][$compayIndex] = $rowTicketApp['total_amount'];
                            }
                        }
                    }
                    // Website
                    $sqlTicketWeb = mysql_query("SELECT t_tickets.company_id, IFNULL(t_tickets.payment_method_id, online_orders.payment_method_id) AS payment_method_id,
                                            SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) AS total_amount
                                            FROM 
                                            (
                                                SELECT * FROM t_tickets WHERE t_tickets.status = 2 AND t_tickets.type IN (5,10,11) AND t_tickets.t_agent_id = 55".$condFilter."
                                                UNION ALL
                                                SELECT * FROM t_ticket_3months AS t_tickets WHERE t_tickets.status = 2 AND t_tickets.type IN (5,10,11) AND t_tickets.t_agent_id = 55".$condFilter."
                                            ) AS t_tickets 
                                            INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                                            LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id
                                            WHERE ".$condition." AND IFNULL(t_tickets.payment_method_id, online_orders.payment_method_id) IN (4,5,6,7,8) GROUP BY payment_method_id, t_tickets.company_id ORDER BY payment_method_id, t_tickets.company_id");
                    if(mysql_num_rows($sqlTicketWeb)){
                        while($rowTicketWeb = mysql_fetch_array($sqlTicketWeb)){
                            $compayIndex = 0;
                            foreach ($companyList as $idx => $list) {
                                $companies = explode(',', $list);
                                if (in_array((string)$rowTicketWeb['company_id'], $companies)) { $compayIndex = $idx; break; }
                            }
                            if(array_key_exists($rowTicketWeb['payment_method_id'], $dataBanks)){
                                if(array_key_exists($compayIndex, $dataBanks[$rowTicketWeb['payment_method_id']])){
                                    $dataBanks[$rowTicketWeb['payment_method_id']][$compayIndex] += $rowTicketWeb['total_amount'];
                                } else {
                                    $dataBanks[$rowTicketWeb['payment_method_id']][$compayIndex] = $rowTicketWeb['total_amount'];
                                }
                            } else {
                                $dataBanks[$rowTicketWeb['payment_method_id']][$compayIndex] = $rowTicketWeb['total_amount'];
                            }
                        }
                    }
                    // Terminal
                    $sqlTicketTerminal = mysql_query("SELECT t_tickets.company_id, IFNULL(t_tickets.payment_method_id, online_orders.payment_method_id) AS payment_method_id,
                                            SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) AS total_amount
                                            FROM 
                                            (
                                                SELECT * FROM t_tickets WHERE t_tickets.status = 2 AND t_tickets.terminal_id IS NOT NULL".$condFilter."
                                                UNION ALL
                                                SELECT * FROM t_ticket_3months AS t_tickets WHERE t_tickets.status = 2 AND t_tickets.terminal_id IS NOT NULL".$condFilter."
                                            ) AS t_tickets 
                                            INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                                            LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id
                                            WHERE ".$condition." AND IFNULL(t_tickets.payment_method_id, online_orders.payment_method_id) IN (4,5,6,7,8) GROUP BY payment_method_id, t_tickets.company_id ORDER BY payment_method_id, t_tickets.company_id");
                    if(mysql_num_rows($sqlTicketTerminal)){
                        while($rowTicketTerminal = mysql_fetch_array($sqlTicketTerminal)){
                            $compayIndex = 0;
                            foreach ($companyList as $idx => $list) {
                                $companies = explode(',', $list);
                                if (in_array((string)$rowTicketTerminal['company_id'], $companies)) { $compayIndex = $idx; break; }
                            }
                            if(array_key_exists($rowTicketTerminal['payment_method_id'], $dataBanks)){
                                if(array_key_exists($compayIndex, $dataBanks[$rowTicketTerminal['payment_method_id']])){
                                    $dataBanks[$rowTicketTerminal['payment_method_id']][$compayIndex] += $rowTicketTerminal['total_amount'];
                                } else {
                                    $dataBanks[$rowTicketTerminal['payment_method_id']][$compayIndex] = $rowTicketTerminal['total_amount'];
                                }
                            } else {
                                $dataBanks[$rowTicketTerminal['payment_method_id']][$compayIndex] = $rowTicketTerminal['total_amount'];
                            }
                        }
                    }
                    
                    // Phone Call Payment
                    $sqlTicketPhoneCall = mysql_query("SELECT t_tickets.company_id, IFNULL(t_tickets.payment_method_id, online_orders.payment_method_id) AS payment_method_id,
                                            SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) AS total_amount
                                            FROM 
                                            (
                                                SELECT * FROM t_tickets WHERE t_tickets.status = 2 AND t_tickets.type = 2 AND t_tickets.api_bank_ref != ''".$condPhoneCall."
                                                UNION ALL
                                                SELECT * FROM t_ticket_3months AS t_tickets WHERE t_tickets.status = 2 AND t_tickets.type = 2 AND t_tickets.api_bank_ref != ''".$condPhoneCall."
                                            ) AS t_tickets 
                                            INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                                            LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id
                                            WHERE ".$condition." AND IFNULL(t_tickets.payment_method_id, online_orders.payment_method_id) IN (4,5,6,7,8) GROUP BY payment_method_id, t_tickets.company_id ORDER BY payment_method_id, t_tickets.company_id");
                    if(mysql_num_rows($sqlTicketPhoneCall)){
                        while($rowTicketPhoneCall = mysql_fetch_array($sqlTicketPhoneCall)){
                            $compayIndex = 0;
                            foreach ($companyList as $idx => $list) {
                                $companies = explode(',', $list);
                                if (in_array((string)$rowTicketPhoneCall['company_id'], $companies)) { $compayIndex = $idx; break; }
                            }
                            if(array_key_exists($rowTicketPhoneCall['payment_method_id'], $dataBanks)){
                                if(array_key_exists($compayIndex, $dataBanks[$rowTicketPhoneCall['payment_method_id']])){
                                    $dataBanks[$rowTicketPhoneCall['payment_method_id']][$compayIndex] += $rowTicketPhoneCall['total_amount'];
                                } else {
                                    $dataBanks[$rowTicketPhoneCall['payment_method_id']][$compayIndex] = $rowTicketPhoneCall['total_amount'];
                                }
                            } else {
                                $dataBanks[$rowTicketPhoneCall['payment_method_id']][$compayIndex] = $rowTicketPhoneCall['total_amount'];
                            }
                        }
                    }
                    // ABA Mini App
                    $dataBanksMiniApp = array();
                    $sqlTicketMiniApp = mysql_query("SELECT t_tickets.company_id,
                                            SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) AS total_amount
                                            FROM 
                                            (
                                                SELECT * FROM t_tickets WHERE t_tickets.status = 2 AND t_tickets.payment_method_id = 5 AND ((t_tickets.type = 5 OR t_tickets.type = 10 OR t_tickets.type = 11) AND t_tickets.t_agent_id = 106)".$condFilter."
                                                UNION ALL
                                                SELECT * FROM t_ticket_3months AS t_tickets WHERE t_tickets.status = 2 AND t_tickets.payment_method_id = 5 AND ((t_tickets.type = 5 OR t_tickets.type = 10 OR t_tickets.type = 11) AND t_tickets.t_agent_id = 106)".$condFilter."
                                            ) AS t_tickets 
                                            INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                                            WHERE ".$condition." GROUP BY t_tickets.company_id ORDER BY t_tickets.company_id");
                    if(mysql_num_rows($sqlTicketMiniApp)){
                        while($rowTicketMiniApp = mysql_fetch_array($sqlTicketMiniApp)){
                            $compayIndex = 0;
                            foreach ($companyList as $idx => $list) {
                                $companies = explode(',', $list);
                                if (in_array((string)$rowTicketMiniApp['company_id'], $companies)) { $compayIndex = $idx; break; }
                            }
                            if(array_key_exists($compayIndex, $dataBanksMiniApp)){
                                $dataBanksMiniApp[$compayIndex] += $rowTicketMiniApp['total_amount'];
                            } else {
                                $dataBanksMiniApp[$compayIndex] = $rowTicketMiniApp['total_amount'];
                            }
                        }
                    }

                    // WING Mini App
                    $dataBanksWingMiniApp = array();
                    $sqlTicketWingMiniApp = mysql_query("SELECT t_tickets.company_id,
                                            SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) AS total_amount
                                            FROM 
                                            (
                                                SELECT * FROM t_tickets WHERE t_tickets.status = 2 AND t_tickets.payment_method_id = 4 AND ((t_tickets.type = 5 OR t_tickets.type = 10 OR t_tickets.type = 11) AND t_tickets.t_agent_id = 106)".$condFilter."
                                                UNION ALL
                                                SELECT * FROM t_ticket_3months AS t_tickets WHERE t_tickets.status = 2 AND t_tickets.payment_method_id = 4 AND ((t_tickets.type = 5 OR t_tickets.type = 10 OR t_tickets.type = 11) AND t_tickets.t_agent_id = 106)".$condFilter."
                                            ) AS t_tickets 
                                            INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                                            WHERE ".$condition." GROUP BY t_tickets.company_id ORDER BY t_tickets.company_id");
                    if(mysql_num_rows($sqlTicketWingMiniApp)){
                        while($rowTicketWingMiniApp = mysql_fetch_array($sqlTicketWingMiniApp)){
                            $compayIndex = 0;
                            foreach ($companyList as $idx => $list) {
                                $companies = explode(',', $list);
                                if (in_array((string)$rowTicketWingMiniApp['company_id'], $companies)) { $compayIndex = $idx; break; }
                            }
                            if(array_key_exists($compayIndex, $dataBanksWingMiniApp)){
                                $dataBanksWingMiniApp[$compayIndex] += $rowTicketWingMiniApp['total_amount'];
                            } else {
                                $dataBanksWingMiniApp[$compayIndex] = $rowTicketWingMiniApp['total_amount'];
                            }
                        }
                    }

                    $dataBanksTravelPackage = 0;
                    $sqlTravelPackageOrder  = mysql_query("SELECT SUM(package_price) AS total_amount FROM travel_package_orders WHERE status = 2".$condPackage);
                    if(mysql_num_rows($sqlTravelPackageOrder)){
                        while($rowTravelPackageOrder = mysql_fetch_array($sqlTravelPackageOrder)){
                            $dataBanksTravelPackage += $rowTravelPackageOrder['total_amount'];
                        }
                    }

                    $dataAgentTopUp = 0;
                    $sqlAgentTopUp  = mysql_query("SELECT SUM(IF(type = 1, (amount * -1), amount)) AS total_amount FROM agency_topups WHERE status = 1".$condTopUp);
                    if(mysql_num_rows($sqlAgentTopUp)){
                        while($rowAgentTopUp = mysql_fetch_array($sqlAgentTopUp)){
                            $dataAgentTopUp += $rowAgentTopUp['total_amount'];
                        }
                    }

                    $dataVETAppAPi = array();
                    $sqlTicketAgentVETAppApi = mysql_query("SELECT t_tickets.t_agent_id AS id, t_tickets.company_id,
                                                    SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) AS total_net 
                                                    FROM 
                                                    (
                                                        SELECT * FROM t_tickets WHERE t_tickets.status = 2 AND t_tickets.t_agent_id = 47 AND t_tickets.online_order_id IS NOT NULL AND t_tickets.booking_type = 2".$condFilter."
                                                        UNION ALL
                                                        SELECT * FROM t_ticket_3months AS t_tickets WHERE t_tickets.status = 2 AND t_tickets.t_agent_id = 47 AND t_tickets.online_order_id IS NOT NULL AND t_tickets.booking_type = 2".$condFilter."
                                                    ) AS t_tickets 
                                                    INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                                                    WHERE ".$condition."
                                                    GROUP BY t_tickets.company_id");
                    if(mysql_num_rows($sqlTicketAgentVETAppApi)){
                        while($rowTicketAgentVETAppApi = mysql_fetch_array($sqlTicketAgentVETAppApi)){
                            $compayIndex = 0;
                            foreach ($companyList as $idx => $list) {
                                $companies = explode(',', $list);
                                if (in_array((string)$rowTicketAgentVETAppApi['company_id'], $companies)) { $compayIndex = $idx; break; }
                            }
                            if(array_key_exists($rowTicketAgentVETAppApi['id'], $dataVETAppAPi)){
                                if(array_key_exists($compayIndex, $dataVETAppAPi[$rowTicketAgentVETAppApi['id']])){
                                    $dataVETAppAPi[$rowTicketAgentVETAppApi['id']][$compayIndex]['total_net'] += $rowTicketAgentVETAppApi['total_net'];
                                } else {
                                    $dataVETAppAPi[$rowTicketAgentVETAppApi['id']][$compayIndex]['total_net'] = $rowTicketAgentVETAppApi['total_net'];
                                }
                            } else {
                                $dataVETAppAPi[$rowTicketAgentVETAppApi['id']]['name'] = "VET Digital (Air Bus) Gross";
                                $dataVETAppAPi[$rowTicketAgentVETAppApi['id']][$compayIndex]['total_net'] = $rowTicketAgentVETAppApi['total_net'];
                            }
                        }
                    }

                    $dataAgents = array();
                    // VET Digital (BMB)
                    $sqlTicketAgent = mysql_query("SELECT t_agents.id, t_agents.name, t_tickets.company_id,
                                                    SUM((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) - (((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) * t_agents.commission) / 100)) AS total_net 
                                                    FROM 
                                                    (
                                                        SELECT * FROM t_tickets WHERE t_tickets.status = 2 AND t_tickets.t_agent_id = 47 AND t_tickets.online_order_id IS NOT NULL AND t_tickets.booking_type = 1".$condAgent."
                                                        UNION ALL
                                                        SELECT * FROM t_ticket_3months AS t_tickets WHERE t_tickets.status = 2 AND t_tickets.t_agent_id = 47 AND t_tickets.online_order_id IS NOT NULL AND t_tickets.booking_type = 1".$condAgent."
                                                    ) AS t_tickets 
                                                    INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                                                    INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id
                                                    WHERE ".$condition."
                                                    GROUP BY t_agents.id, t_tickets.company_id ORDER BY t_agents.name");
                    if(mysql_num_rows($sqlTicketAgent)){
                        while($rowTicketAgent = mysql_fetch_array($sqlTicketAgent)){
                            $compayIndex = 0;
                            foreach ($companyList as $idx => $list) {
                                $companies = explode(',', $list);
                                if (in_array((string)$rowTicketAgent['company_id'], $companies)) { $compayIndex = $idx; break; }
                            }
                            if(array_key_exists($rowTicketAgent['id'], $dataAgents)){
                                if(array_key_exists($compayIndex, $dataAgents[$rowTicketAgent['id']])){
                                    $dataAgents[$rowTicketAgent['id']][$compayIndex]['total_net'] += $rowTicketAgent['total_net'];
                                } else {
                                    $dataAgents[$rowTicketAgent['id']][$compayIndex]['total_net'] = $rowTicketAgent['total_net'];
                                }
                            } else {
                                $dataAgents[$rowTicketAgent['id']]['name'] = $rowTicketAgent['name']." (BMB)";
                                $dataAgents[$rowTicketAgent['id']][$compayIndex]['total_net'] = $rowTicketAgent['total_net'];
                            }
                        }
                    }
                    // Agent API Postpaid
                    $sqlTicketAgent = mysql_query("SELECT t_agents.id, t_agents.name, t_tickets.company_id,
                                                    SUM((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) - (((IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)) * t_agents.commission) / 100)) AS total_net 
                                                    FROM 
                                                    (
                                                        SELECT * FROM t_tickets WHERE t_tickets.status = 2 AND t_tickets.t_agent_id IS NOT NULL AND t_tickets.t_agent_id != 106 AND t_tickets.t_agent_id != 55 AND t_tickets.t_agent_id != 47".$condAgent."
                                                        UNION ALL
                                                        SELECT * FROM t_ticket_3months AS t_tickets WHERE t_tickets.status = 2 AND t_tickets.t_agent_id IS NOT NULL AND t_tickets.t_agent_id != 106 AND t_tickets.t_agent_id != 55 AND t_tickets.t_agent_id != 47".$condAgent."
                                                    ) AS t_tickets 
                                                    INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                                                    INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id
                                                    WHERE ".$condition." AND t_agents.type = 3 AND t_agents.payment = 2
                                                    GROUP BY t_agents.id, t_tickets.company_id ORDER BY t_agents.name");
                    if(mysql_num_rows($sqlTicketAgent)){
                        while($rowTicketAgent = mysql_fetch_array($sqlTicketAgent)){
                            $compayIndex = 0;
                            foreach ($companyList as $idx => $list) {
                                $companies = explode(',', $list);
                                if (in_array((string)$rowTicketAgent['company_id'], $companies)) { $compayIndex = $idx; break; }
                            }
                            if(array_key_exists($rowTicketAgent['id'], $dataAgents)){
                                if(array_key_exists($compayIndex, $dataAgents[$rowTicketAgent['id']])){
                                    $dataAgents[$rowTicketAgent['id']][$compayIndex]['total_net'] += $rowTicketAgent['total_net'];
                                } else {
                                    $dataAgents[$rowTicketAgent['id']][$compayIndex]['total_net'] = $rowTicketAgent['total_net'];
                                }
                            } else {
                                $dataAgents[$rowTicketAgent['id']]['name'] = $rowTicketAgent['name'];
                                $dataAgents[$rowTicketAgent['id']][$compayIndex]['total_net'] = $rowTicketAgent['total_net'];
                            }
                        }
                    }

                    $amtVET = 0;
                    $amtAirBus = 0;
                    $amtBuvaSea = 0;
                    $totalVET     = 0;
                    $totalAirBus  = 0;
                    $totalBuvaSea = 0;
                    $totalBalance = 0;
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo "Website & Mobile App Booking & Terminal & Phone call Payment (ABA)";
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($dataBanks[5]) || !empty($dataBanks[6]) || !empty($dataBanks[7])){
                                if(!empty($dataBanks[5])){
                                    if(!empty($dataBanks[5][1])){
                                        $amtVET   += $dataBanks[5][1];
                                    }
                                }
                                if(!empty($dataBanks[6])){
                                    if(!empty($dataBanks[6][1])){
                                        $amtVET   += $dataBanks[6][1];
                                    }
                                }
                                if(!empty($dataBanks[7])){
                                    if(!empty($dataBanks[7][1])){
                                        $amtVET   += $dataBanks[7][1];
                                    }
                                }
                            }
                            echo number_format($amtVET, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            if(!empty($dataBanks[5]) || !empty($dataBanks[6]) || !empty($dataBanks[7])){
                                if(!empty($dataBanks[5])){
                                    if(!empty($dataBanks[5][2])){
                                        $amtAirBus   += $dataBanks[5][2];
                                    }
                                }
                                if(!empty($dataBanks[6])){
                                    if(!empty($dataBanks[6][2])){
                                        $amtAirBus   += $dataBanks[6][2];
                                    }
                                }
                                if(!empty($dataBanks[7])){
                                    if(!empty($dataBanks[7][2])){
                                        $amtAirBus   += $dataBanks[7][2];
                                    }
                                }
                            }
                            echo number_format($amtAirBus, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($dataBanks[5]) || !empty($dataBanks[6]) || !empty($dataBanks[7])){
                                if(!empty($dataBanks[5])){
                                    if(!empty($dataBanks[5][3])){
                                        $amtBuvaSea   += $dataBanks[5][3];
                                    }
                                }
                                if(!empty($dataBanks[6])){
                                    if(!empty($dataBanks[6][3])){
                                        $amtBuvaSea   += $dataBanks[6][3];
                                    }
                                }
                                if(!empty($dataBanks[7])){
                                    if(!empty($dataBanks[7][3])){
                                        $amtBuvaSea   += $dataBanks[7][3];
                                    }
                                }
                            }
                            echo number_format($amtBuvaSea, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            $balance       = $amtVET + $amtAirBus + $amtBuvaSea; 
                            $totalVET     += $amtVET;
                            $totalAirBus  += $amtAirBus;
                            $totalBuvaSea += $amtBuvaSea;
                            $totalBalance += $balance;
                            echo number_format($balance, 2); 
                            $excelContent .= "\nWebsite & Mobile App Booking & Terminal & Phone call Payment (ABA)\t".number_format($amtVET, 2)."\t".number_format($amtAirBus, 2)."\t".number_format($amtBuvaSea, 2)."\t".number_format($balance, 2);
                            ?>
                        </td>
                    </tr>
                    <?php
                    $amtVET = 0;
                    $amtAirBus = 0;
                    $amtBuvaSea = 0;
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo "Website & Mobile App Booking & Terminal & Phone call Payment (ACLEDA)";
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($dataBanks[8])){
                                if(!empty($dataBanks[8][1])){
                                    $amtVET = $dataBanks[8][1];
                                }
                            }
                            echo number_format($amtVET, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            if(!empty($dataBanks[8])){
                                if(!empty($dataBanks[8][2])){
                                    $amtAirBus = $dataBanks[8][2];
                                }
                            }
                            echo number_format($amtAirBus, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($dataBanks[8])){
                                if(!empty($dataBanks[8][3])){
                                    $amtBuvaSea = $dataBanks[8][3];
                                }
                            }
                            echo number_format($amtBuvaSea, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            $balance       = $amtVET + $amtAirBus + $amtBuvaSea; 
                            $totalVET     += $amtVET;
                            $totalAirBus  += $amtAirBus;
                            $totalBuvaSea += $amtBuvaSea;
                            $totalBalance += $balance;
                            echo number_format($balance, 2); 
                            $excelContent .= "\nWebsite & Mobile App Booking & Terminal & Phone call Payment (ACLEDA)\t".number_format($amtVET, 2)."\t".number_format($amtAirBus, 2)."\t".number_format($amtBuvaSea, 2)."\t".number_format($balance, 2);
                            ?>
                        </td>
                    </tr>
                    <?php
                    $amtVET = 0;
                    $amtAirBus = 0;
                    $amtBuvaSea = 0;
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo "Website & Mobile App Booking & Terminal & Phone call Payment (WING)";
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($dataBanks[4])){
                                if(!empty($dataBanks[4][1])){
                                    $amtVET = $dataBanks[4][1];
                                }
                            }
                            echo number_format($amtVET, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            if(!empty($dataBanks[4])){
                                if(!empty($dataBanks[4][2])){
                                    $amtAirBus = $dataBanks[4][2];
                                }
                            }
                            echo number_format($amtAirBus, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($dataBanks[4])){
                                if(!empty($dataBanks[4][3])){
                                    $amtBuvaSea = $dataBanks[4][3];
                                }
                            }
                            echo number_format($amtBuvaSea, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            $balance       = $amtVET + $amtAirBus + $amtBuvaSea; 
                            $totalVET     += $amtVET;
                            $totalAirBus  += $amtAirBus;
                            $totalBuvaSea += $amtBuvaSea;
                            $totalBalance += $balance;
                            echo number_format($balance, 2); 
                            $excelContent .= "\nWebsite & Mobile App Booking & Terminal & Phone call Payment (WING)\t".number_format($amtVET, 2)."\t".number_format($amtAirBus, 2)."\t".number_format($amtBuvaSea, 2)."\t".number_format($balance, 2);
                            ?>
                        </td>
                    </tr>
                    <?php
                    $amtVET = 0;
                    $amtAirBus = 0;
                    $amtBuvaSea = 0;
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo "Mini App ABA_ Select all Companies to VET (2)";
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($dataBanksMiniApp[1])){
                                $amtVET = $dataBanksMiniApp[1];
                            }
                            echo number_format($amtVET, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($dataBanksMiniApp[2])){
                                $amtAirBus = $dataBanksMiniApp[2];
                            }
                            echo number_format($amtAirBus, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($dataBanksMiniApp[3])){
                                $amtBuvaSea = $dataBanksMiniApp[3];
                            }
                            echo number_format($amtBuvaSea, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            $balance       = $amtVET + $amtAirBus + $amtBuvaSea; 
                            $totalVET     += $amtVET;
                            $totalAirBus  += $amtAirBus;
                            $totalBuvaSea += $amtBuvaSea;
                            $totalBalance += $balance;
                            echo number_format($balance, 2); 
                            $excelContent .= "\nMini App ABA_ Select all Companies to VET (2)\t".number_format($amtVET, 2)."\t".number_format($amtAirBus, 2)."\t".number_format($amtBuvaSea, 2)."\t".number_format($balance, 2);
                            ?>
                        </td>
                    </tr>
                    <?php
                    $amtVET = 0;
                    $amtAirBus = 0;
                    $amtBuvaSea = 0;
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo "Mini App Wing- Select all Companies to VET (2)";
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($dataBanksWingMiniApp[1])){
                                $amtVET = $dataBanksWingMiniApp[1];
                            }
                            echo number_format($amtVET, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($dataBanksWingMiniApp[2])){
                                $amtAirBus = $dataBanksWingMiniApp[2];
                            }
                            echo number_format($amtAirBus, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($dataBanksWingMiniApp[3])){
                                $amtBuvaSea = $dataBanksWingMiniApp[3];
                            }
                            echo number_format($amtBuvaSea, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            $balance       = $amtVET + $amtAirBus + $amtBuvaSea; 
                            $totalVET     += $amtVET;
                            $totalAirBus  += $amtAirBus;
                            $totalBuvaSea += $amtBuvaSea;
                            $totalBalance += $balance;
                            echo number_format($balance, 2); 
                            $excelContent .= "\nMini App Wing- Select all Companies to VET (2)\t".number_format($amtVET, 2)."\t".number_format($amtAirBus, 2)."\t".number_format($amtBuvaSea, 2)."\t".number_format($balance, 2);
                            ?>
                        </td>
                    </tr>
                    <?php
                    $amtVET = 0;
                    $amtAirBus = 0;
                    $amtBuvaSea = 0;
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo "Package Travel, pay to Vireak Buntham Account (3)";
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            $amtVET = $dataBanksTravelPackage;
                            echo number_format($amtVET, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            echo number_format($amtAirBus, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            echo number_format($amtBuvaSea, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            $balance       = $amtVET + $amtAirBus + $amtBuvaSea; 
                            $totalVET     += $amtVET;
                            $totalAirBus  += $amtAirBus;
                            $totalBuvaSea += $amtBuvaSea;
                            $totalBalance += $balance;
                            echo number_format($balance, 2); 
                            $excelContent .= "\nPackage Travel, pay to Vireak Buntham Account (3)\t".number_format($amtVET, 2)."\t".number_format($amtAirBus, 2)."\t".number_format($amtBuvaSea, 2)."\t".number_format($balance, 2);
                            ?>
                        </td>
                    </tr>
                    <?php
                    $amtVET = 0;
                    $amtAirBus = 0;
                    $amtBuvaSea = 0;
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo "Agency Top Up";
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            $amtVET = $dataAgentTopUp;
                            echo number_format($amtVET, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            echo number_format($amtAirBus, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            echo number_format($amtBuvaSea, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            $balance       = $amtVET + $amtAirBus + $amtBuvaSea; 
                            $totalVET     += $amtVET;
                            $totalAirBus  += $amtAirBus;
                            $totalBuvaSea += $amtBuvaSea;
                            $totalBalance += $balance;
                            echo number_format($balance, 2); 
                            $excelContent .= "\nAgency Top Up\t".number_format($amtVET, 2)."\t".number_format($amtAirBus, 2)."\t".number_format($amtBuvaSea, 2)."\t".number_format($balance, 2);
                            ?>
                        </td>
                    </tr>
                    <?php
                    if(!empty($dataVETAppAPi)){
                        foreach($dataVETAppAPi as $data){
                            $amtVET = 0;
                            $amtAirBus = 0;
                            $amtBuvaSea = 0;
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo $data['name'];
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($data[1]['total_net'])){
                                $amtVET = $data[1]['total_net'];
                            }
                            echo number_format($amtVET, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($data[2]['total_net'])){
                                $amtAirBus = $data[2]['total_net'];
                            }
                            echo number_format($amtAirBus, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($data[3]['total_net'])){
                                $amtBuvaSea = $data[3]['total_net'];
                            }
                            echo number_format($amtBuvaSea, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            $balance       = $amtVET + $amtAirBus + $amtBuvaSea; 
                            $totalVET     += $amtVET;
                            $totalAirBus  += $amtAirBus;
                            $totalBuvaSea += $amtBuvaSea;
                            $totalBalance += $balance;
                            echo number_format($balance, 2); 
                            $excelContent .= "\n".$data['name']." (BMB)\t".number_format($amtVET, 2)."\t".number_format($amtAirBus, 2)."\t".number_format($amtBuvaSea, 2)."\t".number_format($balance, 2);
                            ?>
                        </td>
                    </tr>
                    <?php
                        }
                    }
                    if(!empty($dataAgents)){
                        foreach($dataAgents as $data){
                            $amtVET = 0;
                            $amtAirBus = 0;
                            $amtBuvaSea = 0;
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo $data['name'];
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($data[1]['total_net'])){
                                $amtVET = $data[1]['total_net'];
                            }
                            echo number_format($amtVET, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($data[2]['total_net'])){
                                $amtAirBus = $data[2]['total_net'];
                            }
                            echo number_format($amtAirBus, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php
                            if(!empty($data[3]['total_net'])){
                                $amtBuvaSea = $data[3]['total_net'];
                            }
                            echo number_format($amtBuvaSea, 2);
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            $balance       = $amtVET + $amtAirBus + $amtBuvaSea; 
                            $totalVET     += $amtVET;
                            $totalAirBus  += $amtAirBus;
                            $totalBuvaSea += $amtBuvaSea;
                            $totalBalance += $balance;
                            echo number_format($balance, 2); 
                            $excelContent .= "\n".$data['name']."\t".number_format($amtVET, 2)."\t".number_format($amtAirBus, 2)."\t".number_format($amtBuvaSea, 2)."\t".number_format($balance, 2);
                            ?>
                        </td>
                    </tr>
                    <?php
                        }
                    }
                    ?>
                    <tr>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"><?php echo TABLE_TOTAL; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"><?php echo number_format($totalVET, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"><?php echo number_format($totalAirBus, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"><?php echo number_format($totalBuvaSea, 2); ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"><?php echo number_format($totalBalance, 2); 
                        $excelContent .= "\n".TABLE_TOTAL."\t".number_format($totalVET, 2)."\t".number_format($totalAirBus, 2)."\t".number_format($totalBuvaSea, 2)."\t".number_format($totalBalance, 2); ?></td>
                    </tr>
                </tbody>
            </table>
            <br/>
            Report sales Ticket online, and report of
            <br/><br/>
            Report 1+2+3
            <br/><br/>
            <?php
            $excelContent .= "\nReport sales Ticket online, and report of";
            $excelContent .= "\nReport 1+2+3";
            $excelContent .= "\nAccount Balance as ending of month\tUSD\tKHR\t\t1. Report Sales Ticket Online";
            $excelContent .= "\nABA VET\t\t\t\t2. Report Agency Top Up Balance";
            $excelContent .= "\nABA Air Bus\t\t\t\t3. Report Travel Package (Bought)";
            $excelContent .= "\nABA Buva Sea\t\t\t\t4. Report Agency Ticket";
            $excelContent .= "\nAcleda VET\t\t\t";
            $excelContent .= "\nAcleda Air Bus\t\t\t";
            $excelContent .= "\nAcleda Buva Sea\t\t\t";
            $excelContent .= "\nWing\t\t\t";
            $excelContent .= "\nVET Group\tVET Travel\t\tVET Logistics";
            $excelContent .= "\nTransferred to Acleda Bank Account\tTransferred\tNot yet Transferred\tTransferred\tNot yet Transferred";
            $excelContent .= "\nPP KKN\t\t\t";
            $excelContent .= "\nPP NM\t\t\t";
            $excelContent .= "\nPP Olympic\t\t\t";
            $excelContent .= "\nTotal Branches Transferred\t\t\t";
            $excelContent .= "\nDelivery\t\t\t";
            ?>
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: center;"><?php echo "Account Balance as ending of month"; ?></th>
                        <th style="width: 20%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: center;"><?php echo "USD"; ?></th>
                        <th style="width: 15%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: center;"><?php echo "KHR"; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;" colspan="2"><?php echo "1. Report Sales Ticket Online"; ?></th>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><?php echo "ABA VET"; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;" colspan="2">2. Report Agency Top Up Balance</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><?php echo "ABA Air Bus"; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;" colspan="2">3. Report Travel Package (Bought)</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><?php echo "ABA Buva Sea"; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;" colspan="2">4. Report Agency Ticket</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><?php echo "Acleda VET"; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;" colspan="2"></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><?php echo "Acleda Air bus"; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;" colspan="2"></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><?php echo "Acleda Buva sea"; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;" colspan="2"></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><?php echo "WING"; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;" colspan="2"></td>
                    </tr>
                    <tr>
                        <td style="text-align: right; font-size: 12px; font-weight: bold;"><?php echo "VET Group"; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: center;" colspan="2">VET Travel</td>
                        <td style="font-size: 12px; font-weight: bold; text-align: center;" colspan="2">VET Logistics</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><?php echo "Transferred to Acleda Bank Account"; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: center;">Transferred</td>
                        <td style="font-size: 12px; font-weight: bold; text-align: center;">Not yet Transferred</td>
                        <td style="font-size: 12px; font-weight: bold; text-align: center; width: 15%;">Transferred</td>
                        <td style="font-size: 12px; font-weight: bold; text-align: center; width: 15%;">Not yet Transferred</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><?php echo "PP KKN"; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right; width: 15%;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right; width: 15%;"></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><?php echo "PP NM"; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right; width: 15%;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right; width: 15%;"></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><?php echo "PP Olympic"; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right; width: 15%;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right; width: 15%;"></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><?php echo "Total Branches Transferred"; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right; width: 15%;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right; width: 15%;"></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><?php echo "Delivery"; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right; width: 15%;"></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right; width: 15%;"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div style="clear: both;"></div>
    <?php
    $excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
    fwrite($fp,$excelContent);
    fclose($fp);
    ?>
    <br />
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
    <div style="clear: both;"></div>
</div>
<div class="rightPanel"></div>