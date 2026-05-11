<?php
class Company extends AppModel {
    var $name = 'Company';
    var $belongsTo = array(
        'CurrencyCenter' => array(
            'className' => 'CurrencyCenter',
            'foreignKey' => 'currency_center_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

}
?>