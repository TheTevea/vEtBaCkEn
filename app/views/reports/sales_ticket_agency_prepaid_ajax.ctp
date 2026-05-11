<?php
// Function
include('includes/function.php');
/**
 * export to excel
 */
$filename = "public/report/sales_ticket_agency_postpaid" . $user['User']['id'] . ".csv";
$fp = fopen($filename,"wb");
$excelContent = REPORT_SALES_TICKET_AGENCY_ONLINE." (Postpaid)\n\n";
$excelContent .= REPORT_FROM.": ".str_replace("|||", "/", $data[1]);
$excelContent .= " ".REPORT_TO.": ".str_replace("|||", "/", $data[2]);
$excelContent .= "\n\n".TABLE_NO."\t".MENU_AGENT."\tTransaction ID\t".TABLE_REFERENCE."\t".TABLE_TICKET_CODE."\t".TABLE_BOOKING_DATE."\t".TABLE_JOURNEY_DATE."\t".REPORT_FROM."\t".REPORT_TO."\t".TABLE_SEAT."\t".TABLE_FARE."\t".TABLE_COMMISSION."\t".TABLE_MARKUP."\t".TABLE_CREATED."\t".TABLE_STATUS;
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    't_tickets.id',
    't_agents.name',
    'online_orders.code',
    't_tickets.agt_refer_code',
    't_tickets.code',
    't_tickets.date', // 5 
    'CONCAT_WS(" ", t_tickets.journey_date,t_tickets.journey_time)',
    '(SELECT name FROM t_destinations WHERE id = t_tickets.t_destination_from_id)',
    '(SELECT name FROM t_destinations WHERE id = t_tickets.t_destination_to_id)',
    '(SELECT GROUP_CONCAT(label_number) FROM t_ticket_details WHERE t_ticket_id = t_tickets.id AND is_active = 1)',
    '(IFNULL(t_tickets.total_amount, 0) + IFNULL(t_tickets.total_vat, 0))', // 10
    '(IFNULL(t_journeys.agent_price_amount, 0) + IFNULL(t_tickets.total_vat, 0))',
    'IFNULL(t_tickets.commission, 0)',
    'IFNULL(t_tickets.total_bonus, 0)',
    'IFNULL(t_tickets.total_markup, 0)',
    'IFNULL(t_tickets.discount_amount, 0)', // 15
    't_tickets.created',
    't_tickets.status',
    'currency_centers.symbol',
    't_tickets.is_open_date',
    't_agents.commission_type', //20
    't_journeys.unit_price',
    't_agents.commission',
    't_agents.type',
    't_journeys.foreigner_price',
    't_journeys.agetn_price_percent', //25
    't_journeys.t_destination_from_id',
    't_journeys.t_destination_to_id',
    't_journeys.t_transportation_type_id',
    't_journeys.id',
    'companies.type', //30
    't_agents.commission_buva_sea_type',
    't_agents.commission_buva_sea');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "t_tickets.id";

/* DB table to use */
$sTable = "t_tickets 
           INNER JOIN t_agents ON t_agents.id = t_tickets.t_agent_id AND t_agents.type = 1
           INNER JOIN currency_centers ON currency_centers.id = t_tickets.currency_center_id 
           LEFT JOIN payment_methods ON payment_methods.id = t_tickets.payment_method_id
           LEFT JOIN t_journeys ON t_journeys.id = t_tickets.t_journey_id
           LEFT JOIN companies ON companies.id = t_journeys.company_id
           LEFT JOIN online_orders ON online_orders.id = t_tickets.online_order_id";

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
    for ($i = 0; $i < count($aColumns) - 16; $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - 16; $i++) {
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
    $condition = 't_tickets.status =' . $data[8] . ' AND t_tickets.t_agent_id != 55';
} else {
    $condition = 't_tickets.status >= 0 AND t_tickets.t_agent_id != 55';
}
if($user['User']['type'] != 1){
    $condition .= " AND t_tickets.offline_project_id = ".$user['User']['offline_project_id'];
}
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
// Filter Agency
if ($data[9] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 't_tickets.t_agent_id =' . $data[9];
} else {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 't_tickets.t_agent_id IN (SELECT id FROM t_agents WHERE status = 1 AND t_agents.type = 1 AND t_agents.payment = 1 AND id != 55)';
}
if ($data[10] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "t_tickets.company_id IN (".$data[10].")";
}

