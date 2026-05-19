<?php

// GZip components
ob_start("ob_gzhandler");

class AppController extends Controller {

    var $helpers = array('Html', 'Form', 'Javascript', 'Session');
    var $components = array('Session');
    var $menu = array();

    function menu() {
        if(SERVER_TYPE == '1'){
            $syncUrl = '/sync_monitors/server';
            $setting = array('text' => MENU_SYSTEM_SETTINGS, 'url' => '', 'target' => '',
                            'submenu' => array(
                                array('text' => MENU_USER_MANAGEMENT, 'url' => '/users/index', 'target' => 'ajax',
                                    'submenu' => array(
                                        array('text' => MENU_GROUP_MANAGEMENT, 'url' => '/groups/index', 'target' => 'ajax')
                                    )
                                ),
                                array('text' => "Code Generate", 'url' => '/auto_generate_codes/index', 'target' => 'ajax'),
                                array('text' => MENU_PROJECT_SERVER, 'url' => '/offline_servers/index', 'target' => 'ajax'),
                                array('text' => MENU_COMPANY_MANAGEMENT, 'url' => '', 'target' => '',
                                    'submenu' => array(
                                        array('text' => MENU_COMPANY_MANAGEMENT, 'url' => '/companies/index', 'target' => 'ajax'),
                                        array('text' => MENU_BRANCH, 'url' => '/branches/index', 'target' => 'ajax')
                                    )
                                ),
                                array('text' => MENU_MAIN_BRANCH, 'url' => '/main_branches/index', 'target' => 'ajax'),
                                array('text' => MENU_MEMBERSHIP_CARD, 'url' => '/online_customer_tickets/index', 'target' => 'ajax'),
                                array('text' => MENU_RESORT, 'url' => '/resorts/index', 'target' => 'ajax'),
                                array('text' => MENU_COUPON_TYPE, 'url' => '/coupon_types/index', 'target' => 'ajax'),
                                array('text' => MENU_COUPON, 'url' => '/coupons/index', 'target' => 'ajax'),
                                array('text' => MENU_AGENT, 'url' => '/t_agents/index', 'target' => 'ajax'),
                                array('text' => MENU_AGENT_TYPE, 'url' => '/t_agent_types/index', 'target' => 'ajax'),
                                array('text' => MENU_DESTINATION, 'url' => '/t_destinations/index', 'target' => 'ajax'),
                                array('text' => MENU_DESTINATION_GROUP, 'url' => '/t_destination_groups/index', 'target' => 'ajax'),
                                array('text' => MENU_TRANSPORTATION_TYPE, 'url' => '/t_transportation_types/index', 'target' => 'ajax'),
                                array('text' => MENU_ROUTE, 'url' => '/t_routes/index', 'target' => 'ajax'),
//                                array('text' => MENU_JOURNEY_TYPE, 'url' => '/t_journey_types/index', 'target' => 'ajax'),
//                                array('text' => MENU_COMMISION, 'url' => '/t_commisions/index', 'target' => 'ajax'),
//                                array('text' => MENU_BOAT, 'url' => '/t_boats/index', 'target' => 'ajax'),
                                array('text' => MENU_BOARDING_POINT, 'url' => '/t_boarding_points/index', 'target' => 'ajax'),
                                array('text' => MENU_DROP_OFF, 'url' => '/t_drop_offs/index', 'target' => 'ajax'),
                                array('text' => MENU_TERMINAL, 'url' => '/terminals/index', 'target' => 'ajax'),
                                array('text' => MENU_QUESTION_FEEDBACK, 'url' => '/question_feedbacks/index', 'target' => 'ajax'),
                                array('text' => MENU_BUS, 'url' => '/buses/index', 'target' => 'ajax'),
                                array('text' => MENU_BUS_TYPE, 'url' => '/bus_types/index', 'target' => 'ajax'),
                                array('text' => MENU_AMENITY, 'url' => '/amenities/index', 'target' => 'ajax'),
                            )
                        );
        } else {
            $syncUrl = '/sync_monitors/index';
            $setting = array('text' => '', 'url' => '', 'target' => 'ajax');
        }
        $this->menu = array(
            array('text' => MENU_DASHBOARD, 'url' => '/dashboards/index', 'target' => 'ajax'),
            array('text' => MENU_SELL_TICKET, 'url' => '/t_tickets/index', 'target' => 'ajax'),
            array('text' => MENU_SCHEDULE, 'url' => '/schedules/viewSchedule', 'target' => 'ajax'),
            array('text' => MENU_SCHEDULE_TV, 'url' => '', 'target' => '',
                'submenu' => array(
                    array('text' => MENU_SCHEDULE_DISPLAY, 'url' => '/schedules/departureSchedule', 'target' => 'blank'),
                    array('text' => MENU_SCHEDULE_TV_LED, 'url' => '/schedules/tvSchedule', 'target' => 'blank'),
                )
            ),
            array('text' => MENU_ONLINE_BOOKING, 'url' => '/online_orders/index', 'target' => 'ajax'),
            array('text' => MENU_JOURNEY_BUS, 'url' => '/bus_schedules/index', 'target' => 'ajax'),
            array('text' => MENU_JOURNEY_HEAD, 'url' => '', 'target' => '',
                'submenu' => array(
                    array('text' => MENU_JOURNEY, 'url' => '/t_journeys/index', 'target' => 'ajax'),
                    array('text' => MENU_SET_PRICE_PERIOD, 'url' => '/t_journey_price_periods/index', 'target' => 'ajax'),
                    array('text' => MENU_SET_PRICE_DEFAULT, 'url' => '/t_journey_price_defaults/index', 'target' => 'ajax')
                )
            ),
            array('text' => MENU_TRAVEL_PACKAGE, 'url' => '', 'target' => '',
                'submenu' => array(
                    array('text' => MENU_TRAVEL_PACKAGE, 'url' => '/travel_packages/index', 'target' => 'ajax'),
                    array('text' => MENU_TRAVEL_PACKAGE_INTRODUCT, 'url' => '/travel_package_introduces/index', 'target' => 'ajax'),
                    array('text' => MENU_TRAVEL_PACKAGE_CUSTOMER, 'url' => '/travel_package_orders/index', 'target' => 'ajax'),
                    array('text' => REPORT_TRAVEL_PACKAGE_TICKET, 'url' => '/reports/salesTicketTravelPackage', 'target' => 'ajax'),
                )
            ),
            array('text' => MENU_PROMOTION_PACKAGE, 'url' => '', 'target' => '',
                'submenu' => array(
                    array('text' => MENU_PROMOTION_PACKAGE, 'url' => '/promotion_packages/index', 'target' => 'ajax'),
                    array('text' => MENU_PROMOTION_PACKAGE_APPLY, 'url' => '/promotion_apply_packages/index', 'target' => 'ajax'),
                )
            ),
            array('text' => MENU_BUS_RENTAL, 'url' => '/bus_rentals/index', 'target' => 'ajax'),
            array('text' => 'Mini App', 'url' => '', 'target' => '',
                'submenu' => array(
                    array('text' => 'Mini App', 'url' => '/mini_app_partners/index', 'target' => 'ajax'),
                    array('text' => 'Mini App Discount', 'url' => '/mini_app_partner_discounts/index', 'target' => 'ajax')
                )
            ),
            // array('text' => MENU_SYNC_MONITORING, 'url' => $syncUrl, 'target' => 'ajax'),
            // array('text' => MENU_WEBSITE, 'url' => '', 'target' => '',
            //     'submenu' => array(
            //         array('text' => MENU_WEBSITE_ABOUT, 'url' => '/website_abouts/index', 'target' => 'ajax'),
            //         array('text' => MENU_WEBSITE_TERM_CONDITION, 'url' => '/website_termconditions/index', 'target' => 'ajax'),
            //         array('text' => MENU_WEBSITE_POLICY, 'url' => '/website_policies/index', 'target' => 'ajax'),
            //         array('text' => MENU_WEBSITE_GALLERY, 'url' => '/website_galleries/index', 'target' => 'ajax'),
            //         array('text' => MENU_WEBSITE_DISCOVER, 'url' => '/website_discovers/index', 'target' => 'ajax'),
            //         array('text' => MENU_WEBSITE_BANNER, 'url' => '/website_banners/index', 'target' => 'ajax'),
            //     )
            // ),
            $setting,
            array('text' => MENU_REPORT, 'url' => '', 'target' => '',
                'submenu' => array(
                    array('text' => REPORT_COLLECT_BY_USER, 'url' => '/reports/collectByUser', 'target' => 'ajax'),
                    array('text' => REPORT_AGENCY, 'url' => '', 'target' => '',
                        'submenu' => array(
                            array('text' => REPORT_SALES_TICKET_AGENCY_ONLINE, 'url' => '/reports/salesTicketAgencyOnline', 'target' => 'ajax'),
                            array('text' => REPORT_SALES_TICKET_AGENCY_ONLINE." (Prepaid)", 'url' => '/reports/salesTicketAgencyPrepaid', 'target' => 'ajax'),
                            array('text' => REPORT_SALES_TICKET_AGENCY_ONLINE." (Offline Postpaid)", 'url' => '/reports/salesTicketAgencyPostpaid', 'target' => 'ajax'),
                            array('text' => REPORT_SALES_TICKET_AGENCY_ONLINE." (VET Digital)", 'url' => '/reports/salesTicketAgencyVetDigital', 'target' => 'ajax'),
                            array('text' => REPORT_SALES_TICKET_AGENCY_ONLINE_POSTPAID, 'url' => '/reports/agencyOnlinePostpaid', 'target' => 'ajax'),
                            array('text' => REPORT_SALES_TICKET_AGENCY_ONLINE_POSTPAID." (Invoice)", 'url' => '/reports/agencyOnlinePostpaidInvoice', 'target' => 'ajax'),
                            array('text' => REPORT_AGENCY_POP_UP_BALANCE, 'url' => '/reports/agencyPopupBalance', 'target' => 'ajax'),
                            array('text' => REPORT_AGENCY_BALANCE, 'url' => '/reports/agencyBalance', 'target' => 'ajax'),
                            array('text' => REPORT_AGENCY_BALANCE." (Offline)", 'url' => '/reports/agencyBalanceOffline', 'target' => 'ajax')
                        )
                    ),
                    array('text' => MENU_REPORT_PHONE_CALL, 'url' => '/reports/phoneCall', 'target' => 'ajax'),
                    array('text' => MENU_REPORT_CANCEL_PHONE_CALL, 'url' => '/reports/cancelPhoneCall', 'target' => 'ajax'),
                    array('text' => MENU_REPORT_SALES_TICKET_FREE, 'url' => '/reports/salesTicketFree', 'target' => 'ajax'),
                    array('text' => REPORT_TOTAL_CUSTOMER_BOOKED, 'url' => '/reports/customerTotalBooked', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_TICKET_LUCKY_DRAW, 'url' => '/reports/salesTicketLuckyDraw', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_TICKET_BRANCH, 'url' => '/reports/salesTicketBranch', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_TICKET_BRANCH." Summary", 'url' => '/reports/salesSummary', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_TICKET_BRANCH." Summary (City/Province)", 'url' => '/reports/salesSummaryProvince', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_TICKET_BRANCH." Summary (National)", 'url' => '/reports/salesSummaryNational', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_BY_BANK_SUMMARY, 'url' => '/reports/salesByBankSummary', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_JOURNEY_SUMMARY, 'url' => '/reports/salesScheduleSummary', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_TICKET_ONLINE, 'url' => '/reports/salesTicketOnline', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_TICKET_BRANCH." (Company)", 'url' => '/reports/salesSummaryByBranch', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_TICKET_BRANCH." (Open Date)", 'url' => '/reports/salesTicketOpen', 'target' => 'ajax'),
                    array('text' => MENU_REPORT_SALES_TICKET_WEBSITE, 'url' => '/reports/salesTicketWebsite', 'target' => 'ajax'),
                    array('text' => MENU_REPORT_TERMINAL, 'url' => '/reports/salesTicketTerminal', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_TICKET_BRANCH." (VAT)", 'url' => '/reports/salesTicketVat', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_TICKET_BRANCH." (Bkk & Buva Sea)", 'url' => '/reports/salesTicketBkkBuva', 'target' => 'ajax'),
                    array('text' => REPORT_TRAVEL_PACKAGE_ORDER, 'url' => '/reports/travelPackageBuy', 'target' => 'ajax'),
                    array('text' => MENU_SALES_TICKET_BY_SEAT, 'url' => '/reports/salesTicketBySeat', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_TICKET_VOID, 'url' => '/reports/salesTicketVoid', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_TICKET_RELEASE, 'url' => '/reports/salesTicketRelease', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_TICKET_CHANGE_SHIFT, 'url' => '/reports/salesTicketShift', 'target' => 'ajax'),
                    array('text' => REPORT_SALES_KOH_KAMPOT, 'url' => '/reports/salesTicketPort', 'target' => 'ajax'),
                    array('text' => REPORT_SURVEY, 'url' => '/reports/userFeedback', 'target' => 'ajax'),
                    array('text' => REPORT_NET_PROFIT, 'url' => '/reports/netProfit', 'target' => 'ajax'),
                    array('text' => MENU_USERS, 'url' => '', 'target' => '',
                        'submenu' => array(
                            array('text' => MENU_USER_RIGHTS, 'url' => '/reports/userRights', 'target' => 'ajax'),
                            array('text' => MENU_USER_LOG, 'url' => '/reports/userLog', 'target' => 'ajax')
                        )
                    )
                )
            )
        );
    }

