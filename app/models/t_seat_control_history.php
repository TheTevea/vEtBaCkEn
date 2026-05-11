<?php
class TSeatControlHistory extends AppModel {
    var $name = 'TSeatControlHistory';
    var $belongsTo = array(
        'TTicket' => array(
            'className' => 'TTicket',
            'foreignKey' => 't_ticket_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

}
?>