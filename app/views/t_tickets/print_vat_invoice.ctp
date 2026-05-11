<?php 
include("includes/function.php");
// Get Printer Name 
$printerName = '';
$printSilent = 0;
$sqlPrinter  = mysql_query("SELECT printer_name, silent FROM printers WHERE branch_id = ".$this->data['TTicket']['branch_id']." AND type_id = 1 AND is_active = 1 ORDER BY id DESC LIMIT 1;");
if(mysql_num_rows($sqlPrinter)){
    $rowPrinter  = mysql_fetch_array($sqlPrinter);
    $printerName = $rowPrinter[0]; 
    $printSilent = $rowPrinter[1];
}
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
    
    // Destination
    $sqlFrom = mysql_query("SELECT code, name FROM t_destinations WHERE id = ".$this->data['TTicket']['t_destination_from_id']);
    $rowFrom = mysql_fetch_array($sqlFrom);
    $sqlTo   = mysql_query("SELECT code, name FROM t_destinations WHERE id = ".$this->data['TTicket']['t_destination_to_id']);
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
                <?php echo $this->data['TTicket']['code']; ?>
            </td>
        </tr>
        <tr><td style="font-size: 10px; text-align: right;">អតិថិជន/Customer:</td>
        <td style="font-size: 10px;"><?php echo $customerType; ?></td></tr>
        <tr><td style="font-size: 10px; text-align: right;">លេខទូរស័ព្ទ/Telephone No:</td>
        <td style="font-size: 10px;"><?php echo $this->data['TTicket']['telephone']; ?></td></tr>
        <tr><td style="font-size: 10px; text-align: right;">ថ្ងៃទិញ/Issued Date:</td>
            <td style="font-size: 10px;">
                <?php 
                echo dateShort($this->data['TTicket']['date']); 
                if($this->data['TTicket']['lucky_draw_fee'] > 0){
                    echo " (<b>Lucky Draw</b>)";
                }
                ?>
            </td>
        </tr>
        <tr><td style="font-size: 10px; text-align: right;">ថ្ងៃ​ធ្វើ​ដំណើរ/Journey Date:</td>
            <td style="font-size: 10px;">
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
        $sqlSeat = mysql_query("SELECT IFNULL(label_number, seat_number) AS seat, unit_price, vat_price, markup, discount, total_amount, is_free FROM t_ticket_details WHERE t_ticket_details.t_ticket_id = ".$this->data['TTicket']['id']);
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
            <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;" colspan="2">បញ្ចុះ តម្លៃ/Discount</td>
            <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;" colspan="2">
            <?php
            if($totalAmount > 0){
                echo number_format($this->data['TTicket']['discount_amount'] + $this->data['TTicket']['total_change'] + $this->data['TTicket']['coupon_amount'], 2); 
            } else {
                echo '0';
            }
            ?> $
            </td>
        </tr>
        <tr>
            <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;" colspan="2">តម្លៃបន្ថែម/Extra Price</td>
            <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;" colspan="2">
            <?php echo number_format($this->data['TTicket']['lucky_draw_fee'], 2); ?> $
            </td>
        </tr>
        <tr style="display: none;">
            <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;" colspan="2">អាករលើតម្លៃបន្ថែម/VAT (10%)</td>
            <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;" colspan="2"><?php echo number_format($this->data['TTicket']['total_vat'], 2); ?> $</td>
        </tr>
        <tr>
            <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; text-align: right;" colspan="2">សរុបចុងក្រោយ/Grand Total USD</td>
            <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: right;" colspan="2">
            <?php 
            $grandTotalUs   = $totalAmount + $this->data['TTicket']['lucky_draw_fee'] - $this->data['TTicket']['discount_amount'] - $this->data['TTicket']['total_change'] - $this->data['TTicket']['coupon_amount'];
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
            <td style="font-size: 10px; padding: 2px; margin: 0px; border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: 1px solid #000; text-align: right;" colspan="2"><?php echo number_format($grandTotalRiel, 0); ?> ៛</td>
        </tr>
    </table>
    <table style="width: 100%;">
        <tr><td style="font-size: 10px;">តម្លៃសំបុត្របូកបញ្ចូលពន្ធអាករលើតម្លៃបន្ថែមរួចជាស្រេច/VAT INCLUDED</td></tr>
        <tr><td style="font-size: 10px;">អត្រាប្តូរប្រាក់/Exchange Rate: 4,100៛</td></tr>
        <tr><td style="font-size: 10px;">
            - ទីតាំងឡើង&លេខទូរស័ព្ទ/Boarding Point & Tel: 
            <?php 
            $boardingPointTime = "";
            if(!empty($this->data['TTicket']['t_boarding_point_id'])){
                $sqlBoardingTime = mysql_query("SELECT CONCAT(HOUR(time),':',MINUTE(time)) AS time FROM t_journey_boarding_points WHERE t_journey_id = ".$this->data['TTicket']['t_journey_id']." AND t_boarding_point_id = ".$this->data['TTicket']['t_boarding_point_id']);
            } else {
                $sqlBoardingTime = mysql_query("SELECT CONCAT(HOUR(time),':',MINUTE(time)) AS time FROM t_journey_boarding_points WHERE t_journey_id = ".$this->data['TTicket']['t_journey_id']." LIMIT 1");
            }
            if(mysql_num_rows($sqlBoardingTime)){
                $rowBoardingTime   = mysql_fetch_array($sqlBoardingTime);
                $boardingPointTime = date('h:i A', strtotime(date("Y-m-d")." ".$rowBoardingTime['time']));
            }
            echo $this->data['TBoardingPoint']['name'];  ?> (<?php echo $boardingPointTime; ?>) <?php echo $this->data['Branch']['telephone']; ?></td></tr>
        <tr>
            <td style="font-size: 10px;">
                - ទីតាំងចុះ&លេខទូរស័ព្ទ/Drop Off Point & Tel:
                <?php
                $dropOffTime = "";
                if(!empty($this->data['TTicket']['t_drop_off_id'])){
                    $sqlDropOffTime = mysql_query("SELECT CONCAT(HOUR(time),':',MINUTE(time)) AS time FROM t_journey_drop_offs WHERE t_journey_id = ".$this->data['TTicket']['t_journey_id']." AND t_drop_off_id = ".$this->data['TTicket']['t_drop_off_id']);
                } else {
                    $sqlDropOffTime = mysql_query("SELECT CONCAT(HOUR(time),':',MINUTE(time)) AS time FROM t_journey_drop_offs WHERE t_journey_id = ".$this->data['TTicket']['t_journey_id']." LIMIT 1");
                }
                if(mysql_num_rows($sqlDropOffTime)){
                    $rowDropOffTime  = mysql_fetch_array($sqlDropOffTime);
                    $dropOffTime     = date('h:i A', strtotime(date("Y-m-d")." ".$rowDropOffTime['time']));
                }
                $sqlBranchTo = mysql_query("SELECT name, telephone FROM branches WHERE id IN (SELECT branch_id FROM branch_destinations WHERE t_destination_id = ".$this->data['TTicket']['t_destination_to_id'].") AND company_id = ".$this->data['TTicket']['company_id']);
                $rowBranchTo = mysql_fetch_array($sqlBranchTo);
                echo $this->data['TDropOff']['name']." (".$dropOffTime.") ".$rowBranchTo[1];
                ?>
            </td>
        </tr>
        <tr><td style="font-size: 10px;">ល័ក្ខខ័ណ្ឌ/Term & Condition:</td></tr>
        <tr><td style="font-size: 10px;">
            <?php
            if($this->data['TTicket']['is_open_date'] == 1){
            ?>
            សូមអតិថិជនទាំងអស់ធ្វើការបញ្ជាក់សំបុត្រត្រលប់1ថ្ងៃមុនចេញដំណើរ។ 
            <?php
            }
            ?>
            សូមអញ្ជើញមកដល់យ៉ាងហោចណាស់30នាទីមុនពេលការចេញដំណើរ។ សំបុត្រទិញហើយមិនអាចដូរយកប្រាក់វិញបានទេ។ អរគុណចំពោះការប្រើប្រាស់សេវាកម្មយើងខ្ញុំ។</td></tr>
        <tr><td style="font-size: 10px;">
            <?php
            if($this->data['TTicket']['is_open_date'] == 1){
            ?>
            Please confirm your return ticket one day in advance.
            <?php
            }
            ?>
            Please arrive at least 30 minutes before departure time. Ticket sold cannot be refund. Thank you for using our service.</td></tr>
    </table>
    <?php
    foreach($seatRecords AS $data){
    ?>
    <div style="width: 100%;" class="breakPage">
        <table style="width: 100%;" cellpadding="0" cellspacing="3">
            <tr><td style="width: 60%; font-size: 10px;">លេខរៀងវិក្កយបត្រ/Invoice No.:<br/><?php echo $this->data['TTicket']['code']."_".$data['number']; ?></td>
                <td rowspan="6"><input type="hidden" class="qrCodeTicket" value="<?php echo $this->data['TTicket']['code']."_".$data['number']; ?>" /><div class="cardQRCode"></div></td></tr>
            <tr><td style="font-size: 10px;">ទិសដៅ/Direction:<br/><?php echo $destinationFromName." -> ".$destinationToName; ?></td></tr>
            <tr><td style="font-size: 10px;">លេខកៅអី/Seat No.: <?php echo $data['number']; ?></td></tr>
            <tr><td style="font-size: 10px;">អតិថិជន/Customer: <?php echo $customerType; ?></td></tr>
            <tr><td style="font-size: 10px;">ថ្ងៃទិញ/Issued Date: <?php echo dateShort($this->data['TTicket']['date']); ?></td></tr>
            <tr><td style="font-size: 10px;">ថ្ងៃ​ធ្វើ​ដំណើរ/Journey Date:<br/>
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
        $('.qrCodeTicket').each(function(){
            var qrCode = $(this).val();
            var obj    = $(this).closest("tr").find(".cardQRCode");
            obj.qrcode({
                width	: "110",
                height	: "110",
                text	: qrCode
            }); 
        });
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