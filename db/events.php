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
 * Event observers for local_personalxp.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$observers = [
    [
        'eventname' => '\\core\\event\\course_module_created',
        'callback' => '\\local_personalxp\\observer::course_module_created',
    ],
    [
        'eventname' => '\\core\\event\\course_module_updated',
        'callback' => '\\local_personalxp\\observer::course_module_updated',
    ],
    [
        'eventname' => '\\core\\event\\course_module_deleted',
        'callback' => '\\local_personalxp\\observer::course_module_deleted',
    ],
    [
        'eventname' => '\\mod_quiz\\event\\slot_created',
        'callback' => '\\local_personalxp\\observer::quiz_structure_changed',
    ],
    [
        'eventname' => '\\mod_quiz\\event\\slot_deleted',
        'callback' => '\\local_personalxp\\observer::quiz_structure_changed',
    ],
    [
        'eventname' => '\\mod_quiz\\event\\slot_version_updated',
        'callback' => '\\local_personalxp\\observer::quiz_structure_changed',
    ],
    [
        'eventname' => '\\core\\event\\course_module_completion_updated',
        'callback' => '\\local_personalxp\\observer::course_module_completion_updated',
    ],
    [
        'eventname' => '\\core\\event\\course_completed',
        'callback' => '\\local_personalxp\\observer::course_completed',
    ],
    [
        'eventname' => '\\mod_forum\\event\\post_created',
        'callback' => '\\local_personalxp\\observer::forum_post_created',
    ],
    [
        'eventname' => '\\mod_quiz\\event\\attempt_submitted',
        'callback' => '\\local_personalxp\\observer::quiz_attempt_submitted',
    ],
];
