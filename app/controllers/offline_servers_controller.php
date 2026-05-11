<?php
class OfflineServersController extends AppController {

    var $name = 'OfflineServers';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Project Server', 'Dashboard');
    }

    function ajax() {
        $this->layout = 'ajax';
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Project Server', 'View', $id);
        $this->data = $this->OfflineServer->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('code', 'offline_servers', $this->data['OfflineServer']['code'], 'status >= 0')) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Project Server', 'Save Add New (Code has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $offlineProject = ClassRegistry::init('OfflineProject')->read(null, $this->data['OfflineServer']['offline_project_id']);
                $dateNow  = date("Y-m-d H:i:s");
                $this->OfflineServer->create();
                $this->data['OfflineServer']['key']      = $offlineProject['OfflineProject']['key'];
                $this->data['OfflineServer']['s_t']      = "CTK".$this->Helper->generateRandomString(21);
                $this->data['OfflineServer']['sct_c']    = $this->Helper->generateRandomString(24);
                $this->data['OfflineServer']['created']  = $dateNow;
                $this->data['OfflineServer']['created_by'] = $user['User']['id'];
                $this->data['OfflineServer']['status']   = 1;
                if($this->OfflineServer->save($this->data)) {
                    $serverId = $this->OfflineServer->id;
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Project Server', 'Save Add New', $serverId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                }else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Project Server', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $offlineProjects  = ClassRegistry::init('OfflineProject')->find('list',array('conditions' => array('OfflineProject.is_active = 1')));
        $this->set(compact('offlineProjects'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('code', 'offline_servers', $id, $this->data['OfflineServer']['code'], 'status >= 0')) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Project Server', 'Save Edit (Code has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['OfflineServer']['modified'] = $dateNow;
                $this->data['OfflineServer']['modified_by'] = $user['User']['id'];
                if($this->OfflineServer->Save($this->data)){
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Project Server', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;                  
                    exit;                                
                }else{
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Project Server', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit; 
                }    
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Project Server', 'Edit', $id);
            $this->data = $this->OfflineServer->read(null, $id);
            $offlineProjects  = ClassRegistry::init('OfflineProject')->find('list',array('conditions' => array('OfflineProject.is_active = 1')));
            $this->set(compact('offlineProjects'));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `offline_servers` SET `status` = 0 WHERE `id`=".$id.";");
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Project Server', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;    
    }
    
    function active($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `offline_servers` SET `status` = 2 WHERE `id`=".$id.";");
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Project Server', 'Active', $id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;    
    }

}

?>