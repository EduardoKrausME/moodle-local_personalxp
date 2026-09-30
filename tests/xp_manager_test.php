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

use local_personalxp\service\xp_manager;

/**
 * Tests for XP manager.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class xp_manager_test extends \advanced_testcase {
    public function test_parse_levels_orders_and_ignores_invalid_lines(): void {
        $levels = xp_manager::parse_levels("700|Practitioner\ninvalid\n0|Beginner\n100|Apprentice");
        $this->assertSame(0, $levels[0]['xp']);
        $this->assertSame('Beginner', $levels[0]['name']);
        $this->assertSame(700, $levels[2]['xp']);
    }

    public function test_level_state_calculates_progress(): void {
        $this->resetAfterTest();
        set_config('levels', "0|Beginner\n100|Apprentice\n300|Explorer", 'local_personalxp');
        $state = xp_manager::get_level_state(150);
        $this->assertSame('Apprentice', $state['current']['name']);
        $this->assertSame('Explorer', $state['next']['name']);
        $this->assertSame(25, $state['progress']);
    }
}
