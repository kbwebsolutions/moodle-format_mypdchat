<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * From to post a comment.
 *
 * @package format_mypdchat
 * @copyright 2014 Andreas Wagner, Synergy Learning
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class comment_form extends moodleform {

    // Define the form.
    protected function definition() {

        $mform = & $this->_form;
        $postid = $this->_customdata['postid'];
        $courseid = $this->_customdata['courseid'];
        $replycommentid = $this->_customdata['replycommentid'];

        // ...get errors from cache and set them to elements.
        $errorcache = cache::make('format_mypdchat', 'commentformerrors');
        if ($errors = $errorcache->get($postid)) {
            foreach ($errors as $element => $error) {
                $mform->setElementError($element, $error['message']);
            }
        }
        $errorcache->delete($postid);

        $textareaparams = array('class' => 'tl-commenttext', 'id' => 'commenttext_' . $postid.'_'.$replycommentid, 'rows' => '5', 'cols' => '65');
        $mform->addElement('textarea', 'text', '', $textareaparams);
        $mform->setType('text', PARAM_TEXT);
        //$mform->addRule('text', null, 'required', null, 'client');

        $mform->addElement('hidden', 'postid', $postid);
        $mform->setType('postid', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'action', 'postcomment');
        $mform->setType('action', PARAM_TEXT);

        $mform->addElement('hidden', 'replycommentid', $replycommentid);
        $mform->setType('replycommentid', PARAM_INT);

        $params = array('class' => 'tl-postcomment', 'id' => 'postcomment_' . $postid.'_'.$replycommentid);
        $mform->addElement('submit', 'submitcomment', get_string('postcomment', 'format_mypdchat'), $params);
    }

    protected function get_form_identifier() {
        return "comment_form_".$this->_customdata['replycommentid'];
    }


    public function has_errors() {
        $mform = & $this->_form;
        $error = $mform->getElementError('text');
        return !empty($error);
    }

    public function validation($data, $files) {

        $errors = array();

        // Submit is redirected if error occurs, so we store errordata in session.
        $sessionerrordata = array();
        $cache = cache::make('format_mypdchat', 'commentformerrors');
        $cache->delete($data['postid']);

        // ... check if comment is all empty.
        if (isset($data['submitcomment'])) {
            if (empty($data['text'])) {
                $errors['text'] = get_string('textrequired', 'format_mypdchat');
                $sessionerrordata['text'] = array('message' => $errors['text'], 'value' => $data['text']);
            }
        }

        // ... store or clean.
        if (!empty($sessionerrordata)) {
            $cache->set($data['postid'], $sessionerrordata);
        }

        return $errors;
    }

}