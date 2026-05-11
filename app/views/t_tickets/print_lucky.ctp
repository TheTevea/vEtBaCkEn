<?php 
include("includes/function.php");
// Get Printer Name 
$printerName = '';
$printSilent = 0;
mysql_query("UPDATE t_tickets SET total_print_lucky = total_print_lucky + 1 WHERE id = ".$this->data['TTicket']['id']);
?>
<style type="text/css" media="screen">
    div.print-footer {display: none;}
</style> 
<style type="text/css" media="print">
    div.print_doc { width:100%;}
    #btnDisappearPrint { display: none;}
    div.print-footer {display: block; width:100%;} 
    .breakPage {page-break-before: always;}
</style>
<div class="print_doc" style="width: 300px;">
    
    <?php
    $customerType = '';
    $priceType    = '';
    if($this->data['TTicket']['price_type'] == 1){
        $priceType = '(Khmer)';
    } else if($this->data['TTicket']['price_type'] == 2){
        $priceType = 'Walk In (VIP Card)';
    } else if($this->data['TTicket']['price_type'] == 3){
        $priceType = 'Walk In (Foreigner)';
    } else if($this->data['TTicket']['price_type'] == 4){
        $priceType = 'Ticket 10 Free 1';
    }

    if($this->data['TTicket']['type'] == 1){
        $customerType = 'Walk In '.$priceType;
    } else if ($this->data['TTicket']['type'] == 2) {
        $customerType = 'Phone Call '.$priceType;
    } else if($this->data['TTicket']['type'] == 5 || $this->data['TTicket']['type'] == 11){
        if(empty($this->data['TTicket']['t_agent_id'])){
            $customerType = 'App';
        } else {
            if($this->data['TTicket']['t_agent_id'] == 55){
                $customerType = 'Website';
            } else {
                $customerType = 'Mini App';
            }
        }
    } else if($this->data['TTicket']['type'] == 10){
        $customerType = 'Terminal';
    } else {
        if(!empty($this->data['TTicket']['t_agent_id'])){
            $customerType = 'Agency';
        }
    }
    $qty  = 0;
    $seatRecords = array();
    $sqlSeat = mysql_query("SELECT IFNULL(label_number, seat_number) AS seat, unit_price, vat_price, markup, discount, total_amount, is_free FROM t_ticket_details WHERE t_ticket_details.t_ticket_id = ".$this->data['TTicket']['id']);
    while($rowSeat = mysql_fetch_array($sqlSeat)){
        $qty += 1;
        $seatRecords[$qty]['number'] = $rowSeat['seat'];
    }
    // Destination
    $sqlFrom = mysql_query("SELECT code, name FROM t_destinations WHERE id = ".$this->data['TTicket']['t_destination_from_id']);
    $rowFrom = mysql_fetch_array($sqlFrom);
    $sqlTo   = mysql_query("SELECT code, name FROM t_destinations WHERE id = ".$this->data['TTicket']['t_destination_to_id']);
    $rowTo   = mysql_fetch_array($sqlTo);
    $destinationFromCode = $rowFrom[0];
    $destinationFromName = $rowFrom[1];
    $destinationToCode   = $rowTo[0];
    $destinationToName   = $rowTo[1];
    $i = 0;
    foreach($seatRecords AS $data){
        $break = "";
        if($i > 0){
            $break = ' class="breakPage"';
        }
    ?>
    <div style="width: 100%;"<?php echo $break; ?>>
        <table style="width: 100%;">
            <tr>
                <td style="width: 20%;"><img src="<?php echo $this->webroot; ?>img/logo-print.png" style="width: 45px;" /></td>
                <td>
                    <b style="font-size: 14px;">វិរៈប៊ុនថាំ អេចប្រេស</b><br/>Vireak Buntham Express Co.,Ltd
                </td>
            </tr>
        </table>
        <table style="width: 100%;" cellpadding="5" cellspacing="0">
            <tr>
                <td style="font-size: 12px; font-weight: bold;">LUCKY TICKET</td>
            </tr>
            <tr>
                <td style="font-size: 10px;">លេខទូរស័ព្ទ/Telephone No.: <?php echo $this->data['TTicket']['telephone']; ?></td>
            </tr>
            <tr>
                <td style="font-size: 10px;">លេខរៀងវិក្កយបត្រ/Invoice No.: <?php echo $this->data['TTicket']['code']; ?></td>
            </tr>
            <tr>
                <td style="font-size: 10px;">ទិសដៅ/Direction: <?php echo $destinationFromName." -> ".$destinationToName; ?></td>
            </tr>
            <tr>
                <td style="font-size: 10px;">លេខកៅអី/Seat No.: <?php echo $data['number']; ?></td>
            </tr>
            <tr>
                <td style="font-size: 10px;">អតិថិជន/Customer: <?php echo $customerType; ?></td>
            </tr>
            <tr>
                <td style="font-size: 10px;">ថ្ងៃទិញ/Issued Date: <?php echo dateShort($this->data['TTicket']['date']); ?></td>
            </tr>
            <tr>
                <td style="font-size: 10px;">ថ្ងៃ​ធ្វើ​ដំណើរ/Journey Date: 
                <?php 
                $depare  = explode(":", $this->data['TTicket']['journey_time']);
                $depareureTime = (int) $depare[0];
                if(strtotime($this->data['TTicket']['journey_date']) >= strtotime("2021-12-07")){
                    echo dateShort($this->data['TTicket']['journey_date'])." ".date('h:i A', strtotime($this->data['TTicket']['journey_time']));  
                } else {
                    if(checkDateFrom($this->data['TTicket']['branch_id'], $depareureTime) == 1){
                        echo dateShort($this->data['TTicket']['journey_date'])." ".date('h:i A', strtotime($this->data['TTicket']['journey_time']));  
                    } else {
                        echo date("d/m/Y", strtotime("+1 day", strtotime($this->data['TTicket']['journey_date'])))." ".date('h:i A', strtotime($this->data['TTicket']['journey_time']));  
                    }
                }
                ?>
                </td>
            </tr>
        </table>
    </div>
    <?php
        $i++;
    }
    ?>
    <div style="margin-top: 10px; margin-bottom: 10px;">
        <input type="button" value="<?php echo ACTION_PRINT; ?>" id="btnDisappearPrint" onClick="window.print();window.close();" class="noprint">
    </div>
</div>
<div style="clear:both"></div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<!-- QR Code -->
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.qrcode.min.js"></script>
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