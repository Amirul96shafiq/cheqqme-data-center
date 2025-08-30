<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix existing projects with invalid status values
        // Convert to the proper status values used in ProjectResource
        $projects = \App\Models\Project::all();

        foreach ($projects as $project) {
            $currentStatus = $project->status;

            // Map old status values to new ones
            $statusMap = [
                'active' => 'In Progress',
                'completed' => 'Completed',
                'on_hold' => 'Planning',
                'Planning' => 'Planning', // Already correct
                'In Progress' => 'In Progress', // Already correct
                'Completed' => 'Completed', // Already correct
            ];

            if (isset($statusMap[$currentStatus])) {
                $project->status = $statusMap[$currentStatus];
                $project->save();
            } else {
                // For any unknown status, default to Planning
                $project->status = 'Planning';
                $project->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
