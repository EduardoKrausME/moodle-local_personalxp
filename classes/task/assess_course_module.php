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
 * Ad hoc task that refines course module XP with AI Bridge.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_personalxp\task;

use core\task\adhoc_task;
use local_personalxp\service\activity_xp_service;

/**
 * Assess one course module outside the request that created or edited it.
 */
class assess_course_module extends adhoc_task {
    /**
     * Execute task.
     */
    public function execute(): void {
        $data = $this->get_custom_data();
        activity_xp_service::assess_with_ai(
            (int)($data->cmid ?? 0),
            (int)($data->userid ?? 0)
        );
    }
}
