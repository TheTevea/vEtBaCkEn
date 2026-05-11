<?php
include("includes/function.php");
$this->element('check_access');
$allowAdd = checkAccess($user['User']['id'], $this->params['controller'], 'add');
$index = 0;
$sqlComCurrency = mysql_query("SELECT name, symbol FROM currency_centers WHERE id = ".$company['Company']['currency_center_id']);
$rowComCurrency = mysql_fetch_array($sqlComCurrency);
$sqlCurrency = mysql_query("SELECT company_currencies.id AS id, companies.name AS com_name, currency_centers.name AS curr_name, currency_centers.symbol AS curr_symbol, company_currencies.rate_to_sell AS rate_sell, company_currencies.rate_to_change AS rate_change, company_currencies.modified AS modified, company_currencies.company_id, company_currencies.currency_center_id FROM company_currencies INNER JOIN companies ON companies.id = company_currencies.company_id INNER JOIN currency_centers ON currency_centers.id = company_currencies.currency_center_id WHERE company_currencies.is_active = 1 AND company_currencies.company_id = '".$companyId."' ORDER BY company_currencies.id ASC");
if(mysql_num_rows($sqlCurrency)){    
    while($rowCurrency = mysql_fetch_array($sqlCurrency)){
?>
<tr>
    <td class="first"><?php echo ++$index; ?></td>
    <td><?php echo $rowCurrency['com_name']; ?></td>
    <td><?php echo $rowComCurrency['name']." (".$rowComCurrency['symbol'].")"; ?></td>
    <td>1.00</td>
    <td><?php echo $rowCurrency['curr_name']." (".$rowCurrency['curr_symbol'].")"; ?></td>
    <td><input type="text" id="rateSell<?php echo $index; ?>" class="rateSell" exrate="<?php echo $rowCurrency['id']; ?>" style="width: 90%;" value="<?php echo $rowCurrency['rate_sell']; ?>" <?php if(!$allowAdd){ ?>readonly="readonly"<?php } ?> /></td>
    <td><input type="text" id="rateChange<?php echo $index; ?>" class="rateChange" exrate="<?php echo $rowCurrency['id']; ?>" style="width: 90%;" value="<?php echo $rowCurrency['rate_change']; ?>" <?php if(!$allowAdd){ ?>readonly="readonly"<?php } ?> /></td>
    <td><?php echo dateShort($rowCurrency['modified'], "d/m/Y H:i:s"); ?></td>
    <td><?php echo '<a href="" class="btnViewExchangeHistory" com-id="' . $rowCurrency['company_id'] . '" currency-center-id="' . $rowCurrency['currency_center_id'] . '" name="' . $rowComCurrency['name'] . ' (' . $rowCurrency['curr_symbol'] . ')' . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a>' ?></td>
</tr>
<?php
    }
} else {
?>
<tr>
    <td colspan="8" class="dataTables_empty first"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
</tr>
<?php
}
?>