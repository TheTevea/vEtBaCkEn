<?php echo $this->element('prevent_multiple_submit'); ?>
<style type="text/css" media="screen">
    .labelDropTBoat {
        width: 100px;
        height: 30px;
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        border: 1px solid #000;
        float: left;
        margin-left: 5px;
        margin-top: 5px;
        border: 1px #000 solid;
        text-align: center;
    }
    
    .chairTBoat {
        width: 50px; 
        height: 80px;
        float: left;
        margin-left: 5px;
        margin-top: 5px;
        border: 1px #000 solid;
        text-align: center;
    }
    
    .chairLabel {
        width: 100%; 
        text-align: center; 
        margin-bottom: 3px; 
        margin-top: 3px;
    }
    
    .chairImg {
        width: 35px; 
        height: 30px;
        margin: 0px auto;
        font-size: 12px;
        font-weight: bold;
        background-image: url("<?php echo $this->webroot; ?>img/button/seating-active-25.png");
        background-repeat: no-repeat;
        text-align: left;
    }
    
    .chairRemove {
        width: 100%; 
        margin-top: 3px;
    }
    
    #layoutContainerTBoat div.ui-draggable, #layoutContainerTBoat div.ui-draggable-dragging {
        font-size: 12px;
        font-weight: bold;
        margin-left: 5px;
        padding-top: 5px;
        padding-left: 5px;
    }
    
    #layoutContainerTBoat .ui-draggable-disabled, #layoutContainerTBoat .ui-state-disabled {
        opacity: 100;
    }
    
    .removeChairTBoat {
        display: none;
        cursor: pointer;
        width: 16px;
    }
    
    .layoutSelectTBoat{
        background-color: #79b7e7;
    }
