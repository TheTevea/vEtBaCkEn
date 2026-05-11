<?php
include("includes/function.php");
// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
//$allowAddVehicle = checkAccess($user['User']['id'], $this->params['controller'], 'addVehicle');
//$allowFareEvent  = checkAccess($user['User']['id'], $this->params['controller'], 'fareEvent');
$allowUpdateStatus  = checkAccess($user['User']['id'], $this->params['controller'], 'updateStatus');
$allowChangeTransportation  = checkAccess($user['User']['id'], $this->params['controller'], 'changeTransportation');
// $allowCloneJourney  = checkAccess($user['User']['id'], $this->params['controller'], 'cloneJourney');
$allowAdd = checkAccess($user['User']['id'], $this->params['controller'], 'add');
$allowUndoVoid    = checkAccess($user['User']['id'], $this->params['controller'], 'undoVoid');
$allowPricePeriod = checkAccess($user['User']['id'], $this->params['controller'], 'updatePricePeriod');
$allowDeleteForever = checkAccess($user['User']['id'], $this->params['controller'], 'deleteForever');
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    't_journeys.id',
    't_journeys.description_kh', 
    't_destinations.name',
    'dest_to.name',
    't_departure_times.name',
    't_journeys.arrival', 
    't_routes.name',
    't_journeys.route_code',
    't_journeys.nation_road',
    'currency_centers.symbol',
    't_journeys.unit_price',
    't_journeys.foreigner_price',
    't_journeys.agent_price_amount',
    't_journeys.type',
    't_journeys.allow_access', // 13
    't_journeys.allow_price_period', // 14
    't_journeys.status',
    't_journeys.t_route_id',
    't_journeys.is_highlight',
    't_journeys.t_destination_from_id',
    't_journeys.t_destination_to_id',
    't_journeys.t_transportation_type_id',
    't_journeys.note');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "t_journeys.id";

