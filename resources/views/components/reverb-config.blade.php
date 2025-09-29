@push('scripts')
<script>
    // Make Reverb configuration available to frontend JavaScript
    window.reverbConfig = {
        key: '{{ config('broadcasting.connections.reverb.key') }}',
        host: '{{ config('broadcasting.connections.reverb.options.host') }}',
        port: {{ config('broadcasting.connections.reverb.options.port') }},
        scheme: '{{ config('broadcasting.connections.reverb.options.scheme') }}',
        useTLS: {{ config('broadcasting.connections.reverb.options.useTLS') ? 'true' : 'false' }},
    };

    // Make status configuration available to frontend JavaScript
    window.statusConfig = @json(\App\Services\OnlineStatus\StatusConfig::getJavaScriptConfig());

    // Make current user available to frontend JavaScript
    @auth
        window.currentUser = {
            id: {{ auth()->id() }},
            name: '{{ auth()->user()->name }}',
            email: '{{ auth()->user()->email }}',
            status: '{{ auth()->user()->online_status ?? 'online' }}',
            avatar: '{{ auth()->user()->avatar_url ?? null }}',
        };
    @endauth

    // console.log('Reverb configuration loaded:', window.reverbConfig);
    // console.log('Status configuration loaded:', window.statusConfig);
    @auth
        // console.log('Current user loaded:', window.currentUser);
    @endauth
</script>
@endpush
