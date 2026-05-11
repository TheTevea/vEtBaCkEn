<?php

// Function
include('includes/function.php');
/**
 * export to excel
 */
$filename = "public/report/ticket_agency_balance" . $user['User']['id'] . ".csv";
$fp = fopen($filename,"wb");
$excelContent = REPORT_AGENCY_BALANCE."\n\n";
$excelContent .= REPORT_FROM.": ".str_replace("|||", "/", $data[1]);
$excelContent .= " ".REPORT_TO.": ".str_replace("|||", "/", $data[2]);
$excelContent .= "\n\n".TABLE_NO."\t".GENERAL_DESCRIPTION."\t".TABLE_DATE."\t".TABLE_REFERENCE."\tSelling Price\tNet Price\tBonus\t".GENERAL_AMOUNT." ($)\t".GENERAL_BALANCE." ($)";

$tableName = "agency_balance_" . $user['User']['id'];
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
$conTicket = "";
if ($data[1] != '') {
    $glCondition != '' ? $glCondition .= ' AND ' : '';
    $glCondition .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" > DATE(created)';
}
if ($data[3] != '') {
    $glCondition != '' ? $glCondition .= ' AND ' : '';
    $glCondition .= 't_agency_id=' . $data[3];
    $conTicket .= " AND t_agent_id = ". $data[3];
} else {
    $glCondition .= 't_agency_id IN (SELECT id FROM t_agents WHERE offline_project_id = 1)';
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
    'gl.credit',
    '(gl.net_price + gl.vat_price)',
    'gl.bonus',
    'IF(gl.credit>0,gl.credit,gl.debit*-1)',
    'gl.t_agency_id',
    'gl.t_ticket_id');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "gl.id";

