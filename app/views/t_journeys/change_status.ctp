<script type="text/javascript">
    $(document).ready(function(){
        $("input[name='data[active_type]']").unbind("click").click(function(){
            $("#changeStatusJourneyStart, #changeStatusJourneyEnd").removeAttr("class");
            if($(this).val() == '1'){
                $(".divChangeStatusCustomize").hide();
            } else {
                $(".divChangeStatusCustomize").show();
                $("#changeStatusJourneyStart, #changeStatusJourneyEnd").attr("class", "validate[required]");
            }
        });
        var dates = $("#changeStatusJourneyStart, #changeStatusJourneyEnd").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "changeStatusJourneyStart" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
            }
        });
    });
</script>
<form id="frmChangeStatusJourney">
    <table style="width: 100%;" cellpadding="5">
        <tr>
            <td colspan="2">
                <input type="radio" value="1" name="data[apply_type]" id="changeStatusJourneyApplyTypeThis" checked="" /> <label for="changeStatusJourneyApplyTypeThis"><?php echo 'Only this journey'; ?></label>
                <input type="radio" value="2" name="data[apply_type]" id="changeStatusJourneyApplyTypeAll" /> <label for="changeStatusJourneyApplyTypeAll"><?php echo 'Apply for this route'; ?></label>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="radio" value="1" name="data[active_type]" id="changeStatusJourneyActiveTypePer" checked="" /> <label for="changeStatusJourneyActiveTypePer"><?php echo TABLE_PERMANENT; ?></label>
                <input type="radio" value="2" name="data[active_type]" id="changeStatusJourneyActiveTypeCus" /> <label for="changeStatusJourneyActiveTypeCus"><?php echo TABLE_CUSTOMIZE; ?></label>
            </td>
        </tr>
        <tr class="divChangeStatusCustomize" style="display: none;">
            <td><?php __(TABLE_START_DATE); ?> :</td>
            <td>
                <input type="text" name="data[active_start]" id="changeStatusJourneyStart" style="width: 90%; height: 25px;" />
            </td>
        </tr>
        <tr class="divChangeStatusCustomize" style="display: none;">
            <td><?php __(TABLE_END_DATE); ?> :</td>
            <td>
                <input type="text" name="data[active_end]" id="changeStatusJourneyEnd" style="width: 90%; height: 25px;" />
            </td>
        </tr>
    </table>
    <div style="clear: both;"></div>
</form>