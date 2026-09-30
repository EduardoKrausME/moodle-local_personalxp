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
 * Personal XP dashboard.
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
require_capability('local/personalxp:view', $context);

$PAGE->set_url(new moodle_url('/local/personalxp/index.php', ['courseid' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('myxp', 'local_personalxp'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->add_body_class('local-personalxp-page');

$totalxp = xp_manager::get_total($USER->id, $courseid);
$state = xp_manager::get_level_state($totalxp);

$history = $DB->get_records(
    'local_personalxp_log',
    ['userid' => $USER->id, 'courseid' => $courseid],
    'timecreated DESC',
    '*',
    0,
    20
);

echo $OUTPUT->header();

echo html_writer::start_div('personalxp-card');
echo html_writer::div(get_string('currentlevel', 'local_personalxp'), 'personalxp-eyebrow');
echo html_writer::tag('h2', s($state['current']['name']), ['class' => 'personalxp-level']);
echo html_writer::div(get_string('totalxpvalue', 'local_personalxp', $totalxp), 'personalxp-total');

echo html_writer::start_div('personalxp-progress', ['role' => 'progressbar', 'aria-valuemin' => '0',
    'aria-valuemax' => '100', 'aria-valuenow' => $state['progress']]);
echo html_writer::div('', 'personalxp-progress-bar', ['style' => 'width:' . $state['progress'] . '%']);
echo html_writer::end_div();

if ($state['next']) {
    $remaining = max(0, $state['next']['xp'] - $totalxp);
    echo html_writer::div(get_string('nextlevel', 'local_personalxp', (object) [
        'xp' => $remaining,
        'level' => $state['next']['name'],
    ]), 'personalxp-next');
} else {
    echo html_writer::div(get_string('highestlevel', 'local_personalxp'), 'personalxp-next');
}
echo html_writer::end_div();

if (has_capability('local/personalxp:viewreport', $context)) {
    echo html_writer::div(html_writer::link(
        new moodle_url('/local/personalxp/report.php', ['courseid' => $courseid]),
        get_string('openreport', 'local_personalxp'),
        ['class' => 'btn btn-secondary']
    ), 'mb-3');
}

echo html_writer::tag('h3', get_string('recenthistory', 'local_personalxp'));
if (!$history) {
    echo $OUTPUT->notification(get_string('nohistory', 'local_personalxp'), 'info');
} else {
    $table = new html_table();
    $table->head = [
        get_string('when', 'local_personalxp'),
        get_string('source', 'local_personalxp'),
        get_string('xp', 'local_personalxp'),
    ];
    foreach ($history as $item) {
        $table->data[] = [
            userdate($item->timecreated),
            s($item->label),
            '+' . (int) $item->xp . ' XP',
        ];
    }
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
