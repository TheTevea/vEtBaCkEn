<?php

/**
 * Description of Helper
 *
 * @author UDAYA
 */
date_default_timezone_set('Asia/Phnom_Penh');

class GeneratePhpViewComponent extends Object {
    function generateContentDashboard(
        $controllerName = "BusType",
        $moduleName = "BusType",
        $menuLang = "BUS_TYPE",
        $showView = true,
        $showAdd  = true,
        $showEdit = true,
        $showDelete = true,
        $tableFields = [
            'TABLE_NAME',
        ]
    ) {
        $tblName = "tbl" . rand();
        if($showAdd){
        $content = <<<EOD
    <?php
    // Authentication
    \$this->element('check_access');
    \$allowAdd=checkAccess(\$user['User']['id'], \$this->params['controller'], 'add');
    ?>
    EOD;
        }
    $content = <<<EOD
    <?php \$tblName = "{$tblName}"; ?>
    <script type="text/javascript" src="<?php echo \$this->webroot; ?>js/pipeline.js"></script>
    <script type="text/javascript">
        var oTable{$controllerName};
        \$(document).ready(function(){
            // Prevent Key Enter
            preventKeyEnter();
            \$("#{$tblName} td:first-child").addClass('first');
            oTable{$controllerName} = \$("#{$tblName}").dataTable({
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": "<?php echo \$this->base.'/'. \$this->params['controller']; ?>/ajax/",
                "fnServerData": fnDataTablesPipeline,
                "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                    \$("#{$tblName} td:first-child").addClass('first');
                    \$("#{$tblName} td:last-child").css("white-space", "nowrap");
    EOD;
    
        // Dynamically add View functionality
        if ($showView) {
            $content .= <<<EOD
                    \$(".btnView{$controllerName}").click(function(event){
                        event.preventDefault();
                        var id = \$(this).attr('rel');
                        var name = \$(this).attr('name');
                        var leftPanel=\$(this).parent().parent().parent().parent().parent().parent().parent();
                        var rightPanel=leftPanel.parent().find(".rightPanel");
                        leftPanel.hide("slide", { direction: "left" }, 500, function() {
                            rightPanel.show();
                        });
                        rightPanel.html("<?php echo ACTION_LOADING; ?>");
                        rightPanel.load("<?php echo \$this->base; ?>/<?php echo \$this->params['controller']; ?>/view/" + id);
                    });
    EOD;
        }
    
        // Dynamically add Edit functionality
        if ($showEdit) {
            $content .= <<<EOD
                    \$(".btnEdit{$controllerName}").click(function(event){
                        event.preventDefault();
                        var id = \$(this).attr('rel');
                        var name = \$(this).attr('name');
                        var leftPanel=\$(this).parent().parent().parent().parent().parent().parent().parent();
                        var rightPanel=leftPanel.parent().find(".rightPanel");
                        leftPanel.hide("slide", { direction: "left" }, 500, function() {
                            rightPanel.show();
                        });
                        rightPanel.html("<?php echo ACTION_LOADING; ?>");
                        rightPanel.load("<?php echo \$this->base; ?>/<?php echo \$this->params['controller']; ?>/edit/" + id);
                    });
    EOD;
        }
    
        // Dynamically add Delete functionality
        if ($showDelete) {
            $content .= <<<EOD
                    \$(".btnDelete{$controllerName}").click(function(event){
                        event.preventDefault();
                        var id = \$(this).attr('rel');
                        var name = \$(this).attr('name');
                        \$("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
                        \$("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_DELETE; ?> <b>' + name + '</b>?</p>');
                        \$("#dialog").dialog({
                            title: '<?php echo DIALOG_CONFIRMATION; ?>',
                            resizable: false,
                            modal: true,
                            width: 'auto',
                            height: 'auto',
                            open: function(event, ui){
                                \$(".ui-dialog-buttonpane").show();
                            },
                            buttons: {
                                '<?php echo ACTION_DELETE; ?>': function() {
                                    \$.ajax({
                                        type: "GET",
                                        url: "<?php echo \$this->base.'/'. \$this->params['controller']; ?>/delete/" + id,
                                        data: "",
                                        beforeSend: function(){
                                            \$("#dialog").dialog("close");
                                            \$(".loader").attr("src", "<?php echo \$this->webroot; ?>img/layout/spinner.gif");
                                        },
                                        success: function(result){
                                            \$(".loader").attr("src", "<?php echo \$this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            oCache.iCacheLower = -1;
                                            oTable{$controllerName}.fnDraw(false);
                                            // alert message
                                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                                createSysAct('{$moduleName}', 'Delete', 2, result);
                                                \$("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                            }else {
                                                createSysAct('{$moduleName}', 'Delete', 1, '');
                                                \$("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                            }
                                            \$("#dialog").dialog({
                                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                                resizable: false,
                                                modal: true,
                                                width: 'auto',
                                                height: 'auto',
                                                buttons: {
                                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                                        \$(this).dialog("close");
                                                    }
                                                }
                                            });
                                        }
                                    });
                                },
                                '<?php echo ACTION_CANCEL; ?>': function() {
                                    \$(this).dialog("close");
                                }
                            }
                        });
                    });
    EOD;
        }
    
        $content .= <<<EOD
                    return sPre;
                },
                "aoColumnDefs": [{
                    "sType": "numeric", "aTargets": [ 0 ],
                    "bSortable": false, "aTargets": [ 0,-1 ]
                }]
            });
        EOD;
        if($showAdd){
        $content = <<<EOD
            \$(".btnAdd{$controllerName}").click(function(event){
                event.preventDefault();
                var leftPanel=\$(this).parent().parent().parent();
                var rightPanel=leftPanel.parent().find(".rightPanel");
                leftPanel.hide("slide", { direction: "left" }, 500, function() {
                    rightPanel.show();
                });
                rightPanel.html("<?php echo ACTION_LOADING; ?>");
                rightPanel.load("<?php echo \$this->base; ?>/<?php echo \$this->params['controller']; ?>/add/");
            });
        EOD;
        }
        $content = <<<EOD
        });
    </script>
    <div class="leftPanel">
        EOD;
        if($showAdd){
        $content = <<<EOD
        <?php if(\$allowAdd){ ?>
        <div style="padding: 5px;border: 1px dashed #bbbbbb;">
            <div class="buttons">
                <a href="" class="positive btnAdd{$controllerName}">
                    <img src="<?php echo \$this->webroot; ?>img/button/plus.png" alt=""/>
                    <?php echo MENU_{$menuLang}_ADD; ?>
                </a>
            </div>
            <div style="clear: both;"></div>
        </div>
        <?php } ?>
        EOD;
        }
        $content = <<<EOD
        <br />
        <div id="dynamic">
            <table id="{$tblName}" class="table" cellspacing="0">
                <thead>
                    <tr>
                        <th class="first"><?php echo TABLE_NO; ?></th>
    EOD;
    
        // Dynamically generate table header fields
        foreach ($tableFields as $field) {
            $content .= "                    <th><?php echo {$field}; ?></th>\n";
        }
    
        $content .= <<<EOD
                        <th><?php echo ACTION_ACTION; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="{(count($tableFields) + 2)}" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <br />
        <br />
        EOD;
        if($showAdd){
        $content .= <<<EOD
        <?php if(\$allowAdd){ ?>
        <div style="padding: 5px;border: 1px dashed #bbbbbb;">
            <div class="buttons">
                <a href="" class="positive btnAdd{$controllerName}">
                    <img src="<?php echo \$this->webroot; ?>img/button/plus.png" alt=""/>
                    <?php echo MENU_{$menuLang}_ADD; ?>
                </a>
            </div>
            <div style="clear: both;"></div>
        </div>
        <?php } ?>
        EOD;
        }
        $content .= <<<EOD
    </div>
    <div class="rightPanel"></div>
    EOD;
    
        return $content;
    }
    
    // Example usage:
    // Default fields
    // echo generateBusTypeContent("BusType", "BusModule", "BUS_TYPE");
    
    // Custom fields
    // echo generateBusTypeContent(
    //     "CarType",
    //     "CarModule",
    //     "CAR_TYPE",
    //     true,
    //     true,
    //     true,
    //     [
    //         'TABLE_IMAGE',
    //         'TABLE_CATEGORY',
    //         'TABLE_TITLE',
    //         '"Status"'
    //     ]
    // );

    function generateContentAjax(
        $controllerName = "BusType",
        $tableName = "bus_types",
        $moduleName = "BusType",
        $columns = [
            'id' => 'id',
            'name' => 'name'
        ],
        $condition = "is_active=1",
        $showView = true,
        $showEdit = true,
        $showDelete = true
    ) {
        $content = <<<EOD
    <?php
    EOD;
    if($showView || $showView || $showDelete) {
        $content = <<<EOD
        // Authentication
        \$this->element('check_access');
        EOD;   
    }
    if ($showView) {
        $content = <<<EOD
        \$allowView   = checkAccess(\$user['User']['id'], \$this->params['controller'], 'view');
        EOD;
    }
    if ($showEdit) {
        $content = <<<EOD
        \$allowEdit   = checkAccess(\$user['User']['id'], \$this->params['controller'], 'edit');
        EOD;
    }
    if ($showDelete) {
        $content = <<<EOD
        \$allowDelete = checkAccess(\$user['User']['id'], \$this->params['controller'], 'delete');
        EOD;
    }
    $content = <<<EOD
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Easy set variables
     */
    
    /* Array of database columns which should be read and sent back to DataTables. Use a space where
     * you want to insert a non-database field (for example a counter or static image)
     */
    \$aColumns = array('{$columns['id']}', '{$columns['name']}', '{$columns['apply_rent']}');
    
    /* Indexed column (used for fast and accurate table cardinality) */
    \$sIndexColumn = "{$columns['id']}";
    
    /* DB table to use */
    \$sTable = " {$tableName}";
    
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP server-side, there is
     * no need to edit below this line
     */
    
    /*
     * Paging
     */
    \$sLimit = "";
    if (isset(\$_GET['iDisplayStart']) && \$_GET['iDisplayLength'] != '-1') {
        \$sLimit = "LIMIT " . mysql_real_escape_string(\$_GET['iDisplayStart']) . ", " .
                mysql_real_escape_string(\$_GET['iDisplayLength']);
    }
    
    
    /*
     * Ordering
     */
    if (isset(\$_GET['iSortCol_0'])) {
        \$sOrder = "ORDER BY  ";
        for (\$i = 0; \$i < intval(\$_GET['iSortingCols']); \$i++) {
            if (\$_GET['bSortable_' . intval(\$_GET['iSortCol_' . \$i])] == "true") {
                \$sOrder .= \$aColumns[intval(\$_GET['iSortCol_' . \$i])] . "
                                    " . mysql_real_escape_string(\$_GET['sSortDir_' . \$i]) . ", ";
            }
        }
    
        \$sOrder = substr_replace(\$sOrder, "", -2);
        if (\$sOrder == "ORDER BY") {
            \$sOrder = "";
        }
    }
    
    
    /*
     * Filtering
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here, but concerned about efficiency
     * on very large tables, and MySQL's regex functionality is very limited
     */
    \$sWhere = "";
    if (\$_GET['sSearch'] != "") {
        \$sWhere = "WHERE (";
        for (\$i = 0; \$i < count(\$aColumns); \$i++) {
            \$sWhere .= \$aColumns[\$i] . " LIKE '%" . mysql_real_escape_string(\$_GET['sSearch']) . "%' OR ";
        }
        \$sWhere = substr_replace(\$sWhere, "", -3);
        \$sWhere .= ')';
    }
    
    /* Individual column filtering */
    for (\$i = 0; \$i < count(\$aColumns); \$i++) {
        if (\$_GET['bSearchable_' . \$i] == "true" && \$_GET['sSearch_' . \$i] != '') {
            if (\$sWhere == "") {
                \$sWhere = "WHERE ";
            } else {
                \$sWhere .= " AND ";
            }
            \$sWhere .= \$aColumns[\$i] . " LIKE '%" . mysql_real_escape_string(\$_GET['sSearch_' . \$i]) . "%' ";
        }
    }
    
    /* Customize condition */
    \$condition = "{$condition}";
    if (!eregi("WHERE", \$sWhere)) {
        \$sWhere .= "WHERE " . \$condition;
    } else {
        \$sWhere .= "AND " . \$condition;
    }
    
    /*
     * SQL queries
     * Get data to display
     */
    \$sQuery = "
            SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", \$aColumns)) . "
            FROM   \$sTable
            \$sWhere
            \$sOrder
            \$sLimit
    ";
    \$rResult = mysql_query(\$sQuery) or die(mysql_error());
    
    /* Data set length after filtering */
    \$sQuery = "
            SELECT FOUND_ROWS()
    ";
    \$rResultFilterTotal = mysql_query(\$sQuery) or die(mysql_error());
    \$aResultFilterTotal = mysql_fetch_array(\$rResultFilterTotal);
    \$iFilteredTotal = \$aResultFilterTotal[0];
    
    /* Total data set length */
    \$sQuery = "
            SELECT COUNT(" . \$sIndexColumn . ")
            FROM   \$sTable
    ";
    \$rResultTotal = mysql_query(\$sQuery) or die(mysql_error());
    \$aResultTotal = mysql_fetch_array(\$rResultTotal);
    \$iTotal = \$aResultTotal[0];
    
    
    /*
     * Output
     */
    \$output = array(
        "sEcho" => intval(\$_GET['sEcho']),
        "iTotalRecords" => \$iTotal,
        "iTotalDisplayRecords" => \$iFilteredTotal,
        "aaData" => array()
    );
    \$index = \$_GET['iDisplayStart'];
    while (\$aRow = mysql_fetch_array(\$rResult)) {
        \$row = array();
        for (\$i = 0; \$i < count(\$aColumns); \$i++) {
            if (\$i == 0) {
                /* Special output formatting */
                \$row[] = ++\$index;
            } else if (\$aColumns[\$i] == 't_transportation_types.photo'){
                if(\$aRow[\$i] != ""){
                    \$row[] = '<img src="' . \$this->webroot . 'public/transportation_type/'.\$aRow[\$i].'" style="width: 100px;" />';
                } else {
                    \$row[] = "";
                }
            } else if (\$aColumns[\$i] == '{$columns['apply_rent']}'){
                if(\$aRow[\$i] == 1){
                    \$row[] = ACTION_YES;
                } else {
                    \$row[] = ACTION_NO;
                }
            } else if (\$aColumns[\$i] != ' ') {
                /* General output */
                \$row[] = \$aRow[\$i];
            }
        }
        \$row[] = '';
    EOD;
    
        // Dynamically add action buttons based on parameters
        if ($showView) {
            $content .= <<<EOD
        \$row['actions'] .= (\$allowView ? '<a href="" class="btnView{$controllerName}" rel="' . \$aRow[0] . '" name="' . \$aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . \$this->webroot . 'img/button/view.png" /></a> ' : '');
    EOD;
        }
    
        if ($showEdit) {
            $content .= <<<EOD
        \$row['actions'] .= (\$allowEdit ? '<a href="" class="btnEdit{$controllerName}" rel="' . \$aRow[0] . '" name="' . \$aRow[2] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . \$this->webroot . 'img/button/edit.png" /></a> ' : '');
    EOD;
        }
    
        if ($showDelete) {
            $content .= <<<EOD
        \$row['actions'] .= (\$allowDelete ? '<a href="" class="btnDelete{$controllerName}" rel="' . \$aRow[0] . '" name="' . \$aRow[2] . '"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . \$this->webroot . 'img/button/delete.png" /></a>' : '');
    EOD;
        }
    
        $content .= <<<EOD
        \$output['aaData'][] = \$row;
    }
    
    echo json_encode(\$output);
    ?>
    EOD;
    
        return $content;
    }
    
    // Example usage:
    // Default - all buttons shown
    // echo generateDataTableServerSideContent();
    
    // Custom - selective buttons
    // echo generateDataTableServerSideContent(
    //     "CarType",           // controllerName
    //     "car_types",        // tableName
    //     "CarModule",        // moduleName
    //     [
    //         'id' => 'car_types.id',
    //         'name' => 'car_types.name',
    //         'apply_rent' => 'car_types.apply_rent'
    //     ],                  // columns
    //     "car_types.is_active=1", // condition
    //     true,              // showView
    //     false,             // showEdit
    //     true               // showDelete
    // );

    function generateContentView($controllerName, $fieldsConfig) {
        $controllerProper = ucfirst($controllerName);
        
        // Generate dynamic table rows
        $tableRows = '';
        foreach ($fieldsConfig as $field) {
            $model = isset($field['model']) ? $field['model'] : $controllerProper;
            $label = $field['label'];
            $fieldName = $field['field'];
            $isConditional = isset($field['conditional']) ? $field['conditional'] : false;
            $isNl2br = isset($field['nl2br']) ? $field['nl2br'] : false;
            
            $tableRows .= "<tr>\n";
            $tableRows .= "    <th style=\"width: 10%; font-size: 12px;\"><?php echo {$label}; ?> :</th>\n";
            $tableRows .= "    <td style=\"font-size: 12px;\">\n";
            
            if ($isConditional) {
                $tableRows .= "        <?php\n";
                $tableRows .= "        if(\$this->data['{$model}']['{$fieldName}'] == 1) {\n";
                $tableRows .= "            echo {$field['true_value']};\n";
                $tableRows .= "        } else {\n";
                $tableRows .= "            echo {$field['false_value']};\n";
                $tableRows .= "        }\n";
                $tableRows .= "        ?>\n";
            } else {
                if ($isNl2br) {
                    $tableRows .= "        <?php echo nl2br(\$this->data['{$model}']['{$fieldName}']); ?>\n";
                } else {
                    $tableRows .= "        <?php echo \$this->data['{$model}']['{$fieldName}']; ?>\n";
                }
            }
            
            $tableRows .= "    </td>\n";
            $tableRows .= "</tr>\n";
        }
    
        $content = <<<HTML
    <script type="text/javascript">
        \$(document).ready(function(){
            \$(".btnBack{$controllerProper}").click(function(event){
                event.preventDefault();
                oCache.iCacheLower = -1;
                oTable{$controllerProper}.fnDraw(false);
                var rightPanel=\$(this).parent().parent().parent();
                var leftPanel=rightPanel.parent().find(".leftPanel");
                rightPanel.hide();rightPanel.html("");
                leftPanel.show("slide", { direction: "left" }, 500);
            });
        });
    </script>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnBack{$controllerProper}">
                <img src="<?php echo \$this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <br />
    <table width="100%" cellpadding="10">
        {$tableRows}
    </table>
    HTML;
    
        return $content;
    }
    // Example configuration for fields
    // $fieldsConfig = [
    //     [
    //         'label' => 'MENU_TRANSPORTATION_TYPE',
    //         'field' => 'name',
    //         'model' => 'TTransportationType'
    //     ],
    //     [
    //         'label' => 'TABLE_NAME',
    //         'field' => 'name'
    //     ],
    //     [
    //         'label' => '"Number of Seat"',
    //         'field' => 'number_of_seat'
    //     ],
    //     [
    //         'label' => '"Apply Rent"',
    //         'field' => 'apply_rent',
    //         'conditional' => true,
    //         'true_value' => 'ACTION_YES',
    //         'false_value' => 'ACTION_NO'
    //     ],
    //     [
    //         'label' => 'GENERAL_DESCRIPTION',
    //         'field' => 'description',
    //         'nl2br' => true
    //     ]
    // ];

    function generateContentAdd($controllerName, $fieldsConfig, $options = array()) {
        $controllerProper = ucfirst($controllerName);
        $modelName = isset($options['modelName']) ? $options['modelName'] : $controllerProper;
        
        // Generate form fields
        $formFields = '';
        foreach ($fieldsConfig as $field) {
            $fieldName = $field['field'];
            $label     = $field['label'];
            $required  = isset($field['required']) && $field['required'] ? '<span class="red">*</span>' : '';
            $inputType = isset($field['type']) ? $field['type'] : 'text';
            $selectOptions   = isset($field['options']) ? $field['options'] : array();
            $validationClass = isset($field['required']) && $field['required'] ? 'validate[required]' : '';
            
            $formFields .= "<tr>\n";
            $formFields .= "    <td><label for=\"{$controllerProper}{$fieldName}\">{$label} {$required} :</label></td>\n";
            $formFields .= "    <td>\n";
            $formFields .= "        <div class=\"inputContainer\">\n";
            
            if ($inputType === 'select') {
                $emptyOption = isset($field['empty']) ? $field['empty'] : 'INPUT_SELECT';
                if (isset($field['customSelect'])) {
                    // Custom select with selected logic
                    $formFields .= "            <select name=\"data[{$controllerProper}][{$fieldName}]\" id=\"{$controllerProper}{$fieldName}\" class=\"{$validationClass}\">\n";
                    $formFields .= "                <option value=\"\"><?php echo {$emptyOption}; ?></option>\n";
                    foreach ($selectOptions as $value => $option) {
                        $formFields .= "                <option value=\"{$value}\"><?php echo {$option}; ?></option>\n";
                    }
                    $formFields .= "            </select>\n";
                } else {
                    $formFields .= "            <?php echo \$this->Form->input('{$fieldName}', array(";
                    $formFields .= "'class'=>'{$validationClass}', ";
                    $formFields .= "'label' => false, ";
                    $formFields .= "'empty' => {$emptyOption}, ";
                    $formFields .= "'div' => false, ";
                    $formFields .= "'style' => 'width: 260px'";
                    $formFields .= ")); ?>\n";
                }
            } elseif ($inputType === 'textarea') {
                $formFields .= "            <?php echo \$this->Form->textarea('{$fieldName}', array(";
                $formFields .= "'style' => 'width: 250px;'";
                $formFields .= ")); ?>\n";
            } else {
                $formFields .= "            <?php echo \$this->Form->text('{$fieldName}', array(";
                $formFields .= "'class'=>'{$validationClass}', ";
                $formFields .= "'style' => 'width: 250px;'";
                $formFields .= ")); ?>\n";
            }
            
            $formFields .= "        </div>\n";
            $formFields .= "    </td>\n";
            $formFields .= "</tr>\n";
        }
    
        $content = <<<HTML
    <?php 
    // Prevent Button Submit
    echo \$this->element('prevent_multiple_submit'); ?>
    <script type="text/javascript">
        \$(document).ready(function(){
            // Prevent Key Enter
            preventKeyEnter();
            $("#{$controllerProper}TTransportationTypeId").chosen({width: 260});
            $("#{$controllerProper}AddForm").validationEngine('attach', {
                isOverflown: true,
                overflownDIV: ".ui-tabs-panel"
            });
            $("#{$controllerProper}AddForm").ajaxForm({
                beforeSerialize: function(\$form, options) {
                    if(\$("#{$controllerProper}TTransportationTypeId").val() == ""){
                        alertSelectRequireField();
                        \$(".btnSave{$controllerProper}").removeAttr('disabled');
                        return false;
                    }
                },
                beforeSubmit: function(arr, \$form, options) {
                    \$(".txtSave{$controllerProper}").html("<?php echo ACTION_LOADING; ?>");
                    \$(".loader").attr("src", "<?php echo \$this->webroot; ?>img/layout/spinner.gif");
                },
                success: function(result) {
                    \$(".loader").attr("src", "<?php echo \$this->webroot; ?>img/layout/spinner-placeholder.gif");
                    \$(".btnBack{$controllerProper}").click();
                    // alert message
                    if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                        createSysAct('{$controllerProper}', 'Add', 2, result);
                        \$("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                    }else {
                        createSysAct('{$controllerProper}', 'Add', 1, '');
                        // alert message
                        \$("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                    }
                    \$("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            \$(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                \$(this).dialog("close");
                            }
                        }
                    });
                }
            });
            \$(".btnBack{$controllerProper}").click(function(event){
                event.preventDefault();
                oCache.iCacheLower = -1;
                oTable{$controllerProper}.fnDraw(false);
                var rightPanel=\$(this).parent().parent().parent();
                var leftPanel=rightPanel.parent().find(".leftPanel");
                rightPanel.hide();rightPanel.html("");
                leftPanel.show("slide", { direction: "left" }, 500);
            });
        });
    </script>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnBack{$controllerProper}">
                <img src="<?php echo \$this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <br />
    <?php echo \$this->Form->create('{$modelName}'); ?>
    <fieldset>
        <legend><?php __(MENU_{$controllerProper}_INFO); ?></legend>
        <table>
            {$formFields}
        </table>
    </fieldset>
    <br />
    <div class="buttons">
        <button type="submit" class="positive btnSave{$controllerProper}">
            <img src="<?php echo \$this->webroot; ?>img/button/save.png" alt=""/>
            <span class="txtSave{$controllerProper}"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
    <?php echo \$this->Form->end(); ?>
    HTML;
    
        return $content;
    }
    
    // Example configuration for fields
    // $fieldsConfig = [
    //     [
    //         'field' => 't_transportation_type_id',
    //         'label' => 'MENU_TRANSPORTATION_TYPE',
    //         'type' => 'select',
    //         'required' => true
    //     ],
    //     [
    //         'field' => 'name',
    //         'label' => 'TABLE_NAME',
    //         'required' => true
    //     ],
    //     [
    //         'field' => 'number_of_seat',
    //         'label' => '"Number of Seat"',
    //         'required' => true
    //     ],
    //     [
    //         'field' => 'apply_rent',
    //         'label' => '"Apply Rent"',
    //         'type' => 'select',
    //         'required' => true,
    //         'customSelect' => true,
    //         'options' => [
    //             '1' => 'ACTION_YES',
    //             '0' => 'ACTION_NO'
    //         ]
    //     ],
    //     [
    //         'field' => 'description',
    //         'label' => 'GENERAL_DESCRIPTION',
    //         'type' => 'textarea'
    //     ]
    // ];
    
    // Example usage:
    // echo generateFormContent('busType', $fieldsConfig, ['modelName' => 'BusType']);

    function generateContentEdit($controllerName, $fieldsConfig, $options = array()) {
        $controllerProper = ucfirst($controllerName);
        $modelName = isset($options['modelName']) ? $options['modelName'] : $controllerProper;
        $formId = "{$controllerProper}EditForm";
        
        // Generate form fields
        $formFields = '';
        foreach ($fieldsConfig as $field) {
            $fieldName = $field['field'];
            $label = $field['label'];
            $required = isset($field['required']) && $field['required'] ? '<span class="red">*</span>' : '';
            $inputType = isset($field['type']) ? $field['type'] : 'text';
            $selectOptions = isset($field['options']) ? $field['options'] : array();
            $validationClass = isset($field['required']) && $field['required'] ? 'validate[required]' : '';
            
            $formFields .= "<tr>\n";
            $formFields .= "    <td><label for=\"{$controllerProper}{$fieldName}\">{$label} {$required} :</label></td>\n";
            $formFields .= "    <td>\n";
            $formFields .= "        <div class=\"inputContainer\">\n";
            
            if ($inputType === 'select') {
                $emptyOption = isset($field['empty']) ? $field['empty'] : 'INPUT_SELECT';
                if (isset($field['customSelect'])) {
                    // Custom select with selected logic
                    $formFields .= "            <select name=\"data[{$controllerProper}][{$fieldName}]\" id=\"{$controllerProper}{$fieldName}\" class=\"{$validationClass}\">\n";
                    $formFields .= "                <option value=\"\"><?php echo {$emptyOption}; ?></option>\n";
                    foreach ($selectOptions as $value => $option) {
                        $formFields .= "                <option value=\"{$value}\" <?php if(\$this->data['{$controllerProper}']['{$fieldName}'] == {$value}){ ?>selected=\"\"<?php } ?>><?php echo {$option}; ?></option>\n";
                    }
                    $formFields .= "            </select>\n";
                } else {
                    // Standard CakePHP select input
                    $formFields .= "            <?php echo \$this->Form->input('{$fieldName}', array(";
                    $formFields .= "'class'=>'{$validationClass}', ";
                    $formFields .= "'label' => false, ";
                    $formFields .= "'empty' => {$emptyOption}, ";
                    $formFields .= "'div' => false, ";
                    $formFields .= "'style' => 'width: 260px'";
                    $formFields .= ")); ?>\n";
                }
            } elseif ($inputType === 'textarea') {
                $formFields .= "            <?php echo \$this->Form->textarea('{$fieldName}', array(";
                $formFields .= "'style' => 'width: 250px;'";
                $formFields .= ")); ?>\n";
            } else {
                $formFields .= "            <?php echo \$this->Form->text('{$fieldName}', array(";
                $formFields .= "'class'=>'{$validationClass}', ";
                $formFields .= "'style' => 'width: 250px;'";
                $formFields .= ")); ?>\n";
            }
            
            $formFields .= "        </div>\n";
            $formFields .= "    </td>\n";
            $formFields .= "</tr>\n";
        }
    
        $content = <<<HTML
    <?php 
    // Prevent Button Submit
    echo \$this->element('prevent_multiple_submit'); ?>
    <script type="text/javascript">
        \$(document).ready(function(){
            // Prevent Key Enter
            preventKeyEnter();
            $("#{$controllerProper}TTransportationTypeId").chosen({width: 260});
            $("#{$formId}").validationEngine('attach', {
                isOverflown: true,
                overflownDIV: ".ui-tabs-panel"
            });
            $("#{$formId}").ajaxForm({
                beforeSerialize: function(\$form, options) {
                    if(\$("#{$controllerProper}TTransportationTypeId").val() == ""){
                        alertSelectRequireField();
                        \$(".btnSave{$controllerProper}").removeAttr('disabled');
                        return false;
                    }
                },
                beforeSubmit: function(arr, \$form, options) {
                    \$(".txtSave{$controllerProper}").html("<?php echo ACTION_LOADING; ?>");
                    \$(".loader").attr("src", "<?php echo \$this->webroot; ?>img/layout/spinner.gif");
                },
                success: function(result) {
                    \$(".loader").attr("src", "<?php echo \$this->webroot; ?>img/layout/spinner-placeholder.gif");
                    \$(".btnBack{$controllerProper}").click();
                    // alert message
                    if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                        createSysAct('{$controllerProper}', 'Edit', 2, result);
                        \$("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                    }else {
                        createSysAct('{$controllerProper}', 'Edit', 1, '');
                        // alert message
                        \$("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                    }
                    \$("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            \$(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                \$(this).dialog("close");
                            }
                        }
                    });
                }
            });
            \$(".btnBack{$controllerProper}").click(function(event){
                event.preventDefault();
                oCache.iCacheLower = -1;
                oTable{$controllerProper}.fnDraw(false);
                var rightPanel=\$(this).parent().parent().parent();
                var leftPanel=rightPanel.parent().find(".leftPanel");
                rightPanel.hide();rightPanel.html("");
                leftPanel.show("slide", { direction: "left" }, 500);
            });
        });
    </script>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnBack{$controllerProper}">
                <img src="<?php echo \$this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <br />
    <?php echo \$this->Form->create('{$modelName}', array('id' => '{$formId}')); ?>
    <?php echo \$this->Form->input('id'); ?>
    <fieldset>
        <legend><?php __(MENU_{$controllerProper}_INFO); ?></legend>
        <table>
            {$formFields}
        </table>
    </fieldset>
    <br />
    <div class="buttons">
        <button type="submit" class="positive btnSave{$controllerProper}">
            <img src="<?php echo \$this->webroot; ?>img/button/save.png" alt=""/>
            <span class="txtSave{$controllerProper}"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
    <?php echo \$this->Form->end(); ?>
    HTML;
    
        return $content;
    }
    
    // Example configuration for fields
    // $fieldsConfig = [
    //     [
    //         'field' => 't_transportation_type_id',
    //         'label' => 'MENU_TRANSPORTATION_TYPE',
    //         'type' => 'select',
    //         'required' => true
    //     ],
    //     [
    //         'field' => 'name',
    //         'label' => 'TABLE_NAME',
    //         'required' => true
    //     ],
    //     [
    //         'field' => 'number_of_seat',
    //         'label' => '"Number of Seat"',
    //         'required' => true
    //     ],
    //     [
    //         'field' => 'apply_rent',
    //         'label' => '"Apply Rent"',
    //         'type' => 'select',
    //         'required' => true,
    //         'customSelect' => true,
    //         'options' => [
    //             '1' => 'ACTION_YES',
    //             '0' => 'ACTION_NO'
    //         ]
    //     ],
    //     [
    //         'field' => 'description',
    //         'label' => 'GENERAL_DESCRIPTION',
    //         'type' => 'textarea'
    //     ]
    // ];
    
    // Example usage:
    // echo generateEditFormContent('busType', $fieldsConfig, ['modelName' => 'BusType']);

}

?>