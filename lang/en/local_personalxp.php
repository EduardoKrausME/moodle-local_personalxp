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
 * English strings for Personal XP.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Personal XP';
$string['personalxp:view'] = 'View own Personal XP';
$string['personalxp:viewreport'] = 'View Personal XP course report';
$string['enabled'] = 'Enable Personal XP';
$string['enabled_desc'] = 'Awards personal learning XP without leaderboards, podiums or learner-to-learner ranking.';
$string['xpactivity'] = 'XP for activity completion';
$string['xpactivity_desc'] = 'XP awarded once when an activity becomes complete.';
$string['xpforum'] = 'XP per forum post';
$string['xpforum_desc'] = 'XP awarded for a forum post. A daily cap prevents point farming.';
$string['forumdailymax'] = 'Daily forum XP cap';
$string['forumdailymax_desc'] = 'Maximum forum XP a learner can receive per course per day.';
$string['xpquiz'] = 'XP for quiz submission';
$string['xpquiz_desc'] = 'XP awarded for submitting a quiz attempt. It rewards participation and is independent from the grade.';
$string['xpcourse'] = 'XP for course completion';
$string['xpcourse_desc'] = 'XP awarded once when the course completion event is triggered.';
$string['levels'] = 'Levels';
$string['levels_desc'] = 'One level per line using XP|Name, for example 700|Practitioner. The list is ordered by XP automatically.';
$string['defaultlevel'] = 'Beginner';
$string['levelbeginner'] = 'Beginner';
$string['levelapprentice'] = 'Apprentice';
$string['levelexplorer'] = 'Explorer';
$string['levelpractitioner'] = 'Practitioner';
$string['levelspecialist'] = 'Specialist';
$string['levelmaster'] = 'Master';
$string['myxp'] = 'My Personal XP';
$string['report'] = 'Personal XP report';
$string['openreport'] = 'Open course XP report';
$string['currentlevel'] = 'Current level';
$string['totalxpvalue'] = '{$a} XP earned';
$string['nextlevel'] = '{$a->xp} XP until {$a->level}';
$string['highestlevel'] = 'You reached the highest configured level.';
$string['recenthistory'] = 'Recent XP history';
$string['nohistory'] = 'No XP has been earned in this course yet.';
$string['when'] = 'When';
$string['source'] = 'Source';
$string['xp'] = 'XP';
$string['level'] = 'Level';
$string['participant'] = 'Participant';
$string['lastgain'] = 'Last XP gain';
$string['activitycompleted'] = 'Activity completed';
$string['activitycompletedwithname'] = 'Completed: {$a}';
$string['coursecompleted'] = 'Course completed';
$string['forumparticipation'] = 'Forum participation';
$string['quizsubmitted'] = 'Quiz attempt submitted';
$string['reportnocompetition'] = 'This report is intentionally not ranked by XP. Personal XP measures each learner\'s own ' .
    'progress and is not a competition.';
$string['privacy:metadata:log'] = 'Stores the immutable history of XP awarded to learners.';
$string['privacy:metadata:log:userid'] = 'The learner who received the XP.';
$string['privacy:metadata:log:courseid'] = 'The course where the XP was earned.';
$string['privacy:metadata:log:rulekey'] = 'The internal rule that generated the XP.';
$string['privacy:metadata:log:xp'] = 'The amount of XP awarded.';
$string['privacy:metadata:log:label'] = 'A human-readable description of why XP was awarded.';
$string['privacy:metadata:log:timecreated'] = 'When the XP was awarded.';
$string['privacy:metadata:user'] = 'Stores aggregated XP totals per learner and course.';
$string['privacy:metadata:user:userid'] = 'The learner whose total is stored.';
$string['privacy:metadata:user:courseid'] = 'The course related to the total.';
$string['privacy:metadata:user:totalxp'] = 'The learner\'s total XP in the course.';
$string['privacy:metadata:user:timemodified'] = 'When the total was last updated.';
