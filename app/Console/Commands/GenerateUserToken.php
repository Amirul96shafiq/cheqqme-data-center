<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateUserToken extends Command
{
    protected $signature = 'sanctum:token-user {user_id} {--device_name=OpenAI Logs API}';

    protected $description = 'Generate a Sanctum API token for a given user';

    public function handle(): int
    {
        $userId = (int) $this->argument('user_id');
        $deviceName = $this->option('device_name') ?? 'OpenAI Logs API';

        $user = User::find($userId);
        if (! $user) {
            $this->error("User not found: {$userId}");

            return 1;
        }

        $token = $user->createToken($deviceName)->plainTextToken;
        $this->info("TOKEN for user {$userId} ({$deviceName}): ".$token);

        return 0;
    }
}
