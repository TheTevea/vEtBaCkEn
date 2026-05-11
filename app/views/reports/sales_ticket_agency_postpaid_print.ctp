<?php 
include("includes/function.php");
// Get Printer Name 
$printerName = '';
$printSilent = 0;
$sqlClaim = mysql_query("SELECT * FROM agency_postpaid_claims WHERE id = ".$id);
$rowClaim = mysql_fetch_array($sqlClaim);

$dataRecord = array();
$agencyData = array();
$i = 0;
$sqlTicket  = mysql_query("SELECT t_tickets.*, t_agents.name AS agency_name 
                           FROM agency_postpaid_claim_details 
                           INNER JOIN t_tickets ON t_tickets.id = agency_postpaid_claim_details.t_ticket_id
                           INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id
                           WHERE agency_postpaid_claim_details.agency_postpaid_claim_id = ".$id." AND agency_postpaid_claim_details.is_active = 1");
while($rowTicket = mysql_fetch_array($sqlTicket)){
    $sqlPaid  = mysql_query("SELECT * FROM agency_balances WHERE t_ticket_id = ".$rowTicket['id']." AND module = 'Agent PostPaid Paid' LIMIT 1;");
    $rowPaid  = mysql_fetch_array($sqlPaid);
    $destFrom = "";
    $destTo   = "";
    $sqlDest  = mysql_query("SELECT * FROM t_destinations WHERE id IN (".$rowTicket['t_destination_from_id'].", ".$rowTicket['t_destination_to_id'].")");
    while($rowDest = mysql_fetch_array($sqlDest)){
        if($rowDest['id'] == $rowTicket['t_destination_from_id']){
            $destFrom = $rowDest['name'];
        } else {
            $destTo   = $rowDest['name'];
        }
    }
    $agencyData[$rowTicket['t_agent_id']] = $rowTicket['agency_name'];
    $dataRecord[$i]['date'] = $rowTicket['date'];
    $dataRecord[$i]['code'] = $rowTicket['code'];
    $dataRecord[$i]['journey_date'] = $rowTicket['journey_date'];
    $dataRecord[$i]['journey_time'] = $rowTicket['journey_time'];
    $dataRecord[$i]['total_seat']   = $rowTicket['total_seat'];
    $dataRecord[$i]['unit_price']   = $rowTicket['price'];
    $dataRecord[$i]['dest_from']    = $destFrom;
    $dataRecord[$i]['dest_to']      = $destTo;
    $dataRecord[$i]['amt']          = $rowPaid['credit'];
    $i++;
}
?>
<style type="text/css" media="screen">
    div.print-footer {display: none;}
</style> 
<style type="text/css" media="print">
    div.print_doc { width:100%; }
    #btnDisappearPrint { display: none; }
    div.print-footer {display: block; width:100%;} 
    .breakPage {page-break-before: always;}
</style>
<div class="print_doc" style="width: 100%;">
    <table style="width: 100%;">
        <tr>
            <td style="text-align: center;">
                <img alt="" src="<?php echo $this->webroot; ?>img/logo.png" style="height: 90px;" />
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; text-align: center; width: 34%;">
                <div style="font-size: 12px; font-weight: bold; text-transform: uppercase;"><?php echo ''; ?></div>
            </td>
        </tr>
    </table>
    <table style="width: 100%;">
        <tr>
            <td style="width: 90px; font-size: 12px;">Invoice Date: </td>
            <td style="width: 140px; font-size: 12px;"><?php echo dateShort($rowClaim['date']); ?></td>
            <td style="width: 90px; font-size: 12px;">Invoice Code: </td>
            <td style="font-size: 12px;"><?php echo $rowClaim['code']; ?></td>
        </tr>
        <tr>
            <td style="font-size: 12px;">Agency Name: </td>
            <td colspan="3" style="font-size: 12px;"><?php echo implode(", ", $agencyData); ?></td>
        </tr>
    </table>
    <br/><br/>
    <table style="width: 100%;" cellpadding="2" cellspacing="0">
        <tr>
            <td style="font-size: 12px; border-left: 2px solid #000; border-top: 2px solid #000; border-bottom: 2px solid #000;">Issued Date</td>
            <td style="font-size: 12px; border-left: 2px solid #000; border-top: 2px solid #000; border-bottom: 2px solid #000;">Ticket Code</td>
            <td style="font-size: 12px; border-left: 2px solid #000; border-top: 2px solid #000; border-bottom: 2px solid #000;">Destination From</td>
            <td style="font-size: 12px; border-left: 2px solid #000; border-top: 2px solid #000; border-bottom: 2px solid #000;">Destination To</td>
            <td style="font-size: 12px; border-left: 2px solid #000; border-top: 2px solid #000; border-bottom: 2px solid #000;">Travel Date</td>
            <td style="font-size: 12px; border-left: 2px solid #000; border-top: 2px solid #000; border-bottom: 2px solid #000;">Seats</td>
            <td style="font-size: 12px; border-left: 2px solid #000; border-top: 2px solid #000; border-bottom: 2px solid #000; text-align: right;">Unit Price</td>
            <td style="font-size: 12px; border-left: 2px solid #000; border-top: 2px solid #000; border-bottom: 2px solid #000; border-right: 2px solid #000; text-align: right;">Total</td>
        </tr>
        <?php
        $totalAmt  = 0;
        foreach($dataRecord AS $data){
            $amt = $data['amt'];
            $totalAmt  += $amt;
        ?>
        <tr>
            <td style="font-size: 12px; border-left: 2px solid #000; border-bottom: 2px solid #000;"><?php echo dateShort($data['date']); ?></td>
            <td style="font-size: 12px; border-left: 2px solid #000; border-bottom: 2px solid #000;"><?php echo $data['code']; ?></td>
            <td style="font-size: 12px; border-left: 2px solid #000; border-bottom: 2px solid #000;"><?php echo $data['dest_from']; ?></td>
            <td style="font-size: 12px; border-left: 2px solid #000; border-bottom: 2px solid #000;"><?php echo $data['dest_to']; ?></td>
            <td style="font-size: 12px; border-left: 2px solid #000; border-bottom: 2px solid #000;"><?php echo dateShort($data['journey_date'])." ".date('h:i A', strtotime($data['journey_time'])); ?></td>
            <td style="font-size: 12px; border-left: 2px solid #000; border-bottom: 2px solid #000;"><?php echo $data['total_seat']; ?></td>
            <td style="font-size: 12px; border-left: 2px solid #000; border-bottom: 2px solid #000; text-align: right;"><?php echo number_format($amt / $data['total_seat'], 2); ?></td>
            <td style="font-size: 12px; border-left: 2px solid #000; border-bottom: 2px solid #000; border-right: 2px solid #000; text-align: right;"><?php echo number_format($amt, 2); ?> $</td>
        </tr>
        <?php
        }
        ?>
        <tr>
            <td colspan="7" style="font-size: 12px; font-weight: bold; text-align: right;">Total</td>
            <td style="font-size: 12px; font-weight: bold; text-align: right;"><?php echo number_format($totalAmt, 2); ?> $</td>
        </tr>
    </table>
    <div style="margin-top: 10px; margin-bottom: 10px;">
        <input type="button" value="<?php echo ACTION_PRINT; ?>" id="btnDisappearPrint" onClick="window.print();window.close();" class="noprint">
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