<?php

class CouponsController extends AppController {

    var $name = 'Coupons';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Coupon', 'Dashboard');
        $couponTypes = ClassRegistry::init('CouponType')->find('list', array("conditions" => array("CouponType.is_active = 1")));
        $this->set(compact('couponTypes'));
    }

    function ajax($couponTypeId = 'all') {
        $this->layout = 'ajax';
        $this->set(compact('couponTypeId'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Coupon', 'View', $id);
        $this->data = $this->Coupon->read(null, $id);
    }

    function viewGenerate($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Coupon', 'View Generate', $id);
        $this->loadModel('CouponGenerate');
        $generate = $this->CouponGenerate->read(null, $id);
        if (!$generate) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $coupons = $this->Coupon->find('all', array(
            'conditions' => array('Coupon.coupon_generate_id' => intval($id), 'Coupon.status > 0'),
            'order' => array('Coupon.code' => 'ASC')
        ));
        $this->set(compact('generate', 'coupons'));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            // Required/optional fields
            $prefix         = isset($this->data['Coupon']['code_prefix']) ? trim($this->data['Coupon']['code_prefix']) : '';
            $start          = isset($this->data['Coupon']['start']) ? $this->data['Coupon']['start'] : '';
            $end            = isset($this->data['Coupon']['end']) ? $this->data['Coupon']['end'] : '';
            $amount         = isset($this->data['Coupon']['amount']) ? $this->data['Coupon']['amount'] : '';
            $totalTimeUse   = isset($this->data['Coupon']['total_time_use']) ? $this->data['Coupon']['total_time_use'] : '';
            $totalGenerate  = isset($this->data['Coupon']['total_generate']) ? intval($this->data['Coupon']['total_generate']) : 0;
            $couponTypeId   = isset($this->data['Coupon']['coupon_type_id']) ? $this->data['Coupon']['coupon_type_id'] : '';
            $exactCode      = isset($this->data['Coupon']['exact_code']) ? trim($this->data['Coupon']['exact_code']) : '';
            $startNumber    = isset($this->data['Coupon']['start_number']) ? intval($this->data['Coupon']['start_number']) : 0;
            $createdIds     = array();

            // Validate common required fields
            if ($start === '' || $end === '' || $amount === '' || $totalTimeUse === '' || $couponTypeId === '') {
                echo MESSAGE_DATA_INVALID;
                exit;
            }

            $success = true;
            $this->loadModel('CouponGenerate');
            if ($exactCode !== '') {
                // Manual single coupon (no generation)
                if ($this->Coupon->hasAny(array('Coupon.code' => $exactCode))) {
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
                // Create coupon generate record
                $genData = array('CouponGenerate' => array(
                    'date'            => date('Y-m-d H:i:s'),
                    'coupon_type_id'  => $couponTypeId,
                    'total_coupon'    => 1,
                    'created'         => date('Y-m-d H:i:s'),
                    'created_by'      => $user['User']['id'],
                    'status'          => 1
                ));
                $this->CouponGenerate->create();
                if (!$this->CouponGenerate->save($genData)) {
                    $success = false;
                } else {
                    $couponGenerateId = $this->CouponGenerate->id;
                    $saveData = array('Coupon' => array(
                        'code'                => $exactCode,
                        'start'               => $start,
                        'end'                 => $end,
                        'amount'              => $amount,
                        'total_time_use'      => $totalTimeUse,
                        'created_by'          => $user['User']['id'],
                        'coupon_type_id'      => $couponTypeId,
                        'coupon_generate_id'  => $couponGenerateId,
                        'status'              => 2
                    ));
                    $this->Coupon->create();
                    if (!$this->Coupon->save($saveData)) {
                        $success = false;
                    } else {
                        $createdIds[] = $this->Coupon->id;
                    }
                }
            } else {
                // Auto-generate one or many coupons
                if ($prefix === '' || $totalGenerate <= 0) {
                    echo MESSAGE_DATA_INVALID;
                    exit;
                }
                // Create coupon generate record for the batch
                $genData = array('CouponGenerate' => array(
                    'date'            => date('Y-m-d H:i:s'),
                    'coupon_type_id'  => $couponTypeId,
                    'total_coupon'    => $totalGenerate,
                    'created'         => date('Y-m-d H:i:s'),
                    'created_by'      => $user['User']['id'],
                    'status'          => 1
                ));
                $this->CouponGenerate->create();
                if (!$this->CouponGenerate->save($genData)) {
                    $success = false;
                } else {
                    $couponGenerateId = $this->CouponGenerate->id;
                    // Find current count for the prefix
                    $currentCount = $this->Coupon->find('count', array(
                        'conditions' => array('Coupon.code LIKE' => $prefix . '%')
                    ));
                    // Base sequence start
                    $baseNumber = $startNumber > 0 ? $startNumber : ($currentCount + 1);
                    for ($i = 0; $i < $totalGenerate; $i++) {
                        // Generate unique code using prefix + incremental number (6-digit padded)
                        $codeNumber = $baseNumber + $i;
                        $code = $prefix . str_pad($codeNumber, 6, '0', STR_PAD_LEFT);
                        // Avoid clashes if some codes already exist
                        while ($this->Coupon->hasAny(array('Coupon.code' => $code))) {
                            $codeNumber++;
                            $code = $prefix . str_pad($codeNumber, 6, '0', STR_PAD_LEFT);
                        }
                        $saveData = array('Coupon' => array(
                            'code'                => $code,
                            'start'               => $start,
                            'end'                 => $end,
                            'amount'              => $amount,
                            'total_time_use'      => $totalTimeUse,
                            'created_by'          => $user['User']['id'],
                            'coupon_type_id'      => $couponTypeId,
                            'coupon_generate_id'  => $couponGenerateId,
                            'status'              => 2
                        ));
                        $this->Coupon->create();
                        if (!$this->Coupon->save($saveData)) {
                            $success = false;
                            break;
                        } else {
                            $createdIds[] = $this->Coupon->id;
                        }
                    }
                }
            }

            if ($success) {
                // Remember the last created coupon IDs for export
                $this->Session->write('CouponsLastCreatedIds', $createdIds);
                $this->Helper->saveUserActivity($user['User']['id'], 'Coupon', 'Save Add New', $this->Coupon->id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Coupon', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Coupon', 'Add New');
        $couponTypes = ClassRegistry::init('CouponType')->find('list', array("conditions" => array("CouponType.is_active = 1")));
        $this->set(compact('couponTypes'));
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Coupon', 'Delete', $id);
        mysql_query("UPDATE `coupons` SET `status` = 0, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $couponTypeId = isset($_POST['coupon_type_id']) ? $_POST['coupon_type_id'] : 'all';
            $onlyLastCreated = isset($_POST['only_last_created']) ? intval($_POST['only_last_created']) : 0;
            $couponGenerateId = isset($_POST['coupon_generate_id']) ? intval($_POST['coupon_generate_id']) : 0;
            $filename = "public/report/coupon_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Coupons' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_CODE . "\t" . REPORT_FROM . "\t" . REPORT_TO . "\t" . GENERAL_AMOUNT . "\t" . TABLE_TIME_USE . "\t" . __('Coupon Type', true);
            $condition = " WHERE c.status = 2";
            if ($couponGenerateId > 0) {
                $condition .= " AND c.coupon_generate_id = ".$couponGenerateId;
            } else if ($onlyLastCreated == 1) {
                $ids = $this->Session->read('CouponsLastCreatedIds');
                if (is_array($ids) && count($ids) > 0) {
                    $ids = array_map('intval', $ids);
                    $condition .= " AND c.id IN (" . implode(',', $ids) . ")";
                } else {
                    // Nothing to export
                    fclose($fp);
                    exit();
                }
            } else if($couponTypeId != 'all' && $couponTypeId != ''){
                $condition .= " AND c.coupon_type_id = ".intval($couponTypeId);
            }
            $query = mysql_query('SELECT c.code, c.start, c.end, c.amount, c.total_time_use, c.status, ct.name FROM coupons c LEFT JOIN coupon_types ct ON ct.id = c.coupon_type_id ' . $condition . ' ORDER BY c.code ASC');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $excelContent .= "\n" . $index++ . "\t" . $data[0] . "\t" . $data[1] . "\t" . $data[2] . "\t" . $data[3] . "\t" . $data[4] . "\t" . $data[6];
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            if ($onlyLastCreated == 1) {
                $this->Session->delete('CouponsLastCreatedIds');
            }
            exit();
        }
    }

}

?>