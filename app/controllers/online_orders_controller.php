<?php

class OnlineOrdersController extends AppController {

    var $uses = 'OnlineOrders';
    var $components = array('Helper');

    function index(){
        $this->layout = 'ajax';  
    }

    function ajax($type = 'all', $status = 'all', $date = 'all', $payment = 'all'){
        $this->layout = 'ajax';
        $this->set(compact('type', 'status', 'date', 'payment'));
    }

    function viewApiResponse($id){
        $this->layout = 'ajax';  
        if(empty($id)){
            echo "Invalid Id";
            exit;
        }
        $this->set(compact('id'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Online Order', 'View', $id);
        $this->set(compact('id'));
    }
}

?>