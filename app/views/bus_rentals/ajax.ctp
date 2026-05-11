<?php
include("includes/function.php");
/**
 * export to excel
 */
$filename = "public/report/bus_retal" . $user['User']['id'] . ".csv";
$fp = fopen($filename,"wb");
$excelContent = MENU_BUS_RENTAL."\n";
$excelContent .= "\n\n".TABLE_NO."\t".TABLE_DATE."\t".TABLE_NAME."\t".TABLE_TELEPHONE."\t".TABLE_DESTINATION_FROM."\t".TABLE_DESTINATION_TO."\t".MENU_BUS_TYPE."\tNumber of Car\t".REPORT_FROM."\t".REPORT_TO;
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('bus_rentals.id', 'bus_rentals.created', 'bus_rentals.name', 'bus_rentals.telephone', 'provinces.name', 'province_to.name', 'bus_types.name', 'bus_rentals.number_bus', 'bus_rentals.date_from', 'bus_rentals.date_to');
 
/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "bus_rentals.id";

/* DB table to use */
$sTable = "bus_rentals
           INNER JOIN provinces ON provinces.id = bus_rentals.province_from
           INNER JOIN provinces AS province_to ON province_to.id = bus_rentals.province_to
           INNER JOIN bus_types ON bus_types.id = bus_rentals.bus_type_id";

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
    for ($i = 0; $i < count($aColumns); $i++) {
        if($aColumns[$i] == 'name'){
            $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
        }
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns); $i++) {
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
$condition = "bus_rentals.status > 0";
if($from != "all"){
    $condition .= " AND bus_rentals.province_from = ".$from;
}
if($to != "all"){
    $condition .= " AND bus_rentals.province_to = ".$to;
}
if($bus != "all"){
    $condition .= " AND bus_rentals.bus_type_id = ".$bus;
}
if($date != "all"){
    $condition .= " AND DATE(bus_rentals.created) = '".$date."'";
}
// if($status != "all"){
//     if($status == 1){
//         $condition .= " AND DATE_ADD(bus_rentals.departure, INTERVAL IFNULL(bus_rentals.delay_time, 0) MINUTE) >= now()";
//     } else {
//         $condition .= " AND DATE_ADD(bus_rentals.departure, INTERVAL IFNULL(bus_rentals.delay_time, 0) MINUTE) < now()";
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
            $excelContent .= "\n" . $index;
        } else if ($aColumns[$i] == 'bus_rentals.created') {
            $row[] = dateShort($aRow[$i], "d/m/Y H:i");
            $excelContent .= "\t" .dateShort($aRow[$i], "d/m/Y H:i:s");
        } else if ($aColumns[$i] == 'bus_rentals.date_from' || $aColumns[$i] == 'bus_rentals.date_to') {
            if($aRow[$i] != "" && $aRow[$i] != "0000-00-00"){
                $row[] = dateShort($aRow[$i], "d/m/Y");
                $excelContent .= "\t" .dateShort($aRow[$i], "d/m/Y");
            } else {
                $row[] = "";
                $excelContent .= "\t";
            }
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
            $excelContent .= "\t".$aRow[$i];
        }
    }
    // $row[] =
    //         ($allowView ? '<a href="" class="btnViewBusRental" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
    //         ($allowNote ? '<a href="#" class="btnUpdateNoteBusRental" rel="' . $aRow[0] . '" name="' . $aRow[1] . '" note="' . $aRow[9] . '"><img alt="'.ACTION_UPDATE_NOTE.'" onmouseover="Tip(\'' . ACTION_UPDATE_NOTE . '\')" src="' . $this->webroot . 'img/button/note.png" style="width: 16px;" /></a> ' : ' ') .
    //         ($allowDelete ? '<a href="" class="btnDeleteBusRental" rel="' . $aRow[0] . '" name="' . $aRow[3] . ' - ' . $aRow[4] . ' (' . $aRow[2] . ')"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);

$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);
?>