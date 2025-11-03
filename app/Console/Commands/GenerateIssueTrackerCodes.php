<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;

class GenerateIssueTrackerCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:generate-issue-tracker-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate issue tracker codes for projects that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating issue tracker codes for projects...');

        $projects = Project::whereNull('issue_tracker_code')->get();

        if ($projects->isEmpty()) {
            $this->info('✅ All projects already have issue tracker codes.');

            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($projects->count());
        $bar->start();

        $generated = 0;
        foreach ($projects as $project) {
            $project->issue_tracker_code = Project::generateIssueTrackerCode();
            $project->save();
            $generated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Successfully generated {$generated} issue tracker code(s).");

        return Command::SUCCESS;
    }
}
