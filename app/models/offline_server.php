<?php
class OfflineServer extends AppModel {
    var $name = 'OfflineServer';
    var $belongsTo = array(
        'OfflineProject' => array(
            'className' => 'OfflineProject',
            'foreignKey' => 'offline_project_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

}
?>