<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Services\TemporaryFileService;
use Illuminate\Http\Request;

abstract class BaseTrackerController extends Controller
{
    /**
     * The status to assign to tracker tasks (e.g., 'issue_tracker', 'wishlist')
     */
    protected string $trackerStatus;

    /**
     * The route name prefix for this tracker (e.g., 'issue-tracker', 'wishlist-tracker')
     */
    protected string $routePrefix;

    /**
     * The code field name in projects table (e.g., 'issue_tracker_code', 'wishlist_tracker_code')
     */
    protected string $projectCodeField;

    /**
     * The token prefix for tracking tokens (e.g., 'CHEQQ-ISU-', 'CHEQQ-WSH-')
     */
    protected string $tokenPrefix;

    /**
     * The view directory name (e.g., 'issue-tracker', 'wishlist-tracker')
     */
    protected string $viewDirectory;

    /**
     * Display the tracker form for a specific project.
     */
    public function show(string $project)
    {
        $projectModel = Project::where($this->projectCodeField, $project)->firstOrFail();

        return view($this->viewDirectory.'.create', [
            'project' => $projectModel,
        ]);
    }

    /**
     * Store a new tracker ticket.
     */
    public function store(Request $request)
    {
        try {
            // Get the appropriate form request class
            $formRequestClass = $this->getFormRequestClass();

            // Create a form request instance to get validation rules and messages
            $formRequest = new $formRequestClass;

            // Get validation rules and messages
            $rules = $formRequest->rules();
            $messages = $formRequest->messages();

            // Validate the request using Laravel's validator
            $validated = $request->validate($rules, $messages);

            // Handle temporary file uploads
            $tempService = new TemporaryFileService;
            $attachments = ! empty($validated['temp_file_ids'])
                ? $tempService->moveToPermanent($validated['temp_file_ids'])
                : [];
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors specifically
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput($request->all())
                ->with('temp_file_ids', $request->input('temp_file_ids', []));
        } catch (\Exception $e) {
            // For debugging, show the actual error
            $errorMessage = 'An error occurred while processing your submission. Please try again.';
            if (config('app.debug')) {
                $errorMessage .= ' Debug: '.$e->getMessage();
            }

            // Log the actual error for debugging
            \Log::error('Issue/Wishlist submission error: '.$e->getMessage(), [
                'request_data' => $request->except(['_token']),
                'user_id' => auth()->id(),
                'exception' => $e->getTraceAsString(),
            ]);

            // If something goes wrong, redirect back with temp_file_ids preserved
            return redirect()->back()
                ->withErrors(['error' => $errorMessage])
                ->withInput($request->all())
                ->with('temp_file_ids', $request->input('temp_file_ids', []));
        }

        // Get the maximum order_column value and add 1 for the new task
        $maxOrder = Task::max('order_column') ?? 0;

        $project = Project::findOrFail($validated['project_id']);

        // Auto-populate resources from project
        $clientId = $project->client_id;

        // Get all documents for the project
        $documents = \App\Models\Document::where('project_id', $project->id)
            ->withTrashed()
            ->pluck('id')
            ->toArray();

        // Get all important URLs for the project
        $importantUrls = \App\Models\ImportantUrl::where('project_id', $project->id)
            ->withTrashed()
            ->pluck('id')
            ->toArray();

        // Create the task with tracker status
        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => $this->trackerStatus,
            'project' => [$validated['project_id']], // Task model expects project as array
            'client' => $clientId, // Auto-populate client from project
            'document' => ! empty($documents) ? $documents : null, // Auto-populate documents from project
            'important_url' => ! empty($importantUrls) ? $importantUrls : null, // Auto-populate important URLs from project
            'order_column' => $maxOrder + 1,
            'attachments' => ! empty($attachments) ? $attachments : null,
            'extra_information' => $this->buildExtraInformation($validated, $request),
        ]);

        // Refresh task to get the generated tracking token
        $task->refresh();

        // Handle reporter information
        $this->handleReporterInformation($project, $validated, $request);

        return redirect()
            ->route($this->routePrefix.'.show', ['project' => $project->{$this->projectCodeField}])
            ->with('success', $this->getSuccessMessage())
            ->with('tracking_token', $task->tracking_token);
    }

    /**
     * Display the status of a tracker ticket by tracking token.
     */
    public function status(string $token)
    {
        $task = Task::with('updatedBy')->where('tracking_token', $token)->firstOrFail();

        // Get project information
        $project = null;
        if (! empty($task->project) && is_array($task->project) && ! empty($task->project[0])) {
            $project = Project::find($task->project[0]);
        }

        return view($this->viewDirectory.'.status', [
            'task' => $task,
            'project' => $project,
        ]);
    }

    /**
     * Get tracking tokens count for a project (API endpoint).
     */
    public function getTrackingTokensCount(string $projectCode)
    {
        $project = Project::where($this->projectCodeField, $projectCode)->firstOrFail();

        $count = $this->getTokensQuery($project->id)->count();

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Get tracking tokens for a project (API endpoint).
     */
    public function getTrackingTokens(string $projectCode)
    {
        $project = Project::where($this->projectCodeField, $projectCode)->firstOrFail();

        return response()->json([
            'project' => [
                'title' => $project->title,
                'code' => $project->{$this->projectCodeField},
            ],
            'tracking_tokens' => $this->getTokensQuery($project->id)
                ->orderBy('created_at', 'desc')
                ->select(['tracking_token', 'title', 'status', 'created_at'])
                ->get()
                ->map(function ($task) {
                    return [
                        'token' => $task->tracking_token,
                        'title' => $task->title,
                        'status' => $task->status,
                        'created_at' => $task->created_at->format('m/d/Y, g:i A'),
                        'url' => route($this->routePrefix.'.status', ['token' => $task->tracking_token]),
                    ];
                }),
        ]);
    }

    /**
     * Upload a file temporarily via AJAX.
     */
    public function uploadTemporaryFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:8192|mimes:jpg,jpeg,png,pdf,mp4',
        ]);

        $file = $request->file('file');
        $tempService = new TemporaryFileService;

        try {
            $tempFile = $tempService->storeTemporarily($file);

            return response()->json([
                'success' => true,
                'temp_file' => $tempFile,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to upload file: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get temporary files for the current session.
     */
    public function getTemporaryFiles(Request $request)
    {
        $tempService = new TemporaryFileService;

        try {
            $tempFiles = $tempService->getSessionFiles();

            return response()->json([
                'success' => true,
                'temp_files' => $tempFiles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get temporary files: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the form request class for validation.
     */
    abstract protected function getFormRequestClass(): string;

    /**
     * Build extra information array for the task.
     */
    abstract protected function buildExtraInformation(array $validated, Request $request): array;

    /**
     * Handle reporter information processing.
     */
    abstract protected function handleReporterInformation(Project $project, array $validated, Request $request): void;

    /**
     * Get success message after submission.
     */
    abstract protected function getSuccessMessage(): string;

    /**
     * Get tokens query for this tracker type.
     */
    protected function getTokensQuery(int $projectId)
    {
        return Task::whereNotNull('tracking_token')
            ->where('tracking_token', 'like', $this->tokenPrefix.'%')
            ->where(function ($query) use ($projectId) {
                $query
                    ->whereJsonContains('project', $projectId)
                    ->orWhereJsonContains('project', (string) $projectId)
                    ->orWhere('project', 'like', '%"'.$projectId.'"%')
                    ->orWhere('project', 'like', '%['.$projectId.']%');
            });
    }

    /**
     * Normalize phone number to digits only (consistent with ClientResource)
     */
    protected static function normalizePhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Extract digits only (consistent with ClientResource)
        $digits = preg_replace('/\D+/', '', $phone);

        if (empty($digits)) {
            return null;
        }

        // Return digits only (matching ClientResource storage format)
        return $digits;
    }

    /**
     * Detect country code from phone number digits
     */
    protected static function detectCountryCode(string $digits): string
    {
        $countryCodes = [
            '60' => 'MY', // Malaysia (+60)
            '62' => 'ID', // Indonesia (+62)
            '65' => 'SG', // Singapore (+65)
            '63' => 'PH', // Philippines (+63)
            '1' => 'US', // USA (+1)
            '86' => 'CN', // China (+86)
            '91' => 'IN', // India (+91)
            '44' => 'GB', // UK (+44)
            '61' => 'AU', // Australia (+61)
        ];

        foreach ($countryCodes as $code => $country) {
            if (str_starts_with($digits, $code)) {
                return $country;
            }
        }

        // Default to Malaysia if no match
        return 'MY';
    }
}
