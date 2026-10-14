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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Course and activity backup for Personal XP.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/backup/moodle2/backup_local_plugin.class.php');

/**
 * Backs up learner XP at course level and the adaptive XP of each activity.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_local_personalxp_plugin extends backup_local_plugin {
    /**
     * Include XP history and totals only when the course backup includes user data.
     *
     * @return backup_plugin_element
     */
    protected function define_course_plugin_structure(): backup_plugin_element {
        $plugin = $this->get_plugin_element();
        $wrapper = new backup_nested_element($this->get_recommended_name());
        $plugin->add_child($wrapper);

        $logs = new backup_nested_element('xp_logs');
        $log = new backup_nested_element('xp_log', ['id'], [
            'userid', 'rulekey', 'component', 'eventname', 'objectid',
            'xp', 'label', 'timecreated',
        ]);
        $totals = new backup_nested_element('xp_totals');
        $total = new backup_nested_element('xp_total', ['id'], [
            'userid', 'totalxp', 'timemodified',
        ]);

        $wrapper->add_child($logs);
        $logs->add_child($log);
        $wrapper->add_child($totals);
        $totals->add_child($total);

        if ($this->get_setting_value('users')) {
            $log->set_source_table('local_personalxp_log', ['courseid' => backup::VAR_COURSEID]);
            $total->set_source_table('local_personalxp_user', ['courseid' => backup::VAR_COURSEID]);
            $log->annotate_ids('user', 'userid');
            $total->annotate_ids('user', 'userid');
        }

        return $plugin;
    }

    /**
     * Retain a module's XP value even when duplicating just that activity.
     *
     * @return backup_plugin_element
     */
    protected function define_module_plugin_structure(): backup_plugin_element {
        $plugin = $this->get_plugin_element();
        $wrapper = new backup_nested_element($this->get_recommended_name());
        $activity = new backup_nested_element('xp_activity', ['id'], [
            'xp', 'difficulty', 'estimatedminutes', 'source', 'rationale',
            'contenthash', 'timecreated', 'timemodified',
        ]);

        $plugin->add_child($wrapper);
        $wrapper->add_child($activity);
        $activity->set_source_table('local_personalxp_activity', ['cmid' => backup::VAR_MODID]);
        return $plugin;
    }
}
