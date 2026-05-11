<?php
// Function
include('includes/function.php');
/**
 * export to excel
 */
$filename = "public/report/sales_ticket_travel_package" . $user['User']['id'] . ".csv";
$fp = fopen($filename,"wb");
$excelContent = REPORT_TRAVEL_PACKAGE_TICKET." (Postpaid)\n\n";
if($data[1] != "" && $data[2] != ""){
    $excelContent .= TABLE_BOOKING_FROM.": ".str_replace("|||", "/", $data[1]);
    $excelContent .= " ".TABLE_BOOKING_TO.": ".str_replace("|||", "/", $data[2]);
}
if($data[4] != "" && $data[5] != ""){
    $excelContent .= "\n".TABLE_TRAVELING_FROM.": ".str_replace("|||", "/", $data[4]);
    $excelContent .= " ".TABLE_TRAVELING_TO.": ".str_replace("|||", "/", $data[5]);
}
$excelContent .= "\n\n".TABLE_NO."\tPackage Type\tPackage Code\tPackage Name\tPackage Tel\t".TABLE_BOOKING_DATE."\t".TABLE_TICKET_CODE."\t".TABLE_JOURNEY_DATE."\t".TABLE_DEPARTURE."\t".REPORT_FROM."\t".REPORT_TO."\t".TABLE_TELEPHONE."\tSeat Number\t".GENERAL_AMOUNT."\t".TABLE_TYPE;

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    't_tickets.id',
    'travel_packages.name',
    'travel_package_orders.package_code',
    'travel_package_orders.name',
    'travel_package_orders.telephone',
    't_tickets.date', 
    't_tickets.code',
    't_tickets.journey_date',
    't_tickets.journey_time',
    't_destinations.name',
    'destTo.name',
    't_tickets.telephone',
    '(SELECT GROUP_CONCAT(label_number) FROM t_ticket_details WHERE t_ticket_id = t_tickets.id)',
    'IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)',
    't_tickets.type',
    't_tickets.t_agent_id'
     );

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "t_tickets.id";

/* DB table to use */
$sTable = "t_tickets
           INNER JOIN travel_package_orders ON travel_package_orders.id = t_tickets.travel_package_order_id 
           INNER JOIN travel_packages ON travel_packages.id = travel_package_orders.travel_package_id
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
    $sOrder = "ORDER BY travel_package_orders.code, t_tickets.date, t_tickets.code ASC";
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
$condition = 't_tickets.status = 2 AND t_tickets.offline_project_id = 1 AND t_tickets.travel_package_order_id > 0';
// Booking Date
if ($data[1] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= t_tickets.date';
}
if ($data[2] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= t_tickets.date';
}
// Travel Date
if ($data[4] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[4])) . '" <= t_tickets.journey_date';
}
if ($data[5] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[5])) . '" >= t_tickets.journey_date';
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

// Filter Travel Package
if ($data[10] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "t_tickets.travel_package_order_id = '" . $data[10]. "'";
}

// Filter Customer Name
if ($data[11] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "travel_package_orders.name = '" . $data[11]. "'";
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
            $excelContent .= "\n" . $index;
        } else if ($aColumns[$i] == 't_tickets.date') {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y");
                $excelContent .= "\t" .dateShort($aRow[$i], "d/m/Y");
            } else {
                $row[] = '';
                $excelContent .= "\t";
            }
        } else if ($aColumns[$i] == 't_tickets.journey_date') {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y");
                $excelContent .= "\t".dateShort($aRow[$i], "d/m/Y");
            } else {
                $row[] = '';
                $excelContent .= "\t";
            }
        } else if ($aColumns[$i] == 't_tickets.journey_time') {
            if ($aRow[$i] != '00:00:00' && $aRow[$i] != '') {
                $row[] = date("h:i A", strtotime($aRow[$i]));
                $excelContent .= "\t".date("h:i A", strtotime($aRow[$i]));
            } else {
                $row[] = '';
                $excelContent .= "\t";
            }
        } else if ($aColumns[$i] == 't_tickets.type') {
            if($aRow[$i] == 1){
                $row[] = 'Walk In';
                $excelContent .= "\tWalk In";
            } else if($aRow[$i] == 2){   
                $row[] = 'Phone Call';
                $excelContent .= "\tPhone Call";
            } else if($aRow[$i] == 5){   
                if($aRow[15] == 55){
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
        } else if ($aColumns[$i] == 'IFNULL(t_tickets.total_amount, 0) - IFNULL(t_tickets.discount_amount, 0) + IFNULL(t_tickets.total_vat, 0) + IFNULL(t_tickets.lucky_draw_fee, 0)') {
            $row[] = number_format($aRow[$i], 2)." $";
            $excelContent .= "\t".number_format($aRow[$i], 2);
        } else if ($aColumns[$i] == 't_tickets.t_agent_id') {
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