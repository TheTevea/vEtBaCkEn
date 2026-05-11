<?php

class AutoGenerateCodesController extends AppController {

    var $name = 'AutoGenerateCodes';
    var $components = array('Helper', 'GeneratePhpController', 'AiAssistant');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Auto Generate Code', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Auto Generate Code', 'View', $id);
        $this->data = $this->AutoGenerateCode->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            // Create Controller File
            $tableName  = $this->data['AutoGenerateCode']['module_name'];
            $controllerToSigular = $this->GeneratePhpController->pluralToSingular($tableName);
            $controllerClassName = $this->GeneratePhpController->convertName($tableName, 2);
            $moduleName = $this->GeneratePhpController->convertName($controllerToSigular, 2);
            $module     = $this->GeneratePhpController->convertName($controllerToSigular, 3);
            
            // Create Controller File
            $controllerFileName = $tableName . '_controller.php'; // Follow CakePHP naming convention
            $controllerFilePath = APP . 'controllers' . DS . $controllerFileName;
            $functionCode = array();
            // Dashboard
            // Function Index
            // $config = array(
            //     'modelName' => $moduleName,
            //     'tableName' => $tableName,
            //     'layout' => 'ajax',
            //     'module' => $module,
            //     'functionName' => 'index',
            //     'action' => 'Dashboard',
            //     'fieldName' => '',
            //     'validationRules' => '',
            //     'isEdit' => false,
            //     'isForm' => false,
            //     'isParam' => false,
            //     'conditionDelete' => '',
            //     'steps' => array(
            //         'layout' => true,
            //         'paramValidation' => false,
            //         'userFetch' => true,
            //         'dataCheck' => false,
            //         'duplicateCheck' => false,
            //         'saveOperation' => false,
            //         'dataFetch' => false,
            //         'dropdowns' => false,
            //         'dataDelete' => false,
            //         'userActivity' => true
            //     ),
            //     'dropdowns' => array()
            // );
            // $functionCode[] = $this->GeneratePhpController->generateControllerAction($config);
            // Function Ajax
            // $config = array(
            //     'modelName' => '',
            //     'tableName' => '',
            //     'layout' => 'ajax',
            //     'module' => '',
            //     'functionName' => 'ajax',
            //     'action' => '',
            //     'fieldName' => '',
            //     'validationRules' => '',
            //     'isEdit' => false,
            //     'isForm' => false,
            //     'isParam' => false,
            //     'conditionDelete' => '',
            //     'steps' => array(
            //         'layout' => true,
            //         'paramValidation' => false,
            //         'userFetch' => false,
            //         'dataCheck' => false,
            //         'duplicateCheck' => false,
            //         'saveOperation' => false,
            //         'dataFetch' => false,
            //         'dropdowns' => false,
            //         'dataDelete' => false,
            //         'userActivity' => false
            //     ),
            //     'dropdowns' => array()
            // );
            // $functionCode[] = $this->GeneratePhpController->generateControllerAction($config);
            // Generate From AI index, ajax
            $funcIndex = $this->AiAssistant->createChatMessage('model name '.$moduleName.', function name index, it have 3 process. 1. Set layout = ajax, 2. Fetch the current user, 3. Save user activity', 'php');
            if($funcIndex['status'] == 1){
                $functionCode[] = $funcIndex['msg'];
            }
            $funcAjax = $this->AiAssistant->createChatMessage('model name '.$moduleName.', function name ajax, it have 1 process. 1. Set layout = ajax', 'php');
            if($funcAjax['status'] == 1){
                $functionCode[] = $funcAjax['msg'];
            }
            if(!empty($this->data['AutoGenerateCode']['has_view'])){
                $this->data['AutoGenerateCode']['has_view'] = 1;
                // Function View
                // $config = array(
                //     'modelName' => $moduleName,
                //     'tableName' => $tableName,
                //     'layout' => 'ajax',
                //     'module' => $module,
                //     'functionName' => 'view',
                //     'action' => 'View',
                //     'fieldName' => '',
                //     'validationRules' => '',
                //     'isEdit' => false,
                //     'isForm' => false,
                //     'isParam' => true,
                //     'conditionDelete' => '',
                //     'steps' => array(
                //         'layout' => true,
                //         'paramValidation' => true,
                //         'userFetch' => true,
                //         'dataCheck' => false,
                //         'duplicateCheck' => false,
                //         'saveOperation' => false,
                //         'dataFetch' => true,
                //         'dropdowns' => false,
                //         'dataDelete' => false,
                //         'userActivity' => true
                //     ),
                //     'dropdowns' => array()
                // );
                // $functionCode[] = $this->GeneratePhpController->generateControllerAction($config);
                $funcView = $this->AiAssistant->createChatMessage('model name '.$moduleName.', function name view, it have process like this. 1. Set layout = ajax, 2. validate input param id, 3. Fetch the current user, 4. Save user activity, 5. Fetch the existing data', 'php');
                if($funcView['status'] == 1){
                    $functionCode[] = $funcView['msg'];
                }
            } else {
                $this->data['AutoGenerateCode']['has_view'] = 0;
            }
            if(!empty($this->data['AutoGenerateCode']['has_add'])){
                $this->data['AutoGenerateCode']['has_add'] = 1;
                $command = 'function name add, it have process like this. 1. Set layout = ajax, 2. Fetch the current user, 3. Handle form submission, 4. Save the data, 5. Model data for the dropdown [model: TTransportationType], 6.Set the model data, 7. Save user activity';
                $funcAdd = $this->AiAssistant->createChatMessage('model name '.$moduleName.', '.$command, 'php');
                if($funcAdd['status'] == 1){
                    $functionCode[] = $funcAdd['msg'];
                }
            } else {
                $this->data['AutoGenerateCode']['has_add'] = 0;
            }
            if(!empty($this->data['AutoGenerateCode']['has_edit'])){
                $this->data['AutoGenerateCode']['has_edit'] = 1;
                $command = 'function name edit, it have process like this. 1. Set layout = ajax, 2. validate input param id and data post, 3. Fetch the current user, 4. Handle form submission, 5. Save the data, 6. Save user activity, 7. Fetch the existing data, 8. Model data for the dropdown [model: TTransportationType], 9. Set the model data';
                $funcEdit = $this->AiAssistant->createChatMessage('model name '.$moduleName.', '.$command, 'php');
                if($funcEdit['status'] == 1){
                    $functionCode[] = $funcEdit['msg'];
                }
            } else {
                $this->data['AutoGenerateCode']['has_edit'] = 0;
            }
            if($this->data['AutoGenerateCode']['has_add'] == 1 || $this->data['AutoGenerateCode']['has_edit'] == 1){
                // $isEdit = false;
                // $functionName = 'Add';
                // if($this->data['AutoGenerateCode']['has_edit'] == 1){
                //     $isEdit = true;
                //     $functionName = 'Edit';
                // }
                // Function Add/Edit
                // $config = array(
                //     'modelName' => $moduleName,
                //     'tableName' => $tableName,
                //     'layout' => 'ajax',
                //     'module' => $module,
                //     'functionName' => strtolower($functionName),
                //     'action' => $functionName,
                //     'fieldName' => '',
                //     'validationRules' => '',
                //     'isEdit' => $isEdit,
                //     'isForm' => true,
                //     'isParam' => $isEdit,
                //     'conditionDelete' => '',
                //     'steps' => array(
                //         'layout' => true,
                //         'paramValidation' => true,
                //         'userFetch' => true,
                //         'dataCheck' => true,
                //         'duplicateCheck' => false,
                //         'saveOperation' => true,
                //         'dataFetch' => $isEdit,
                //         'dropdowns' => false,
                //         'dataDelete' => false,
                //         'userActivity' => true
                //     ),
                //     'dropdowns' => array()
                // );
                // $functionCode[] = $this->GeneratePhpController->generateControllerAction($config);
                
            }
            if(!empty($this->data['AutoGenerateCode']['has_delete'])){
                $this->data['AutoGenerateCode']['has_delete'] = 1;
                // Function Delete
                // $config = array(
                //     'modelName' => $moduleName,
                //     'tableName' => $tableName,
                //     'layout' => '',
                //     'module' => $module,
                //     'functionName' => 'delete',
                //     'action' => 'Delete',
                //     'fieldName' => '',
                //     'validationRules' => '',
                //     'isEdit' => false,
                //     'isForm' => false,
                //     'isParam' => true,
                //     'conditionDelete' => 'is_active = 1',
                //     'steps' => array(
                //         'layout' => false,
                //         'paramValidation' => true,
                //         'userFetch' => true,
                //         'dataCheck' => false,
                //         'duplicateCheck' => false,
                //         'saveOperation' => false,
                //         'dataFetch' => false,
                //         'dropdowns' => false,
                //         'dataDelete' => true,
                //         'userActivity' => true
                //     ),
                //     'dropdowns' => array()
                // );
                // $functionCode[] = $this->GeneratePhpController->generateControllerAction($config);
            } else {
                $this->data['AutoGenerateCode']['has_delete'] = 0;
            }
            if ($this->AutoGenerateCode->save($this->data)) {
                $autoGenerateCodeId = $this->AutoGenerateCode->id;

                $hasView = 0;
                $hasAdd = 0;
                $hasEdit = 0;
                $hasDelete = 0;
                // Loop through the function names and save them
                // foreach($this->data['fields_label'] as $key => $value){
                //     $this->loadModel('AutoGenerateCodeFunction');
                //     $this->AutoGenerateCodeFunction->create();
                    
                //     $autoGenerateCodeFunctions = array(
                //         'AutoGenerateCodeFunction' => array(
                //             'auto_generate_code_id' => $autoGenerateCodeId,
                //             'function_name' => $functionName,
                //             'dashboard_fields' => $dashboardFields,
                //             'ajax_fields' => $ajaxFields
                //         )
                //     );
                // }
                try {
                    // Check if controller directory is writable
                    if (!is_writable(dirname($controllerFilePath))) {
                        throw new Exception('Controllers directory is not writable');
                    }

                    // Check if file already exists
                    if (file_exists($controllerFilePath)) {
                        throw new Exception('Controller file already exists');
                    }
                    // Generate controller content
                    $controllerContent = $this->GeneratePhpController->generateControllerClass($controllerClassName);
                    // Add Fucntion to the controller
                    $controllerContent = $this->GeneratePhpController->addMethodsToController($controllerContent, $functionCode);
                    // Write the file
                    if (file_put_contents($controllerFilePath, $controllerContent) === false) {
                        throw new Exception('Failed to write controller file');
                    }

                    // Set proper permissions
                    chmod($controllerFilePath, 0644);

                    // echo "Controller file created successfully: " . $controllerFilePath;
                } catch (Exception $e) {
                    echo "Error creating controller file: " . $e->getMessage();
                }
                mysql_query("UPDATE auto_generate_codes SET has_view = $hasView, has_add = $hasAdd, has_edit = $hasEdit, has_delete = $hasDelete WHERE id = $autoGenerateCodeId");
                $this->Helper->saveUserActivity($user['User']['id'], 'Auto Generate Code', 'Save Add New', $autoGenerateCodeId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Auto Generate Code', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        
        $this->Helper->saveUserActivity($user['User']['id'], 'Auto Generate Code', 'Add New');
    }

}

?>