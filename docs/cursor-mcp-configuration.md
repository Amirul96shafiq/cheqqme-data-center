# Cursor IDE MCP Configuration Guide

## Adding Playwright MCP Server to Cursor Settings

### Method 1: Through Cursor IDE Settings UI

1. **Open Cursor IDE**
2. **Go to Settings**:

    - Windows/Linux: `Ctrl + ,` or `File > Preferences > Settings`
    - macOS: `Cmd + ,` or `Cursor > Preferences > Settings`

3. **Navigate to MCP Settings**:

    - Search for "MCP" in the settings search bar
    - Or go to `Extensions > MCP`

4. **Add New MCP Server**:

    - Click "Add new MCP Server"
    - Fill in the following details:

    ```
    Name: playwright
    Command: npx
    Arguments: @playwright/mcp
    ```

5. **Save and Restart**:
    - Save the configuration
    - Restart Cursor IDE

### Method 2: Manual Configuration File

If the UI method doesn't work, you can manually edit the configuration file:

1. **Locate Cursor Settings Directory**:

    - Windows: `%APPDATA%\Cursor\User\settings.json`
    - macOS: `~/Library/Application Support/Cursor/User/settings.json`
    - Linux: `~/.config/Cursor/User/settings.json`

2. **Add MCP Configuration**:

    ```json
    {
        "mcp.servers": {
            "playwright": {
                "command": "npx",
                "args": ["@playwright/mcp"],
                "env": {
                    "NODE_ENV": "development"
                }
            }
        }
    }
    ```

3. **Save and Restart Cursor IDE**

### Method 3: Using Cursor CLI (if available)

```bash
# Add MCP server via command line (if CLI is available)
cursor --add-mcp-server playwright npx @playwright/mcp
```

## Verification Steps

After adding the Playwright MCP server:

1. **Check MCP Status**:

    - Open Cursor IDE
    - Look for MCP server status in the status bar or settings
    - The Playwright MCP server should show as "Connected" or "Available"

2. **Test Playwright MCP Integration**:

    - Try asking Cursor to take a screenshot: "Take a screenshot of https://example.com"
    - Ask Cursor to test a webpage: "Test the functionality of https://example.com"
    - Request browser automation: "Navigate to https://example.com and extract the page title"

3. **Verify with Your Laravel Application**:
    ```bash
    # Test if Playwright MCP is working with your Laravel app
    curl -X GET http://localhost:8000/api/playwright/status \
      -H "Authorization: Bearer YOUR_TOKEN"
    ```

## Troubleshooting

### Common Issues and Solutions

1. **MCP Server Not Found**:

    - Ensure Playwright MCP is installed: `npm list -g @playwright/mcp`
    - Reinstall if needed: `npm install -g @playwright/mcp`

2. **Permission Errors**:

    - Run Cursor IDE as administrator (Windows) or with sudo (Linux/Mac)
    - Check file permissions on the settings directory

3. **Node.js Not Found**:

    - Ensure Node.js is installed and in PATH
    - Verify with: `node --version` and `npm --version`

4. **Port Conflicts**:
    - Default Playwright MCP runs on port 3000
    - Change port if needed: `npx @playwright/mcp --port 3001`

### Advanced Configuration

For advanced users, you can customize the Playwright MCP server configuration:

```json
{
    "mcp.servers": {
        "playwright": {
            "command": "npx",
            "args": [
                "@playwright/mcp",
                "--headless",
                "--timeout-action",
                "5000",
                "--timeout-navigation",
                "60000",
                "--viewport-size",
                "1280,720"
            ],
            "env": {
                "NODE_ENV": "development",
                "PLAYWRIGHT_BROWSER": "chromium"
            }
        }
    }
}
```

## Integration with Your Laravel Project

Once configured, you can use Playwright MCP with your CheQQme Data Center project:

1. **Enable Playwright MCP in Laravel**:

    ```env
    PLAYWRIGHT_MCP_ENABLED=true
    PLAYWRIGHT_MCP_BASE_URL=http://localhost:3000
    ```

2. **Start the Playwright MCP Server**:

    ```bash
    # Use your project's startup script
    scripts\start-playwright-mcp.bat  # Windows
    ./scripts/start-playwright-mcp.sh  # Linux/Mac
    ```

3. **Test Integration**:
    ```bash
    # Test the integration endpoint
    curl -X POST http://localhost:8000/api/playwright/test-boost-integration \
      -H "Authorization: Bearer YOUR_TOKEN" \
      -H "Content-Type: application/json"
    ```

## Next Steps

After successfully adding Playwright MCP to Cursor:

1. **Configure Laravel Environment**: Update your `.env` file with Playwright MCP settings
2. **Start Playwright MCP Server**: Use the provided startup scripts
3. **Test Browser Automation**: Try automated testing of your Filament admin panel
4. **Use Combined Workflow**: Leverage both Laravel Boost and Playwright MCP for comprehensive debugging

## Support

If you encounter issues:

1. Check the Cursor IDE documentation for MCP server configuration
2. Review the Playwright MCP documentation: https://github.com/microsoft/playwright-mcp
3. Test Playwright MCP installation: `node scripts/test-playwright-mcp.js`
4. Check Laravel logs for integration errors

The integration between Cursor IDE, Playwright MCP, and Laravel Boost provides powerful browser automation and testing capabilities for your CheQQme Data Center project.


