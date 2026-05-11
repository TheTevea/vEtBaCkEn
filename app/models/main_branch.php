<?php
class MainBranch extends AppModel {
    var $name = 'MainBranch';
    var $belongsTo = array(
        'TDestination' => array(
            'className' => 'TDestination',
            'foreignKey' => 't_destination_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

}
?>