    function beforeFilter() {
        /**
         *  set default language
         */
        if (!$this->Session->check('lang')) {
            $this->Session->write('lang', 'en');
        }
        $this->generateLang($this->Session->read('lang'));

        /**
         * define path
         */
        require_once('../../app/webroot/path.php');
        // Check Permission
        if (($this->params['controller'] == 'mobiles' && in_array($this->params['action'], array('agencySalesTicket', 'agencySalesTicketResult', 'salesTicketView', 'ticketBalance', 'ticketBalanceResult', 'salesTicket', 'salesTicketResult')))){
            // Report end point
        } else if (($this->params['controller'] == 'payments' && in_array($this->params['action'], array('terminalPayment', 'checkAbaTransaction', 'abaTransactionList', 'checkTerminalPaymentComplete', 'abaMobilePay', 'abaVisalPayment', 'abaAlipay', 'paymentSuccess', 'wingPayment', 'saveWingCompleted', 'wingCancel', 'websiteAbaPay', 'websiteAbaPayComplete', 'checkBuvaSeaABAPayment', 'checkBuvaSeaAbaTransaction', 'wingWebsiteComplete', 'wingWebsiteCancel', 'acledaMobilePay', 'acledaCheckStatus', 'acledaComplete','callbackUrl', 'terminalAbaAlipay', 'acledaXpay', 'acledaXpayCancel', 'acledaXpayComplete', 'busWebsitePaymentProcess', 'busWebsiteAbaPayComplete', 'saveAbaPhoneCallComplete', 'terminalAbaPhoneCall', 'terminalAbaAlipayPhoneCall', 'websiteWingRequestPay', 'wingCheckStatus', 'checkAbaTransactionPhoneCall', 'saveApiCheckTransaction', 'checkOrderPayment', 'wingNewApiPayment', 'saveNewApiWingCompleted', 'wingNewWebsiteComplete', 'abaMobilePayPackage', 'abaVisalPaymentPackage', 'packagePaymentComplete', 'checkTravelPackageStatus', 'busWebsitePackagePaymentProcess', 'wingNewApiPaymentPro','deeplinkAcledaPay','deeplinkAcledaPayComplete', 'busWebsiteAcledaXpay', 'buvaSeaWebsitePaymentProcess', 'buvaSeaWebsiteAbaPayComplete', 'testAbaMobilePay', 'fixedPaymentFailed')))){
            // Report end point
        } else {
            $this->menu();
            if ($this->params['controller'] != 'users' || ($this->params['controller'] == 'users' && !in_array($this->params['action'], array('lang', 'checkDuplicate', 'checkDuplicate2', 'login', 'logout', 'profile', 'backup', 'smartcode', 'silentOps', 'silentOps2', 'checkInvAdj', 'approveInvAdj', 'addToDetail', 'checkStatusTo', 'receiveToAll', 'checkReceiveAllTO', 'deliveryStock', 'checkDnPickUp', 'deliveryPos', 'systemConfig', 'sync', 'vatGenerateInvoice', 'addOnlineCustomer', 'updateOnlineCustomer', 'updateStatusOnlineCustomer', 'updateStatusAllOnlineCustomer')))) {
                if ($this->checkAccess() == false) {
                    echo "No Authentication";
                    exit();
                }
            }   
        }
    }

