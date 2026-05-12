<?php
class MiniAppPartnerDiscount extends AppModel {
    var $name = 'MiniAppPartnerDiscount';
    var $useTable = 'mini_app_partner_discount';
    var $belongsTo = array(
        'MiniAppPartner' => array(
            'className' => 'MiniAppPartner',
            'foreignKey' => 'mini_app_partner_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>
