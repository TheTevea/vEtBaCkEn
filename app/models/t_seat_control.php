<?php
class TSeatControl extends AppModel {
    var $name = 'TSeatControl';
    var $belongsTo = array(
        'TTicket' => array(
            'className' => 'TTicket',
            'foreignKey' => 't_ticket_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TTicketDetail' => array(
            'className' => 'TTicketDetail',
            'foreignKey' => 't_ticket_detail_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

}
?>