    function afterFilter() {

    }

    function checkAccess($controller = null, $action = null) {
        if (!$controller) {
            $controller = $this->params['controller'];
        }
        if (!$action) {
            $action = $this->params['action'];
        }

        $users = $this->getCurrentUser();
        if (!$users) {
            $this->redirect('/users/login');
        } else {
            /**
             * Access Rules
             */
            $accessRules = array();
            $queryPermission = mysql_query("SELECT groups.id, module_details.controllers, module_details.views 
                                            FROM groups
                                            INNER JOIN `permissions` ON permissions.group_id = groups.id
                                            INNER JOIN module_details ON module_details.module_id = permissions.module_id
                                            WHERE groups.is_active = 1 ORDER BY module_details.controllers");
            $firstControllerName = "";
            while ($dataPermission = mysql_fetch_array($queryPermission)) {
                $accessRules[$dataPermission['id']][$dataPermission['controllers']][] = $dataPermission['views'];
            }
            $_SESSION['accessRules'] = $accessRules;
            $this->set('user', $users);
            $this->set('menu', $this->menu);
        }

        $accessRules = $_SESSION['accessRules'];
        $queryUserGroup = mysql_query("SELECT group_id FROM `user_groups` WHERE user_id=" . $users['User']['id']);
        while ($dataUserGroup = mysql_fetch_array($queryUserGroup)) {
            if (!empty($accessRules[$dataUserGroup['group_id']][$controller]) && (is_array($accessRules[$dataUserGroup['group_id']][$controller]) && in_array($action, $accessRules[$dataUserGroup['group_id']][$controller]))) {
                return true;
            }
        }
        return false;
    }

    function getDefaultPage($userId = null) {
        if (!empty($this->menu) && count($this->menu) > 0) {
            if(!empty($userId)){
                $db = ConnectionManager::getDataSource('default');
                mysql_connect($db->config['host'], $db->config['login'], $db->config['password']);
                mysql_select_db($db->config['database']);
                $sqlModule = mysql_query("SELECT GROUP_CONCAT(name) FROM module_types WHERE id IN (SELECT module_type_id FROM modules WHERE id IN (SELECT module_id FROM permissions WHERE group_id IN (SELECT group_id FROM user_groups WHERE user_id = ".$userId.")))");
                $rowModule = mysql_fetch_array($sqlModule);
                if($rowModule[0] == 'Dashboard,Schedule Screen Display'){
                    return array('controller' => 'schedules', 'action' => 'departureSchedule');
                } else {
                    $place = explode('/', $this->menu[0]['url']);
                    return array('controller' => $place[0], 'action' => $place[1] . '/' . $place[2]);
                }
            } else {
                $place = explode('/', $this->menu[0]['url']);
                return array('controller' => $place[0], 'action' => $place[1] . '/' . $place[2]);
            }
        } else {
            return array('controller' => 'users', 'action' => 'logout');
        }
    }

    /**
     * Read user object from session
     */
    function getCurrentUser() {
        if ($this->Session->check('User')) {
            return $this->Session->read('User');
        } else {
            return false;
        }
    }
    

    /**
     * Write user object into session when login
     */
    function setCurrentUser($user) {
        $this->Session->write('User', $user);
    }
    
    /**
     * Generate Language
     */
    function generateLang($langId = null){
        $filename = "../../app/webroot/lang/lang_".$langId. ".php";
        if(file_exists($filename)){
            require_once($filename);
        }
    }

}

?>