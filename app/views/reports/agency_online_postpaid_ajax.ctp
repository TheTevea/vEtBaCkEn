<?php
// Authentication
$this->element('check_access');
$allowPaid = checkAccess($user['User']['id'], $this->params['controller'], 'agencyOnlinePostpaidClaim');

// Function
include('includes/function.php');
/**
 * export to excel
 */
$filename = "public/report/sales_ticket_agency_online_postpaid" . $user['User']['id'] . ".csv";
$fp = fopen($filename,"wb");
$excelContent = REPORT_SALES_TICKET_AGENCY_ONLINE_POSTPAID."\n\n";
$excelContent .= REPORT_FROM.": ".str_replace("|||", "/", $data[1]);
$excelContent .= " ".REPORT_TO.": ".str_replace("|||", "/", $data[2]);
$excelContent .= "\n\n".TABLE_NO."\t".MENU_AGENT."\t".TABLE_TICKET_CODE."\t".TABLE_BOOKING_DATE."\t".TABLE_JOURNEY_DATE."\t".REPORT_FROM."\t".REPORT_TO."\t".TABLE_SEAT."\tSelling Price\tNet Price\t".TABLE_COMMISSION."\t".TABLE_MARKUP."\t".TABLE_CREATED."\tPaid Date\tPaid By\t".TABLE_STATUS."\t".ACTION_ACTION;

