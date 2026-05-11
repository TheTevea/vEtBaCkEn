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
    'agency_postpaid_claims.id',
    'agency_postpaid_claims.date',
    'agency_postpaid_claims.code',
    'main_branches.name',
    'agency_postpaid_claims.created_by',
    'SUM(t_tickets.total_seat)',
    'SUM(agency_balances.credit)',
    'agency_postpaid_claims.created');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "agency_postpaid_claims.id";

/* DB table to use */
$sTable = "agency_postpaid_claims 
           INNER JOIN agency_postpaid_claim_details ON agency_postpaid_claim_details.agency_postpaid_claim_id = agency_postpaid_claims.id
           INNER JOIN 
           (
           SELECT * FROM t_tickets WHERE status = 2 AND offline_project_id = 1 AND t_agent_id IN (SELECT id FROM t_agents WHERE status = 1 AND type = 1 AND payment = 2 AND id != 55 AND offline_project_id = 1)
           UNION ALL
           SELECT * FROM t_ticket_3months WHERE status = 2 AND offline_project_id = 1 AND t_agent_id IN (SELECT id FROM t_agents WHERE status = 1 AND type = 1 AND payment = 2 AND id != 55 AND offline_project_id = 1)
           ) AS t_tickets ON t_tickets.id = agency_postpaid_claim_details.t_ticket_id
           INNER JOIN agency_balances ON agency_balances.t_ticket_id = t_tickets.id
           INNER JOIN t_agents ON t_agents.id = agency_balances.t_agency_id
           INNER JOIN main_branches ON main_branches.id = t_agents.main_branch_id";
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
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
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
$condition = "";
// Booking Date
if ($data[1] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= DATE(agency_postpaid_claims.date)';
}
if ($data[2] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= DATE(agency_postpaid_claims.date)';
}
if ($data[3] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 't_tickets.t_agent_id =' . $data[3];
}
if ($data[4] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "t_tickets.t_agent_id IN (SELECT id FROM t_agents WHERE t_agent_type_id = ".$data[4]." AND `status` = 1)";
}

if ($data[5] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "main_branches.id = ".$data[5];
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
$groupBy = "GROUP BY agency_postpaid_claims.id";
$sQuery = "
        SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
        FROM   $sTable
        $sWhere
        $sOrder
        $groupBy
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
        } else if ($aColumns[$i] == 'agency_postpaid_claims.date') {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y");
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'agency_postpaid_claims.created_by') {
            $sqlAg = mysql_query("SELECT GROUP_CONCAT(name) FROM t_agents WHERE id IN (SELECT t_tickets.t_agent_id FROM agency_postpaid_claim_details INNER JOIN t_tickets ON t_tickets.id = agency_postpaid_claim_details.t_ticket_id WHERE agency_postpaid_claim_details.agency_postpaid_claim_id = ".$aRow[0]." AND agency_postpaid_claim_details.is_active = 1 GROUP BY t_tickets.t_agent_id)");
            $rowAg = mysql_fetch_array($sqlAg);
            $row[] = $rowAg[0];
        } else if ($aColumns[$i] == 'SUM(t_tickets.total_seat)') {
            $row[] = number_format($aRow[$i], 0);
        } else if ($aColumns[$i] == 'SUM(agency_balances.credit)') {
            $row[] = number_format($aRow[$i], 2);
        } else if ($aColumns[$i] == 'agency_postpaid_claims.created') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] = '<a href="" class="btnPrintInvoiceAgencyPaid" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_PRINT . '\')" src="' . $this->webroot . 'img/button/printer.png" /></a> ';
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>