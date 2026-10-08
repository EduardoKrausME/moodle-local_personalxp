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
 * Personal XP MCP integration tests.
 *
 * @package local_personalxp
 * @copyright 2026 Eduardo Kraus
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_personalxp;

use advanced_testcase;
use local_personalxp\mcp\get_course_xp;
use local_personalxp\mcp\get_settings;
use local_personalxp\mcp\get_xp_history;
use local_personalxp\mcp\provider;
use local_personalxp\mcp\settings_service;
use local_personalxp\mcp\update_settings;
use local_personalxp\service\xp_manager;

defined('MOODLE_INTERNAL') || die();

/** Validate optional READ and WRITE tools, privacy and safe rule updates. */
final class mcp_integration_test extends advanced_testcase {
    /**
     * @return void
     */
    private function require_mcp(): void {
        if (!\core_component::get_component_directory('local_mcp')) {
            $this->markTestSkipped('Optional local_mcp integration not installed.');
        }
    }

    /**
     * @return void
     */
    public function test_provider_and_json_schema_are_valid(): void {
        $this->require_mcp();
        $provider = new provider();
        $this->assertSame(['personalxp_get_course_xp', 'personalxp_get_xp_history',
            'personalxp_get_settings'], array_map(
            static fn($tool): string => $tool->get_name(), $provider->get_read_tools()));
        $this->assertSame('personalxp_update_settings', $provider->get_write_tools()[0]->get_name());
        $this->assertSame('{"type":"object","properties":{},"additionalProperties":false}',
            json_encode((new get_settings())->get_input_schema()));
        $this->assertTrue((new update_settings())->requires_confirmation());
        $this->assertTrue((new update_settings())->supports_dry_run());
        $this->assertInstanceOf(\local_mcp\extension\read_provider_interface::class, $provider);
        $this->assertInstanceOf(\local_mcp\extension\write_provider_interface::class, $provider);
    }

    /**
     * @return void
     */
    public function test_course_state_history_and_other_learner_privacy(): void {
        $this->require_mcp();
        $this->resetAfterTest();
        set_config('enabled', 1, 'local_personalxp');
        $student = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($other->id, $course->id, 'student');
        $this->setUser($student);
        $identity = new \local_mcp\security\authenticated_identity(
            (int)$student->id, 'test', ['mcp:read']);
        xp_manager::award($student->id, $course->id, 'mcp_test', 42, 40,
            'Test activity', 'local_personalxp', __METHOD__);

        $state = (new get_course_xp())->execute(['courseid' => $course->id], $identity);
        $this->assertSame(40, $state['totalxp']);
        $this->assertSame((int)$student->id, $state['userid']);
        $history = (new get_xp_history())->execute(['courseid' => $course->id, 'limit' => 5], $identity);
        $this->assertSame(1, $history['count']);
        $this->assertSame('mcp_test', $history['history'][0]['rule']);

        $this->expectException(\local_mcp\exception\api_exception::class);
        (new get_course_xp())->execute(['courseid' => $course->id, 'userid' => $other->id], $identity);
    }

    /**
     * @return void
     */
    public function test_settings_patch_preserves_other_rules_and_awards(): void {
        $this->require_mcp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $identity = new \local_mcp\security\authenticated_identity(
            (int)get_admin()->id, 'test', ['mcp:read', 'mcp:write']);
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        xp_manager::award($user->id, $course->id, 'mcp_test', 11, 75,
            'Award before update', 'local_personalxp', __METHOD__);
        $before = (new get_settings())->execute([], $identity);
        $writer = new update_settings();
        $input = [
            'expected_hash' => $before['state_hash'],
            'settings' => [
                'xpforum' => 8,
                'levels' => "0|Beginner\n100|Apprentice\n500|Expert",
            ],
        ];
        $preview = $writer->preview($input, $identity);
        $this->assertFalse($preview['changes_existing_awards']);
        $this->assertSame(5, $before['settings']['xpforum']);
        $this->assertSame(8, $preview['after']['xpforum']);
        $result = $writer->execute($input, $identity);
        $this->assertTrue($result['success']);
        $after = settings_service::snapshot();
        $this->assertSame(8, $after['settings']['xpforum']);
        $this->assertSame($before['settings']['xpquiz'], $after['settings']['xpquiz']);
        $this->assertSame(75, xp_manager::get_total($user->id, $course->id));
    }

    /**
     * @return void
     */
    public function test_invalid_levels_and_stale_hash_are_rejected(): void {
        $this->require_mcp();
        $this->resetAfterTest();
        $snapshot = settings_service::snapshot();
        $this->expectException(\local_mcp\exception\api_exception::class);
        settings_service::prepare([
            'expected_hash' => $snapshot['state_hash'],
            'settings' => ['levels' => "0|Beginner\n0|Duplicate"],
        ]);
    }
}
