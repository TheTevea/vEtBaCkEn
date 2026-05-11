<?php

// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    'gl.id',
    'gl.module',
    'gl.created',
    'IFNULL(gl.reference, "")',
    'IF(gl.credit>0,gl.credit,gl.debit*-1)',
    'gl.t_ticket_id');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "gl.id";

/* DB table to use */
$sTable = "agency_balances gl";

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
    $sOrder = "ORDER BY gl.created DESC";
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
    for ($i = 0; $i < count($aColumns) - 9; $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - 9; $i++) {
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
$condition = "gl.t_agency_id=".$agencyId;
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
            $row[] = '<b>' . ++$index . '</b>';
        } else if ($aColumns[$i] == 'gl.created') {
            if ($aRow[$i] != '0000-00-00 00:00:00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y H:i:s");
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'IF(gl.credit>0,gl.credit,gl.debit*-1)') {
            $row[] = number_format($aRow[$i], 2);
        } else if ($aColumns[$i] == 'IFNULL(gl.reference, "")') {
            if(!empty($aRow[5])){
                $sqlTicket = mysql_query("
                             SELECT code FROM t_tickets WHERE id = ".$aRow[5]."
                             UNION ALL
                             SELECT code FROM t_ticket_3months WHERE id = ".$aRow[5]."
                             ");
                if(mysql_num_rows($sqlTicket)){
                    $rowTicket = mysql_fetch_array($sqlTicket);
                    $row[] = $rowTicket['code'];
                } else {
                    $sqlTmp = mysql_query("SELECT online_order_id FROM t_ticket_api_tmps WHERE id = ".$aRow[5]."
                                           UNION ALL 
                                           SELECT online_order_id FROM t_ticket_api_tmp_2024s WHERE id = ".$aRow[5]."");
                    if(mysql_num_rows($sqlTmp)){
                        $rowTmp = mysql_fetch_array($sqlTmp);
                        $sqlTicket = mysql_query("
                                     SELECT code FROM t_tickets WHERE online_order_id = ".$rowTmp['online_order_id']."
                                     UNION ALL
                                     SELECT code FROM t_ticket_3months WHERE online_order_id = ".$rowTmp['online_order_id']."
                                     ");
                        if(mysql_num_rows($sqlTicket)){
                            $rowTicket = mysql_fetch_array($sqlTicket);
                            $row[] = $rowTicket['code'];
                        } else {
                            $row[] = $aRow[$i];
                        }
                    } else {
                        $row[] = $aRow[$i];
                    }
                }
            } else {
                $row[] = $aRow[$i];
            }
        } else if ($aColumns[$i] == 'gl.t_ticket_id') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>