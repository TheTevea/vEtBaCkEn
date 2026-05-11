<?php
class OfflineProjectsController extends AppController {

    var $name = 'OfflineProjects';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Project', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Project', 'View', $id);
        $this->data = $this->OfflineProject->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('code', 'offline_projects', $this->data['OfflineProject']['code'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Project', 'Save Add New (Code has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->OfflineProject->create();
                $this->data['OfflineProject']['key']      = $this->Helper->generateRandomKey(13)."@CAMTKT";
                $this->data['OfflineProject']['created']  = $dateNow;
                $this->data['OfflineProject']['created_by'] = $user['User']['id'];
                $this->data['OfflineProject']['is_active'] = 1;
                if($this->OfflineProject->save($this->data)) {
                    $projectId = $this->OfflineProject->id;
                    // Main Project Server
                    $this->loadModel('OfflineServer');
                    $server = array();
                    $server['OfflineServer']['offline_project_id'] = $projectId;
                    $server['OfflineServer']['code']  = $this->data['OfflineProject']['code'];
                    $server['OfflineServer']['name']  = $this->data['OfflineProject']['name'];
                    $server['OfflineServer']['key']   = $this->data['OfflineProject']['key'];
                    $server['OfflineServer']['s_t']   = "CTK".$this->Helper->generateRandomString(21);
                    $server['OfflineServer']['sct_c'] = $this->Helper->generateRandomString(24);
                    $server['OfflineServer']['is_main'] = 1;
                    $server['OfflineServer']['status']  = 2;
                    $this->OfflineServer->save($server);
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Project', 'Save Add New', $projectId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                }else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Project', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('code', 'offline_projects', $id, $this->data['OfflineProject']['code'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Project', 'Save Edit (Code has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['OfflineProject']['modified'] = $dateNow;
                $this->data['OfflineProject']['modified_by'] = $user['User']['id'];
                if($this->OfflineProject->Save($this->data)){
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Project', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;                  
                    exit;                                
                }else{
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Project', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit; 
                }    
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Project', 'Edit', $id);
            $this->data = $this->OfflineProject->read(null, $id);
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `offline_projects` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Project', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;    
    }

}

?>