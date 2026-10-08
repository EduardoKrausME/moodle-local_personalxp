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

/** Recent learner XP awards without competitive rankings. */
final class get_xp_history implements tool_interface {
    public function get_name(): string {
        return 'personalxp_get_xp_history';
    }

    public function get_title(): string {
        return 'Get personal XP history';
    }

    public function get_description(): string {
        return 'Get the most recent XP awards, source labels and timestamps for a learner '
            . 'in a course. No leaderboards. Defaults to the connected learner; viewing '
            . 'another enrolled learner requires the XP report permission. Maximum 50 entries.';
    }

    public function get_input_schema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'courseid' => ['type' => 'integer', 'minimum' => 1],
                'userid' => ['type' => 'integer', 'minimum' => 1,
                    'description' => 'Optional: defaults to connected learner.'],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50,
                    'description' => 'Default 20, maximum 50.'],
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
        global $DB;
        $courseid = (int)$arguments['courseid'];
        $userid = (int)($arguments['userid'] ?? $identity->userid);
        $limit = (int)($arguments['limit'] ?? 20);
        if ($limit < 1 || $limit > 50) {
            throw new api_exception('invalid_limit', 400);
        }
        $context = context_course::instance($courseid);
        if (!has_capability('local/personalxp:view', $context, $identity->userid)
                || ($userid !== $identity->userid
                    && (!has_capability('local/personalxp:viewreport', $context, $identity->userid)
                        || !is_enrolled($context, $userid)))) {
            throw new api_exception('permission_denied', 403);
        }
        $history = [];
        if (xp_manager::is_enabled()) {
            $records = $DB->get_records('local_personalxp_log',
                ['courseid' => $courseid, 'userid' => $userid], 'timecreated DESC, id DESC',
                'id, rulekey, xp, label, timecreated', 0, $limit);
            foreach ($records as $record) {
                $history[] = [
                    'id' => (int)$record->id,
                    'rule' => $record->rulekey,
                    'xp' => (int)$record->xp,
                    'label' => $record->label,
                    'timecreated' => (int)$record->timecreated,
                ];
            }
        }
        return [
            'enabled' => xp_manager::is_enabled(),
            'courseid' => $courseid,
            'userid' => $userid,
            'count' => count($history),
            'history' => $history,
        ];
    }
}
