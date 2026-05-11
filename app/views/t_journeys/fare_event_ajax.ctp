<?php
include("includes/function.php");
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    'name',
    'start', 
    'end',
    'CONCAT_WS(",",mon,tue,wed,thu,fri,sat,sun)',
    'price',
    'foreigner_price',
    'membership',
    'id');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "id";

/* DB table to use */
$sTable = "t_journey_fare_events";

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
    $sOrder = "ORDER BY id DESC";
//    for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
//        if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
//            $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
//        }
//    }

//    $sOrder = substr_replace($sOrder, "", -2);
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
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
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
$condition = "t_journey_id = ".$journey_id." AND is_active = 1";
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
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($aColumns[$i] == 'start' || $aColumns[$i] == 'end') {
            if($aRow[$i] != '' && $aRow[$i] != '0000-00-00'){
                $row[] = dateShort($aRow[$i]);
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'price' || $aColumns[$i] == 'foreigner_price' || $aColumns[$i] == 'membership') {
            $row[] = number_format($aRow[$i], 2);
        } else if ($aColumns[$i] == 'CONCAT_WS(",",mon,tue,wed,thu,fri,sat,sun)') {
            $schedule = explode(",", $aRow[$i]);
            $lblCheck = '';
            if($schedule[0] == 1){
                $lblCheck .= 'Mon ';
            } 
            if($schedule[1] == 1){
                $lblCheck .= 'Tue ';
            }
            if($schedule[2] == 1){
                $lblCheck .= 'Wed ';
            }
            if($schedule[3] == 1){
                $lblCheck .= 'Thu ';
            }
            if($schedule[4] == 1){
                $lblCheck .= 'Fri ';
            }
            if($schedule[5] == 1){
                $lblCheck .= 'Sat ';
            }
            if($schedule[6] == 1){
                $lblCheck .= 'Sun ';
            }
            $row[] = $lblCheck;
        } else if ($aColumns[$i] == 'id') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] =
            '<a href="#" class="btnEditTJourneyFareEvent" rel="' . $aRow[7] . '" name="' . $aRow[0] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> '.
            '<a href="#" class="btnDeleteTJourneyFareEvent" rel="' . $aRow[7] . '" name="' . $aRow[0] . '"><img alt="Delete" onmouseover="Tip(\'' . ACTION_VOID . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>';
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>