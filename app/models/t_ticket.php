<?php
class TTicket extends AppModel {
    var $name = 'TTicket';
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
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'created_by',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TBoardingPoint' => array(
            'className' => 'TBoardingPoint',
            'foreignKey' => 't_boarding_point_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TDropOff' => array(
            'className' => 'TDropOff',
            'foreignKey' => 't_drop_off_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TAgent' => array(
            'className' => 'TAgent',
            'foreignKey' => 't_agent_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TBoardingPoint' => array(
            'className' => 'TBoardingPoint',
            'foreignKey' => 't_boarding_point_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TDropOff' => array(
            'className' => 'TDropOff',
            'foreignKey' => 't_drop_off_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

}
?>