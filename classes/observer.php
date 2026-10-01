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
 * Event observer for Personal XP.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_personalxp;

use core\event\base;
use core\event\course_completed;
use core\event\course_module_completion_updated;
use core\event\course_module_created;
use core\event\course_module_deleted;
use core\event\course_module_updated;
use core_date;
use core_user;
use local_personalxp\service\activity_xp_service;
use local_personalxp\service\xp_manager;
use mod_forum\event\post_created;
use mod_quiz\event\attempt_submitted;
use Throwable;

/**
 * Event observer.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * Create or refresh the adaptive XP assessment when a course module is created.
     *
     * @param course_module_created $event Event.
     */
    public static function course_module_created(course_module_created $event): void {
        activity_xp_service::queue_assessment((int)$event->contextinstanceid, (int)$event->userid);
    }

    /**
     * Refresh adaptive XP when the activity is edited.
     *
     * @param course_module_updated $event Event.
     */
    public static function course_module_updated(course_module_updated $event): void {
        activity_xp_service::queue_assessment((int)$event->contextinstanceid, (int)$event->userid);
    }

    /**
     * Remove the persisted assessment when a course module is deleted.
     *
     * @param course_module_deleted $event Event.
     */
    public static function course_module_deleted(course_module_deleted $event): void {
        activity_xp_service::delete_assessment((int)$event->contextinstanceid);
    }

    /**
     * Reassess a quiz after its question structure changes.
     *
     * @param base $event Quiz structure event.
     */
    public static function quiz_structure_changed(base $event): void {
        activity_xp_service::queue_assessment((int)$event->contextinstanceid, (int)$event->userid);
    }

    /**
     * Award XP when an activity becomes complete.
     *
     * @param course_module_completion_updated $event Event.
     */
    public static function course_module_completion_updated(course_module_completion_updated $event): void {
        if (!xp_manager::is_enabled()) {
            return;
        }

        $completion = $event->get_record_snapshot('course_modules_completion', $event->objectid);
        if (!in_array((int)$completion->completionstate, [COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS], true)) {
            return;
        }

        $userid = (int)$event->relateduserid;
        $courseid = (int)$event->courseid;
        $cmid = (int)$event->contextinstanceid;
        $xp = activity_xp_service::get_xp($cmid);

        $label = get_string('activitycompleted', 'local_personalxp');
        try {
            $cm = get_fast_modinfo($courseid, $userid)->get_cm($cmid);
            $label = get_string('activitycompletedwithname', 'local_personalxp', $cm->name);
        } catch (Throwable) {
            $label = get_string('activitycompleted', 'local_personalxp');
        }

        xp_manager::award(
            $userid,
            $courseid,
            'activity_completion',
            $cmid,
            $xp,
            $label,
            'core_completion',
            $event::class
        );
    }

    /**
     * Award XP when a course is completed.
     *
     * @param course_completed $event Event.
     */
    public static function course_completed(course_completed $event): void {
        $userid = (int)($event->relateduserid ?: $event->userid);
        $courseid = (int)$event->courseid;
        $xp = max(0, xp_manager::get_int_setting('xpcourse', 200));

        xp_manager::award(
            $userid,
            $courseid,
            'course_completion',
            $courseid,
            $xp,
            get_string('coursecompleted', 'local_personalxp'),
            'core_completion',
            $event::class
        );
    }

    /**
     * Award limited XP for meaningful forum participation.
     *
     * @param post_created $event Event.
     */
    public static function forum_post_created(post_created $event): void {
        $userid = (int)$event->userid;
        $courseid = (int)$event->courseid;
        $xp = max(0, xp_manager::get_int_setting('xpforum', 5));
        $dailymax = max(0, xp_manager::get_int_setting('forumdailymax', 20));

        if ($xp <= 0 || $dailymax <= 0) {
            return;
        }

        $user = core_user::get_user($userid, 'id,timezone', MUST_EXIST);
        $timezone = core_date::get_user_timezone($user);
        $daystart = usergetmidnight(time(), $timezone);
        $awarded = xp_manager::get_awarded_since($userid, $courseid, 'forum_post', $daystart);
        $xp = min($xp, max(0, $dailymax - $awarded));
        if ($xp <= 0) {
            return;
        }

        xp_manager::award(
            $userid,
            $courseid,
            'forum_post',
            (int)$event->objectid,
            $xp,
            get_string('forumparticipation', 'local_personalxp'),
            'mod_forum',
            $event::class
        );
    }

    /**
     * Award XP for submitting a quiz attempt, independently from the grade.
     *
     * @param attempt_submitted $event Event.
     */
    public static function quiz_attempt_submitted(attempt_submitted $event): void {
        $userid = (int)($event->relateduserid ?: $event->userid);
        $courseid = (int)$event->courseid;
        $xp = max(0, xp_manager::get_int_setting('xpquiz', 10));

        xp_manager::award(
            $userid,
            $courseid,
            'quiz_attempt',
            (int)$event->objectid,
            $xp,
            get_string('quizsubmitted', 'local_personalxp'),
            'mod_quiz',
            $event::class
        );
    }
}
