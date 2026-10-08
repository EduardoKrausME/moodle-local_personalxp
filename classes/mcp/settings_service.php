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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU GPL for details.

/**
 * MCP integration for Personal XP.
 *
 * @package local_personalxp
 * @copyright 2026 Eduardo Kraus
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_personalxp\mcp;

use local_mcp\exception\api_exception;
use local_personalxp\service\xp_manager;

/** Validated snapshots and partial edits of Personal XP settings. */
final class settings_service {
    /**
     * @return array Config types and default values.
     */
    public static function definitions(): array {
        return [
            'enabled' => ['type' => 'bool', 'default' => true],
            'aienabled' => ['type' => 'bool', 'default' => true],
            'xpperminute' => ['type' => 'int', 'default' => 2],
            'minactivityxp' => ['type' => 'int', 'default' => 2],
            'maxactivityxp' => ['type' => 'int', 'default' => 250],
            'xpactivity' => ['type' => 'int', 'default' => 20],
            'xpforum' => ['type' => 'int', 'default' => 5],
            'forumdailymax' => ['type' => 'int', 'default' => 20],
            'xpquiz' => ['type' => 'int', 'default' => 10],
            'xpcourse' => ['type' => 'int', 'default' => 200],
            'levels' => ['type' => 'levels',
                'default' => "0|Beginner\n100|Apprentice\n300|Explorer\n700|Practitioner\n1500|Specialist\n3000|Master"],
        ];
    }

    /**
     * @return array Current configuration and an optimistic concurrency hash.
     */
    public static function snapshot(): array {
        $settings = [];
        foreach (self::definitions() as $name => $definition) {
            $stored = get_config('local_personalxp', $name);
            $value = $stored === false ? $definition['default'] : $stored;
            if ($definition['type'] === 'bool') {
                $value = (bool)$value;
            } else if ($definition['type'] === 'int') {
                $value = (int)$value;
            } else {
                $value = (string)$value;
                if (trim($value) === '') {
                    $value = $definition['default'];
                }
            }
            $settings[$name] = $value;
        }
        return [
            'settings' => $settings,
            'levels' => xp_manager::parse_levels($settings['levels']),
            'state_hash' => hash('sha256', json_encode($settings,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)),
        ];
    }

    /**
     * @param array $arguments Proposed change.
     * @return array Validated changes.
     */
    public static function prepare(array $arguments): array {
        $before = self::snapshot();
        if (!isset($arguments['expected_hash']) || !is_string($arguments['expected_hash'])
                || !hash_equals($before['state_hash'], $arguments['expected_hash'])) {
            throw new api_exception('settings_changed', 409,
                'Read personalxp_get_settings again before changing XP rules.');
        }
        $input = $arguments['settings'] ?? null;
        if (!is_array($input) || !$input) {
            throw new api_exception('no_changes', 400);
        }
        $definitions = self::definitions();
        $changes = [];
        foreach ($input as $name => $value) {
            if (!isset($definitions[$name])) {
                throw new api_exception('unknown_setting', 400);
            }
            $type = $definitions[$name]['type'];
            if (($type === 'bool' && !is_bool($value))
                    || ($type === 'int' && (!is_int($value) || $value < 0 || $value > 100000))
                    || ($type === 'levels' && (!is_string($value) || strlen($value) > 6000))) {
                throw new api_exception('invalid_setting', 400);
            }
            if ($type === 'levels') {
                self::validate_levels($value);
            }
            if ($before['settings'][$name] !== $value) {
                $changes[$name] = $value;
            }
        }
        $effective = array_replace($before['settings'], $changes);
        if ($effective['minactivityxp'] > $effective['maxactivityxp']) {
            throw new api_exception('invalid_activity_xp_range', 400);
        }
        if (!$changes) {
            throw new api_exception('no_changes', 400);
        }
        return ['before' => $before, 'changes' => $changes];
    }

    /**
     * @param string $raw Level lines in XP|Name format.
     * @return void
     */
    private static function validate_levels(string $raw): void {
        $lines = preg_split('/\R/u', trim($raw));
        if (!$lines || count($lines) > 40) {
            throw new api_exception('invalid_levels', 400);
        }
        $previous = -1;
        foreach ($lines as $line) {
            $parts = explode('|', trim($line), 2);
            if (count($parts) !== 2 || !preg_match('/^\d+$/D', trim($parts[0]))) {
                throw new api_exception('invalid_levels', 400);
            }
            $threshold = (int)trim($parts[0]);
            $name = trim($parts[1]);
            if ($threshold < 0 || $threshold > 1000000 || $threshold <= $previous
                    || $name === '' || mb_strlen($name) > 100
                    || clean_param($name, PARAM_TEXT) !== $name) {
                throw new api_exception('invalid_levels', 400);
            }
            $previous = $threshold;
        }
        if ((int)trim(explode('|', trim($lines[0]), 2)[0]) !== 0) {
            throw new api_exception('invalid_levels', 400,
                'The first Personal XP level must begin at 0 XP.');
        }
    }

    /**
     * @param array $prepared Validated changes.
     * @return void
     */
    public static function apply(array $prepared): void {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        foreach ($prepared['changes'] as $name => $value) {
            set_config($name, is_bool($value) ? (int)$value : $value, 'local_personalxp');
        }
        $transaction->allow_commit();
    }
}
