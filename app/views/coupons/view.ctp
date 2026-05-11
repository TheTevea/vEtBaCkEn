<?php
include("includes/function.php"); 
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackCoupon").unbind('click').click(function(event){
            event.preventDefault();
            var rightPanel=$(this).closest('.rightCouponPanel');
            var leftPanel=rightPanel.parent().find(".leftCouponPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackCoupon">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_COUPON_TYPE_INFO); ?></legend>
    <table width="100%" class="info">
        <tr>
            <th><?php __(TABLE_CODE); ?></th>
            <td><?php echo $this->data['Coupon']['code']; ?></td>
        </tr>
        <tr>
            <th><?php echo REPORT_FROM; ?></th>
            <td><?php echo $this->data['Coupon']['start']; ?></td>
        </tr>
        <tr>
            <th><?php echo REPORT_TO; ?></th>
            <td><?php echo $this->data['Coupon']['end']; ?></td>
        </tr>
        <tr>
            <th><?php echo GENERAL_AMOUNT; ?></th>
            <td><?php echo $this->data['Coupon']['amount']; ?></td>
        </tr>
        <tr>
            <th><?php echo TABLE_TIME_USE; ?></th>
            <td><?php echo $this->data['Coupon']['total_time_use']; ?></td>
        </tr>
        <tr>
            <th><?php echo __('Coupon Type', true); ?></th>
            <td>
                <?php 
                if(!empty($this->data['Coupon']['coupon_type_id'])){
                    $sqlType = mysql_query("SELECT name FROM coupon_types WHERE id = ".intval($this->data['Coupon']['coupon_type_id'])." LIMIT 1");
                    if($sqlType && mysql_num_rows($sqlType)){
                        $rowType = mysql_fetch_array($sqlType);
                        echo $rowType['name'];
                    }
                }
                ?>
            </td>
        </tr>
        <tr>
            <th><?php echo __('Status', true); ?></th>
            <td><?php echo $this->data['Coupon']['status']; ?></td>
        </tr>
        <tr>
            <th><?php echo __('Created', true); ?></th>
            <td><?php echo $this->data['Coupon']['created']; ?></td>
        </tr>
        <tr>
            <th><?php echo __('Created By', true); ?></th>
            <td>
                <?php 
                if(!empty($this->data['Coupon']['created_by'])){
                    $sqlUser = mysql_query("SELECT username FROM users WHERE id = ".intval($this->data['Coupon']['created_by'])." LIMIT 1");
                    if($sqlUser && mysql_num_rows($sqlUser)){
                        $rowUser = mysql_fetch_array($sqlUser);
                        echo $rowUser['username'];
                    }
                }
                ?>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php echo __('Coupon Uses', true); ?></legend>
    <table width="100%" class="list">
        <tr>
            <th><?php echo TABLE_NO; ?></th>
            <th><?php echo __('Ticket Code', true); ?></th>
            <th><?php echo GENERAL_AMOUNT; ?> ($)</th>
            <th><?php echo __('Created', true); ?></th>
        </tr>
        <?php
        $couponId = isset($this->data['Coupon']['id']) ? intval($this->data['Coupon']['id']) : 0;
        if ($couponId > 0) {
            $no = 1;
            $sqlUse = mysql_query("SELECT ct.t_ticket_id, ct.amount, ct.created 
                                   FROM coupon_transactions ct
                                   WHERE ct.coupon_id = ".$couponId." ORDER BY ct.created DESC");
            if ($sqlUse && mysql_num_rows($sqlUse) > 0) {
                while ($rowUse = mysql_fetch_array($sqlUse)) {
                    $rowUse['ticket_code'] = "";
                    $sqlTicket = mysql_query("SELECT code 
                                              FROM t_tickets WHERE id = ".$rowUse['t_ticket_id']."
                                              UNION ALL
                                              SELECT code 
                                              FROM t_ticket_3months WHERE id = ".$rowUse['t_ticket_id']."
                                              ");
                    if ($sqlTicket && mysql_num_rows($sqlTicket) > 0) {
                        $rowTicket = mysql_fetch_array($sqlTicket);
                        $rowUse['ticket_code'] = $rowTicket['code'];
                    }
                    echo '<tr>';
                    echo '<td>'.$no.'</td>';
                    echo '<td>'.$rowUse['ticket_code'].'</td>';
                    echo '<td>'.number_format($rowUse['amount'], 2).'</td>';
                    echo '<td>'.dateShort($rowUse['created'], "d/m/Y H:i").'</td>';
                    echo '</tr>';
                    $no++;
                }
            } else {
                echo '<tr><td colspan="4" style="text-align:center;">'.__('No usage found', true).'</td></tr>';
            }
        } else {
            echo '<tr><td colspan="4" style="text-align:center;">'.__('No usage found', true).'</td></tr>';
        }
        ?>
    </table>
</fieldset>