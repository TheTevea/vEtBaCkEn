<?php
class TJourneyAgentPrice extends AppModel {
    var $name = 'TJourneyAgentPrice';
    var $belongsTo = array(
        'TAgent' => array(
            'className' => 'TAgent',
            'foreignKey' => 't_agent_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>