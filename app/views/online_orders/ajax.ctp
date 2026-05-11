<?php
include("includes/function.php");
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    'online_orders.id',
    'online_orders.created',
    'online_orders.code',
    'online_orders.reference',
    'online_orders.name',
    'online_orders.contact_telephone',
    'online_orders.email',
    'IFNULL(payment_methods.name, "N/A")',
    '((online_orders.total_amount + online_orders.total_vat + online_orders.lucky_draw_fee) - online_orders.discount_amount)',
    'online_orders.type',
    'online_orders.note',
    'online_orders.modified',
    'online_orders.status',
    'online_orders.click_payment');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "online_orders.id";

/* DB table to use */
$sTable = "online_orders LEFT JOIN payment_methods ON payment_methods.id = online_orders.payment_method_id";

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
    // Strip whitespace from the search value so "012 345 678" matches "012345678"
    $sSearchRaw    = mysql_real_escape_string($_GET['sSearch']);
    $sSearchNoSpace = mysql_real_escape_string(str_replace(' ', '', $_GET['sSearch']));
    $sWhere = "WHERE (";
    for ($i = 0; $i < count($aColumns) - 1; $i++) {
        if ($aColumns[$i] == 'online_orders.contact_telephone' || $aColumns[$i] == 'online_orders.code') {
            // Compare stored telephone (spaces stripped) against search value (spaces stripped)
            $sWhere .= "REPLACE(" . $aColumns[$i] . ", ' ', '') LIKE '%" . $sSearchNoSpace . "%' OR ";
        } else if ($aColumns[$i] == 'online_orders.reference' || $aColumns[$i] == 'online_orders.name' || $aColumns[$i] == 'online_orders.email') {
            $sWhere .= $aColumns[$i] . " LIKE '%" . $sSearchRaw . "%' OR ";
        }
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - 1; $i++) {
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
$condition = "online_orders.status >= 0 AND online_orders.offline_project_id = 1";
if($type != 'all'){
    if($type == 1){
        $condition .= " AND online_orders.type IN (1,2) AND online_orders.email != 'user@gmail.com'";
    } else {
        $condition .= " AND online_orders.type = ".$type;
    }
}

if($status != 'all'){
    if($status == 1){
        $condition .= " AND online_orders.status IN (1,2) ";
    } else {
        $condition .= " AND online_orders.status = ".$status;
    }
}

if($date != 'all'){
    $condition .= " AND online_orders.date = '".$date."'";
}

if($payment != 'all'){
    $condition .= " AND online_orders.payment_method_id IN (".$payment.")";
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
        } else if ($aColumns[$i] == 'online_orders.status') {
            if($aRow[$i] == 0){
                $row[] = 'Cancel';
            } else if($aRow[$i] == 1){
                $row[] = 'Confirmed';
            } else if($aRow[$i] == 2){
                $row[] = 'Confirmed';
            } else {
                $row[] = 'Completed';
            }
        } else if ($aColumns[$i] == 'online_orders.email') {
            if($aRow[$i] != 'user@gmail.com' && $aRow[$i] != 'minapp@gmail.com'){
                $row[] = $aRow[$i];
            } else {
                $row[] = "";
            }
        } else if ($aColumns[$i] == 'online_orders.type') {
            if($aRow[$i] == 1){
                $row[] = "Website";
            } else if($aRow[$i] == 2){
                if($aRow[6] == "user@gmail.com"){
                    $row[] = "APP";
                } else {
                    $row[] = "Website";
                }
            } else if($aRow[$i] == 3){
                $row[] = "APi";
            } else if($aRow[$i] == 4){
                $row[] = "Terminal";
            } else if($aRow[$i] == 5){
                $row[] = "ABA (Mini App)";
            } else if($aRow[$i] == 6){
                $row[] = "Internal App";
            } else {
                $row[] = "";
            }
        } else if ($aColumns[$i] == 'IFNULL(payment_methods.name, "N/A")') {
            $viewApiRepsonse = "";
            $goPayment = ' <img src="'.$this->webroot.'img/button/inactive.png" />';
            if($user['User']['id'] == 2 && $aRow[$i] != "N/A" && $aRow[$i] != "Cash" && $aRow[9] != 5){
                $viewApiRepsonse = ' <img rel="'.$aRow[0].'" style="cursor: pointer;" src="'.$this->webroot.'img/button/link.png" alt="" class="btnOnlineOrderViewAPi" />';
            }
            if($aRow[13] == 1){
                $goPayment = ' <img src="'.$this->webroot.'img/button/tick.png" />';
            }
            $row[] = $aRow[$i].$goPayment.$viewApiRepsonse ;
        } else if ($aColumns[$i] == 'online_orders.created' || $aColumns[$i] == 'online_orders.modified') {
            if($aRow[$i] != "" && $aRow[$i] != "0000-00-00 00:00:00"){
                $row[] = dateShort($aRow[$i], "d/m/Y H:i:s");
            } else {
                $row[] = "";
            }
        } else if ($aColumns[$i] == '((online_orders.total_amount + online_orders.total_vat + online_orders.lucky_draw_fee) - online_orders.discount_amount)') {
            $row[] = number_format($aRow[$i], 2)." $";
        } else if ($aColumns[$i] == 'online_orders.click_payment') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] = '<a href="" class="btnViewOnlineOrder" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> '; 
    
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>