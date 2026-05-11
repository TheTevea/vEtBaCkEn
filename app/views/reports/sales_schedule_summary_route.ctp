<?php
include('includes/function.php');
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
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
            window.open("<?php echo $this->webroot; ?>public/report/sales_journey_summary_route<?php echo $user['User']['id']; ?>.csv", "_blank");
        });
    });
</script>
<div class="leftPanel">
    <div id="<?php echo $printArea; ?>">
        <?php
        /**
         * export to excel
         */
        $filename = "public/report/sales_journey_summary_route" . $user['User']['id'] . ".csv";
        $fp = fopen($filename,"wb");
        $excelContent = REPORT_SALES_JOURNEY_SUMMARY." (By Route)\n";
        $condtion = 't_tickets.status = 2 AND t_ticket_details.is_active = 1 AND t_tickets.offline_project_id = 1';
        $msg  = '<b style="font-size: 18px;">'.REPORT_SALES_JOURNEY_SUMMARY.'</b><br />';
        $logo = "";
        if($_POST['booking_from']!='') {
            $msg .= 'ថ្ងៃទី : '.$_POST['booking_from'];
            $excelContent .= "\nថ្ងៃទី: ".$_POST['booking_from'];
            $condtion .= " AND t_tickets.date >= '".dateConvert($_POST['booking_from'])."'";
        }
        if($_POST['booking_to']!='') {
            $msg .= ' ទៅ: '.$_POST['booking_to'];
            $excelContent .= "\t ទៅ: ".$_POST['booking_to'];
            $condtion .= " AND t_tickets.date <= '".dateConvert($_POST['booking_to'])."'";
        }
        if($_POST['traveling_from']!='') {
            $msg .= 'ថ្ងៃទី : '.$_POST['traveling_from'];
            $excelContent .= "\nថ្ងៃទី: ".$_POST['traveling_from'];
            $condtion .= " AND t_tickets.journey_date >= '".dateConvert($_POST['traveling_from'])."'";
        }
        if($_POST['traveling_to']!='' && $_POST['traveling_to'] != $_POST['traveling_from']) {
            $msg .= ' ទៅ: '.$_POST['traveling_to'];
            $excelContent .= "\t ទៅ: ".$_POST['traveling_to'];
            $condtion .= " AND t_tickets.journey_date >= '".dateConvert($_POST['traveling_to'])."'";
        }
        if($_POST['company'] != '') {
            if($_POST['company'] == "1,2"){
                $msg .= '<br/>ក្រុមហ៊ុន: VET Ticket<br/>';
            } else {
                $sqlCompany = mysql_query("SELECT GROUP_CONCAT(name) AS name FROM companies WHERE id IN (".$_POST['company'].")");
                $rowCompany = mysql_fetch_array($sqlCompany);
                $msg .= '<br/>ក្រុមហ៊ុន: '.$rowCompany[0].'<br/>';
            }
            $sqlLogo = mysql_query("SELECT photo FROM companies WHERE id IN (".$_POST['company'].") LIMIT 1");
            $rowLogo = mysql_fetch_array($sqlLogo);
            $logo    = $rowLogo[0];
            $condtion .= ' AND t_tickets.company_id IN ('.$_POST['company'].')';
        }
        if($_POST['journeyId'] != '') {
            $condtion .= " AND t_tickets.t_journey_id IN (".$_POST['journeyId'].")";
        }
        if($_POST['destination_from'] != '') {
            $condtion .= " AND t_tickets.t_destination_from_id = ".$_POST['destination_from'];
        }
        if($_POST['destination_to'] != '') {
            $condtion .= " AND t_tickets.t_destination_to_id = ".$_POST['destination_to'];
        }
        if($_POST['route_code'] != '') {
            $condtion .= " AND t_journeys.route_code = '".$_POST['route_code']."'";
        }
        $msg .= '<br />ថ្ងៃបោះពុម្ព: '.date("d/m/Y H:i:s");
        echo $this->element('/print/header-report',array('msg' => $msg, 'logo' => $logo));
        $excelContent .= "\n\n".TABLE_NO."\t".TABLE_DESTINATION_FROM."\t".TABLE_DESTINATION_TO."\tKhmer\tForeigners\tTotal";
        ?>
        <div id="dynamic">
            <table class="table_print" cellspacing="0" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="text-align: left; padding: 5px; font-size: 11px; width: 35px;"><?php echo TABLE_NO; ?></th>
                        <th style="text-align: left; padding: 5px; width: 150px !important; font-size: 11px;"><?php echo TABLE_DESTINATION_FROM; ?></th>
                        <th style="text-align: left; padding: 5px; width: 150px !important; font-size: 11px;"><?php echo TABLE_DESTINATION_TO; ?></th>
                        <th style="text-align: right; padding: 5px; width: 90px !important; font-size: 11px;"><?php echo "Khmer"; ?></th>
                        <th style="text-align: right; padding: 5px; width: 110px !important; font-size: 11px;"><?php echo "Foreigners"; ?></th>
                        <th style="text-align: right; padding: 5px; width: 90px !important; font-size: 11px;"><?php echo "Total"; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sqlTicket = mysql_query("SELECT COUNT(t_ticket_details.id) AS customer, 
                                              t_ticket_details.nationally,
                                              t_destinations.name AS destFrom, 
                                              dest_to.name AS destTo,
                                              t_tickets.t_destination_from_id,
                                              t_tickets.t_destination_to_id
                                              FROM t_ticket_details
                                              INNER JOIN t_tickets ON t_tickets.id = t_ticket_details.t_ticket_id
                                              INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id
                                              INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                                              INNER JOIN t_destinations AS dest_to ON dest_to.id = t_tickets.t_destination_to_id
                                              WHERE ".$condtion." 
                                              GROUP BY t_tickets.t_destination_from_id, t_tickets.t_destination_to_id, t_ticket_details.nationally
                                              UNION ALL
                                              SELECT COUNT(t_ticket_details.id) AS customer, 
                                              t_ticket_details.nationally,
                                              t_destinations.name AS destFrom, 
                                              dest_to.name AS destTo,
                                              t_tickets.t_destination_from_id,
                                              t_tickets.t_destination_to_id
                                              FROM t_ticket_detail_3months AS t_ticket_details
                                              INNER JOIN t_ticket_3months AS t_tickets ON t_tickets.id = t_ticket_details.t_ticket_id
                                              INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id
                                              INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
                                              INNER JOIN t_destinations AS dest_to ON dest_to.id = t_tickets.t_destination_to_id
                                              WHERE ".$condtion." 
                                              GROUP BY t_tickets.t_destination_from_id, t_tickets.t_destination_to_id, t_ticket_details.nationally
                                              ORDER BY destFrom, destTo
                                              ");
                    $journeyData = array();
                    while($rowTicket = mysql_fetch_array($sqlTicket)){
                        $key = $rowTicket['t_destination_from_id'].$rowTicket['t_destination_to_id'];
                        if (array_key_exists($key, $journeyData)){
                            if($rowTicket['nationally'] == 1){
                                $journeyData[$key]['khmer']     += $rowTicket['customer'];
                            } else {
                                $journeyData[$key]['foreinger'] += $rowTicket['customer'];
                            }
                        } else {
                            $journeyData[$key]['destFrom']  = $rowTicket['destFrom'];
                            $journeyData[$key]['destTo']    = $rowTicket['destTo'];
                            if($rowTicket['nationally'] == 1){
                                $journeyData[$key]['khmer']     = $rowTicket['customer'];
                                $journeyData[$key]['foreinger'] = 0;
                            } else {
                                $journeyData[$key]['khmer']     = 0;
                                $journeyData[$key]['foreinger'] = $rowTicket['customer'];
                            }
                        }
                    }
                    $index = 0;
                    $totalKhmer = 0;
                    $totalFor   = 0;
                    $totalCustomer = 0;
                    foreach($journeyData AS $data){
                    ?>
                    <tr>
                        <td style="text-align: left; padding: 5px;  font-size: 11px; font-weight: bold;">
                            <?php 
                            echo ++$index; 
                            $excelContent .= "\n" . $index;
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 11px; font-weight: bold;">
                            <?php 
                            echo $data['destFrom']; 
                            $excelContent .= "\t".$data['destFrom'];
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 11px; font-weight: bold;">
                            <?php 
                            echo $data['destTo']; 
                            $excelContent .= "\t".$data['destTo'];
                            ?>
                        </td>
                        <td style="text-align: right; padding: 5px; font-size: 11px; font-weight: bold;">
                            <?php 
                            echo number_format($data['khmer'], 0); 
                            $excelContent .= "\t".number_format($data['khmer'], 0);
                            $totalKhmer   += $data['khmer'];
                            ?>
                        </td>
                        <td style="text-align: right; padding: 5px; font-size: 11px; font-weight: bold;">
                            <?php 
                            echo number_format($data['foreinger'], 0); 
                            $excelContent .= "\t".number_format($data['foreinger'], 0);
                            $totalFor += $data['foreinger'];
                            ?>
                        </td>
                        <td style="text-align: right; padding: 5px; font-size: 11px; font-weight: bold;">
                            <?php 
                            $total = $data['khmer'] + $data['foreinger'];
                            echo number_format($total, 0); 
                            $excelContent .= "\t".number_format($total, 0);
                            ?>
                        </td>
                    </tr>
                    <?php
                        $totalCustomer += $total;
                    }
                    $excelContent .= "\n\t\tTotal\t".number_format($totalKhmer, 0)."\t".number_format($totalFor, 0)."\t".number_format($totalCustomer, 0);
                    ?>
                    <tr>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="3">Total</td>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;"><?php echo number_format($totalKhmer, 0); ?></td>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;"><?php echo number_format($totalFor, 0); ?></td>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;"><?php echo number_format($totalCustomer, 0); ?></td>
                    </tr>
                </tbody>
            </table>
            <br />
        </div>
    </div>
    <div style="clear: both;"></div>
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
<?php
$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);
?>