<?php
include("includes/function.php");
// Authentication
$this->element('check_access');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printSchedule');
$allowBlockSeat = checkAccess($user['User']['id'], $this->params['controller'], 'blockSchedule');
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    't_journeys.id',
    'branches.name',
    't_journeys.t_transportation_type_id', 
    'CONCAT_WS(" ",t_journeys.description,t_journeys.vehicle_no)',
    't_departure_times.name',
    't_transportation_types.name',
    't_journeys.route_code',
    't_transportation_types.number_of_seat',
    't_journeys.type',
    't_journeys.t_departure_time_id',
    't_transportation_types.id',
    't_journeys.discount_amt',
    't_journeys.is_default',
    't_journeys.status',
    't_journeys.t_route_id');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "t_journeys.id";

/* DB table to use */
$sTable = "t_journeys INNER JOIN branches ON branches.id = t_journeys.branch_id INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id INNER JOIN t_transportation_types ON t_transportation_types.id = t_journeys.t_transportation_type_id";

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
    for ($i = 0; $i < count($aColumns) - 1; $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
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
$condition = "t_journeys.offline_project_id = 1";
if($companyId != 'all'){
    $condition .= " AND t_journeys.company_id =".$companyId;
} else {
    $condition .= " AND t_journeys.company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
}
if($branchId != 'all'){
    $condition .= " AND t_journeys.branch_id =".$branchId;
}
if($from != 'all'){
    $condition .= " AND t_journeys.t_destination_from_id =".$from;
}
if($to != 'all'){
    $condition .= " AND t_journeys.t_destination_to_id =".$to;
}
if($time != 'all'){
    $condition .= " AND t_departure_times.id =".$time;
}
if($routeCode != 'all'){
    $routeCode = str_replace("*",":",$routeCode);
    $routeCode = str_replace("]"," ",$routeCode);
    $condition .= " AND t_journeys.route_code = '".$routeCode."'";
}
if($status != 'all'){
    $condition .= " AND t_journeys.status =".$status;
} else {
    $condition .= " AND t_journeys.status = 1";
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
//$groupBy = "GROUP BY t_journeys.t_boat_id, t_journey_departures.t_departure_time_id";
$groupBy = "";
$sQuery = "
        SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
        FROM   $sTable
        $sWhere
        $groupBy
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
if($date == ''){
    $date = date("Y-m-d");
}
$index = $_GET['iDisplayStart'];
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    $scanned = 0;
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if ($aColumns[$i] == 't_journeys.t_transportation_type_id') {
            $row[] = dateShort($date);
        } else if ($aColumns[$i] == 't_transportation_types.id') {
        } else if ($aColumns[$i] == 't_departure_times.name') {
            $row[]   = date("h:i A", strtotime($aRow[$i]));
        } else if ($aColumns[$i] == 't_transportation_types.number_of_seat') {
            $row[]   = number_format($aRow[$i], 0);
        } else if ($aColumns[$i] == 't_journeys.type') {
            if($aRow[$i] == 1){
                $row[]   = 'Direct';
            } else if($aRow[$i] == 3){
                $row[]   = 'Direct MR';
            } else {
                $row[]   = 'Transit';
            }
        } else if ($aColumns[$i] == 't_journeys.t_departure_time_id') {
            $totalBooked = 0;
            $travelDate  = $date;
            if($aRow[8] == 3){ // Direct/Transit
                $seatBooked = array();
                $sqlTransit = mysql_query("SELECT t_transportation_type_id, t_route_id, t_journeys.id AS journey_id, t_journey_transits.is_next_day 
                                       FROM t_journeys 
                                       INNER JOIN t_journey_transits ON t_journey_transits.t_journey_departure_id = t_journeys.id
                                       WHERE t_journey_transits.t_journey_id = ".$aRow[0]." 
                                       GROUP BY t_journey_departure_id");
                while($rowTransit = mysql_fetch_array($sqlTransit)){
                    if($rowTransit['is_next_day'] == 1){
                        $travelDate = date("Y-m-d", strtotime("+1 day", strtotime($date)));
                    }
                    // Get Seat Booked
                    $sqlSeat = mysql_query("SELECT seat_number, is_pickup FROM t_seat_controls WHERE t_transportation_type_id = ".$rowTransit['t_transportation_type_id']." AND t_route_id = ".$rowTransit['t_route_id']." AND journey_date = '".$travelDate."' AND status IN (1,2,3)");
                    while($rowSeat = mysql_fetch_array($sqlSeat)){
                        if (!array_key_exists($rowSeat['seat_number'], $seatBooked)) {
                            $seatBooked[$rowSeat['seat_number']] = $rowSeat['seat_number'];
                            if($rowSeat['is_pickup'] > 0){
                                $scanned += 1;
                            }
                            $totalBooked++;
                        }
                    }
                }
            } else if($aRow[8] == 1){
                $sqlSeat = mysql_query("SELECT id, is_pickup FROM t_seat_controls WHERE t_transportation_type_id = ".$aRow[10]." AND t_route_id = ".$aRow[14]." AND journey_date = '".$travelDate."' AND status IN (1,2,3)");
                while($rowSeat = mysql_fetch_array($sqlSeat)){
                    if($rowSeat['is_pickup'] > 0){
                        $scanned += 1;
                    }
                    $totalBooked++;
                }
            } else {
                $con = ' AND t_journey_id = '.$aRow[0].' AND (t_journey_transit_id IS NULL OR t_journey_transit_id = "")';
                if($aRow[8] == 2){
                    $con = ' AND t_journey_transit_id = '.$aRow[0].' GROUP BY t_journey_transit_id';
                }
                $sqlCus = mysql_query("(SELECT COUNT(id) FROM t_ticket_details WHERE t_ticket_id IN (SELECT id FROM t_tickets WHERE t_tickets.t_route_id = ".$aRow[13]." AND t_tickets.t_transportation_type_id = ".$aRow[2]." AND t_tickets.status > 0 AND t_tickets.journey_date = '".$date."'".$con."))");
                $rowCus = mysql_fetch_array($sqlCus);
                $totalBooked = $rowCus[0];
            }
            $row[]   = number_format($totalBooked, 0);
        } else if ($aColumns[$i] == 't_journeys.discount_amt') {
            $sqlChkJouBus = mysql_query("SELECT bus_schedule_details.id 
                                         FROM bus_schedule_details 
                                         INNER JOIN bus_schedules ON bus_schedules.id = bus_schedule_details.bus_schedule_id
                                         WHERE bus_schedule_details.t_journey_id = ".$aRow[0]." AND bus_schedules.status > 0 AND bus_schedules.offline_project_id = 1 AND bus_schedules.date = '".$date."' LIMIT 1");
            if(mysql_num_rows($sqlChkJouBus)){
                $row[] = '<img src="' . $this->webroot . 'img/button/active.png" />';
            } else {
                $row[] = '<img src="' . $this->webroot . 'img/button/delete.png" />';
            }
        } else if ($aColumns[$i] == 't_journeys.is_default') {
            $row[] = $scanned;
        } else if ($aColumns[$i] == 't_journeys.status') {
            if($aRow[$i] == 1){
                $row[] = "Active";
            } else {
                $row[] = "Inactive";
            }
        } else if ($aColumns[$i] == 't_journeys.t_route_id') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] =
            '<a href="" class="btnViewSchedule" rel="'.$aRow[0].'"><img alt="'.ACTION_VIEW.'" onmouseover="Tip(\''.ACTION_VIEW.'\')" src="' . $this->webroot . 'img/button/view.png" /></a> '.
            ($allowPrint && $user['User']['type'] != 1 ? '<a href="" class="btnPrintSchedule" rel="'.$aRow[0].'"><img alt="'.ACTION_PRINT.'" onmouseover="Tip(\''.ACTION_PRINT.'\')" src="' . $this->webroot . 'img/button/printer.png" /></a> ' : ' ').
            ($allowBlockSeat && $user['User']['type'] != 1 ? '<a href="" class="btnBlockSeatSchedule" rel="'.$aRow[0].'"><img alt="'.ACTION_BLOCK.'" onmouseover="Tip(\''.ACTION_BLOCK.'\')" src="' . $this->webroot . 'img/button/stop.png" /></a> ' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>