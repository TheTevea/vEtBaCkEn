<?php
include('includes/function.php');
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnPaidAll  = "btnPayAll" . $rnd;
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
        
        // Payment
        $(".btnAgentPayment").click(function(event){
            event.preventDefault();
            var id = $(this).attr('rel');
            var name = $(this).attr('name');
            var obj  = $(this);
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to paid for <b>' + name + '</b>?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_YES; ?>': function() {
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/agentPaid/" + id,
                            data: "",
                            beforeSend: function(){
                                $("#dialog").dialog("close");
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                            },
                            success: function(result){
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                obj.hide().closest("td").find("span").text("Paid");
                                obj.hide().closest("td").find("input[name='chkAgencyPay']").hide();
                                // Alert Message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                                    createSysAct('Ticket', 'Agency Payment', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Ticket', 'Agency Payment', 1, '');
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                }
                                $("#dialog").dialog({
                                    title: '<?php echo DIALOG_INFORMATION; ?>',
                                    resizable: false,
                                    modal: true,
                                    width: 'auto',
                                    height: 'auto',
                                    buttons: {
                                        '<?php echo ACTION_CLOSE; ?>': function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                            }
                        });
                    },
                    '<?php echo ACTION_NO; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
        
        $("#<?php echo $btnPaidAll; ?>").click(function(event){
            event.preventDefault();
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to paid for all checked?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_YES; ?>': function() {
                        $("input[name='chkAgencyPay']").each(function(){
                            if($(this).is(':checked')){
                                var id  = $(this).attr('data');
                                var obj = $(this);
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/agentPaid/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        obj.closest("td").find(".btnAgentPayment").hide();
                                        obj.hide().closest("td").find("span").text("Paid");
                                        // Alert Message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                                            createSysAct('Ticket', 'Agency Payment All', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Ticket', 'Agency Payment All', 1, '');
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                        }
                                        $("#dialog").dialog({
                                            title: '<?php echo DIALOG_INFORMATION; ?>',
                                            resizable: false,
                                            modal: true,
                                            width: 'auto',
                                            height: 'auto',
                                            buttons: {
                                                '<?php echo ACTION_CLOSE; ?>': function() {
                                                    $(this).dialog("close");
                                                }
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    },
                    '<?php echo ACTION_NO; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
    });
</script>
<div class="leftPanel">
    <div id="<?php echo $printArea; ?>">
        <?php
        $msg = MENU_REPORT_AGENCY.'<br/>';
        $condtion = '';
        if($_POST['company'] != '') {
            $sqlCompany = mysql_query("SELECT name FROM companies WHERE id = ".$_POST['company']);
            $rowCompany = mysql_fetch_array($sqlCompany);
            $msg .= 'Company: '.$rowCompany[0].'<br/>';
            $condtion .= ' AND t_tickets.company_id = '.$_POST['company'];
        } else {
            $condtion .= ' AND t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
        }
        if($_POST['branch'] != '') {
            $sqlBranch = mysql_query("SELECT name FROM branches WHERE id = ".$_POST['branch']);
            $rowBranch = mysql_fetch_array($sqlBranch);
            $msg .= 'Branch: '.$rowBranch[0].'<br/>';
            $condtion .= ' AND t_tickets.branch_id = '.$_POST['branch'];
        } else {
            $condtion .= ' AND t_tickets.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
        }
        if($_POST['view_date'] == 1){
            $dateFilter = 't_tickets.date';
        } else {
            $dateFilter = 't_tickets.journey_date';
        }
        if($_POST['date_from']!='') {
            $msg .= 'From : '.$_POST['date_from'];
            $condtion .= " AND ".$dateFilter." >= '".dateConvert($_POST['date_from'])."'";
        }
        if($_POST['date_to'] != '') {
            if($_POST['date_to'] != $_POST['date_from']){
                $msg .= ' To: '.$_POST['date_to'];
            }
            $condtion .= " AND ".$dateFilter." <= '".dateConvert($_POST['date_to'])."'";
        }
        if($_POST['show'] != '') {
           $condtion .= ' AND t_tickets.is_agent_confirm = '.$_POST['show'];
        }
        if($_POST['departure_time'] != '') {
           $condtion .= " AND t_tickets.journey_time = '".$_POST['departure_time']."'";
        }
        $agentCon = '';
        if($_POST['agent'] != '') {
            $agentCon = ' AND id = '.$_POST['agent'];
        }
        echo $this->element('/print/header-report',array('msg'=>$msg));
        ?>
        <div id="dynamic">
            <table class="table_print" cellspacing="0">
                <tbody>
                    <?php
                    $sqlAgent = mysql_query("SELECT id, first_name, last_name FROM t_agents WHERE status = 1".$agentCon);
                    $totalAgentPrice = 0;
                    while($rowAgent = mysql_fetch_array($sqlAgent)){
                        $sqlTicket = mysql_query("SELECT t_tickets.is_open_date, t_tickets.id, t_tickets.code, t_tickets.date, t_tickets.journey_date, t_tickets.journey_time, t_journeys.description, t_tickets.note, t_tickets.reference_code, t_tickets.is_agent_confirm, (IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0)) AS amount FROM t_tickets INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id WHERE t_tickets.status = 2 AND t_agent_id = ".$rowAgent['id'].$condtion." ORDER BY is_agent_confirm ASC");
                        if(mysql_num_rows($sqlTicket)){
                    ?>
                    <tr>
                        <td colspan="10" style="text-align: left; padding: 5px; font-size: 12px; font-weight: bold;"><?php echo $rowAgent['first_name']." ".$rowAgent['last_name']; ?></td>
                    </tr>
                    <tr>
                        <th class="first" style="font-size: 11px; font-weight: bold; padding: 5px;"><?php echo TABLE_NO; ?></th>
                        <th style="font-size: 11px; font-weight: bold; padding: 5px; width: 111px !important;"><?php echo TABLE_TICKET_CODE; ?></th>
                        <th style="font-size: 11px; font-weight: bold; padding: 5px; width: 111px !important;"><?php echo TABLE_BOOKING_DATE; ?></th>
                        <th style="font-size: 11px; font-weight: bold; padding: 5px; width: 111px !important;"><?php echo TABLE_JOURNEY_DATE; ?></th>
                        <th style="font-size: 11px; font-weight: bold; padding: 5px; width: 111px !important;"><?php echo TABLE_DEPARTURE_TIME; ?></th>
                        <th style="font-size: 11px; font-weight: bold; padding: 5px;"><?php echo TABLE_SEAT_NUMBER; ?></th>
                        <th style="font-size: 11px; font-weight: bold; padding: 5px;"><?php echo TABLE_DIRECTION; ?></th>
                        <th style="font-size: 11px; font-weight: bold; padding: 5px;">Pick Up</th>
                        <th style="font-size: 11px; font-weight: bold; padding: 5px; width: 120px !important;">Ref</th>
                        <th style="font-size: 11px; font-weight: bold; padding: 5px; width: 120px !important;"><?php echo TABLE_TICKET_FARE; ?></th>
                        <th style="font-size: 11px; font-weight: bold; padding: 5px; width: 120px !important;"><?php echo TABLE_STATUS; ?></th>
                    </tr>
                    <?php
                            $index = 0;
                            $totalTicketPrice = 0;
                            $symbol = '';
                            while($rowTicket = mysql_fetch_array($sqlTicket)){
                                $totalTicketPrice += $rowTicket['amount'];
                                $totalAgentPrice  += $rowTicket['amount'];
                                $sqlSeat = mysql_query("SELECT GROUP_CONCAT(seat_number) FROM t_ticket_details WHERE t_ticket_id = ".$rowTicket['id']);
                                $rowSeat = mysql_fetch_array($sqlSeat);
                    ?>
                    <tr>
                        <td class="first" style="font-size: 11px; font-weight: bold; padding: 5px; text-align: center;"><?php echo ++$index; ?></td>
                        <td style="font-size: 11px; font-weight: bold; padding: 5px; text-align: center;"><?php echo $rowTicket['code']; ?></td>
                        <td style="font-size: 11px; font-weight: bold; padding: 5px; text-align: center;"><?php echo dateShort($rowTicket['date']); ?></td>
                        <td style="font-size: 11px; font-weight: bold; padding: 5px; text-align: center;">
                            <?php 
                            if($rowTicket['is_open_date'] == 1){
                                echo 'Open Date';
                            } else {
                                if($rowTicket['journey_date'] != '' && $rowTicket['journey_date'] != '0000-00-00'){
                                    echo dateShort($rowTicket['journey_date']); 
                                }
                            }
                            ?>
                        </td>
                        <td style="font-size: 11px; font-weight: bold; padding: 5px; text-align: center;">
                            <?php
                            if($rowTicket['is_open_date'] == 1){
                                echo 'Open Date';
                            } else {
                                echo date("h:i A", strtotime($rowTicket['journey_time']));
                            }
                            ?>
                        </td>
                        <td style="font-size: 11px; font-weight: bold; padding: 5px; text-align: center;"><?php echo str_replace(",", ", ", $rowSeat[0]); ?></td>
                        <td style="font-size: 11px; font-weight: bold; padding: 5px;"><?php echo $rowTicket['description']; ?></td>
                        <td style="font-size: 11px; font-weight: bold; padding: 5px;"><?php echo $rowTicket['note']; ?></td>
                        <td style="font-size: 11px; font-weight: bold; padding: 5px;"><?php echo $rowTicket['reference_code']; ?></td>
                        <td style="font-size: 11px; font-weight: bold; padding: 5px; text-align: right;"><?php echo $rowTicket['amount']; ?></td>
                        <td style="font-size: 11px; font-weight: bold; padding: 5px; text-align: center;">
                            <?php
                            if($rowTicket['is_agent_confirm'] == 0){
                                echo '<span style="font-size: 11px; font-weight: bold;">UnPaid</span>';
                                echo ' <a href="" class="btnAgentPayment" rel="' . $rowTicket['id'] . '" name="' . $rowTicket['code'] . '"><img alt="Delete" onmouseover="Tip(\'Pay\')" src="' . $this->webroot . 'img/button/coins.png" /></a>';
                                echo ' <input type="checkbox" name="chkAgencyPay" data="' . $rowTicket['id'] . '" />';
                            } else {
                                echo 'Paid';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                            }
                    ?>
                    <tr>
                        <td style="text-align: left; padding: 5px;  font-size: 11px; font-weight: bold;" colspan="9">Sub Total</td>
                        <td style="text-align: right; padding: 5px; font-size: 11px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"><?php echo $symbol; ?></span><?php echo number_format($totalTicketPrice, 2); ?></td>
                        <td></td>
                    </tr>
                    <?php
                        }
                    }
                    ?>
                    <tr>
                        <td style="text-align: left; padding: 5px;  font-size: 11px; font-weight: bold;" colspan="9">Total</td>
                        <td style="text-align: right; padding: 5px; font-size: 11px; font-weight: bold;"><span style="float: left; width:20px; font-size: 14px;"></span><?php echo number_format($totalAgentPrice, 2); ?></td>
                        <td></td>
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
        <button type="button" id="<?php echo $btnPaidAll; ?>" class="positive">
            <img src="<?php echo $this->webroot; ?>img/button/coins.png" alt=""/>
            Paid By Check Box
        </button>
    </div>
    <div style="clear: both;"></div>
</div>
<div class="rightPanel"></div>