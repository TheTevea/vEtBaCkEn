<?php
class Customer extends AppModel {
    var $name = 'Customer';
    
    var $belongsTo = array(
        'PaymentTerm' => array(
            'className' => 'PaymentTerm',
            'foreignKey' => 'payment_term_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Street' => array(
            'className' => 'Street',
            'foreignKey' => 'street_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

}
?>