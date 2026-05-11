<?php
include("includes/function.php");
// Authentication
$this->element('check_access');
$allowView    = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowReprint = checkAccess($user['User']['id'], $this->params['controller'], 'reprint');
$allowEdit    = checkAccess($user['User']['id'], $this->params['controller'], 'editOpen');
$allowDelete  = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowCancel  = checkAccess($user['User']['id'], $this->params['controller'], 'cancelTicket');
$allowViewByUser = checkAccess($user['User']['id'], $this->params['controller'], 'viewByUser');
$allowFullDelete = checkAccess($user['User']['id'], $this->params['controller'], 'fullDelete');
$allowPrintLucky = checkAccess($user['User']['id'], $this->params['controller'], 'printLucky');
$allowAddLucky   = checkAccess($user['User']['id'], $this->params['controller'], 'addLuckyTicket');
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

$condFilter = "t_tickets.offline_project_id = 1";
if($isOpen != 'all'){
    $condFilter .= " AND t_tickets.is_open_date = 1";
}
if($status != 'all'){
    $condFilter .= " AND t_tickets.`status` = ".$status;
} else {
    $condFilter .= " AND t_tickets.`status` >= -1";
}
if($show == 1){
    $condFilter .= " AND t_tickets.`date` = '".$dateTravel."'";
} else {
    if($date != 'all'){
        $condFilter .= " AND t_tickets.`date` = '".$date."'";
    } else {
        if(empty($search)){
            $date7Day = date("Y-m-d", strtotime("-3 day", strtotime(date("Y-m-d"))));
            $condFilter .= " AND t_tickets.`date` >= '".$date7Day."'";
        }
    }
}
if($user['User']['type'] == 2){
    //$condFilter .= " AND (t_tickets.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].") OR t_tickets.main_branch_id = ".$user['User']['main_branch_id'].")";
} else {
    $condFilter .= " AND t_tickets.created_by = ".$user['User']['id']." AND t_tickets.t_agent_id IN (SELECT id FROM t_agents WHERE user_id = ".$user['User']['id'].")";
}

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    't_tickets.id',
    't_tickets.created', 
    't_tickets.code',
    'IF(online_orders.code != "", online_orders.code, IF(online_order_2025s.code != "", online_order_2025s.code, IF(t_tickets.api_bank_ref != "", t_tickets.api_bank_ref, "")))',
    'IFNULL(t_tickets.agt_refer_code, "")',
    'CONCAT(t_tickets.journey_date," ",IF(t_tickets.is_open_date=1,"00:00:00",t_tickets.journey_time))',
    't_journeys.description',
    't_tickets.telephone',
    't_tickets.email',
    'IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) - IFNULL(t_tickets.coupon_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0) + IFNULL(t_tickets.total_markup, 0)',
    't_tickets.balance',
    'travel_packages.name',
    't_tickets.type',
    't_tickets.is_boarding',
    't_tickets.status',
    'currency_centers.symbol',
    't_tickets.is_special_seat',
    't_tickets.branch_id',
    'IFNULL(t_tickets.confirm_by, "")',
    'IFNULL(t_tickets.note, "")',
    't_journeys.company_id',
    't_tickets.t_agent_id',
    'IF(t_tickets.lucky_draw_fee > 0 && t_tickets.total_print_lucky = 0, 1, 0)',
    't_tickets.is_change',
    't_tickets.lucky_draw_fee');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "t_tickets.id";

