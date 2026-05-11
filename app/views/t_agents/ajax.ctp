<?php

// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowPopUp  = checkAccess($user['User']['id'], $this->params['controller'], 'popBalance');
$allowStatus = checkAccess($user['User']['id'], $this->params['controller'], 'status');
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    't_agents.id',
    'main_branches.name',
    't_agents.code', 
    't_agents.name',
    't_agents.e_mail',
    't_agents.telephone',
    't_agents.commission',
    'IFNULL((SELECT SUM(credit - debit) FROM `agency_balances` WHERE t_agency_id = t_agents.id), 0)',
    't_agents.max_balance',
    't_agents.type',
    't_agents.payment',
    't_agents.status',
    'users.username');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "t_agents.id";

/* DB table to use */
$sTable = "t_agents LEFT JOIN main_branches ON main_branches.id = t_agents.main_branch_id LEFT JOIN users ON users.id = t_agents.user_id";

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
        if($aColumns[$i] == 't_agents.code' || $aColumns[$i] == 't_agents.name' || $aColumns[$i] == 'users.username'){
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
$condition = "t_agents.status > 0 AND t_agents.offline_project_id = 1";
if($companyId != 'all'){
    $condition .= " AND t_agents.id IN (SELECT t_agent_id FROM t_agent_companies WHERE company_id = ".$companyId.")";
} else {
    $condition .= " AND t_agents.id IN (SELECT t_agent_id FROM t_agent_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
}
if($branchId != 'all'){
    $condition .= " AND t_agents.id IN (SELECT t_agent_id FROM t_agent_branches WHERE branch_id = ".$branchId.")";
} else {
    $condition .= " AND t_agents.id IN (SELECT t_agent_id FROM t_agent_branches WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
}
if($type != 'all'){
    $condition .= " AND t_agents.type = ".$type;
}
if($group != 'all'){
    $condition .= " AND t_agents.t_agent_type_id = ".$group;
}
if($mainBranchId != 'all'){
    $condition .= " AND t_agents.main_branch_id = ".$mainBranchId;
}
if (!eregi("WHERE", $sWhere)) {
    $sWhere .= "WHERE " . $condition;
} else {
    $sWhere .= " AND " . $condition;
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
        } else if ($aColumns[$i] == 't_agents.commission' || $aColumns[$i] == 'IFNULL((SELECT SUM(credit - debit) FROM `agency_balances` WHERE t_agency_id = t_agents.id), 0)') {
            $row[] = number_format($aRow[$i], 2);
        } else if ($aColumns[$i] == 't_agents.type') {
            $type = '';
            if($aRow[$i] == 1){
                $type = TABLE_ONLINE;
            } else if($aRow[$i] == 2){
                $type = TABLE_OFFLINE;
            } else {
                $type = TABLE_API;
            }
            $row[] = $type;
        } else if ($aColumns[$i] == 't_agents.payment') {
            $payment = '';
            if($aRow[$i] == 1){
                $payment = 'Prepaid';
            } else if($aRow[$i] == 2){
                $payment = 'Postpaid';
            }
            $row[] = $payment;
        } else if ($aColumns[$i] == 't_agents.max_balance') {
            if($aRow[10] == 2){ // Payment = PostPaid
                $row[] = number_format($aRow[$i], 2);
            } else {
                $row[] = "-";
            }
        } else if ($aColumns[$i] == 't_agents.status') {
            if($aRow[$i] == 1){
                $row[] = TABLE_ACTIVE;
            } else if($aRow[$i] == 2){
                $row[] = TABLE_INACTIVE;
            } else {
                $row[] = ACTION_VOID;
            }
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] =
            ($allowView ? '<a href="" class="btnViewTAgent" rel="' . $aRow[0] . '" name="' . $aRow[3] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowPopUp && $aRow[0] != 55 && $aRow[11] == 1 && ($aRow[10] == 1 || $aRow[10] == 2) ? '<a href="" class="btnTopupTAgent" rel="' . $aRow[0] . '" name="' . $aRow[3] . '"><img alt="Topup Balance" onmouseover="Tip(\'Topup Balance\')" src="' . $this->webroot . 'img/button/coins.png" /></a> ' : '') .
            ($allowStatus && $aRow[0] != 55 && $aRow[11] >= 1 ? '<a href="" class="btnChangeStatusTAgent" rel="' . $aRow[0] . '" name="' . $aRow[2] . ' ' . $aRow[3] . '"><img alt="Change Status" onmouseover="Tip(\'Change Status\')" src="' . $this->webroot . 'img/button/cycle.png" /></a> ' : ' ') .
            ($allowEdit && $aRow[0] != 55 && $aRow[11] == 1 ? '<a href="" class="btnEditTAgent" rel="' . $aRow[0] . '" name="' . $aRow[3] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
            ($allowDelete && $aRow[0] != 55 && $aRow[11] > 0 ? '<a href="" class="btnDeleteTAgent" rel="' . $aRow[0] . '" name="' . $aRow[3] . '"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>