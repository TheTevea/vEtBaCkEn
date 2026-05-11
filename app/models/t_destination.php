<?php
class TDestination extends AppModel {
    var $name = 'TDestination';
    var $belongsTo = array(
        'TDestinationGroup' => array(
            'className' => 'TDestinationGroup',
            'foreignKey' => 't_destination_group_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Province' => array(
            'className' => 'Province',
            'foreignKey' => 'province_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Country' => array(
            'className' => 'Country',
            'foreignKey' => 'country_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

}
?>