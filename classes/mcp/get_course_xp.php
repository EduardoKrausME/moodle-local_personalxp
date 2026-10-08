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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU GPL for details.

/**
 * MCP integration for Personal XP.
 *
 * @package local_personalxp
 * @copyright 2026 Eduardo Kraus
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_personalxp\mcp;

use context;
use context_course;
use local_mcp\exception\api_exception;
use local_mcp\read\tool_interface;
use local_mcp\security\authenticated_identity;
use local_personalxp\service\xp_manager;

/** Personal XP state for a learner and course, also used by LearnLingo. */
final class get_course_xp implements tool_interface {
    public function get_name(): string {
        return 'personalxp_get_course_xp';
    }

    public function get_title(): string {
        return 'Get learner Personal XP in a course';
    }

    public function get_description(): string {
        return 'Return total XP, current level, next level, level percentage, XP remaining '
            . 'and activity XP map for a Moodle course. This is separate from course completion '
            . 'progress displayed in LearnLingo. Defaults to the authenticated learner; '
            . 'other enrolled learners require local/personalxp:viewreport.';
    }

    public function get_input_schema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'courseid' => ['type' => 'integer', 'minimum' => 1],
                'userid' => ['type' => 'integer', 'minimum' => 1,
                    'description' => 'Optional: defaults to the connected learner.'],
            ],
            'required' => ['courseid'],
            'additionalProperties' => false,
        ];
    }

    public function get_required_capability(): string {
        return 'local/personalxp:view';
    }

    public function resolve_context(array $arguments): context {
        return context_course::instance((int)$arguments['courseid'], MUST_EXIST);
    }

    public function execute(array $arguments, authenticated_identity $identity): array {
        $courseid = (int)$arguments['courseid'];
        $userid = (int)($arguments['userid'] ?? $identity->userid);
        $context = context_course::instance($courseid);
        if (!has_capability('local/personalxp:view', $context, $identity->userid)
                || ($userid !== $identity->userid
                    && (!has_capability('local/personalxp:viewreport', $context, $identity->userid)
                        || !is_enrolled($context, $userid)))) {
            throw new api_exception('permission_denied', 403);
        }
        return ['courseid' => $courseid, 'userid' => $userid]
            + xp_manager::get_course_display_state($userid, $courseid);
    }
}
