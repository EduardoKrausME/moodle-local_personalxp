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
 * XP service for Personal XP.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_personalxp\service;

/**
 * XP service.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class xp_manager {
    /**
     * Whether the plugin is enabled.
     *
     * @return bool
     */
    public static function is_enabled(): bool {
        $value = get_config('local_personalxp', 'enabled');
        return $value === false ? true : (bool) $value;
    }

    /**
     * Return an integer setting with a safe default when it has never been saved.
     *
     * @param string $name Setting name.
     * @param int $default Default value.
     * @return int
     */
    public static function get_int_setting(string $name, int $default): int {
        $value = get_config('local_personalxp', $name);
        return $value === false ? $default : (int) $value;
    }

    /**
     * Award XP exactly once for the supplied unique source.
     *
     * @param int $userid User id.
     * @param int $courseid Course id.
     * @param string $rulekey Stable rule identifier.
     * @param int $objectid Source object id.
     * @param int $xp XP amount.
     * @param string $label Human readable source label.
     * @param string $component Moodle component.
     * @param string $eventname Event class name.
     * @return bool True when XP was awarded.
     */
    public static function award(
        int $userid,
        int $courseid,
        string $rulekey,
        int $objectid,
        int $xp,
        string $label,
        string $component,
        string $eventname
    ): bool {
        global $DB;

        if (!self::is_enabled() || $userid <= 0 || $courseid <= 0 || $xp <= 0) {
            return false;
        }

        $uniquehash = hash('sha256', implode('|', [$userid, $courseid, $rulekey, $objectid]));
        if ($DB->record_exists('local_personalxp_log', ['uniquehash' => $uniquehash])) {
            return false;
        }

        $transaction = $DB->start_delegated_transaction();
        try {
            $now = time();
            $log = (object) [
                'userid' => $userid,
                'courseid' => $courseid,
                'rulekey' => substr($rulekey, 0, 64),
                'component' => substr($component, 0, 100),
                'eventname' => substr($eventname, 0, 255),
                'objectid' => $objectid,
                'xp' => $xp,
                'label' => substr($label, 0, 255),
                'uniquehash' => $uniquehash,
                'timecreated' => $now,
            ];
            $DB->insert_record('local_personalxp_log', $log);

            $summary = $DB->get_record('local_personalxp_user', [
                'userid' => $userid,
                'courseid' => $courseid,
            ]);
            if ($summary) {
                $summary->totalxp += $xp;
                $summary->timemodified = $now;
                $DB->update_record('local_personalxp_user', $summary);
            } else {
                $DB->insert_record('local_personalxp_user', (object) [
                    'userid' => $userid,
                    'courseid' => $courseid,
                    'totalxp' => $xp,
                    'timemodified' => $now,
                ]);
            }

            $transaction->allow_commit();
            return true;
        } catch (\dml_write_exception $exception) {
            $transaction->rollback($exception);
            return false;
        }
    }

    /**
     * Return total XP for a user in a course.
     *
     * @param int $userid User id.
     * @param int $courseid Course id.
     * @return int
     */
    public static function get_total(int $userid, int $courseid): int {
        global $DB;
        return (int) $DB->get_field('local_personalxp_user', 'totalxp', [
            'userid' => $userid,
            'courseid' => $courseid,
        ]);
    }

    /**
     * Return XP already awarded for a rule since a timestamp.
     *
     * @param int $userid User id.
     * @param int $courseid Course id.
     * @param string $rulekey Rule key.
     * @param int $since Timestamp.
     * @return int
     */
    public static function get_awarded_since(int $userid, int $courseid, string $rulekey, int $since): int {
        global $DB;
        $sql = "SELECT COALESCE(SUM(xp), 0)
                  FROM {local_personalxp_log}
                 WHERE userid = :userid
                   AND courseid = :courseid
                   AND rulekey = :rulekey
                   AND timecreated >= :since";
        return (int) $DB->get_field_sql($sql, [
            'userid' => $userid,
            'courseid' => $courseid,
            'rulekey' => $rulekey,
            'since' => $since,
        ]);
    }

    /**
     * Parse configured levels.
     *
     * Format: one "XP|Name" pair per line.
     *
     * @param string|null $config Raw configuration.
     * @return array<int, array{xp:int,name:string}>
     */
    public static function parse_levels(?string $config = null): array {
        if ($config === null) {
            $stored = get_config('local_personalxp', 'levels');
            if ($stored === false || trim((string) $stored) === '') {
                $config = implode("\n", [
                    '0|' . get_string('levelbeginner', 'local_personalxp'),
                    '100|' . get_string('levelapprentice', 'local_personalxp'),
                    '300|' . get_string('levelexplorer', 'local_personalxp'),
                    '700|' . get_string('levelpractitioner', 'local_personalxp'),
                    '1500|' . get_string('levelspecialist', 'local_personalxp'),
                    '3000|' . get_string('levelmaster', 'local_personalxp'),
                ]);
            } else {
                $config = (string) $stored;
            }
        }

        $levels = [];
        foreach (preg_split('/\R/', $config) as $line) {
            $line = trim($line);
            if ($line === '' || !str_contains($line, '|')) {
                continue;
            }
            [$xp, $name] = array_map('trim', explode('|', $line, 2));
            if ($name === '' || !is_numeric($xp) || (int) $xp < 0) {
                continue;
            }
            $levels[] = ['xp' => (int) $xp, 'name' => clean_param($name, PARAM_TEXT)];
        }

        if (!$levels) {
            $levels = [
                ['xp' => 0, 'name' => get_string('defaultlevel', 'local_personalxp')],
            ];
        }

        usort($levels, static fn(array $a, array $b): int => $a['xp'] <=> $b['xp']);
        return $levels;
    }

    /**
     * Resolve current and next level.
     *
     * @param int $xp Current XP.
     * @return array{current:array{xp:int,name:string},next:?array{xp:int,name:string},progress:int}
     */
    public static function get_level_state(int $xp): array {
        $levels = self::parse_levels();
        $current = $levels[0];
        $next = null;
        foreach ($levels as $level) {
            if ($level['xp'] <= $xp) {
                $current = $level;
                continue;
            }
            $next = $level;
            break;
        }

        $progress = 100;
        if ($next !== null) {
            $range = max(1, $next['xp'] - $current['xp']);
            $progress = (int) floor((($xp - $current['xp']) / $range) * 100);
            $progress = max(0, min(100, $progress));
        }

        return ['current' => $current, 'next' => $next, 'progress' => $progress];
    }
}
