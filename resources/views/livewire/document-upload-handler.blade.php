<div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const draggedFile = sessionStorage.getItem('draggedFile');
            if (!draggedFile) return;
            
            console.log("Processing dragged file:", JSON.parse(draggedFile).name);
            sessionStorage.removeItem('draggedFile');
            
            const fileData = JSON.parse(draggedFile);
            
            // Initialize form auto-fill
            initializeFormAutoFill(fileData);
            
            // Retry after Livewire loads
            document.addEventListener('livewire:init', () => {
                setTimeout(() => initializeFormAutoFill(fileData), 100);
            });
        });
        
        function initializeFormAutoFill(fileData) {
            // Set title field
            setTitleField(fileData.name);
            
            // Set document type and file upload
            setDocumentTypeAndFile(fileData);
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