<?php
class BusSchedule extends AppModel {
    var $name = 'BusSchedule';
    var $belongsTo = array(
        'TTransportationType' => array(
            'className' => 'TTransportationType',
            'foreignKey' => 't_transportation_type_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Bus' => array(
            'className' => 'Bus',
            'foreignKey' => 'bus_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>