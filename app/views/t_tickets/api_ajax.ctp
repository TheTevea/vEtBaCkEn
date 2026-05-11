<?php
include("includes/function.php");
// Authentication
$this->element('check_access');
$allowView    = checkAccess($user['User']['id'], $this->params['controller'], 'view');
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
$timeNow = strtotime(date("H:i:s"));
$timeCompare = strtotime("04:00:59");
if($timeNow > $timeCompare){
    $dateTravel = date("Y-m-d");
} else {
    $dateTravel = date("Y-m-d", strtotime("-1 day", strtotime(date("Y-m-d"))));
}
/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    't_tickets.id',
    't_tickets.created', 
    't_tickets.code',
    't_tickets.agt_refer_code',
    'CONCAT(t_tickets.journey_date," ",IF(t_tickets.is_open_date=1,"00:00:00",t_tickets.journey_time))',
    't_journeys.description',
    't_tickets.telephone',
    'IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0)',
    'IFNULL(t_tickets.total_markup, 0)',
    't_tickets.balance',
    't_tickets.type',
    't_tickets.status',
    'currency_centers.symbol',
    't_tickets.is_special_seat',
    't_tickets.branch_id',
    'IFNULL(t_tickets.confirm_by, "")',
    'IFNULL(t_tickets.note, "")');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "t_tickets.id";

/* DB table to use */
$sTable = "t_tickets INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id";

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
    for ($i = 0; $i < (count($aColumns) - 5); $i++) {
        if($aColumns[$i]  == 't_tickets.code' || $aColumns[$i] == 't_tickets.agt_refer_code' || $aColumns[$i] == 't_tickets.telephone'){
            $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
        }
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < (count($aColumns) - 5); $i++) {
    if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        if($aColumns  == 't_tickets.code' || $aColumns == 't_tickets.agt_refer_code' || $aColumns == 't_tickets.telephone'){
            $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
        }
    }
}

/* Customize condition */
$condition = "t_tickets.`date` = '".date("Y-m-d")."' AND t_tickets.offline_project_id = 1 AND t_tickets.`status` = 2 AND t_tickets.`type` IN (5,7)";
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
        } else if ($aColumns[$i] == 't_tickets.created') {
            $row[] = dateShort($aRow[$i], "d/m/Y H:i:s");
        } else if ($aColumns[$i] == 'CONCAT(t_tickets.journey_date," ",IF(t_tickets.is_open_date=1,"00:00:00",t_tickets.journey_time))') {
            if($aRow[$i] != '' && $aRow[$i] != '0000-00-00 00:00:00'){
                $row[] = dateShort($aRow[$i], "d/m/Y h:i A");
                $isOpenDate = 0;
            } else {
                $isOpenDate = 1;
                $row[] = 'Open Date';
            }
        } else if ($aColumns[$i] == 'IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0)' || $aColumns[$i] == 't_tickets.balance' || $aColumns[$i] == 'IFNULL(t_tickets.total_markup, 0)') {
            $row[] = number_format($aRow[$i], 2)." ".$aRow[12] ;
        } else if ($aColumns[$i] == 't_tickets.type') {
            if($aRow[$i] == 1){
                $row[] = 'Walk In';
            } else if($aRow[$i] == 2){   
                $row[] = 'Phone Call';
            } else if($aRow[$i] == 5){   
                $row[] = 'Website/App';
            } else if($aRow[$i] == 7){   
                $row[] = 'API';
            } else {
                $row[] = 'Agency';
            }
        } else if ($aColumns[$i] == 't_tickets.status') {
            if($aRow[$i] == 0){
                $row[] = 'Void';
            } else if($aRow[$i] == 1){   
                $row[] = 'Pending';
            } else {
                $row[] = 'Completed';
            }
        } else if ($aColumns[$i] == 'currency_centers.symbol' || $aColumns[$i] == 't_tickets.is_special_seat' || $aColumns[$i] == 't_tickets.branch_id' || $aColumns[$i] == 'IFNULL(t_tickets.confirm_by, "")' || $aColumns[$i] == 'IFNULL(t_tickets.note, "")') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] =
            ($allowView ? '<a href="" class="btnViewTTicket" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>