<?php

// Function
include('includes/function.php');

$tableName = "agency_balance_offline_" . $user['User']['id'];
mysql_query("DROP TABLE `".$tableName."`;");
mysql_query("SET max_heap_table_size = 1024*1024*1024");
mysql_query("CREATE TABLE IF NOT EXISTS `$tableName` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `debit` double DEFAULT NULL,
                  `credit` double DEFAULT NULL,
                  `t_agency_id` bigint(20) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `t_agency_id` (`t_agency_id`)
                ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
mysql_query("TRUNCATE $tableName") or die(mysql_error());

$glCondition = "1";
if ($data[1] != '') {
    $glCondition != '' ? $glCondition .= ' AND ' : '';
    $glCondition .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" > DATE(created)';
}
if ($data[3] != '') {
    $glCondition != '' ? $glCondition .= ' AND ' : '';
    $glCondition .= 't_agency_id=' . $data[3];
} else {
    $glCondition .= 't_agency_id IN (SELECT id FROM t_agents WHERE offline_project_id = 1 AND status = 1 AND type = 2)';
}

mysql_query("INSERT INTO `".$tableName."` (t_agency_id, debit, credit) SELECT t_agency_id, SUM(debit), SUM(credit) FROM agency_balances WHERE ".$glCondition." GROUP BY t_agency_id");

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    'gl.id',
    'CONCAT(t_agents.code," - ",t_agents.name)',
    'gl.module',
    'gl.created',
    'gl.reference',
    'IF(gl.credit>0,gl.credit*-1,gl.debit)',
    'gl.t_agency_id');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "gl.id";

/* DB table to use */
$sTable = "agency_balances gl INNER JOIN t_agents ON t_agents.id = gl.t_agency_id AND t_agents.type = 2 LEFT JOIN t_tickets ON t_tickets.id = gl.t_ticket_id";

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
    $sOrder = "ORDER BY t_agents.code, gl.created ASC";
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
$condition = "1";
if ($data[1] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= DATE(gl.created)';
}
if ($data[2] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= DATE(gl.created)';
}
if ($data[3] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'gl.t_agency_id=' . $data[3];
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
$amount = 0;
$amountTotal = 0;
while ($aRow = mysql_fetch_array($rResult)) {
    // Beging Balance
    $sqlBg = mysql_query("SELECT SUM(credit - debit) FROM `".$tableName."` WHERE t_agency_id = ".$aRow[6]);
    $rowBg = mysql_fetch_array($sqlBg);
    if ($index != 0 && $aRow[1] != $tmpId) {
        $index = 0;
        $rowTotal = array();
        $rowTotal[] = '<b class="colspanParent">Total ' . $tmpName . '</b>';
        for ($i = 0; $i < count($aColumns) - 3; $i++) {
            $rowTotal[] = '';
        }
        $rowTotal[] = '<b>' . number_format($amount, 2) . '</b>';
        $rowTotal[] = '<b>' . number_format($amount, 2) . '</b>';
        $output['aaData'][] = $rowTotal;
    }
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            if ($aRow[1] == $tmpId) {
                $row[] = '<b>' . ++$index . '</b>';
            } else {
                $amount = $rowBg[0];
                $amountTotal += $rowBg[0];
                if (!is_null($aRow[1])) {
                    $tmpName = $aRow[1];
                } else {
                    $tmpName = 'General Agency';
                }
                $row[] = '<b class="colspanParent">' . $tmpName . '</b>';
                for ($j = 0; $j < count($aColumns) - 3; $j++) {
                    $row[] = '';
                }
                $row[] = '<b>' . number_format($amount, 2) . '</b>';
                $output['aaData'][] = $row;
                $row = array();
                $row[] = '<b>' . ++$index . '</b>';
            }
        } else if ($i == 1) {
            
        } else if ($aColumns[$i] == 'gl.created') {
            if ($aRow[$i] != '0000-00-00 00:00:00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y H:i:s");
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'IF(gl.credit>0,gl.credit*-1,gl.debit)') {
            $row[] = number_format($aRow[$i], 2);
            $amount += $aRow[$i];
            $amountTotal += $aRow[$i];
        } else if ($aColumns[$i] == 'gl.t_agency_id') {
            
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] = number_format($amount, 2);
    $output['aaData'][] = $row;
    $tmpId = $aRow[1];
}
if (mysql_num_rows($rResult)) {
    $rowTotal = array();
    $rowTotal[] = '<b class="colspanParent">Balance ' . $tmpName . '</b>';
    for ($i = 0; $i < count($aColumns) - 3; $i++) {
        $rowTotal[] = '';
    }
    $rowTotal[] = '<b>' . number_format($amount, 2) . '</b>';
    $rowTotal[] = '<b>' . number_format($amount, 2) . '</b>';
    $output['aaData'][] = $rowTotal;

    $rowTotal = array();
    $rowTotal[] = '<b class="colspanParent">Current Balance</b>';
    for ($i = 0; $i < count($aColumns) - 3; $i++) {
        $rowTotal[] = '';
    }
    $rowTotal[] = '<b>' . number_format($amountTotal, 2) . '</b>';
    $rowTotal[] = '<b>' . number_format($amountTotal, 2) . '</b>';
    $output['aaData'][] = $rowTotal;
}

echo json_encode($output);
?>