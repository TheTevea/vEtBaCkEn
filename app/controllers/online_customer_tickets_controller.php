<?php
class OnlineCustomerTicketsController extends AppController {

    var $name = 'OnlineCustomerTickets';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Online Customer Ticket', 'Dashboard');
    }

    function ajax() {
        $this->layout = 'ajax';
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Online Customer Ticket', 'View', $id);
        $this->data = $this->OnlineCustomerTicket->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('telephone', 'online_customer_tickets', $this->data['OnlineCustomerTicket']['telephone'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Online Customer Ticket', 'Save Add New Online Customer Ticket (Name has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $code = "VET-".$this->Helper->generatePackageCode(10);
                $this->OnlineCustomerTicket->create();
                $this->data['OnlineCustomerTicket']['code']     = $code;
                $this->data['OnlineCustomerTicket']['created']  = $dateNow;
                $this->data['OnlineCustomerTicket']['created_by'] = $user['User']['id'];
                $this->data['OnlineCustomerTicket']['is_active'] = 1;
                if($this->OnlineCustomerTicket->save($this->data)) {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Online Customer Ticket', 'Save Add New');
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                }else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Online Customer Ticket', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $mainBranches = ClassRegistry::init('MainBranch')->find('list', array("conditions" => array("MainBranch.is_active = 1")));
        $this->set(compact('mainBranches'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('telephone', 'online_customer_tickets', $id, $this->data['OnlineCustomerTicket']['telephone'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Online Customer Ticket', 'Save Edit Online Customer Ticket(Name has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['OnlineCustomerTicket']['modified'] = $dateNow;
                $this->data['OnlineCustomerTicket']['modified_by'] = $user['User']['id'];
                if($this->OnlineCustomerTicket->Save($this->data)){  
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;                  
                    exit();                                
                }else{
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit; 
                }    
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Online Customer Ticket', 'Edit Online Customer Ticket', $id);
            $this->data = $this->OnlineCustomerTicket->read(null, $id);
            $mainBranches = ClassRegistry::init('MainBranch')->find('list', array("conditions" => array("MainBranch.is_active = 1")));
            $this->set(compact('mainBranches'));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user     = $this->getCurrentUser();
        mysql_query("UPDATE `online_customer_tickets` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->Helper->saveUserActivity($user['User']['id'], 'Online Customer Ticket', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit; 
    }

}

?>