/* DB table to use */
$sTable = "agency_balances gl 
           INNER JOIN t_agents ON t_agents.id = gl.t_agency_id ";

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
    for ($i = 0; $i < count($aColumns) - 2; $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - 2; $i++) {
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
$amountTotal  = 0;
$agencyResult = array();
while ($aRow = mysql_fetch_array($rResult)) {
    $agencyResult[] = $aRow[9];
    // Beging Balance
    $sqlBg = mysql_query("SELECT SUM(credit - debit) FROM `".$tableName."` WHERE t_agency_id = ".$aRow[9]);
    $rowBg = mysql_fetch_array($sqlBg);
    if ($index != 0 && $aRow[1] != $tmpId) {
        $index = 0;
        $rowTotal = array();
        $rowTotal[] = '<b class="colspanParent">Total ' . $tmpName . '</b>';
        $excelContent .= "\nTotal" . $tmpName;
        for ($i = 0; $i < count($aColumns) - 2; $i++) {
            $rowTotal[] = '';
            if($i > 0){
                $excelContent .= "\t";
            }
        }
        $rowTotal[] = '<b>' . number_format($amount, 2) . '</b>';
        $rowTotal[] = '<b>' . number_format($amount, 2) . '</b>';
        $excelContent .= "\t". number_format($amount, 2);
        $excelContent .= "\t". number_format($amount, 2);
        $output['aaData'][] = $rowTotal;
    }
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        $ticketPrice = 0;
        $ticketCode  = "";
        if(!empty($aRow[10])){
            $sqlTicket = mysql_query("
                         SELECT * FROM t_tickets WHERE id = ".$aRow[10]."
                         UNION ALL
                         SELECT * FROM t_ticket_3months WHERE id = ".$aRow[10]."
                         ");
            if(mysql_num_rows($sqlTicket)){
                $rowTicket   = mysql_fetch_array($sqlTicket);
                $ticketCode  = $rowTicket['code'];
                $ticketPrice = $rowTicket['total_amount'] - $rowTicket['discount_amount'] + $rowTicket['total_vat'] + $rowTicket['lucky_draw_fee'];
            } else {
                $sqlTmp = mysql_query("SELECT online_order_id FROM t_ticket_api_tmps WHERE id = ".$aRow[10]."
                                       UNION ALL 
                                       SELECT online_order_id FROM t_ticket_api_tmp_2024s WHERE id = ".$aRow[10]."");
                if(mysql_num_rows($sqlTmp)){
                    $rowTmp = mysql_fetch_array($sqlTmp);
                    $sqlTicket = mysql_query("
                                 SELECT * FROM t_tickets WHERE online_order_id = ".$rowTmp['online_order_id']."
                                 UNION ALL
                                 SELECT * FROM t_ticket_3months WHERE online_order_id = ".$rowTmp['online_order_id']."
                                 ");
                    if(mysql_num_rows($sqlTicket)){
                        $rowTicket   = mysql_fetch_array($sqlTicket);
                        $ticketCode  = $rowTicket['code'];
                        $ticketPrice = $rowTicket['total_amount'] - $rowTicket['discount_amount'] + $rowTicket['total_vat'] + $rowTicket['lucky_draw_fee'];
                    } 
                }
            }
        }
        if ($i == 0) {
            /* Special output formatting */
            if ($aRow[1] == $tmpId) {
                $row[] = '<b>' . ++$index . '</b>';
                $excelContent .= "\n" . $index;
            } else {
                $amount = $rowBg[0];
                $amountTotal += $rowBg[0];
                if (!is_null($aRow[1])) {
                    $tmpName = $aRow[1];
                } else {
                    $tmpName = 'General Agency';
                }
                $row[] = '<b class="colspanParent">' . $tmpName . '</b>';
                $excelContent .= "\n" . $tmpName;
                for ($j = 0; $j < count($aColumns) - 2; $j++) {
                    $row[] = '';
                    if($j > 0){
                        $excelContent .= "\t";
                    }
                }
                $row[] = '<b>' . number_format($amount, 2) . '</b>';
                $excelContent .= "\t".number_format($amount, 2);
                $output['aaData'][] = $row;
                // New Row
                $row = array();
                $row[] = '<b>' . ++$index . '</b>';
                $excelContent .= "\n" . $index;
            }
        } else if ($i == 1) {
            
        } else if ($aColumns[$i] == 'gl.created') {
            if ($aRow[$i] != '0000-00-00 00:00:00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i], "d/m/Y H:i:s");
                $excelContent .= "\t" . dateShort($aRow[$i], "d/m/Y H:i:s");
            } else {
                $row[] = '';
                $excelContent .= "\t";
            }
        } else if ($aColumns[$i] == '(gl.net_price + gl.vat_price)') {
            if($aRow[$i] > 0 && !empty($ticketCode)){
                $row[] = number_format($aRow[$i] * -1, 2);
                $excelContent .= "\t".number_format($aRow[$i] * -1, 2);
            } else {
                $row[] = "-";
                $excelContent .= "\t-";
            }
        } else if ($aColumns[$i] == 'gl.bonus') {
            if($aRow[$i] > 0){
                $row[] = number_format($aRow[$i], 2);
                $excelContent .= "\t".number_format($aRow[$i], 2);
            } else {
                $row[] = "-";
                $excelContent .= "\t-";
            }
        } else if ($aColumns[$i] == 'gl.credit') {
            if($ticketPrice > 0){
                $row[] = number_format($ticketPrice, 2);
                $excelContent .= "\t".number_format($ticketPrice, 2);
            } else {
                $row[] = "-";
                $excelContent .= "\t-";
            }
        } else if ($aColumns[$i] == 'IF(gl.credit>0,gl.credit,gl.debit*-1)') {
            $row[] = number_format($aRow[$i], 2);
            $excelContent .= "\t".number_format($aRow[$i], 2);
            $amount += $aRow[$i];
            $amountTotal += $aRow[$i];
        } else if ($aColumns[$i] == 'gl.reference') {
            if(!empty($ticketCode)){
                $row[] = $ticketCode;
                $excelContent .= "\t".$ticketCode;
            } else {
                $row[] = $aRow[$i];
                $excelContent .= "\t".$aRow[$i];
            }
        } else if ($aColumns[$i] == 'gl.t_agency_id' || $aColumns[$i] == 'gl.t_ticket_id') {
            
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
            $excelContent .= "\t".$aRow[$i];
        }
    }
    $row[] = number_format($amount, 2);
    $excelContent .= "\t".number_format($amount, 2);
    $output['aaData'][] = $row;
    $tmpId = $aRow[1];
}
if (mysql_num_rows($rResult)) {
    $rowTotal = array();
    $rowTotal[] = '<b class="colspanParent">Total ' . $tmpName . '</b>';
    $excelContent .= "\nTotal ".$tmpName;
    for ($i = 0; $i < count($aColumns) - 2; $i++) {
        $rowTotal[] = '';
        if($i > 1){
            $excelContent .= "\t";
        }
    }
    $rowTotal[] = '<b>' . number_format($amount, 2) . '</b>';
    $rowTotal[] = '<b>' . number_format($amount, 2) . '</b>';
    $excelContent .= "\t". number_format($amount, 2);
    $excelContent .= "\t". number_format($amount, 2);
    $output['aaData'][] = $rowTotal;

    // $rowTotal = array();
    // $rowTotal[] = '<b class="colspanParent">GRAND TOTAL</b>';
    // for ($i = 0; $i < count($aColumns) - 3; $i++) {
    //     $rowTotal[] = '';
    // }
    // $rowTotal[] = '<b>' . number_format($amountTotal, 2) . '</b>';
    // $rowTotal[] = '<b>' . number_format($amountTotal, 2) . '</b>';
    // $output['aaData'][] = $rowTotal;
}
// Check Agency Balance
$condition = "1";
if ($data[3] != '') {
    if(!empty($agencyResult)){
        $condition = "1 = 2";
    } else {
        $condition = "id = ".$data[3];   
    }
} else {
    if(!empty($agencyResult)){
        $agencyId = implode(",", $agencyResult);
        $condition = "id NOT IN (".$agencyId.")";   
    }
}
$sqlAgency = mysql_query("SELECT id, t_agents.name AS name FROM t_agents WHERE ".$condition." AND offline_project_id = 1 AND status = 1 AND payment = 1");
while($rowAge = mysql_fetch_array($sqlAgency)){
    // Beging Balance
    $forwardBalance = 0;
    $sqlBg = mysql_query("SELECT SUM(credit - debit) FROM `agency_balances` WHERE t_agency_id = ".$rowAge['id']);
    if(mysql_num_rows($sqlBg)){
        $rowBg = mysql_fetch_array($sqlBg);
        $forwardBalance = $rowBg[0];
    }
    $rowTotal = array();
    $rowTotal[] = '<b class="colspanParent">' . $rowAge['name'] . '</b>';
    $excelContent .= "\n".$rowAge['name'];
    for ($i = 0; $i < count($aColumns) - 3; $i++) {
        $rowTotal[] = '';
        if($i > 0){
            $excelContent .= "\t";
        }
    }
    $rowTotal[] = '<b>' . number_format($forwardBalance, 2) . '</b>';
    $excelContent .= "\t".number_format($forwardBalance, 2);
    $output['aaData'][] = $rowTotal;
}

echo json_encode($output);

$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);
?>