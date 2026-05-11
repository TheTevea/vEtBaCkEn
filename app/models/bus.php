<?php
class Bus extends AppModel {
    var $name = 'Bus';
    var $belongsTo = array(
        'BusType' => array(
            'className' => 'BusType',
            'foreignKey' => 'bus_type_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>