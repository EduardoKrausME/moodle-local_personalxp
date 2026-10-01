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
 * Admin settings for local_personalxp.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_personalxp', get_string('pluginname', 'local_personalxp'));

    $settings->add(new admin_setting_configcheckbox(
        'local_personalxp/enabled',
        get_string('enabled', 'local_personalxp'),
        get_string('enabled_desc', 'local_personalxp'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_personalxp/aienabled',
        get_string('aienabled', 'local_personalxp'),
        get_string('aienabled_desc', 'local_personalxp'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_personalxp/xpperminute',
        get_string('xpperminute', 'local_personalxp'),
        get_string('xpperminute_desc', 'local_personalxp'),
        2,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_personalxp/minactivityxp',
        get_string('minactivityxp', 'local_personalxp'),
        get_string('minactivityxp_desc', 'local_personalxp'),
        2,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_personalxp/maxactivityxp',
        get_string('maxactivityxp', 'local_personalxp'),
        get_string('maxactivityxp_desc', 'local_personalxp'),
        250,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_personalxp/xpactivity',
        get_string('xpactivity', 'local_personalxp'),
        get_string('xpactivity_desc', 'local_personalxp'),
        20,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_personalxp/xpforum',
        get_string('xpforum', 'local_personalxp'),
        get_string('xpforum_desc', 'local_personalxp'),
        5,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_personalxp/forumdailymax',
        get_string('forumdailymax', 'local_personalxp'),
        get_string('forumdailymax_desc', 'local_personalxp'),
        20,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_personalxp/xpquiz',
        get_string('xpquiz', 'local_personalxp'),
        get_string('xpquiz_desc', 'local_personalxp'),
        10,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_personalxp/xpcourse',
        get_string('xpcourse', 'local_personalxp'),
        get_string('xpcourse_desc', 'local_personalxp'),
        200,
        PARAM_INT
    ));

    $defaultlevels = "0|Beginner\n100|Apprentice\n300|Explorer\n700|Practitioner\n1500|Specialist\n3000|Master";
    $settings->add(new admin_setting_configtextarea(
        'local_personalxp/levels',
        get_string('levels', 'local_personalxp'),
        get_string('levels_desc', 'local_personalxp'),
        $defaultlevels,
        PARAM_RAW_TRIMMED
    ));

    $ADMIN->add('localplugins', $settings);
}
