<?php

/**
 * Description of Helper
 *
 * @author UDAYA
 */
date_default_timezone_set('Asia/Phnom_Penh');

class GeneratePhpControllerComponent {

    var $components = array('AiAssistant');

    function pluralToSingular($pluralWord) {
        // List of irregular plural to singular conversions
        $irregularWords = array(
            'children' => 'child',
            'people' => 'person',
            'men' => 'man',
            'women' => 'woman',
            'feet' => 'foot',
            'teeth' => 'tooth',
            'geese' => 'goose',
            'mice' => 'mouse',
            'oxen' => 'ox',
            'data' => 'datum',
            'criteria' => 'criterion',
            'phenomena' => 'phenomenon'
        );
        
        // Split into parts if underscore exists
        if (strpos($pluralWord, '_') !== false) {
            $parts = explode('_', $pluralWord);
            $lastPart = array_pop($parts);
            $singularLastPart = $this->convertWordToSingular($lastPart, $irregularWords);
            $parts[] = $singularLastPart;
            return implode('_', $parts);
        }
        
        // If no underscores, convert the whole word
        return $this->convertWordToSingular($pluralWord, $irregularWords);
    }
    
    protected function convertWordToSingular($word, $irregularWords) {
        // Check if the word is in our irregular list
        $lowerWord = strtolower($word);
        if (isset($irregularWords[$lowerWord])) {
            // Handle case of original word (preserve capitalization)
            if (ctype_upper($word)) {
                return strtoupper($irregularWords[$lowerWord]);
            } elseif (ctype_upper(substr($word, 0, 1))) {
                return ucfirst($irregularWords[$lowerWord]);
            }
            return $irregularWords[$lowerWord];
        }
        
        // Regular plural rules
        $rules = array(
            '/(quiz)zes$/i' => '\1',
            '/(matr)ices$/i' => '\1ix',
            '/(vert|ind)ices$/i' => '\1ex',
            '/^(ox)en/i' => '\1',
            '/(alias|status)es$/i' => '\1',
            '/([octop|vir])i$/i' => '\1us',
            '/(cris|ax|test)es$/i' => '\1is',
            '/(shoe)s$/i' => '\1',
            '/(o)es$/i' => '\1',
            '/(bus)es$/i' => '\1',
            '/([m|l])ice$/i' => '\1ouse',
            '/(x|ch|ss|sh)es$/i' => '\1',
            '/(m)ovies$/i' => '\1ovie',
            '/(s)eries$/i' => '\1eries',
            '/([^aeiouy]|qu)ies$/i' => '\1y',
            '/([lr])ves$/i' => '\1f',
            '/(tive)s$/i' => '\1',
            '/(hive)s$/i' => '\1',
            '/([^f])ves$/i' => '\1fe',
            '/(^analy)ses$/i' => '\1sis',
            '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
            '/([ti])a$/i' => '\1um',
            '/(n)ews$/i' => '\1ews',
            '/s$/i' => ''
        );
        
        foreach ($rules as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                $singularWord = preg_replace($rule, $replacement, $word);
                return $singularWord;
            }
        }
        
