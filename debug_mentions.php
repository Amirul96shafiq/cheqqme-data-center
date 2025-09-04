<?php
// Debug script to test mention functionality
require_once 'vendor/autoload.php';

use App\Models\User;

// Simple test to see if users exist
$users = User::take(5)->get(['id', 'username', 'email', 'name']);

echo "Users available for mentions:\n";
foreach ($users as $user) {
    echo "- ID: {$user->id}, Username: {$user->username}, Email: {$user->email}\n";
}

echo "\nTotal users: " . User::count() . "\n";
