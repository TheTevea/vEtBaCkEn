<?php

// Authentication
$this->element('check_access');
$allowView     = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit     = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete   = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
// $aColumns = array('t_destinations.id', 'countries.name', 'provinces.name', 't_destination_groups.name', 't_destinations.code', 't_destinations.name_kh', 't_destinations.name', 't_destinations.name_cn', 't_destinations.is_active');

$aColumns = array('t_destinations.id', 'countries.name', 'provinces.name', 't_destination_groups.name', 't_destinations.code', 't_destinations.name_kh', 't_destinations.name', 't_destinations.name_cn', 't_destinations.is_allow_shedule');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "t_destinations.id";

/* DB table to use */
$sTable = "t_destinations 
           LEFT JOIN countries ON countries.id = t_destinations.country_id
           LEFT JOIN provinces ON provinces.id = t_destinations.province_id 
           LEFT JOIN t_destination_groups ON t_destination_groups.id = t_destinations.t_destination_group_id";

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP server-side, there is
 * no need to edit below this line
 */

/*
 * Paging
 */
$sLimit = "";
if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
    $sLimit = "LIMIT " . mysql_real_escape_string($_GET['iDisplayStart']) . ", " .
            mysql_real_escape_string($_GET['iDisplayLength']);
}


/*
 * Ordering
 */
if (isset($_GET['iSortCol_0'])) {
    $sOrder = "ORDER BY  ";
    for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
        if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
            $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . "
                                " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
        }
    }

    $sOrder = substr_replace($sOrder, "", -2);
    if ($sOrder == "ORDER BY") {
        $sOrder = "";
    }
}


/*
 * Filtering
 * NOTE this does not match the built-in DataTables filtering which does it
 * word by word on any field. It's possible to do here, but concerned about efficiency
 * on very large tables, and MySQL's regex functionality is very limited
 */
$sWhere = "";
if ($_GET['sSearch'] != "") {
    $sWhere = "WHERE (";
    for ($i = 0; $i < count($aColumns); $i++) {
        if($aColumns[$i] == 't_destinations.code' || $aColumns[$i] == 't_destinations.name_kh' || $aColumns[$i] == 't_destinations.name'){
            $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
        }
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns); $i++) {
    if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
    }
}

/* Customize condition */
$condition = "t_destinations.is_active = 1 AND t_destinations.offline_project_id = 1";
if($province != 'all'){
    $condition .= " AND t_destinations.province_id = ".$province;
}

if (!eregi("WHERE", $sWhere)) {
    $sWhere .= "WHERE " . $condition;
} else {
    $sWhere .= "AND " . $condition;
}

/*
 * SQL queries
 * Get data to display
 */
$sQuery = "
        SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
        FROM   $sTable
        $sWhere
        $sOrder
        $sLimit
";
$rResult = mysql_query($sQuery) or die(mysql_error());

/* Data set length after filtering */
$sQuery = "
        SELECT FOUND_ROWS()
";
$rResultFilterTotal = mysql_query($sQuery) or die(mysql_error());
$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
$iFilteredTotal = $aResultFilterTotal[0];

/* Total data set length */
$sQuery = "
        SELECT COUNT(" . $sIndexColumn . ")
        FROM   $sTable
";
$rResultTotal = mysql_query($sQuery) or die(mysql_error());
$aResultTotal = mysql_fetch_array($rResultTotal);
$iTotal = $aResultTotal[0];


/*
 * Output
 */
$output = array(
    "sEcho" => intval($_GET['sEcho']),
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => array()
);

$index = $_GET['iDisplayStart'];
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    // $destiTo = '';
    // $sqlTo = mysql_query("SELECT GROUP_CONCAT(name) FROM t_destinations WHERE id IN (SELECT t_destination_to_id FROM t_destination_tos WHERE t_destination_from_id = ".$aRow[0]." AND is_active = 1)");
    // if(mysql_num_rows($sqlTo)){
    //     $rowTo = mysql_fetch_array($sqlTo);
    //     $destiTo = $rowTo[0];
    // }
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if ($aColumns[$i] == 't_destinations.is_allow_shedule') {
            // $row[] = $destiTo;
            $btnAllowSchedule = "";
            if($allowEdit){
                if($aRow[$i] == 1){
                    $btnAllowSchedule = "btnUnAllowScheduleTDestination";
                } else {
                    $btnAllowSchedule = "btnAllowScheduleTDestination";
                }
            }
            $row[] = '<img alt="' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '" class="'.$btnAllowSchedule.'" onmouseover="Tip(\'' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '\')" rel="' . $aRow[0] . '" name="'.$aRow[6].'" style="cursor: pointer;" src="' . $this->webroot . 'img/button/' . ($aRow[$i] == 1 ? 'active' : 'inactive') . '.png" />';
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    // $sqlTransaction = mysql_query("SELECT id FROM t_tickets WHERE t_destination_to_id = ".$aRow[0]." OR t_destination_from_id = ".$aRow[0]." LIMIT 1");
    $row[] =
            ($allowView ? '<a href="" class="btnViewTDestination" rel="' . $aRow[0] . '" name="' . $aRow[4] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowEdit ? '<a href="" class="btnEditTDestination" rel="' . $aRow[0] . '" name="' . $aRow[4] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
            ($allowDelete ? '<a href="" class="btnDeleteTDestination" rel="' . $aRow[0] . '" name="' . $aRow[4] . '"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>