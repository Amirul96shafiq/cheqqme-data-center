<?php

// Simple test script to verify chatbot functionality
require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing Chatbot API...\n";

    // Get first user
    $user = User::first();
    if (! $user) {
        echo "❌ No users found in database\n";
        exit(1);
    }

    echo "✅ User found: {$user->name}\n";

    // Create controller and request
    $controller = new \App\Http\Controllers\ChatbotController;
    $request = new Request;
    $request->merge([
        'message' => 'Hello, test message',
        'conversation_id' => 'test-'.time(),
    ]);

    // Authenticate user
    Auth::login($user);

    // Call chatbot
    $response = $controller->chat($request);

    echo "📡 API Response Status: {$response->getStatusCode()}\n";

    if ($response->getStatusCode() === 200) {
        $data = json_decode($response->getContent(), true);
        echo '💬 Response: '.substr($data['response'], 0, 150)."...\n";
        echo "✅ Chatbot API is working!\n";
    } else {
        $data = json_decode($response->getContent(), true);
        echo '❌ Error: '.($data['error'] ?? 'Unknown error')."\n";
    }

} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

echo "\n🎯 To test the chatbot in your browser:\n";
echo "1. Start your Laravel server: php artisan serve\n";
echo "2. Go to your app and log in\n";
echo "3. Look for the floating chat button in the bottom-right corner\n";
echo "4. Click it and try sending a message\n";
