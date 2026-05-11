<?php

class BusRentalsController extends AppController {

    var $name = 'BusRentals';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Rental', 'Dashboard');
    }

    function ajax($from = 'all', $to = 'all', $bus = 'all', $date = 'all') {
        $this->layout = 'ajax';
        $this->set(compact('from', 'to', 'bus', 'date'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Bus Rental', 'View', $id);
        $this->data = $this->BusRental->read(null, $id);
    }

}

?>