<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Profile;
use Awcodes\LightSwitch\Enums\Alignment;
use Awcodes\LightSwitch\LightSwitchPlugin;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
// -----------------------------
// Plugins
// -----------------------------
// Light Switch by Adam Weston
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
// Global Search Modal by CharrafiMed
use Illuminate\Support\Facades\Request;
use Illuminate\View\Middleware\ShareErrorsFromSession;
// ActivityLog by Rômulo Ramos
use Rmsramos\Activitylog\ActivitylogPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->homeUrl(fn() => route('filament.admin.pages.dashboard'))
            ->id('admin')
            ->path('admin')
            ->favicon(asset('images/favicon.png'))
            ->brandLogo(Request::is('admin/login')
                ? asset('logos/logo-light.png')
                : asset('logos/logo-light-vertical.png'))

            ->darkModeBrandLogo(Request::is('admin/login')
                ? asset('logos/logo-dark.png')
                : asset('logos/logo-dark-vertical.png'))

            ->brandLogoHeight(Request::is('admin/login') ? '8rem' : '2.75rem')
            ->font('Roboto')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->profile(Profile::class, isSimple: false)
            ->databaseNotifications(true, false)
            ->databaseNotificationsPolling('5s')
            ->colors([
                'primary' => [
                    '50' => '#fff8eb',
                    '100' => '#fde7c3',
                    '200' => '#fcd39b',
                    '300' => '#fbbe72',
                    '400' => '#fab54f',
                    '500' => '#fbb43e',
                    '600' => '#e6a135',
                    '700' => '#c5862c',
                    '800' => '#a56b23',
                    '900' => '#844f1a',
                ],
                // Add native danger palette so Filament can style danger buttons (fi-color-danger)
                'danger' => Color::Red,
            ])
            ->sidebarWidth('17rem')
            // -----------------------------
            // Load both the Filament admin theme and the main app Tailwind bundle so that
            // all generated utilities (including danger reds) are guaranteed to be present
            // even if purge / safelist changes or fallback overrides are removed later.
            // -----------------------------
            ->viteTheme([
                'resources/css/filament/admin/theme.css',
                'resources/css/app.css',
            ])
            ->pages([
                \Filament\Pages\Dashboard::class,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Data Management'),

                NavigationGroup::make()
                    ->label('User Management'),

                NavigationGroup::make()
                    ->label('Tools'),
            ])
            ->renderHook(
                'panels::body.end',
                fn() => <<<'HTML'
                            <!-- Chatbot Widget -->
                            <div class="fixed bottom-4 right-4 z-[99]">
                                <!-- Floating Chat Button -->
                                <button 
                                    onclick="toggleChatbot()"
                                    class="bg-primary-600 hover:bg-primary-700 text-white rounded-full p-4 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-110 focus:outline-none focus:ring-4 focus:ring-primary-300"
                                    title="Chat with Arem"
                                >
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </button>

                                <!-- Chat Interface -->
                                <div id="chatbot-interface" class="absolute bottom-20 right-0 w-96 h-[500px] bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col hidden">
                                    <!-- Header -->
                                    <div class="bg-primary-600 text-white px-4 py-3 rounded-t-lg flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <div>
                                                <h3 class="font-semibold">Arem AI</h3>
                                                <p class="text-xs text-primary-100">Your brilliant assistant</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button 
                                                onclick="clearConversation()"
                                                class="p-1 hover:bg-white/20 rounded transition-colors"
                                                title="Clear conversation"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                            <button 
                                                onclick="toggleChatbot()"
                                                class="p-1 hover:bg-white/20 rounded transition-colors"
                                                title="Close chat"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Chat Messages -->
                                    <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
                                        <div class="flex justify-start">
                                            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg px-3 py-2 shadow-sm">
                                                <p class="text-sm text-gray-800 dark:text-gray-200">Type anything to start a new conversation!</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Input Area -->
                                    <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                                        <form onsubmit="sendMessage(event)" class="flex space-x-2" autocomplete="off">
                                            <input 
                                                type="text" 
                                                id="chat-input"
                                                autocomplete="off" 
                                                autocorrect="off"  
                                                spellcheck="false" 
                                                placeholder="Chat here" 
                                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                            >
                                            <button 
                                                type="submit" 
                                                class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    // -----------------------------
                                    // Global Search Keyboard Shortcut + Custom Placeholder
                                    // -----------------------------
                                    const searchInput = document.querySelector('.fi-global-search input');

                                    // Set placeholder
                                    if (searchInput) {
                                        searchInput.placeholder = "CTRL + / to search";
                                    }

                                    // Keyboard shortcut: /
                                    document.addEventListener('keydown', function (e) {
                                        if (e.ctrlKey && e.key.toLowerCase() === '/') {
                                            e.preventDefault();
                                            const input = document.querySelector('.fi-global-search input');
                                            if (input) {
                                                input.focus();
                                            }
                                        }
                                    });
                                });
                                // -----------------------------
                                // Enable horizontal drag-scroll on Flowforge board
                                // -----------------------------
                                (function () {
                                    let isBound = false;
                                    function bind() {
                                        if (isBound) return; isBound = true;
                                        document.addEventListener('mousedown', function (e) {
                                            const content = e.target.closest('.ff-column__content');
                                            if (!content) return;
                                            if (e.target.closest('.ff-card')) return; // don't interfere with card drag
                                            const scroller = content.closest('.ff-board__columns');
                                            if (!scroller) return;
                                            e.preventDefault(); // prevent text selection
                                            let isDown = true;
                                            const startX = e.pageX;
                                            const startScrollLeft = scroller.scrollLeft;
                                            scroller.classList.add('ff-drag-scrolling');
                                            const onMove = (ev) => {
                                                if (!isDown) return;
                                                scroller.scrollLeft = startScrollLeft - (ev.pageX - startX);
                                                ev.preventDefault();
                                            };
                                            const end = () => {
                                                isDown = false;
                                                scroller.classList.remove('ff-drag-scrolling');
                                                window.removeEventListener('mousemove', onMove);
                                                window.removeEventListener('mouseup', end);
                                                window.removeEventListener('mouseleave', end);
                                            };
                                            window.addEventListener('mousemove', onMove);
                                            window.addEventListener('mouseup', end);
                                            window.addEventListener('mouseleave', end);
                                        });
                                    }
                                    if (document.readyState === 'loading') {
                                        document.addEventListener('DOMContentLoaded', bind);
                                    } else {
                                        bind();
                                    }
                                    document.addEventListener('livewire:navigated', function(){ isBound = false; bind(); });
                                })();
                                
                                // -----------------------------
                                // Chatbot functionality
                                // -----------------------------
                                let conversationId = localStorage.getItem('chatbot_conversation_id') || ('conv_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9));
                                let conversation = [];
                                let isLoadingConversation = false;
                                let conversationLoaded = false;

                                console.log('Initializing chatbot:', {
                                    conversationIdFromStorage: localStorage.getItem('chatbot_conversation_id'),
                                    finalConversationId: conversationId,
                                    isNewConversation: !localStorage.getItem('chatbot_conversation_id')
                                });

                                // Save conversation ID to localStorage if it's newly generated
                                if (!localStorage.getItem('chatbot_conversation_id')) {
                                    localStorage.setItem('chatbot_conversation_id', conversationId);
                                    console.log('Saved new conversation ID to localStorage:', conversationId);
                                }

                                // Set welcome time
                                document.getElementById('welcome-time').textContent = new Date().toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit'});

                                // Try to load conversation history immediately if we have a conversation ID
                                if (conversationId) {
                                    console.log('Attempting to load conversation history on page load');
                                    setTimeout(() => {
                                        loadConversationHistory();
                                    }, 1000); // Small delay to ensure DOM is ready
                                }

                                function toggleChatbot() {
                                    const interface = document.getElementById('chatbot-interface');
                                    const isHidden = interface.classList.contains('hidden');

                                    interface.classList.toggle('hidden');
                                    const isNowHidden = interface.classList.contains('hidden');

                                    console.log('Toggling chatbot:', { wasHidden: isHidden, isNowHidden, conversationId });

                                    // Load conversation history when opening chat (when it becomes visible)
                                    if (isHidden && !isNowHidden && !isLoadingConversation) {
                                        loadConversationHistory();
                                    }
                                }

                                async function loadConversationHistory() {
                                    console.log('Loading conversation history:', {
                                        conversationId,
                                        conversationLength: conversation.length,
                                        conversationLoaded,
                                        isLoadingConversation
                                    });

                                    if (!conversationId || conversationLoaded || isLoadingConversation) {
                                        console.log('Skipping load - conversation already loaded or loading in progress');
                                        return;
                                    }

                                    try {
                                        isLoadingConversation = true;
                                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                                                         document.querySelector('input[name="_token"]')?.value;

                                        const response = await fetch(`/chatbot/conversation?conversation_id=${encodeURIComponent(conversationId)}`, {
                                            method: 'GET',
                                            headers: {
                                                'X-CSRF-TOKEN': csrfToken,
                                                'Accept': 'application/json'
                                            }
                                        });

                                        console.log('Conversation history response:', response.status);

                                        if (response.ok) {
                                            const data = await response.json();
                                            console.log('Conversation data:', data);

                                            if (data.conversation && data.conversation.length > 0) {
                                                // Load conversation messages
                                                const chatMessages = document.getElementById('chat-messages');
                                                // Clear welcome message
                                                chatMessages.innerHTML = '';

                                                data.conversation.forEach(message => {
                                                    addMessage(message.content, message.role, message.timestamp);
                                                });

                                                conversationLoaded = true;
                                                console.log('Loaded', data.conversation.length, 'messages from conversation');
                                            } else {
                                                console.log('No conversation messages found in database');
                                            }
                                        } else {
                                            console.error('Failed to load conversation:', response.statusText);
                                        }
                                    } catch (error) {
                                        console.error('Error loading conversation history:', error);
                                    } finally {
                                        isLoadingConversation = false;
                                    }
                                }

                                function addMessage(content, role, timestamp = null) {
                                    const chatMessages = document.getElementById('chat-messages');
                                    const messageDiv = document.createElement('div');
                                    messageDiv.className = 'flex ' + (role === 'user' ? 'justify-end' : 'justify-start');
                                    
                                    const messageClass = role === 'user' 
                                        ? 'bg-primary-600 text-white' 
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';
                                    
                                    const timeClass = role === 'user' 
                                        ? 'text-primary-100' 
                                        : 'text-gray-500 dark:text-gray-400';
                                    
                                    messageDiv.innerHTML = 
                                        '<div class="max-w-xs lg:max-w-md ' + messageClass + ' rounded-lg px-3 py-2 shadow-sm">' +
                                            '<p class="text-sm whitespace-pre-wrap">' + content + '</p>' +
                                            '<p class="text-xs ' + timeClass + ' mt-1">' + (timestamp || new Date().toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit'})) + '</p>' +
                                        '</div>';
                                    
                                    chatMessages.appendChild(messageDiv);
                                    chatMessages.scrollTop = chatMessages.scrollHeight;
                                }

                                function showLoading() {
                                    const chatMessages = document.getElementById('chat-messages');
                                    const loadingDiv = document.createElement('div');
                                    loadingDiv.id = 'loading-message';
                                    loadingDiv.className = 'flex justify-start';
                                    loadingDiv.innerHTML = 
                                        '<div class="bg-gray-100 dark:bg-gray-700 rounded-lg px-3 py-2 shadow-sm">' +
                                            '<div class="flex items-center space-x-2">' +
                                                '<div class="flex space-x-1">' +
                                                    '<div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>' +
                                                    '<div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>' +
                                                    '<div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>' +
                                                '</div>' +
                                                '<span class="text-sm text-gray-500 dark:text-gray-400">CheQQme is thinking...</span>' +
                                            '</div>' +
                                        '</div>';
                                    chatMessages.appendChild(loadingDiv);
                                    chatMessages.scrollTop = chatMessages.scrollHeight;
                                }

                                function hideLoading() {
                                    const loadingMessage = document.getElementById('loading-message');
                                    if (loadingMessage) {
                                        loadingMessage.remove();
                                    }
                                }

                                async function sendMessage(event) {
                                    event.preventDefault();
                                    
                                    const input = document.getElementById('chat-input');
                                    const message = input.value.trim();
                                    
                                    if (!message) return;
                                    
                                    // Add user message
                                    addMessage(message, 'user');
                                    input.value = '';
                                    
                                    // Show loading
                                    showLoading();
                                    
                                    try {
                                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                                         document.querySelector('input[name="_token"]')?.value;
                                        
                                        const response = await fetch('/chatbot/chat', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': csrfToken,
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                message: message,
                                                conversation_id: conversationId
                                            })
                                        });
                                        
                                        hideLoading();
                                        
                                        if (response.ok) {
                                            const data = await response.json();
                                            
                                            // Add AI response
                                            addMessage(data.response, 'assistant', data.timestamp);
                                            
                                            // Update conversation ID if provided
                                            if (data.conversation_id) {
                                                conversationId = data.conversation_id;
                                                localStorage.setItem('chatbot_conversation_id', conversationId);
                                                console.log('Updated conversation ID after message:', conversationId);
                                            }
                                        } else {
                                            addMessage('Sorry, I encountered an error. Please try again.', 'assistant');
                                        }
                                    } catch (error) {
                                        hideLoading();
                                        addMessage('Sorry, I encountered an error. Please try again.', 'assistant');
                                        console.error('Chatbot error:', error);
                                    }
                                }

                                async function clearConversation() {
                                    try {
                                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                                                         document.querySelector('input[name="_token"]')?.value;

                                        // Clear conversation from server
                                        await fetch('/chatbot/conversation', {
                                            method: 'DELETE',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': csrfToken,
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                conversation_id: conversationId
                                            })
                                        });
                                    } catch (error) {
                                        console.error('Error clearing conversation:', error);
                                    }

                                    // Clear local conversation
                                    const chatMessages = document.getElementById('chat-messages');
                                    chatMessages.innerHTML =
                                        '<div class="flex justify-start">' +
                                            '<div class="bg-gray-100 dark:bg-gray-700 rounded-lg px-3 py-2 shadow-sm">' +
                                                '<p class="text-sm text-gray-800 dark:text-gray-200">Type anything to start a new conversation!</p>'
                                            '</div>' +
                                        '</div>';

                                    // Generate new conversation ID and save to localStorage
                                    conversationId = 'conv_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                                    localStorage.setItem('chatbot_conversation_id', conversationId);
                                    conversation = [];
                                    conversationLoaded = false;
                                }
                            </script>
                        HTML,
            )
            ->plugins([
                LightSwitchPlugin::make()
                    ->position(Alignment::TopCenter)
                    ->enabledOn([
                        'auth.login',
                    ]),

                GlobalSearchModalPlugin::make()
                    ->maxWidth(MaxWidth::ThreeExtraLarge)
                    ->expandedUrlTarget(enabled: false),

                ActivitylogPlugin::make()
                    ->navigationGroup(fn() => __('activitylog.navigation_group'))
                    ->navigationSort(11),
            ]);
    }
}
