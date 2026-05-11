<?php 
include("includes/function.php");
// Get Printer Name 
$printerName = '';
$printSilent = 0;
//$sqlPrinter = mysql_query("SELECT printer_name, silent FROM printers WHERE branch_id = ".$tJourney['TJourney']['branch_id']." AND type_id = 2 AND is_active = 1 ORDER BY id DESC LIMIT 1;");
//if(mysql_num_rows($sqlPrinter)){
//    $rowPrinter = mysql_fetch_array($sqlPrinter);
//    $printerName = $rowPrinter[0]; 
//    $printSilent = $rowPrinter[1];
//}
?>
<style type="text/css" media="screen">
    div.print-footer {display: none;}
</style> 
<style type="text/css" media="print">
    div.print_doc { width:100%;}
    #btnDisappearPrint { display: none;}
    div.print-footer {display: block; width:100%;} 
</style>
<div class="print_doc" style="width: 900px;">
    <table style="width: 100%;">
        <tr>
            <td style="text-align: center; font-size: 12px; font-weight: bold;">
                <?php echo $tJourney['Branch']['name']; ?> Plan Seat
            </td>
        </tr>
        <tr>
            <td style="text-align: center; font-size: 11px;">
                <?php echo $tJourney['TJourney']['description']; ?>
            </td>
        </tr>
        <tr>
            <td style="text-align: center; font-size: 11px;">
                Date: <?php echo date("d/m/Y"); ?>
            </td>
        </tr>
    </table>
    <table cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="width: 10%;">Departure Time: </td>
            <td style="width: 65%;"><?php echo date("h:i A", strtotime($tJourney['TDepartureTime']['name'])); ?></td>
            <td style="width: 15%;">Transportaion Type: </td>
            <td><?php echo $tBoat['TTransportationType']['name']; ?></td>
        </tr>
    </table>
    <div style="width: 100%; border: 1px solid #000; text-align: center;">
        <?php
        $layouts = json_decode($tBoat['TTransportationType']['layout'], true);
        $tableLayout  = '';
        $seatInactive = array();
        // Get Seat In Active
        foreach($tSeatControlls AS $tSeatControll){
            if(empty($tSeatControll['TTicket']['id'])){
                $sqlTck = mysql_query("SELECT * FROM t_ticket_3months WHERE id = ".$tSeatControll['TSeatControl']['t_ticket_id']);
                $rowTck = mysql_fetch_array($sqlTck);
                $tSeatControll['TTicket']['id'] = $rowTck['id'];
                $tSeatControll['TTicket']['confirm_by'] = $rowTck['confirm_by'];
                $tSeatControll['TTicket']['created_by'] = $rowTck['created_by'];
                $tSeatControll['TTicket']['t_destination_to_id'] = $rowTck['t_destination_to_id'];
                $tSeatControll['TTicket']['code'] = $rowTck['code'];
                $tSeatControll['TTicket']['telephone']  = $rowTck['telephone'];
                $tSeatControll['TTicket']['note']       = $rowTck['note'];
                $tSeatControll['TTicket']['price_type'] = $rowTck['price_type'];
                $tSeatControll['TTicket']['t_agent_id'] = $rowTck['t_agent_id'];
                $tSeatControll['TTicket']['agt_refer_code'] = $rowTck['agt_refer_code'];
                $tSeatControll['TTicket']['t_journey_transit_id'] = $rowTck['t_journey_transit_id'];
                $tSeatControll['TTicket']['main_branch_id'] = $rowTck['main_branch_id'];
            } 
            if($tSeatControll['TTicket']['confirm_by'] != ''){
                $createdBy = $tSeatControll['TTicket']['confirm_by'];
            } else {
                $createdBy = $tSeatControll['TTicket']['created_by'];
            }
            $username = "";
            if(!empty($createdBy)){
                $sqlUser = mysql_query("SELECT first_name, last_name FROM users WHERE id = ".$createdBy);
                $rowUser = mysql_fetch_array($sqlUser);
                $username = $rowUser['first_name']." ".$rowUser['last_name'];
            }
            // Destination To
            $destTo  = '';
            if(!empty($tSeatControll['TTicket']['t_destination_to_id'])){
                $sqlDest = mysql_query("SELECT code FROM t_destinations WHERE id = ".$tSeatControll['TTicket']['t_destination_to_id']);
                $rowDest = mysql_fetch_array($sqlDest);
                $destTo  = $rowDest[0];
            }
            $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['note'] = $tSeatControll['TTicket']['note'];
            $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['ticket'] = $tSeatControll['TTicket']['code'];
            $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['user'] = $username;
            $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['tel']  = $tSeatControll['TTicket']['telephone'];
            $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['dest'] = $destTo;
            if($tSeatControll['TTicket']['price_type'] == 3){
                $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['type'] = '(VIP)';
            } else {
                $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['type'] = '';
            }
            if(!empty($tSeatControll['TTicket']['t_agent_id'])){
                $sqlAg = mysql_query("SELECT code, name FROM t_agents WHERE id = ".$tSeatControll['TTicket']['t_agent_id']);
                $rowAg = mysql_fetch_array($sqlAg);
                $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['agency'] = $rowAg['code']." - ".$rowAg['name'];
                $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['ref'] = $tSeatControll['TTicket']['agt_refer_code'];
            } else {
                $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['agency'] = '';
                $seatInactive[$tSeatControll['TSeatControl']['seat_number']]['ref'] = '';
            }
        }
        // List Seat
        $tableWeight = 0;
        foreach($layouts AS $layout){
            $cols = $layout['col'];
            $tableLayout .= '<tr>';
            $totalTmpWeight = 0;
            foreach($cols AS $col){
                $colspan = $col['attr']['colspan'];
                $value   = $col['value'];
                $label   = $value;
                if (array_key_exists("label", $col)) {
                    $label = $col['label'];
                }
                $attrCol = '';
                if($value != '') {
                    $colWidth = 160;
                } else {
                    $colWidth = 30;
                }
                if($colspan != ''){
                    $attrCol = 'colspan="'.$colspan.'"';
                    $totalTmpWeight += $colWidth * $colspan;
                } else {
                    $totalTmpWeight += $colWidth;
                }
                if(is_numeric($value) && $value != ''){
                    $tableLayout .= '<td '.$attrCol.' style="height: 155px; width: '.$colWidth.'px; text-align: left; vertical-align: top; font-size: 10px;">';
                    $tableLayout .= '<div style="width: 97%; height: 150px; border: 1px solid #000; padding-left: 5px; font-size: 10px;">';
                    if(!empty($seatInactive[$value])){
                        $tableLayout .= $label.'<br/><b>'.$seatInactive[$value]['ticket'].'</b> '.$seatInactive[$value]['type'];
                        $tableLayout .= '<br/>Tel :'.$seatInactive[$value]['tel'];
                        $tableLayout .= '<br/>Pick :'.$seatInactive[$value]['note'];
                        $tableLayout .= '<br/>DT:'.$seatInactive[$value]['dest'];
                        $tableLayout .= '<br/>'.$seatInactive[$value]['user'];
                        $tableLayout .= '<br/>AG:'.$seatInactive[$value]['agency'];
                        $tableLayout .= '<br/>Ref:'.$seatInactive[$value]['ref'];
                    } else {
                        $tableLayout .= $label;
                    }
                    $tableLayout .= '</div>';
                } else if($value != '') {
                    $tableLayout .= '<td '.$attrCol.' style="height: 90px; width: '.$colWidth.'px; text-align: center; vertical-align: top;">';
                    if($label == 'Open1' || $label == 'Open2') {
                        $tableLayout .= 'Open Air Seat';
                    } else {
                        $tableLayout .= $label;
                    }
                } else {
                    $tableLayout .= '<td '.$attrCol.' style="height: 70px; width: '.$colWidth.'px; text-align: center; vertical-align: top;">';
                }
                $tableLayout .= '</td>';
            }
            if($tableWeight <= $totalTmpWeight){
                $tableWeight = $totalTmpWeight;
            }
            $tableLayout .= '</tr>';
        }
        ?>
        <table cellpadding="0" cellspacing="0" style="width: <?php echo $tableWeight; ?>px; margin: 0px auto; margin-top: 10px; margin-bottom: 10px;">
            <?php echo $tableLayout; ?>
        </table>
        <div style="width: 100%; text-align: left; width: <?php echo $tableWeight; ?>px; margin: 0px auto; min-height: 300px;">
            Note: <br/>
            <?php ?>
            <div style="clear: both;"></div>
        </div>
    </div>
    <div style="margin-top: 10px; margin-bottom: 10px;">
        <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' onClick='window.print();window.close();' class='noprint'>
    </div>
</div>
<div style="clear:both"></div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<!-- Print -->
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        var ws = window;
        try {
            jsPrintSetup.refreshOptions();
            var printer = '';
            var silent  = <?php echo $printSilent; ?>;
            <?php
            if($printerName != ''){
            ?>
            printer = getPrinterName('<?php echo $printerName; ?>');
            if(printer != ''){
                jsPrintSetup.setPrinter(printer);
            }
            <?php
            }
            ?>
            jsPrintSetup.setOption('marginTop', 0);
            jsPrintSetup.setOption('marginBottom', 0);
            jsPrintSetup.setOption('marginLeft', 0);
            jsPrintSetup.setOption('marginRight', 0);
            jsPrintSetup.setSilentPrint(silent);
            jsPrintSetup.printWindow(ws);
            ws.close();
        } catch (e) {
           ws.print();
           ws.close();
        }
    });
</script>