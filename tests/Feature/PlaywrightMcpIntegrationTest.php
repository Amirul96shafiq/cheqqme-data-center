<?php

namespace Tests\Feature;

use App\Services\PlaywrightMcpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaywrightMcpIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected PlaywrightMcpService $playwrightService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->playwrightService = app(PlaywrightMcpService::class);
    }

    /** @test */
    public function it_can_check_playwright_mcp_service_status()
    {
        $response = $this->getJson('/api/playwright/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'available',
                'timestamp',
                'service',
            ]);
    }

    /** @test */
    public function it_can_test_filament_panel_integration()
    {
        $response = $this->postJson('/api/playwright/test-filament', [
            'base_url' => config('app.url'),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'tests' => [
                    'login_form',
                    'email_input',
                    'password_input',
                ],
            ]);
    }

    /** @test */
    public function it_can_test_action_board_integration()
    {
        $response = $this->postJson('/api/playwright/test-action-board', [
            'base_url' => config('app.url'),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'tests' => [
                    'kanban_board',
                    'task_cards',
                    'columns',
                ],
            ]);
    }

    /** @test */
    public function it_can_test_api_endpoints()
    {
        $response = $this->postJson('/api/playwright/test-api', [
            'url' => config('app.url') . '/api/users',
            'method' => 'GET',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status',
            ]);
    }

    /** @test */
    public function it_can_generate_comprehensive_test_report()
    {
        $response = $this->postJson('/api/playwright/test-report', [
            'base_url' => config('app.url'),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'report' => [
                    'timestamp',
                    'base_url',
                    'tests',
                ],
            ]);
    }

    /** @test */
    public function it_can_test_boost_integration()
    {
        $response = $this->postJson('/api/playwright/test-boost-integration', [
            'base_url' => config('app.url'),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'integration' => [
                    'laravel_boost' => [
                        'enabled',
                        'browser_logs_watcher',
                    ],
                    'playwright_mcp' => [
                        'available',
                        'base_url',
                    ],
                    'application_tests',
                ],
                'timestamp',
            ]);
    }

    /** @test */
    public function it_validates_screenshot_request_parameters()
    {
        $response = $this->postJson('/api/playwright/screenshot', [
            'url' => 'invalid-url',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    /** @test */
    public function it_validates_extract_data_request_parameters()
    {
        $response = $this->postJson('/api/playwright/extract-data', [
            'url' => 'https://example.com',
            'selectors' => [
                ['name' => 'title', 'selector' => 'h1'],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'url',
                'timestamp',
            ]);
    }

    /** @test */
    public function it_validates_test_url_request_parameters()
    {
        $response = $this->postJson('/api/playwright/test-url', [
            'url' => 'invalid-url',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    /** @test */
    public function playwright_service_can_be_resolved_from_container()
    {
        $service = app(PlaywrightMcpService::class);

        $this->assertInstanceOf(PlaywrightMcpService::class, $service);
    }

    /** @test */
    public function playwright_service_handles_unavailable_server_gracefully()
    {
        // Mock the service to return unavailable
        $this->mock(PlaywrightMcpService::class, function ($mock) {
            $mock->shouldReceive('isAvailable')->andReturn(false);
        });

        $response = $this->getJson('/api/playwright/status');

        $response->assertStatus(200)
            ->assertJson([
                'available' => false,
                'service' => 'Playwright MCP',
            ]);
    }
}
