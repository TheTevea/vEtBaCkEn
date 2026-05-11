<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackQuestionFeedback").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableQuestionFeedback.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackQuestionFeedback">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<table width="100%" cellpadding="5">
    <tr>
        <th style="width: 10%; font-size: 12px;"><?php __(TABLE_NAME); ?> (English)</th>
        <td style="font-size: 12px;"><?php echo $this->data['QuestionFeedback']['name']; ?></td>
    </tr>
    <tr>
        <th style="width: 10%; font-size: 12px;"><?php __(TABLE_NAME); ?> (Khmer)</th>
        <td style="font-size: 12px;"><?php echo $this->data['QuestionFeedback']['name_kh']; ?></td>
    </tr>
    <tr>
        <th style="width: 10%; font-size: 12px;"><?php echo TABLE_TYPE; ?></th>
        <td style="font-size: 12px;">
            <?php
            if($this->data['QuestionFeedback']['type'] == 1){
                echo "Driver";
            } else if($this->data['QuestionFeedback']['type'] == 2){
                echo "Office";
            } 
            ?>
        </td>
    </tr>
</table>