/* DB table to use */
if($search != ""){
    
    // $sTable = "
    //        (
    //         SELECT * FROM t_tickets WHERE ".$condFilter." AND code = '".$search."' AND online_order_id IS NULL
    //         UNION ALL
    //         SELECT * FROM t_tickets WHERE ".$condFilter." AND telephone = '".$search."' AND online_order_id IS NULL
    //         UNION ALL
    //         SELECT * FROM t_tickets WHERE ".$condFilter." AND agt_refer_code = '".$search."' AND online_order_id IS NULL
    //         UNION ALL
    //         SELECT * FROM t_tickets WHERE ".$condFilter." AND api_bank_ref = '".$search."' AND online_order_id IS NULL
    //         UNION ALL
    //         SELECT t_tickets.* FROM t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE ".$condFilter." AND t_tickets.code = '".$search."'
    //         UNION ALL
    //         SELECT t_tickets.* FROM t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE ".$condFilter." AND t_tickets.telephone = '".$search."'
    //         UNION ALL
    //         SELECT t_tickets.* FROM t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE ".$condFilter." AND t_tickets.email = '".$search."'
    //         UNION ALL
    //         SELECT t_tickets.* FROM t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE ".$condFilter." AND t_tickets.agt_refer_code = '".$search."'
    //         UNION ALL
    //         SELECT t_tickets.* FROM t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE ".$condFilter." AND t_tickets.api_bank_ref = '".$search."'
    //         UNION ALL
    //         SELECT t_tickets.* FROM t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE ".$condFilter." AND online_orders.code = '".$search."'
    //         UNION ALL
    //         SELECT * FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND code = '".$search."' AND online_order_id IS NULL
    //         UNION ALL
    //         SELECT * FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND telephone = '".$search."' AND online_order_id IS NULL
    //         UNION ALL
    //         SELECT * FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND agt_refer_code = '".$search."' AND online_order_id IS NULL
    //         UNION ALL
    //         SELECT * FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND api_bank_ref = '".$search."' AND online_order_id IS NULL
    //         UNION ALL
    //         SELECT t_tickets.* FROM t_ticket_3months AS t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE ".$condFilter." AND t_tickets.code = '".$search."'
    //         UNION ALL
    //         SELECT t_tickets.* FROM t_ticket_3months AS t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE ".$condFilter." AND t_tickets.telephone = '".$search."'
    //         UNION ALL
    //         SELECT t_tickets.* FROM t_ticket_3months AS t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE ".$condFilter." AND t_tickets.email = '".$search."'
    //         UNION ALL
    //         SELECT t_tickets.* FROM t_ticket_3months AS t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE ".$condFilter." AND t_tickets.agt_refer_code = '".$search."'
    //         UNION ALL
    //         SELECT t_tickets.* FROM t_ticket_3months AS t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE ".$condFilter." AND t_tickets.api_bank_ref = '".$search."'
    //         UNION ALL
    //         SELECT t_tickets.* FROM t_ticket_3months AS t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE ".$condFilter." AND online_orders.code = '".$search."'
    //        ) AS t_tickets 
    //        INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id 
    //        INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id
    //        LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id
    //        LEFT JOIN travel_package_orders ON travel_package_orders.id = t_tickets.travel_package_order_id 
    //        LEFT JOIN travel_packages ON travel_packages.id = travel_package_orders.travel_package_id";
    $sTable = "
           (
            SELECT * FROM t_tickets WHERE ".$condFilter." AND code = '".$search."' AND online_order_id IS NULL
            UNION ALL
            SELECT * FROM t_tickets WHERE ".$condFilter." AND telephone = '".$search."' AND online_order_id IS NULL
            UNION ALL
            SELECT * FROM t_tickets WHERE ".$condFilter." AND agt_refer_code = '".$search."' AND online_order_id IS NULL
            UNION ALL
            SELECT * FROM t_tickets WHERE ".$condFilter." AND api_bank_ref = '".$search."' AND online_order_id IS NULL
            UNION ALL
            SELECT t_tickets.* FROM t_tickets WHERE ".$condFilter." AND t_tickets.code = '".$search."' AND online_order_id IS NOT NULL
            UNION ALL
            SELECT t_tickets.* FROM t_tickets WHERE ".$condFilter." AND t_tickets.telephone = '".$search."' AND online_order_id IS NOT NULL
            UNION ALL
            SELECT t_tickets.* FROM t_tickets WHERE ".$condFilter." AND t_tickets.email = '".$search."' AND online_order_id IS NOT NULL
            UNION ALL
            SELECT t_tickets.* FROM t_tickets WHERE ".$condFilter." AND t_tickets.agt_refer_code = '".$search."' AND online_order_id IS NOT NULL
            UNION ALL
            SELECT t_tickets.* FROM t_tickets WHERE ".$condFilter." AND t_tickets.api_bank_ref = '".$search."' AND online_order_id IS NOT NULL
            UNION ALL
            SELECT t_tickets.* FROM t_tickets 
            INNER JOIN 
            (
                SELECT * FROM online_orders WHERE status = 4 AND code = '".$search."'
                UNION ALL
                SELECT * FROM online_order_2025s WHERE status = 4 AND code = '".$search."'
            ) AS online_orders ON online_orders.id = t_tickets.online_order_id WHERE ".$condFilter."
            UNION ALL
            SELECT * FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND code = '".$search."' AND online_order_id IS NULL
            UNION ALL
            SELECT * FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND telephone = '".$search."' AND online_order_id IS NULL
            UNION ALL
            SELECT * FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND agt_refer_code = '".$search."' AND online_order_id IS NULL
            UNION ALL
            SELECT * FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND api_bank_ref = '".$search."' AND online_order_id IS NULL
            UNION ALL
            SELECT t_tickets.* FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND t_tickets.code = '".$search."' AND online_order_id IS NOT NULL
            UNION ALL
            SELECT t_tickets.* FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND t_tickets.telephone = '".$search."' AND online_order_id IS NOT NULL
            UNION ALL
            SELECT t_tickets.* FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND t_tickets.email = '".$search."' AND online_order_id IS NOT NULL
            UNION ALL
            SELECT t_tickets.* FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND t_tickets.agt_refer_code = '".$search."' AND online_order_id IS NOT NULL
            UNION ALL
            SELECT t_tickets.* FROM t_ticket_3months AS t_tickets WHERE ".$condFilter." AND t_tickets.api_bank_ref = '".$search."' AND online_order_id IS NOT NULL
            UNION ALL
            SELECT t_tickets.* FROM t_ticket_3months AS t_tickets
            INNER JOIN 
            (
                SELECT * FROM online_orders WHERE status = 4 AND code = '".$search."'
                UNION ALL
                SELECT * FROM online_order_2025s WHERE status = 4 AND code = '".$search."'
            ) AS online_orders ON online_orders.id = t_tickets.online_order_id WHERE ".$condFilter."
           ) AS t_tickets 
           INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id 
           INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id
           LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id
           LEFT JOIN online_order_2025s ON online_order_2025s.id = t_tickets.online_order_id
           LEFT JOIN travel_package_orders ON travel_package_orders.id = t_tickets.travel_package_order_id 
           LEFT JOIN travel_packages ON travel_packages.id = travel_package_orders.travel_package_id";
} else {
    $sTable = "
           (
            SELECT * FROM t_tickets WHERE ".$condFilter."
            UNION ALL
            SELECT * FROM t_ticket_3months AS t_tickets WHERE ".$condFilter."
           ) AS t_tickets 
            INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id 
            INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id
            LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id
            LEFT JOIN online_order_2025s ON online_order_2025s.id = t_tickets.online_order_id
            LEFT JOIN travel_package_orders ON travel_package_orders.id = t_tickets.travel_package_order_id 
            LEFT JOIN travel_packages ON travel_packages.id = travel_package_orders.travel_package_id";
}

