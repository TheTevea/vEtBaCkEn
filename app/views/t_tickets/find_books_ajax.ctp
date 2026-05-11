<?php
include("includes/function.php");
// Authentication
$this->element('check_access');
$allowCancel = checkAccess($user['User']['id'], $this->params['controller'], 'cancelTicket');
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    't_tickets.id',
    't_tickets.date', 
    't_tickets.code',
    'CONCAT(t_tickets.journey_date," ",t_tickets.journey_time)',
    '(SELECT name FROM t_destinations WHERE id = t_tickets.t_destination_from_id)',
    '(SELECT name FROM t_destinations WHERE id = t_tickets.t_destination_to_id)',
    't_tickets.telephone',
    't_tickets.branch_id',
    't_tickets.t_journey_id',
    't_tickets.journey_date');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "t_tickets.id";

/* DB table to use */
$sTable = "t_tickets INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id";

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
    // for ($i = 0; $i < (count($aColumns) - 3); $i++) {
    //     $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    // }
    $sWhere .= "t_tickets.code LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    $sWhere .= "t_tickets.telephone LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < (count($aColumns) - 3); $i++) {
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
$condition = "t_tickets.status = 1 AND t_tickets.offline_project_id = ".$user['User']['offline_project_id']." AND journey_date >= '".date("Y-m-d")."'";
if($user['User']['is_admin'] == 0){
    $condition .= " AND t_tickets.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
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
$timeNow = strtotime(date("Y-m-d H:i:s"));
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    if($aRow[3] == '0000-00-00 00:00:00' || $aRow[3] == ''){
        $timeJourney = strtotime(date('Y-m-d H:i:s', strtotime("+1 days")));
    } else {
        $dateJourney = explode(" ", $aRow[3]);
        $departure   = explode(":", $dateJourney[1]);
        $departureTime = (int) $departure;
        if(checkDateFrom($aRow[7], $departureTime) == 0) {
            $timeJourney = strtotime("+1 day", strtotime($aRow[3]));
        } else {
            $timeJourney = strtotime($aRow[3]);
        }
        $timeJourney = strtotime('+2 hour', $timeJourney);
    }
    if($timeNow < $timeJourney){
        for ($i = 0; $i < count($aColumns); $i++) {
            if ($i == 0) {
                /* Special output formatting */
                $row[] = ++$index;
            } else if ($aColumns[$i] == 't_tickets.date') {
                $row[] = dateShort($aRow[$i]);
            } else if ($aColumns[$i] == 'CONCAT(t_tickets.journey_date," ",t_tickets.journey_time)') {
                if($aRow[$i] != '' && $aRow[$i] != '0000-00-00 00:00:00'){
                    $row[] = dateShort($aRow[$i], "d/m/Y H:i A");
                    $departure = dateShort($aRow[$i], "d/m/Y H:i A");
                } else {
                    $row[] = '';
                    $departure = '';
                }
            } else if ($aColumns[$i] == 't_tickets.branch_id' || $aColumns[$i] == 't_tickets.t_journey_id' || $aColumns[$i] == 't_tickets.journey_date') {
            } else if ($aColumns[$i] != ' ') {
                /* General output */
                $row[] = $aRow[$i];
            }
        }
        // Get Title
        $sqlJourney = mysql_query("SELECT t_journeys.description, t_journeys.t_departure_time_id, t_transportation_types.name AS tran_type FROM t_journeys INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id INNER JOIN t_transportation_types ON t_transportation_types.id = t_journeys.t_transportation_type_id WHERE t_journeys.id = ".$aRow[8]);
        $rowJourney = mysql_fetch_array($sqlJourney);
        $title = $rowJourney['description'].' ('.$departure.') - '.$rowJourney['tran_type'];
        $row[] =
                '<a href="" class="btnComfirmTTicket" ticket="' . $aRow[0] . '" j-id="' . $aRow[8] . '" t-id="'.$rowJourney['t_departure_time_id'].'" is-return="0" act="'.$title.'" date="'.$aRow[9].'"><img alt="Confirm" onmouseover="Tip(\'' . ACTION_CONFIRM . '\')" src="' . $this->webroot . 'img/button/ticket-icon.png" style="width: 20px;" /></a> '.
                ($allowCancel ? '<a href="" class="btnCancelFindBooksTTicket" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Cancel" onmouseover="Tip(\'' . ACTION_CANCEL . '\')" src="' . $this->webroot . 'img/button/void.png" /></a> ' : '');
    
        $output['aaData'][] = $row;
    }
}

echo json_encode($output);
?>