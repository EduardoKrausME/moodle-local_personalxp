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
 * Plugin callbacks for local_personalxp.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_personalxp\service\xp_manager;

/**
 * Extend course navigation.
 *
 * @param navigation_node $parentnode Course navigation node.
 * @param stdClass $course Course record.
 * @param context_course $context Course context.
 */
function local_personalxp_extend_navigation_course(
    navigation_node $parentnode,
    stdClass $course,
    context_course $context
): void {
    if (!xp_manager::is_enabled()) {
        return;
    }

    if (has_capability('local/personalxp:view', $context)) {
        $parentnode->add(
            get_string('myxp', 'local_personalxp'),
            new moodle_url('/local/personalxp/index.php', ['courseid' => $course->id]),
            navigation_node::TYPE_SETTING,
            null,
            'local_personalxp_myxp'
        );
    }

    if (has_capability('local/personalxp:viewreport', $context)) {
        $parentnode->add(
            get_string('report', 'local_personalxp'),
            new moodle_url('/local/personalxp/report.php', ['courseid' => $course->id]),
            navigation_node::TYPE_SETTING,
            null,
            'local_personalxp_report'
        );
    }
}
