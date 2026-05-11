<?php
include('includes/function.php');
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $btnPrint; ?>").click(function(){
            $(".rowView").hide();
            $("#singatureNetProfit").show();
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
            $(".rowView").show();
            $("#singatureNetProfit").hide();
        });
    });
</script>
<div class="leftPanel">
    <div id="<?php echo $printArea; ?>">
        <?php
        $condtion  = '';
        $msg = '<b style="font-size: 18px;">របាយការណ៏ PHONE CALL</b><br />';
        $logo = "";
        if($_POST['company'] != '') {
            $sqlCompany = mysql_query("SELECT GROUP_CONCAT(name) FROM companies WHERE id IN (".$_POST['company'].")");
            $rowCompany = mysql_fetch_array($sqlCompany);
            $msg .= 'សាខា: '.$rowCompany[0].'<br/>';
            $sqlLogo = mysql_query("SELECT photo FROM companies WHERE id IN (".$_POST['company'].") LIMIT 1");
            $rowLogo = mysql_fetch_array($sqlLogo);
            $logo    = $rowLogo[0];
            $condtion  .= ' AND t_tickets.company_id IN ('.$_POST['company'].')';
        }
        if($_POST['branch'] != '') {
            $sqlBranch = mysql_query("SELECT name FROM branches WHERE id = ".$_POST['branch']);
            $rowBranch = mysql_fetch_array($sqlBranch);
            $msg .= 'សាខា: '.$rowBranch[0].'<br/>';
            $condtion  .= ' AND t_tickets.branch_id = '.$_POST['branch'];
        }
        if($_POST['date_from']!='') {
            $msg .= 'ថ្ងៃទី : '.$_POST['date_from'];
        }
        if($_POST['date_to']!='' && $_POST['date_to'] != $_POST['date_from']) {
            $msg .= ' ទៅ: '.$_POST['date_to'];
        }
        if($_POST['main_branch'] != '') {
            $sqlMB = mysql_query("SELECT name FROM main_branches WHERE id = ".$_POST['main_branch']);
            $rowMB = mysql_fetch_array($sqlMB);
            $msg .= '<br/ >សាខាដើម: '.$rowMB['name'];
            $condtion  .= ' AND t_tickets.main_branch_id = '.$_POST['main_branch'];
        } else {
            $condtion  .= ' AND t_tickets.main_branch_id = '.$user['User']['main_branch_id'];
        }
        $msg .= '<br />ថ្ងៃបោះពុម្ព: '.date("d/m/Y H:i:s");
        echo $this->element('/print/header-report',array('msg' => $msg, 'logo' => $logo));
        $symbol = '';
        $sqlSym = mysql_query("SELECT symbol FROM currency_centers WHERE id = 1;");
        $rowSym = mysql_fetch_array($sqlSym);
        $symbol = $rowSym[0];
        ?>
        <div id="dynamic">
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <th class="first" style="width: 5%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">No</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Ticket Code</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Booking Date</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Travel Date</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Departure Time</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Direction</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Type</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Seat No</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;">Amount</th>
                    </tr>
                    <?php
                    $seatNumber = 1;
                    $ticketId   = 0;
                    $totalPaid  = 0;
                    $index = 0;
                    $sqlAct = mysql_query("SELECT IFNULL(label_number, seat_number) AS seat, t_tickets.id, t_journeys.description, t_tickets.date, t_tickets.code, t_tickets.journey_date, t_tickets.journey_time, (IFNULL(t_ticket_details.total_amount, 0) - IFNULL(t_ticket_details.discount, 0)) AS total_amount, t_tickets.balance, t_tickets.is_open_date, t_tickets.price_type, t_tickets.edit_from, t_tickets.modified_by FROM t_ticket_details INNER JOIN t_tickets ON t_tickets.id = t_ticket_details.t_ticket_id INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id WHERE t_tickets.status = 1 AND t_tickets.type = 2 AND t_tickets.offline_project_id = ".$user['User']['offline_project_id']." AND journey_type IN (1, 2) AND t_tickets.date >= '".dateConvert($_POST['date_from'])."' AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'".$condtion." GROUP BY t_ticket_details.id;");
                    if(mysql_num_rows($sqlAct)){
                        while($rowAct = mysql_fetch_array($sqlAct)){
                            if($ticketId != $rowAct['id']){
                                $seatNumber = 1;
                                $ticketId   = $rowAct['id'];
                            }
                            $paid = $rowAct['total_amount'];
                            $totalPaid += $paid;
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: center;"><?php echo ++$index; ?></td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;"><?php echo $rowAct['code']."-".$seatNumber; ?></td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;"><?php echo dateShort($rowAct['date']); ?></td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php 
                            if($rowAct['is_open_date'] == 1){
                                echo 'Open Date';
                            } else {
                                echo dateShort($rowAct['journey_date']); 
                            }
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php 
                            if($rowAct['is_open_date'] == 1){
                                echo 'Open Date';
                            } else {
                                echo date("h:i A", strtotime($rowAct['journey_time'])); 
                            }
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;"><?php echo $rowAct['description']; ?></td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            Phone Call
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: center;"><?php echo $rowAct['seat']; ?></td>
                        <td style="text-align: right; padding: 5px; font-size: 12px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($paid, 2); ?></td>
                    </tr>
                    <?php
                            $seatNumber++;
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td style="text-align: right; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="8">Total</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($totalPaid, 2); ?></td>
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
    <div style="clear: both;"></div>
</div>
<div class="rightPanel"></div>