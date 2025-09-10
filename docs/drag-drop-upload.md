# Drag & Drop File Upload Feature

## Overview

This feature enables users to drag and drop files from anywhere in the admin panel to automatically create a new document. The system will redirect to the document creation page and auto-fill the form with the dropped file.

## How It Works

1. **Page Detection**: Checks if the current page has Filament upload fields
    - **If Filament upload fields found**: Disables global drag-drop, allows native Filament drag-drop
    - **If no Filament upload fields**: Enables global drag-drop functionality
2. **File Detection**: When a user drags a file over any admin page, a blue overlay appears
3. **File Validation**: Only supported file types are accepted (PDF, Word, Excel, PowerPoint, images, videos, CSV)
4. **Size Check**: Files are checked against the 20MB limit
5. **Storage Strategy**:
    - **Small files (≤ 5MB)**: Converted to base64 and stored in `sessionStorage` for auto-upload
    - **Large files (5MB - 20MB)**: Only metadata stored, user must manually upload
    - **Oversized files (> 20MB)**: Blocked with browser popup
6. **Redirect**: User is redirected to `/admin/documents/create`
7. **Auto-fill**: The form is automatically filled with:
    - Document title (from file name)
    - Document type (set to "Internal")
    - File upload (for small files only)
    - Notification (for large files)

## Supported File Types

-   PDF documents (`application/pdf`)
-   Images (`image/jpeg`, `image/png`)
-   Microsoft Word documents (`application/msword`, `.docx`)
-   Microsoft Excel spreadsheets (`application/vnd.ms-excel`, `.xlsx`)
-   Microsoft PowerPoint presentations (`application/vnd.ms-powerpoint`, `.pptx`)
-   CSV files (`text/csv`)
-   Video files (`video/mp4`)

## File Size Limits

-   **Maximum file size**: 20MB (as per DocumentResource configuration)
-   **Small files (≤ 5MB)**: Fully automated upload via drag-and-drop
-   **Large files (5MB - 20MB)**: Title and document type auto-filled, manual upload required
-   **Oversized files (> 20MB)**: Blocked with browser popup
-   **sessionStorage limit**: ~5-10MB (browser dependent)

## Large File Handling

For files larger than 5MB, the system:

1. Validates the file type and size
2. Stores only metadata (name, size, type) in sessionStorage
3. Redirects to the create document page
4. Auto-fills the title and document type
5. Shows a notification asking the user to manually upload the file
6. The user can then use the file upload field to upload the large file

## Filament Upload Field Detection

The system automatically detects when Filament upload fields are present on a page and disables the global drag-drop feature to prevent conflicts:

### Detection Logic

The system checks for various Filament upload field selectors:

-   `input[type="file"]` - Standard file inputs
-   `.fi-fo-file-upload` - Filament file upload component
-   `[wire:model*="file"]` - Livewire file model bindings
-   `[wire:model*="upload"]` - Livewire upload model bindings
-   `.fi-input[data-field*="file"]` - Filament input with file field
-   `input[name*="file"]` - Inputs with "file" in name

### Behavior

-   **Pages WITH Filament upload fields**: Global drag-drop disabled, native Filament drag-drop works
-   **Pages WITHOUT Filament upload fields**: Global drag-drop enabled, redirects to document creation

### Examples

-   **Document creation page**: Has Filament upload field → Global drag-drop disabled
-   **Document list page**: No upload fields → Global drag-drop enabled
-   **User profile page**: No upload fields → Global drag-drop enabled

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
