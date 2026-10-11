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

/**
 * Confirmed administrative edit of XP rules, not learner XP balances.
 */
final class update_settings extends base_tool {
    /**
     * get_name
     *
     * @return string
     */
    public function get_name(): string {
        return 'personalxp_update_settings';
    }

    /**
     * get_title
     *
     * @return string
     */
    public function get_title(): string {
        return 'Update Personal XP rules and levels';
    }

    /**
     * get_description
     *
     * @return string
     */
    public function get_description(): string {
        return 'Configure the Personal XP rewards, AI scoring, enablement and level thresholds '
            . 'used by Moodle and LearnLingo. Read personalxp_get_settings and provide its '
            . 'state_hash before changing settings. Writes require explicit confirmation. '
            . 'Existing XP awards are never modified.';
    }

    /**
     * get_required_capability
     *
     * @return string
     */
    public function get_required_capability(): string {
        return 'moodle/site:config';
    }

    /**
     * get_input_schema
     *
     * @return array
     */
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

    /**
     * resolve_context
     *
     * @param array $arguments
     * @return context
     * @throws \dml_exception
     */
    public function resolve_context(array $arguments): context {
        return context_system::instance();
    }

    /**
     * supports_dry_run
     *
     * @return bool
     */
    public function supports_dry_run(): bool {
        return true;
    }

    /**
     * preview
     *
     * @param array $arguments
     * @param authenticated_identity $identity
     * @return array
     * @throws \local_mcp\exception\api_exception
     */
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

    /**
     * execute
     *
     * @param array $arguments
     * @param authenticated_identity $identity
     * @return array
     * @throws \JsonException
     * @throws \local_mcp\exception\api_exception
     */
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
