<div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const draggedFile = sessionStorage.getItem('draggedFile');
            if (!draggedFile) return;
            
            const fileData = JSON.parse(draggedFile);
            console.log("Processing dragged file:", fileData.name);
            sessionStorage.removeItem('draggedFile');
            
            // Check if this is a large file
            if (fileData.isLargeFile) {
                console.log("Large file detected, setting title and document type");
                initializeLargeFileAutoFill(fileData);
                
                // Retry after Livewire loads
                document.addEventListener('livewire:init', () => {
                    setTimeout(() => initializeLargeFileAutoFill(fileData), 100);
                });
            } else {
                // Initialize form auto-fill for regular files
                initializeFormAutoFill(fileData);
                
                // Retry after Livewire loads
                document.addEventListener('livewire:init', () => {
                    setTimeout(() => initializeFormAutoFill(fileData), 100);
                });
            }
        });
        
        function initializeFormAutoFill(fileData) {
            // Set title field
            setTitleField(fileData.name);
            
            // Set document type and file upload
            setDocumentTypeAndFile(fileData);
        }
        
        function initializeLargeFileAutoFill(fileData) {
            // Set title field
            setTitleField(fileData.name);
            
            // Set document type
            setDocumentType('internal');
            
            // Show notification
            showLargeFileMessage(fileData);
        }
        
        function setTitleField(fileName) {
            const titleSelectors = [
                'input[name="data[title]"]',
                'input[wire\\:model="data.title"]',
                'input[data-field="title"]',
                'input[placeholder*="title" i]',
                'input[placeholder*="document" i]',
                'input[placeholder*="name" i]',
                '.fi-input[data-field="title"]',
                'input[type="text"]:first-of-type'
            ];
            
            let titleField = findElement(titleSelectors);
            
            // Aggressive search if not found
            if (!titleField) {
                const forms = document.querySelectorAll('form');
                forms.forEach(form => {
                    const inputs = form.querySelectorAll('input[type="text"]');
                    inputs.forEach(input => {
                        if (input.name === 'data[title]' || 
                            input.getAttribute('wire:model') === 'data.title' ||
                            input.getAttribute('data-field') === 'title' ||
                            (input.placeholder && input.placeholder.toLowerCase().includes('title'))) {
                            titleField = input;
                        }
                    });
                });
            }
            
            if (titleField) {
                console.log("Setting document title:", fileName);
                setFieldValue(titleField, fileName);
            } else {
                console.log("Title field not found, retrying...");
                setTimeout(() => setTitleField(fileName), 500);
                setTimeout(() => setTitleField(fileName), 1500);
            }
        }
        
        function setDocumentTypeAndFile(fileData) {
            const typeSelectors = [
                'select[name="data[type]"]',
                'select[wire\\:model="data.type"]',
                'select[data-field="type"]',
                '[data-field="type"]',
                '[wire\\:model="data.type"]'
            ];
            
            const documentTypeField = findElement(typeSelectors);
            
            if (documentTypeField) {
                setFieldValue(documentTypeField, 'internal');
            }
            
            // Proceed with file upload after a delay
            setTimeout(() => setFileUpload(fileData), 1000);
        }
        
        function setDocumentType(type) {
            const typeSelectors = [
                'select[name="data[type]"]',
                'select[wire\\:model="data.type"]',
                'select[data-field="type"]',
                '[data-field="type"]',
                '[wire\\:model="data.type"]'
            ];
            
            const documentTypeField = findElement(typeSelectors);
            
            if (documentTypeField) {
                setFieldValue(documentTypeField, type);
            }
        }
        
        function showLargeFileMessage(fileData) {
            // Check if notification already exists to prevent duplicates
            const existingNotification = document.querySelector('.large-file-notification');
            if (existingNotification) {
                return; // Notification already exists, don't create another
            }
            
            // Create a notification for large files
            const message = `Large file detected (${(fileData.size / 1024 / 1024).toFixed(1)}MB). Please use the file upload field below to upload "${fileData.name}".`;
            
            // Try to find a notification area or create one
            let notificationArea = document.querySelector('.fi-notifications') || 
                                 document.querySelector('.notifications') ||
                                 document.querySelector('.fi-form');
            
            if (notificationArea) {
                const notification = document.createElement('div');
                notification.className = 'large-file-notification'; // Add class for duplicate detection
                notification.style.cssText = `
                    background: #fef3c7;
                    border: 1px solid #f59e0b;
                    border-radius: 6px;
                    padding: 12px 16px;
                    margin: 16px 0;
                    color: #92400e;
                    font-size: 14px;
                `;
                notification.innerHTML = `
                    <strong>📁 Large File Upload</strong><br>
                    ${message}
                `;
                
                // Insert at the top of the form
                notificationArea.insertBefore(notification, notificationArea.firstChild);
                
                // Auto-remove after 10 seconds
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 10000);
            }
            
            console.log("Large file message:", message);
        }
        
        function setFileUpload(fileData) {
            const file = createFileFromBase64(fileData);
            const fileInput = findFileInput();
            
            if (fileInput) {
                console.log("Starting file upload:", fileData.name);
                setFileInputValue(fileInput, file);
            } else {
                console.log("File input not found");
            }
        }
        
        function findElement(selectors) {
            for (const selector of selectors) {
                const element = document.querySelector(selector);
                if (element) return element;
            }
            return null;
        }
        
        function findFileInput() {
            const fileSelectors = [
                'input[type="file"]',
                '[wire\\:model="data.file_path"]',
                '[data-field="file_path"]',
                'input[name="data[file_path]"]',
                '.fi-fo-file-upload input[type="file"]',
                '[data-component="file-upload"] input[type="file"]',
                'input[accept*="pdf"]',
                'input[accept*="image"]',
                'input[accept*="word"]'
            ];
            
            return findElement(fileSelectors);
        }
        
        function setFieldValue(field, value) {
            // Set value using multiple approaches
            field.value = value;
            field.setAttribute('value', value);
            
            // Trigger events for Filament/Livewire
            ['input', 'change', 'blur'].forEach(eventType => {
                field.dispatchEvent(new Event(eventType, { bubbles: true }));
            });
            
            if (window.Livewire) {
                field.dispatchEvent(new Event('livewire:update', { bubbles: true }));
            }
            
            // Trigger focus/blur cycle for better compatibility
            field.focus();
            field.blur();
        }
        
        function createFileFromBase64(fileData) {
            const byteCharacters = atob(fileData.content.split(',')[1]);
            const byteNumbers = new Array(byteCharacters.length);
            
            for (let i = 0; i < byteCharacters.length; i++) {
                byteNumbers[i] = byteCharacters.charCodeAt(i);
            }
            
            const byteArray = new Uint8Array(byteNumbers);
            return new File([byteArray], fileData.name, { type: fileData.type });
        }
        
        function setFileInputValue(fileInput, file) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
            
            // Trigger events for Filament/Livewire
            ['change', 'input'].forEach(eventType => {
                fileInput.dispatchEvent(new Event(eventType, { bubbles: true }));
            });
            
            // Verify file was set
            setTimeout(() => {
                if (fileInput.files.length > 0) {
                    console.log("File uploaded successfully:", fileInput.files[0].name);
                } else {
                    console.log("File upload failed");
                }
            }, 100);
        }
    </script>
</div>