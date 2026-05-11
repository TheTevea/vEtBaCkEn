
<table cellpadding="0" cellspacing="0" style="width: 300px;">
    <tr>
        <td style="width: 90px;"><?php echo TABLE_NOTE; ?> :</td>
        <td>
            <textarea id="updateTicketNote"></textarea>
        </td>
    </tr>
    <tr>
        <td style="width: 90px;"><?php echo MENU_PICK_UP; ?> :</td>
        <td>
            <select style="width: 150px; height: 25px;" id="updateTicketPickup">
                <option value=""><?php echo INPUT_SELECT; ?></option>
                <?php
                $sqlPK = mysql_query("SELECT t_pick_ups.id, t_pick_ups.name FROM t_pick_ups WHERE is_active = 1");
                while($rowPK = mysql_fetch_array($sqlPK)){
                ?>
                <option value="<?php echo $rowPK['id']; ?>"><?php echo $rowPK['name']; ?></option>
                <?php
                }
                ?>
            </select>
        </td>
    </tr>
</table>
