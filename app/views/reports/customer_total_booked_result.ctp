<?php
include('includes/function.php');
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
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
            window.open("<?php echo $this->webroot; ?>public/report/customerTotalBooked.csv", "_blank");
        });
    });
</script>
<div class="leftPanel">
    <div id="<?php echo $printArea; ?>">
        <?php
        $msg = REPORT_TOTAL_CUSTOMER_BOOKED.'<br/>';
        $filename = "public/report/customerTotalBooked.csv";
        $fp = fopen($filename, "wb");
        $excelContent  = REPORT_TOTAL_CUSTOMER_BOOKED."\n";
        $condtion = '';
        if($_POST['date_from']!='') {
            $msg .= 'ថ្ងៃទី : '.$_POST['date_from'];
            $excelContent .= "ថ្ងៃទី : ".$_POST['date_from'];
            $condtion .= " AND t_tickets.date >= '".dateConvert($_POST['date_from'])."'";
        }
        if($_POST['date_to'] != '') {
            if($_POST['date_to'] != $_POST['date_from']){
                $msg .= ' ទៅ: '.$_POST['date_to'];
                $excelContent .= " ទៅ: ".$_POST['date_to']."\n";
            }
            $condtion .= " AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'";
        }
        $excelContent .= "\nNo\tTelephone\tTotal Booked\tTotal Seat\tTotal Amount ($)";
        echo $this->element('/print/header-report',array('msg'=>$msg));
        ?>
        <div id="dynamic">
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <th class="first" style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo TABLE_NO; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; text-align: left;"><?php echo TABLE_TELEPHONE; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; text-align: left;"><?php echo "Total Booked"; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; text-align: left;"><?php echo "Total Seat"; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; text-align: left;"><?php echo "Total Amount ($)"; ?></th>
                    </tr>
                    <?php
                        $sqlTicket = mysql_query("SELECT COUNT(t_tickets.id) AS total_booked, t_tickets.telephone, '1' AS `type`
                                                  FROM t_tickets 
                                                  WHERE t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND t_tickets.telephone != '' AND (t_agent_id IN (55, 106) OR t_agent_id IS NULL)".$condtion." 
                                                  GROUP BY t_tickets.telephone HAVING total_booked > 10
                                                  UNION ALL
                                                  SELECT COUNT(t_tickets.id) AS total_booked, t_tickets.telephone, '2' AS `type`
                                                  FROM t_ticket_3months AS t_tickets 
                                                  WHERE t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND t_tickets.telephone != '' AND (t_agent_id IN (55, 106) OR t_agent_id IS NULL)".$condtion." 
                                                  GROUP BY t_tickets.telephone HAVING total_booked > 10
                                                  ORDER BY telephone");
                        $datas = array();
                        while($rowTicket = mysql_fetch_array($sqlTicket)){
                            if($rowTicket['type'] == 1){
                                $tblTicket = "t_tickets";
                                $tblDetail = "t_ticket_details";
                            } else if($rowTicket['type'] == 2){
                                $tblTicket = "t_ticket_3months";
                                $tblDetail = "t_ticket_detail_3months";
                            }
                            $sqlDetail = mysql_query("SELECT COUNT(t_ticket_details.id) AS total, SUM(IFNULL(t_ticket_details.total_amount, 0) - IFNULL(t_ticket_details.discount, 0) + IFNULL(t_ticket_details.vat_price, 0)) AS total_amount 
                                                      FROM ".$tblDetail." AS t_ticket_details
                                                      INNER JOIN ".$tblTicket." AS t_tickets ON t_tickets.id = t_ticket_details.t_ticket_id
                                                      WHERE t_ticket_details.is_active = 1 AND t_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND t_tickets.telephone = '".$rowTicket['telephone']."' GROUP BY t_tickets.telephone");
                            $rowDetail = mysql_fetch_array($sqlDetail);
                            if(array_key_exists($rowTicket['telephone'], $datas)){
                                $datas[$rowTicket['telephone']]['total_booked']  += $rowTicket['total_booked'];
                                $datas[$rowTicket['telephone']]['total']  += $rowDetail['total'];
                                $datas[$rowTicket['telephone']]['amount'] += $rowDetail['total_amount'];
                            } else {
                                $datas[$rowTicket['telephone']]['telephone']    = $rowTicket['telephone'];
                                $datas[$rowTicket['telephone']]['total_booked'] = $rowTicket['total_booked'];
                                $datas[$rowTicket['telephone']]['total']  = $rowDetail['total'];
                                $datas[$rowTicket['telephone']]['amount'] = $rowDetail['total_amount'];
                            }
                        }
                        function sortArray($a, $b) {
                            return $a['total_booked'] < $b['total_booked'];
                        }
                        usort($datas, "sortArray");
                        $index = 0;
                        $totalBooked = 0;
                        $totalSeat   = 0;
                        $totalAmount = 0;
                        foreach($datas AS $data){
                            $totalBooked += $data['total_booked'];
                            $totalSeat   += $data['total'];
                            $totalAmount += $data['amount'];
                    ?>
                    <tr>
                        <td class="first" style="font-size: 12px; font-weight: bold; padding: 5px; text-align: center;">
                            <?php 
                            echo ++$index; 
                            $excelContent .= "\n".$index;
                            ?>
                        </td>
                        <td style="font-size: 12px; font-weight: bold; padding: 5px; text-align: left;">
                            <?php 
                            echo $data['telephone']; 
                            $excelContent .= "\t".$data['telephone'];
                            ?>
                        </td>
                        <td style="font-size: 12px; font-weight: bold; padding: 5px; text-align: left;">
                            <?php 
                            echo number_format($data['total_booked'], 0); 
                            $excelContent .= "\t".number_format($data['total_booked'], 0);
                            ?>
                        </td>
                        <td style="font-size: 12px; font-weight: bold; padding: 5px; text-align: left;">
                            <?php 
                            echo number_format($data['total'], 0); 
                            $excelContent .= "\t".number_format($data['total'], 0);
                            ?>
                        </td>
                        <td style="font-size: 12px; font-weight: bold; padding: 5px; text-align: left;">
                            <?php 
                            echo number_format($data['amount'], 2); 
                            $excelContent .= "\t".number_format($data['amount'], 2);
                            ?>
                        </td>
                    </tr>
                    <?php
                        }
                        $excelContent .= "\n\tTotal\t".number_format($totalBooked, 0)."\t".number_format($totalSeat, 0)."\t".number_format($totalAmount, 2);
                    ?>
                    <tr>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="2">Total</td>
                        <td style="text-align: left; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalBooked, 0); ?></td>
                        <td style="text-align: left; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalSeat, 0); ?></td>
                        <td style="text-align: left; padding: 5px; font-size: 14px; font-weight: bold;"><?php echo number_format($totalAmount, 2); ?></td>
                    </tr>
                </tbody>
            </table>
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