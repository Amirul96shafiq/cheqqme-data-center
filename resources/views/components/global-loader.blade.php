<div id="global-loader" class="fixed inset-0 z-[9999] flex items-center justify-center bg-white dark:bg-gray-900 transition-opacity duration-500 opacity-0 pointer-events-none"></div>
    <!-- Loader visual here if needed -->
<script>
    // Hide body content immediately (before DOMContentLoaded)
    document.body.classList.add('loader-active');
    document.addEventListener('DOMContentLoaded', function() {
        const loader = document.getElementById('global-loader');
        if (loader) {
            // Fade in loader
            loader.classList.remove('pointer-events-none');
            setTimeout(() => loader.classList.remove('opacity-0'), 10);
            // Fade out loader after 800ms
            setTimeout(() => {
                loader.classList.add('opacity-0', 'pointer-events-none');
                document.body.classList.remove('loader-active'); // Show content
                document.body.classList.add('content-fade-in');
            }, 800);
            // Remove loader from DOM after fade
            setTimeout(() => loader.remove(), 1200);
        } else {
            // Fallback: always show content if loader missing
            document.body.classList.remove('loader-active');
        }
    });
</script>
<style>
#global-loader {
    opacity: 1;
}
#global-loader.opacity-0 {
    opacity: 0;
    transition: opacity 0.5s;
}
body.loader-active > *:not(#global-loader) {
    opacity: 0 !important;
    pointer-events: none !important;
    transition: opacity 0.2s;
}
body.content-fade-in > *:not(#global-loader) {
    opacity: 1 !important;
    transition: opacity 0.5s;
}
</style>
<noscript><style>body > *:not(#global-loader){opacity:1!important;pointer-events:auto!important;}</style></noscript>