$conTicket = "t_tickets.status >= 0 AND t_tickets.t_agent_id != 55 AND t_tickets.offline_project_id = 1";
// Booking Date
if ($data[1] != '') {
    $conTicket != '' ? $conTicket .= ' AND ' : '';
    $conTicket .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= t_tickets.date';
}
if ($data[2] != '') {
    $conTicket != '' ? $conTicket .= ' AND ' : '';
    $conTicket .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= t_tickets.date';
}
// Travel Date
if ($data[4] != '') {
    $conTicket != '' ? $conTicket .= ' AND ' : '';
    $conTicket .= '"' . dateConvert(str_replace("|||", "/", $data[4])) . '" <= t_tickets.journey_date';
}
if ($data[5] != '') {
    $conTicket != '' ? $conTicket .= ' AND ' : '';
    $conTicket .= '"' . dateConvert(str_replace("|||", "/", $data[5])) . '" >= t_tickets.journey_date';
}
// Filter Agency
if ($data[9] != '') {
    $conTicket != '' ? $conTicket .= ' AND ' : '';
    $conTicket .= 't_tickets.t_agent_id =' . $data[9];
}
// Filter Location Branch
if ($data[11] != '') {
    $conTicket != '' ? $conTicket .= ' AND ' : '';
    $conTicket .= 't_agents.main_branch_id =' . $data[11];
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */

$aColumns = array(
            't_tickets.id',
            't_agents.name',
            't_tickets.code',
            't_tickets.date', 
            'CONCAT_WS(" ", t_tickets.journey_date,t_tickets.journey_time)',
            'destTo.name',
            'destFrom.name',
            't_tickets.t_journey_transit_id',
            'IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0)',
            'IFNULL(agency_balances.debit, 0)',
            'IFNULL(t_tickets.commission, 0)', // 10
            'IFNULL(t_tickets.total_markup, 0)',  
            't_tickets.created',
            'agency_postpaid_claim_details.created',
            'IFNULL(users.username, "")',
            't_tickets.status', // 15
            'currency_centers.symbol',
            't_tickets.is_open_date', 
            't_tickets.is_agent_paid');
        
/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "t_tickets.id";
        
/* DB table to use */
$sTable = "(
                SELECT t_tickets.*  
                FROM t_tickets
                INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.status = 1 AND t_agents.type = 1 AND t_agents.payment = 2 AND t_agents.id != 55
                WHERE ".$conTicket."
                UNION ALL
                SELECT t_tickets.*  
                FROM t_ticket_3months AS t_tickets
                INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.status = 1 AND t_agents.type = 1 AND t_agents.payment = 2 AND t_agents.id != 55
                WHERE ".$conTicket."
            ) AS t_tickets 
            INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.status = 1 AND t_agents.type = 1 AND t_agents.payment = 2 AND t_agents.id != 55
            INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id 
            INNER JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id AND agency_balances.module = 'Ticket Booking'
            INNER JOIN t_destinations AS destTo ON destTo.id = t_tickets.t_destination_from_id
            INNER JOIN t_destinations AS destFrom ON destFrom.id = t_tickets.t_destination_to_id
            LEFT JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id
            LEFT JOIN agency_postpaid_claim_details ON agency_postpaid_claim_details.t_ticket_id = t_tickets.id AND agency_postpaid_claim_details.is_active = 1
            LEFT JOIN users ON users.id = agency_postpaid_claim_details.created_by";

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
    for ($i = 0; $i < count($aColumns) - 14; $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - 14; $i++) {
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
// Filter Status
if ($data[8] != '') {
    $condition = 't_tickets.status =' . $data[8] . ' AND t_tickets.t_agent_id != 55 AND t_tickets.offline_project_id = 1';
} else {
    $condition = 't_tickets.status >= 0 AND t_tickets.t_agent_id != 55 AND t_tickets.offline_project_id = 1';
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

if ($data[10] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "t_tickets.company_id IN (".$data[10].")";
}
if ($data[12] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "t_agents.t_agent_type_id = ".$data[12];
}
if ($data[13] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "t_tickets.is_agent_paid = ".$data[13];
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
    $sqlSeat   = mysql_query("SELECT GROUP_CONCAT(label_number) FROM t_ticket_details WHERE t_ticket_id = ".$aRow[0]." AND is_active = 1");
    $rowSeat   = mysql_fetch_array($sqlSeat);
    if(empty($rowSeat[0])){
        $sqlSeat   = mysql_query("SELECT GROUP_CONCAT(label_number) FROM t_ticket_detail_3months WHERE t_ticket_id = ".$aRow[0]." AND is_active = 1");
        $rowSeat   = mysql_fetch_array($sqlSeat);
        if(empty($rowSeat[0])){
            $sqlSeat   = mysql_query("SELECT GROUP_CONCAT(label_number) FROM 2023_t_ticket_details WHERE t_ticket_id = ".$aRow[0]." AND is_active = 1");
            $rowSeat   = mysql_fetch_array($sqlSeat);
        }
    }
    
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
            $excelContent .= "\n" . $index;
        } else if ($aColumns[$i] == 't_tickets.created' || $aColumns[$i] == 'agency_postpaid_claim_details.created') {
            if ($aRow[$i] != '0000-00-00 00:00:00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y H:i:s");
                $excelContent .= "\t" .dateShort($aRow[$i], "d/m/Y H:i:s");
            } else {
                $row[] = '';
                $excelContent .= "\t";
            }
        } else if ($aColumns[$i] == 't_tickets.date') {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y");
                $excelContent .= "\t" .dateShort($aRow[$i], "d/m/Y");
            } else {
                $row[] = 'Open Date';
                $excelContent .= "\tOpen Date";
            }
        } else if ($aColumns[$i] == 'CONCAT_WS(" ", t_tickets.journey_date,t_tickets.journey_time)') {
            if ($aRow[17] == 1){
                $row[] = 'Open Date';
                $excelContent .= "\tOpen Date";
            } else {
                if ($aRow[$i] != '0000-00-00 00:00:00' && $aRow[$i] != '') {
                    $row[] = dateShort($aRow[$i], "d/m/Y H:i");
                    $excelContent .= "\t" .dateShort($aRow[$i], "d/m/Y H:i");
                } else {
                    $row[] = '';
                    $excelContent .= "\t";
                }
            }
        } else if ($aColumns[$i] == 't_tickets.status') {
            $status = "";
            if($aRow[$i] == 0){
                $row[]  = 'Void';
                $status = "Void";
            } else if($aRow[$i] == 1){
                $row[]  = 'Pending';
                $status = "Pending";
            } else if($aRow[$i] == 2){
                if($aRow[18] == 1){
                    $row[]  = 'Paid';
                    $status = "Paid";
                } else {
                    $row[]  = 'Unpaid';
                    $status = "Unpaid";
                }
            }
            $excelContent .= "\t".$status;
        } else if ($aColumns[$i] == 'IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0)') {
            $row[] = number_format($aRow[$i], 2)." ".$aRow[16];
            $excelContent .= "\t".number_format($aRow[$i], 2);
        } else if ($aColumns[$i] == 'IFNULL(agency_balances.debit, 0)') {
            $row[] = number_format($aRow[$i], 2)." ".$aRow[16];
            $excelContent .= "\t".number_format($aRow[$i], 2);
        } else if ($aColumns[$i] == 'IFNULL(t_tickets.commission, 0)') {
            $row[] = number_format(($aRow[8] - $aRow[9]), 2)." ".$aRow[16];
            $excelContent .= "\t".number_format(($aRow[8] - $aRow[9]), 2);
        } else if ($aColumns[$i] == 'IFNULL(t_tickets.total_markup, 0)') {
            $row[] = number_format($aRow[$i], 2)." ".$aRow[16];
            $excelContent .= "\t".number_format($aRow[$i], 2);
        } else if ($aColumns[$i] == 'currency_centers.symbol') {
            if($allowPaid){
                if($aRow[15] == 2 && $aRow[18] == 0){
                    $row[] = '<input type="checkbox" value="'.$aRow[0].'" class="chkAgencyOnlinePostpaid" />';
                } else {
                    $row[] = '<a href="#" class="btnAgentOnlinePostPaidUnPaid" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Unpaid" onmouseover="Tip(\'Unpaid\')" src="' . $this->webroot . 'img/button/stop.png" /></a>';
                }   
            } else {
                $row[] = '';
            }
            $excelContent .= "\t";
        } else if($aColumns[$i] == 't_tickets.t_journey_transit_id'){
            $row[] = $rowSeat[0];
            $excelContent .= "\t".$rowSeat[0];
        } else if ($aColumns[$i] == 't_tickets.is_agent_paid') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
            $excelContent .= "\t".$aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);

$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);
?>