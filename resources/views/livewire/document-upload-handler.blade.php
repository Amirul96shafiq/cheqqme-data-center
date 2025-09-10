<div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DocumentUploadHandler loaded");
            
            // Check if there's a dragged file in sessionStorage
            const draggedFile = sessionStorage.getItem('draggedFile');
            
            if (draggedFile) {
                console.log("Found dragged file in sessionStorage");
                // Clear the sessionStorage
                sessionStorage.removeItem('draggedFile');
                
                // Parse the file data
                const fileData = JSON.parse(draggedFile);
                
                // Pre-fill the form fields - try multiple selectors
                console.log("Looking for title field...");
                console.log("Available inputs:", document.querySelectorAll('input'));
                
                let titleField = document.querySelector('input[name="data[title]"]') || 
                                document.querySelector('input[wire\\:model="data.title"]') ||
                                document.querySelector('input[data-field="title"]') ||
                                document.querySelector('input[placeholder*="title" i]') ||
                                document.querySelector('input[placeholder*="document" i]') ||
                                document.querySelector('input[placeholder*="name" i]') ||
                                document.querySelector('.fi-input[data-field="title"]') ||
                                document.querySelector('input[type="text"]:first-of-type');
                
                console.log("Title field search result:", titleField);
                
                if (titleField) {
                    console.log("Setting title field:", titleField);
                    console.log("Setting title to:", fileData.name);
                    
                    // Try multiple approaches to set the value
                    titleField.value = fileData.name;
                    titleField.setAttribute('value', fileData.name);
                    
                    // Trigger multiple events to ensure Filament picks it up
                    titleField.dispatchEvent(new Event('input', { bubbles: true }));
                    titleField.dispatchEvent(new Event('change', { bubbles: true }));
                    titleField.dispatchEvent(new Event('blur', { bubbles: true }));
                    
                    // Try to trigger Livewire update
                    if (window.Livewire) {
                        titleField.dispatchEvent(new Event('livewire:update', { bubbles: true }));
                    }
                    
                    console.log("Title field value after setting:", titleField.value);
                } else {
                    console.log("Title field not found");
                    console.log("Available input fields:");
                    document.querySelectorAll('input').forEach((input, index) => {
                        console.log(`Input ${index}:`, {
                            type: input.type,
                            name: input.name,
                            placeholder: input.placeholder,
                            'wire:model': input.getAttribute('wire:model'),
                            'data-field': input.getAttribute('data-field'),
                            class: input.className
                        });
                    });
                }
                
                // Also try to set title after a delay in case the form wasn't fully rendered
                setTimeout(() => {
                    console.log("Retrying title field after delay...");
                    
                    let titleFieldDelayed = document.querySelector('input[name="data[title]"]') || 
                                          document.querySelector('input[wire\\:model="data.title"]') ||
                                          document.querySelector('input[data-field="title"]') ||
                                          document.querySelector('input[placeholder*="title" i]') ||
                                          document.querySelector('input[placeholder*="document" i]') ||
                                          document.querySelector('input[placeholder*="name" i]') ||
                                          document.querySelector('.fi-input[data-field="title"]') ||
                                          document.querySelector('input[type="text"]:first-of-type');
                    
                    if (titleFieldDelayed && (!titleField || titleFieldDelayed.value !== fileData.name)) {
                        console.log("Setting title field (delayed):", titleFieldDelayed);
                        titleFieldDelayed.value = fileData.name;
                        titleFieldDelayed.setAttribute('value', fileData.name);
                        titleFieldDelayed.dispatchEvent(new Event('input', { bubbles: true }));
                        titleFieldDelayed.dispatchEvent(new Event('change', { bubbles: true }));
                        console.log("Title field value after delayed setting:", titleFieldDelayed.value);
                    }
                }, 500);
                
                // Set document type to internal first - try multiple selectors
                let documentTypeField = document.querySelector('select[name="data[type]"]') || 
                                      document.querySelector('select[wire\\:model="data.type"]') ||
                                      document.querySelector('select[data-field="type"]') ||
                                      document.querySelector('[data-field="type"]') ||
                                      document.querySelector('[wire\\:model="data.type"]');
                
                console.log("Looking for document type field...");
                console.log("Available selects:", document.querySelectorAll('select'));
                
                if (documentTypeField) {
                    console.log("Setting document type field:", documentTypeField);
                    documentTypeField.value = 'internal';
                    documentTypeField.dispatchEvent(new Event('change', { bubbles: true }));
                    
                    // Wait a bit for the form to update, then set the file
                    setTimeout(() => {
                        console.log("Attempting to set file directly...");
                        
                        // Convert base64 back to file
                        const byteCharacters = atob(fileData.content.split(',')[1]);
                        const byteNumbers = new Array(byteCharacters.length);
                        for (let i = 0; i < byteCharacters.length; i++) {
                            byteNumbers[i] = byteCharacters.charCodeAt(i);
                        }
                        const byteArray = new Uint8Array(byteNumbers);
                        const file = new File([byteArray], fileData.name, { type: fileData.type });
                        
                        // Try to find the Filament file upload component
                        const fileUploadSelectors = [
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
                        
                        let fileInput = null;
                        for (const selector of fileUploadSelectors) {
                            fileInput = document.querySelector(selector);
                            if (fileInput) {
                                console.log("Found file input with selector:", selector);
                                break;
                            }
                        }
                        
                        if (fileInput) {
                            console.log("Setting file input:", fileInput);
                            
                            // Create a new DataTransfer object and add the file
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            fileInput.files = dataTransfer.files;
                            
                            // Trigger change event
                            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                            
                            // Also try input event
                            fileInput.dispatchEvent(new Event('input', { bubbles: true }));
                            
                            console.log("File set successfully");
                            
                            // Check if the file was actually set
                            setTimeout(() => {
                                console.log("File input files:", fileInput.files);
                                if (fileInput.files.length > 0) {
                                    console.log("✅ File successfully set in input");
                                    console.log("File name:", fileInput.files[0].name);
                                } else {
                                    console.log("❌ File not set in input");
                                }
                            }, 100);
                            
                        } else {
                            console.log("No file input found");
                            console.log("Available inputs:", document.querySelectorAll('input'));
                        }
                    }, 1000);
                } else {
                    console.log("Document type field not found, proceeding with file upload anyway");
                    
                    // Proceed with file upload even if document type field not found
                    setTimeout(() => {
                        console.log("Attempting to set file directly (no document type field)...");
                        
                        // Convert base64 back to file
                        const byteCharacters = atob(fileData.content.split(',')[1]);
                        const byteNumbers = new Array(byteCharacters.length);
                        for (let i = 0; i < byteCharacters.length; i++) {
                            byteNumbers[i] = byteCharacters.charCodeAt(i);
                        }
                        const byteArray = new Uint8Array(byteNumbers);
                        const file = new File([byteArray], fileData.name, { type: fileData.type });
                        
                        // Try to find the Filament file upload component
                        const fileUploadSelectors = [
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
                        
                        let fileInput = null;
                        for (const selector of fileUploadSelectors) {
                            fileInput = document.querySelector(selector);
                            if (fileInput) {
                                console.log("Found file input with selector:", selector);
                                break;
                            }
                        }
                        
                        if (fileInput) {
                            console.log("Setting file input:", fileInput);
                            
                            // Create a new DataTransfer object and add the file
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            fileInput.files = dataTransfer.files;
                            
                            // Trigger change event
                            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                            
                            // Also try input event
                            fileInput.dispatchEvent(new Event('input', { bubbles: true }));
                            
                            console.log("File set successfully");
                            
                            // Check if the file was actually set
                            setTimeout(() => {
                                console.log("File input files:", fileInput.files);
                                if (fileInput.files.length > 0) {
                                    console.log("✅ File successfully set in input");
                                    console.log("File name:", fileInput.files[0].name);
                                } else {
                                    console.log("❌ File not set in input");
                                }
                            }, 100);
                            
                        } else {
                            console.log("No file input found");
                            console.log("Available inputs:", document.querySelectorAll('input'));
                        }
                    }, 1000);
                }
            }
        });
    </script>
</div>