<?php
include("includes/function.php");
// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('travel_package_orders.id', 'CONCAT_WS("",travel_package_orders.photo_path,travel_package_orders.photo)', 'travel_packages.name', 'travel_package_orders.package_date', 'travel_package_orders.package_code', 'travel_package_orders.code', 'travel_package_orders.name', 'travel_package_orders.telephone', 'travel_package_orders.package_price', 'travel_package_orders.package_expired', 'travel_package_orders.status');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "travel_package_orders.id";

/* DB table to use */
$sTable = "travel_package_orders 
           INNER JOIN travel_packages ON travel_packages.id = travel_package_orders.travel_package_id";

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
        if($aColumns[$i] == 'name'){
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
$condition = "travel_package_orders.status IN (2,3) AND travel_package_orders.type = 1";
if($travelPackage != 'all'){
    $condition .= " AND travel_package_orders.travel_package_id = ".$travelPackage;
}
if($telephone != 'all'){
    $condition .= " AND travel_package_orders.telephone = '".$telephone."'";
}
if($date != 'all'){
    $condition .= " AND travel_package_orders.package_date = '".$date."'";
}
if($status != 'all'){
    if($status == "1"){ // Active
        $condition .= " AND travel_package_orders.package_expired >= '".date("Y-m-d")."'";
    } else {
        $condition .= " AND travel_package_orders.package_expired < '".date("Y-m-d")."'";
    }
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
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if ($aColumns[$i] == 'CONCAT_WS("",travel_package_orders.photo_path,travel_package_orders.photo)') {
            $row[] = '<img src="' . $aRow[$i] . '" width="50" height="50" />';
        } else if ($aColumns[$i] == 'travel_package_orders.package_price') {
            $row[] = number_format($aRow[$i], 2)." $";
        } else if ($aColumns[$i] == 'travel_package_orders.status') {
            $expired = strtotime($aRow[9]);
            if($expired >= strtotime(date("Y-m-d"))){
                if($aRow[$i] == 2){
                    $row[] = "Active";
                } else {
                    $row[] = "Disabled";
                }
            } else {
                $row[] = "Inactive";
            }
        } else if ($aColumns[$i] == 'travel_package_orders.package_date' || $aColumns[$i] == 'travel_package_orders.package_expired') {
            $row[] = dateShort($aRow[$i]);
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] =
            ($allowView ? '<a href="" class="BtnTravelPackageOrder" rel="' . $aRow[0] . '" name="' . $aRow[3] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '').
            ($allowEdit && $aRow[10] == 2 ? '<a href="" class="BtnEditTravelPackageOrder" rel="' . $aRow[0] . '" name="' . $aRow[3] . '"><img alt="Edit" onmouseover="Tip(\'Edit\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : ' ').
            ($allowDelete && $aRow[10] == 2 ? '<a href="" class="BtnDisableTravelPackageOrder" rel="' . $aRow[0] . '" name="' . $aRow[3] . '"><img alt="Disable" onmouseover="Tip(\'Disable\')" src="' . $this->webroot . 'img/button/delete.png" /></a> ' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>