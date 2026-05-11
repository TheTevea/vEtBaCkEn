<?php
class TAgentsController extends AppController {

    var $name = 'TAgents';
    var $components = array('Helper', 'AgencyOnline');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Agent', 'Dashboard');
        $companies = ClassRegistry::init('Company')->find('all',
                    array(
                        'joins' => array(
                            array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                        ),
                        'fields' => array('Company.id', 'Company.name'),
                        'conditions' => array('Company.is_active = 1', 'Company.offline_project_id' => $user['User']['offline_project_id'], 'user_companies.user_id=' . $user['User']['id'])
                    ));
        $branches = ClassRegistry::init('Branch')->find('all',
                    array(
                        'joins' => array(
                            array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))
                        ),
                        'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),
                        'conditions' => array('Branch.is_active = 1', 'Branch.offline_project_id' => $user['User']['offline_project_id'], 'user_branches.user_id=' . $user['User']['id'])
                    ));
        $this->set(compact('companies', 'branches'));
    }

    function ajax($companyId = 'all', $branchId = 'all', $type = 'all', $group = 'all', $mainBranchId = 'all') {
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId', 'type', 'group', 'mainBranchId'));
    }
    
    function balanceAjax($agencyId) {
        $this->layout = 'ajax';
        $this->set(compact('agencyId'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Agent', 'View', $id);
        $this->data = $this->TAgent->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('code', 't_agents', $this->data['TAgent']['code'], 'status > 0')) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Agent', 'Save Add New Agent (Code has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->TAgent->create();
                $this->data['TAgent']['sys_code']   = $this->Helper->generateRandomString(6);
                $this->data['TAgent']['offline_project_id'] = $user['User']['offline_project_id'];
                // Commission Bus
                if($this->data['TAgent']['commission_type'] == 1){
                    $this->data['TAgent']['commission'] = $this->data['TAgent']['commission'];
                } else if($this->data['TAgent']['commission_type'] == 3){
                    $this->data['TAgent']['commission'] = $this->data['TAgent']['commission_fix_amount'];
                }
                // Commission Buva Sea
                if($this->data['TAgent']['commission_buva_sea_type'] == 1){
                    $this->data['TAgent']['commission_buva_sea'] = $this->data['TAgent']['commission_buva_sea'];
                } else if($this->data['TAgent']['commission_buva_sea_type'] == 3){
                    $this->data['TAgent']['commission_buva_sea'] = $this->data['TAgent']['commission_buva_sea_fix_amount'];
                }
                $this->data['TAgent']['created']    = $dateNow;
                $this->data['TAgent']['created_by'] = $user['User']['id'];
                $this->data['TAgent']['status']     = 1;
                if($this->TAgent->save($this->data)) {
                    $lastInsertId = $this->TAgent->getLastInsertId();
                    // Commission History
                    mysql_query("INSERT INTO `t_agents_commission_histories` (`id`, `t_agent_id`, `commission`, `commission_type`, `created`) 
                                 VALUES (NULL, '".$lastInsertId."', '".$this->data['TAgent']['commission']."', '".$this->data['TAgent']['commission_type']."', now());");
                    // User
                    if(!empty($this->data['TAgent']['username']) && !empty($this->data['TAgent']['password']) && !empty($this->data['TAgent']['confirm_password'])){
                        if($this->data['TAgent']['password'] == $this->data['TAgent']['confirm_password']){
                            $sysCode  = $this->Helper->generateRandomString(6);
                            if($this->data['TAgent']['type'] == 1){ // Online
                                $userType = 4;
                            } else {
                                $userType = 3;
                            }
                            // $password = md5(Configure::read('Security.salt') . $this->data['TAgent']['password'] . Configure::read('Security.cipherSeed'));
                            // Change password to Bcrypt
                            $options = array(
                                'cost' => 10,
                            );
                            $password = mysql_real_escape_string($this->data['TAgent']['password']);
                            $password_hash = password_hash($password, PASSWORD_BCRYPT, $options);
                            $newPassword   = str_replace("$2y$", "$2a$", $password_hash);
                            mysql_query("INSERT INTO `users` (`sys_code`, `username`, `password`, `first_name`, `last_name`, `telephone`, `email`, `offline_project_id`, `main_branch_id`, `created`, `created_by`, `modified`, `type`, `is_admin`, `is_hash`)
                                         VALUES ('".$sysCode."', '".$this->data['TAgent']['username']."', '".$newPassword."', 'Agency', '".$this->data['TAgent']['name']."', '".$this->data['TAgent']['telephone']."', '".$this->data['TAgent']['e_mail']."', ".$user['User']['offline_project_id'].", 0, '".date("Y-m-d H:i:s")."', ".$user['User']['id'].", '".date("Y-m-d H:i:s")."', ".$userType.", 0, 1);");
                            $userId = mysql_insert_id();
                            // Update Agency User
                            mysql_query("UPDATE t_agents SET user_id = ".$userId." WHERE id = ".$lastInsertId);
                            mysql_query("INSERT INTO user_groups (user_id,group_id) VALUES ('".$userId."', ".$this->data['TAgent']['group_id'].")");
                        }
                    }
                    // Company
                    if (isset($this->data['TAgent']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['TAgent']['company_id']); $i++) {
                            mysql_query("INSERT INTO t_agent_companies (t_agent_id,company_id) VALUES ('" . $lastInsertId . "','" . $this->data['TAgent']['company_id'][$i] . "')");
                            if(!empty($userId)){
                                mysql_query("INSERT INTO user_companies (user_id, company_id) VALUES ('".$userId."','" . $this->data['TAgent']['company_id'][$i] . "')");
                            }
                        }
                    }
                    // Branch
                    if (isset($this->data['TAgent']['branch_id'])) {
                        for ($i = 0; $i < sizeof($this->data['TAgent']['branch_id']); $i++) {
                            mysql_query("INSERT INTO t_agent_branches (t_agent_id,branch_id) VALUES ('" . $lastInsertId . "','" . $this->data['TAgent']['branch_id'][$i] . "')");
                            if(!empty($userId)){
                                mysql_query("INSERT INTO user_branches (user_id, branch_id) VALUES ('".$userId."','" . $this->data['TAgent']['branch_id'][$i] . "')");
                            }
                        }
                    }
                    // Update API User
                    if($this->data['TAgent']['type'] == 3){
                        $username = "VETAPI".$this->Helper->generateRandomString(8);
                        $password = "VETAPI".$this->Helper->generateRandomString(12);
                        $clientId = "APILINK".$this->Helper->generateRandomString(15);
                        $clientSe = "APISECRET".$this->Helper->generateRandomString(15);
                        mysql_query("UPDATE t_agents SET oauth_user = '".$username."', oauth_password_raw = '".$password."', oauth_client_id = '".$clientId."', oauth_client_secret = '".$clientSe."'  WHERE id = ".$lastInsertId);
                        mysql_query("INSERT INTO oauth_client_details (client_id, client_secret, scope, authorized_grant_types, web_server_redirect_uri, authorities, access_token_validity, refresh_token_validity, additional_information, autoapprove)
                                     VALUES
                                     ('".$clientId."', '".$clientSe."', 'read,write,trust', 'password,refresh_token', null, null, 63072000, 63072000, null, 1);");
                        $post = array();
                        $post['username'] = $username;
                        $post['password'] = $password;
                        $post['clientId'] = $clientId;
                        $post['clientSecret'] = $clientSe;
                        echo $this->AgencyOnline->updateApiUser($post);
                    }
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Agent', 'Save Add New');
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                }else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Agent', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $companies = ClassRegistry::init('Company')->find('list',array(
                        'joins' => array(
                            array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                        ),'fields' => array('Company.id', 'Company.name'), 
                        'conditions' => array('Company.is_active = 1', 'Company.offline_project_id' => $user['User']['offline_project_id'], 'user_companies.user_id=' . $user['User']['id'])));
        $branches = ClassRegistry::init('Branch')->find('all',array(
                        'joins' => array(
                            array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))
                        ),'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'), 
                        'conditions' => array('Branch.is_active = 1', 'Branch.offline_project_id' => $user['User']['offline_project_id'], 'user_branches.user_id=' . $user['User']['id'])));
        $tAgentTypes   = ClassRegistry::init('TAgentType')->find('list', array("conditions" => array("TAgentType.is_active = 1", 'TAgentType.offline_project_id' => $user['User']['offline_project_id'])));
        $groups        = ClassRegistry::init('Group')->find('list', array('conditions' => array('is_active' => '1', "offline_project_id = ".$user['User']['offline_project_id']), 'order' => 'name'));
        $mainBranches  = ClassRegistry::init('MainBranch')->find('list', array('conditions' => array('is_active' => '1', 'offline_project_id' => $user['User']['offline_project_id']), 'order' => 'name'));
        $this->set(compact('companies', 'tAgentTypes', 'branches', 'groups', 'mainBranches'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('code', 't_agents', $id, $this->data['TAgent']['code'], 'status > 0')) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Agent', 'Save Edit Agent(Code has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $agency     = $this->TAgent->read(null, $this->data['TAgent']['id']);
                $userAgency = ClassRegistry::init('User')->read(null, $agency['TAgent']['user_id']);
                $dateNow    = date("Y-m-d H:i:s");
                // Commission Bus
                if($this->data['TAgent']['commission_type'] == 1){
                    $this->data['TAgent']['commission'] = $this->data['TAgent']['commission'];
                } else if($this->data['TAgent']['commission_type'] == 3){
                    $this->data['TAgent']['commission'] = $this->data['TAgent']['commission_fix_amount'];
                }
                if(!empty($this->data['TAgent']['commission_buva_sea_type'])){
                    // Commission Buva Sea
                    if($this->data['TAgent']['commission_buva_sea_type'] == 1){
                        $this->data['TAgent']['commission_buva_sea'] = $this->data['TAgent']['commission_buva_sea'];
                    } else if($this->data['TAgent']['commission_buva_sea_type'] == 3){
                        $this->data['TAgent']['commission_buva_sea'] = $this->data['TAgent']['commission_buva_sea_fix_amount'];
                    }
                }
                $this->data['TAgent']['modified'] = $dateNow;
                $this->data['TAgent']['modified_by'] = $user['User']['id'];
                if($this->TAgent->Save($this->data)){  
                    // Commission History
                    $sqlCommissionHistory = mysql_query("SELECT * FROM t_agents_commission_histories WHERE t_agent_id = ".$id." ORDER BY id DESC LIMIT 1");
                    if(!mysql_num_rows($sqlCommissionHistory)){
                        mysql_query("INSERT INTO `t_agents_commission_histories` (`id`, `t_agent_id`, `commission`, `commission_type`, `created`) 
                                     VALUES (NULL, '".$this->data['TAgent']['id']."', '".$this->data['TAgent']['commission']."', '".$this->data['TAgent']['commission_type']."', now());");
                    } else {
                        $rowCommissionHistory = mysql_fetch_array($sqlCommissionHistory);
                        if($rowCommissionHistory['commission'] != $this->data['TAgent']['commission'] || $rowCommissionHistory['commission_type'] != $this->data['TAgent']['commission_type']){
                            mysql_query("INSERT INTO `t_agents_commission_histories` (`id`, `t_agent_id`, `commission`, `commission_type`, `created`) 
                                         VALUES (NULL, '".$this->data['TAgent']['id']."', '".$this->data['TAgent']['commission']."', '".$this->data['TAgent']['commission_type']."', now());");
                        }
                    }
                    $userId = "";
                    if(!empty($userAgency)){
                        $userId = $userAgency['User']['id'];
                        $sqlCkG = mysql_query("SELECT id FROM user_groups WHERE user_id = ".$userAgency['User']['id']);
                        if(!mysql_num_rows($sqlCkG)){
                            mysql_query("INSERT INTO user_groups (user_id,group_id) VALUES ('".$userId."', 10)");
                        }
                        // User
                        if(!empty($this->data['TAgent']['username']) && !empty($this->data['TAgent']['password']) && !empty($this->data['TAgent']['confirm_password'])){
                            if($this->data['TAgent']['password'] == $this->data['TAgent']['confirm_password']){
                                if($this->data['TAgent']['type'] == 1){ // Online
                                    $userType = 4;
                                } else {
                                    $userType = 3;
                                }
                                // Change password to Bcrypt
                                $options = array(
                                    'cost' => 10,
                                );
                                $password = mysql_real_escape_string($this->data['TAgent']['password']);
                                $password_hash = password_hash($password, PASSWORD_BCRYPT, $options);
                                $newPassword   = str_replace("$2y$", "$2a$", $password_hash);
                                mysql_query("UPDATE users SET username = '".$this->data['TAgent']['username']."', password = '".$newPassword."', is_hash = 1, type = ".$userType." WHERE id = ".$agency['TAgent']['user_id']);
                                mysql_query("DELETE FROM user_groups WHERE user_id=" . $agency['TAgent']['user_id']);
                                mysql_query("INSERT INTO user_groups (user_id,group_id) VALUES ('".$userId."', ".$this->data['TAgent']['group_id'].")");
                            }
                        } else {
                            $updateCon = "";
                            if($this->data['TAgent']['type'] != $agency['TAgent']['type']){
                                if($this->data['TAgent']['type'] == 1){
                                    $userType = 4;
                                    $updateCon .= ", type = 4";
                                } else {
                                    $userType = 3;
                                    $updateCon .= ", type = 3";
                                }
                            }
                            mysql_query("UPDATE users SET username = '".$this->data['TAgent']['username']."'".$updateCon." WHERE id = ".$agency['TAgent']['user_id']);
                            mysql_query("DELETE FROM user_groups WHERE user_id=" . $agency['TAgent']['user_id']);
                            mysql_query("INSERT INTO user_groups (user_id,group_id) VALUES ('".$userId."', ".$this->data['TAgent']['group_id'].")");
                        }
                    } else {
                        if(!empty($this->data['TAgent']['username']) && !empty($this->data['TAgent']['password']) && !empty($this->data['TAgent']['confirm_password'])){
                            if($this->data['TAgent']['password'] == $this->data['TAgent']['confirm_password']){
                                $sysCode  = $this->Helper->generateRandomString(6);
                                if($this->data['TAgent']['type'] == 1){
                                    $userType = 4;
                                } else {
                                    $userType = 3;
                                }
                                // Change password to Bcrypt
                                $options = array(
                                    'cost' => 10,
                                );
                                $password = mysql_real_escape_string($this->data['TAgent']['password']);
                                $password_hash = password_hash($password, PASSWORD_BCRYPT, $options);
                                $newPassword   = str_replace("$2y$", "$2a$", $password_hash);
                                mysql_query("INSERT INTO `users` (`sys_code`, `username`, `password`, `first_name`, `last_name`, `telephone`, `email`, `offline_project_id`, `main_branch_id`, `created`, `created_by`, `modified`, `type`, `is_hash`, `is_admin`)
                                             VALUES ('".$sysCode."', '".$this->data['TAgent']['username']."', '".$newPassword."', 'Agency', '".$this->data['TAgent']['name']."', '".$this->data['TAgent']['telephone']."', '".$this->data['TAgent']['e_mail']."', ".$user['User']['offline_project_id'].", 0, '".date("Y-m-d H:i:s")."', ".$user['User']['id'].", '".date("Y-m-d H:i:s")."', ".$userType.", 1, 0);");
                                $userId = mysql_insert_id();
                                // Update Agency User
                                mysql_query("UPDATE t_agents SET user_id = ".$userId." WHERE id = ".$id);
                                mysql_query("INSERT INTO user_groups (user_id,group_id) VALUES ('".$userId."', ".$this->data['TAgent']['group_id'].")");
                            }
                        }
                    }
                    if(!empty($userId)){
                        mysql_query("DELETE FROM user_companies WHERE user_id=".$userId);
                        mysql_query("DELETE FROM user_branches WHERE user_id=".$userId);
                    }
                    // Company
                    mysql_query("DELETE FROM t_agent_companies WHERE t_agent_id=".$id);
                    if (isset($this->data['TAgent']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['TAgent']['company_id']); $i++) {
                            mysql_query("INSERT INTO t_agent_companies (t_agent_id,company_id) VALUES ('".$id."','" . $this->data['TAgent']['company_id'][$i] . "')");
                            if(!empty($userId)){
                                mysql_query("INSERT INTO user_companies (user_id, company_id) VALUES ('".$userId."','" . $this->data['TAgent']['company_id'][$i] . "')");
                            }
                        }
                    }
                    // Branch
                    mysql_query("DELETE FROM t_agent_branches WHERE t_agent_id=".$id);
                    if (isset($this->data['TAgent']['branch_id'])) {
                        for ($i = 0; $i < sizeof($this->data['TAgent']['branch_id']); $i++) {
                            mysql_query("INSERT INTO t_agent_branches (t_agent_id,branch_id) VALUES ('" . $id . "','" . $this->data['TAgent']['branch_id'][$i] . "')");
                            if(!empty($userId)){
                                mysql_query("INSERT INTO user_branches (user_id, branch_id) VALUES ('".$userId."','" . $this->data['TAgent']['branch_id'][$i] . "')");
                            }
                        }
                    }
                    // Update API User
                    if($this->data['TAgent']['type'] == 3){
                        if(empty($agency['TAgent']['oauth_token'])){
                            if(empty($agency['TAgent']['oauth_user']) || empty($agency['TAgent']['oauth_password_raw']) || empty($agency['TAgent']['oauth_client_id']) || empty($agency['TAgent']['oauth_client_secret'])){
                                $username = "VETAPI".$this->Helper->generateRandomString(8);
                                $password = "VETAPI".$this->Helper->generateRandomString(12);
                                $clientId = "APILINK".$this->Helper->generateRandomString(15);
                                $clientSe = "APISECRET".$this->Helper->generateRandomString(15);
                                mysql_query("UPDATE t_agents SET oauth_user = '".$username."', oauth_password_raw = '".$password."', oauth_client_id = '".$clientId."', oauth_client_secret = '".$clientSe."'  WHERE id = ".$id);
                                mysql_query("INSERT INTO oauth_client_details (client_id, client_secret, scope, authorized_grant_types, web_server_redirect_uri, authorities, access_token_validity, refresh_token_validity, additional_information, autoapprove)
                                     VALUES
                                     ('".$clientId."', '".$clientSe."', 'read,write,trust', 'password,refresh_token', null, null, 63072000, 63072000, null, 1);");
                            } else {
                                $username = $agency['TAgent']['oauth_user'];
                                $password = $agency['TAgent']['oauth_password_raw'];
                                $clientId = $agency['TAgent']['oauth_client_id'];
                                $clientSe = $agency['TAgent']['oauth_client_secret'];
                            }
                            $post = array();
                            $post['username'] = $username;
                            $post['password'] = $password;
                            $post['clientId'] = $clientId;
                            $post['clientSecret'] = $clientSe;
                            $this->AgencyOnline->updateApiUser($post);
                        }
                    }
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;                  
                    exit();                                
                }else{
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit; 
                }    
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Agent', 'Edit Agent', $id);
            $this->data = $this->TAgent->read(null, $id);
            $companies = ClassRegistry::init('Company')->find('list',array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                            ),'fields' => array('Company.id', 'Company.name'), 
                            'conditions' => array('Company.is_active = 1', 'Company.offline_project_id' => $user['User']['offline_project_id'], 'user_companies.user_id=' . $user['User']['id'])));
            $branches = ClassRegistry::init('Branch')->find('all',array(
                        'joins' => array(
                            array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))
                        ),'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'), 
                        'conditions' => array('Branch.is_active = 1', 'Branch.offline_project_id' => $user['User']['offline_project_id'], 'user_branches.user_id=' . $user['User']['id'])));
            $tAgentTypes = ClassRegistry::init('TAgentType')->find('list', array("conditions" => array("TAgentType.is_active = 1", 'TAgentType.offline_project_id' => $user['User']['offline_project_id'])));
            $groups      = ClassRegistry::init('Group')->find('list', array('conditions' => array('is_active' => '1', "offline_project_id = ".$user['User']['offline_project_id']), 'order' => 'name'));
            $mainBranches  = ClassRegistry::init('MainBranch')->find('list', array('conditions' => array('is_active' => '1', 'offline_project_id' => $user['User']['offline_project_id']), 'order' => 'name'));
            $this->set(compact('companies', 'tAgentTypes', 'branches', 'groups', 'mainBranches'));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $tAgent   = $this->TAgent->read(null, $id);
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `t_agents` SET `status`=0, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        mysql_query("UPDATE `users` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$tAgent['TAgent']['user_id'].";");
        if(!empty($tAgent['TAgent']['oauth_user'])){
            mysql_query("DELETE FROM oauth_refresh_token WHERE token_id = (SELECT token_id FROM oauth_access_token WHERE user_name = '".$tAgent['TAgent']['oauth_user']."' LIMIT 1)");
            mysql_query("DELETE FROM oauth_access_token WHERE user_name = '".$tAgent['TAgent']['oauth_user']."'");
        }
        if(!empty($tAgent['TAgent']['oauth_client_id'])){
            mysql_query("DELETE FROM oauth_client_details WHERE client_id = '".$tAgent['TAgent']['oauth_client_id']."'");
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Agent', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;    
    }
    
    function status($id = null, $status = null){
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `t_agents` SET `status`=".$status.", `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->Helper->saveUserActivity($user['User']['id'], 'Agent', 'Change Agent Status', $id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }
    
    function popBalance($id = null){
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user   = $this->getCurrentUser();
        if (!empty($this->data)) {
            $result = array();  
            $dateNow  = date("Y-m-d H:i:s");
            $this->loadModel('AgencyTopup');
            $this->AgencyTopup->create();
            $agencyTopup = array();
            $agencyTopup['AgencyTopup']['sys_code']   = $this->Helper->generateRandomString(6);
            $agencyTopup['AgencyTopup']['offline_project_id'] = $user['User']['offline_project_id'];
            $agencyTopup['AgencyTopup']['date']        = date("Y-m-d");
            $agencyTopup['AgencyTopup']['t_agency_id'] = $this->data['t_agency_id'];
            $agencyTopup['AgencyTopup']['amount']      = $this->data['amount'];
            $agencyTopup['AgencyTopup']['type']        = $this->data['type'];
            $agencyTopup['AgencyTopup']['note']        = $this->data['note'];
            $agencyTopup['AgencyTopup']['created']     = $dateNow;
            $agencyTopup['AgencyTopup']['created_by']  = $user['User']['id'];
            $agencyTopup['AgencyTopup']['modified']    = $dateNow;
            $agencyTopup['AgencyTopup']['is_active']   = 1;
            if($this->AgencyTopup->save($agencyTopup)) {
                $popUpId = $this->AgencyTopup->id;
                // Get Module Code
                $modCode = $this->Helper->getModuleCode("VPOP", $popUpId, 'code', 'agency_topups', 'status >= 0');
                // Updaet Module Code
                mysql_query("UPDATE agency_topups SET code = '".$modCode."' WHERE id = ".$popUpId);
                // Update agency pop up 
                if($agencyTopup['AgencyTopup']['type'] == 1){ // Debit (-)
                    $debit  = $agencyTopup['AgencyTopup']['amount'];
                    $credit = 0;
                    $popupAmount = $agencyTopup['AgencyTopup']['amount'] * -1;
                } else { // Credit (+)
                    $popupAmount = $agencyTopup['AgencyTopup']['amount'];
                    $debit  = 0;
                    $credit = $agencyTopup['AgencyTopup']['amount'];
                }
                mysql_query("INSERT INTO `agency_balances` (`t_agency_id`, `agency_topup_id`, `reference`, `debit`, `credit`, `type`, `module`, `created`, `created_by`) VALUES (".$agencyTopup['AgencyTopup']['t_agency_id'].", ".$popUpId.", '".$modCode."', ".$debit.", ".$credit.", '".$this->data['type']."', 'Top Up Balance', '".$dateNow."', ".$user['User']['id'].");");
                // Update Agent Balance
                mysql_query("UPDATE t_agents SET balance = (IFNULL(balance, 0) + ".$popupAmount.") WHERE id = ".$this->data['t_agency_id']);
                $this->Helper->saveUserActivity($user['User']['id'], 'Agent', 'Save Topup', $id);
                $result['error'] = 0;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Agent', 'Save Topup (Error)', $id);
                $result['error'] = 1;
            }
            echo json_encode($result);
            exit;
        }
        $this->data = $this->TAgent->read(null, $id);
        $this->Helper->saveUserActivity($user['User']['id'], 'Agent', 'View Topup', $id);
    }
    
    function popBalanceAjax($id = null){
        $this->layout = 'ajax';
        $this->set(compact('id'));
    }

}

?>