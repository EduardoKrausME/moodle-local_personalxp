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

$string['activitycompleted'] = 'Activity completed';
$string['aienabled'] = 'Use AI to refine activity XP';
$string['aienabled_desc'] = 'Queues an AI Bridge assessment after an activity is created or its scoring-relevant content changes. If AI is unavailable, the deterministic local estimate remains in use.';
$string['activitycompletedwithname'] = 'Completed: {$a}';
$string['coursecompleted'] = 'Course completed';
$string['currentlevel'] = 'Current level';
$string['defaultlevel'] = 'Beginner';
$string['enabled'] = 'Enable Personal XP';
$string['enabled_desc'] = 'Awards personal learning XP without leaderboards, podiums or learner-to-learner ranking.';
$string['forumdailymax'] = 'Daily forum XP cap';
$string['forumdailymax_desc'] = 'Maximum forum XP a learner can receive per course per day.';
$string['forumparticipation'] = 'Forum participation';
$string['highestlevel'] = 'You reached the highest configured level.';
$string['lastgain'] = 'Last XP gain';
$string['level'] = 'Level';
$string['levelapprentice'] = 'Apprentice';
$string['levelbeginner'] = 'Beginner';
$string['levelexplorer'] = 'Explorer';
$string['levelmaster'] = 'Master';
$string['levelpractitioner'] = 'Practitioner';
$string['levels'] = 'Levels';
$string['levels_desc'] = 'One level per line using XP|Name, for example 700|Practitioner. The list is ordered by XP automatically.';
$string['levelspecialist'] = 'Specialist';
$string['maxactivityxp'] = 'Maximum XP per activity';
$string['maxactivityxp_desc'] = 'Upper limit applied after time and difficulty are converted to XP.';
$string['minactivityxp'] = 'Minimum XP per activity';
$string['minactivityxp_desc'] = 'Lower limit applied after time and difficulty are converted to XP.';
$string['myxp'] = 'My Personal XP';
$string['nextlevel'] = '{$a->xp} XP until {$a->level}';
$string['nohistory'] = 'No XP has been earned in this course yet.';
$string['openreport'] = 'Open course XP report';
$string['participant'] = 'Participant';
$string['personalxp:view'] = 'View own Personal XP';
$string['personalxp:viewreport'] = 'View Personal XP course report';
$string['pluginname'] = 'Personal XP';
$string['privacy:metadata:log'] = 'Stores the immutable history of XP awarded to learners.';
$string['privacy:metadata:log:courseid'] = 'The course where the XP was earned.';
$string['privacy:metadata:log:label'] = 'A human-readable description of why XP was awarded.';
$string['privacy:metadata:log:rulekey'] = 'The internal rule that generated the XP.';
$string['privacy:metadata:log:timecreated'] = 'When the XP was awarded.';
$string['privacy:metadata:log:userid'] = 'The learner who received the XP.';
$string['privacy:metadata:log:xp'] = 'The amount of XP awarded.';
$string['privacy:metadata:user'] = 'Stores aggregated XP totals per learner and course.';
$string['privacy:metadata:user:courseid'] = 'The course related to the total.';
$string['privacy:metadata:user:timemodified'] = 'When the total was last updated.';
$string['privacy:metadata:user:totalxp'] = 'The learner\'s total XP in the course.';
$string['privacy:metadata:user:userid'] = 'The learner whose total is stored.';
$string['quizsubmitted'] = 'Quiz attempt submitted';
$string['recenthistory'] = 'Recent XP history';
$string['report'] = 'Personal XP report';
$string['reportnocompetition'] = 'This report is intentionally not ranked by XP. Personal XP measures each learner\'s own ' .
    'progress and is not a competition.';
$string['source'] = 'Source';
$string['totalxpvalue'] = '{$a} XP earned';
$string['when'] = 'When';
$string['xp'] = 'XP';
$string['xpactivity'] = 'XP for activity completion';
$string['xpactivity_desc'] = 'XP awarded once when an activity becomes complete.';
$string['xpcourse'] = 'XP for course completion';
$string['xpcourse_desc'] = 'XP awarded once when the course completion event is triggered.';
$string['xpforum'] = 'XP per forum post';
$string['xpforum_desc'] = 'XP awarded for a forum post. A daily cap prevents point farming.';
$string['xpperminute'] = 'Base XP per estimated minute';
$string['xpperminute_desc'] = 'Base XP used before the difficulty multiplier is applied. Difficulty multipliers are controlled by the plugin, not by AI.';
$string['xpquiz'] = 'XP for quiz submission';
$string['xpquiz_desc'] = 'XP awarded for submitting a quiz attempt. It rewards participation and is independent from the grade.';

