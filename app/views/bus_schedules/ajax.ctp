<?php
include("includes/function.php");
// Authentication
$this->element('check_access');
$allowUpdateBus  = checkAccess($user['User']['id'], $this->params['controller'], 'add');
$allowView       = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowDelete     = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowDelayTime  = checkAccess($user['User']['id'], $this->params['controller'], 'updateDelay');
$allowClose      = checkAccess($user['User']['id'], $this->params['controller'], 'closeSchedule');
$allowNote       = checkAccess($user['User']['id'], $this->params['controller'], 'updateNote');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('bus_schedules.id', 'bus_schedules.date', 'bus_schedules.departure', 't_destinations.name', 'destination_to.name', 'CONCAT(IFNULL(buses.code, " "), " (", buses.name, ")")', 'IFNULL(bus_schedules.delay_time, 0)', 'bus_schedules.left_date', 'bus_schedules.left_by', 'bus_schedules.status', 'bus_schedules.note');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "bus_schedules.id";

/* DB table to use */
$sTable = "bus_schedules
           INNER JOIN t_destinations ON t_destinations.id = bus_schedules.t_destination_from_id
           INNER JOIN t_destinations AS destination_to ON destination_to.id = bus_schedules.t_destination_to_id
           INNER JOIN buses ON buses.id = bus_schedules.bus_id";

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
    for ($i = 0; $i < count($aColumns) - 2; $i++) {
        if($aColumns[$i] == 'name'){
            $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
        }
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - 2; $i++) {
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
$condition = "bus_schedules.status > 0 AND bus_schedules.offline_project_id = 1";
if($user['User']['is_admin'] == 0){
    $condition .= " AND bus_schedules.t_destination_from_id = (SELECT t_destination_id FROM main_branches WHERE id = ".$user['User']['main_branch_id']." LIMIT 1)";
}
if($from != "all"){
    $condition .= " AND bus_schedules.t_destination_from_id = ".$from;
}
if($to != "all"){
    $condition .= " AND bus_schedules.t_destination_to_id = ".$to;
}
if($bus != "all"){
    $condition .= " AND bus_schedules.bus_id = ".$bus;
}
if($status != "all"){
    if($status == 1){
        $condition .= " AND DATE_ADD(bus_schedules.departure, INTERVAL IFNULL(bus_schedules.delay_time, 0) MINUTE) >= now()";
    } else {
        $condition .= " AND DATE_ADD(bus_schedules.departure, INTERVAL IFNULL(bus_schedules.delay_time, 0) MINUTE) < now()";
    }
}
if($date != "all"){
    $condition .= " AND bus_schedules.date = '".$date."'";
} else {
    $condition .= " AND bus_schedules.date = DATE(now())";
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
        } else if ($aColumns[$i] == 'bus_schedules.date') {
            $row[] = dateShort($aRow[$i]);
        } else if ($aColumns[$i] == 'IFNULL(bus_schedules.delay_time, 0)') {
            $row[] = $aRow[$i]." ".($allowDelayTime ? '<a href="#" class="btnUpdateDelayTimeBusSchedule" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="'.ACTION_UPDATE_DELAY.'" onmouseover="Tip(\'' . ACTION_UPDATE_DELAY . '\')" src="' . $this->webroot . 'img/button/alarm-icon.png" style="width: 16px;" /></a>' : '');
        } else if ($aColumns[$i] == 'CONCAT(IFNULL(buses.code, " "), " (", buses.name, ")")') {
            if($allowUpdateBus){
                $row[] = '<a href="#" rel="' . $aRow[0] . '" class="btnUpdateBusBusSchedule" style="color: blue;">'.$aRow[$i].'</a>';
            } else {
                $row[] = $aRow[$i];
            }
        } else if ($aColumns[$i] == 'bus_schedules.status') {
            if($aRow[$i] == 1){
                $row[] = '<button type="button" rel="' . $aRow[0] . '" data="' . $aRow[3] . ' - ' . $aRow[4] . ' (' . $aRow[2] . ')" class="BusScheduleLeave" style="width: 100px; height: 30px;">Leave</button> ';
            } else if($aRow[$i] == 3){   
                $row[] = '<span style="font-size: 14px; color: red;">Close</span>';
            } else {
                $row[] = '<span style="font-size: 14px; color: red;">Left</span>';
            }
        } else if ($aColumns[$i] == 'bus_schedules.left_date') {
            if($aRow[$i] != "" && $aRow[$i] != "0000-00-00 00:00:00"){
                $row[] = dateShort($aRow[$i], "d/m/Y H:i");
            } else {
                $row[] = "";
            }
        } else if ($aColumns[$i] == 'bus_schedules.left_by') {
            $routeId = "";
            $transportaionId = "";
            $sqlTransportation = mysql_query("SELECT t_journeys.id AS journey_id, t_journeys.t_route_id, t_journeys.t_transportation_type_id, t_journeys.type
                                              FROM t_journeys
                                              INNER JOIN bus_schedule_details ON bus_schedule_details.t_journey_id = t_journeys.id
                                              WHERE t_journeys.type IN (1,3) AND bus_schedule_details.bus_schedule_id = ".$aRow[0]." ORDER BY bus_schedule_details.id DESC LIMIT 1;");
            if(mysql_num_rows($sqlTransportation)){
                    $rowTransportation = mysql_fetch_array($sqlTransportation);
                    if($rowTransportation['type'] == 3){    
                        $sqlTransit = mysql_query("SELECT t_journeys.t_transportation_type_id, t_journeys.t_route_id, t_journeys.id AS journey_id, t_journeys.type
                                                FROM t_journeys
                                                INNER JOIN t_journey_transits ON t_journey_transits.t_journey_departure_id = t_journeys.id
                                                WHERE t_journey_transits.t_journey_id = ".$rowTransportation['journey_id']."
                                                GROUP BY t_journey_transits.t_journey_departure_id LIMIT 1;");
                        if(mysql_num_rows($sqlTransit)){
                            $rowTransit = mysql_fetch_array($sqlTransit);
                            $routeId = $rowTransit['t_route_id'];
                            $transportaionId = $rowTransit['t_transportation_type_id'];
                        }
                    } else {
                        $routeId = $rowTransportation['t_route_id'];
                        $transportaionId = $rowTransportation['t_transportation_type_id'];
                    }
            }
            if(!empty($routeId)){
                $totalSales = 0;
                $totalChecked = 0;
                $sqlSold = mysql_query("SELECT IF(t_seat_controls.is_pickup > 0, 1, 0) AS check_in
                                           FROM t_seat_controls
                                           INNER JOIN t_tickets ON t_tickets.id = t_seat_controls.t_ticket_id AND t_tickets.status = 2
                                           WHERE t_seat_controls.t_transportation_type_id = ".$transportaionId." AND t_seat_controls.t_route_id = ".$routeId." AND t_seat_controls.journey_date = DATE(now()) AND t_seat_controls.status IN (1,2,3);");
                while($rowSold = mysql_fetch_array($sqlSold)){
                    if($rowSold['check_in'] == 1){
                        $totalChecked++;
                    }
                    $totalSales++;
                }
                $row[] = $totalChecked."/".$totalSales;
            } else {
                $row[] = "";
            }
        } else if ($aColumns[$i] == 'bus_schedules.note') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] =
            ($allowView ? '<a href="" class="btnViewBusSchedule" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : ' ') .
            ($allowNote ? '<a href="#" class="btnUpdateNoteBusSchedule" rel="' . $aRow[0] . '" name="' . $aRow[1] . '" note="' . $aRow[10] . '"><img alt="'.ACTION_UPDATE_NOTE.'" onmouseover="Tip(\'' . ACTION_UPDATE_NOTE . '\')" src="' . $this->webroot . 'img/button/note.png" style="width: 16px;" /></a> ' : ' ') .
            ($allowClose && $aRow[9] != 3 ? '<a href="#" class="btnCloseBusSchedule" rel="' . $aRow[0] . '" name="' . $aRow[3] . ' ' . $aRow[4] . ' ' . $aRow[2] . '"><img alt="'.ACTION_CLOSE.'" onmouseover="Tip(\'' . ACTION_CLOSE . '\')" src="' . $this->webroot . 'img/button/stop.png" style="width: 16px;" /></a> ' : ' ') .
            ($allowDelete ? '<a href="" class="btnDeleteBusSchedule" rel="' . $aRow[0] . '" name="' . $aRow[3] . ' - ' . $aRow[4] . ' (' . $aRow[2] . ')"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>