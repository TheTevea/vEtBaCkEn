<?php
include("includes/function.php");
?>
<table cellpadding="5" cellspacing="0" style="width: 730px;" class="table">
    <tr>
        <th class="first"><?php echo TABLE_NO; ?></th>
        <th><?php echo GENERAL_DESCRIPTION; ?></th>
        <th style="width: 160px;"><?php echo TABLE_CREATED; ?></th>
        <th style="width: 130px;"><?php echo TABLE_FOLLOW_UP_BY; ?></th>
    </tr>
    <?php
    $i = 0;
    $sqlFollowUp = mysql_query("SELECT t_ticket_followups.description, t_ticket_followups.created, CONCAT(users.first_name,' ',users.last_name) AS user FROM t_ticket_followups INNER JOIN users ON users.id = t_ticket_followups.created_by WHERE t_ticket_followups.t_ticket_id = ".$id." ORDER BY t_ticket_followups.id DESC");
    while($rowFollowUp = mysql_fetch_array($sqlFollowUp)){
    ?>
    <tr>
        <td class="first"><?php echo ++$i; ?></td>
        <td><?php echo $rowFollowUp['description']; ?></td>
        <td><?php echo dateShort($rowFollowUp['created'], "d/m/Y H:i:s"); ?></td>
        <td><?php echo $rowFollowUp['user']; ?></td>
    </tr>
    <?php
    }
    ?>
</table>