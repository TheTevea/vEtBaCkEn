<?php
class BusType extends AppModel {
    var $name = 'BusType';
    var $belongsTo = array(
        'TTransportationType' => array(
            'className' => 'TTransportationType',
            'foreignKey' => 't_transportation_type_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>