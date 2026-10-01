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
 * Tests for the Personal XP service.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_personalxp;

use advanced_testcase;
use local_personalxp\service\xp_manager;

/**
 * Tests for XP manager.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_personalxp\service\xp_manager
 */
final class xp_manager_test extends advanced_testcase {
    /**
     * Method test_parse_levels_orders_and_ignores_invalid_lines.
     *
     * @return void Return value.
     */
    public function test_parse_levels_orders_and_ignores_invalid_lines(): void {
        $levels = xp_manager::parse_levels("700|Practitioner\ninvalid\n0|Beginner\n100|Apprentice");
        $this->assertSame(0, $levels[0]['xp']);
        $this->assertSame('Beginner', $levels[0]['name']);
        $this->assertSame(700, $levels[2]['xp']);
    }

    /**
     * Method test_level_state_calculates_progress.
     *
     * @return void Return value.
     */
    public function test_level_state_calculates_progress(): void {
        $this->resetAfterTest();
        set_config('levels', "0|Beginner\n100|Apprentice\n300|Explorer", 'local_personalxp');
        $state = xp_manager::get_level_state(150);
        $this->assertSame('Apprentice', $state['current']['name']);
        $this->assertSame('Explorer', $state['next']['name']);
        $this->assertSame(25, $state['progress']);
    }

    /**
     * Method test_course_display_state_respects_enabled_setting.
     *
     * @return void Return value.
     */
    public function test_course_display_state_respects_enabled_setting(): void {
        $this->resetAfterTest();
        set_config('enabled', 0, 'local_personalxp');
        $state = xp_manager::get_course_display_state(123, 456);
        $this->assertFalse($state['enabled']);
        $this->assertSame(0, $state['totalxp']);
    }

    /**
     * Method test_course_display_state_returns_level_and_completion_reward.
     *
     * @return void Return value.
     */
    public function test_course_display_state_returns_level_and_completion_reward(): void {
        $this->resetAfterTest();
        set_config('enabled', 1, 'local_personalxp');
        set_config('levels', "0|Beginner\n100|Apprentice\n300|Explorer", 'local_personalxp');
        set_config('xpactivity', 25, 'local_personalxp');

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        xp_manager::award($user->id, $course->id, 'test', 1, 150, 'Test', 'local_personalxp', __METHOD__);

        $state = xp_manager::get_course_display_state($user->id, $course->id);
        $this->assertTrue($state['enabled']);
        $this->assertSame(150, $state['totalxp']);
        $this->assertSame('Apprentice', $state['currentlevel']);
        $this->assertSame('Explorer', $state['nextlevel']);
        $this->assertSame(25, $state['levelprogress']);
        $this->assertSame(150, $state['xptonext']);
        $this->assertSame(25, $state['activitycompletionxp']);
    }
}
