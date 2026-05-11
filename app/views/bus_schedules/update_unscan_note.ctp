<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#BusScheduleUpdateUnscanNoteForm").unbind("submit").submit(function(){
            return false;
        });
    });
</script>
<?php echo $this->Form->create('BusSchedule', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<table cellpadding="10" cellspacing="0" style="width: 500px;">
    <tr>
        <td>
            <?php
            $note = "";
            $sqlNote = mysql_query("SELECT * FROM bus_schedule_seat_notes WHERE bus_schedule_id = ".$id." AND seat_number = '".$seatNumber."' AND is_active = 1 LIMIT 1");
            if(mysql_num_rows($sqlNote)){
                $rowNote = mysql_fetch_array($sqlNote);
                $note = $rowNote['note'];
            }
            ?>
            <textarea name="data[note]" style="width: 100%; height: 300px;"><?php echo $note; ?></textarea>
        </td>
    </tr>
</table>
<?php echo $this->Form->end(); ?>