</style>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".integer").autoNumeric({mDec: 0, aSep: ','});
        $("#TBoatCloneAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TBoatCloneAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $(".interger").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                var layout = convertLayoutToJsonTBoat();
                $("#TBoatLayout").val(layout);
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTBoat").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackTBoat").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Boat', 'Add New', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Boat', 'Add New', 1, '');
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                }
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        $(".btnBackTBoat").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTBoat.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        $("#TBoatNumberOfSeat").blur(function(){
            generateSeatTBoat();
            generateDefaultTableTBoat();
            resetDragTBoat();
        });
        
        $(".btnGenerateLayoutDropTBoat").click(function(event){
            event.preventDefault();
            var totalRow = replaceNum($("#layoutDropTboatRow").val());
            var totalColumn = replaceNum($("#layoutDropTboatColumn").val());
            if(totalRow > 0 && totalColumn > 0){
                generateSeatTBoat();
                generateDefaultTableTBoat();
                resetDragTBoat();
            }
        });
        
        $(".btnRowPlusLayoutDropTBoat").click(function(event){
            event.preventDefault();
            rowPlusTBoat();
        });
        
        $(".btnRowCutLayoutDropTBoat").click(function(event){
            event.preventDefault();
            rowCutTBoat();
        });
        
        $(".btnColPlusLayoutDropTBoat").click(function(event){
            event.preventDefault();
            colPlusTBoat();
        });
        
        $(".btnColCutLayoutDropTBoat").click(function(event){
            event.preventDefault();
            colCutTBoat();
        });
        
        $(".btnMergeLayoutDropTBoat").click(function(event){
            event.preventDefault();
            mergeTBoat();
        });
        
        $(".btnUnmergeLayoutDropTBoat").click(function(event){
            event.preventDefault();
            unMergeTBoat();
        });
        generateSeatTBoat();
        generateDefaultTableTBoat();
        resetDragTBoat();
        setDragPositionTBoat();
    });
    
    function resetDragTBoat(){
        $('#labelDropTBoatCapitain, #labelDropTBoatToilet, #labelDropTBoatOpen1, #labelDropTBoatOpen2').removeAttr("style").css('background', 'none');
        generateDragTBoat('#labelDropTBoatCapitain', 'Capitain');
        generateDragTBoat('#labelDropTBoatToilet', 'Toilet');
        generateDragTBoat('#labelDropTBoatOpen1', 'Open1');
        generateDragTBoat('#labelDropTBoatOpen2', 'Open2');
        eventRemoveDragPosition();
    }
    
    function eventRemoveDragPosition(){
        $(".removeChairTBoat").unbind('click');
        $(".removeChairTBoat").click(function(){
            var dragNum   = $(this).parent().parent().find(".chairImg").find("span").text();
            var columDrop = "#"+$(this).parent().parent().find(".layoutDropSeat").text();
            if(isNaN(dragNum) == true){
                // Label Seat
                var chairDrag = "#labelDropTBoat"+dragNum;
            } else {
                // Seat
                var chairDrag = "#chairTBoat"+dragNum;
            }
            $(chairDrag).removeAttr("style");
            // Reset Drag & Drop Function
            generateDragTBoat(chairDrag, dragNum);
            generateDropTBoat(columDrop);
            $(this).hide();
            // Reset Drag
            if(isNaN(dragNum) == false){
                $(chairDrag).find("input").removeAttr('disabled');
            }
            $(chairDrag).find("input").attr('disabled', false);
            $(chairDrag).find('.removeChairTBoat').hide();
            $(chairDrag).find('.layoutDropSeat').text('');
            $(chairDrag).css('visibility', 'visible');
            // Reset Drop
            $(columDrop).find("span.BoatNumber").text('');
            $(columDrop).find("span.BoatLabel").text('');
            $(columDrop).find(".layoutDisplaySeat").html('');
        });
    }
    
    function handleChairTBoatDrop(event, ui) {
        var number = ui.draggable.data('number');
        var dvClone = '';
        if(isNaN(number) == true){
            // Label Seat
            var drag = "#labelDropTBoat"+number;
            dvClone  = "labelDropTBoat";
        } else {
            // Seat
            var drag = '#chairTBoat'+number;
            dvClone  = "chairTBoat";
        }
        var label  = $(drag).find("input").val();
        var dropId = $(this).attr("id");
        // Config Drag
        $(drag).find("input").attr('disabled', true);
        $(drag).find('.removeChairTBoat').show();
        $(drag).find('.layoutDropSeat').text(dropId).hide();
        $(drag).css('visibility', 'hidden');
        // Clone Drag
        var cloneDrag   = $(drag).html();
        var layoutClone = '<div class="'+dvClone+'" style="padding: 0px; margin: 0px; border: none;">'+cloneDrag+'</div>';
        $(this).find(".layoutDisplaySeat").html(layoutClone);
        eventRemoveDragPosition();
        // Set Boat Number & Label
        $(this).find("span.BoatNumber").text(number).hide();
        $(this).find("span.BoatLabel").text(label).hide();
        $(this).droppable('disable');
        // Remove Select
        $("#layoutDropTBoat").find("td").removeClass("layoutSelectTBoat");
    }
    
    function generateDragTBoat(drag, number){
        destroyDragDropTBoat(drag);
        $(drag).data('number', number).draggable({
            containment: '#layoutContainerTBoat',
            cursor: 'move',
            revert: 'invalid' 
        });
    }
    
    function generateDropTBoat(drop){
        destroyDragDropTBoat(drop);
        $(drop).droppable( {
            accept: '.chairTBoat, .labelDropTBoat',
            hoverClass: 'hovered',
            drop: handleChairTBoatDrop
        });
    }
    
    function destroyDragDropTBoat(event){
        $(event).draggable('destroy');
        $(event).droppable('destroy');
        $(event).removeClass("ui-state-disabled").removeAttr("aria-disabled");
    }
    
    function generateDefaultTableTBoat(){
        var totalRow = replaceNum($("#layoutDropTboatRow").val());
        var totalColumn = replaceNum($("#layoutDropTboatColumn").val());
        $("#layoutDropTBoat").html('');
        if(totalRow > 0 && totalColumn > 0){
            $(".btnGenerateLayoutDropTBoat").find('span').text('<?php echo ACTION_LOADING; ?>');
            var totalTableWidth = 61 * totalColumn;
            for (var row = 1; row <= totalRow; row++) {
                var eventDrop = [];
                var table = '<tr>';
                for (var col = 1; col <= totalColumn; col++) {
                    var indexCol = row+"BT"+col;
                    table += createColTBoat(indexCol);
                    eventDrop[col] = indexCol;
                }
                table += '</tr>';
                $("#layoutDropTBoat").append(table);
                $.each( eventDrop, function( key, value ) {
                    if(value != '' && value != undefined){
                        var drop = '#layoutDropTBoat'+value;
                        generateDropTBoat(drop);
                    }
                });
            }
            $("#layoutDropTBoat").css('width', totalTableWidth);
            loadEventLayoutTBoat();
            $(".btnGenerateLayoutDropTBoat").find('span').text('Generate');
        }
    }
    
    function generateSeatTBoat(){
        var totalSeat = replaceNum($("#TBoatNumberOfSeat").val());
        $("#layoutSeatTBoat").html('');
        if(totalSeat > 0){
            for (var i = 1; i <= totalSeat; i++) {
                var seatLayout = '<div class="chairTBoat" id="chairTBoat'+i+'"><div class="chairLabel"><span class="layoutDropSeat"></span><input type="text" style="width: 50%; height: 10px; font-size: 11px;" value="'+i+'" /></div><div class="chairImg"><span style="display: none;">'+i+'</span></div><div class="chairRemove"><img src="<?php echo $this->webroot; ?>img/button/void.png" class="removeChairTBoat" /></div></div>';
                $("#layoutSeatTBoat").append(seatLayout);
                var drag = '#chairTBoat'+i;
                generateDragTBoat(drag, i);
            }
            $(".chairLabel input").unbind('blur');
            $(".chairLabel input").blur(function(){
                var number = $(this).closest(".chairTBoat").find(".chairImg").text();
                if($(this).val() == ""){
                    $(this).val(number);
                }
            });
        }
    }
    
    function loadEventLayoutTBoat(){
        $("#layoutDropTBoat").find("td").unbind('click');
        $("#layoutDropTBoat").find("td").click(function(){
            var checked = $(this).find("b").text();
            var chairDrog = $(this).find("span.BoatNumber").text();
            if(checked == '1' || chairDrog != ''){
                $(this).find("b").text('');
                $(this).removeClass("layoutSelectTBoat");
            } else {
                $(this).find("b").text(1);
                $(this).addClass("layoutSelectTBoat");
            }
        });
    }
    
    function rowPlusTBoat(){
        var totalRow = replaceNum($("#layoutDropTBoat tr").length) + 1;
        var totalColumn = 0;
        $("#layoutDropTBoat tr:first").find("td").each(function(){
            if(replaceNum($(this).attr('colspan')) > 0){
                totalColumn += replaceNum($(this).attr('colspan'));
            } else {
                totalColumn += 1;
            }
        });
        if(totalRow > 0 && totalColumn> 0){
            var eventDrop = [];
            var table = '<tr>';
            for (var col = 1; col <= totalColumn; col++) {
                var indexCol = totalRow+"BT"+col;
                table += createColTBoat(indexCol);
                eventDrop[col] = indexCol;
            }
            table += '</tr>';
            $("#layoutDropTBoat").append(table);
            $.each( eventDrop, function( key, value ) {
                if(value != '' && value != undefined){
                    var drop = '#layoutDropTBoat'+value;
                    generateDropTBoat(drop);
                }
            });
            loadEventLayoutTBoat();
            $("#layoutDropTboatRow").val(totalRow);
        }
    }
    
    function rowCutTBoat(){
        var totalRow = replaceNum($("#layoutDropTBoat tr").length);
        var totalColumn = replaceNum($("#layoutDropTBoat tr:first td").length);
        if(totalRow > 0 && totalColumn> 0){
            $("#layoutDropTBoat").find("tr:last").remove();
            $("#layoutDropTboatRow").val(totalRow - 1);
        }
    }
    
    function colPlusTBoat(){
        var totalRow = replaceNum($("#layoutDropTBoat tr").length);
        var totalColumn = 1;
        $("#layoutDropTBoat tr:first").find("td").each(function(){
            if(replaceNum($(this).attr('colspan')) > 0){
                totalColumn += replaceNum($(this).attr('colspan'));
            } else {
                totalColumn += 1;
            }
        });
        if(totalRow > 0 && totalColumn> 0){
            $("#layoutDropTBoat tr").each(function(index, value){
                var indexCol = index+"BT"+totalColumn;
                var col = createColTBoat(indexCol);
                $(this).find("td:last").after(col);
                var drop = '#layoutDropTBoat'+indexCol;
                generateDropTBoat(drop);
            });
            var totalTableWidth = 61 * totalColumn;
            $("#layoutDropTBoat").css('width', totalTableWidth);
            $("#layoutDropTboatColumn").val(totalColumn);
            loadEventLayoutTBoat();
        }
    }
    
    function colCutTBoat(){
        var totalRow = replaceNum($("#layoutDropTBoat tr").length);
        var totalColumn = 0;
        $("#layoutDropTBoat tr:first").find("td").each(function(){
            if(replaceNum($(this).attr('colspan')) > 0){
                totalColumn += replaceNum($(this).attr('colspan'));
            } else {
                totalColumn += 1;
            }
        });
        if(totalRow > 0 && totalColumn > 0){
            $("#layoutDropTBoat tr").each(function(){
                if(replaceNum($(this).find("td:last").attr('colspan')) > 0){
                    $(this).find("td:last").find('b').text(1);
                    unMergeTBoat();
                    $(this).find("td:last").remove();
                } else {
                    $(this).find("td:last").remove();
                }
            });
            var totalTableWidth = 61;
            if((totalColumn - 1) > 0){
                totalTableWidth = totalTableWidth * (totalColumn - 1);
            } else {
                totalTableWidth = 0;
            }
            $("#layoutDropTBoat").css('width', totalTableWidth);
            $("#layoutDropTboatColumn").val(totalColumn - 1);
            loadEventLayoutTBoat();
        }
    }
    
    function createColTBoat(indexCol){
        var column = '<td style="width: 60px; height: 86px; padding: 0px; margin: 0px;" id="layoutDropTBoat'+indexCol+'"><span class="BoatNumber"></span><span class="BoatLabel"></span><b style="display: none;"></b><div class="layoutDisplaySeat"></div></td>';
        return column;
    }
    
    function mergeTBoat(){
        var rowFirst = 0;
        var colFirst = 0;
        var mergeRow = false;
        var mergeCol = false;
        var rowIndex = 0;
        var checked  = 0; 
        if($("#layoutDropTBoat").html() != ''){
            mergeRow = true;
            mergeCol = true;
            $("#layoutDropTBoat tr").each(function(){
                var colIndex = 0;
                rowIndex++;
                $(this).find("td").each(function(){
                    colIndex++;
                    if($(this).find("b").text() == '1'){
                        if(rowFirst == 0 && colFirst == 0){
                            rowFirst = rowIndex;
                            colFirst = colIndex;
                        } else {
                            if(rowFirst != rowIndex && colFirst == colIndex){
                                mergeCol = false;
                            } else if(rowFirst == rowIndex && colFirst != colIndex){
                                mergeRow = false;
                            } else {
                                mergeRow = false;
                                mergeCol = false;
                            }
                        }
                        checked += 1;
                    }
                });
            });
            if(checked == 0 || checked == 1){
                mergeRow = false;
                mergeCol = false;
            }
        }
        if(mergeCol == true){
            var alertMergerCol = true;
            var rowMerged = 0;
            var colMerged = 0;
            var totalColMerged = 0;
            var colRemove = [];
            var compareIndex = 0;
            $("#layoutDropTBoat tr").each(function(){
                var rowObj = $(this);
                $(this).find("td").each(function(){
                    var colChecked   = $(this).find("b").text();
                    if(colChecked == '1'){
                        var checkedIndex = ($(this).index() + 1);
                        if(compareIndex == 0){
                            compareIndex = checkedIndex;
                            colMerged = $(this).index();
                        } else if(checkedIndex != (compareIndex+1)){
                            alertMergerCol = false;
                        } else if (checkedIndex == (compareIndex+1)){
                            compareIndex = checkedIndex;
                            colRemove[$(this).index()] = $(this).index();
                        }
                        rowMerged = rowObj.index();
                        totalColMerged++;
                    }
                });
            });
            if(alertMergerCol == true){
                $(colRemove).each(function(index, value){
                    if(value != undefined && value != ''){
                        var colSpanRemove = $("#layoutDropTBoat").find("tr").eq(rowMerged).find("td").eq((colMerged + 1));
                        if(replaceNum(colSpanRemove.attr('colspan')) > 0){
                            totalColMerged = totalColMerged + replaceNum(colSpanRemove.attr('colspan')) - 1;
                        }
                        colSpanRemove.remove();
                    }
                });
                var colSpan = $("#layoutDropTBoat").find("tr").eq(rowMerged).find("td").eq(colMerged);
                var totalColspan = totalColMerged;
                if(replaceNum(colSpan.attr('colspan')) > 0){
                    totalColspan = replaceNum(colSpan.attr('colspan')) + totalColMerged - 1;
                }
                colSpan.attr('colspan', totalColspan);
            }
        }
    }
    
    function unMergeTBoat(){
        var rowIndex = 0;
        $("#layoutDropTBoat tr").each(function(){
            var colIndex = 0;
            rowIndex++;
            $(this).find("td").each(function(){
                if($(this).find("b").text() == '1'){
                    if(replaceNum($(this).attr('colspan')) > 0){
                        colIndex++;
                        for (var newCol = 1; newCol < replaceNum($(this).attr('colspan')); newCol++) {
                            colIndex++;
                            var indexCol = rowIndex+"BT"+colIndex;
                            var col = createColTBoat(indexCol);
                            $(this).after(col);
                            var drop = '#layoutDropTBoat'+indexCol;
                            generateDropTBoat(drop);
                        }
                        $(this).removeAttr('colspan');
                    }
                    loadEventLayoutTBoat();
                }
            });
        });
    }
    
    function convertLayoutToJsonTBoat(){
        var jsonRow = '';
        var totalRow = 0;
        var totalCol = 0;
        jsonRow += '[';
        $("#layoutDropTBoat tr").each(function(){
            if(totalRow > 0){
                jsonRow += ',';
            }
            jsonRow += '{';
            jsonRow += '"row":"",';
            jsonRow += '"col":[';
                var colIndex = 0;
                $(this).find("td").each(function(){
                    if(colIndex > 0){
                        jsonRow += ',';
                    }
                    var colspan = '';
                    var rowspan = '';
                    jsonRow += '{';
                    if($(this).attr("colspan") != undefined){
                        colspan = $(this).attr("colspan");
                    }
                    if($(this).attr("rowspan") != undefined){
                        rowspan = $(this).attr("rowspan");
                    }
                    var value = $(this).find("span.BoatNumber").text();
                    var label = $(this).find("span.BoatLabel").text();
                    jsonRow += '"attr":{';
                    jsonRow += '"colspan":"'+colspan+'",';
                    jsonRow += '"rowspan":"'+rowspan+'"';
                    jsonRow += '},"value":"'+value+'"';
                    jsonRow += ',"label":"'+label+'"';
                    jsonRow += '}';
                    colIndex++;
                });
                totalCol = colIndex;
            jsonRow += ']';
            jsonRow += '}';
            totalRow++;
        });
        jsonRow += ']';
        $("#layoutDropTboatRow").val(totalRow);
        $("#layoutDropTboatColumn").val(totalCol);
        return jsonRow;
    }
    
    function setDragPositionTBoat(){
        var TboatLayouts = $.parseJSON('<?php echo $this->data['TBoat']['layout']; ?>');
        $.each(TboatLayouts, function(key, value) {
            var columns = value.col;
            var rowIndex = key + 1;
            var totalColSpan = 0;
            $.each(columns, function(index, col) {
                var colspan = replaceNum(col.attr.colspan);
                var colIndex = index + 1;
                if(colspan > 0){
                    for (var rmCol = 1; rmCol < colspan; rmCol++) {
                        var removeCol = colIndex;
                        $("#layoutDropTBoat").find("tr").eq((rowIndex - 1)).find("td").eq(removeCol).remove();
                    }
                    $("#layoutDropTBoat").find("tr").eq((rowIndex - 1)).find("td").eq(index).attr('colspan', colspan);
                    if(index > 0){
                        totalColSpan = totalColSpan + colspan - 1;
                    }
                }
                if(col.value != ''){
                    var drag  = '';
                    var label = col.value;
                    if(isNaN(replaceNum(col.value)) == true){
                        drag = '#labelDropTBoat'+col.value;
                    } else {
                        drag = '#chairTBoat'+col.value;
                    }
                    if(col.label != undefined){
                        label = col.label;
                    }
                    var colIndexSpan = totalColSpan + colIndex;
                    var drop = '#layoutDropTBoat'+rowIndex+'BT'+colIndexSpan;
                    autoDragTBoat(drag, drop, label);
                }
            });
        });
    }
    
    function autoDragTBoat(drag, drop, label){
        var number  = $(drag).data('number');
        var dvClone = '';
        if(isNaN(number) == true){
            dvClone = "labelDropTBoat";
        } else {
            dvClone = "chairTBoat";
        }
        var dropId  = $(drop).attr("id");
        // Config Drag
        $(drag).find("input").attr('disabled', true).val(label);
        $(drag).find('.removeChairTBoat').show();
        $(drag).find('.layoutDropSeat').text(dropId).hide();
        $(drag).css('visibility', 'hidden');
        // Clone Drag
        var cloneDrag   = $(drag).html();
        var layoutClone = '<div class="'+dvClone+'" style="padding: 0px; margin: 0px; border: none;">'+cloneDrag+'</div>';
        $(drop).find(".layoutDisplaySeat").html(layoutClone);
        $(drop).find(".layoutDisplaySeat").find("."+dvClone).find("input").val(label);
        eventRemoveDragPosition();
        // Set Boat Number & Label
        $(drop).find("span.BoatNumber").text(number).hide();
        $(drop).find("span.BoatLabel").text(label).hide();
        $(drop).droppable('disable');
        // Remove Select
        $("#layoutDropTBoat").find("td").removeClass("layoutSelectTBoat");
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTBoat">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php
echo $this->Form->create('TBoat'); 
echo $this->Form->hidden('layout'); ?>
<fieldset>
    <legend><?php __(MENU_ADD_NEW_BOAT); ?></legend>
    <table>
        <tr>
            <td><label for="TBoatTTransportationTypeId"><?php echo TABLE_TRANSPORTATION_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select id="TBoatTTransportationTypeId" name="data[TBoat][t_transportation_type_id]" class="validate[required]">
                        <option value="">Please Select</option>
                        <?php
                            $queryType = mysql_query("SELECT id,name FROM t_transportation_types WHERE is_active = 1 ORDER BY id");
                            while ($dataType = mysql_fetch_array($queryType)) {  
                                $selected = '';
                                if($this->data['TBoat']['t_transportation_type_id'] == $dataType['id']){
                                    $selected = "selected='selected'";
                                }
                        ?>
                        <option value="<?php echo $dataType['id']; ?>" <?php echo $selected; ?>><?php echo $dataType['name']; ?></option>
                        <?php                                        
                            }
                        ?> 
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TBoatCode"><?php echo TABLE_CODE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('code', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TBoatName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TBoatNumberOfSeat"><?php echo TABLE_SEAT_NUMBER; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('number_of_seat', array('class'=>'validate[required] integer')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset id="layoutContainerTBoat">
    <legend>Layout</legend>
    <div style="width: 320px; float: left; border: 1px solid #000; min-height: 400px; padding: 5px;" id="layoutSeatTBoat"></div>
    <div style="width: 900px; float: left; border: 1px solid #000; min-height: 400px; padding: 5px; margin-left: 10px;">
        <table cellpadding="5" cellspacing="0" style="width: 100%;">
            <tr>
                <td style="width: 70px;">Total Row:</td>
                <td style="width: 70px;"><input type="text" id="layoutDropTboatRow" name="data[TBoat][total_row]" value="<?php echo $this->data['TBoat']['total_row']; ?>" style="width: 90%;" class="integer" /></td>
                <td style="width: 80px;">Total Column:</td>
                <td style="width: 70px;"><input type="text" id="layoutDropTboatColumn" name="data[TBoat][total_column]" value="<?php echo $this->data['TBoat']['total_column']; ?>" style="width: 90%;" class="integer" /></td>
                <td>
                    <div class="buttons">
                        <a href="#" class="positive btnGenerateLayoutDropTBoat">
                            <span>Generate</span>
                        </a>
                    </div>
                    <div class="buttons">
                        <a href="#" class="positive btnRowPlusLayoutDropTBoat">
                            <span>Row +</span>
                        </a>
                    </div>
                    <div class="buttons">
                        <a href="#" class="positive btnRowCutLayoutDropTBoat">
                            <span>Row -</span>
                        </a>
                    </div>
                    <div class="buttons">
                        <a href="#" class="positive btnColPlusLayoutDropTBoat">
                            <span>Col +</span>
                        </a>
                    </div>
                    <div class="buttons">
                        <a href="#" class="positive btnColCutLayoutDropTBoat">
                            <span>Col -</span>
                        </a>
                    </div>
                    <div class="buttons">
                        <a href="#" class="positive btnMergeLayoutDropTBoat">
                            <span>Merge Col</span>
                        </a>
                    </div>
                    <div class="buttons">
                        <a href="#" class="positive btnUnmergeLayoutDropTBoat">
                            <span>Unmerge Col</span>
                        </a>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="5">
                    <div class="labelDropTBoat" id="labelDropTBoatCapitain">
                        <div class="chairLabel"><span class="layoutDropSeat"></span><input type="hidden" value="Capitain" /></div>
                        <div class="chairImg" style="background: none; width: 100%; text-align: center;"><span style="display: none;">Capitain</span>Capitain</div>
                        <div class="chairRemove"><img src="<?php echo $this->webroot; ?>img/button/void.png" class="removeChairTBoat" /></div>
                    </div>
                    <div class="labelDropTBoat" id="labelDropTBoatToilet">
                        <div class="chairLabel"><span class="layoutDropSeat"></span><input type="hidden" value="Toilet" /></div>
                        <div class="chairImg" style="background: none; width: 100%; text-align: center;"><span style="display: none;">Toilet</span>Toilet</div>
                        <div class="chairRemove"><img src="<?php echo $this->webroot; ?>img/button/void.png" class="removeChairTBoat" /></div>
                    </div>
                    <div class="labelDropTBoat" id="labelDropTBoatOpen1">
                        <div class="chairLabel"><span class="layoutDropSeat"></span><input type="hidden" value="Open Air Seat" /></div>
                        <div class="chairImg" style="background: none; width: 100%; text-align: center;"><span style="display: none;">Open1</span>Open Air Seat</div>
                        <div class="chairRemove"><img src="<?php echo $this->webroot; ?>img/button/void.png" class="removeChairTBoat" /></div>
                    </div>
                    <div class="labelDropTBoat" id="labelDropTBoatOpen2">
                        <div class="chairLabel"><span class="layoutDropSeat"></span><input type="hidden" value="Open Air Seat" /></div>
                        <div class="chairImg" style="background: none; width: 100%; text-align: center;"><span style="display: none;">Open2</span>Open Air Seat</div>
                        <div class="chairRemove"><img src="<?php echo $this->webroot; ?>img/button/void.png" class="removeChairTBoat" /></div>
                    </div>
                    <div style="clear: both;"></div>
                </td>
            </tr>
        </table>
        <table cellpadding="2" cellspacing="0" style="width: 100%; margin-top: 10px;" id="layoutDropTBoat" class="table_print"></table>
    </div>
    <div style="clear: both;"></div>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSaveTBoat"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>