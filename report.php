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
 * Course XP report.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_personalxp\service\xp_manager;

$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);
require_login($course);

$context = context_course::instance($courseid);
require_capability('local/personalxp:viewreport', $context);

$PAGE->set_url(new moodle_url('/local/personalxp/report.php', ['courseid' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('report', 'local_personalxp'));
$PAGE->set_heading(format_string($course->fullname));

$userfields = 'u.id,u.firstname,u.lastname,u.firstnamephonetic,u.lastnamephonetic,u.middlename,u.alternatename,u.email';
$users = get_enrolled_users($context, 'local/personalxp:view', 0, $userfields);
$totals = $DB->get_records('local_personalxp_user', ['courseid' => $courseid], '', 'userid,totalxp,timemodified');

uasort($users, static function (stdClass $a, stdClass $b): int {
    return strcoll(fullname($a), fullname($b));
});

echo $OUTPUT->header();
echo $OUTPUT->notification(get_string('reportnocompetition', 'local_personalxp'), 'info');

$table = new html_table();
$table->head = [
    get_string('participant', 'local_personalxp'),
    get_string('xp', 'local_personalxp'),
    get_string('level', 'local_personalxp'),
    get_string('lastgain', 'local_personalxp'),
];
foreach ($users as $user) {
    $summary = $totals[$user->id] ?? null;
    $totalxp = $summary ? (int) $summary->totalxp : 0;
    $state = xp_manager::get_level_state($totalxp);
    $lastgain = $summary ? userdate($summary->timemodified) : get_string('never');
    $table->data[] = [
        fullname($user),
        $totalxp,
        s($state['current']['name']),
        $lastgain,
    ];
}

echo html_writer::table($table);
echo $OUTPUT->footer();