if ($data[11] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "t_agents.main_branch_id = ".$data[11];
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
    $sqlSeat   = mysql_query("SELECT COUNT(id) AS total, nationally FROM t_ticket_details WHERE t_ticket_id = ".$aRow[0]." AND is_active = 1");
    $rowSeat   = mysql_fetch_array($sqlSeat);
    $sellPrice = $aRow[10];
    $agePrice  = $aRow[11] * $rowSeat['total'];
    // if($rowSeat['nationally'] == 2){ // Foriegner  
    //     $agePrice  = $aRow[25] * $rowSeat['total'];
    // }
    $date = $aRow[5];
    // Check Price in Period
    // By Journey
    $sqlPJ = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = 1 AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND t_journey_id = ".$aRow[29].' ORDER BY id DESC LIMIT 1');
    if(mysql_num_rows($sqlPJ)){
        $rowPJ = mysql_fetch_array($sqlPJ);
        // if($rowSeat['nationally'] == 2){ // Foriegner  
        //     $agePrice  = $rowPJ['agency_price_foreigner'] * $rowSeat['total'];
        // } else {
            $agePrice  = $rowPJ['agency_price'] * $rowSeat['total'];
        // }
    } else { // By Destination
        $sqlPA = mysql_query("SELECT * FROM t_journey_price_periods WHERE offline_project_id = 1 AND destination_from_id = ".$aRow[26]." AND destination_to_id = ".$aRow[27]." AND t_transportation_type_id = ".$aRow[28]." AND start <= '".$date."' AND end >= '".$date."' AND status = 1 AND (main_branch_id IS NULL OR main_branch_id = '') ORDER BY id DESC LIMIT 1");
        if(mysql_num_rows($sqlPA)){
            $rowPAPrice = mysql_fetch_array($sqlPA);
            if($rowPAPrice['price_type'] == 1){
                // if($rowSeat['nationally'] == 2){ // Foriegner  
                //     $agePrice  = $rowPAPrice['agency_price_foreigner'] * $rowSeat['total'];
                // } else {
                    $agePrice  = $rowPAPrice['agency_price'] * $rowSeat['total'];
                // }
            } else {
                // if($rowSeat['nationally'] == 2){ // Foriegner
                //     $agePrice  = ($agePrice + ($rowPAPrice['agency_price_foreigner'])) * $rowSeat['total'];
                // } else {
                    $agePrice  = ($agePrice + ($rowPAPrice['agency_price'])) * $rowSeat['total'];   
                // }
            }
        }
    }
    $commission   = $sellPrice - $agePrice;
    $agentComType = $aRow[20];
    $agentCom     = $aRow[22];
    if($aRow[30] == 2){ // Company Type Boat
        $agentComType = $aRow[31];
        $agentCom = $aRow[32];
    }
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
            $excelContent .= "\n" . $index;
        } else if ($aColumns[$i] == 't_tickets.created') {
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
            if ($aRow[19] == 1){
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
            $satus = "";
            switch($aRow[$i]){
                case 0:
                    $row[]  = 'Void';
                    $satus = 'Void';
                    break;
                case 1:
                    $row[] =  'Pending';
                    $satus = 'Pending';
                    break;
                case 2:
                    $row[] =  'Completed';
                    $satus = 'Completed';
                    break;
            }
            $excelContent .= "\t".$satus;
        } else if ($aColumns[$i] == '(IFNULL(t_journeys.agent_price_amount, 0) + IFNULL(t_tickets.total_vat, 0))') {
            $row[] = number_format($agePrice, 2)." ".$aRow[18];
            $excelContent .= "\t".number_format($agePrice, 2)." ".$aRow[18];
        } else if ($aColumns[$i] == 'IFNULL(t_tickets.commission, 0)') {
            if($agentComType == 1){ // Percentage
                $row[] = number_format(($aRow[10] * $agentCom) / 100, 2)." ".$aRow[18];
                $excelContent .= "\t".number_format(($aRow[10] * $agentCom) / 100, 2)." ".$aRow[18];
            } else if($agentComType == 2){ // Selling Price - Agent Price (Journey)
                $row[] = number_format($commission, 2)." ".$aRow[18];
                $excelContent .= "\t".number_format($commission, 2)." ".$aRow[18];
            } else { // Fixed Amount
                $row[] = number_format($agentCom, 2)." ".$aRow[18];
                $excelContent .= "\t".number_format($agentCom, 2)." ".$aRow[18];
            }            
        } else if ($aColumns[$i] == 'IFNULL(t_tickets.total_markup, 0)') {
            $row[] = number_format($aRow[$i], 2)." ".$aRow[18];
            $excelContent .= "\t".number_format($aRow[$i], 2)." ".$aRow[18];
        } else if ($aColumns[$i] == 'IFNULL(t_tickets.discount_amount, 0)') {
            $row[] = number_format($aRow[$i], 2)." ".$aRow[18];
            $excelContent .= "\t".number_format($aRow[$i], 2)." ".$aRow[18];
        } else if ($aColumns[$i] == '(IFNULL(t_tickets.total_amount, 0) + IFNULL(t_tickets.total_vat, 0))') {
            $row[] = number_format($aRow[$i], 2)." ".$aRow[18];
            $excelContent .= "\t".number_format($aRow[$i], 2)." ".$aRow[18];
        } else if ($aColumns[$i] == 'IFNULL(t_tickets.total_bonus, 0)') {
            $row[] = number_format($aRow[$i], 2)." ".$aRow[18];
            $excelContent .= "\t".number_format($aRow[$i], 2)." ".$aRow[18];
        } else if ($aColumns[$i] == 't_agents.commission_type' || $aColumns[$i] == 't_journeys.unit_price' || $aColumns[$i] == 't_agents.type' || $aColumns[$i] == 't_journeys.foreigner_price' || $aColumns[$i] == 'currency_centers.symbol' || $aColumns[$i] == 't_tickets.is_open_date' || $aColumns[$i] == 't_journeys.agetn_price_percent' || $aColumns[$i] == 't_journeys.t_destination_from_id' || $aColumns[$i] == 't_journeys.t_destination_to_id' || $aColumns[$i] == 't_journeys.t_transportation_type_id' || $aColumns[$i] == 't_journeys.id') {
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