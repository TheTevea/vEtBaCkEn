<?php

class ExpensesController extends AppController {

    var $name = 'Expenses';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'Dashborad');
    }

    function ajax($status = 'all', $show = 1, $date = '') {
        $this->layout = 'ajax';
        $this->set(compact('status', 'show', 'date'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'View', $id);
        $this->data = $this->Expense->read(null, $id);
        $expenseDetails = ClassRegistry::init('ExpenseDetail')->find('all', array("conditions" => array("ExpenseDetail.expense_id" => $id)));
        $this->set(compact('expenseDetails'));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if($this->data['Expense']['date'] != "" && $this->data['Expense']['date'] != '0000-00-00'){
                $r = 0;
                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                // Insert New Expense
                $this->Expense->create();
                $this->data['Expense']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Expense']['created'] = $dateNow;
                $this->data['Expense']['created_by'] = $user['User']['id'];
                $this->data['Expense']['status']     = 1;
                if ($this->Expense->save($this->data)) {
                    $expenseId = $this->Expense->id;
                    $branch = ClassRegistry::init('Branch')->find('first', array("conditions" => array("Branch.id" => $this->data['Expense']['branch_id'])));
                    // Get Module Code
                    $modCode = $this->Helper->getModuleCode($branch['Branch']['code']."-EP", $expenseId, 'code', 'expenses', 'status != -1  AND branch_id = '.$this->data['Expense']['branch_id']);
                    // Updaet Module Code
                    mysql_query("UPDATE expenses SET code = '".$modCode."' WHERE id = ".$expenseId);
                    // Convert to SYNC
                    $restCode[$r]['sys_code']  = $this->data['Expense']['sys_code'];
                    $restCode[$r]['branch_id'] = "(SELECT id FROM branches WHERE sys_code = '".$branch['Branch']['sys_code']."' LIMIT 1)";
                    $restCode[$r]['code'] = $modCode;
                    $restCode[$r]['date'] = $this->data['Expense']['date'];
                    $restCode[$r]['total_amount'] = $this->data['Expense']['total_amount'];
                    $restCode[$r]['note'] = $this->data['Expense']['note'];
                    $restCode[$r]['created'] = $this->data['Expense']['created'];
                    $restCode[$r]['created_by'] = $this->data['Expense']['created_by'];
                    $restCode[$r]['status']   = $this->data['Expense']['status'];
                    $restCode[$r]['dbtodo'] = 'expenses';
                    $restCode[$r]['actodo'] = 'is';
                    $r++;
                    // Insert New Expense Detail
                    $this->loadModel("ExpenseDetail");
                    for ($i = 0; $i < sizeof($_POST['reference']); $i++) {
                        $rate = 1;
                        $exchangeId = 0;
                        if($branch['Branch']['currency_center_id'] != $_POST['currency'][$i]){
                            $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array(
                                "conditions" => array("DATE(ExchangeRate.created)" => $this->data['Expense']['date'], "ExchangeRate.branch_id" => $this->data['Expense']['branch_id'], "ExchangeRate.currency_center_id" => $_POST['currency'][$i]),
                                "order" => array("ExchangeRate.id DESC")
                            ));
                            if(empty($lastExchangeRate)){
                                $rate = 0;
                            } else {
                                $rate = $lastExchangeRate['ExchangeRate']['rate_to_sell'];
                            }
                        }
                        $expenseDetail = array();
                        $this->ExpenseDetail->create();
                        $expenseDetail['ExpenseDetail']['expense_id']      = $expenseId;
                        $expenseDetail['ExpenseDetail']['expense_type_id'] = $_POST['expense_type'][$i];
                        $expenseDetail['ExpenseDetail']['exchange_rate_id'] = $exchangeId;
                        $expenseDetail['ExpenseDetail']['rate']        = $rate;
                        $expenseDetail['ExpenseDetail']['reference']   = $_POST['reference'][$i];
                        $expenseDetail['ExpenseDetail']['description'] = $_POST['description'][$i];
                        $expenseDetail['ExpenseDetail']['amount']      = $_POST['amount'][$i];
                        $expenseDetail['ExpenseDetail']['currency_center_id'] = $_POST['currency'][$i];
                        $expenseDetail['ExpenseDetail']['qty']          = $_POST['qty'][$i];
                        $expenseDetail['ExpenseDetail']['total_amount'] = $_POST['total_amount'][$i];
                        $this->ExpenseDetail->save($expenseDetail);
                        // Convert to SYNC
                        $sqlExType = mysql_query("SELECT sys_code FROM expense_types WHERE id = ".$_POST['expense_type'][$i]);
                        $rowExType = mysql_fetch_array($sqlExType);
                        if($exchangeId > 0){
                            $sqlExRate = mysql_query("SELECT sys_code FROM exchange_rates WHERE id = ".$exchangeId);
                            $rowExRate = mysql_fetch_array($sqlExRate);
                            $rateSync = "(SELECT id FROM exchange_rates WHERE sys_code = '".$rowExRate['sys_code']."' LIMIT 1)";
                        } else {
                            $rateSync = 0;
                        }
                        $restCode[$r]['expense_id'] = "(SELECT id FROM expenses WHERE sys_code = '".$this->data['Expense']['sys_code']."' LIMIT 1)";
                        $restCode[$r]['expense_type_id']  = "(SELECT id FROM expense_types WHERE sys_code = '".$rowExType['sys_code']."' LIMIT 1)";
                        $restCode[$r]['exchange_rate_id'] = $rateSync;
                        $restCode[$r]['rate'] = $rate;
                        $restCode[$r]['reference'] = $_POST['reference'][$i];
                        $restCode[$r]['description'] = $_POST['description'][$i];
                        $restCode[$r]['amount'] = $_POST['amount'][$i];
                        $restCode[$r]['currency_center_id'] = $_POST['currency'][$i];
                        $restCode[$r]['qty'] = $_POST['qty'][$i];
                        $restCode[$r]['total_amount']   = $_POST['total_amount'][$i];
                        $restCode[$r]['dbtodo'] = 'expense_details';
                        $restCode[$r]['actodo'] = 'is';
                        $r++;
                    }
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode);
                    $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'Save Add New', $expenseId);
                    $result['id'] = $expenseId;
                    $result['error'] = 0;
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'Save Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'Save Add New (Error)');
                $result['error'] = 1;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'Add New');
        $branches = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1 AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")")));
        $expenseTypes = ClassRegistry::init('ExpenseType')->find("all", array("conditions" => array("ExpenseType.is_active = 1")));
        $this->set(compact("expenseTypes", "branches"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if($this->data['Expense']['date'] != "" && $this->data['Expense']['date'] != '0000-00-00'){
                $r = 0;
                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $expense = $this->Expense->read(null, $id);
                // Update Status Debit Note Edit
                $this->Expense->updateAll(
                        array('Expense.status' => "-1", "modified_by"=>$user['User']['id']), array('Expense.id' => $id)
                );
                // Convert to SYNC
                $restCode[$r]['status'] = -1;
                $restCode[$r]['modified'] = $dateNow;
                $restCode[$r]['modified_by'] = $user['User']['id'];
                $restCode[$r]['dbtodo'] = 'expenses';
                $restCode[$r]['actodo'] = 'ut';
                $restCode[$r]['con']    = "sys_code = '".$expense['ExpenseType']['sys_code']."'";
                $r++;
                // Insert New Expense
                $this->Expense->create();
                $expenseInput = array();
                $expenseInput['Expense']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $expenseInput['Expense']['branch_id']  = $this->data['Expense']['branch_id'];
                $expenseInput['Expense']['code'] = $expense['Expense']['code'];
                $expenseInput['Expense']['date'] = $this->data['Expense']['date'];
                $expenseInput['Expense']['note'] = $this->data['Expense']['note'];
                $expenseInput['Expense']['total_amount'] = $this->data['Expense']['total_amount'];
                $expenseInput['Expense']['created']    = $expense['Expense']['created'];
                $expenseInput['Expense']['created_by'] = $expense['Expense']['created_by'];
                $expenseInput['Expense']['edited']     = $dateNow;
                $expenseInput['Expense']['edited_by']  = $user['User']['id'];
                $expenseInput['Expense']['modified']   = $dateNow;
                $expenseInput['Expense']['status']     = 1;
                if ($this->Expense->save($expenseInput)) {
                    $expenseId = $this->Expense->id;
                    if($this->data['Expense']['branch_id'] != $expense['Expense']['branch_id']){
                        // Get Module Code
                        $modCode = $this->Helper->getModuleCode($this->data['Expense']['branch_id']."EP", $expenseId, 'code', 'expenses', 'status != -1  AND branch_id = '.$this->data['Expense']['branch_id']);
                        // Updaet Module Code
                        mysql_query("UPDATE expenses SET code = '".$modCode."' WHERE id = ".$expenseId);
                    } else {
                        $modCode = $expenseInput['Expense']['code'];
                    }
                    $branch = ClassRegistry::init('Branch')->find('first', array("conditions" => array("Branch.id" => $this->data['Expense']['branch_id'])));
                    // Convert to SYNC
                    $restCode[$r]['branch_id'] = "(SELECT id FROM branches WHERE sys_code = '".$branch['Branch']['sys_code']."' LIMIT 1)";
                    $restCode[$r]['sys_code'] = $expenseInput['Expense']['sys_code'];
                    $restCode[$r]['code'] = $modCode;
                    $restCode[$r]['date'] = $expenseInput['Expense']['date'];
                    $restCode[$r]['total_amount'] = $expenseInput['Expense']['total_amount'];
                    $restCode[$r]['note'] = $expenseInput['Expense']['note'];
                    $restCode[$r]['created'] = $expenseInput['Expense']['created'];
                    $restCode[$r]['created_by'] = $expenseInput['Expense']['created_by'];
                    $restCode[$r]['edited']    = $expenseInput['Expense']['edited'];
                    $restCode[$r]['edited_by'] = $expenseInput['Expense']['edited_by'];
                    $restCode[$r]['modified']  = $expenseInput['Expense']['modified'];
                    $restCode[$r]['status'] = $expenseInput['Expense']['status'];
                    $restCode[$r]['dbtodo'] = 'expenses';
                    $restCode[$r]['actodo'] = 'is';
                    $r++;
                    // Insert New Expense Detail
                    $this->loadModel("ExpenseDetail");
                    for ($i = 0; $i < sizeof($_POST['reference']); $i++) {
                        $rate = 1;
                        $exchangeId = 0;
                        if($branch['Branch']['currency_center_id'] != $_POST['currency'][$i]){
                            $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array(
                                "conditions" => array("DATE(ExchangeRate.created)" => $this->data['Expense']['date'], "ExchangeRate.branch_id" => $this->data['Expense']['branch_id'], "ExchangeRate.currency_center_id" => $_POST['currency'][$i]),
                                "order" => array("ExchangeRate.id DESC")
                            ));
                            if(empty($lastExchangeRate)){
                                $rate = 0;
                            } else {
                                $rate = $lastExchangeRate['ExchangeRate']['rate_to_sell'];
                            }
                        }
                        $expenseDetail = array();
                        $this->ExpenseDetail->create();
                        $expenseDetail['ExpenseDetail']['expense_id']       = $expenseId;
                        $expenseDetail['ExpenseDetail']['expense_type_id']  = $_POST['expense_type'][$i];
                        $expenseDetail['ExpenseDetail']['exchange_rate_id'] = $exchangeId;
                        $expenseDetail['ExpenseDetail']['rate']        = $rate;
                        $expenseDetail['ExpenseDetail']['reference']   = $_POST['reference'][$i];
                        $expenseDetail['ExpenseDetail']['description'] = $_POST['description'][$i];
                        $expenseDetail['ExpenseDetail']['amount']      = $_POST['amount'][$i];
                        $expenseDetail['ExpenseDetail']['currency_center_id'] = $_POST['currency'][$i];
                        $expenseDetail['ExpenseDetail']['qty']          = $_POST['qty'][$i];
                        $expenseDetail['ExpenseDetail']['total_amount'] = $_POST['total_amount'][$i];
                        $this->ExpenseDetail->save($expenseDetail);
                        // Convert to SYNC
                        $sqlExType = mysql_query("SELECT sys_code FROM expense_types WHERE id = ".$_POST['expense_type'][$i]);
                        $rowExType = mysql_fetch_array($sqlExType);
                        if($exchangeId > 0){
                            $sqlExRate = mysql_query("SELECT sys_code FROM exchange_rates WHERE id = ".$exchangeId);
                            $rowExRate = mysql_fetch_array($sqlExRate);
                            $rateSync = "(SELECT id FROM exchange_rates WHERE sys_code = '".$rowExRate['sys_code']."' LIMIT 1)";
                        } else {
                            $rateSync = 0;
                        }
                        $restCode[$r]['expense_id'] = "(SELECT id FROM expenses WHERE sys_code = '".$expenseInput['Expense']['sys_code']."' LIMIT 1)";
                        $restCode[$r]['expense_type_id']  = "(SELECT id FROM expense_types WHERE sys_code = '".$rowExType['sys_code']."' LIMIT 1)";
                        $restCode[$r]['exchange_rate_id'] = $rateSync;
                        $restCode[$r]['rate'] = $rate;
                        $restCode[$r]['reference'] = $_POST['reference'][$i];
                        $restCode[$r]['description'] = $_POST['description'][$i];
                        $restCode[$r]['amount'] = $_POST['amount'][$i];
                        $restCode[$r]['currency_center_id'] = $_POST['currency'][$i];
                        $restCode[$r]['qty'] = $_POST['qty'][$i];
                        $restCode[$r]['total_amount']   = $_POST['total_amount'][$i];
                        $restCode[$r]['dbtodo'] = 'expense_details';
                        $restCode[$r]['actodo'] = 'is';
                        $r++;
                    }
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode);
                    $result['id'] = $expenseId;
                    $result['error'] = 0;
                    echo json_encode($result);
                    exit;
                } else {
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            } else {
                $result['error'] = 1;
                echo json_encode($result);
                exit;
            }
        }
        if (empty($this->data)) {
            $this->data = $this->Expense->read(null, $id);
            $branches = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1 AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")")));
            $expenseTypes = ClassRegistry::init('ExpenseType')->find("all", array("conditions" => array("ExpenseType.is_active = 1")));
            $expenseDetails = ClassRegistry::init('ExpenseDetail')->find("all", array("conditions" => array("ExpenseDetail.expense_id" => $id)));
            $this->set(compact("expenseDetails", "expenseTypes", "branches"));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $r = 0;
        $restCode = array();
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->Expense->read(null, $id);
        $this->Expense->updateAll(
                array('Expense.status' => 0, 'Expense.modified_by' => $user['User']['id']),
                array('Expense.id' => $id)
        );
        // Convert to SYNC
        $restCode[$r]['status']   = 0;
        $restCode[$r]['modified'] = $dateNow;
        $restCode[$r]['modified_by'] = $user['User']['id'];
        $restCode[$r]['dbtodo'] = 'expenses';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "sys_code = '".$this->data['ExpenseType']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function close($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $r = 0;
        $restCode = array();
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->Expense->read(null, $id);
        $this->Expense->updateAll(
                array('Expense.status' => 2, 'Expense.modified_by' => $user['User']['id']),
                array('Expense.id' => $id)
        );
        // Convert to SYNC
        $restCode[$r]['status']   = 2;
        $restCode[$r]['modified'] = $dateNow;
        $restCode[$r]['modified_by'] = $user['User']['id'];
        $restCode[$r]['dbtodo'] = 'expenses';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "sys_code = '".$this->data['ExpenseType']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $filename = "public/report/expense_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Expense' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_DATE . "\t" . TABLE_CODE. "\t" . TABLE_VENDOR. "\t" . TABLE_EMPLOYEE. "\t" . TABLE_STATUS;
            $query = mysql_query('SELECT expenses.date, expenses.code, expenses.total_amount, expenses.note, expenses.status FROM expenses WHERE expenses.status > 0 ORDER BY expenses.code ASC');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $status = '';
                if($data[6] == 1){
                    $status = 'Open';
                }else{
                    $status = 'Closed';
                }
                $excelContent .= "\n" . $index++ . "\t" . $this->Helper->dateShort($data[0]) . "\t" . $data[1] . "\t" . $data[2] . "\t" . $data[3]. "\t" . $status;
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }
    
    function printInvoice($id = null) {
        if (!empty($id)) {
            $this->layout = 'ajax';
            $this->data = $this->Expense->read(null, $id);
            $expenseDetails = ClassRegistry::init('ExpenseDetail')->find('all', array("conditions" => array("ExpenseDetail.expense_id" => $id)));
            $this->set(compact('expenseDetails'));
        } else {
            exit;
        }
    }

}

?>