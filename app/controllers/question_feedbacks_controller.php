<?php

class QuestionFeedbacksController extends AppController {

    var $name = 'QuestionFeedbacks';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Question Feedback', 'Dashborad');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Question Feedback', 'View', $id);
        $this->data = $this->QuestionFeedback->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'question_feedbacks', $this->data['QuestionFeedback']['name'], "is_active = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Question Feedback', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->QuestionFeedback->create();
                $this->data['QuestionFeedback']['created_by'] = $user['User']['id'];
                if ($this->QuestionFeedback->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Question Feedback', 'Save Add New', $this->QuestionFeedback->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Question Feedback', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Question Feedback', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'question_feedbacks', $id, $this->data['QuestionFeedback']['name'], "is_active = 1")) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Question Feedback', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->data['QuestionFeedback']['modified_by'] = $user['User']['id'];
                if ($this->QuestionFeedback->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Question Feedback', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Question Feedback', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Question Feedback', 'Edit', $id);
            $this->data = $this->QuestionFeedback->read(null, $id);
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Question Feedback', 'Delete', $id);
        mysql_query("UPDATE `question_feedbacks` SET `is_active`=2, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>