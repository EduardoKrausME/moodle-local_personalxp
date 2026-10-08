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

use context;
use context_system;
use local_mcp\security\authenticated_identity;
use local_mcp\write\tool\base_tool;

/** Confirmed administrative edit of XP rules, not learner XP balances. */
final class update_settings extends base_tool {
    public function get_name(): string {
        return 'personalxp_update_settings';
    }

    public function get_title(): string {
        return 'Update Personal XP rules and levels';
    }

    public function get_description(): string {
        return 'Configure the Personal XP rewards, AI scoring, enablement and level thresholds '
            . 'used by Moodle and LearnLingo. Read personalxp_get_settings and provide its '
            . 'state_hash before changing settings. Writes require explicit confirmation. '
            . 'Existing XP awards are never modified.';
    }

    public function get_required_capability(): string {
        return 'moodle/site:config';
    }

    public function get_input_schema(): array {
        $fields = [];
        foreach (settings_service::definitions() as $name => $definition) {
            $type = $definition['type'];
            $property = ['type' => $type === 'bool' ? 'boolean' : ($type === 'int' ? 'integer' : 'string'),
                'description' => $name];
            if ($type === 'int') {
                $property['minimum'] = 0;
                $property['maximum'] = 100000;
            }
            $fields[$name] = $property;
        }
        return $this->object_schema([
            'settings' => [
                'type' => 'object',
                'description' => 'Partial settings. Unspecified values remain unchanged.',
                'properties' => (object)$fields,
                'additionalProperties' => false,
            ],
            'expected_hash' => [
                'type' => 'string', 'minLength' => 64, 'maxLength' => 64,
                'description' => 'state_hash from personalxp_get_settings.',
            ],
        ], ['settings', 'expected_hash']);
    }

    public function resolve_context(array $arguments): context {
        return context_system::instance();
    }

    public function supports_dry_run(): bool {
        return true;
    }

    public function preview(array $arguments, authenticated_identity $identity): array {
        $prepared = settings_service::prepare($arguments);
        return [
            'changed_settings' => array_keys($prepared['changes']),
            'before' => array_intersect_key($prepared['before']['settings'], $prepared['changes']),
            'after' => $prepared['changes'],
            'changes_existing_awards' => false,
            'requires_confirmation' => true,
        ];
    }

    public function execute(array $arguments, authenticated_identity $identity): array {
        $prepared = settings_service::prepare($arguments);
        settings_service::apply($prepared);
        return [
            'success' => true,
            'changed_settings' => array_keys($prepared['changes']),
            'changes_existing_awards' => false,
            'state_hash' => settings_service::snapshot()['state_hash'],
        ];
    }
}
