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
    't_ticket_details.id',
    'main_branches.name',
    't_tickets.date', 
    't_tickets.code',
    't_tickets.journey_date',
    't_tickets.journey_time',
    't_destinations.name',
    'destTo.name',
    't_tickets.telephone',
    't_ticket_details.label_number',
    'IFNULL(t_ticket_details.total_amount, 0) + IFNULL(t_ticket_details.vat_price, 0)',
    'CONCAT_WS(users.first_name," ",users.last_name)'
     );

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "t_ticket_details.id";

/* DB table to use */
$sTable = "t_ticket_details
           INNER JOIN t_tickets ON t_tickets.id = t_ticket_details.t_ticket_id
           INNER JOIN users ON users.id = t_tickets.created_by
           INNER JOIN main_branches ON main_branches.id = t_tickets.main_branch_id 
           INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id 
           INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
           INNER JOIN t_destinations AS destTo ON destTo.id = t_tickets.t_destination_to_id";

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
    $sOrder = "ORDER BY main_branches.name, t_tickets.date, t_tickets.code ASC";
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
$condition = 't_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND t_ticket_details.is_free = 1 AND t_ticket_details.is_active = 1';
// Booking Date
if ($data[1] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= DATE(t_tickets.date)';
}
if ($data[2] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= DATE(t_tickets.date)';
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
// Filter Company
$condition != '' ? $condition .= ' AND ' : '';
if ($data[6] != '') {
    $condition .= "t_tickets.company_id IN (" . $data[6]. ")";
} else {
    $condition .= "t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '" . $user['User']['id']. "')";
}
// Filter Main Branch
if ($data[7] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 't_tickets.main_branch_id =' . $data[7];
} else {
    if($user['User']['is_admin'] == 0){
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 't_tickets.main_branch_id = '.$user['User']['main_branch_id'];
    } else {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 't_tickets.main_branch_id > 0';
    }
}
// Filter Destination From
if ($data[8] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "t_tickets.t_destination_from_id = '" . $data[8]. "'";
}
// Filter Destination To
if ($data[9] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "t_tickets.t_destination_to_id = '" . $data[9]. "'";
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

$index = 0;
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            $row[] = ++$index;
        } else if ($aColumns[$i] == 't_tickets.date') {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y");
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 't_tickets.journey_date') {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y");
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 't_tickets.journey_time') {
            if ($aRow[$i] != '00:00:00' && $aRow[$i] != '') {
                $row[] = date("h:i A", strtotime($aRow[$i]));
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'IFNULL(t_ticket_details.total_amount, 0) + IFNULL(t_ticket_details.vat_price, 0)') {
            $row[] = number_format($aRow[$i], 2)." $";
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
    $tmpId = $aRow[1];
}

echo json_encode($output);
?>