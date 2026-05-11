<?php

class ExchangeRatesController extends AppController {

    var $name = 'ExchangeRates';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Exchange Rate', 'Dashboard');
    }

    function ajax($companyId = '') {
        $this->layout = 'ajax';
        $company = ClassRegistry::init('Company')->read(null, $companyId);
        $this->set(compact('companyId', 'company'));
    }

    function add($id) {
        $this->layout = 'ajax';
        if(!empty($id)){
            $user = $this->getCurrentUser();
            $comCurrency = ClassRegistry::init('CompanyCurrency')->read(null, $id);
            $rateSell    = $_POST['rate_sell']!=''?$_POST['rate_sell']:0;
            $rateChange  = $_POST['rate_change']!=''?$_POST['rate_change']:0;
            // Insert Exchange Rate
            $this->ExchangeRate->create();
            $this->data['ExchangeRate']['company_id'] = $comCurrency['CompanyCurrency']['company_id'];
            $this->data['ExchangeRate']['currency_center_id'] = $comCurrency['CompanyCurrency']['currency_center_id'];
            $this->data['ExchangeRate']['rate_to_sell'] = $rateSell;
            $this->data['ExchangeRate']['rate_to_change'] = $rateChange;
            $this->data['ExchangeRate']['created_by']  = $user['User']['id'];
            $this->data['ExchangeRate']['is_active']   = 1;
            $this->ExchangeRate->save($this->data);
            $exchangeRateId = $this->ExchangeRate->id;
            // Update Company Currency
            mysql_query("UPDATE `company_currencies` SET `exchange_rate_id` = '".$exchangeRateId."', `rate_to_sell`='".$rateSell."', `rate_to_change`='".$rateChange."', `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
            $this->Helper->saveUserActivity($user['User']['id'], 'Exchange Rate', 'Save Add New', $exchangeRateId);
        }
        exit;
    }
    function view($companyId = null, $currencyCenterId = null){   
        $this->layout = 'ajax';
        if (!$companyId && !$currencyCenterId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Company Currency', 'View', $companyId);             
        
        $this->data = ClassRegistry::init('ExchangeRate')->find('all',
                        array(
                            'fields' => array(
                                'ExchangeRate.*',
                                'companies.name',
                                'companies.currency_center_id',
                                'currency_centers.name',
                                'currency_centers.symbol',
                            ),
                            'joins' => array(
                                array('table' => 'companies', 'type' => 'left', 'conditions' => array('ExchangeRate.company_id=companies.id')),
                                array('table' => 'currency_centers', 'type' => 'left', 'conditions' => array('currency_centers.id=ExchangeRate.currency_center_id'))
                            ),
                            'conditions' => array('ExchangeRate.is_active = 1', 'ExchangeRate.company_id' => $companyId, 'ExchangeRate.currency_center_id' => $currencyCenterId)
                        )
        );        
    }
}

?>