# Playwright MCP Server Integration Guide

## Overview

This document provides a comprehensive guide for using the Playwright MCP (Model Context Protocol) server with the CheQQme Data Center project. The Playwright MCP server enables browser automation, web scraping, E2E testing, and UI validation directly from Cursor IDE.

## Installation Status

✅ **Playwright MCP Server v0.0.37** is installed and configured

## Quick Start

### 1. Configure Cursor IDE

1. Open Cursor IDE
2. Go to **Settings** → **MCP** → **Add new MCP Server**
3. Configure as follows:
    - **Name**: `playwright`
    - **Command**: `npx @playwright/mcp`
    - **Arguments**: (leave empty)

### 2. Start the Server

#### Windows

```batch
scripts\start-playwright-mcp.bat
```

#### Linux/Mac

```bash
./scripts/start-playwright-mcp.sh
```

#### Manual Start

```bash
npx @playwright/mcp --headless --timeout-action 5000
```

### 3. Verify Installation

```bash
node scripts/test-playwright-mcp.js
```

## Configuration

The project includes a pre-configured `playwright-mcp.config.json` file with optimal settings:

```json
{
    "browser": "chrome",
    "headless": true,
    "timeoutAction": 5000,
    "timeoutNavigation": 60000,
    "viewportSize": "1280,720",
    "outputDir": "./playwright-sessions",
    "saveSession": true,
    "saveTrace": true,
    "allowedOrigins": "*",
    "imageResponses": "allow",
    "ignoreHttpsErrors": false,
    "isolated": false,
    "userDataDir": "./playwright-user-data"
}
```

## Use Cases for CheQQme Data Center

### 1. Trello Integration Automation

-   Automate Trello board interactions for task management
-   Extract task data from Trello boards
-   Create and update Trello cards programmatically

### 2. Client Data Collection

-   Scrape client information from various sources
-   Automate data entry processes
-   Extract contact information from websites

### 3. Document Processing

-   Convert web-based documents to PDF
-   Extract text from online documents
-   Automate form submissions

### 4. API Testing

-   Verify external API endpoints and responses
-   Test webhook integrations
-   Validate API documentation

### 5. Dashboard Validation

-   Ensure Filament admin panels work correctly
-   Test user workflows through the application
-   Validate form submissions and data display

### 6. User Workflow Testing

-   Test complete user journeys through the application
-   Validate authentication flows
-   Test task management workflows

## Command Examples

### Basic Automation

```bash
# Headless browser automation
npx @playwright/mcp --headless --timeout-action 5000

# With session saving for debugging
npx @playwright/mcp --save-session --output-dir ./sessions

# Specific browser and viewport
npx @playwright/mcp --browser chrome --viewport-size "1920,1080"

# Device emulation for mobile testing
npx @playwright/mcp --device "iPhone 15"
```

### Advanced Configuration

```bash
# With custom timeouts and output
npx @playwright/mcp \
  --headless \
  --timeout-action 10000 \
  --timeout-navigation 120000 \
  --viewport-size "1920,1080" \
  --output-dir "./automation-results" \
  --save-session \
  --save-trace \
  --user-data-dir "./browser-profile"
```

## File Structure

```
├── playwright-mcp.config.json          # Main configuration file
├── scripts/
│   ├── start-playwright-mcp.bat       # Windows startup script
│   ├── start-playwright-mcp.sh        # Linux/Mac startup script
│   └── test-playwright-mcp.js         # Installation test script
├── playwright-sessions/                # Session output directory
├── playwright-user-data/               # Browser profile directory
└── docs/
    └── playwright-mcp-integration.md   # This documentation
```

## Best Practices

### 1. Performance

-   Use `--headless` mode for faster execution
-   Set appropriate timeouts to avoid hanging
-   Use isolated sessions for testing
-   Specify viewport size for consistent rendering

### 2. Debugging

-   Save sessions with `--save-session`
-   Use `--save-trace` for detailed debugging
-   Check output directories for logs and screenshots
-   Use browser developer tools when needed

### 3. Security

-   Be cautious with `--ignore-https-errors`
-   Use specific `--allowed-origins` when possible
-   Avoid storing sensitive data in user data directories
-   Regularly clean up session and trace files

### 4. Development Workflow

-   Test automation scripts in isolated mode first
-   Use version control for automation scripts
-   Document automation workflows
-   Keep configuration files updated

## Troubleshooting

### Common Issues

#### 1. Server Won't Start

```bash
# Check if Playwright MCP is installed
npm list -g @playwright/mcp

# Reinstall if necessary
npm install -g @playwright/mcp
```

#### 2. Browser Issues

```bash
# Install browser dependencies
npx playwright install

# Use different browser
npx @playwright/mcp --browser firefox
```

#### 3. Permission Issues

```bash
# Check file permissions
ls -la playwright-sessions/
ls -la playwright-user-data/

# Fix permissions if needed
chmod 755 playwright-sessions/
chmod 755 playwright-user-data/
```

#### 4. Timeout Issues

```bash
# Increase timeouts
npx @playwright/mcp --timeout-action 30000 --timeout-navigation 180000
```

### Getting Help

1. **Check logs**: Look in `playwright-sessions/` for error logs
2. **Test installation**: Run `node scripts/test-playwright-mcp.js`
3. **Check configuration**: Validate `playwright-mcp.config.json`
4. **Documentation**: Refer to [Playwright MCP documentation](https://github.com/microsoft/playwright-mcp)

## Integration with Laravel

### Testing Filament Components

```php
// Example: Test Filament resource creation
public function test_can_create_client_via_automation()
{
    // Use Playwright MCP to automate form submission
    // Validate data in database
    $this->assertDatabaseHas('clients', [
        'name' => 'Test Client',
        'email' => 'test@example.com'
    ]);
}
```

### API Endpoint Testing

```php
// Example: Test API endpoints with browser automation
public function test_api_endpoints_with_automation()
{
    // Use Playwright MCP to test API responses
    // Validate JSON structure and content
    // Test error handling
}
```

## Security Considerations

1. **Data Protection**: Never store sensitive data in automation scripts
2. **Access Control**: Use appropriate authentication in automated tests
3. **Rate Limiting**: Be respectful of external APIs and websites
4. **Cleanup**: Regularly clean up session files and browser data
5. **Monitoring**: Monitor automation scripts for unexpected behavior

## Maintenance

### Regular Tasks

-   Update Playwright MCP server: `npm update -g @playwright/mcp`
-   Clean up old session files
-   Update browser dependencies: `npx playwright install`
-   Review and update configuration files
-   Test automation scripts regularly

### Version Management

-   Keep track of Playwright MCP versions
-   Test compatibility with Laravel updates
-   Update automation scripts as needed
-   Document breaking changes

## Conclusion

The Playwright MCP server integration provides powerful browser automation capabilities for the CheQQme Data Center project. By following this guide, you can effectively use browser automation for testing, data collection, and workflow validation while maintaining security and performance best practices.

For additional support or questions, refer to the project documentation or the Playwright MCP community resources.


