<?php

class ReportsController extends AppController {

    var $uses = array('User');
    var $components = array('Helper');

    /**
     * Sate Ticket
     */
    function salesTicket() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        $usrCondition = "";
        $payCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            $usrCondition = " AND User.offline_project_id = ".$user['User']['offline_project_id'];
            $payCondition = " AND (PaymentMethod.offline_project_id = ".$user['User']['offline_project_id']." OR id = 1)";
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
                $usrCondition .= " AND User.id IN (SELECT user_id FROM user_branches WHERE branch_id IN (".$branchUse."))";
            }
        }
        $companies    = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $users        = ClassRegistry::init('User')->find('list', array("conditions" => array("User.is_active = 1".$usrCondition), 'fields' => array('id', 'username')));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $paymentMethods = ClassRegistry::init('PaymentMethod')->find('list', array("conditions" => array("PaymentMethod.is_active = 1".$payCondition)));
        $this->set(compact('branches', 'destinations', 'companies', 'users', 'paymentMethods'));
    }

    function salesTicketResult() {
        $this->layout = 'ajax';
    }

    function salesTicketAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(",", $data);
        $this->set("data", $data);
    }
    
    /**
     * Sate Ticket By Seat
     */
    function salesTicketBySeat() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id'];
        }
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        // $companies    = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1 AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")".$comCondition)));
        // $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1 AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")".$braCondition)));
        // $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $tJourneys = ClassRegistry::init('TJourney')->find('list', array("conditions" => array("TJourney.status = 1 AND TJourney.offline_project_id = 1"), "fields" => array("id", "description")));
        $this->set(compact('tJourneys'));
    }

    function salesTicketBySeatResult() {
        $this->layout = 'ajax';
    }

    function salesTicketBySeatAjax($post = null) {
        $this->layout = 'ajax';
        $data = explode("-", $post);
        $this->set("data", $data);
    }
    
    function collectByUser(){
        $this->layout = 'ajax';
        $this->set('dateRange', $this->dateRange());
        $user = $this->getCurrentUser();
        $comCondition = "";
        $braCondition = "";
        $mBrCondition = "";
        $usrCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $mBrCondition = " AND MainBranch.offline_project_id = ".$user['User']['offline_project_id'];
            $usrCondition = " AND User.offline_project_id = ".$user['User']['offline_project_id'];
            if($user['User']['is_admin'] == 0){
                $mainBranchList = array();
                $mainBranchList[] = $user['User']['main_branch_id'];
                $sqlReportMainBranch = mysql_query("SELECT main_branch_id FROM user_report_main_branches WHERE user_id = ".$user['User']['id']);
                while($rowReportMainBranch = mysql_fetch_array($sqlReportMainBranch)){
                    $mainBranchList[] = $rowReportMainBranch['main_branch_id'];
                }
                $mainBranchId = implode(',', $mainBranchList);
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
                $usrCondition .= " AND User.id IN (SELECT user_id FROM user_branches WHERE branch_id IN (".$branchUse."))";
                $mBrCondition .= " AND MainBranch.id IN (".$mainBranchId.")";
            }
        }
        $companies    = ClassRegistry::init('Company')->find('all', array("conditions" => array("Company.is_active = 1".$comCondition)));
        $mainBranches = ClassRegistry::init('MainBranch')->find('list', array("fields" => array("MainBranch.id", "MainBranch.name"), "conditions" => array("MainBranch.is_active = 1".$mBrCondition)));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $users        = ClassRegistry::init('User')->find('all', array("conditions" => array("User.is_active = 1".$usrCondition)));
        $this->set(compact('branches', 'companies', 'users', 'mainBranches'));
    }
    
    function collectByUserResult(){
        $this->layout = 'ajax';
    }
    
    function collectByUserSummary(){
        $this->layout = 'ajax';
    }

    function collectByUserSummaryAll(){
        $this->layout = 'ajax';
    }
    
    function collectByUserDeparture(){
        $this->layout = 'ajax';
    }
    
    function getUserByBranch($branchId, $mainBranchId){
        $this->layout = 'ajax';
        $result = '<option value="">'.TABLE_ALL.'</option>';
        if(!empty($branchId) && !empty($_GET['from']) && !empty($_GET['to'])){
            $dateNow = strtotime(date("Y-m-d"));
            $dateSt  = strtotime("2018-06-01");
            if($dateNow < $dateSt){
                $users = ClassRegistry::init('User')->find('all', array("conditions" => array("User.is_active = 1", "User.id IN (SELECT created_by FROM t_tickets WHERE branch_id = ".$branchId." AND status = 2 AND type = 1 AND journey_type = 1 AND date >= '".$_GET['from']."' AND date <= '".$_GET['to']."' GROUP BY created_by) OR User.id IN (SELECT created_by FROM t_tickets WHERE return_branch_id = ".$branchId." AND status = 2 AND type = 1 AND journey_type = 2 AND date >= '".$_GET['from']."' AND date <= '".$_GET['to']."' GROUP BY created_by) OR User.id IN (SELECT confirm_by FROM t_tickets WHERE branch_id = ".$branchId." AND status = 2 AND type = 2 AND date >= '".$_GET['from']."' AND date <= '".$_GET['to']."' GROUP BY confirm_by)")));
                foreach($users AS $user){
                    $result .= '<option value="'.$user['User']['id'].'">'.$user['User']['first_name'].' '.$user['User']['last_name'].'</option>';
                }
            } else {
                $users = ClassRegistry::init('User')->find('all', array("conditions" => array("User.is_active = 1", "User.id IN (SELECT user_id FROM user_main_branches WHERE main_branch_id = ".$mainBranchId.")", "User.id IN (SELECT created_by FROM t_tickets WHERE branch_id = ".$branchId." AND status = 2 AND type = 1 AND journey_type = 1 AND date >= '".$_GET['from']."' AND date <= '".$_GET['to']."' GROUP BY created_by) OR User.id IN (SELECT created_by FROM t_tickets WHERE return_branch_id = ".$branchId." AND status = 2 AND type = 1 AND journey_type = 2 AND date >= '".$_GET['from']."' AND date <= '".$_GET['to']."' GROUP BY created_by) OR User.id IN (SELECT confirm_by FROM t_tickets WHERE branch_id = ".$branchId." AND status = 2 AND type = 2 AND date >= '".$_GET['from']."' AND date <= '".$_GET['to']."' GROUP BY confirm_by)")));
                foreach($users AS $user){
                    $result .= '<option value="'.$user['User']['id'].'">'.$user['User']['first_name'].' '.$user['User']['last_name'].'</option>';
                }
            }
        }
        echo $result;
        exit;
    }
    
    // Net Profit
    function netProfit(){
        $this->layout = 'ajax';
        $this->set('dateRange', $this->dateRange());
        $user = $this->getCurrentUser();
        $companies = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1 AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")")));
        $branches  = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1 AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")")));
        $this->set(compact('branches', 'companies'));
    }
    
    function netProfitResult(){
        $this->layout = 'ajax';
    }
    
    function netProfitMonthResult(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $branches = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1 AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")")));
        $this->set(compact('branches'));
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
    
    function agency() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $companies = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1 AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")")));
        $branches = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1 AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")")));
        $agents   = ClassRegistry::init('TAgent')->find('all', array('joins' => array(array('table' => 't_tickets', 'type' => 'inner', 'conditions' => array('t_tickets.t_agent_id=TAgent.id'))),"conditions" => array("TAgent.status = 1"), 'order' => 'TAgent.code', 'group' => 'TAgent.id'));
        $tDepartureTimes = ClassRegistry::init('TDepartureTime')->find('all', array("conditions" => array("TDepartureTime.is_active = 1"), 'order' => 'TDepartureTime.name'));
        $this->set(compact('branches', 'agents', 'companies', 'tDepartureTimes'));
    }

    function agencyResult() {
        $this->layout = 'ajax';
    }
    
    function dailyRevenueSummary() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $branches = ClassRegistry::init('Branch')->find('list', array("conditions" => array("Branch.is_active = 1 AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")")));
        $tJourneyTypes = ClassRegistry::init('TJourneyType')->find('list', array("conditions" => array("TDestination.is_active = 1")));
        $this->set(compact('branches', 'tJourneyTypes'));
    }

    function dailyRevenueSummaryResult() {
        $this->layout = 'ajax';
    }
    
    function agentPaid($id = null){
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Agency Payment');
        mysql_query("UPDATE t_tickets SET is_agent_confirm = 1, agent_confirm_date = '".date("Y-m-d H:i:s")."', agent_confirm_by = ".$user['User']['id']." WHERE id = ".$id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;  
    }
    
    function getDestinationTo($destinationFrom = null){
        $user = $this->getCurrentUser();
        $option = '<option value="">'.INPUT_SELECT.'</option>';
        $condition = "";
        if($user['User']['is_admin'] != 0){
            $condition .= " AND offline_project_id = ".$user['User']['offline_project_id'];
        }
        if (!empty($destinationFrom)) {
            $sqlDest = mysql_query("SELECT id, name FROM t_destinations WHERE is_active = 1".$condition." AND id IN (SELECT t_destination_to_id FROM t_destination_tos WHERE t_destination_from_id = ".$destinationFrom.") GROUP BY id");
        } else {
            $sqlDest = mysql_query("SELECT id, name FROM t_destinations WHERE is_active = 1".$condition." GROUP BY id");
        }
        while($rowDest = mysql_fetch_array($sqlDest)){
            $option .= '<option value="'.$rowDest['id'].'">'.$rowDest['name'].'</option>';
        }
        echo $option;
        exit;
    }
    
    /**
     * Sate Ticket Agency Offline
     */
    function salesTicketAgency() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        $usrCondition = "";
        $payCondition = "";
        $agentCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id'];
            $usrCondition = " AND User.offline_project_id = ".$user['User']['offline_project_id'];
            $payCondition = " AND (PaymentMethod.offline_project_id = ".$user['User']['offline_project_id']." OR id = 1)";
            if($user['User']['type'] == 2){
                $agentCondition = " AND TAgent.offline_project_id = ".$user['User']['offline_project_id'];
            } else {
                $agentCondition = " AND TAgent.offline_project_id = ".$user['User']['offline_project_id']." AND TAgent.user_id = ".$user['User']['id'];
            }
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
                if($user['User']['type'] == 2){
                    $usrCondition .= " AND User.id IN (SELECT user_id FROM user_branches WHERE branch_id IN (".$branchUse."))";
                } else {
                    $usrCondition .= " AND User.id = ".$user['User']['id'];
                }
            }
        }
        $companies    = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $users        = ClassRegistry::init('User')->find('list', array("conditions" => array("User.is_active = 1".$usrCondition), 'fields' => array('id', 'username')));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $tAgents      = ClassRegistry::init('TAgent')->find('list', array("conditions" => array("TAgent.status = 1 AND TAgent.type = 2".$agentCondition)));
        $paymentMethods = ClassRegistry::init('PaymentMethod')->find('list', array("conditions" => array("PaymentMethod.is_active = 1".$payCondition)));
        $this->set(compact('branches', 'destinations', 'companies', 'users', 'paymentMethods', 'tAgents'));
    }

    function salesTicketAgencyResult() {
        $this->layout = 'ajax';
    }

    function salesTicketAgencyAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(",", $data);
        $this->set("data", $data);
    }
    
    /**
     * Sate Ticket Agency Online
     */
    function salesTicketAgencyOnline() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        $agentCondition = "";
        $mBrCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            $mBrCondition = " AND MainBranch.offline_project_id = 1";
            if($user['User']['type'] == 2){
                $agentCondition = " AND TAgent.offline_project_id = ".$user['User']['offline_project_id'];
            } else {
                $agentCondition = " AND TAgent.offline_project_id = ".$user['User']['offline_project_id']." AND TAgent.user_id = ".$user['User']['id'];
            }
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
                // $mBrCondition .= " AND MainBranch.id = ".$user['User']['main_branch_id'];
            }
        }
        $companies    = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $tAgents      = ClassRegistry::init('TAgent')->find('list', array("conditions" => array("TAgent.status = 1 AND TAgent.id != 55".$agentCondition)));
        $mainBranches = ClassRegistry::init('MainBranch')->find('list', array("fields" => array("MainBranch.id", "MainBranch.name"), "conditions" => array("MainBranch.is_active = 1".$mBrCondition)));
        $this->set(compact('branches', 'destinations', 'companies', 'users', 'paymentMethods', 'tAgents', 'mainBranches'));
    }

    function salesTicketAgencyOnlineResult() {
        $this->layout = 'ajax';
    }

    function salesTicketAgencyOnlineAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(",", $data);
        $this->set("data", $data);
    }
    
    /**
     * Sate Ticket Main Branch
     */
    function salesTicketBranch() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        $usrCondition = "";
        $payCondition = "";
        $mBrCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            $usrCondition = " AND User.offline_project_id = ".$user['User']['offline_project_id'];
            $payCondition = " AND (PaymentMethod.offline_project_id = ".$user['User']['offline_project_id']." OR id = 1)";
            $mBrCondition = " AND MainBranch.offline_project_id = ".$user['User']['offline_project_id'];
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
                $usrCondition .= " AND User.id IN (SELECT user_id FROM user_branches WHERE branch_id IN (".$branchUse."))";
                $mBrCondition .= " AND MainBranch.id = ".$user['User']['main_branch_id'];
            }
        }
        $companies    = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $users        = ClassRegistry::init('User')->find('list', array("conditions" => array("User.is_active = 1".$usrCondition), 'fields' => array('id', 'username')));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $paymentMethods = ClassRegistry::init('PaymentMethod')->find('list', array("conditions" => array("PaymentMethod.is_active = 1".$payCondition)));
        $mainBranches   = ClassRegistry::init('MainBranch')->find('list', array("fields" => array("MainBranch.id", "MainBranch.name"), "conditions" => array("MainBranch.is_active = 1".$mBrCondition)));
        $this->set(compact('branches', 'destinations', 'companies', 'users', 'paymentMethods', 'mainBranches'));
    }

    function salesTicketBranchResult() {
        $this->layout = 'ajax';
    }

    function salesTicketBranchAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(";", $data);
        $this->set("data", $data);
    }

    function salesTicketBranchSummary() {
        $this->layout = 'ajax';
    }
    
    /**
     * Agency Balance
     */
    function agencyBalance() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $agentCondition = "";
        if($user['User']['type'] != 1){
            if($user['User']['type'] == 2){
                $agentCondition = " AND TAgent.offline_project_id = 1";
            } else {
                $agentCondition = " AND TAgent.offline_project_id = 1 AND TAgent.user_id = ".$user['User']['id'];
            }
        }
        $tAgents = ClassRegistry::init('TAgent')->find('list', array("conditions" => array("TAgent.status = 1".$agentCondition)));
        $this->set(compact('tAgents'));
    }

    function agencyBalanceResult() {
        $this->layout = 'ajax';
    }

    function agencyBalanceAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(",", $data);
        $this->set("data", $data);
    }

    function phoneCall(){
        $this->layout = 'ajax';
        $this->set('dateRange', $this->dateRange());
        $user = $this->getCurrentUser();
        $comCondition = "";
        $braCondition = "";
        $mBrCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $mBrCondition = " AND MainBranch.offline_project_id = ".$user['User']['offline_project_id'];
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
                $mBrCondition .= " AND MainBranch.id = ".$user['User']['main_branch_id'];
            }
        }
        $companies    = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $mainBranches = ClassRegistry::init('MainBranch')->find('list', array("fields" => array("MainBranch.id", "MainBranch.name"), "conditions" => array("MainBranch.is_active = 1".$mBrCondition)));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $this->set(compact('branches', 'companies', 'mainBranches'));
    }
    
    function phoneCallResult(){
        $this->layout = 'ajax';
    }

    function cancelPhoneCall(){
        $this->layout = 'ajax';
        $this->set('dateRange', $this->dateRange());
        $user = $this->getCurrentUser();
        $comCondition = "";
        $braCondition = "";
        $mBrCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $mBrCondition = " AND MainBranch.offline_project_id = ".$user['User']['offline_project_id'];
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
                $mBrCondition .= " AND MainBranch.id = ".$user['User']['main_branch_id'];
            }
        }
        $companies    = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $mainBranches = ClassRegistry::init('MainBranch')->find('list', array("fields" => array("MainBranch.id", "MainBranch.name"), "conditions" => array("MainBranch.is_active = 1".$mBrCondition)));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $this->set(compact('branches', 'companies', 'mainBranches'));
    }
    
    function cancelPhoneCallResult(){
        $this->layout = 'ajax';
    }
    
    /**
     * Sate Ticket Terminal
     */
    function salesTicketTerminal() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        $agentCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            if($user['User']['type'] == 2){
                $agentCondition = " AND TAgent.offline_project_id = ".$user['User']['offline_project_id'];
            } else {
                $agentCondition = " AND TAgent.offline_project_id = ".$user['User']['offline_project_id']." AND TAgent.user_id = ".$user['User']['id'];
            }
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
            }
        }
        $companies    = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $terminals    = ClassRegistry::init('Terminal')->find('list', array("conditions" => array("Terminal.status = 2")));
        $this->set(compact('branches', 'destinations', 'companies', 'users', 'paymentMethods', 'terminals'));
    }

    function salesTicketTerminalResult() {
        $this->layout = 'ajax';
    }

    function salesTicketTerminalAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(",", $data);
        $this->set("data", $data);
    }

    /**
     * Sate Ticket Website
     */
    function salesTicketWebsite() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
            }
        }
        $companies    = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $this->set(compact('branches', 'destinations', 'companies', 'users', 'paymentMethods'));
    }

    function salesTicketWebsiteResult() {
        $this->layout = 'ajax';
    }

    function salesTicketWebsiteAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(",", $data);
        $this->set("data", $data);
    }

    /**
     * Sate Ticket Open
     */
    function salesTicketOpen() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        $usrCondition = "";
        $payCondition = "";
        $mBrCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            $usrCondition = " AND User.offline_project_id = ".$user['User']['offline_project_id'];
            $payCondition = " AND (PaymentMethod.offline_project_id = ".$user['User']['offline_project_id']." OR id = 1)";
            $mBrCondition = " AND MainBranch.offline_project_id = ".$user['User']['offline_project_id'];
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
                $usrCondition .= " AND User.id IN (SELECT user_id FROM user_branches WHERE branch_id IN (".$branchUse."))";
                $mBrCondition .= " AND MainBranch.id = ".$user['User']['main_branch_id'];
            }
        }
        $companies    = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $users        = ClassRegistry::init('User')->find('list', array("conditions" => array("User.is_active = 1".$usrCondition), 'fields' => array('id', 'username')));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $paymentMethods = ClassRegistry::init('PaymentMethod')->find('list', array("conditions" => array("PaymentMethod.is_active = 1".$payCondition)));
        $mainBranches   = ClassRegistry::init('MainBranch')->find('list', array("fields" => array("MainBranch.id", "MainBranch.name"), "conditions" => array("MainBranch.is_active = 1".$mBrCondition)));
        $this->set(compact('branches', 'destinations', 'companies', 'users', 'paymentMethods', 'mainBranches'));
    }

    function salesTicketOpenResult() {
        $this->layout = 'ajax';
    }

    function salesTicketOpenAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(";", $data);
        $this->set("data", $data);
    }

    /**
     * Sate Ticket Agency (PostPaid)
     */
    function salesTicketAgencyPostpaid() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        $agentCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            if($user['User']['type'] == 2){
                $agentCondition = " AND TAgent.offline_project_id = ".$user['User']['offline_project_id'];
            } else {
                $agentCondition = " AND TAgent.offline_project_id = ".$user['User']['offline_project_id']." AND TAgent.user_id = ".$user['User']['id'];
            }
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
            }
        }
        $companies     = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $branches      = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $destinations  = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $tAgents       = ClassRegistry::init('TAgent')->find('list', array("conditions" => array("TAgent.status = 1 AND TAgent.type = 2 AND TAgent.payment = 2 AND TAgent.id != 55".$agentCondition)));
        $tAgentTypes   = ClassRegistry::init('TAgentTypes')->find('list', array("conditions" => array("TAgentTypes.is_active = 1")));
        $mainBranches  = ClassRegistry::init('MainBranch')->find('list', array("conditions" => array("MainBranch.is_active = 1")));
        $this->set(compact('branches', 'destinations', 'companies', 'users', 'paymentMethods', 'tAgents', 'tAgentTypes', 'mainBranches'));
    }

    function salesTicketAgencyPostpaidResult() {
        $this->layout = 'ajax';
    }

    function salesTicketAgencyPostpaidAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(",", $data);
        $this->set("data", $data);
    }

    function salesTicketAgencyPostpaidSummary() {
        $this->layout = 'ajax';
    }

    function salesTicketAgencyPostpaidPrint($id=null){
        $this->layout = 'ajax';
        if(!$id){
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Print Agency Offline Postpaid Claim', $id);
        $this->set(compact('id'));
    }

    /**
     * Sate Ticket VAT
     */
    function salesTicketVat() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        $usrCondition = "";
        $payCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            $usrCondition = " AND User.offline_project_id = ".$user['User']['offline_project_id'];
            $payCondition = " AND (PaymentMethod.offline_project_id = ".$user['User']['offline_project_id']." OR id = 1)";
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
                $usrCondition .= " AND User.id IN (SELECT user_id FROM user_branches WHERE branch_id IN (".$branchUse."))";
            }
        }
        $companies    = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $this->set(compact('branches', 'destinations', 'companies'));
    }

    function salesTicketVatResult() {
        $this->layout = 'ajax';
    }

    /**
     * Sate Ticket Agency (PostPaid)
     */
    function salesTicketAgencyPrepaid() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        $agentCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            if($user['User']['type'] == 2){
                $agentCondition = " AND TAgent.offline_project_id = ".$user['User']['offline_project_id'];
            } else {
                $agentCondition = " AND TAgent.offline_project_id = ".$user['User']['offline_project_id']." AND TAgent.user_id = ".$user['User']['id'];
            }
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
            }
        }
        $companies    = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $tAgents      = ClassRegistry::init('TAgent')->find('list', array("conditions" => array("TAgent.status = 1 AND TAgent.payment = 1 AND TAgent.type = 1 AND TAgent.id != 55".$agentCondition)));
        $mainBranches  = ClassRegistry::init('MainBranch')->find('list', array("conditions" => array("MainBranch.is_active = 1")));
        $this->set(compact('branches', 'destinations', 'companies', 'users', 'paymentMethods', 'tAgents', 'mainBranches'));
    }

    function salesTicketAgencyPrepaidResult() {
        $this->layout = 'ajax';
    }

    function salesTicketAgencyPrepaidAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(",", $data);
        $this->set("data", $data);
    }

    function salesTicketAgencyPrepaidSummary() {
        $this->layout = 'ajax';
    }

    /**
     * Agency Balance Offline
     */
    function agencyBalanceOffline() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $agentCondition = " AND TAgent.offline_project_id = 1 AND TAgent.type = 2";
        $tAgents = ClassRegistry::init('TAgent')->find('list', array("conditions" => array("TAgent.status = 1".$agentCondition)));
        $this->set(compact('tAgents'));
    }

    function agencyBalanceOfflineResult() {
        $this->layout = 'ajax';
    }

    function agencyBalanceOfflineAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(",", $data);
        $this->set("data", $data);
    }

    function agencyBalanceOfflineSummary() {
        $this->layout = 'ajax';
    }

    /**
     * Agency VET Digital
     */
    function salesTicketAgencyVetDigital() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $DesCondition = "";
        $comCondition = "";
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
        }
        $companies    = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $this->set(compact('destinations', 'companies'));
    }

    function salesTicketAgencyVetDigitalResult() {
        $this->layout = 'ajax';
    }

    /**
     * Survey
     */
    function userFeedback() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
    }

    function userFeedbackResult() {
        $this->layout = 'ajax';
    }

    function userFeedbackSummary() {
        $this->layout = 'ajax';
    }

    function userFeedbackView($busId = null, $dateFrom = "", $dateTo = "") {
        $this->layout = 'ajax';
        if(empty($busId) || empty($dateFrom) || empty($dateTo)){
            echo "Invalid Data";
            exit;
        }
        $this->set(compact('busId', 'dateFrom', 'dateTo'));
    }

    /**
     * Sate Ticket Bkk and Buva Sea
     */
    function salesTicketBkkBuva() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
    }

    function salesTicketBkkBuvaResult() {
        $this->layout = 'ajax';
    }

    /**
     * Agency Pop Up Balance
     */
    function agencyPopupBalance() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
    }

    function agencyPopupBalanceResult() {
        $this->layout = 'ajax';
    }

    function agencyPopupBalanceSummary() {
        $this->layout = 'ajax';
    }

    /**
     * Sate Ticket Main Branch
     */
    function salesSummary() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        $usrCondition = "";
        $payCondition = "";
        $mBrCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            $usrCondition = " AND User.offline_project_id = ".$user['User']['offline_project_id'];
            $payCondition = " AND (PaymentMethod.offline_project_id = ".$user['User']['offline_project_id']." OR id = 1)";
            $mBrCondition = " AND MainBranch.offline_project_id = ".$user['User']['offline_project_id'];
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
                $usrCondition .= " AND User.id IN (SELECT user_id FROM user_branches WHERE branch_id IN (".$branchUse."))";
                $mBrCondition .= " AND MainBranch.id = ".$user['User']['main_branch_id'];
            }
        }
        $companies    = ClassRegistry::init('Company')->find('all', array("conditions" => array("Company.is_active = 1".$comCondition)));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $users        = ClassRegistry::init('User')->find('list', array("conditions" => array("User.is_active = 1".$usrCondition), 'fields' => array('id', 'username')));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $origins      = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1")));
        $paymentMethods = ClassRegistry::init('PaymentMethod')->find('list', array("conditions" => array("PaymentMethod.is_active = 1".$payCondition)));
        $mainBranches   = ClassRegistry::init('MainBranch')->find('list', array("fields" => array("MainBranch.id", "MainBranch.name"), "conditions" => array("MainBranch.is_active = 1".$mBrCondition)));
        $provinces      = ClassRegistry::init('Province')->find('list', array("conditions" => array("Province.is_active = 1")));
        $tAgents      = ClassRegistry::init('TAgent')->find('list', array("conditions" => array("TAgent.status = 1")));
        $this->set(compact('branches', 'destinations', 'companies', 'users', 'paymentMethods', 'mainBranches', 'provinces', 'origins', 'tAgents'));
    }

    function salesSummaryResult() {
        $this->layout = 'ajax';
    }

    function salesSummaryAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(";", $data);
        $this->set("data", $data);
    }

    function salesSummaryAll() {
        $this->layout = 'ajax';
    }

    function agentPostPaidPaid($id = null, $type = null){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $result['error'] = 1;
        if(!empty($id) && !empty($type)){
            $sqlTicketBalance = mysql_query("SELECT * FROM agency_balances WHERE t_ticket_id = ".$id." AND module = 'Ticket Booking' LIMIT 1");
            if(mysql_num_rows($sqlTicketBalance)){
                $rowTicketBalance = mysql_fetch_array($sqlTicketBalance);
                $credit = 0;
                $debit  = 0;
                if($type == 2) { // Paid
                    $module = 'Agent PostPaid Paid';
                    $credit = $rowTicketBalance['debit'];
                    $isPaid = 1;
                } else { // Void Paid
                    $module = 'Agent PostPaid Void Paid';
                    $debit  = $rowTicketBalance['debit'];
                    $isPaid = 0;
                }
                mysql_query("INSERT INTO `agency_balances` (`t_agency_id`, `t_ticket_id`, `net_price`, `vat_price`, `bonus`, `reference`, `debit`, `credit`, `type`, `module`, `created`, `created_by`) 
                VALUES (".$rowTicketBalance['t_agency_id'].", ".$rowTicketBalance['t_ticket_id'].", ".$rowTicketBalance['net_price'].", ".$rowTicketBalance['vat_price'].", ".$rowTicketBalance['bonus'].", '".$rowTicketBalance['reference']."', ".$debit.", ".$credit.", ".$type.", '".$module."', now(), ".$user['User']['id'].");");
                mysql_query("UPDATE t_tickets SET is_agent_paid = ".$isPaid." WHERE id = ".$id);
                $result['error'] = 0;
            }
        }
        echo json_encode($result);
        exit;
    }

    /**
     * Sate Summary By Branch
     */
    function salesSummaryByBranch() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $mBrCondition = "";
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $mBrCondition = " AND MainBranch.offline_project_id = ".$user['User']['offline_project_id'];
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $mBrCondition .= " AND MainBranch.id = ".$user['User']['main_branch_id'];
            }
        }
        $companies    = ClassRegistry::init('Company')->find('all', array("conditions" => array("Company.is_active = 1".$comCondition)));
        $mainBranches = ClassRegistry::init('MainBranch')->find('list', array("fields" => array("MainBranch.id", "MainBranch.name"), "conditions" => array("MainBranch.is_active = 1".$mBrCondition)));
        $tAgents      = ClassRegistry::init('TAgent')->find('list', array("conditions" => array("TAgent.status = 1 AND TAgent.type = 2 AND TAgent.offline_project_id = 1")));
        $this->set(compact('companies', 'tAgents', 'mainBranches'));
    }

    function salesSummaryByBranchResult() {
        $this->layout = 'ajax';
    }

    /**
     * Sate Ticket Agency Online (PostPaid)
     */
    function agencyOnlinePostpaid() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        $agentCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            if($user['User']['type'] == 2){
                $agentCondition = " AND TAgent.offline_project_id = ".$user['User']['offline_project_id'];
            } else {
                $agentCondition = " AND TAgent.offline_project_id = ".$user['User']['offline_project_id']." AND TAgent.user_id = ".$user['User']['id'];
            }
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
            }
        }
        $companies     = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $branches      = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $destinations  = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $tAgents       = ClassRegistry::init('TAgent')->find('list', array("conditions" => array("TAgent.status = 1 AND TAgent.type = 1 AND TAgent.payment = 2 AND TAgent.id != 55".$agentCondition)));
        $tAgentTypes   = ClassRegistry::init('TAgentTypes')->find('list', array("conditions" => array("TAgentTypes.is_active = 1")));
        $mainBranches  = ClassRegistry::init('MainBranch')->find('list', array("conditions" => array("MainBranch.is_active = 1")));
        $this->set(compact('branches', 'destinations', 'companies', 'users', 'paymentMethods', 'tAgents', 'tAgentTypes', 'mainBranches'));
    }

    function agencyOnlinePostpaidResult() {
        $this->layout = 'ajax';
    }

    function agencyOnlinePostpaidAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(",", $data);
        $this->set("data", $data);
    }

    function agencyOnlinePostpaidSummary() {
        $this->layout = 'ajax';
    }

    function agencyOnlinePostpaidClaim($type = 1){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $result['error'] = 1;
        if(!empty($_POST['tid'])){
            $this->loadModel('agencyPostpaidClaim');
            $this->agencyPostpaidClaim->create();
            $this->data['agencyPostpaidClaim']['date']       = date("Y-m-d");
            $this->data['agencyPostpaidClaim']['created']    = date("Y-m-d");
            $this->data['agencyPostpaidClaim']['created_by'] = $user['User']['id'];
            $this->data['agencyPostpaidClaim']['type']       = $type;
            if($this->agencyPostpaidClaim->save($this->data)) {
                $claimId = $this->agencyPostpaidClaim->id;
                mysql_query("UPDATE agency_postpaid_claims SET `code` = CONCAT('AGP','',LPAD(".$claimId.",7,'0')) WHERE id = ".$claimId);
                for ($i = 0; $i < sizeof($_POST['tid']); $i++) {
                    $sqlTicketBalance = mysql_query("SELECT * FROM agency_balances WHERE t_ticket_id = ".$_POST['tid'][$i]." AND module = 'Ticket Booking' LIMIT 1");
                    if(mysql_num_rows($sqlTicketBalance)){
                        $rowTicketBalance = mysql_fetch_array($sqlTicketBalance);
                        $credit = $rowTicketBalance['debit'];
                        $debit  = 0;
                        $module = 'Agent PostPaid Paid';
                        $isPaid = 1;
                        mysql_query("INSERT INTO `agency_balances` (`t_agency_id`, `t_ticket_id`, `net_price`, `vat_price`, `bonus`, `reference`, `debit`, `credit`, `type`, `module`, `created`, `created_by`) 
                        VALUES (".$rowTicketBalance['t_agency_id'].", ".$rowTicketBalance['t_ticket_id'].", ".$rowTicketBalance['net_price'].", ".$rowTicketBalance['vat_price'].", ".$rowTicketBalance['bonus'].", '".$rowTicketBalance['reference']."', ".$debit.", ".$credit.", 1, '".$module."', now(), ".$user['User']['id'].");");
                        $sqlTck = mysql_query("SELECT * FROM t_tickets WHERE id = ".$_POST['tid'][$i]);
                        if(mysql_num_rows($sqlTck)){
                            $tblTicket = "t_tickets";
                        } else {
                            $sqlTck = mysql_query("SELECT * FROM t_ticket_3months WHERE id = ".$_POST['tid'][$i]);
                            if(mysql_num_rows($sqlTck)){
                                $tblTicket = "t_ticket_3months";
                            } else {
                                $tblTicket = "2023_t_tickets";
                            }
                        }
                        mysql_query("UPDATE ".$tblTicket." SET is_agent_paid = ".$isPaid." WHERE id = ".$_POST['tid'][$i]);
                        // Update Claim Detail
                        mysql_query("INSERT INTO `agency_postpaid_claim_details` (`agency_postpaid_claim_id`, `t_ticket_id`, `created`, `created_by`) 
                                     VALUES (".$claimId.", ".$_POST['tid'][$i].", now(), ".$user['User']['id'].")");
                    }   
                }
                $result['id'] = $claimId;  
                $result['error'] = 0;  
            }
        }
        echo json_encode($result);
        exit;
    }

    function agencyOnlinePostpaidPrint($id=null){
        $this->layout = 'ajax';
        if(!$id){
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Ticket', 'Print Agency Online Postpaid Claim', $id);
        $this->set(compact('id'));
    }

    /**
     * Sate Ticket Agency Online (PostPaid) Invoice
     */
    function agencyOnlinePostpaidInvoice() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $mBrCondition = " AND MainBranch.offline_project_id = 1";
        if($user['User']['type'] != 1){
            if($user['User']['is_admin'] == 0){
                $mBrCondition .= " AND MainBranch.id = ".$user['User']['main_branch_id'];
            }
        }
        $tAgents      = ClassRegistry::init('TAgent')->find('list', array("conditions" => array("TAgent.status = 1 AND TAgent.type = 1 AND TAgent.payment = 2 AND TAgent.id != 55 AND TAgent.offline_project_id = 1")));
        $tAgentTypes  = ClassRegistry::init('TAgentTypes')->find('list', array("conditions" => array("TAgentTypes.is_active = 1")));
        $mainBranches = ClassRegistry::init('MainBranch')->find('list', array("fields" => array("MainBranch.id", "MainBranch.name"), "conditions" => array("MainBranch.is_active = 1".$mBrCondition)));
        $this->set(compact('tAgents', 'tAgentTypes', 'mainBranches'));
    }

    function agencyOnlinePostpaidInvoiceResult() {
        $this->layout = 'ajax';
    }

    function agencyOnlinePostpaidInvoiceAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(",", $data);
        $this->set("data", $data);
    }

    function agencyOnlinePostPaidUnpaid($id = null){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $result['error'] = 1;
        if(!empty($id)){
            $sqlTicketBalance = mysql_query("SELECT * FROM agency_balances WHERE t_ticket_id = ".$id." AND module = 'Ticket Booking' LIMIT 1");
            if(mysql_num_rows($sqlTicketBalance)){
                $rowTicketBalance = mysql_fetch_array($sqlTicketBalance);
                $credit = 0;
                $debit  = $rowTicketBalance['debit'];
                $module = 'Agent Online PostPaid Void Paid';
                $isPaid = 0;
                mysql_query("INSERT INTO `agency_balances` (`t_agency_id`, `t_ticket_id`, `net_price`, `vat_price`, `bonus`, `reference`, `debit`, `credit`, `type`, `module`, `created`, `created_by`) 
                VALUES (".$rowTicketBalance['t_agency_id'].", ".$rowTicketBalance['t_ticket_id'].", ".$rowTicketBalance['net_price'].", ".$rowTicketBalance['vat_price'].", ".$rowTicketBalance['bonus'].", '".$rowTicketBalance['reference']."', ".$debit.", ".$credit.", 1, '".$module."', now(), ".$user['User']['id'].");");
                $sqlTck = mysql_query("SELECT * FROM t_tickets WHERE id = ".$id);
                if(mysql_num_rows($sqlTck)){
                    $tblTicket = "t_tickets";
                } else {
                    $sqlTck = mysql_query("SELECT * FROM t_ticket_3months WHERE id = ".$id);
                    if(mysql_num_rows($sqlTck)){
                        $tblTicket = "t_ticket_3months";
                    } else {
                        $tblTicket = "2023_t_tickets";
                    }
                }
                mysql_query("UPDATE ".$tblTicket." SET is_agent_paid = ".$isPaid." WHERE id = ".$id);
                mysql_query("UPDATE agency_postpaid_claim_details SET is_active = 2 WHERE t_ticket_id = ".$id);
                $result['error'] = 0;
            }
        }
        echo json_encode($result);
        exit;
    }

    /**
     * Sate Ticket Free
     */
    function salesTicketFree() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $DesCondition = "";
        $mBrCondition = "";
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            $mBrCondition = " AND MainBranch.offline_project_id = ".$user['User']['offline_project_id'];
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $mBrCondition .= " AND MainBranch.id = ".$user['User']['main_branch_id'];
            }
        }
        $companies    = ClassRegistry::init('Company')->find('list', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $mainBranches = ClassRegistry::init('MainBranch')->find('list', array("fields" => array("MainBranch.id", "MainBranch.name"), "conditions" => array("MainBranch.is_active = 1".$mBrCondition)));
        $this->set(compact('branches', 'destinations', 'companies', 'mainBranches'));
    }

    function salesTicketFreeResult() {
        $this->layout = 'ajax';
    }

    function salesTicketFreeAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(";", $data);
        $this->set("data", $data);
    }

    /**
     * Sate Ticket Online
     */
    function salesTicketOnline() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
            }
        }
        $companies    = ClassRegistry::init('Company')->find('all', array("conditions" => array("Company.is_active = 1".$comCondition), 'order' => 'Company.name'));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $this->set(compact('branches', 'destinations', 'companies', 'users', 'paymentMethods'));
    }

    function salesTicketOnlineResult() {
        $this->layout = 'ajax';
    }

    function salesTicketOnlineAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(",", $data);
        $this->set("data", $data);
    }
    /**
     * Sate Ticket Lucky Draw
     */
    function salesTicketLuckyDraw() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $DesCondition = "";
        $mBrCondition = "";
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            $mBrCondition = " AND MainBranch.offline_project_id = ".$user['User']['offline_project_id'];
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $mBrCondition .= " AND MainBranch.id = ".$user['User']['main_branch_id'];
            }
        }
        $companies    = ClassRegistry::init('Company')->find('all', array("conditions" => array("Company.is_active = 1".$comCondition)));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $mainBranches = ClassRegistry::init('MainBranch')->find('list', array("fields" => array("MainBranch.id", "MainBranch.name"), "conditions" => array("MainBranch.is_active = 1".$mBrCondition)));
        $this->set(compact('branches', 'destinations', 'companies', 'mainBranches'));
    }

    function salesTicketLuckyDrawResult() {
        $this->layout = 'ajax';
    }

    function salesTicketLuckyDrawAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(";", $data);
        $this->set("data", $data);
    }

    /**
     * Customer Total Booked
     */
    function customerTotalBooked() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
    }

    function customerTotalBookedResult() {
        $this->layout = 'ajax';
    }

    /**
     * Sate Ticket Release
     */
    function salesTicketRelease() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $tJourneys = ClassRegistry::init('TJourney')->find('list', array("conditions" => array("TJourney.status = 1 AND TJourney.offline_project_id = 1"), "fields" => array("id", "description")));
        $this->set(compact('tJourneys'));
    }

    function salesTicketReleaseResult() {
        $this->layout = 'ajax';
    }

    function salesTicketReleaseAjax($post = null) {
        $this->layout = 'ajax';
        $data = explode("-", $post);
        $this->set("data", $data);
    }

    /**
     * Sales Summary City / Province
     */
    function salesSummaryProvince() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        $usrCondition = "";
        $payCondition = "";
        $mBrCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            $usrCondition = " AND User.offline_project_id = ".$user['User']['offline_project_id'];
            $payCondition = " AND (PaymentMethod.offline_project_id = ".$user['User']['offline_project_id']." OR id = 1)";
            $mBrCondition = " AND MainBranch.offline_project_id = ".$user['User']['offline_project_id'];
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
                $usrCondition .= " AND User.id IN (SELECT user_id FROM user_branches WHERE branch_id IN (".$branchUse."))";
                $mBrCondition .= " AND MainBranch.id = ".$user['User']['main_branch_id'];
            }
        }
        $companies    = ClassRegistry::init('Company')->find('all', array("conditions" => array("Company.is_active = 1".$comCondition)));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $users        = ClassRegistry::init('User')->find('list', array("conditions" => array("User.is_active = 1".$usrCondition), 'fields' => array('id', 'username')));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $origins      = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1")));
        $paymentMethods = ClassRegistry::init('PaymentMethod')->find('list', array("conditions" => array("PaymentMethod.is_active = 1".$payCondition)));
        $mainBranches   = ClassRegistry::init('MainBranch')->find('list', array("fields" => array("MainBranch.id", "MainBranch.name"), "conditions" => array("MainBranch.is_active = 1".$mBrCondition)));
        $provinces      = ClassRegistry::init('Province')->find('list', array("conditions" => array("Province.is_active = 1")));
        $this->set(compact('branches', 'destinations', 'companies', 'users', 'paymentMethods', 'mainBranches', 'provinces', 'origins'));
    }

    function salesSummaryProvinceResult() {
        $this->layout = 'ajax';
    }

    /**
     * Sales Summary Nationality
     */
    function salesSummaryNational() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $braCondition = "";
        $DesCondition = "";
        $usrCondition = "";
        $payCondition = "";
        $mBrCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $braCondition = " AND Branch.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            $usrCondition = " AND User.offline_project_id = ".$user['User']['offline_project_id'];
            $payCondition = " AND (PaymentMethod.offline_project_id = ".$user['User']['offline_project_id']." OR id = 1)";
            $mBrCondition = " AND MainBranch.offline_project_id = ".$user['User']['offline_project_id'];
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $braCondition .= " AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
                $usrCondition .= " AND User.id IN (SELECT user_id FROM user_branches WHERE branch_id IN (".$branchUse."))";
                $mBrCondition .= " AND MainBranch.id = ".$user['User']['main_branch_id'];
            }
        }
        $companies    = ClassRegistry::init('Company')->find('all', array("conditions" => array("Company.is_active = 1".$comCondition)));
        $branches     = ClassRegistry::init('Branch')->find('all', array("conditions" => array("Branch.is_active = 1".$braCondition), 'order' => 'Branch.name'));
        $users        = ClassRegistry::init('User')->find('list', array("conditions" => array("User.is_active = 1".$usrCondition), 'fields' => array('id', 'username')));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $origins      = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1")));
        $paymentMethods = ClassRegistry::init('PaymentMethod')->find('list', array("conditions" => array("PaymentMethod.is_active = 1".$payCondition)));
        $mainBranches   = ClassRegistry::init('MainBranch')->find('list', array("fields" => array("MainBranch.id", "MainBranch.name"), "conditions" => array("MainBranch.is_active = 1".$mBrCondition)));
        $provinces      = ClassRegistry::init('Province')->find('list', array("conditions" => array("Province.is_active = 1")));
        $this->set(compact('branches', 'destinations', 'companies', 'users', 'paymentMethods', 'mainBranches', 'provinces', 'origins'));
    }

    function salesSummaryNationalResult() {
        $this->layout = 'ajax';
    }

    /**
     * Sate Ticket Change Shift
     */
    function salesTicketShift() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
    }

    function salesTicketShiftResult() {
        $this->layout = 'ajax';
    }

    function salesTicketShiftAjax($post = null) {
        $this->layout = 'ajax';
        $data = explode("-", $post);
        $this->set("data", $data);
    }

    /**
     * Report Trave Package Order
     */
    function travelPackageBuy() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
    }

    function travelPackageBuyResult() {
        $this->layout = 'ajax';
    }

    /**
     * Sate Ticket Travel Package
     */
    function salesTicketTravelPackage() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $DesCondition = "";
        $mBrCondition = "";
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            $mBrCondition = " AND MainBranch.offline_project_id = ".$user['User']['offline_project_id'];
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
                $mBrCondition .= " AND MainBranch.id = ".$user['User']['main_branch_id'];
            }
        }
        $companies    = ClassRegistry::init('Company')->find('all', array("conditions" => array("Company.is_active = 1".$comCondition)));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $mainBranches = ClassRegistry::init('MainBranch')->find('list', array("fields" => array("MainBranch.id", "MainBranch.name"), "conditions" => array("MainBranch.is_active = 1".$mBrCondition)));
        $travelPackageOrders = ClassRegistry::init('travelPackageOrder')->find('all', array("conditions" => array("travelPackageOrder.status = 2")));
        $this->set(compact('branches', 'destinations', 'companies', 'mainBranches', 'travelPackageOrders'));
    }

    function salesTicketTravelPackageResult() {
        $this->layout = 'ajax';
    }

    function salesTicketTravelPackageAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(";", $data);
        $this->set("data", $data);
    }

    /**
     * Sate Ticket Void
     */
    function salesTicketVoid() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $tJourneys = ClassRegistry::init('TJourney')->find('list', array("conditions" => array("TJourney.status = 1 AND TJourney.offline_project_id = 1"), "fields" => array("id", "description")));
        $this->set(compact('tJourneys'));
    }

    function salesTicketVoidResult() {
        $this->layout = 'ajax';
    }

    function salesTicketVoidAjax($post = null) {
        $this->layout = 'ajax';
        $data = explode("-", $post);
        $this->set("data", $data);
    }

    /**
     * Sales Schedule Summary
     */
    function salesScheduleSummary() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $companies    = ClassRegistry::init('Company')->find('all', array("conditions" => array("Company.is_active = 1 AND Company.offline_project_id = 1")));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1 AND TDestination.offline_project_id = 1")));
        $tJourneys    = ClassRegistry::init('TJourney')->find('list', array("conditions" => array("TJourney.status = 1 AND TJourney.offline_project_id = 1"), "fields" => array("id", "description")));
        $this->set(compact('companies', 'destinations', 'tJourneys'));
    }

    function salesScheduleSummaryResult() {
        $this->layout = 'ajax';
    }

    function salesScheduleSummaryRoute() {
        $this->layout = 'ajax';
    }

    function getAgencyByType($type = null, $payment = null){
        $this->layout = 'ajax';
        $response  = "<option value=''>".TABLE_ALL."</option>";
        if(!empty($type) && !empty($payment)){
            if($type == 3 && $payment == 2){
                $sqlAgency = mysql_query("SELECT id, name FROM t_agents WHERE id != 106 AND id != 55 AND id != 86 AND id != 87 AND id != 91 AND id != 47 AND status = 1 AND `type` = ".$type." AND payment = ".$payment." AND offline_project_id = 1 ORDER BY name ASC");
            } else {
                $sqlAgency = mysql_query("SELECT id, name FROM t_agents WHERE id != 106 AND id != 55 AND status = 1 AND `type` = ".$type." AND payment = ".$payment." AND offline_project_id = 1 ORDER BY name ASC");
            }
            if(mysql_num_rows($sqlAgency)){
                while($rowAgency = mysql_fetch_array($sqlAgency)){
                    $response .= "<option value='".$rowAgency['id']."'>".$rowAgency['name']."</option>";
                }
                $response .= "<option value='vetDg1'>VET Digital (API)</option>";
                $response .= "<option value='vetDg2'>VET App (API)</option>";
                $response .= "<option value='vetDg3'>VET Digital (Manual)</option>";
                $response .= "<option value='vetDg4'>VET APP (Manual)</option>";
            }
        } else {
            $sqlAgency = mysql_query("SELECT id, name FROM t_agents WHERE id != 106 AND id != 55 AND status = 1 AND offline_project_id = 1 ORDER BY name ASC");
            while($rowAgency = mysql_fetch_array($sqlAgency)){
                $response .= "<option value='".$rowAgency['id']."'>".$rowAgency['name']."</option>";
            }
        }
        echo $response;
        exit;
    }

    /**
     * Sate Ticket Main Branch
     */
    function salesByBankSummary() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $comCondition = "";
        $DesCondition = "";
        $branchUse = 0;
        $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM user_branches WHERE user_id = ".$user['User']['id']);
        if(mysql_num_rows($sqlBranch)){
            $rowBranch = mysql_fetch_array($sqlBranch);
            $branchUse = $rowBranch[0];
        }
        if($user['User']['type'] != 1){
            $comCondition = " AND Company.offline_project_id = ".$user['User']['offline_project_id'];
            $DesCondition = " AND TDestination.offline_project_id = ".$user['User']['offline_project_id']." AND TDestination.id IN (SELECT t_destination_id FROM branch_destinations WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
            if($user['User']['is_admin'] == 0){
                $comCondition .= " AND Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
            }
        }
        $companies    = ClassRegistry::init('Company')->find('all', array("conditions" => array("Company.is_active = 1".$comCondition)));
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1".$DesCondition)));
        $origins      = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1")));
        $provinces      = ClassRegistry::init('Province')->find('list', array("conditions" => array("Province.is_active = 1")));
        $this->set(compact('companies', 'destinations', 'origins', 'provinces'));
    }

    function salesByBankSummaryResult() {
        $this->layout = 'ajax';
    }

    /**
     * Sate Ticket Port
     */
    function salesTicketPort() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('dateRange', $this->dateRange());
        $destinations = ClassRegistry::init('TDestination')->find('list', array("conditions" => array("TDestination.is_active = 1 AND TDestination.id IN (26, 32, 121, 24)")));
        $this->set(compact('destinations'));
    }

    function salesTicketPortResult() {
        $this->layout = 'ajax';
    }

    function salesTicketPortAjax($data = null) {
        $this->layout = 'ajax';
        $data = explode(";", $data);
        $this->set("data", $data);
    }

}

?>