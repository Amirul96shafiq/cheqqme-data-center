<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueTicketRequest;
use App\Models\Project;
use App\Models\Task;

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
        $validated = $request->validated();

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Store file in public/tasks directory (same as Filament)
                // Preserve original filename like Filament does
                $path = $file->storeAs('tasks', $file->getClientOriginalName(), 'public');
                $attachments[] = $path;
            }
        }

        // Get the maximum order_column value and add 1 for the new task
        $maxOrder = Task::max('order_column') ?? 0;

        // Create the task with issue_tracker status
        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => 'issue_tracker',
            'project' => [$validated['project_id']], // Task model expects project as array
            'order_column' => $maxOrder + 1,
            'attachments' => ! empty($attachments) ? $attachments : null,
            'extra_information' => [
                'reporter_name' => $validated['name'],
                'reporter_email' => $validated['email'],
            ],
        ]);

        $project = Project::findOrFail($validated['project_id']);

        return redirect()
            ->route('issue-tracker.show', ['project' => $project->issue_tracker_code])
            ->with('success', 'Your issue has been submitted successfully. Thank you!');
    }
}
