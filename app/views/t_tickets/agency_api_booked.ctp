<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#agencyApiFilterAgency").chosen({width: 160});
        $("#btnRefreshAgencyApi").unbind('click').click(function(){
            searchAgencyAPiBooked();
        });
        searchAgencyAPiBooked();
    });

    function searchAgencyAPiBooked(){
        var url = "agencyApiBookedResult";
        if($("#agencyApiFilterViewBy").val() == "2"){
            url = "agencyApiBookedDetail";
        }   
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base . '/'; ?>t_tickets/"+url+"/"+$("#agencyApiFilterAgency").val(),
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                $("#agencyApiRecordResult").html("<?php echo ACTION_LOADING; ?>");
                $("#lblRefreshAgencyApi").html("<?php echo ACTION_LOADING; ?>");
                $("#btnRefreshAgencyApi").attr("disabled", true);
            },
            success: function(result){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#lblRefreshAgencyApi").html("<?php echo GENERAL_SEARCH; ?>");
                $("#btnRefreshAgencyApi").attr("disabled", false);
                $("#agencyApiRecordResult").html(result);
            }
        });
    }
</script>
<div class="leftPanel">
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div style="float: right; width: 600px;">
            <table cellpadding="0" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="width: 55px;"><label for="agencyApiFilterViewBy"><?php echo "View By"; ?></label> :</td>
                    <td style="width: 160px;">
                        <select id="agencyApiFilterViewBy" style="width: 150px; height: 30px; font-size: 12px;">
                            <option value="1" selected=""><?php echo "Summary"; ?></option>
                            <option value="2"><?php echo "Detail"; ?></option>
                        </select>
                    </td>
                    <td style="width: 55px;"><label for="agencyApiFilterAgency"><?php echo "Agency"; ?></label> :</td>
                    <td style="width: 160px;">
                        <select id="agencyApiFilterAgency" style="width: 150px; height: 30px; font-size: 12px;">
                            <option value="all"><?php echo TABLE_ALL; ?></option>
                            <?php
                            foreach($agencyApis AS $agencyApi){
                            ?>
                            <option value="<?php echo $agencyApi['TAgent']['id']; ?>"><?php echo $agencyApi['TAgent']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <div class="buttons" style="float: right; margin-left: 10px;">
                            <button type="button" class="positive" id="btnRefreshAgencyApi">
                                <img src="<?php echo $this->webroot; ?>img/button/search.png" alt=""/>
                                <span id="lblRefreshAgencyApi"><?php echo GENERAL_SEARCH; ?></span>
                            </button>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div style="clear: both;"></div>
    </div>
    <br />
    <div id="agencyApiRecordResult"></div>
    <br />
    <br />
</div>
<div class="rightPanel"></div>