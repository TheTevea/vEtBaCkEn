<?php
// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    't_tickets.id',
    'IF(online_orders.code!="",online_orders.code,IF(t_tickets.api_bank_ref!="",t_tickets.api_bank_ref,""))',
    't_tickets.code',
    'IF(t_tickets.api_bank_ref!="",t_tickets.pay_date,t_tickets.date)', 
    'CONCAT_WS(" ", t_tickets.journey_date,t_tickets.journey_time)',
    '(SELECT name FROM t_destinations WHERE id = t_tickets.t_destination_from_id)',
    '(SELECT name FROM t_destinations WHERE id = t_tickets.t_destination_to_id)',
    '(SELECT GROUP_CONCAT(label_number) FROM t_ticket_details WHERE t_ticket_id = t_tickets.id AND is_active = 1)',
    'IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0)',
    't_tickets.telephone',
    'IF(t_tickets.api_bank_ref!="",payCall.name,payment_methods.name)', //10
    't_tickets.t_agent_id',
    't_tickets.created',
    'currency_centers.symbol',
    'IFNULL(t_tickets.discount_amount, 0)',
    'IFNULL(online_orders.discount_amount, 0)', // 15
    'IFNULL(online_orders.total_amount, 0)',
    'IFNULL(t_tickets.total_amount, 0)',
    't_tickets.terminal_id',
    't_tickets.api_bank_ref');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "t_tickets.id";

/* DB table to use */
$sTable = "t_tickets 
           INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id 
           LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id
           LEFT JOIN payment_methods ON payment_methods.id = online_orders.payment_method_id
           LEFT JOIN payment_methods AS payCall ON payCall.id = t_tickets.payment_method_id
           LEFT JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id";

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
    for ($i = 0; $i < count($aColumns) - 7; $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - 7; $i++) {
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
$condition = "(t_tickets.type = 5 OR t_tickets.type = 10 OR t_tickets.type = 11 OR (t_tickets.type = 2 AND t_tickets.api_bank_ref != ''))";
$condition != '' ? $condition .= ' AND ' : '';
// Filter Status
if ($data[8] != '') {
    $condition .= 't_tickets.status =' . $data[8];
} else {
    $condition .= 't_tickets.status >= 0';
}
if($user['User']['type'] != 1){
    $condition .= " AND t_tickets.offline_project_id = ".$user['User']['offline_project_id'];
}
// Booking Date
if ($data[1] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= DATE(IF(t_tickets.api_bank_ref!="",t_tickets.pay_date,t_tickets.date))';
}
if ($data[2] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= DATE(IF(t_tickets.api_bank_ref!="",t_tickets.pay_date,t_tickets.date))';
}
// Travel Date
if ($data[4] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[4])) . '" <= DATE(t_tickets.journey_date)';
}
if ($data[5] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[5])) . '" >= DATE(t_tickets.journey_date)';
}
// Filter Destination From
if ($data[6] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "t_tickets.t_destination_from_id = '" . $data[6]. "'";
}
// Filter Destination To
if ($data[7] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "t_tickets.t_destination_to_id = '" . $data[7]. "'";
}
if ($data[9] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $typeMulti = explode("-", $data[9]);
    if(!empty($typeMulti[1])){ // Multi Select
        $condition .= '(';
        foreach($typeMulti AS $key => $type){
            if($key > 0){
                $condition .= ' OR ';
            }
            if($type == "1"){ // App
                $condition .= "(t_tickets.t_agent_id IS NULL AND t_tickets.terminal_id IS NULL)";
            } else if($type == "2"){ // Website
                $condition .= "(t_tickets.t_agent_id = 55)";
            } else if($type == "3") { // Mini App
                $condition .= "(t_tickets.t_agent_id = 106)";
            } else if($type == "5") { // Phone Call Payment
                $condition .= "(t_tickets.api_bank_ref != '')";
            } else { // Terminal
                $condition .= "(t_tickets.terminal_id IS NOT NULL)";
            }
        }   
        $condition .= ')';
    } else {
        if($data[9] == "1"){ // App
            $condition .= "t_tickets.t_agent_id IS NULL AND t_tickets.terminal_id IS NULL";
        } else if($data[9] == "2"){ // Website
            $condition .= "t_tickets.t_agent_id = 55";
        } else if($data[9] == "3") { // Mini App
            $condition .= "t_tickets.t_agent_id = 106";
        } else if($data[9] == "5") { // Phone Call Payment
            $condition .= "t_tickets.api_bank_ref != ''";
        } else { // Terminal
            $condition .= "t_tickets.terminal_id IS NOT NULL";
        }
    }
}
if ($data[10] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "IFNULL(t_tickets.payment_method_id, online_orders.payment_method_id) IN (" . str_replace("-", ",",  $data[10]). ")";
}
if ($data[11] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "t_tickets.company_id IN (".str_replace("-", ",", $data[11]).")";
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
    $discount = 0;
    // if($aRow[16] > 0){ // Check total Order & Ticket
    //     if($aRow[16] > $aRow[17]){
    //         mysql_query("UPDATE t_tickets SET total_amount = ".$aRow[16]." WHERE id = ".$aRow[0]);
    //     }
    // }
    // if($aRow[14] == 0){
    //     if($aRow[15] > 0){
    //         $discount = $aRow[15];
    //         mysql_query("UPDATE t_tickets SET discount_amount = ".$aRow[15]." WHERE id = ".$aRow[0]);
    //     }
    // } else {
    //     if($aRow[15] > $aRow[14]){
    //         $discount = $aRow[15];
    //         mysql_query("UPDATE t_tickets SET discount_amount = ".$aRow[15]." WHERE id = ".$aRow[0]);
    //     }
    // }
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if ($aColumns[$i] == 't_tickets.created') {
            if ($aRow[$i] != '0000-00-00 00:00:00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y H:i:s");
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'IF(t_tickets.api_bank_ref!="",t_tickets.pay_date,t_tickets.date)') {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y");
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'CONCAT_WS(" ", t_tickets.journey_date,t_tickets.journey_time)') {
            if ($aRow[$i] != '0000-00-00 00:00:00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y H:i");
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0)') {
            $row[] = number_format(($aRow[$i] + $discount), 2)." ".$aRow[13];
        } else if ($aColumns[$i] == 't_tickets.t_agent_id') {
            if($aRow[19] != ''){
                $row[] = "Phone Call Payment";
            } else {
                if($aRow[18] > 0){
                    $row[] = "Terminal";
                } else {
                    if($aRow[$i] == 55){
                        $row[] = "Website";
                    } else if($aRow[$i] == 106){
                        $row[] = "Mini App";
                    } else {
                        $row[] = "App";
                    }
                }
            }
        } else if ($aColumns[$i] == 'currency_centers.symbol' || $aColumns[$i] == 'IFNULL(t_tickets.discount_amount, 0)' || $aColumns[$i] == 'IFNULL(online_orders.discount_amount, 0)' || $aColumns[$i] == 'IFNULL(online_orders.total_amount, 0)' || $aColumns[$i] == 'IFNULL(t_tickets.total_amount, 0)' || $aColumns[$i] == 't_tickets.terminal_id' || $aColumns[$i] == 't_tickets.api_bank_ref') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>