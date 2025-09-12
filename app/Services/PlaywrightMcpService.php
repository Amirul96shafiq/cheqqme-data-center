<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlaywrightMcpService
{
  protected string $baseUrl;

  protected string $timeout;

  protected bool $enabled;

  public function __construct()
  {
    $this->baseUrl = Config::get('playwright-mcp.base_url', 'http://localhost:3000');
    $this->timeout = Config::get('playwright-mcp.timeout', 30);
    $this->enabled = Config::get('playwright-mcp.enabled', false);
  }

  /**
   * Check if Playwright MCP server is available
   */
  public function isAvailable(): bool
  {
    if (!$this->enabled) {
      return false;
    }

    try {
      $response = Http::timeout(5)->get($this->baseUrl . '/health');

      return $response->successful();
    } catch (\Exception $e) {
      Log::warning('Playwright MCP server not available', [
        'error' => $e->getMessage(),
        'url' => $this->baseUrl,
      ]);

      return false;
    }
  }

  /**
   * Take a screenshot of a URL
   */
  public function takeScreenshot(string $url, array $options = []): ?string
  {
    if (!$this->isAvailable()) {
      return null;
    }

    try {
      $response = Http::timeout($this->timeout)
        ->post($this->baseUrl . '/screenshot', [
          'url' => $url,
          'options' => array_merge([
            'fullPage' => true,
            'type' => 'png',
          ], $options),
        ]);

      if ($response->successful()) {
        return $response->body();
      }
    } catch (\Exception $e) {
      Log::error('Failed to take screenshot', [
        'url' => $url,
        'error' => $e->getMessage(),
      ]);
    }

    return null;
  }

  /**
   * Test a URL for accessibility and functionality
   */
  public function testUrl(string $url, array $tests = []): array
  {
    if (!$this->isAvailable()) {
      return [
        'success' => false,
        'error' => 'Playwright MCP server not available',
      ];
    }

    try {
      $response = Http::timeout($this->timeout)
        ->post($this->baseUrl . '/test', [
          'url' => $url,
          'tests' => $tests,
        ]);

      if ($response->successful()) {
        return $response->json();
      }
    } catch (\Exception $e) {
      Log::error('Failed to test URL', [
        'url' => $url,
        'error' => $e->getMessage(),
      ]);
    }

    return [
      'success' => false,
      'error' => 'Request failed',
    ];
  }

  /**
   * Extract data from a webpage
   */
  public function extractData(string $url, array $selectors = []): array
  {
    if (!$this->isAvailable()) {
      return [];
    }

    try {
      $response = Http::timeout($this->timeout)
        ->post($this->baseUrl . '/extract', [
          'url' => $url,
          'selectors' => $selectors,
        ]);

      if ($response->successful()) {
        return $response->json();
      }
    } catch (\Exception $e) {
      Log::error('Failed to extract data', [
        'url' => $url,
        'error' => $e->getMessage(),
      ]);
    }

    return [];
  }

  /**
   * Test Filament admin panel functionality
   */
  public function testFilamentPanel(string $baseUrl, string $loginUrl = '/admin/login'): array
  {
    $tests = [
      'login_form' => [
        'selector' => 'form[action*="login"]',
        'description' => 'Login form should be present',
      ],
      'email_input' => [
        'selector' => 'input[type="email"]',
        'description' => 'Email input should be present',
      ],
      'password_input' => [
        'selector' => 'input[type="password"]',
        'description' => 'Password input should be present',
      ],
    ];

    return $this->testUrl($baseUrl . $loginUrl, $tests);
  }

  /**
   * Test Action Board functionality
   */
  public function testActionBoard(string $baseUrl): array
  {
    $tests = [
      'kanban_board' => [
        'selector' => '[data-flowforge-kanban]',
        'description' => 'Kanban board should be present',
      ],
      'task_cards' => [
        'selector' => '[data-flowforge-card]',
        'description' => 'Task cards should be present',
      ],
      'columns' => [
        'selector' => '[data-flowforge-column]',
        'description' => 'Kanban columns should be present',
      ],
    ];

    return $this->testUrl($baseUrl . '/admin/action-board', $tests);
  }

  /**
   * Test API endpoints
   */
  public function testApiEndpoint(string $url, string $method = 'GET', array $headers = []): array
  {
    if (!$this->isAvailable()) {
      return [
        'success' => false,
        'error' => 'Playwright MCP server not available',
      ];
    }

    try {
      $response = Http::timeout($this->timeout)
            ->withHeaders($headers)
        ->{strtolower($method)}($url);

      return [
        'success' => $response->successful(),
        'status' => $response->status(),
        'headers' => $response->headers(),
        'body' => $response->body(),
      ];
    } catch (\Exception $e) {
      Log::error('Failed to test API endpoint', [
        'url' => $url,
        'method' => $method,
        'error' => $e->getMessage(),
      ]);

      return [
        'success' => false,
        'error' => $e->getMessage(),
      ];
    }
  }

  /**
   * Generate a comprehensive test report for the application
   */
  public function generateTestReport(string $baseUrl): array
  {
    $report = [
      'timestamp' => now()->toISOString(),
      'base_url' => $baseUrl,
      'tests' => [],
    ];

    // Test main pages
    $pages = [
      'admin_login' => '/admin/login',
      'admin_dashboard' => '/admin',
      'action_board' => '/admin/action-board',
      'clients' => '/admin/clients',
      'projects' => '/admin/projects',
      'tasks' => '/admin/tasks',
    ];

    foreach ($pages as $name => $path) {
      $report['tests'][$name] = $this->testUrl($baseUrl . $path);
    }

    // Test API endpoints
    $apiEndpoints = [
      'api_users' => '/api/users',
      'api_tasks' => '/api/tasks',
      'api_projects' => '/api/projects',
      'api_clients' => '/api/clients',
    ];

    foreach ($apiEndpoints as $name => $path) {
      $report['tests'][$name] = $this->testApiEndpoint($baseUrl . $path);
    }

    // Test Filament panel specifically
    $report['tests']['filament_panel'] = $this->testFilamentPanel($baseUrl);
    $report['tests']['action_board'] = $this->testActionBoard($baseUrl);

    return $report;
  }
}


