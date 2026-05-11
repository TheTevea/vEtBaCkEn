<?php
include('includes/function.php');
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
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
    });
</script>
<div class="leftPanel">
    <div id="<?php echo $printArea; ?>">
        <?php
        $msg = MENU_REPORT_AGENCY.'<br/>';
        $condtion = '';
        if($_POST['branch'] != '') {
            $sqlBranch = mysql_query("SELECT name FROM branches WHERE id = ".$_POST['branch']);
            $rowBranch = mysql_fetch_array($sqlBranch);
            $msg .= 'សាខា: '.$rowBranch[0].'<br/>';
            $condtion .= ' AND t_tickets.branch_id = '.$_POST['branch'];
        } else {
            $condtion .= ' AND t_tickets.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
        }
        if($_POST['date_from']!='') {
            $msg .= 'ថ្ងៃទី : '.$_POST['date_from'];
            $condtion .= " AND t_tickets.date <= '".dateConvert($_POST['date_from'])."'";
        }
        if($_POST['date_to'] != '') {
            if($_POST['date_to'] != $_POST['date_from']){
                $msg .= ' ទៅ: '.$_POST['date_to'];
            }
            $condtion .= " AND t_tickets.date <= '".dateConvert($_POST['date_to'])."'";
        }
        echo $this->element('/print/header-report',array('msg'=>$msg));
        ?>
        <div id="dynamic">
            <table class="table_print" cellspacing="0">
                <tbody>
                    <?php
                    $sqlAgent = mysql_query("SELECT id, first_name, last_name FROM t_agents WHERE status = 1;");
                    $totalAgentPrice = 0;
                    while($rowAgent = mysql_fetch_array($sqlAgent)){
                    ?>
                    <tr>
                        <td colspan="6" style="text-align: left; padding: 5px; font-size: 12px;"><?php echo $rowAgent['first_name']." ".$rowAgent['last_name']; ?></td>
                    </tr>
                    <tr>
                        <th class="first" style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo TABLE_NO; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo TABLE_TICKET_CODE; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo TABLE_BOOKING_DATE; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo TABLE_JOURNEY_DATE; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo TABLE_DEPARTURE_TIME; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo TABLE_DIRECTION; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo TABLE_CUSTOMER_PHONE; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px;">Ref</th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo TABLE_TICKET_FARE; ?></th>
                    </tr>
                    <?php
                        $sqlTicket = mysql_query("SELECT t_tickets.code, t_tickets.date, t_tickets.journey_date, t_tickets.journey_time, t_journeys.description, t_tickets.telephone, t_tickets.reference_code, (IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0)) AS amount FROM t_tickets INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id WHERE t_tickets.status = 2".$condtion);
                        $index = 0;
                        $totalTicketPrice = 0;
                        $symbol = '';
                        while($rowTicket = mysql_fetch_array($sqlTicket)){
                            $totalTicketPrice += $rowTicket['amount'];
                            $totalAgentPrice  += $rowTicket['amount'];
                    ?>
                    <tr>
                        <td class="first" style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo ++$index; ?></td>
                        <td style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo $rowTicket['code']; ?></td>
                        <td style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo dateShort($rowTicket['date']); ?></td>
                        <td style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo dateShort($rowTicket['journey_date']); ?></td>
                        <td style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo $rowTicket['journey_time']; ?></td>
                        <td style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo $rowTicket['description']; ?></td>
                        <td style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo $rowTicket['telephone']; ?></td>
                        <td style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo $rowTicket['reference_code']; ?></td>
                        <td style="font-size: 12px; font-weight: bold; padding: 5px;"><?php echo $rowTicket['amount']; ?></td>
                    </tr>
                    <?php
                        }
                    ?>
                    <tr>
                        <td style="text-align: left; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="8">Sub Total</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($totalTicketPrice, 2); ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td style="text-align: left; padding: 5px;  font-size: 12px; font-weight: bold;" colspan="8">Total</td>
                        <td style="text-align: right; padding: 5px; font-size: 14px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"></span><?php echo number_format($totalAgentPrice, 2); ?></td>
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