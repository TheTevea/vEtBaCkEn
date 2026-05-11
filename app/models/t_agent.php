<?php
class TAgent extends AppModel {
    var $name = 'TAgent';
    var $belongsTo = array(
        'TAgentType' => array(
            'className' => 'TAgentType',
            'foreignKey' => 't_agent_type_id',
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