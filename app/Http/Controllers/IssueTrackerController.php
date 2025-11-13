<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueTicketRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TemporaryFileService;
use Illuminate\Http\Request;

class IssueTrackerController extends Controller
{
    /**
     * Display the issue tracker form for a specific project.
     */
    public function show(string $project)
    {
        $projectModel = Project::where('issue_tracker_code', $project)->firstOrFail();

        return view('issue-tracker.create', [
            'project' => $projectModel,
        ]);
    }

    /**
     * Store a new issue ticket.
     */
    public function store(StoreIssueTicketRequest $request)
    {
        try {
            // Get validated data from the form request
            $validated = $request->validated();

            // Handle temporary file uploads
            $tempService = new TemporaryFileService;
            $attachments = $tempService->moveToPermanent($validated['temp_file_ids']);
        } catch (\Exception $e) {
            // If something goes wrong, redirect back with temp_file_ids preserved
            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while processing your submission. Please try again.'])
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

        // Create the task with issue_tracker status
        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => 'issue_tracker',
            'project' => [$validated['project_id']], // Task model expects project as array
            'client' => $clientId, // Auto-populate client from project
            'document' => ! empty($documents) ? $documents : null, // Auto-populate documents from project
            'important_url' => ! empty($importantUrls) ? $importantUrls : null, // Auto-populate important URLs from project
            'order_column' => $maxOrder + 1,
            'attachments' => ! empty($attachments) ? $attachments : null,
            'extra_information' => [
                [
                    'title' => 'Reporter Name',
                    'value' => $validated['name'],
                ],
                [
                    'title' => 'Communication Preference',
                    'value' => $validated['communication_preference'] === 'email' ? 'Email' : ($validated['communication_preference'] === 'whatsapp' ? 'WhatsApp' : 'Both (Email & WhatsApp)'),
                ],
                ...($validated['communication_preference'] === 'both' ? [
                    [
                        'title' => 'Reporter Email',
                        'value' => $validated['email'] ?? '',
                    ],
                    [
                        'title' => 'Reporter WhatsApp',
                        'value' => $validated['whatsapp_number'] ?? '',
                    ],
                ] : [
                    [
                        'title' => $validated['communication_preference'] === 'email' ? 'Reporter Email' : 'Reporter WhatsApp',
                        'value' => $validated['communication_preference'] === 'email'
                            ? ($validated['email'] ?? '')
                            : ($validated['whatsapp_number'] ?? ''),
                    ],
                ]),
                [
                    'title' => 'Submitted on',
                    'value' => now()->format('j/n/y, h:i A'),
                ],
            ],
        ]);

        // Refresh task to get the generated tracking token
        $task->refresh();

        // Auto-transfer reporter information to client's staff_information
        $client = $project->client;
        if ($client) {
            $reporterName = $validated['name'];
            $reporterEmail = in_array($validated['communication_preference'], ['email', 'both'])
                ? ($validated['email'] ?? null)
                : null;
            $reporterWhatsapp = in_array($validated['communication_preference'], ['whatsapp', 'both'])
                ? ($validated['whatsapp_number'] ?? null)
                : null;

            // Normalize phone number to +country_code format
            $normalizedWhatsapp = static::normalizePhoneNumber($reporterWhatsapp);

            // Check for duplicates (based on normalized phone number only)
            $existingStaff = $client->staff_information ?? [];
            $isDuplicate = false;

            if ($normalizedWhatsapp) {
                foreach ($existingStaff as $staff) {
                    $existingContact = $staff['staff_contact_number'] ?? null;
                    if ($existingContact) {
                        $normalizedExisting = static::normalizePhoneNumber($existingContact);
                        if ($normalizedExisting === $normalizedWhatsapp) {
                            $isDuplicate = true;
                            break;
                        }
                    }
                }
            }

            // Add if not duplicate
            if (! $isDuplicate) {
                $existingStaff[] = [
                    'staff_name' => $reporterName,
                    'staff_email' => $reporterEmail,
                    'staff_contact_number' => $normalizedWhatsapp,
                ];

                $client->staff_information = $existingStaff;
                $client->save();
            }
        }

        return redirect()
            ->route('issue-tracker.show', ['project' => $project->issue_tracker_code])
            ->with('success', 'Your issue has been submitted successfully. Thank you!')
            ->with('tracking_token', $task->tracking_token);
    }

    /**
     * Display the status of an issue ticket by tracking token.
     */
    public function status(string $token)
    {
        $task = Task::where('tracking_token', $token)->firstOrFail();

        // Get project information
        $project = null;
        if (! empty($task->project) && is_array($task->project) && ! empty($task->project[0])) {
            $project = Project::find($task->project[0]);
        }

        return view('issue-tracker.status', [
            'task' => $task,
            'project' => $project,
        ]);
    }

    /**
     * Get tracking tokens count for a project (API endpoint).
     */
    public function getTrackingTokensCount(string $projectCode)
    {
        $project = Project::where('issue_tracker_code', $projectCode)->firstOrFail();

        return response()->json([
            'count' => $project->tracking_tokens_count,
        ]);
    }

    /**
     * Get tracking tokens for a project (API endpoint).
     */
    public function getTrackingTokens(string $projectCode)
    {
        $project = Project::where('issue_tracker_code', $projectCode)->firstOrFail();

        return response()->json([
            'project' => [
                'title' => $project->title,
                'code' => $project->issue_tracker_code,
            ],
            'tracking_tokens' => $project->getTrackingTokens(),
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
     * Normalize phone number to +country_code format (e.g., +60123456789)
     */
    private static function normalizePhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Extract digits only
        $digits = preg_replace('/\D+/', '', $phone);

        if (empty($digits)) {
            return null;
        }

        // Detect country code and normalize
        $countryCodes = [
            '60' => 'MY', // Malaysia
            '62' => 'ID', // Indonesia
            '65' => 'SG', // Singapore
            '63' => 'PH', // Philippines
            '1' => 'US',  // USA
        ];

        // Check if already starts with country code
        foreach ($countryCodes as $code => $country) {
            if (str_starts_with($digits, $code)) {
                return '+'.$digits;
            }
        }

        // If starts with 0, remove it and add default country code (60 for MY)
        if (str_starts_with($digits, '0')) {
            $digits = ltrim($digits, '0');

            return '+60'.$digits;
        }

        // Default to Malaysia country code if no match
        return '+60'.$digits;
    }
}
