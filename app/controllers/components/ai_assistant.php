<?php

/**
 * Description of AI Assistant Component
 *
 * @author UDAYA
 */

class AiAssistantComponent extends Object {

    private $apiKey = 'sk-6a161ed7e5224e40a618de15b6609d54';
    private $apiUrl = 'https://api.deepseek.com/v1/chat/completions';
    
    function makeChat($messages, $model = 'deepseek-chat', $temperature = 0.3, $maxTokens = 500) {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ];
        
        $data = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens
        ];
        
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        return json_decode($response, true);
    }

    function createChatMessage($messages, $language = 'php', $version = '5.6') {
        $result = array();
        $codeTemplateFunction = 'function edit($id = null) {
        // Set layout
        $this->layout = \'ajax\';
        // Validate input
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        // Fetch the current user
        $user = $this->getCurrentUser();
        // Handle form submission
        if (!empty($this->data)) {
            // Check for duplicate filed
            if ($this->Helper->checkDouplicateEdit(\'name\', \'bus_types\', $id, $this->data[\'BusType\'][\'name\'], "is_active = 1")) {
                $this->Helper->saveUserActivity($user[\'User\'][\'id\'], \'Bus Type\', \'Save Edit (Name ready existed)\', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            }
            // Save the data
            $this->data[\'BusType\'][\'modified_by\'] = $user[\'User\'][\'id\'];
            if ($this->BusType->save($this->data)) {
                $this->Helper->saveUserActivity($user[\'User\'][\'id\'], \'Bus Type\', \'Save Edit\', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
            } else {
                $this->Helper->saveUserActivity($user[\'User\'][\'id\'], \'Bus Type\', \'Save Edit (Error)\', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            }
            exit;
        }
        // Save user activity for viewing the edit form
        $this->Helper->saveUserActivity($user[\'User\'][\'id\'], \'Bus Type\', \'Edit\', $id);
        // Fetch the existing data for the bus type
        $this->data = $this->BusType->read(null, $id);
        // Model data for the dropdown
        $tTransportationTypes = ClassRegistry::init(\'TTransportationType\')->find(\'list\', array(
            "conditions" => array(
                "TTransportationType.is_active = 1", 
                \'TTransportationType.offline_project_id\' => 1
            )
        ));
        // Set the model data for the view
        $this->set(compact(\'tTransportationTypes\'));
    }';

        $systemMessage = "You are an expert programming assistant. When providing code responses, ONLY output the raw code without any comments, or explanations."
                     ."The code format: \n".$codeTemplateFunction;
    
        $messages = [
            ['role' => 'system', 'content' => $systemMessage],
            ['role' => 'user', 'content' => 'write code using '.$language.' version '.$version.' follow the code format. '.$messages]
        ];

        $response = $this->makeChat($messages);
        if (isset($response['choices'][0]['message']['content'])) {
            $content = $response['choices'][0]['message']['content'];
            $content = str_replace(['```'.$language, '```'], '', $content);
            $result['msg'] = $content;
            $result['status'] = 1;
        } else {
            $result['msg'] = 'Error: ' . $response;
            $result['status'] = 0;
        }
        return $result;
    }

}

?>