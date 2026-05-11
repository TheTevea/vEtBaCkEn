<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackTTicket").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTTicket">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo "ត្រលប់"; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset style="min-width: 330px; margin: 0px auto; overflow: auto; height: 200px;">
    <legend><?php __("ព​ត៌​មានសំបុត្រ"); ?></legend>
    <table style="width: 100%;" cellpadding="5">
        <tr>
            <th style="width: 120px;"><?php __("ថ្ងៃកក់"); ?></th>
            <td style="width: 30px;">:</td>
            <td>
                <?php echo dateShort($this->data['TTicket']['date']); ?>
            </td>
        </tr>
        <tr>
            <th><?php __("លេខកូដ"); ?></th>
            <td>:</td>
            <td>
                <?php echo $this->data['TTicket']['code']; ?>
            </td>
        </tr>
        <tr>
            <th><?php __("ថ្ងៃធ្វើដំណើរ"); ?></th>
            <td>:</td>
            <td>
                <?php 
                if($this->data['TTicket']['is_open_date'] == 0){
                    echo dateShort($this->data['TTicket']['journey_date']); 
                } else {
                    echo 'Open';
                }
                ?>
            </td>
        </tr>
        <tr>
            <th><?php __("ម៉ោងចេញដំណើរ"); ?></th>
            <td>:</td>
            <td>
                <?php 
                if($this->data['TTicket']['is_open_date'] == 0){
                    echo $this->data['TTicket']['journey_time']; 
                }
                ?>
            </td>
        </tr>
        <tr>
            <th><?php __("ចេញពីទិសដៅ"); ?></th>
            <td>:</td>
            <td>
                <?php 
                $sqlFrom = mysql_query("SELECT name FROM t_destinations WHERE id = ".$this->data['TTicket']['t_destination_from_id']);
                $rowFrom = mysql_fetch_array($sqlFrom);
                echo $rowFrom[0];
                ?>
            </td>
        </tr>
        <tr>
            <th><?php __("ទៅទិសដៅ"); ?></th>
            <td>:</td>
            <td>
                <?php 
                $sqlTo = mysql_query("SELECT name FROM t_destinations WHERE id = ".$this->data['TTicket']['t_destination_to_id']);
                $rowTo = mysql_fetch_array($sqlTo);
                echo $rowTo[0];
                ?>
            </td>
        </tr>
        <tr>
            <th><?php __("តម្លៃលក់"); ?></th>
            <td>:</td>
            <td>
                <?php echo number_format($this->data['TTicket']['total_amount'], 2).' '.$this->data['CurrencyCenter']['symbol']; ?>
            </td>
        </tr>
        <tr>
            <th><?php __("កំរៃជើងសារ"); ?></th>
            <td>:</td>
            <td>
            <?php echo number_format($this->data['TTicket']['commission'], 2).' '.$this->data['CurrencyCenter']['symbol']; ?>
            </td>
        </tr>
        <tr>
            <th><?php __("តម្លៃ Markup"); ?></th>
            <td>:</td>
            <td>
                <?php echo number_format($this->data['TTicket']['total_markup'], 2).' '.$this->data['CurrencyCenter']['symbol']; ?>
            </td>
        </tr>
        <tr>
            <th><?php __("ទីតាំងឡើង"); ?></th>
            <td>:</td>
            <td>
                <?php echo $this->data['TBoardingPoint']['name']; ?>
            </td>
        </tr>
        <tr>
            <th><?php __("ទីតាំងចុះ"); ?></th>
            <td>:</td>
            <td>
                <?php echo $this->data['TDropOff']['name']; ?>
            </td>
        </tr>
        <tr>
            <th><?php __("លេខកៅអី"); ?></th>
            <td>:</td>
            <td>
                <?php 
                $sqlSeat = mysql_query("SELECT GROUP_CONCAT(label_number) FROM t_ticket_details WHERE t_ticket_id = ".$this->data['TTicket']['id']." AND is_active = 1");
                $rowSeat = mysql_fetch_array($sqlSeat);
                echo $rowSeat[0]; 
                ?>
            </td>
        </tr>
        <tr>
            <th><?php __("លេខទូរស័ព្ទ"); ?></th>
            <td>:</td>
            <td>
                <?php echo $this->data['TTicket']['telephone']; ?>
            </td>
        </tr>
    </table>
 </fieldset>