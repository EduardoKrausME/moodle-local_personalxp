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
 * Tests for adaptive activity XP.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_personalxp;

use advanced_testcase;
use local_personalxp\service\activity_xp_service;

/**
 * Adaptive activity XP tests.
 *
 * @covers \local_personalxp\service\activity_xp_service
 */
final class activity_xp_service_test extends advanced_testcase {
    /**
     * Method test_short_easy_page_receives_few_points.
     *
     * @return void Return value.
     */
    public function test_short_easy_page_receives_few_points(): void {
        $this->resetAfterTest();
        set_config('xpperminute', 2, 'local_personalxp');
        set_config('minactivityxp', 2, 'local_personalxp');
        set_config('maxactivityxp', 250, 'local_personalxp');

        $this->assertSame(4, activity_xp_service::calculate_xp(2, 1));
    }

    /**
     * Method test_difficulty_increases_reward_for_same_effort.
     *
     * @return void Return value.
     */
    public function test_difficulty_increases_reward_for_same_effort(): void {
        $this->resetAfterTest();
        set_config('xpperminute', 2, 'local_personalxp');
        set_config('minactivityxp', 2, 'local_personalxp');
        set_config('maxactivityxp', 250, 'local_personalxp');

        $easy = activity_xp_service::calculate_xp(20, 1);
        $hard = activity_xp_service::calculate_xp(20, 5);

        $this->assertSame(40, $easy);
        $this->assertSame(112, $hard);
        $this->assertGreaterThan($easy, $hard);
    }

    /**
     * Method test_activity_xp_respects_configured_cap.
     *
     * @return void Return value.
     */
    public function test_activity_xp_respects_configured_cap(): void {
        $this->resetAfterTest();
        set_config('xpperminute', 2, 'local_personalxp');
        set_config('minactivityxp', 2, 'local_personalxp');
        set_config('maxactivityxp', 100, 'local_personalxp');

        $this->assertSame(100, activity_xp_service::calculate_xp(180, 5));
    }
}
