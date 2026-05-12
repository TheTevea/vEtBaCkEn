<?php
include("includes/function.php");

// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowChangeStatus = checkAccess($user['User']['id'], $this->params['controller'], 'status');

$aColumns = array('d.id', 'p.name', 'd.fixed_discount', 'd.percent', 'd.start_date', 'd.end_date', 'd.status', 'd.created_at');
$sIndexColumn = "d.id";
$sTable = "mini_app_partner_discount d LEFT JOIN mini_app_partner p ON p.id = d.mini_app_partner_id";

$sLimit = "";
if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
    $sLimit = "LIMIT " . mysql_real_escape_string($_GET['iDisplayStart']) . ", " .
            mysql_real_escape_string($_GET['iDisplayLength']);
}

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

$sWhere = "";
if ($_GET['sSearch'] != "") {
    $sWhere = "WHERE (";
    for ($i = 0; $i < count($aColumns); $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

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

$sQuery = "
        SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
        FROM   $sTable
        $sWhere
        $sOrder
        $sLimit
";
$rResult = mysql_query($sQuery) or die(mysql_error());

$sQuery = "SELECT FOUND_ROWS()";
$rResultFilterTotal = mysql_query($sQuery) or die(mysql_error());
$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
$iFilteredTotal = $aResultFilterTotal[0];

$sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM $sTable";
$rResultTotal = mysql_query($sQuery) or die(mysql_error());
$aResultTotal = mysql_fetch_array($rResultTotal);
$iTotal = $aResultTotal[0];

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
            $row[] = ++$index;
        } else if ($aColumns[$i] == 'd.fixed_discount') {
            $row[] = number_format($aRow[$i], 2).' $';
        } else if ($aColumns[$i] == 'd.percent') {
            $row[] = number_format($aRow[$i], 2).' %';
        } else if ($aColumns[$i] == 'd.start_date' || $aColumns[$i] == 'd.end_date' || $aColumns[$i] == 'd.created_at') {
            $row[] = dateShort($aRow[$i], "d/m/Y");
        } else if ($aColumns[$i] == 'd.status') {
            $row[] = $aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE;
        } else if ($aColumns[$i] != ' ') {
            $row[] = $aRow[$i];
        }
    }
    $row[] =
            ($allowView ? '<a href="" class="btnViewMiniAppPartnerDiscount" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowEdit ? '<a href="" class="btnEditMiniAppPartnerDiscount" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
            ($allowChangeStatus ? '<a href="" class="btnChangeStatusMiniAppPartnerDiscount" rel="' . $aRow[0] . '" name="' . $aRow[1] . '" status="' . $aRow[6] . '"><img alt="Change Status" onmouseover="Tip(\'Change Status\')" src="' . $this->webroot . 'img/button/active.png" /></a> ' : '') .
            ($allowDelete ? '<a href="" class="btnDeleteMiniAppPartnerDiscount" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>
