<?php

class MobilesController extends AppController {

    var $uses = 'User';
    var $components = array('Helper');

    function index(){
        $this->layout = 'mobile';
    }

    function dateRange() {
        return array(
            'Today' => 'Today',
            'Yesterday' => 'Yesterday',
            'This Week' => 'This Week',
            'This Week-to-date' => 'This Week-to-date',
            'This Month' => 'This Month',
            'This Month-to-date' => 'This Month-to-date',
            'This Quarter' => 'This Quarter',
            'This Quarter-to-date' => 'This Quarter-to-date',
            'This Year' => 'This Year',
            'This Year-to-date' => 'This Year-to-date',
            'Last 30 days' => 'Last 30 days',
            'Last 365 days' => 'Last 365 days',
            'Last Week' => 'Last Week',
            'Last Week-to-date' => 'Last Week-to-date',
            'Last Month' => 'Last Month',
            'Last Month-to-date' => 'Last Month-to-date',
            'Last Quarter' => 'Last Quarter',
            'Last Quarter-to-date' => 'Last Quarter-to-date',
            'Last Year' => 'Last Year',
            'Last Year-to-date' => 'Last Year-to-date',
            'Next 30 days' => 'Next 30 days',
            'Next 365 days' => 'Next 365 days',
            'Next Week' => 'Next Week',
            'Next 4 Weeks' => 'Next 4 Weeks',
            'Next Month' => 'Next Month',
            'Next Quarter' => 'Next Quarter',
            'Next Year' => 'Next Year'
        );
    }

    function agencySalesTicket(){
        $this->layout = 'mobile';
        $this->Helper->saveUserActivity(0, 'Mobile', 'Report Agency Sales');
        // Check User
        $sqlUser = mysql_query("SELECT * FROM user_report_tokens WHERE token = '".$_GET['token']."' LIMIT 1");
        if(mysql_num_rows($sqlUser)){
            $rowUser = mysql_fetch_array($sqlUser);
            // Check Module
            $permission = false;
            $queryGroup = mysql_query("SELECT group_id FROM user_groups WHERE user_id=" . $rowUser['created_by']);
            while ($dataGroup = mysql_fetch_array($queryGroup)) {
                $queryModule = mysql_query("SELECT module_id FROM permissions WHERE group_id=" . $dataGroup[0]);
                while ($dataModule = mysql_fetch_array($queryModule)) {
                    $queryPermission = mysql_query("SELECT id FROM module_details WHERE module_id=" . $dataModule[0] . " AND controllers = 'mobiles' AND views = 'agentSalesTicket'");
                    if(mysql_num_rows($queryPermission)){
                        $permission = true;
                    }
                }
            }
            if($permission == true){
                $this->set('dateRange', $this->dateRange());
                $agents = ClassRegistry::init('TAgent')->find('list', array("conditions" => array("TAgent.status = 1 AND TAgent.offline_project_id = 1 AND TAgent.user_id = ".$rowUser['created_by'])));
                $this->set(compact('agents'));
            } else {
                echo "No Authentication"; 
                exit();   
            }
        } else {
            echo "Invalid Permission";
            exit();
        }
    }

    function agencySalesTicketResult(){
        $this->layout = 'ajax';
        $this->Helper->saveUserActivity(0, 'Mobile', 'Report Agency Sales');
    }

    function salesTicketView($id = null){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->Helper->saveUserActivity(0, 'Mobile', 'Report Agency Sales');
        $this->data = ClassRegistry::init('TTicket')->read(null, $id);
    }

    function ticketBalance(){
        $this->layout = 'mobile';
        $this->Helper->saveUserActivity(0, 'Mobile', 'Report Agency Balance');
        // Check User
        $sqlUser = mysql_query("SELECT * FROM user_report_tokens WHERE token = '".$_GET['token']."' LIMIT 1");
        if(mysql_num_rows($sqlUser)){
            $rowUser = mysql_fetch_array($sqlUser);
            // Check Module
            $permission = false;
            $queryGroup = mysql_query("SELECT group_id FROM user_groups WHERE user_id=" . $rowUser['created_by']);
            while ($dataGroup = mysql_fetch_array($queryGroup)) {
                $queryModule = mysql_query("SELECT module_id FROM permissions WHERE group_id=" . $dataGroup[0]);
                while ($dataModule = mysql_fetch_array($queryModule)) {
                    $queryPermission = mysql_query("SELECT id FROM module_details WHERE module_id=" . $dataModule[0] . " AND controllers = 'mobiles' AND views = 'agentBalance'");
                    if(mysql_num_rows($queryPermission)){
                        $permission = true;
                    }
                }
            }
            if($permission == true){
                $this->set('dateRange', $this->dateRange());
                $agents = ClassRegistry::init('TAgent')->find('list', array("conditions" => array("TAgent.status = 1 AND TAgent.offline_project_id = 1 AND TAgent.user_id = ".$rowUser['created_by'])));
                $userId = $rowUser['created_by'];
                $this->set(compact('agents', 'userId'));
            } else {
                echo "No Authentication"; 
                exit();   
            }
        } else {
            echo "Invalid Permission";
            exit();
        }
    }

    function ticketBalanceResult($userId = null){
        $this->layout = 'ajax';
        $this->Helper->saveUserActivity(0, 'Mobile', 'Report Agency Balance');
        $this->set(compact('userId'));
    }

    function salesTicket(){
        $this->layout = 'mobile';
        $this->Helper->saveUserActivity(0, 'Mobile', 'Report Agency Sales');
        // Check User
        $sqlUser = mysql_query("SELECT * FROM user_report_tokens WHERE token = '".$_GET['token']."' LIMIT 1");
        if(mysql_num_rows($sqlUser)){
            $rowUser = mysql_fetch_array($sqlUser);
            // Check Module
            $permission = false;
            $queryGroup = mysql_query("SELECT group_id FROM user_groups WHERE user_id=" . $rowUser['created_by']);
            while ($dataGroup = mysql_fetch_array($queryGroup)) {
                $queryModule = mysql_query("SELECT module_id FROM permissions WHERE group_id=" . $dataGroup[0]);
                while ($dataModule = mysql_fetch_array($queryModule)) {
                    $queryPermission = mysql_query("SELECT id FROM module_details WHERE module_id=" . $dataModule[0] . " AND controllers = 'mobiles' AND views = 'salesTicket'");
                    if(mysql_num_rows($queryPermission)){
                        $permission = true;
                    }
                }
            }
            if($permission == true){
                $this->set('dateRange', $this->dateRange());
                $mainBranches = ClassRegistry::init('MainBranch')->find('list', array("conditions" => array("MainBranch.is_active = 1 AND MainBranch.offline_project_id = 1 AND MainBranch.id = (SELECT main_branch_id FROM users WHERE id =".$rowUser['created_by'].")")));
                $this->set(compact('mainBranches'));
            } else {
                echo "No Authentication"; 
                exit();   
            }
        } else {
            echo "Invalid Permission";
            exit();
        }
    }

    function salesTicketResult(){
        $this->layout = 'ajax';
        $this->Helper->saveUserActivity(0, 'Mobile', 'Report Agency Sales');
    }
}

?>