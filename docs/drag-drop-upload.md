# Drag & Drop File Upload Feature

## Overview

This feature enables users to drag and drop files from anywhere in the admin panel to automatically create a new document. The system will redirect to the document creation page and auto-fill the form with the dropped file.

## How It Works

1. **File Detection**: When a user drags a file over any admin page, a blue overlay appears
2. **File Validation**: Only supported file types are accepted (PDF, Word, Excel, images, text)
3. **Storage**: The file is converted to base64 and stored in `sessionStorage`
4. **Redirect**: User is redirected to `/admin/documents/create`
5. **Auto-fill**: The form is automatically filled with:
    - Document title (from file name)
    - Document type (set to "Internal")
    - File upload (starts uploading the dropped file)

## Supported File Types

-   PDF documents (`application/pdf`)
-   Microsoft Word documents (`application/msword`, `.docx`)
-   Microsoft Excel spreadsheets (`application/vnd.ms-excel`, `.xlsx`)
-   Images (`image/jpeg`, `image/png`, `image/gif`)
-   Text files (`text/plain`, `text/csv`)

## Technical Implementation

### Files Involved

-   `resources/js/drag-drop-upload.js` - Main drag & drop handler
-   `resources/views/livewire/document-upload-handler.blade.php` - Form auto-fill logic
-   `app/Livewire/DocumentUploadHandler.php` - Livewire component
-   `app/Filament/Resources/DocumentResource.php` - Form integration

### Key Components

1. **DragDropUpload Class**: Object-oriented drag event handling and file processing
2. **Modular Form Functions**: Clean, focused functions for title, document type, and file upload
3. **Robust Element Detection**: Multiple selectors with aggressive fallback search
4. **Smart Event Handling**: Comprehensive event triggering for Filament/Livewire compatibility
5. **Retry Mechanisms**: Multiple timing attempts for async form loading

## Usage

Simply drag any supported file from your computer onto any admin page. The system will automatically:

-   Show a visual overlay
-   Validate the file type
-   Redirect to document creation
-   Pre-fill the form
-   Start the file upload

## Error Handling

-   Invalid file types show an alert message
-   Failed file processing shows an error alert
-   Console logging provides debugging information
-   Fallback mechanisms ensure reliability

## Browser Compatibility

-   Modern browsers with FileReader API support
-   Drag and Drop API support required
-   sessionStorage support required
