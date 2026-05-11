<?php 
echo $this->element('prevent_multiple_submit'); 
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $link = "https";
else
      $link = "http";
     
// Here append the common URL characters.
$link .= "://";
     
// Append the host(domain name, ip) to the URL.
$link .= $_SERVER['HTTP_HOST'];
     
// Append the requested resource location to the URL
$link .= str_replace("/t_transportation_types/add/","",$_SERVER['REQUEST_URI']);
?>
<style type="text/css" media="screen">
    .labelDropTTransportationType {
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
    
    .chairTTransportationType {
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
    
    #layoutContainerTTransportationType div.ui-draggable, #layoutContainerTTransportationType div.ui-draggable-dragging {
        font-size: 12px;
        font-weight: bold;
        margin-left: 5px;
        padding-top: 5px;
        padding-left: 5px;
    }
    
    #layoutContainerTTransportationType .ui-draggable-disabled, #layoutContainerTTransportationType .ui-state-disabled {
        opacity: 100;
    }
    
    .removeChairTTransportationType {
        display: none;
        cursor: pointer;
        width: 16px;
    }
    
    .layoutSelectTTransportationType{
        background-color: #79b7e7;
    }

    #sortablePhoto {
        list-style-type: none;
        margin: 0; 
        padding: 0;
        margin-right: 10px; 
        width: 100%;
    }    
    #sortablePhoto li { 
        margin: 0px; 
        padding: 0px; 
        font-size: 1.2em; 
        width: 105px; 
        cursor: pointer;
        float: left; 
    }
