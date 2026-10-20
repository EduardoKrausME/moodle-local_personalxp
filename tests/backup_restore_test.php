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
 * Course backup and restore tests for Personal XP.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_personalxp;

use advanced_testcase;
use backup;
use backup_controller;
use local_personalxp\service\xp_manager;
use restore_controller;
use restore_dbops;

/**
 * Exercises the Moodle backup pipeline rather than manually calling restore hooks.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \backup_local_personalxp_plugin
 * @covers     \restore_local_personalxp_plugin
 */
final class backup_restore_test extends advanced_testcase {
    /**
     * The restored course must retain activity configuration and idempotent learner XP.
     *
     * @return void
     */
    public function test_course_backup_restores_xp_with_user_data(): void {
        global $DB, $USER, $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enabled', 1, 'local_personalxp');
        set_config('backup_general_users', 1, 'backup');
        $course = $this->getDataGenerator()->create_course();
        $page = $this->getDataGenerator()->create_module('page', ['course' => $course->id]);
        $learner = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($learner->id, $course->id);

        $DB->delete_records('local_personalxp_activity', ['cmid' => $page->cmid]);
        $DB->insert_record('local_personalxp_activity', (object)[
            'cmid' => $page->cmid,
            'courseid' => $course->id,
            'xp' => 47,
            'difficulty' => 4,
            'estimatedminutes' => 12,
            'source' => 'local',
            'rationale' => 'Test estimate',
            'contenthash' => hash('sha256', 'test-content'),
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
        $this->assertTrue(xp_manager::award(
            $learner->id, $course->id, 'activity_completion', $page->cmid,
            47, 'Finished page', 'core_completion', 'test'
        ));
        $this->assertTrue(xp_manager::award(
            $learner->id, $course->id, 'course_completion', $course->id,
            200, 'Finished course', 'core_completion', 'test'
        ));

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        $bc = new backup_controller(backup::TYPE_1COURSE, $course->id, backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO, backup::MODE_GENERAL, $USER->id);
        $bc->get_plan()->get_setting('users')->set_value(1);
        $bc->execute_plan();
        $destination = $bc->get_results()['backup_destination'];
        $restoreid = 'personalxp-' . random_string(12);
        $targetpath = make_backup_temp_directory($restoreid);
        $packer = get_file_packer('application/vnd.moodle.backup');
        $destination->extract_to_pathname($packer, $targetpath);
        $bc->destroy();

        $newcourseid = restore_dbops::create_new_course(
            $course->fullname, $course->shortname . '-copy', $course->category
        );
        $rc = new restore_controller($restoreid, $newcourseid, backup::INTERACTIVE_NO,
            backup::MODE_GENERAL, $USER->id, backup::TARGET_NEW_COURSE);
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        $newpage = array_values(get_fast_modinfo($newcourseid)->get_instances_of('page'))[0];
        $newxp = $DB->get_record('local_personalxp_activity', ['cmid' => $newpage->id], '*', MUST_EXIST);
        $this->assertEquals($newcourseid, $newxp->courseid);
        $this->assertEquals(47, $newxp->xp);
        $this->assertEquals(4, $newxp->difficulty);
        $this->assertEquals(247, xp_manager::get_total($learner->id, $newcourseid));
        $this->assertEquals(2, $DB->count_records('local_personalxp_log', [
            'userid' => $learner->id, 'courseid' => $newcourseid,
        ]));
        // The restored award hash must use the new module and course ids.
        $this->assertFalse(xp_manager::award(
            $learner->id, $newcourseid, 'activity_completion', $newpage->id,
            47, 'Finished page', 'core_completion', 'test'
        ));
    }
}
