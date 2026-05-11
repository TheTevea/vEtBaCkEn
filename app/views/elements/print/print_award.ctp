<?php 
include("includes/function.php");
// Get Printer Name 
$printerName = '';
$printSilent = 0;
$sqlPrinter = mysql_query("SELECT printer_name, silent FROM printers WHERE branch_id = ".$this->data['TTicket']['branch_id']." AND type_id = 1 AND is_active = 1 ORDER BY id DESC LIMIT 1;");
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
    <?php
    if($this->data['TTicket']['is_open_date'] == 0){
        $number = 1;
        $sqlSeat = mysql_query("SELECT IFNULL(label_number, seat_number), unit_price FROM t_ticket_details WHERE t_ticket_details.t_ticket_id = ".$this->data['TTicket']['id']);
        while($rowSeat = mysql_fetch_array($sqlSeat)){
            $seatNum = '';
            if(mysql_num_rows($sqlSeat) > 1){
                $seatNum = "-".$number;
            }
            $pageBreak = '';
            $ticketBreak = '';
            if($number > 1){
                $pageBreak = 'breakPage';
            }
            $customerType = '';
            if($this->data['TTicket']['price_type'] == 1){
                if($this->data['TTicket']['type'] == 1){
                    $customerType = 'Walk In';
                } else {
                    $customerType = 'Phone Call';
                }
            } else if($this->data['TTicket']['price_type'] == 2){
                $customerType = $this->data['TAgent']['first_name']." ".$this->data['TAgent']['last_name'];
            } else if($this->data['TTicket']['price_type'] == 3){
                $customerType = 'VIP Card';
            } else if($this->data['TTicket']['price_type'] == 4){
                $customerType = 'Ticket 10 Free 1';
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
            if($this->data['TTicket']['company_id'] != 1){
    ?>
    <div style="width: 100%;" class="<?php echo $pageBreak; ?>">
        <table style="width: 100%;" cellpadding="0" cellspacing="3">
            <tr>
                <td style="width: 18%; font-size: 10px;">Ticket No</td>
                <td style="font-size: 10px;" colspan="3">: <?php echo $this->data['TTicket']['code'].$seatNum; ?>
                    <div style="float: right; width: 100px; font-size: 10px;">
                        <?php
                        echo $destinationFromCode." -> ".$destinationToCode;
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width: 18%; font-size: 10px;">Ticket Date</td>
                <td style="width: 22%; font-size: 10px;">: <?php echo dateShort($this->data['TTicket']['date']); ?></td>
                <td style="width: 18%; font-size: 10px;">Departure</td>
                <td style="font-size: 10px;">: 
                    <?php 
                    $depare  = explode(":", $this->data['TTicket']['journey_time']);
                    $depareureTime = (int) $depare[0];
                    if(checkDateFrom($this->data['TTicket']['branch_id'], $depareureTime) == 1){
                        echo dateShort($this->data['TTicket']['journey_date'])." ".date('h:i A', strtotime($this->data['TTicket']['journey_time']));  
                    } else {
                        echo date("d/m/Y", strtotime("+1 day", strtotime($this->data['TTicket']['journey_date'])))." ".date('h:i A', strtotime($this->data['TTicket']['journey_time']));  
                    }
                    ?>
                </td>
            </tr>
        </table>
        <table style="width: 100%;" cellpadding="0" cellspacing="3">
            <tr>
                <td style="width: 18%; font-size: 10px;">Customer</td>
                <td style="width: 22%; font-size: 10px;">: <?php echo $customerType; ?></td>
                <td style="width: 18%; font-size: 10px;">Seat No</td>
                <td style="width: 10%; font-size: 10px;">: <?php echo $rowSeat[0]; ?></td>
                <td style="width: 12%; font-size: 10px;">Price :</td>
                <td style="font-size: 10px;"><?php echo number_format($this->data['TTicket']['price'] - $this->data['TTicket']['dis_price'], 2)." ".$this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td style="font-size: 10px;" colspan="2">Agency Ref :</td>
                <td style="font-size: 10px;" colspan="2"><?php echo $this->data['TTicket']['reference_code']; ?></td>
                <td style="font-size: 10px;" colspan="3">
                    <?php 
                    if(!empty($this->data['TTicket']['confirm_by'])){
                        $sqlUser = mysql_query("SELECT first_name, last_name FROM users WHERE id = ".$this->data['TTicket']['confirm_by']);
                        $rowUser = mysql_fetch_array($sqlUser);
                        echo $rowUser['first_name']." ".$rowUser['last_name']; 
                    } else {
                        echo $this->data['User']['first_name']." ".$this->data['User']['last_name']; 
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>  
    <?php
                $ticketBreak = 'breakPage';
            } else {
                if($number > 1){
                    $ticketBreak = 'breakPage';
                }
            }
    ?>
    <table style="width: 100%;" class="<?php echo $ticketBreak; ?>">
        <tr>
            <td style="width: 15%;"></td>
            <td>
                <table cellpadding="0" cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style="vertical-align: top; text-align: center; width: 100px; font-size: 14px;">
                            <?php
                            if($this->data['TTicket']['company_id'] == 1){
                            ?>
                            Buva Sea Cambodia
                            <div style="font-size: 10px;">www.buvasea.com</div>
                            <?php
                            } else {
                            ?>
                            Virak Buntham Express
                            <div style="font-size: 10px;">www.virakbuntham.com</div>
                            <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-size: 10px; font-weight: bold;">
                            <?php
                            if($this->data['TTicket']['company_id'] == 1){
                            ?>
                            SPEED FERRY TICKET
                            <?php
                            } else {
                            ?>
                            TICKET
                            <?php
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 15%; font-size: 26px; font-weight: bold;">
                <?php
                $code = preg_replace('/[0-9]+/', '', str_replace($this->data['Branch']['code'],"",$this->data['TTicket']['code']));
                $explode = explode("-",$code);
                if(!empty($explode[2])){
                    echo $explode[2];
                } else {
                    echo $code;
                }
                ?>
            </td>
        </tr>
    </table>
    <table style="width: 100%; margin-top: 5px;">
        <tr>
            <td style="font-size: 10px; text-align: center;"><?php echo $this->data['Branch']['name']; ?></td>
        </tr>
        <tr>
            <td style="font-size: 10px;">លេខសំបុត្រ/Ticket No. : <b><?php echo $this->data['TTicket']['code'].$seatNum; ?></b></td>
        </tr>
        <tr>
            <td style="font-size: 10px;">ប្រភេទអតិថិជន/Type Of Customer : 
                <?php 
                    echo $customerType;
                ?>
                <div style="width: 80px; float: right; text-align: right;"><?php echo $this->data['TTicket']['telephone']; ?></div>
                <div style="clear: both;"></div>
            </td>
        </tr>
        <?php
        if($this->data['TTicket']['price_type'] == 2){
        ?>
        <tr>
            <td style="font-size: 10px;">លេខយោង/Reference Code : 
                <?php 
                echo $this->data['TTicket']['reference_code']; 
                ?>
            </td>
        </tr>
        <?php
        }
        ?>
        <tr>
            <td style="font-size: 10px;">ថ្ងៃ​ធ្វើ​ដំណើរ/Journey Date : 
                <?php 
                $depare  = explode(":", $this->data['TTicket']['journey_time']);
                $depareureTime = (int) $depare[0];
                if(checkDateFrom($this->data['TTicket']['branch_id'], $depareureTime) == 1){
                    echo dateShort($this->data['TTicket']['journey_date'])." ".date('h:i A', strtotime($this->data['TTicket']['journey_time']));  
                } else {
                    echo date("d/m/Y", strtotime("+1 day", strtotime($this->data['TTicket']['journey_date'])))." ".date('h:i A', strtotime($this->data['TTicket']['journey_time']));  
                }
                ?>
            </td>
        </tr>
    </table>
    <table style="width: 100%; margin-top: 5px;" cellspacing="0">
        <tr>
            <td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; vertical-align: top; font-size: 10px;" colspan="2">
                លេខកៅអី/Seat No. : 
                <?php
                echo $rowSeat[0];
                if($this->data['TTicket']['company_id'] != 1 && !empty($this->data['TTicket']['t_boat_id'])){
                    $sqlType = mysql_query("SELECT name FROM t_transportation_types WHERE id = (SELECT t_transportation_type_id FROM t_boats WHERE id = ".$this->data['TTicket']['t_boat_id'].")");
                    $rowType = mysql_fetch_array($sqlType);
                ?>
                <div style="width: 100px; float: right; text-align: right;"><?php echo $rowType[0]; ?></div>
                <?php
                }
                ?>
            </td>
        </tr>
        <tr>
            <td style="width: 45%; border-top: 1px solid #000; border-left: 1px solid #000; font-size: 10px;">ទិសដៅ/Direction</td>
            <td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding-left: 5px; font-size: 10px;">
                <?php echo $destinationFromName." -> ".$destinationToName; ?>
            </td>
        </tr>
        <tr>
            <td style="border-top: 1px solid #000; border-left: 1px solid #000; font-size: 10px;">ទីតាំងឡើង/Boarding Point</td>
            <td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding-left: 5px; font-size: 10px;">
                <?php 
                echo $this->data['TBoardingPoint']['name'];
                ?>
            </td>
        </tr>
        <tr>
            <td style="border-top: 1px solid #000; border-left: 1px solid #000; font-size: 10px;">ទីតាំងចុះ/Drop Off Point</td>
            <td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding-left: 5px; font-size: 10px;">
                <?php 
                echo $this->data['TDropOff']['name'];
                ?>
            </td>
        </tr>
        <tr>
            <td style="border-top: 1px solid #000; border-left: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px;">តម្លៃ/Amount <?php if($this->data['TTicket']['balance'] > 0){ ?><span style=" font-size: 10px; font-weight: bold;">(Not Yet Paid)</span><?php } ?></td>
            <td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: 1px solid #000; padding-left: 5px; font-size: 10px;"><?php echo number_format($this->data['TTicket']['price'] - $this->data['TTicket']['dis_price'], 2)." ".$this->data['CurrencyCenter']['symbol']; ?></td>
        </tr>
    </table>
    <table style="width: 100%; margin-top: 5px;" cellspacing="0">
        <tr>
            <td colspan="2" style="font-size: 8px; text-decoration: underline;">
                ល័ក្ខខ័ណ្ឌផ្សេងៗ/Term & Condition
            </td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 8px;">
                សូមអតិថិជនទាំងអស់ ធ្វើការបញ្ជាក់សំបុត្រត្រលប់ 1ថ្ងៃមុនចេញដំណើរ។<br/>
                សូមអញ្ជើញមកដល់យ៉ាងហោចណាស់ 30 នាទីមុនពេលការចេញដំណើរ។
            </td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 8px;">
                Please confirm your return ticket one day in advance.<br/>
                Please arrive at least 30 minutes before departure time.
            </td>
        </tr>
        <tr>
            <td style="font-size: 8px; padding-top: 5px;">
                សំបុត្រទិញហើយមិនអាចដូរយកប្រាក់វិញបានទេ។
            </td>
            <td style="font-size: 8px; padding-top: 5px; text-align: right;">
                Ticket sold cannot be refunded.
            </td>
        </tr>
        <tr>
            <td style="font-size: 7px; padding-top: 5px;">
                អរគុណចំពោះការប្រើប្រាស់សេវាកម្មយើងខ្ញុំ។<br/>
                Thank you for using our servive.<br/>
                <?php
                if($this->data['TTicket']['company_id'] == 1){
                ?>
                Office Sihanoukville : 097 8888 950, 069 888 950<br/>
                Office Koh Rong Sanloem : 015 888 970<br/>
                Office Koh Rong : 016 888 960
                <?php
                } else {
                    echo $this->data['Branch']['name'];  ?><br />H/P: <?php echo $this->data['Branch']['telephone'];
                    $sqlBranchTo = mysql_query("SELECT name, telephone FROM branches WHERE id IN (SELECT branch_id FROM branch_destinations WHERE t_destination_id = ".$this->data['TTicket']['t_destination_to_id'].") AND company_id = ".$this->data['TTicket']['company_id']);
                    $rowBranchTo = mysql_fetch_array($sqlBranchTo);
                    echo $rowBranchTo[0]."<br/>H/P: ".$rowBranchTo[1];
                }
                ?>
            </td>
            <td style="font-size: 7px; text-align: right; padding-top: 5px; vertical-align: top;">
                បោះពុម្ព/Print <?php echo date("d/m/Y H:i:s"); ?><br/>
                <?php 
                if(!empty($this->data['TTicket']['confirm_by'])){
                    $sqlUser = mysql_query("SELECT first_name, last_name FROM users WHERE id = ".$this->data['TTicket']['confirm_by']);
                    $rowUser = mysql_fetch_array($sqlUser);
                    echo $rowUser['first_name']." ".$rowUser['last_name']; 
                } else {
                    echo $this->data['User']['first_name']." ".$this->data['User']['last_name']; 
                }
                ?>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 7px; text-align: center;">Developed by UDAYA Technology Co.,Ltd.</td>
        </tr>
    </table>
    <?php
            $number++;
        }
    }
    ?>
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