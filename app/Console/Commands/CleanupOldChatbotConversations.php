<?php

namespace App\Console\Commands;

use App\Models\ChatbotConversation;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupOldChatbotConversations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatbot:cleanup
                            {--days=90 : Number of days to keep conversations (default: 90)}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old chatbot conversations to free up database space';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Cleaning up chatbot conversations older than {$days} days ({$cutoffDate->format('Y-m-d')})");

        // Get conversations to be deleted
        $conversationsToDelete = ChatbotConversation::where('last_activity', '<', $cutoffDate);

        $totalCount = $conversationsToDelete->count();

        if ($totalCount === 0) {
            $this->info('No old conversations found to delete.');
            return 0;
        }

        $this->info("Found {$totalCount} conversations to delete.");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No conversations will be actually deleted.');

            // Show some sample conversations
            $samples = $conversationsToDelete->limit(5)->get(['id', 'conversation_id', 'title', 'last_activity']);
            if ($samples->count() > 0) {
                $this->table(
                    ['ID', 'Conversation ID', 'Title', 'Last Activity'],
                    $samples->map(function ($conv) {
                        return [
                            $conv->id,
                            $conv->conversation_id,
                            $conv->title ?: 'Untitled',
                            $conv->last_activity->format('Y-m-d H:i:s')
                        ];
                    })
                );
            }
        } else {
            // Ask for confirmation
            if (!$this->confirm("Are you sure you want to delete {$totalCount} conversations?")) {
                $this->info('Operation cancelled.');
                return 0;
            }

            // Perform the deletion
            $deletedCount = $conversationsToDelete->delete();

            $this->info("Successfully deleted {$deletedCount} conversations.");
        }

        // Show some statistics
        $this->showStats();

        return 0;
    }

    /**
     * Show conversation statistics
     */
    private function showStats()
    {
        $this->info("\nConversation Statistics:");

        $totalConversations = ChatbotConversation::count();
        $activeConversations = ChatbotConversation::active()->count();
        $inactiveConversations = ChatbotConversation::where('is_active', false)->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Conversations', $totalConversations],
                ['Active Conversations', $activeConversations],
                ['Inactive Conversations', $inactiveConversations],
            ]
        );

        // Show oldest and newest conversations
        $oldest = ChatbotConversation::orderBy('created_at', 'asc')->first(['created_at']);
        $newest = ChatbotConversation::orderBy('created_at', 'desc')->first(['created_at']);

        if ($oldest && $newest) {
            $this->info("Date Range: {$oldest->created_at->format('Y-m-d')} to {$newest->created_at->format('Y-m-d')}");
        }
    }
}
