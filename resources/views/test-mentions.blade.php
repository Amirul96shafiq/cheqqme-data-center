<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Mentions</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Test User Mention Dropdown</h1>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <p class="mb-4">Type "@" in the comment field below to test the mention dropdown:</p>
            
            <!-- Include both components as siblings -->
            <livewire:task-comments :task-id="{{ $task->id }}" wire:key="task-comments-{{ $task->id }}" />
            <livewire:user-mention-dropdown />
        </div>
    </div>
    
    @livewireScripts
</body>
</html>
