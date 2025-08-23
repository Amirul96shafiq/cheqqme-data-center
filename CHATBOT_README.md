# CheQQme AI Chatbot Integration

## Overview

The CheQQme AI Chatbot is an intelligent assistant integrated into the CheQQme Data Center that helps users navigate the platform, find resources, and get answers to their questions. The chatbot uses OpenAI's GPT models to provide contextual assistance.

## Features

-   **Floating Chat Button**: Always accessible floating button in the bottom-right corner
-   **Intelligent Responses**: AI-powered responses using OpenAI GPT models
-   **Context Awareness**: Understands the CheQQme Data Center platform and its features
-   **Conversation Memory**: Maintains conversation context during the session
-   **Dark Mode Support**: Fully compatible with both light and dark themes
-   **Responsive Design**: Works seamlessly on desktop and mobile devices

## Setup Instructions

### 1. OpenAI API Configuration

1. **Get OpenAI API Key**:

    - Visit [OpenAI Platform](https://platform.openai.com/)
    - Create an account or sign in
    - Navigate to API Keys section
    - Create a new API key

2. **Configure Environment Variables**:

    ```bash
    # Copy the example environment file
    cp .env.example .env

    # Edit .env and add your OpenAI configuration
    OPENAI_API_KEY=your_actual_openai_api_key_here
    OPENAI_MODEL=gpt-3.5-turbo  # or gpt-4 if you have access
    OPENAI_MAX_TOKENS=500
    OPENAI_TEMPERATURE=1.2
    ```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Generate application key
php artisan key:generate
```

### 3. Database Setup

```bash
# Run migrations
php artisan migrate

# (Optional) Seed with sample data
php artisan db:seed
```

### 4. Start the Application

```bash
# Start Laravel development server
php artisan serve

# In another terminal, start Vite for asset compilation
npm run dev
```

## Usage

### Accessing the Chatbot

1. **Login to the Platform**: Navigate to `/admin` and log in with your credentials
2. **Floating Button**: Look for the chat icon in the bottom-right corner of the screen
3. **Click to Open**: Click the floating button to open the chat interface

### Chatting with the AI

1. **Welcome Message**: The chatbot will greet you with an introduction
2. **Type Your Question**: Use the input field at the bottom to ask questions
3. **Get Responses**: The AI will respond based on the platform context
4. **Conversation Flow**: The chat maintains context throughout your session

### Example Questions

The chatbot can help with various topics:

-   **Platform Navigation**: "How do I access the Action Board?"
-   **Feature Information**: "What can I do with the document management system?"
-   **Task Management**: "How do I create a new task?"
-   **User Management**: "How do I add a new user to the system?"
-   **General Help**: "What features are available in this platform?"

## Technical Details

### Architecture

-   **Frontend**: Vanilla JavaScript with Tailwind CSS
-   **Backend**: Laravel PHP with OpenAI API integration
-   **State Management**: Client-side conversation management
-   **API Endpoints**: RESTful API for chat interactions

### API Endpoints

-   `POST /chatbot/chat` - Send a message and get AI response
-   `GET /chatbot/conversation` - Get conversation history
-   `DELETE /chatbot/conversation` - Clear conversation

### Security Features

-   **Authentication Required**: Only authenticated users can access the chatbot
-   **CSRF Protection**: All requests are protected against CSRF attacks
-   **Input Validation**: Messages are validated and sanitized
-   **Rate Limiting**: Built-in protection against abuse

### Configuration Options

| Setting              | Description               | Default         |
| -------------------- | ------------------------- | --------------- |
| `OPENAI_MODEL`       | AI model to use           | `gpt-3.5-turbo` |
| `OPENAI_MAX_TOKENS`  | Maximum response length   | `500`           |
| `OPENAI_TEMPERATURE` | Response creativity (0-2) | `1.2`           |

## Customization

### Modifying the AI Persona

Edit the system prompt in `app/Http/Controllers/ChatbotController.php`:

```php
protected function getSystemPrompt()
{
    return "Your custom system prompt here...";
}
```

### Styling Changes

The chatbot uses Tailwind CSS classes. Modify the styles in:

-   `resources/views/filament/widgets/chatbot-widget.blade.php`

### Adding New Features

1. **New API Endpoints**: Add routes in `routes/web.php`
2. **Enhanced Logic**: Modify the controller methods
3. **UI Improvements**: Update the Blade template

## Troubleshooting

### Common Issues

1. **Chatbot Not Appearing**:

    - Check if you're logged in
    - Verify the widget is registered in `AdminPanelProvider.php`
    - Check browser console for JavaScript errors

2. **API Errors**:

    - Verify OpenAI API key is correct
    - Check API quota and billing status
    - Review Laravel logs for detailed error messages

3. **Styling Issues**:
    - Ensure Tailwind CSS is properly compiled
    - Check for CSS conflicts with Filament

### Debug Mode

Enable debug mode in `.env`:

```bash
APP_DEBUG=true
LOG_LEVEL=debug
```

### Logs

Check Laravel logs for detailed error information:

```bash
tail -f storage/logs/laravel.log
```

## Performance Considerations

-   **Caching**: Conversation history is cached for 24 hours
-   **API Optimization**: Responses are optimized for speed
-   **Resource Usage**: Minimal impact on page performance
-   **Mobile Optimization**: Responsive design for all devices

## Security Best Practices

1. **API Key Management**: Never commit API keys to version control
2. **Input Sanitization**: All user inputs are properly validated
3. **Authentication**: Ensure proper user authentication
4. **Rate Limiting**: Consider implementing rate limiting for production

## Future Enhancements

-   **Multi-language Support**: Internationalization for different languages
-   **Voice Input**: Speech-to-text capabilities
-   **File Uploads**: Support for document analysis
-   **Integration**: Connect with external knowledge bases
-   **Analytics**: Track usage patterns and improve responses

## Support

For technical support or feature requests:

-   Check the main project README
-   Review Laravel and Filament documentation
-   Open an issue in the project repository

## License

This chatbot integration follows the same license as the main CheQQme Data Center project.
