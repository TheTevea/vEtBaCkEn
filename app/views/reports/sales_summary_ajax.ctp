<?php
// Function
include('includes/function.php');
/**
 * export to excel
 */
$filename = "public/report/sales_ticket_summary_detail" . $user['User']['id'] . ".csv";
$fp = fopen($filename,"wb");
$excelContent = REPORT_SALES_TICKET_BRANCH." Summary\n\n";
$excelContent .= REPORT_FROM.": ".str_replace("|||", "/", $data[1]);
$excelContent .= " ".REPORT_TO.": ".str_replace("|||", "/", $data[2]);
$excelContent .= "\n\n".TABLE_NO."\tTransaction No\t".TABLE_TICKET_CODE."\t".TABLE_BOOKING_DATE."\t".TABLE_JOURNEY_DATE."\t".TABLE_DEPARTURE."\t".REPORT_FROM."\t".REPORT_TO."\tSeat #\t".TABLE_TOTAL_SEAT."\t".GENERAL_AMOUNT."\tPayment\t".TABLE_TELEPHONE."\t".TABLE_CREATED_BY."\t".TABLE_TYPE."\t".TABLE_STATUS;

$condFilter = 't_tickets.offline_project_id = 1';
// Booking Date
if ($data[1] != '') {
    $condFilter != '' ? $condFilter .= ' AND ' : '';
    $condFilter .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= DATE(t_tickets.date)';
}
if ($data[2] != '') {
    $condFilter != '' ? $condFilter .= ' AND ' : '';
    $condFilter .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= DATE(t_tickets.date)';
}
// Travel Date
if ($data[4] != '') {
    $condFilter != '' ? $condFilter .= ' AND ' : '';
    $condFilter .= '"' . dateConvert(str_replace("|||", "/", $data[4])) . '" <= DATE(t_tickets.journey_date)';
}
if ($data[5] != '') {
    $condFilter != '' ? $condFilter .= ' AND ' : '';
    $condFilter .= '"' . dateConvert(str_replace("|||", "/", $data[5])) . '" >= DATE(t_tickets.journey_date)';
}
// Filter Company
$condFilter != '' ? $condFilter .= ' AND ' : '';
if ($data[6] != '') {
    $condFilter .= "t_tickets.company_id IN (" . $data[6]. ")";
} else {
    $condFilter .= "t_tickets.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '" . $user['User']['id']. "')";
}
// Filter Main Branch
if ($data[7] != '') {
    $condFilter != '' ? $condFilter .= ' AND ' : '';
    $condFilter .= 't_tickets.main_branch_id =' . $data[7];
}
// Filter Destination From
if ($data[8] != '') {
    $condFilter != '' ? $condFilter .= ' AND ' : '';
    $condFilter .= "t_tickets.t_destination_from_id = '" . $data[8]. "'";
}
// Filter Destination To
if ($data[9] != '') {
    $condFilter != '' ? $condFilter .= ' AND ' : '';
    $condFilter .= "t_tickets.t_destination_to_id = '" . $data[9]. "'";
}
// Filter Created
if ($data[10] != '') {
    $condFilter != '' ? $condFilter .= ' AND ' : '';
    $condFilter .= "t_tickets.created_by = '" . $data[10]. "'";
}
// Filter Payment Method
if ($data[11] != '') {
    $condFilter != '' ? $condFilter .= ' AND ' : '';
    $condFilter .= "t_tickets.payment_method_id = '" . $data[11]. "'";
}
// Filter Status
if ($data[12] != '') {
    $condFilter != '' ? $condFilter .= ' AND ' : '';
    $condFilter .= 't_tickets.status =' . $data[12];
} else {
    $condFilter != '' ? $condFilter .= ' AND ' : '';
    $condFilter .= 't_tickets.status > 0';
}
// Filter Agency
if ($data[16] != '') {
    $condFilter != '' ? $condFilter .= ' AND ' : '';
    $condFilter .= "t_tickets.t_agent_id = '" . $data[16]. "'";
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    't_tickets.id',
    'online_orders.code',
    't_tickets.code',
    't_tickets.date', 
    't_tickets.journey_date',
    't_tickets.journey_time',
    't_destinations.name',
    'destTo.name',
    '(SELECT GROUP_CONCAT(label_number) FROM t_ticket_details WHERE t_ticket_id = t_tickets.id AND is_active = 1)',
    '(SELECT COUNT(id) FROM t_ticket_details WHERE t_ticket_id = t_tickets.id AND is_active = 1)',
    'IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)',
    'payment_methods.name',
    't_tickets.telephone',
    'IFNULL((SELECT CONCAT_WS(first_name," ",last_name) FROM users WHERE id = t_tickets.confirm_by), (SELECT CONCAT_WS(first_name," ",last_name) FROM users WHERE id = t_tickets.created_by))',
    't_tickets.type',
    't_tickets.status', // 15
    'currency_centers.symbol', 
    't_tickets.is_open_date',
    'IFNULL(agency_balances.debit, 0)',
    't_tickets.t_agent_id');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "t_tickets.id";

