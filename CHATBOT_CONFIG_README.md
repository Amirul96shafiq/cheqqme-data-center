# OpenAI Chatbot Configuration Guide

## Environment Variables Setup

To enable the chatbot functionality, you need to configure the following environment variables in your `.env` file:

### Required OpenAI Configuration

```bash
# OpenAI API Configuration
OPENAI_API_KEY=your_actual_openai_api_key_here
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_MAX_TOKENS=500
OPENAI_TEMPERATURE=1.2
OPENAI_ORGANIZATION=
```

### Optional Chatbot Configuration

```bash
# Chatbot Feature Flags
CHATBOT_ENABLED=true
CHATBOT_PERSONA=cheqqme_assistant
CHATBOT_MAX_CONVERSATION_LENGTH=50
CHATBOT_CACHE_TTL_HOURS=24
```

## Getting Your OpenAI API Key

1. **Visit OpenAI Platform**

    - Go to [https://platform.openai.com/](https://platform.openai.com/)
    - Sign in or create an account

2. **Navigate to API Keys**

    - Click on your profile (top right)
    - Select "API Keys" from the dropdown

3. **Create New API Key**

    - Click "Create new secret key"
    - Give it a descriptive name (e.g., "CheQQme Chatbot")
    - Copy the generated key immediately (you won't see it again!)

4. **Add to Environment**
    - Add the key to your `.env` file
    - Keep this key secure and never commit it to version control

## Configuration Options Explained

### OpenAI Settings

| Variable              | Description             | Default         | Options                                    |
| --------------------- | ----------------------- | --------------- | ------------------------------------------ |
| `OPENAI_API_KEY`      | Your OpenAI API key     | Required        | Any valid OpenAI API key                   |
| `OPENAI_MODEL`        | AI model to use         | `gpt-3.5-turbo` | `gpt-3.5-turbo`, `gpt-4`, `gpt-4-turbo`    |
| `OPENAI_MAX_TOKENS`   | Maximum response length | `500`           | 1-4000 (model dependent)                   |
| `OPENAI_TEMPERATURE`  | Response creativity     | `1.2`             | 0.0-2.0 (0=deterministic, 2=very creative) |
| `OPENAI_ORGANIZATION` | OpenAI organization ID  | Optional        | Your org ID if applicable                  |

### Chatbot Settings

| Variable                          | Description                   | Default             | Range          |
| --------------------------------- | ----------------------------- | ------------------- | -------------- |
| `CHATBOT_ENABLED`                 | Enable/disable chatbot        | `true`              | `true`/`false` |
| `CHATBOT_PERSONA`                 | AI persona identifier         | `cheqqme_assistant` | Any string     |
| `CHATBOT_MAX_CONVERSATION_LENGTH` | Max messages per conversation | `50`                | 10-100         |
| `CHATBOT_CACHE_TTL_HOURS`         | Cache duration                | `24`                | 1-168 hours    |

## Model Recommendations

### GPT-3.5-Turbo (Recommended for most users)

-   **Cost**: Lower cost per token
-   **Speed**: Fast response times
-   **Capabilities**: Excellent for general assistance
-   **Use Case**: Perfect for the CheQQme chatbot use case

### GPT-4 (For advanced users)

-   **Cost**: Higher cost per token
-   **Speed**: Slower response times
-   **Capabilities**: More advanced reasoning and context understanding
-   **Use Case**: Complex technical questions or detailed explanations

### GPT-4-Turbo

-   **Cost**: Moderate cost
-   **Speed**: Fast response times
-   **Capabilities**: Advanced reasoning with better speed/cost ratio
-   **Use Case**: Good balance for professional applications

## Temperature Settings

-   **0.0-0.3**: Factual, consistent responses
-   **0.4-0.7**: Balanced creativity and consistency (recommended)
-   **0.8-1.2**: More creative and varied responses
-   **1.3-2.0**: Highly creative, potentially less coherent

## Security Best Practices

1. **API Key Security**

    - Never commit `.env` files to version control
    - Use environment-specific API keys
    - Rotate keys regularly
    - Monitor usage in OpenAI dashboard

2. **Rate Limiting**

    - The application includes built-in rate limiting
    - Monitor OpenAI usage to avoid unexpected costs
    - Set up billing alerts in OpenAI dashboard

3. **Data Privacy**
    - User conversations are cached temporarily
    - No personal data is sent to OpenAI unless part of the conversation
    - Review OpenAI's data usage policies

## Testing Your Configuration

After setting up your environment variables:

1. **Clear Configuration Cache**

    ```bash
    php artisan config:clear
    php artisan config:cache
    ```

2. **Test API Connection**

    ```bash
    php artisan tinker
    # In tinker:
    $controller = new App\Http\Controllers\ChatbotController();
    $result = $controller->callOpenAI([['role' => 'user', 'content' => 'Hello']]);
    echo $result;
    ```

3. **Access the Application**
    - Visit your application
    - Click the chatbot button in the bottom-right corner
    - Try sending a test message

## Troubleshooting

### Common Issues

1. **"API key not configured" error**

    - Check that `OPENAI_API_KEY` is set in `.env`
    - Ensure the key is valid and active
    - Clear configuration cache

2. **Slow responses**

    - Check your internet connection
    - Consider using GPT-3.5-turbo for faster responses
    - Verify OpenAI service status

3. **Rate limit errors**

    - Upgrade your OpenAI plan
    - Implement client-side rate limiting
    - Check OpenAI dashboard for usage limits

4. **Empty responses**
    - Verify the API key has sufficient credits
    - Check if the model is available in your region
    - Review OpenAI service status

## Cost Management

### Estimated Costs (as of 2024)

-   **GPT-3.5-Turbo**: ~$0.002 per 1K tokens
-   **GPT-4**: ~$0.03 per 1K tokens
-   **GPT-4-Turbo**: ~$0.01 per 1K tokens

### Usage Monitoring

1. **OpenAI Dashboard**

    - Monitor real-time usage
    - Set up billing alerts
    - View cost breakdowns

2. **Application Logging**

    - Check Laravel logs for API errors
    - Monitor conversation lengths
    - Track user engagement

3. **Cost Optimization**
    - Use appropriate token limits
    - Cache frequent responses
    - Implement conversation length limits

## Advanced Configuration

For more advanced setups, you can:

1. **Multiple AI Personas**

    - Create different system prompts for different use cases
    - Store prompts in database or config files
    - Allow users to select persona

2. **Custom Knowledge Base**

    - Integrate with your own documentation
    - Use OpenAI's file upload features
    - Implement retrieval-augmented generation (RAG)

3. **Analytics and Monitoring**
    - Track conversation topics
    - Monitor user satisfaction
    - Analyze common questions

## Support

If you encounter issues:

1. Check the main [CHATBOT_README.md](CHATBOT_README.md)
2. Review Laravel and OpenAI documentation
3. Open an issue in your project repository
4. Contact your development team

---

**Last Updated**: January 2024
**Compatible with**: OpenAI API v1.0+
