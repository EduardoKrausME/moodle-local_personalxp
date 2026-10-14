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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Course and activity restore for Personal XP.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/backup/moodle2/restore_local_plugin.class.php');

/**
 * Remaps XP award sources to the restored course, users and activities.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_local_personalxp_plugin extends restore_local_plugin {
    /** @var int[] Newly inserted award ids awaiting restored activity mappings. */
    private array $pendinglogs = [];

    /** @var int[] User ids whose XP totals must be reconciled after restoring awards. */
    private array $affectedusers = [];

    /**
     * Course-level learner history and totals.
     *
     * @return restore_path_element[]
     */
    protected function define_course_plugin_structure(): array {
        return [
            new restore_path_element('personalxp_log', $this->get_pathfor('/xp_logs/xp_log')),
            new restore_path_element('personalxp_total', $this->get_pathfor('/xp_totals/xp_total')),
        ];
    }

    /**
     * Module-level adaptive XP values, including single-activity duplication.
     *
     * @return restore_path_element[]
     */
    protected function define_module_plugin_structure(): array {
        return [new restore_path_element('personalxp_activity', $this->get_pathfor('/xp_activity'))];
    }

    /**
     * Save a learner award with a unique temporary hash until its source id is mapped.
     *
     * @param array $data Serialized award.
     * @return void
     */
    public function process_personalxp_log($data): void {
        global $DB;

        if (!$this->get_setting_value('users')) {
            return;
        }
        $data = (object)$data;
        $userid = (int)$this->get_mappingid('user', (int)$data->userid);
        if (!$userid) {
            return;
        }

        $oldid = (int)$data->id;
        unset($data->id);
        $data->userid = $userid;
        $data->courseid = (int)$this->task->get_courseid();
        // Old hashes cannot be reused because they include ids from the source site.
        // Keep the candidate distinct from existing awards until the mapping is complete.
        $data->uniquehash = hash('sha256', 'personalxp-restore|' . $this->get_restoreid() . '|' . $oldid);
        $id = $DB->insert_record('local_personalxp_log', $data);
        $this->pendinglogs[] = (int)$id;
        $this->affectedusers[$userid] = $userid;
    }

    /**
     * Record which user summaries need rebuilding, without overwriting existing totals.
     *
     * @param array $data Serialized summary.
     * @return void
     */
    public function process_personalxp_total($data): void {
        if (!$this->get_setting_value('users')) {
            return;
        }
        $data = (object)$data;
        $userid = (int)$this->get_mappingid('user', (int)$data->userid);
        if ($userid) {
            $this->affectedusers[$userid] = $userid;
        }
    }

    /**
     * Rebuild the activity record with the new module id and course id.
     *
     * @param array $data Serialized activity XP.
     * @return void
     */
    public function process_personalxp_activity($data): void {
        global $DB;

        $cmid = (int)$this->task->get_moduleid();
        $courseid = (int)$this->task->get_courseid();
        if (!$cmid || !$courseid) {
            return;
        }

        $data = (object)$data;
        unset($data->id);
        $data->cmid = $cmid;
        $data->courseid = $courseid;
        $existing = $DB->get_record('local_personalxp_activity', ['cmid' => $cmid]);
        if ($existing) {
            $data->id = $existing->id;
            $DB->update_record('local_personalxp_activity', $data);
        } else {
            $DB->insert_record('local_personalxp_activity', $data);
        }
    }

    /**
     * Recompute award identity once the course's activity and user mappings exist.
     *
     * Unrestored source events are discarded: retaining old object ids would make
     * future idempotency checks collide with unrelated activity ids on the target.
     *
     * @return void
     */
    public function after_restore_course(): void {
        global $DB;

        $courseid = (int)$this->task->get_courseid();
        foreach ($this->pendinglogs as $id) {
            $log = $DB->get_record('local_personalxp_log', ['id' => $id]);
            if (!$log) {
                continue;
            }

            $objectid = $this->remap_source_id($log);
            if (!$objectid) {
                $DB->delete_records('local_personalxp_log', ['id' => $id]);
                continue;
            }

            $hash = hash('sha256', implode('|', [
                $log->userid, $courseid, $log->rulekey, $objectid,
            ]));
            if ($DB->record_exists('local_personalxp_log', ['uniquehash' => $hash])) {
                // Restoring into an existing course must never double-award XP.
                $DB->delete_records('local_personalxp_log', ['id' => $id]);
                continue;
            }
            $log->objectid = $objectid;
            $log->uniquehash = $hash;
            $DB->update_record('local_personalxp_log', $log);
        }

        // Aggregates must always match the retained history, also on merge restores.
        foreach ($this->affectedusers as $userid) {
            $xp = (int)$DB->get_field_sql('SELECT COALESCE(SUM(xp), 0)
                FROM {local_personalxp_log} WHERE userid = :userid AND courseid = :courseid', [
                'userid' => $userid,
                'courseid' => $courseid,
            ]);
            $summary = $DB->get_record('local_personalxp_user', [
                'userid' => $userid,
                'courseid' => $courseid,
            ]);
            if ($summary) {
                $summary->totalxp = $xp;
                $summary->timemodified = time();
                $DB->update_record('local_personalxp_user', $summary);
            } else if ($xp > 0) {
                $DB->insert_record('local_personalxp_user', (object)[
                    'userid' => $userid,
                    'courseid' => $courseid,
                    'totalxp' => $xp,
                    'timemodified' => time(),
                ]);
            }
        }
    }

    /**
     * Restore an XP award's source id, only if its original source was restored.
     *
     * @param stdClass $log Restored record awaiting id remapping.
     * @return int Mapped object id, or zero when unavailable.
     */
    private function remap_source_id(stdClass $log): int {
        $objectid = (int)$log->objectid;
        switch ($log->rulekey) {
            case 'activity_completion':
                return (int)$this->get_mappingid('course_module', $objectid);
            case 'course_completion':
                return (int)$this->task->get_courseid();
            case 'forum_post':
                return (int)$this->get_mappingid('forum_post', $objectid);
            case 'quiz_attempt':
                return (int)$this->get_mappingid('quiz_attempt', $objectid);
            default:
                // An unfamiliar source cannot safely retain an id from another site.
                return 0;
        }
    }
}
