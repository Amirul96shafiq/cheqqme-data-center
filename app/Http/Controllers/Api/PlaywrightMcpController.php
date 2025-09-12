<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PlaywrightMcpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlaywrightMcpController extends Controller
{
    protected PlaywrightMcpService $playwrightService;

    public function __construct(PlaywrightMcpService $playwrightService)
    {
        $this->playwrightService = $playwrightService;
    }

    /**
     * Check if Playwright MCP server is available
     */
    public function status(): JsonResponse
    {
        $isAvailable = $this->playwrightService->isAvailable();

        return response()->json([
            'available' => $isAvailable,
            'timestamp' => now()->toISOString(),
            'service' => 'Playwright MCP',
        ]);
    }

    /**
     * Take a screenshot of a URL
     */
    public function screenshot(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'fullPage' => 'boolean',
            'type' => 'in:png,jpeg,webp',
            'quality' => 'integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $options = $request->only(['fullPage', 'type', 'quality']);
        $screenshot = $this->playwrightService->takeScreenshot($request->url, $options);

        if ($screenshot === null) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to take screenshot',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'screenshot' => base64_encode($screenshot),
            'url' => $request->url,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Test a URL for functionality
     */
    public function testUrl(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'tests' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->playwrightService->testUrl(
            $request->url,
            $request->get('tests', [])
        );

        return response()->json($result);
    }

    /**
     * Extract data from a webpage
     */
    public function extractData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'selectors' => 'required|array',
            'selectors.*.name' => 'required|string',
            'selectors.*.selector' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $this->playwrightService->extractData(
            $request->url,
            $request->selectors
        );

        return response()->json([
            'success' => true,
            'data' => $data,
            'url' => $request->url,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Test Filament admin panel
     */
    public function testFilamentPanel(Request $request): JsonResponse
    {
        $baseUrl = $request->get('base_url', config('app.url'));
        $loginUrl = $request->get('login_url', '/admin/login');

        $result = $this->playwrightService->testFilamentPanel($baseUrl, $loginUrl);

        return response()->json($result);
    }

    /**
     * Test Action Board functionality
     */
    public function testActionBoard(Request $request): JsonResponse
    {
        $baseUrl = $request->get('base_url', config('app.url'));

        $result = $this->playwrightService->testActionBoard($baseUrl);

        return response()->json($result);
    }

    /**
     * Test API endpoints
     */
    public function testApiEndpoint(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'method' => 'in:GET,POST,PUT,PATCH,DELETE',
            'headers' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->playwrightService->testApiEndpoint(
            $request->url,
            $request->get('method', 'GET'),
            $request->get('headers', [])
        );

        return response()->json($result);
    }

    /**
     * Generate comprehensive test report
     */
    public function generateTestReport(Request $request): JsonResponse
    {
        $baseUrl = $request->get('base_url', config('app.url'));

        $report = $this->playwrightService->generateTestReport($baseUrl);

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * Test integration with Laravel Boost
     */
    public function testBoostIntegration(Request $request): JsonResponse
    {
        $baseUrl = $request->get('base_url', config('app.url'));

        // Test if Laravel Boost is working
        $boostEnabled = config('boost.enabled', false);
        $boostBrowserLogs = config('boost.browser_logs_watcher', false);

        // Test Playwright MCP availability
        $playwrightAvailable = $this->playwrightService->isAvailable();

        // Test basic application functionality
        $appTests = $this->playwrightService->generateTestReport($baseUrl);

        return response()->json([
            'success' => true,
            'integration' => [
                'laravel_boost' => [
                    'enabled' => $boostEnabled,
                    'browser_logs_watcher' => $boostBrowserLogs,
                ],
                'playwright_mcp' => [
                    'available' => $playwrightAvailable,
                    'base_url' => config('playwright-mcp.base_url'),
                ],
                'application_tests' => $appTests,
            ],
            'timestamp' => now()->toISOString(),
        ]);
    }
}
