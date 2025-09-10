<div>
    <script>
        // Set language data for drag-drop functionality
        window.dragDropLang = {
            largeFileTitle: @json(__('document.drag_drop.large_file_title')),
            largeFileMessage: @json(__('document.drag_drop.large_file_message')),
            fileTooLarge: @json(__('document.drag_drop.file_too_large')),
            unsupportedFileType: @json(__('document.drag_drop.unsupported_file_type')),
            drop_file_to_upload_document: @json(__('document.drag_drop.drop_file_to_upload_document'))
        };
    </script>
    @vite('resources/js/document-upload-handler.js')
</div>