<?php
class OnlineCustomerTicket extends AppModel {
    var $name = 'OnlineCustomerTicket';
    var $belongsTo = array(
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