/* DB table to use */
$sTable = "t_journeys 
           INNER JOIN t_routes ON t_routes.id = t_journeys.t_route_id 
           INNER JOIN t_departure_times ON t_departure_times.id = t_journeys.t_departure_time_id
           LEFT JOIN t_destinations ON t_destinations.id = t_journeys.t_destination_from_id
           LEFT JOIN t_destinations AS dest_to ON dest_to.id = t_journeys.t_destination_to_id
           LEFT JOIN currency_centers ON currency_centers.id = t_journeys.currency_center_id";

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
    for ($i = 0; $i < count($aColumns) - 6; $i++) {
        if($aColumns[$i] == 't_journeys.description_kh'){
            $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
        }
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - 6; $i++) {
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
$condition = "t_journeys.status >= 0 AND DATE(t_journeys.created) > '2021-11-01' AND t_journeys.offline_project_id = ".$user['User']['offline_project_id'];
if($user['User']['id'] == 963){ // User KimLay Bangkok
    $condition .= " AND t_journeys.company_id IN (1,2) AND t_journeys.branch_id IN (19, 28)";
} else {
    if($company != 'all'){
        $condition .= " AND t_journeys.company_id = ".$company;
    } else {
        $condition .= " AND t_journeys.company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
    }
    if($branch != 'all'){
        $condition .= " AND t_journeys.branch_id = ".$branch;
    } else {
        $condition .= " AND t_journeys.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
    }
}

if($from != 'all'){
    $condition .= " AND t_journeys.t_destination_from_id = ".$from;
}
if($to != 'all'){
    $condition .= " AND t_journeys.t_destination_to_id = ".$to;
}
if($status != 'all'){
    $condition .= " AND t_journeys.status IN (".$status.")";
}
if($type != 'all'){
    $condition .= " AND t_journeys.type = ".$type;
}
if($checked != 'all'){
    $condition .= " AND t_journeys.is_highlight = ".$checked;
}
if($filterMarkup != 'all'){
    if($filterMarkup == 1){
        $condition .= " AND '1' = IFNULL((SELECT '1' FROM t_journey_price_periods WHERE offline_project_id = 1 AND destination_from_id = t_journeys.t_destination_from_id AND destination_to_id = t_journeys.t_destination_to_id AND t_transportation_type_id = t_journeys.t_transportation_type_id AND start <= '".date("Y-m-d")."' AND end >= '".date("Y-m-d")."' AND status = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1), '0')";
    } else {
        $condition .= " AND '0' = IFNULL((SELECT '1' FROM t_journey_price_periods WHERE offline_project_id = 1 AND destination_from_id = t_journeys.t_destination_from_id AND destination_to_id = t_journeys.t_destination_to_id AND t_transportation_type_id = t_journeys.t_transportation_type_id AND start <= '".date("Y-m-d")."' AND end >= '".date("Y-m-d")."' AND status = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1), '0')";
    }
}
if($filterPricePeriod != 'all'){
    $condition .= " AND t_journeys.allow_price_period = ".$filterPricePeriod;
}
if(!empty($routeCode) && $routeCode != 'all'){
    $routeCode = str_replace("*",":",$routeCode);
    $routeCode = str_replace("]"," ",$routeCode);
    $condition .= " AND t_journeys.route_code LIKE '%".$routeCode."%'";
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
$groupBy = "GROUP BY t_journeys.id";
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

$index = $_GET['iDisplayStart'];
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    $markUp = 0;
    $price  = $aRow[10];
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if ($aColumns[$i] == 't_journeys.status') {
            if($aRow[$i] == 1){
                $row[] = 'Active';
            } else if($aRow[$i] == 2) {
                $row[] = 'Inactive';
            } else if($aRow[$i] == 0) {
                $row[] = 'Void';
            } else {
                $row[] = 'NULL';
            }
        } else if ($aColumns[$i] == 't_journeys.type') {
            if($aRow[$i] == 1){
                $row[] = 'Direct';
            } else if($aRow[$i] == 2) {
                $row[] = 'Transit';
            } else if($aRow[$i] == 3) {
                $row[] = 'Direct MR';
            } else {
                $row[] = 'NULL';
            }
        } else if ($aColumns[$i] == 't_departure_times.name' || $aColumns[$i] == 't_journeys.arrival') {
            $row[]   = date("h:i A", strtotime($aRow[$i]));
        } else if ($aColumns[$i] == 't_journeys.unit_price') {
            $row[] = number_format($aRow[$i], 2)." ".$aRow[9];
        } else if ($aColumns[$i] == 't_journeys.foreigner_price') {
            $priceAddOn = "";
            // Check Default Price
            $sqlPD = mysql_query("SELECT * FROM t_journey_price_defaults WHERE offline_project_id = 1 AND destination_from_id = ".$aRow[19]." AND destination_to_id = ".$aRow[20]." AND t_transportation_type_id = ".$aRow[21]." AND status = 1 AND main_branch_id = ".$user['User']['main_branch_id']." ORDER BY id DESC LIMIT 1");
            if(mysql_num_rows($sqlPD)){
                $rowPD = mysql_fetch_array($sqlPD);
                $markUp = $rowPD['price'];
                $priceAddOn = "(Fixed)";
            } else {
                $sqlPDA = mysql_query("SELECT * FROM t_journey_price_defaults WHERE offline_project_id = 1 AND destination_from_id = ".$aRow[19]." AND destination_to_id = ".$aRow[20]." AND t_transportation_type_id = ".$aRow[21]." AND status = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
                if(mysql_num_rows($sqlPDA)){
                    $rowPDA = mysql_fetch_array($sqlPDA);
                    $markUp  = $rowPDA['price'];
                    $priceAddOn = "(Fixed)";
                }
            }
            // Check Price in Period
            $sqlPA = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = 1 AND destination_from_id = ".$aRow[19]." AND destination_to_id = ".$aRow[20]." AND t_transportation_type_id = ".$aRow[21]." AND start <= '".date("Y-m-d")."' AND end >= '".date("Y-m-d")."' AND status = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
            if(mysql_num_rows($sqlPA)){
                $rowPAPrice = mysql_fetch_array($sqlPA);
                if($rowPAPrice['price_type'] == 1){
                    $markUp = $rowPAPrice['price'];
                    $priceAddOn = "(Fixed)";
                } else {
                    $markUp = $rowPAPrice['price'];
                    $priceAddOn = "(+)";
                }
            }
            if($priceAddOn == "(Fixed)"){
                $price = $markUp;
            } else {
                $price = $price + $markUp;
            }
            $row[] = number_format($markUp, 2)." ".$aRow[9].' <span style="color: blue;">'.$priceAddOn.'</span>';
        } else if ($aColumns[$i] == 't_journeys.agent_price_amount') {
            $row[] = number_format($price, 2)." ".$aRow[9];
        } else if ($aColumns[$i] == 'currency_centers.symbol' || $aColumns[$i] == 't_journeys.t_route_id' || $aColumns[$i]  == 't_journeys.t_destination_from_id' || $aColumns[$i]  == 't_journeys.t_destination_to_id' || $aColumns[$i]  == 't_journeys.t_transportation_type_id') {
        } else if ($aColumns[$i] == 't_journeys.allow_access') {  
            $status = $aRow[16];
            if($aRow[$i] == 0){
                $row[] = "Internal";
            } else if($aRow[$i] == 1){
                $row[] = "API";
            } else if($aRow[$i] == 2){
                $row[] = "Online";
            } else {
                $row[] = "Int, API, Online";
            }
        } else if ($aColumns[$i] == 't_journeys.is_highlight') {
            $checkbox = '';
            if($aRow[$i] == 1){
                $checkbox = ' checked=""';
            }
        } else if ($aColumns[$i] == 't_journeys.allow_price_period') {
            $btnAllowPricePeriod = "";
            if($allowPricePeriod && $aRow[16] > 0){
                if($aRow[$i] == 1){
                    $btnAllowPricePeriod = "btnUnAllowPricePeriodTJourney";
                } else {
                    $btnAllowPricePeriod = "btnAllowPricePeriodTJourney";
                }
            }
            $row[] = '<img alt="' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '" class="'.$btnAllowPricePeriod.'" onmouseover="Tip(\'' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '\')" rel="' . $aRow[0] . '" name="' . $aRow[3] . '" style="cursor: pointer;" src="' . $this->webroot . 'img/button/' . ($aRow[$i] == 1 ? 'active' : 'inactive') . '.png" />';
        
        } else if ($aColumns[$i] == 't_journeys.note') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    // $sqlTicket = mysql_query("SELECT id FROM t_tickets WHERE t_journey_id = ".$aRow[0]);
    $row[] =
            ('<input type="checkbox"'.$checkbox.' class="chkJourneyList" data="' . $aRow[0] . '" /> ').
            ($allowView ? '<a href="#" class="btnViewTJourney" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="' . ACTION_VIEW . '" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : ' ') .
            ($allowView && $status > 0 ? '<a href="#" class="btnUpdateNoteTJourney" rel="' . $aRow[0] . '" name="' . $aRow[1] . '" note="' . $aRow[22] . '"><img alt="'.ACTION_UPDATE_NOTE.'" onmouseover="Tip(\'' . ACTION_UPDATE_NOTE . '\')" src="' . $this->webroot . 'img/button/note.png" style="width: 16px;" /></a> ' : ' ') .
            ($allowAdd && $status > 0 ? '<a href="#" class="btnCloneTJourney" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="' . ACTION_CLONE . '" onmouseover="Tip(\'' . ACTION_CLONE . '\')" src="' . $this->webroot . 'img/button/clone.png" /></a> ' : ' ') .
            ($allowEdit && $status == 2 && SERVER_TYPE == 1 ? '<a href="#" class="btnEditTJourney" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="' . ACTION_EDIT . '" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : ' ') .
            // ($allowChangeTransportation && $status > 0 && SERVER_TYPE == 1 ? '<a href="#" class="btnUpdateTransportationTJourney" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="'.TABLE_CHANGE_TRANSPORTATION_TYPE.'" onmouseover="Tip(\'' . TABLE_CHANGE_TRANSPORTATION_TYPE . '\')" src="' . $this->webroot . 'img/button/bus-icon.png" style="width: 16px;" /></a> ' : ' ') .
            // ($allowCloneJourney && $status > 0 && SERVER_TYPE == 1 ? '<a href="#" class="btnCloneTJourney" rel="' . $aRow[16] . '" name="' . $aRow[5] . '"><img alt="'.TABLE_CLONE_JOURNEY.'" onmouseover="Tip(\'' . TABLE_CLONE_JOURNEY . '\')" src="' . $this->webroot . 'img/button/clone_journey.png" style="width: 16px;" /></a> ' : ' ') .
            ($allowUpdateStatus && $status > 0 && SERVER_TYPE == 1 ? '<a href="#" class="btnUpdateStatusTJourney" rel="' . $aRow[0] . '" name="' . $aRow[1] . '" status="' . $status . '"><img alt="'.TABLE_UPDATE_STATUS.'" onmouseover="Tip(\'' . TABLE_UPDATE_STATUS . '\')" src="' . $this->webroot . 'img/button/active.png" style="width: 16px;" /></a> ' : ' ') .
            ($allowUndoVoid && $status == 0 && SERVER_TYPE == 1 ? '<a href="#" class="btnUndoVoidTJourney" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="'.TABLE_UNDO_VOID.'" onmouseover="Tip(\'' . TABLE_UNDO_VOID . '\')" src="' . $this->webroot . 'img/button/return.png" style="width: 16px;" /></a> ' : ' ') .
            // ($allowDelete && $status == 1 && !mysql_num_rows($sqlTicket) && SERVER_TYPE == 1 ? '<a href="#" class="btnDeleteTJourney" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="' . ACTION_VOID . '" onmouseover="Tip(\'' . ACTION_VOID . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : ' ');
            ($allowDelete && $status == 2 && SERVER_TYPE == 1 ? '<a href="#" class="btnDeleteTJourney" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="' . ACTION_VOID . '" onmouseover="Tip(\'' . ACTION_VOID . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : ' ').
            ($allowDeleteForever && $status == 0 && SERVER_TYPE == 1 ? '<a href="#" class="btnDeleteForeverTJourney" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="' . ACTION_VOID . '" onmouseover="Tip(\'' . ACTION_VOID . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : ' ');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>