        // If no rules matched, return the original word
        return $word;
    }

    function convertName($input, $type) {
        $input = trim($input);
        if ($input === '') {
            return $input;
        }
        
        $words = explode('_', strtolower($input));
        
        switch ($type) {
            case 1:
                return lcfirst(implode('', array_map('ucfirst', $words))); // camelCase
            case 2:
                return implode('', array_map('ucfirst', $words));        // PascalCase
            case 3:
                return implode(' ', array_map('ucfirst', $words));       // Title Case
            default:
                return $input;
        }
    }

    function generateControllerClass($controllerName, $componentUses = array()) {
        if (empty($controllerName)) {
            return '';
        }
    
        // Start with the default Helper component
        $components = array("'Helper'");
        
        // Add additional components if provided
        if (!empty($componentUses)) {
            if (is_string($componentUses)) {
                $componentUses = array($componentUses);
            }
            
            foreach ($componentUses as $component) {
                // Ensure components are properly quoted
                $components[] = "'" . trim($component, "'\"") . "'";
            }
        }
        
        // Create components array string
        $componentsStr = 'array(' . implode(', ', $components) . ')';
        
        $content = <<<EOD
<?php
/**
 * {$controllerName}Controller
 *
 * @package app
 * @subpackage app.controllers
 */
class {$controllerName}Controller extends AppController {

    var \$name = '$controllerName';
    var \$components = $componentsStr;
}
?>
EOD;
    
        return $content;
    }

    function addMethodsToController($classContent, $methods) {
        // Find the position before the closing brace and PHP tag
        $closingPattern = '/\s*\}\s*\?\>\s*$/s';
        
        if (!preg_match($closingPattern, $classContent, $matches, PREG_OFFSET_CAPTURE)) {
            return $classContent; // Return original if pattern not found
        }
        
        $insertPosition = $matches[0][1]; // Position where the closing starts
        
        // Prepare methods to add with proper indentation
        $methodsToAdd = "";
        foreach ($methods as $methodContent) {
            // Clean up each method content
            $methodContent = trim($methodContent);
            if (!empty($methodContent)) {
                // Ensure method has proper closing brace
                if (substr_count($methodContent, '{') > substr_count($methodContent, '}')) {
                    $methodContent .= '}';
                }
                $methodsToAdd .= "\n\n    " . $methodContent;
            }
        }
        
        // Rebuild the content
        $newContent = substr($classContent, 0, $insertPosition) . 
                     $methodsToAdd . 
                     "\n" . 
                     substr($classContent, $insertPosition);
        
        return $newContent;
    }

    function generateLayout($layout) {
        if (empty($layout)) {
            return '';
        }
        return <<<EOD
        \$this->layout = '{$layout}';
EOD;
    }

    function generateParamValidation($isForm = true) {
        if ($isForm) {
        return <<<EOD
        if (!\$id && empty(\$this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
EOD;
        } else {
            return <<<EOD
        if (empty(\$id)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
EOD;
        }
    }

    function generateUserFetch() {
        return <<<EOD
        \$user = \$this->getCurrentUser();
EOD;
    }

    function generateDataCheck() {
        return <<<EOD
        if (!empty(\$this->data)) {
EOD;
    }

    function generateDuplicateCheck($modelName, $tableName, $fieldName, $validationRules, $isEdit = true) {
        $method = $isEdit ? 'checkDouplicateEdit' : 'checkDouplicate';
        $idParam = $isEdit ? ', $id' : '';
        
        return <<<EOD
            if (\$this->Helper->{$method}('{$fieldName}', '{$tableName}'{$idParam}, \$this->data['{$modelName}']['{$fieldName}'], "{$validationRules}")) {
EOD;
    }

    function generateSaveOperation($modelName, $isEdit = true) {
        if ($isEdit) {
            return <<<EOD
                \$this->data['{$modelName}']['modified_by'] = \$user['User']['id'];
EOD;
        } else {
            return <<<EOD
                \$this->{$modelName}->create();
                \$this->data['{$modelName}']['created_by'] = \$user['User']['id'];
EOD;
        }
    }

    function generateSaveResultHandling($modelName, $module, $actionType, $isEdit = true) {
        $action = $isEdit ? 'Edit' : 'Add New';
        $idParam = $isEdit ? ', $id' : ', $this->' . $modelName . '->id';
        $errorIdParam = $isEdit ? ', $id' : '';
        
        return <<<EOD
        if (\$this->{$modelName}->save(\$this->data)) {
                    \$this->Helper->saveUserActivity(\$user['User']['id'], '{$module}', 'Save {$action}'{$idParam});
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                }
    
                \$this->Helper->saveUserActivity(\$user['User']['id'], '{$module}', 'Save {$action} (Error)'{$errorIdParam});
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
EOD;
    }

    function generateDataFetch($modelName) {
        return <<<EOD
        \$this->data = \$this->{$modelName}->read(null, \$id);
EOD;
    }

    function generateDropdowns($dropdowns) {
        $content = '';
        foreach ($dropdowns as $varName => $config) {
            // Handle ClassRegistry style dropdowns
            if (isset($config['model'])) {
                $conditions = var_export($config['conditions'], true);
                $findMethod = isset($config['findMethod']) ? "'{$config['findMethod']}'" : "'list'";
                $content .= <<<EOD
    
            \${$varName} = ClassRegistry::init('{$config['model']}')->find({$findMethod}, array("conditions" => {$conditions}));
EOD;
            } 
            // Handle direct model method calls
            elseif (isset($config['method'])) {
                $params = isset($config['params']) ? var_export($config['params'], true) : '';
                $content .= <<<EOD
    
            \${$varName} = \$this->{$config['modelObject']}->{$config['method']}({$params});
EOD;
            }
            
            // Add to compact statement if not already handled in group
            if (!isset($config['groupWith'])) {
                $content .= "\n        \$this->set(compact('{$varName}'));";
            }
        }
        
        // Handle grouped sets
        $groupedVars = array();
        foreach ($dropdowns as $varName => $config) {
            if (isset($config['groupWith'])) {
                $groupedVars[] = "'{$varName}'";
            }
        }
        
        if (!empty($groupedVars)) {
            $varsList = implode(', ', $groupedVars);
            $content .= "\n        \$this->set(compact({$varsList}));";
        }
        
        return $content;
    }

    function generateUserActivity($module, $action, $isEdit = true) {
        $idParam = $isEdit ? ', $id' : '';
        return <<<EOD
        \$this->Helper->saveUserActivity(\$user['User']['id'], '{$module}', '{$action}'{$idParam});
EOD;
    }

    function generateControllerAction($config = array()) {
        $defaults = array(
            'modelName' => 'Coa',
            'tableName' => 'coas',
            'functionName' => 'index',
            'action' => 'Dashboard',
            'layout' => 'ajax',
            'module' => 'Chart Account',
            'fieldName' => 'name',
            'validationRules' => 'is_active = 1',
            'isEdit' => true,
            'isForm' => false,
            'isParam' => false,
            'conditionDelete' => '',
            'steps' => array(
                'layout' => true,
                'paramValidation' => true,
                'userFetch' => true,
                'dataCheck' => true,
                'duplicateCheck' => true,
                'saveOperation' => true,
                'dataFetch' => true,
                'dropdowns' => true,
                'dataDelete' => false,
                'userActivity' => true
            ),
            'dropdowns' => array(
                'chartAccountTypes' => array(
                    'model' => 'ChartAccountType',
                    'findMethod' => 'list',
                    'conditions' => array('ChartAccountType.is_active = 1')
                ),
                'chartAccountGroups' => array(
                    'modelObject' => 'Coa',
                    'method' => 'chartAccountGroupList'
                )
            )
        );
        
        $config = array_merge($defaults, $config);
        extract($config);
        
        $defaultAction = $action;
        if ($isParam) {
            $content = "function {$functionName}(\$id = null) {\n";
        } else {
            $content = "function {$functionName}() {\n";
        }
        
        // 1. Layout
        if ($steps['layout']) {
            $content .= $this->generateLayout($layout) . "\n";
        }
        
        // 2. Parameter validation
        if ($isParam) {
            $content .= $this->generateParamValidation($isForm) . "\n";
        }
        
        // 3. User fetch
        if ($steps['userFetch']) {
            $content .= $this->generateUserFetch() . "\n";
        }
        
        // 4. Data check
        if ($steps['dataCheck']) {
            $content .= $this->generateDataCheck() . "\n";
            
            // 5. Duplicate check
            if ($steps['duplicateCheck']) {
                $content .= $this->generateDuplicateCheck($modelName, $tableName, $fieldName, $validationRules, $isEdit) . "\n";
                $content .= "            " . $this->generateUserActivity($module, $isEdit ? 'Save Edit (Name ready existed)' : 'Save Add New (Name ready existed)', $isEdit) . "\n";
                $content .= "            echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;\n";
                $content .= "            exit;\n";
                $content .= "        }\n\n";
            }
            
            // 6. Save operation
            if ($steps['saveOperation']) {
                $content .= $this->generateSaveOperation($modelName, $isEdit) . "\n";
                $content .= "        " . $this->generateSaveResultHandling($modelName, $module, $defaultAction, $isEdit) . "\n";
            }
            
            $content .= "     }\n";
        }
        
        // 7. Data fetch
        if ($steps['dataFetch']) {
            $content .= $this->generateDataFetch($modelName) . "\n";
        }
        
        // 8. Dropdowns
        if ($steps['dropdowns']) {
            $content .= $this->generateDropdowns($dropdowns) . "\n";
        }
        
        // 9. User activity
        if ($steps['userActivity']) {
            $content .= $this->generateUserActivity($module, $defaultAction, $isEdit) . "\n";
        }

        // 10. Delete Data
        if ($steps['dataDelete']) {
            $content .= <<<EOD
        mysql_query("UPDATE `{$tableName}` SET {$conditionDelete}, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".\$user['User']['id']." WHERE `id`=".\$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;\n
EOD;
        }
        
        $content .= "    }";
        
        return $content;
    }

    function formatCodeString($code) {
        $formatted = str_replace('{', " {\n    ", $code);
        $formatted = str_replace('}', "\n}", $formatted);
        $formatted = str_replace(';', ";\n    ", $formatted);
        $formatted = preg_replace('/\)\s*{/', ") {", $formatted);
        
        // Add indentation for nested blocks
        $lines = explode("\n", $formatted);
        $indent = 0;
        foreach ($lines as &$line) {
            if (strpos($line, '}') !== false) {
                $indent--;
            }
            $line = str_repeat('    ', $indent) . trim($line);
            if (strpos($line, '{') !== false) {
                $indent++;
            }
        }
        
        return implode("\n", $lines);
    }
    
}