<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackTDestination").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTDestination.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTDestination">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_DESTINATION_INFO); ?></legend>
    <table width="100%" class="info">
        <td style="width: 50%; vertical-align: top;">
            <table style="width: 100%;">
                <tr>
                    <th style="width:20%;"><?php __(MENU_PROVINCE_MANAGEMENT); ?></th>
                    <td style="width:1%;">:</td>
                    <td><?php echo $this->data['Province']['name']; ?></td>
                </tr>
                <tr>
                    <th style="width:20%;"><?php __("Group"); ?></th>
                    <td style="width:1%;">:</td>
                    <td><?php echo $this->data['TDestinationGroup']['name']; ?></td>
                </tr>
                <tr>
                    <th style="width:20%;"><?php __(TABLE_CODE); ?></th>
                    <td style="width:1%;">:</td>
                    <td><?php echo $this->data['TDestination']['code']; ?></td>
                </tr>
                <tr>
                    <th style="width:20%;"><?php __(TABLE_NAME); ?> (Khmer)</th>
                    <td style="width:1%;">:</td>
                    <td><?php echo $this->data['TDestination']['name_kh']; ?></td>
                </tr>
                <tr>
                    <th style="width:20%;"><?php __(TABLE_NAME); ?></th>
                    <td style="width:1%;">:</td>
                    <td><?php echo $this->data['TDestination']['name']; ?></td>
                </tr>
                <tr>
                    <th style="width:20%;"><?php __(TABLE_NAME); ?> (Chinese)</th>
                    <td style="width:1%;">:</td>
                    <td><?php echo $this->data['TDestination']['name_cn']; ?></td>
                </tr>
            </table>
        </td>
        <td style="width: 50%; vertical-align: top;">
            <table style="width: 100%;">
                <tr>
                    <th style="width:20%;"><?php __(REPORT_TO); ?></th>
                    <td style="width:1%;">:</td>
                    <td>
                        <?php
                            $destiTo = '';
                            $sqlTo = mysql_query("SELECT GROUP_CONCAT(name) FROM t_destinations WHERE id IN (SELECT t_destination_to_id FROM t_destination_tos WHERE t_destination_from_id = ".$this->data['TDestination']['id']." AND is_active = 1)");
                            if(mysql_num_rows($sqlTo)){
                                $rowTo = mysql_fetch_array($sqlTo);
                                $destiTo = $rowTo[0];
                            }
                            echo $destiTo;
                        ?>
                    </td>
                </tr>
            </table>
        </td>
    </table>
 </fieldset>