/* DB table to use */
// $sTable = "(
//             SELECT * FROM t_tickets WHERE ".$condFilter."
//             UNION ALL
//             SELECT * FROM t_ticket_3months AS t_tickets WHERE ".$condFilter."
//             UNION ALL
//             SELECT * FROM 2023_t_tickets AS t_tickets WHERE ".$condFilter."
//             ) 
//            AS t_tickets INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id 
//            INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
//            INNER JOIN t_destinations AS destTo ON destTo.id = t_tickets.t_destination_to_id
//            LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id
//            LEFT JOIN payment_methods ON payment_methods.id = t_tickets.payment_method_id 
//            LEFT JOIN t_agents ON t_agents.id = t_tickets.t_agent_id
//            LEFT JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'";

$sTable = "(
                SELECT * FROM t_tickets WHERE ".$condFilter."
            ) 
            AS t_tickets INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id 
            INNER JOIN t_destinations ON t_destinations.id = t_tickets.t_destination_from_id
            INNER JOIN t_destinations AS destTo ON destTo.id = t_tickets.t_destination_to_id
            LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id
            LEFT JOIN payment_methods ON payment_methods.id = t_tickets.payment_method_id 
            LEFT JOIN t_agents ON t_agents.id = t_tickets.t_agent_id
            LEFT JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'";

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
    $sOrder = "ORDER BY t_tickets.date, t_tickets.code ASC";
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
    for ($i = 0; $i < count($aColumns) - 5; $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - 5; $i++) {
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
$condition = 't_tickets.offline_project_id = 1';
// Filter Booking Type
if ($data[13] != '') {
    if($data[13] == 1){ // Walk In
        $condition .= " AND t_tickets.type = 1";
    } else if($data[13] == 2){ // Website
        $condition .= " AND t_tickets.terminal_id IS NULL AND ((t_tickets.type = 5 OR t_tickets.type = 11) AND t_tickets.t_agent_id = 55)";
    } else if($data[13] == 3){ // Agent APi (Prepaid)
        $condition .= " AND ((t_tickets.type = 3 OR t_tickets.type = 7) AND t_agents.type = 3 AND t_agents.payment = 1)";
    } else if($data[13] == 4){ // Agent Online (Prepaid)
        $condition .= " AND ((t_tickets.type = 3 OR t_tickets.type = 9) AND t_agents.type = 1 AND t_agents.payment = 1)";
    } else if($data[13] == 5){ // Agent Offline (Prepaid)
        $condition .= " AND ((t_tickets.type = 3 OR t_tickets.type = 9) AND t_agents.type = 2 AND t_agents.payment = 1)";
    } else if($data[13] == 6){ // Mini App
        $condition .= " AND ((t_tickets.type = 5 OR t_tickets.type = 10 OR t_tickets.type = 11) AND t_tickets.t_agent_id = 106)";
    } else if($data[13] == 7){ // App
        $condition .= " AND t_tickets.terminal_id IS NULL AND ((t_tickets.type = 5 OR t_tickets.type = 11) AND t_tickets.t_agent_id IS NULL)";
    } else if($data[13] == 8){ // Terminal
        $condition .= " AND t_tickets.terminal_id IS NOT NULL";
    } else if($data[13] == 9){ // Agent APi (Postpaid)
        $condition .= " AND ((t_tickets.type = 3 OR t_tickets.type = 7) AND t_agents.type = 3 AND t_agents.payment = 2)";
    } else if($data[13] == 10){ // Agent Online (Postpaid)
        $condition .= " AND ((t_tickets.type = 3 OR t_tickets.type = 9) AND t_agents.type = 1 AND t_agents.payment = 2)";
    } else if($data[13] == 11){ // Agent Offline (Postpaid)
        $condition .= " AND ((t_tickets.type = 3 OR t_tickets.type = 9) AND t_agents.type = 2 AND t_agents.payment = 2)";
    } 
}
// Filter Status
if ($data[14] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 't_destinations.t_destination_group_id =' . $data[14];
}
// Filter Status
if ($data[15] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 't_destinations.province_id =' . $data[15];
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
$tmpId = '$';
$tmpName = '';
$amount  = 0;
$amountTotal = 0;
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = '<b>' . ++$index . '</b>';
            $excelContent .= "\n" . $index;
        } else if ($aColumns[$i] == 't_tickets.date') {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y");
                $excelContent .= "\t" .dateShort($aRow[$i], "d/m/Y");
            } else {
                $row[] = 'Open Date';
                $excelContent .= "\tOpen Date";
            }
        } else if ($aColumns[$i] == 't_tickets.journey_date') {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y");
                $excelContent .= "\t" .dateShort($aRow[$i], "d/m/Y");
            } else {
                $row[] = '';
                $excelContent .= "\t";
            }
        } else if ($aColumns[$i] == 't_tickets.journey_time') {
            $row[] = date("h:i A", strtotime($aRow[$i]));
            $excelContent .= "\t".date("h:i A", strtotime($aRow[$i]));
        } else if ($aColumns[$i] == 't_tickets.status') {
            switch($aRow[$i]){
                case 0:
                    $row[] =  'Void';
                    $excelContent .= "\tVoid";
                    break;
                case 1:
                    $row[] =  'Pending';
                    $excelContent .= "\tPending";
                    break;
                case 2:
                    $row[] =  'Completed';
                    $excelContent .= "\tCompleted";
                    break;
            }
        } else if ($aColumns[$i] == 't_tickets.type') {
            if($aRow[$i] == 1){
                $row[] = 'Walk In';
                $excelContent .= "\tWalk In";
            } else if($aRow[$i] == 2){   
                $row[] = 'Phone Call';
                $excelContent .= "\tPhone Call";
            } else if($aRow[$i] == 5){   
                if($aRow[19] == 55){
                    $row[] = 'Website';
                    $excelContent .= "\tWebsite";
                } else {
                    $row[] = 'App';
                    $excelContent .= "\tApp";
                }
            } else if($aRow[$i] == 7){   
                $row[] = 'API';
                $excelContent .= "\tAPI";
            } else if($aRow[$i] == 11){   
                $row[] = 'Mini App';
                $excelContent .= "\tMini App";
            } else {
                $row[] = 'Agency';
                $excelContent .= "\tAgency";
            }
        } else if ($aColumns[$i] == 'currency_centers.symbol' || $aColumns[$i] == 't_tickets.is_open_date' || $aColumns[$i] == 'IFNULL(agency_balances.debit, 0)' || $aColumns[$i] == 't_tickets.t_agent_id') {
        } else if ($aColumns[$i] == 'IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)') {
            if($aRow[18] > 0){
                $amount = $aRow[18];
            } else {
                $amount = $aRow[$i];
            }
            $row[] = number_format($amount, 2)." ".$aRow[16];
            $excelContent .= "\t".number_format($amount, 2);
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
            $excelContent .= "\t".$aRow[$i];
        }
    }
    $output['aaData'][] = $row;
    $tmpId = $aRow[1];
}

echo json_encode($output);

$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);
?>