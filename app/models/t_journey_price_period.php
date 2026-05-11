<?php
class TJourneyPricePeriod extends AppModel {
    var $name = 'TJourneyPricePeriod';
    var $belongsTo = array(
        'TTransportationType' => array(
            'className' => 'TTransportationType',
            'foreignKey' => 't_transportation_type_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'MainBranch' => array(
            'className' => 'MainBranch',
            'foreignKey' => 'main_branch_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'created_by',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

}
?>

