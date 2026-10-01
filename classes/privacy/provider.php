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
 * Privacy provider for Personal XP.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_personalxp\privacy;

use context;
use context_course;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for local_personalxp.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    core_userlist_provider {

    /**
     * Describe stored personal data.
     *
     * @param collection $collection Collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_personalxp_log', [
            'userid' => 'privacy:metadata:log:userid',
            'courseid' => 'privacy:metadata:log:courseid',
            'rulekey' => 'privacy:metadata:log:rulekey',
            'xp' => 'privacy:metadata:log:xp',
            'label' => 'privacy:metadata:log:label',
            'timecreated' => 'privacy:metadata:log:timecreated',
        ], 'privacy:metadata:log');
        $collection->add_database_table('local_personalxp_user', [
            'userid' => 'privacy:metadata:user:userid',
            'courseid' => 'privacy:metadata:user:courseid',
            'totalxp' => 'privacy:metadata:user:totalxp',
            'timemodified' => 'privacy:metadata:user:timemodified',
        ], 'privacy:metadata:user');
        return $collection;
    }

    /**
     * Get contexts containing data for a user.
     *
     * @param int $userid User id.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {local_personalxp_user} px ON px.courseid = ctx.instanceid
                 WHERE ctx.contextlevel = :contextlevel AND px.userid = :userid";
        $contextlist->add_from_sql($sql, ['contextlevel' => CONTEXT_COURSE, 'userid' => $userid]);
        return $contextlist;
    }

    /**
     * Export user data.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_course) {
                continue;
            }
            $summary = $DB->get_record('local_personalxp_user', ['userid' => $userid, 'courseid' => $context->instanceid]);
            $history = $DB->get_records(
                'local_personalxp_log',
                ['userid' => $userid, 'courseid' => $context->instanceid],
                'timecreated ASC'
            );
            $data = [
                'totalxp' => $summary ? (int)$summary->totalxp : 0,
                'history' => array_values(array_map(static function ($row) {
                    return [
                        'xp' => (int)$row->xp,
                        'source' => $row->label,
                        'timecreated' => transform::datetime($row->timecreated),
                    ];
                }, $history)),
            ];
            writer::with_context($context)->export_data([get_string('pluginname', 'local_personalxp')], (object)$data);
        }
    }

    /**
     * Delete all user data in a context.
     *
     * @param context $context Context.
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;
        if (!$context instanceof context_course) {
            return;
        }
        $DB->delete_records('local_personalxp_log', ['courseid' => $context->instanceid]);
        $DB->delete_records('local_personalxp_user', ['courseid' => $context->instanceid]);
    }

    /**
     * Delete data for a user in approved contexts.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_course) {
                continue;
            }
            $DB->delete_records('local_personalxp_log', ['userid' => $userid, 'courseid' => $context->instanceid]);
            $DB->delete_records('local_personalxp_user', ['userid' => $userid, 'courseid' => $context->instanceid]);
        }
    }

    /**
     * Add users with data in a context to a user list.
     *
     * @param userlist $userlist User list.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof context_course) {
            return;
        }
        $sql = "SELECT userid FROM {local_personalxp_user} WHERE courseid = :courseid";
        $userlist->add_from_sql('userid', $sql, ['courseid' => $context->instanceid]);
    }

    /**
     * Delete data for approved users in a context.
     *
     * @param approved_userlist $userlist Approved users.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;
        $context = $userlist->get_context();
        if (!$context instanceof context_course) {
            return;
        }
        $userids = $userlist->get_userids();
        if (!$userids) {
            return;
        }
        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params['courseid'] = $context->instanceid;
        $DB->delete_records_select('local_personalxp_log', "courseid = :courseid AND userid $insql", $params);
        $DB->delete_records_select('local_personalxp_user', "courseid = :courseid AND userid $insql", $params);
    }
}