// if($search != ""){
//     $sTable = "
//            (
//             SELECT * FROM t_tickets WHERE offline_project_id = 1 AND status >= 0 AND code = '".$search."' AND online_order_id IS NULL
//             UNION ALL
//             SELECT * FROM t_tickets WHERE offline_project_id = 1 AND status >= 0 AND telephone = '".$search."' AND online_order_id IS NULL
//             UNION ALL
//             SELECT * FROM t_tickets WHERE offline_project_id = 1 AND status >= 0 AND agt_refer_code = '".$search."' AND online_order_id IS NULL
//             UNION ALL
//             SELECT t_tickets.* FROM t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE t_tickets.offline_project_id = 1 AND t_tickets.status >= 0 AND t_tickets.code = '".$search."'
//             UNION ALL
//             SELECT t_tickets.* FROM t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE t_tickets.offline_project_id = 1 AND t_tickets.status >= 0 AND t_tickets.telephone = '".$search."'
//             UNION ALL
//             SELECT t_tickets.* FROM t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE t_tickets.offline_project_id = 1 AND t_tickets.status >= 0 AND t_tickets.email = '".$search."'
//             UNION ALL
//             SELECT t_tickets.* FROM t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE t_tickets.offline_project_id = 1 AND t_tickets.status >= 0 AND t_tickets.agt_refer_code = '".$search."'
//             UNION ALL
//             SELECT t_tickets.* FROM t_tickets INNER JOIN online_orders ON online_orders.id = t_tickets.online_order_id WHERE t_tickets.offline_project_id = 1 AND t_tickets.status >= 0 AND online_orders.code = '".$search."'
//            ) AS t_tickets 
//            INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id 
//            INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id
//            LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id";
// } else {
//     $sTable = "
//            t_tickets 
//            INNER JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id 
//            INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id
//            LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id";
// }

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
    for ($i = 0; $i < (count($aColumns) - 9); $i++) {
        if ($aColumns[$i] == 't_tickets.telephone') {
            // Compare stored telephone (spaces stripped) against search value (spaces stripped)
            $sWhere .= "REPLACE(" . $aColumns[$i] . ", ' ', '') LIKE '%" . $sSearchNoSpace . "%' OR ";
        } else if ($aColumns[$i] == 't_tickets.code' || $aColumns[$i] == 'IF(online_orders.code != "", online_orders.code, IF(t_tickets.api_bank_ref != "", t_tickets.api_bank_ref, ""))' || $aColumns[$i] == 'IFNULL(t_tickets.agt_refer_code, "")' || $aColumns[$i] == 't_tickets.email') {
            $sWhere .= $aColumns[$i] . " LIKE '%" . $sSearchRaw . "%' OR ";
        }
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < (count($aColumns) - 9); $i++) {
    if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        if($aColumns  == 't_tickets.code' || $aColumns[$i]  == 'IF(online_orders.code != "", online_orders.code, IF(t_tickets.api_bank_ref != "", t_tickets.api_bank_ref, ""))' || $aColumns == 'IFNULL(t_tickets.agt_refer_code, "")' || $aColumns == 't_tickets.telephone'){
            $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
        }
    }
}

