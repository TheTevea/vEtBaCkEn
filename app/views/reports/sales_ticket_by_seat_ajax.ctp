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
    'branches.name',
    't_tickets.code',
    't_tickets.date', 
    't_tickets.journey_date',
    't_tickets.journey_time',
    'IFNULL(label_number, seat_number)',
    '(SELECT description FROM t_journeys WHERE id = t_tickets.t_journey_id)',
    't_tickets.telephone',
    'IFNULL((SELECT CONCAT_WS(first_name," ",last_name) FROM users WHERE id = t_tickets.confirm_by), (SELECT CONCAT_WS(first_name," ",last_name) FROM users WHERE id = t_tickets.created_by))',
    'IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0)',
    't_tickets.price_type',
    't_tickets.status',
    'currency_centers.symbol',
    't_tickets.type',
    't_tickets.is_open_date',
    't_tickets.t_agent_id');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "t_tickets.id";

/* DB table to use */
$sTable = "t_ticket_details 
           INNER JOIN t_tickets ON t_tickets.id = t_ticket_details.t_ticket_id 
           INNER JOIN branches ON branches.id = t_tickets.branch_id 
           INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id
           INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id";

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
    for ($i = 0; $i < count($aColumns) - 4; $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - 4; $i++) {
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
$condition = 't_tickets.status > 0 AND t_ticket_details.is_active = 1 AND t_tickets.offline_project_id = 1';
// Check Filter By Booking or Travel Date
if($data[3] == 1){
    $dateFilter = 't_tickets.date';
} else {
    $dateFilter = 't_tickets.journey_date';
}
if ($data[1] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= DATE('.$dateFilter.')';
}
if ($data[2] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= DATE('.$dateFilter.')';
}
// Filter Journey
if ($data[4] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 't_tickets.t_journey_id IN (' . $data[4].')';
}
// Filter Route Code
if ($data[5] != '') {
    $routeCode = str_replace("*",":",$data[5]);
    $routeCode = str_replace("]"," ",$routeCode);
    $routeCode = str_replace("[","-",$routeCode);
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 't_journeys.route_code = "' . $routeCode.'"';
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
$ticketId = 0;
$numberSeat = 1;
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    if($ticketId != $aRow[0]){
        $ticketId   = $aRow[0];
        $numberSeat = 1;
    }
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if ($aColumns[$i] == 't_tickets.date') {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y");
            } else {
                $row[] = 'Open Date';
            }
        } else if ($aColumns[$i] == 't_tickets.journey_date') {
            if ($aRow[15] == 1){
                $row[] = 'Open Date';
            } else {
                if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                    $row[] = dateShort($aRow[$i], "d/m/Y");
                } else {
                    $row[] = '';
                }
            }
        } else if ($aColumns[$i] == 't_tickets.journey_time') {
            if ($aRow[15] == 1){
                $row[] = 'Open Date';
            } else {
                $row[] = date("h:i A", strtotime($aRow[$i]));
            }
        } else if ($aColumns[$i] == 't_tickets.code') {
            $row[] = $aRow[$i]."-".$numberSeat;
        } else if ($aColumns[$i] == 't_tickets.status') {
            switch($aRow[$i]){
                case 0:
                    $row[] =  'Void';
                    break;
                case 1:
                    $row[] =  'Pending';
                    break;
                case 2:
                    $row[] =  'Completed';
                    break;
            }
        } else if ($aColumns[$i] == 't_tickets.price_type') {
            if($aRow[14] == 1){
                $row[] = 'Walk In';
            } else if($aRow[14] == 2){   
                $row[] = 'Phone Call';
            } else if($aRow[14] == 5){   
                if($aRow[16] == 55){
                    $row[] = 'Website';
                } else {
                    $row[] = 'App';
                }
            } else if($aRow[14] == 7){   
                $row[] = 'API';
            } else if($aRow[14] == 11){   
                $row[] = 'Mini App';
            } else {
                $row[] = 'Agency';
            }
        } else if ($aColumns[$i] == 'currency_centers.symbol' || $aColumns[$i] == 't_tickets.type' || $aColumns[$i] == 't_tickets.is_open_date' || $aColumns[$i] == 't_tickets.t_agent_id') {
        } else if ($aColumns[$i] == 'IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0)' || $aColumns[$i] == 't_tickets.balance') {
            $row[] = number_format($aRow[$i], 2)." ".$aRow[13];
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $numberSeat++;
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>