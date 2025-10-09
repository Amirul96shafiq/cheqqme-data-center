<x-filament-panels::page>
    {{ $this->table }}

    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('download-backup', (eventData) => {
                    try {
                        // console.log('Download event received:', eventData);
                        
                        const backupData = eventData[0].data;
                        const filename = eventData[0].filename;

                        // console.log('Filename:', filename);
                        // console.log('Backup data keys:', Object.keys(backupData));

                        const jsonString = JSON.stringify(backupData, null, 2);
                        const blob = new Blob([jsonString], { type: 'application/json' });

                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        URL.revokeObjectURL(link.href);
                        // console.log('Download completed successfully');
                    } catch (error) {
                        console.error('Download failed:', error);
                    }
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