/* Customize condition */
$condition = "1";
// if($user['User']['type'] == 2){
//     $condition = "t_tickets.status >= 0 AND (t_tickets.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].") OR t_tickets.main_branch_id = ".$user['User']['main_branch_id'].")";
// } else {
//     $condition = "t_tickets.status >= 0 AND t_tickets.created_by = ".$user['User']['id'];
// }

// if($user['User']['type'] != 1){
//     $condition .= " AND t_tickets.offline_project_id = ".$user['User']['offline_project_id'];
//     if($user['User']['type'] != 2){
//         $condition .= " AND t_tickets.t_agent_id IN (SELECT id FROM t_agents WHERE user_id = ".$user['User']['id'].")";
//     }
// }
// if($isOpen != 'all'){
//     $condition .= " AND t_tickets.is_open_date = 1";
// }
// if($status != 'all'){
//     $condition .= " AND t_tickets.status = ".$status;
// }
// if($show == 1){
//     $condition .= " AND t_tickets.date = '".$dateTravel."'";
// } else {
//     if($date != 'all'){
//         $condition .= " AND t_tickets.date = '".$date."'";
//     }
// }

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
        } else if ($aColumns[$i] == 'IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) - IFNULL(t_tickets.coupon_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0) + IFNULL(t_tickets.total_markup, 0)') {
            $row[] = number_format($aRow[$i], 2)." ".$aRow[15];
        } else if ($aColumns[$i] == 't_tickets.balance') {
            if($aRow[$i] > 0){
                if($aRow[12] == 2){ // Phone Call
                    $row[] = 'Unpaid';
                } else {
                    $row[] = 'Paid';
                }
            } else {
                $row[] = 'Paid';
            }
        } else if ($aColumns[$i] == 'IFNULL(t_tickets.agt_refer_code, "")') {
            if($aRow[12] != 5 && $aRow[12] != 11){
                $row[] = $aRow[$i];
            } else {
                $row[] = "";
            }
        } else if ($aColumns[$i] == 't_tickets.email') {
            if($aRow[$i] != 'user@gmail.com' && $aRow[$i] != 'minapp@gmail.com' && $aRow[$i] != 'miniappV2.30@gmail.com'){
                $row[] = $aRow[$i];
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 't_tickets.type') {
            if($aRow[$i] == 1){
                $row[] = 'Walk In';
            } else if($aRow[$i] == 2){   
                $row[] = 'Phone Call';
            } else if($aRow[$i] == 5){   
                if($aRow[21] == 55){
                    $row[] = 'Website';
                } else {
                    $row[] = 'App';
                }
            } else if($aRow[$i] == 7){   
                $row[] = 'API';
            } else if($aRow[$i] == 11){   
                $row[] = 'Mini App';
            } else {
                $row[] = 'Agency';
            }
        } else if ($aColumns[$i] == 't_tickets.status') {
            if($aRow[$i] == 0){
                $row[] = 'Void';
            } else if($aRow[$i] == -1){
                $row[] = 'Cancelled';
            } else {
                if($aRow[23] == 1){
                    $row[] = 'Changed';
                } else {
                    $row[] = 'Active';
                }
            }
        } else if ($aColumns[$i] == 't_tickets.is_boarding') {
            if($aRow[$i] == 1){
                $row[] = 'Scaned';
            } else {
                if($isOpenDate == 1){
                    $row[] = 'Pending';
                } else {
                    if(strtotime($aRow[5]) < strtotime(date("Y-m-d H:i:s"))){ // Departure < Date Now
                        $row[] = 'Un-Scaned';
                    } else {
                        $row[] = 'Pending';
                    }
                }
            }
        } else if ($aColumns[$i] == 'currency_centers.symbol' || $aColumns[$i] == 't_tickets.is_special_seat' || $aColumns[$i] == 't_tickets.branch_id' || $aColumns[$i] == 'IFNULL(t_tickets.confirm_by, "")' || $aColumns[$i] == 'IFNULL(t_tickets.note, "")' || $aColumns[$i] == 't_journeys.company_id' || $aColumns[$i] == 't_tickets.t_agent_id' || $aColumns[$i] == 'IF(t_tickets.lucky_draw_fee > 0 && t_tickets.total_print_lucky = 0, 1, 0)' || $aColumns[$i] == 't_tickets.is_change' || $aColumns[$i] == 't_tickets.lucky_draw_fee') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $timeNow = strtotime(date("Y-m-d H:i:s"));
    if($aRow[5] == '0000-00-00 00:00:00' || $aRow[5] == ''){
        $timeJourney = strtotime(date('Y-m-d H:i:s', strtotime("+1 days")));
    } else {
        $dateJourney = explode(" ", $aRow[5]);
        $departure   = explode(":", $dateJourney[1]);
        $departureTime = (int) $departure;
        if(checkDateFrom($aRow[17] , $departureTime) == 0) {
            $timeJourney = strtotime("+1 day", strtotime($aRow[5]));
        } else {
            $timeJourney = strtotime($aRow[5]);
        }
    }
    $agencyPaid = false;
    if(!empty($aRow[21])){
        $sqlChkPaid = mysql_query("SELECT id FROM agency_postpaid_claim_details WHERE t_ticket_id = ".$aRow[0]." AND is_active = 1 LIMIT 1");
        if(mysql_num_rows($sqlChkPaid)){
            $agencyPaid = true;
        }
    }
    $row[] =
            ($allowView ? '<a href="" class="btnViewTTicket" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowView && $aRow[14]  > 0 ? '<a href="" class="btnUpdaetNoteTTicket" rel="' . $aRow[0] . '" name="' . $aRow[2] . '" note="' . str_replace('"', "{dblquote}", trim($aRow[19] )) . '"><img alt="Update Note" onmouseover="Tip(\'Update Note\')" src="' . $this->webroot . 'img/button/note.png" /></a> ' : '') .
            ($allowReprint && $aRow[14]  == 2 ? '<a href="" class="btnPrintTTicket" rel="' . $aRow[0] . '" name="' . $aRow[2] . '" print-type="'. $aRow[20] .'"><img alt="Print" onmouseover="Tip(\'' . ACTION_PRINT . '\')" src="' . $this->webroot . 'img/button/printer.png" /></a> ' : '') .
            ($allowAddLucky && $aRow[14]  == 2 && $aRow[24]  == 0 ? '<a href="" class="btnAddTTicketLucky" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Print Lucky Ticket" onmouseover="Tip(\'Add Lucky Ticket\')" src="' . $this->webroot . 'img/button/luck_ticket.png" style="width: 16px;" /></a> ' : '') .
            ($allowPrintLucky && $aRow[22]  == 1 ? '<a href="" class="btnPrintTTicketLucky" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Print Lucky Ticket" onmouseover="Tip(\'' . ACTION_PRINT . ' Lucky Ticket\')" src="' . $this->webroot . 'img/button/ticket-icon.png" style="width: 16px;" /></a> ' : '') .
            ($allowEdit && $aRow[14]  > 0 && $isOpenDate == 1 ? '<a href="" class="btnEditTTicket" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
            ($allowCancel && $aRow[12]  == 2 && $aRow[14]  == 1 && ($timeNow < $timeJourney) ? '<a href="" class="btnCancelTTicket" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Cancel" onmouseover="Tip(\'' . ACTION_CANCEL . '\')" src="' . $this->webroot . 'img/button/void.png" /></a> ' : '') .
            (($allowFullDelete && $aRow[14]  > 0 && $agencyPaid == false) || ($allowDelete && ($timeNow < $timeJourney) && $aRow[14]  > 0 && $agencyPaid == false) ? '<a href="" class="btnDeleteTTicket" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>