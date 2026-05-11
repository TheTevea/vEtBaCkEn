<?php
include('includes/function.php');
$rnd       = rand();
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
        $msg = '<b style="font-size: 18px;">' . REPORT_SALES_TICKET_BRANCH . ' (SUMMARY)</b><br /><br />';
        if($_POST['status']!='') {
            $condition = "t_tickets.status = ".$_POST['status'];
        } else {
            $condition = "t_tickets.status >= 0";
        }
        $condition .= " AND t_tickets.offline_project_id = ".$user['User']['offline_project_id'];
        if($_POST['booking_from'] !='' ) {
            $msg .= TABLE_BOOKING_FROM.': '.$_POST['booking_from'];
            $condition .= " AND t_tickets.date >= '".dateConvert($_POST['booking_from'])."'";
        }
        if($_POST['booking_to'] !='' ) {
            $msg .= ' '.TABLE_BOOKING_TO.': '.$_POST['booking_to'];
            $condition .= " AND t_tickets.date <= '".dateConvert($_POST['booking_to'])."'";
        }
        $msg .= "<br/>";
        if($_POST['traveling_from'] != '') {
            $msg .= TABLE_TRAVELING_FROM.': '.$_POST['traveling_from'];
            $condition .= " AND t_tickets.journey_date >= '".dateConvert($_POST['traveling_from'])."'";
        }
        if($_POST['traveling_to']!='') {
            $msg .= ' '.TABLE_TRAVELING_TO.': '.$_POST['traveling_to'];
            $condition .= " AND t_tickets.journey_date <= '".dateConvert($_POST['traveling_to'])."'";
        }
        if($_POST['company']!='') {
            $sqlCompany = mysql_query("SELECT name FROM companies WHERE id IN (".$_POST['company'].")");
            $rowCompany = mysql_fetch_array($sqlCompany);
            $msg .= '<br/>'.MENU_COMPANY_MANAGEMENT.': '.$rowCompany[0];
            $condition .= " AND t_tickets.company_id IN (".$_POST['company'].")";
        } else {
            $condition .= " AND t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '" . $user['User']['id']. "')";
        }
    
        if($_POST['main_branch']!='') {
            $sqlMB = mysql_query("SELECT name FROM main_branches WHERE id = ".$_POST['main_branch']);
            $rowMB = mysql_fetch_array($sqlMB);
            $msg  .= '<br/>'.MENU_MAIN_BRANCH.': '.$rowMB[0];
            $condition .= " AND t_tickets.main_branch_id = ".$_POST['main_branch'];
        } else {
            if($user['User']['is_admin'] == 0){
                $condition .= ' AND t_tickets.main_branch_id = '.$user['User']['main_branch_id'];
            } else {
                $condition .= ' AND t_tickets.main_branch_id > 0';
            }
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
        if($_POST['created_by']!='') {
            $sqlUser = mysql_query("SELECT username FROM users WHERE id = ".$_POST['created_by']);
            $rowUser = mysql_fetch_array($sqlUser);
            $msg .= '<br/>'.MENU_USERS.': '.$rowUser[0];
            $condition .= " AND t_tickets.created_by = ".$_POST['created_by'];
        }
        if($_POST['payment_method']!='') {
            $condition .= " AND t_tickets.payment_method_id = ".$_POST['payment_method'];
        }
        echo $this->element('/print/header-report',array('msg'=>$msg));
        ?>
        <div id="dynamic">
            <table class="table_print" cellspacing="0">
                <tbody>
                    <tr>
                        <th class="first" style="width: 10%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8;"><?php echo TABLE_NO; ?></th>
                        <th style="font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo MENU_MAIN_BRANCH; ?></th>
                        <th style="width: 20%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: left;"><?php echo TABLE_TOTAL_BOOKED; ?></th>
                        <th style="width: 20%; font-size: 12px; font-weight: bold; padding: 5px; background: #c8c8c8; text-align: right;"><?php echo GENERAL_AMOUNT; ?> ($)</th>
                    </tr>
                    <?php
                    $index = 0;
                    $sqlTicket = mysql_query("SELECT main_branches.name AS branch_name, COUNT(t_tickets.id) AS total_booked, SUM(IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0)) AS total_amount 
                                            FROM t_tickets 
                                            INNER JOIN main_branches ON main_branches.id = t_tickets.main_branch_id
                                            WHERE ".$condition." AND t_tickets.id IN (SELECT t_ticket_id FROM t_ticket_details WHERE t_ticket_id > 0 AND is_active = 1) GROUP BY t_tickets.main_branch_id ORDER BY main_branches.name");
                    if(mysql_num_rows($sqlTicket)){
                        $totalAmt = 0;
                        while($rowTicket = mysql_fetch_array($sqlTicket)){
                    ?>
                    <tr>
                        <td style="padding: 5px; font-size: 12px; text-align: center;">
                            <?php 
                            echo ++$index;
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo $rowTicket['branch_name'];
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px;">
                            <?php 
                            echo $rowTicket['total_booked']; 
                            ?>
                        </td>
                        <td style="padding: 5px; font-size: 12px; text-align: right;">
                            <?php 
                            $totalAmt += $rowTicket['total_amount']; 
                            echo number_format($rowTicket['total_amount'], 2); 
                            ?>
                        </td>
                    </tr>
                    <?php
                        }
                    ?>
                    <tr>
                        <td colspan="3" style="text-align: right;"><?php echo TABLE_TOTAL_FARE; ?></td>
                        <td style="font-size: 12px; font-weight: bold; text-align: right;"><?php echo number_format($totalAmt, 2); ?></td>
                    </tr>
                    <?php
                    } else {
                    ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 5px;"><?php echo TABLE_NO_RECORD; ?></td>
                    </tr>
                    <?php
                    }
                    ?>
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