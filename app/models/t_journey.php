<?php
class TJourney extends AppModel {
    var $name = 'TJourney';
    var $belongsTo = array(
        'CurrencyCenter' => array(
            'className' => 'CurrencyCenter',
            'foreignKey' => 'currency_center_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Company' => array(
            'className' => 'Company',
            'foreignKey' => 'company_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Branch' => array(
            'className' => 'Branch',
            'foreignKey' => 'branch_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TJourneyType' => array(
            'className' => 'TJourneyType',
            'foreignKey' => 't_journey_type_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TTransportationType' => array(
            'className' => 'TTransportationType',
            'foreignKey' => 't_transportation_type_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TRoute' => array(
            'className' => 'TRoute',
            'foreignKey' => 't_route_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TDestination' => array(
            'className' => 'TDestination',
            'foreignKey' => 't_destination_from_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TDepartureTime' => array(
            'className' => 'TDepartureTime',
            'foreignKey' => 't_departure_time_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

}
?>