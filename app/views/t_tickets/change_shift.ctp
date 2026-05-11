<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#TTicketChangeShiftForm").submit(function(){
            return false;
        });

        $("#btnSearchTicketChangeShift").unbind("click").click(function(event){
            event.preventDefault();
            if($("#searchChangeShift").val() != ""){
                $.ajax({
                    type:   "POST",
                    dataType: "json",
                    url:    "<?php echo $this->base . "/".$this->params['controller']."/checkTicketChangeShift/"; ?>",
                    data:   "data[code]="+$("#searchChangeShift").val(),
                    beforeSend: function(){
                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        $("#btnSearchTicketChangeShift").attr("disabled", true);
                        $("#lblBtnSearchTicketChangeShift").text("Loading..");
                    },
                    success: function(msg){
                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                        $("#btnSearchTicketChangeShift").attr("disabled", false);
                        $("#lblBtnSearchTicketChangeShift").text("<?php echo GENERAL_SEARCH; ?>");
                        $("#responseChangeShift").html(msg.response);
                    }
                });
            }
        });
    });
</script>
<?php echo $this->Form->create('TTicket'); ?>
<table>
    <tr>
        <td>
            <div class="inputContainer">
                <input type="text" id="searchChangeShift" style="width: 250px; height: 25px;" autocomplete="off" placeholder="Enter Ticket Code" />
            </div>
        </td>
        <td>
            <button id="btnSearchTicketChangeShift" style="width: 80px; height: 30px; cursor: pointer;"><span id="lblBtnSearchTicketChangeShift"><?php echo GENERAL_SEARCH; ?></span></button>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <div id="warningChageShift" style="display: none; font-size: 12px; font-weight: bold; color: red;">Please search ticket and select seat!</div>
            <div>
                <table cellpadding="2" style="width: 100%;" class="table">
                    <thead>
                        <tr>
                            <th class="first" style="width: 40%;">Code</th>
                            <th>Telephone</th>
                            <th>Seat #</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="responseChangeShift">
                        
                    </tbody>
                </table>
            </div>
        </td>
    </tr>
</table>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>