</style> 
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#TTransportationTypeAmenityId").chosen({width: 400});
        $(".integer").autoNumeric({mDec: 0, aSep: ','});
        $("#TTransportationTypeAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TTransportationTypeAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $(".interger").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                var layout = convertLayoutToJsonTTransportationType();
                $("#TTransportationTypeLayout").val(layout);
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTTransportationType").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackTTransportationType").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Transportation Type', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Transportation Type', 'Add', 1, '');
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
        $(".btnBackTTransportationType").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTTransportationType.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        $("#TTransportationTypeNumberOfSeat").blur(function(){
            generateSeatTTransportationType();
            generateDefaultTableTTransportationType();
            resetDragTTransportationType();
        });
        
        $(".btnGenerateLayoutDropTTransportationType").click(function(event){
            event.preventDefault();
            var totalRow = replaceNum($("#layoutDropTTransportationTypeRow").val());
            var totalColumn = replaceNum($("#layoutDropTTransportationTypeColumn").val());
            if(totalRow > 0 && totalColumn > 0){
                generateSeatTTransportationType();
                generateDefaultTableTTransportationType();
                resetDragTTransportationType();
            }
        });
        
        $(".btnRowPlusLayoutDropTTransportationType").click(function(event){
            event.preventDefault();
            rowPlusTTransportationType();
        });
        
        $(".btnRowCutLayoutDropTTransportationType").click(function(event){
            event.preventDefault();
            rowCutTTransportationType();
        });
        
        $(".btnColPlusLayoutDropTTransportationType").click(function(event){
            event.preventDefault();
            colPlusTTransportationType();
        });
        
        $(".btnColCutLayoutDropTTransportationType").click(function(event){
            event.preventDefault();
            colCutTTransportationType();
        });
        
        $(".btnMergeLayoutDropTTransportationType").click(function(event){
            event.preventDefault();
            mergeTTransportationType();
        });
        
        $(".btnUnmergeLayoutDropTTransportationType").click(function(event){
            event.preventDefault();
            unMergeTTransportationType();
        });
        resetDragTTransportationType();

        // Upload Image
        // From Action Upload Photo
        $("#TTransportationFormUploadImage").ajaxForm({
            dataType: "json",
            beforeSerialize: function($form, options) {
                extArray = new Array(".bmp",".jpg",".gif",".tif",".png");
                allowSubmit = false;
                file = $("#TTransportationTypePhotoUpload").val();
                if (!file) return;
                while (file.indexOf("\\") != -1)
                    file = file.slice(file.indexOf("\\") + 1);
                ext = file.slice(file.indexOf(".")).toLowerCase();
                for (var i = 0; i < extArray.length; i++) {
                    if (extArray[i] == ext) { allowSubmit = true; break; }
                }
                if (!allowSubmit){
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please only upload files that end in types: <b>' + (extArray.join("  ")) + '</b>. Please select a new file to upload again.</p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position: 'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                    return false;
                }
            },
            beforeSend: function() {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("#TTransportationTypePhoto").val(result.img);
                $("#TTransportationTypePhotoDisplay").attr("src", "<?php echo $this->webroot; ?>public/transportation_type/" + result.img);
            }
        });

        $("#TTransportationFormUploadOtherImage").ajaxForm({
            dataType: "json",
            beforeSerialize: function($form, options) {
                extArray = new Array(".bmp",".jpg",".gif",".tif",".png");
                allowSubmit = false;
                file = $("#TTransportationTypePhotoUploadOther").val();
                if (!file) return;
                while (file.indexOf("\\") != -1)
                    file = file.slice(file.indexOf("\\") + 1);
                ext = file.slice(file.indexOf(".")).toLowerCase();
                for (var i = 0; i < extArray.length; i++) {
                    if (extArray[i] == ext) { allowSubmit = true; break; }
                }
                if (!allowSubmit){
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please only upload files that end in types: <b>' + (extArray.join("  ")) + '</b>. Please select a new file to upload again.</p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position: 'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                    return false;
                }
            },
            beforeSend: function() {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                var index = Math.floor((Math.random() * 100000) + 1);
                var imgDiv = '<li id="sortPhoto'+index+'"><div style="float: left; width: 100px; margin-left: 3px; margin-bottom: 3px;"><img class="btnDeleteTransportationTypeOtherImg" src="<?php echo $this->webroot; ?>img/button/delete.png" style="z-index: 99999; width: 18px; cursor: pointer;"/><img src="<?php echo $this->webroot; ?>public/transportation_type/'+ result.img+'" style="width: 100px; height: 65px;" /><input type="hidden" value="<?php echo $link; ?>" name="data[photo_path_other][]" class="otherImgPathValue" /><input type="hidden" value="'+ result.img+'" name="data[photo_other][]" class="otherImgValue" /></div></li>';
                $("#sortablePhoto").append(imgDiv);
                deleteOtherImageTTransportationType();
            }
        });

        $("#TTransportationTypeUpload").on("change", function (event) {
            let fileInputElement = document.getElementById('TTransportationTypePhotoUpload');
            fileInputElement.files = event.target.files;
            $("#TTransportationFormUploadImage").submit();
        });

        $("#TTransportationTypeUploadOtherImage").on("change", function (event) {
            let fileInputElement = document.getElementById('TTransportationTypePhotoUploadOther');
            fileInputElement.files = event.target.files;
            $("#TTransportationFormUploadOtherImage").submit();
        });

        $("#sortablePhoto").sortable({
            revert: true
        });

    });

    function deleteOtherImageTTransportationType(){
        $(".btnDeleteTransportationTypeOtherImg").unbind("click");
        $(".btnDeleteTransportationTypeOtherImg").click(function(){
            var divImg = $(this).closest("div");
            var img    = $(this).closest("div").find(".otherImgValue").val();
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to delete image?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_YES; ?>': function() {
                        divImg.remove();
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_NO; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    
    function resetDragTTransportationType(){
        $('#labelDropTTransportationTypeCapitain, #labelDropTTransportationTypeToilet, #labelDropTTransportationTypeOpen1, #labelDropTTransportationTypeOpen2, #labelDropTTransportationTypeOpen3, #labelDropTTransportationTypeOpen4').removeAttr("style").css('background', 'none');
        generateDragTTransportationType('#labelDropTTransportationTypeCapitain', 'Capitain');
        generateDragTTransportationType('#labelDropTTransportationTypeToilet', 'Toilet');
        generateDragTTransportationType('#labelDropTTransportationTypeOpen1', 'Open1');
        generateDragTTransportationType('#labelDropTTransportationTypeOpen2', 'Open2');
        generateDragTTransportationType('#labelDropTTransportationTypeOpen3', 'Open3');
        generateDragTTransportationType('#labelDropTTransportationTypeOpen4', 'Open4');
        generateDragTTransportationType('#labelDropTTransportationTypeOpen5', 'Open5');
        eventRemoveDragPosition();
    }
    
    function eventRemoveDragPosition(){
        $(".removeChairTTransportationType").unbind('click');
        $(".removeChairTTransportationType").click(function(){
            var dragNum   = $(this).parent().parent().find(".chairImg").find("span").text();
            var columDrop = "#"+$(this).parent().parent().find(".layoutDropSeat").text();
            if(isNaN(dragNum) == true){
                // Label Seat
                var chairDrag = "#labelDropTTransportationType"+dragNum;
            } else {
                // Seat
                var chairDrag = "#chairTTransportationType"+dragNum;
            }
            $(chairDrag).removeAttr("style");
            // Reset Drag & Drop Function
            generateDragTTransportationType(chairDrag, dragNum);
            generateDropTTransportationType(columDrop);
            $(this).hide();
            // Reset Drag
            if(isNaN(dragNum) == false){
                $(chairDrag).find("input").removeAttr('disabled');
            }
            $(chairDrag).find("input").attr('disabled', false);
            $(chairDrag).find('.removeChairTTransportationType').hide();
            $(chairDrag).find('.layoutDropSeat').text('');
            $(chairDrag).css('visibility', 'visible');
            // Reset Drop
            $(columDrop).find("span.BoatNumber").text('');
            $(columDrop).find("span.BoatLabel").text('');
            $(columDrop).find(".layoutDisplaySeat").html('');
        });
    }
    
    function handleChairTTransportationTypeDrop(event, ui) {
        var number = ui.draggable.data('number');
        var dvClone = '';
        if(isNaN(number) == true){
            // Label Seat
            var drag = "#labelDropTTransportationType"+number;
            dvClone  = "labelDropTTransportationType";
        } else {
            // Seat
            var drag = '#chairTTransportationType'+number;
            dvClone  = "chairTTransportationType";
        }
        var label  = $(drag).find("input").val();
        var dropId = $(this).attr("id");
        // Config Drag
        $(drag).find("input").attr('disabled', true);
        $(drag).find('.removeChairTTransportationType').show();
        $(drag).find('.layoutDropSeat').text(dropId).hide();
        $(drag).css('visibility', 'hidden');
        // Clone Drag
        var cloneDrag   = $(drag).html();
        var layoutClone = '<div class="'+dvClone+'" style="padding: 0px; margin: 0px; border: none;">'+cloneDrag.toString().replace('value="'+number+'"', 'value="'+label+'"')+'</div>';
        $(this).find(".layoutDisplaySeat").html(layoutClone);
        eventRemoveDragPosition();
        // Set Boat Number & Label
        $(this).find("span.BoatNumber").text(number).hide();
        $(this).find("span.BoatLabel").text(label).hide();
        $(this).droppable('disable');
        // Remove Select
        $("#layoutDropTTransportationType").find("td").removeClass("layoutSelectTTransportationType");
    }
    
    function generateDragTTransportationType(drag, number){
        destroyDragDropTTransportationType(drag);
        $(drag).data('number', number).draggable({
            containment: '#layoutContainerTTransportationType',
            cursor: 'move',
            revert: 'invalid' 
        });
    }
    
    function generateDropTTransportationType(drop){
        destroyDragDropTTransportationType(drop);
        $(drop).droppable( {
            accept: '.chairTTransportationType, .labelDropTTransportationType',
            hoverClass: 'hovered',
            drop: handleChairTTransportationTypeDrop
        });
    }
    
    function destroyDragDropTTransportationType(event){
        $(event).draggable('destroy');
        $(event).droppable('destroy');
        $(event).removeClass("ui-state-disabled").removeAttr("aria-disabled");
    }
    
    function generateDefaultTableTTransportationType(){
        var totalRow = replaceNum($("#layoutDropTTransportationTypeRow").val());
        var totalColumn = replaceNum($("#layoutDropTTransportationTypeColumn").val());
        $("#layoutDropTTransportationType").html('');
        if(totalRow > 0 && totalColumn > 0){
            $(".btnGenerateLayoutDropTTransportationType").find('span').text('<?php echo ACTION_LOADING; ?>');
            var totalTableWidth = 61 * totalColumn;
            for (var row = 1; row <= totalRow; row++) {
                var eventDrop = [];
                var table = '<tr>';
                for (var col = 1; col <= totalColumn; col++) {
                    var indexCol = row+"BT"+col;
                    table += createColTTransportationType(indexCol);
                    eventDrop[col] = indexCol;
                }
                table += '</tr>';
                $("#layoutDropTTransportationType").append(table);
                $.each( eventDrop, function( key, value ) {
                    if(value != '' && value != undefined){
                        var drop = '#layoutDropTTransportationType'+value;
                        generateDropTTransportationType(drop);
                    }
                });
            }
            $("#layoutDropTTransportationType").css('width', totalTableWidth);
            loadEventLayoutTTransportationType();
            $(".btnGenerateLayoutDropTTransportationType").find('span').text('Generate');
        }
    }
    
    function generateSeatTTransportationType(){
        var totalSeat = replaceNum($("#TTransportationTypeNumberOfSeat").val());
        $("#layoutSeatTTransportationType").html('');
        if(totalSeat > 0){
            for (var i = 1; i <= totalSeat; i++) {
                var seatLayout = '<div class="chairTTransportationType" id="chairTTransportationType'+i+'"><div class="chairLabel"><span class="layoutDropSeat"></span><input type="text" style="width: 50%; height: 10px; font-size: 11px;" value="'+i+'" /></div><div class="chairImg"><span style="display: none;">'+i+'</span></div><div class="chairRemove"><img src="<?php echo $this->webroot; ?>img/button/void.png" class="removeChairTTransportationType" /></div></div>';
                $("#layoutSeatTTransportationType").append(seatLayout);
                var drag = '#chairTTransportationType'+i;
                generateDragTTransportationType(drag, i);
            }
            $(".chairLabel input").unbind('blur');
            $(".chairLabel input").blur(function(){
                var number = $(this).closest(".chairTTransportationType").find(".chairImg").text();
                if($(this).val() == ""){
                    $(this).val(number);
                }
            });
        }
    }
    
    function loadEventLayoutTTransportationType(){
        $("#layoutDropTTransportationType").find("td").unbind('click');
        $("#layoutDropTTransportationType").find("td").click(function(){
            var checked = $(this).find("b").text();
            var chairDrog = $(this).find("span.BoatNumber").text();
            if(checked == '1' || chairDrog != ''){
                $(this).find("b").text('');
                $(this).removeClass("layoutSelectTTransportationType");
            } else {
                $(this).find("b").text(1);
                $(this).addClass("layoutSelectTTransportationType");
            }
        });
    }
    
    function rowPlusTTransportationType(){
        var totalRow = replaceNum($("#layoutDropTTransportationType tr").length) + 1;
        var totalColumn = 0;
        $("#layoutDropTTransportationType tr:first").find("td").each(function(){
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
                table += createColTTransportationType(indexCol);
                eventDrop[col] = indexCol;
            }
            table += '</tr>';
            $("#layoutDropTTransportationType").append(table);
            $.each( eventDrop, function( key, value ) {
                if(value != '' && value != undefined){
                    var drop = '#layoutDropTTransportationType'+value;
                    generateDropTTransportationType(drop);
                }
            });
            loadEventLayoutTTransportationType();
            $("#layoutDropTTransportationTypeRow").val(totalRow);
        }
    }
    
    function rowCutTTransportationType(){
        var totalRow = replaceNum($("#layoutDropTTransportationType tr").length);
        var totalColumn = replaceNum($("#layoutDropTTransportationType tr:first td").length);
        if(totalRow > 0 && totalColumn> 0){
            $("#layoutDropTTransportationType").find("tr:last").remove();
            $("#layoutDropTTransportationTypeRow").val(totalRow - 1);
        }
    }
    
    function colPlusTTransportationType(){
        var totalRow = replaceNum($("#layoutDropTTransportationType tr").length);
        var totalColumn = 1;
        $("#layoutDropTTransportationType tr:first").find("td").each(function(){
            if(replaceNum($(this).attr('colspan')) > 0){
                totalColumn += replaceNum($(this).attr('colspan'));
            } else {
                totalColumn += 1;
            }
        });
        if(totalRow > 0 && totalColumn> 0){
            $("#layoutDropTTransportationType tr").each(function(index, value){
                var indexCol = index+"BT"+totalColumn;
                var col = createColTTransportationType(indexCol);
                $(this).find("td:last").after(col);
                var drop = '#layoutDropTTransportationType'+indexCol;
                generateDropTTransportationType(drop);
            });
            var totalTableWidth = 61 * totalColumn;
            $("#layoutDropTTransportationType").css('width', totalTableWidth);
            $("#layoutDropTTransportationTypeColumn").val(totalColumn);
            loadEventLayoutTTransportationType();
        }
    }
    
    function colCutTTransportationType(){
        var totalRow = replaceNum($("#layoutDropTTransportationType tr").length);
        var totalColumn = 0;
        $("#layoutDropTTransportationType tr:first").find("td").each(function(){
            if(replaceNum($(this).attr('colspan')) > 0){
                totalColumn += replaceNum($(this).attr('colspan'));
            } else {
                totalColumn += 1;
            }
        });
        if(totalRow > 0 && totalColumn > 0){
            $("#layoutDropTTransportationType tr").each(function(){
                if(replaceNum($(this).find("td:last").attr('colspan')) > 0){
                    $(this).find("td:last").find('b').text(1);
                    unMergeTTransportationType();
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
            $("#layoutDropTTransportationType").css('width', totalTableWidth);
            $("#layoutDropTTransportationTypeColumn").val(totalColumn - 1);
            loadEventLayoutTTransportationType();
        }
    }
    
    function createColTTransportationType(indexCol){
        var column = '<td style="width: 60px; height: 86px; padding: 0px; margin: 0px;" id="layoutDropTTransportationType'+indexCol+'"><span class="BoatNumber"></span><span class="BoatLabel"></span><b style="display: none;"></b><div class="layoutDisplaySeat"></div></td>';
        return column;
    }
    
    function mergeTTransportationType(){
        var rowFirst = 0;
        var colFirst = 0;
        var mergeRow = false;
        var mergeCol = false;
        var rowIndex = 0;
        var checked  = 0; 
        if($("#layoutDropTTransportationType").html() != ''){
            mergeRow = true;
            mergeCol = true;
            $("#layoutDropTTransportationType tr").each(function(){
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
            $("#layoutDropTTransportationType tr").each(function(){
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
                        var colSpanRemove = $("#layoutDropTTransportationType").find("tr").eq(rowMerged).find("td").eq((colMerged + 1));
                        if(replaceNum(colSpanRemove.attr('colspan')) > 0){
                            totalColMerged = totalColMerged + replaceNum(colSpanRemove.attr('colspan')) - 1;
                        }
                        colSpanRemove.remove();
                    }
                });
                var colSpan = $("#layoutDropTTransportationType").find("tr").eq(rowMerged).find("td").eq(colMerged);
                var totalColspan = totalColMerged;
                if(replaceNum(colSpan.attr('colspan')) > 0){
                    totalColspan = replaceNum(colSpan.attr('colspan')) + totalColMerged - 1;
                }
                colSpan.attr('colspan', totalColspan);
            }
        }
    }
    
    function unMergeTTransportationType(){
        var rowIndex = 0;
        $("#layoutDropTTransportationType tr").each(function(){
            var colIndex = 0;
            rowIndex++;
            $(this).find("td").each(function(){
                if($(this).find("b").text() == '1'){
                    if(replaceNum($(this).attr('colspan')) > 0){
                        colIndex++;
                        for (var newCol = 1; newCol < replaceNum($(this).attr('colspan')); newCol++) {
                            colIndex++;
                            var indexCol = rowIndex+"BT"+colIndex;
                            var col = createColTTransportationType(indexCol);
                            $(this).after(col);
                            var drop = '#layoutDropTTransportationType'+indexCol;
                            generateDropTTransportationType(drop);
                        }
                        $(this).removeAttr('colspan');
                    }
                    loadEventLayoutTTransportationType();
                }
            });
        });
    }
    
    function convertLayoutToJsonTTransportationType(){
        var jsonRow = '';
        var totalRow = 0;
        var totalCol = 0;
        jsonRow += '[';
        $("#layoutDropTTransportationType tr").each(function(){
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
        $("#layoutDropTTransportationTypeRow").val(totalRow);
        $("#layoutDropTTransportationTypeColumn").val(totalCol);
        return jsonRow;
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTTransportationType">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<form id="TTransportationFormUploadImage" action="<?php echo $this->base; ?>/t_transportation_types/upload" method="post" enctype="multipart/form-data">
    <table style="display: none;">
        <tr>
            <td>
                <input type="file" name="photo" id="TTransportationTypePhotoUpload" />
            </td>
        </tr>
    </table>
</form>
<form id="TTransportationFormUploadOtherImage" action="<?php echo $this->base; ?>/t_transportation_types/upload" method="post" enctype="multipart/form-data">
    <table style="display: none;">
        <tr>
            <td>
                <input type="file" name="photo" id="TTransportationTypePhotoUploadOther" />
            </td>
        </tr>
    </table>
</form>
<?php 
echo $this->Form->create('TTransportationType'); 
?>
<input type="hidden" name="data[TTransportationType][photo_path]" value="<?php echo $link; ?>" />
<input type="hidden" name="data[TTransportationType][layout]" id="TTransportationTypeLayout" />
<fieldset>
    <legend><?php __(MENU_TRANSPORTATION_TYPE_INFO); ?></legend>
    <table style="width: 100%;">
        <tr>
            <td rowspan="3" style="width: 110px;">
                <table>
                    <tr>
                        <td>
                            <input type="hidden" name="data[TTransportationType][photo]" id="TTransportationTypePhoto" />
                            <img alt="" id="TTransportationTypePhotoDisplay" style="width: 150px; height: 100px;" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Size: 720 * 480 Px
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <input type="file" id="TTransportationTypeUpload" />
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 10%;"><label for="TTransportationTypeName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td style="width: 40%;">
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
            <td style="width: 90px; vertical-align: top;" rowspan="2"><label for="TTransportationTypeAmenityId"><?php echo MENU_AMENITY; ?> :</label></td>
            <td rowspan="3" style="vertical-align: top;">
                <div class="inputContainer">
                    <?php echo $this->Form->input('amenity_id', array('name' => 'data[amenity_id]', 'style' => 'width: 400px;', 'multiple' => true, 'div' => false, 'label' => false)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TTransportationTypeNumberOfSeat"><?php echo TABLE_SEAT_NUMBER; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('number_of_seat', array('class'=>'validate[required] integer')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TTransportationTypeSeatType"><?php echo "Seat Type"; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[TTransportationType][seat_type]" id="TTransportationTypeSeatType" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1"><?php echo "Sitting"; ?></option>
                        <option value="2"><?php echo "Sleeping"; ?></option>
                    </select>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset id="layoutContainerTTransportationType">
    <legend>Layout</legend>
    <div style="width: 320px; float: left; border: 1px solid #000; min-height: 400px; padding: 5px;" id="layoutSeatTTransportationType"></div>
    <div style="width: 850px; float: left; border: 1px solid #000; min-height: 400px; padding: 5px; margin-left: 10px;">
        <table cellpadding="5" cellspacing="0" style="width: 100%;">
            <tr>
                <td style="width: 70px;">Total Row:</td>
                <td style="width: 70px;"><input type="text" id="layoutDropTTransportationTypeRow" name="data[TTransportationType][total_row]" style="width: 90%;" class="integer" /></td>
                <td style="width: 80px;">Total Column:</td>
                <td style="width: 70px;"><input type="text" id="layoutDropTTransportationTypeColumn" name="data[TTransportationType][total_column]" style="width: 90%;" class="integer" /></td>
                <td>
                    <div class="buttons">
                        <a href="#" class="positive btnGenerateLayoutDropTTransportationType">
                            <span>Generate</span>
                        </a>
                    </div>
                    <div class="buttons">
                        <a href="#" class="positive btnRowPlusLayoutDropTTransportationType">
                            <span>Row +</span>
                        </a>
                    </div>
                    <div class="buttons">
                        <a href="#" class="positive btnRowCutLayoutDropTTransportationType">
                            <span>Row -</span>
                        </a>
                    </div>
                    <div class="buttons">
                        <a href="#" class="positive btnColPlusLayoutDropTTransportationType">
                            <span>Col +</span>
                        </a>
                    </div>
                    <div class="buttons">
                        <a href="#" class="positive btnColCutLayoutDropTTransportationType">
                            <span>Col -</span>
                        </a>
                    </div>
                    <div class="buttons">
                        <a href="#" class="positive btnMergeLayoutDropTTransportationType">
                            <span>Merge Col</span>
                        </a>
                    </div>
                    <div class="buttons">
                        <a href="#" class="positive btnUnmergeLayoutDropTTransportationType">
                            <span>Unmerge Col</span>
                        </a>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="5">
                    <div class="labelDropTTransportationType" id="labelDropTTransportationTypeCapitain">
                        <div class="chairLabel"><span class="layoutDropSeat"></span><input type="hidden" value="Capitain" /></div>
                        <div class="chairImg" style="background: none; width: 100%; text-align: center;"><span style="display: none;">Capitain</span>Capitain</div>
                        <div class="chairRemove"><img src="<?php echo $this->webroot; ?>img/button/void.png" class="removeChairTTransportationType" /></div>
                    </div>
                    <div class="labelDropTTransportationType" id="labelDropTTransportationTypeToilet">
                        <div class="chairLabel"><span class="layoutDropSeat"></span><input type="hidden" value="Toilet" /></div>
                        <div class="chairImg" style="background: none; width: 100%; text-align: center;"><span style="display: none;">Toilet</span>Toilet</div>
                        <div class="chairRemove"><img src="<?php echo $this->webroot; ?>img/button/void.png" class="removeChairTTransportationType" /></div>
                    </div>
                    <!-- <div class="labelDropTTransportationType" id="labelDropTTransportationTypeOpen1">
                        <div class="chairLabel"><span class="layoutDropSeat"></span><input type="hidden" value="Open Air Seat" /></div>
                        <div class="chairImg" style="background: none; width: 100%; text-align: center;"><span style="display: none;">Open1</span>Open Air Seat</div>
                        <div class="chairRemove"><img src="<?php echo $this->webroot; ?>img/button/void.png" class="removeChairTTransportationType" /></div>
                    </div> -->
                    <div class="labelDropTTransportationType" id="labelDropTTransportationTypeOpen2">
                        <div class="chairLabel"><span class="layoutDropSeat"></span><input type="hidden" value="Hostess" /></div>
                        <div class="chairImg" style="background: none; width: 100%; text-align: center;"><span style="display: none;">Open2</span>Hostess</div>
                        <div class="chairRemove"><img src="<?php echo $this->webroot; ?>img/button/void.png" class="removeChairTTransportationType" /></div>
                    </div>
                    <div class="labelDropTTransportationType" id="labelDropTTransportationTypeOpen3">
                        <div class="chairLabel"><span class="layoutDropSeat"></span><input type="hidden" value="Down Stair" /></div>
                        <div class="chairImg" style="background: none; width: 100%; text-align: center;"><span style="display: none;">Open3</span>Down Stair</div>
                        <div class="chairRemove"><img src="<?php echo $this->webroot; ?>img/button/void.png" class="removeChairTTransportationType" /></div>
                    </div>
                    <div class="labelDropTTransportationType" id="labelDropTTransportationTypeOpen4">
                        <div class="chairLabel"><span class="layoutDropSeat"></span><input type="hidden" value="Up Stair" /></div>
                        <div class="chairImg" style="background: none; width: 100%; text-align: center;"><span style="display: none;">Open4</span>Up Stair</div>
                        <div class="chairRemove"><img src="<?php echo $this->webroot; ?>img/button/void.png" class="removeChairTTransportationType" /></div>
                    </div>
                    <div class="labelDropTTransportationType" id="labelDropTTransportationTypeOpen5">
                        <div class="chairLabel"><span class="layoutDropSeat"></span><input type="hidden" value="Door" /></div>
                        <div class="chairImg" style="background: none; width: 100%; text-align: center;"><span style="display: none;">Open5</span>Door</div>
                        <div class="chairRemove"><img src="<?php echo $this->webroot; ?>img/button/void.png" class="removeChairTTransportationType" /></div>
                    </div>
                    <div style="clear: both;"></div>
                </td>
            </tr>
        </table>
        <table cellpadding="2" cellspacing="0" style="width: 100%; margin-top: 10px;" id="layoutDropTTransportationType" class="table_print"></table>
    </div>
    <div style="width: 340px; float: left; border: 1px solid #000; min-height: 400px; padding: 5px; margin-left: 10px;">
        <table style="width: 100%;" cellpadding="3">
            <tr>
                <td>Upload Other Image for Slide (Size: 720 * 480 Px)</td>
            </tr>
            <tr>
                <td style="border-bottom: 2px solid #000; padding-bottom: 15px;">
                    <input type="file" id="TTransportationTypeUploadOtherImage" />
                </td>
            </tr>
            <tr>
                <td valign="top"><ul id="sortablePhoto"></ul></td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveTTransportationType"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>