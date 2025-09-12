# Troubleshooting Playwright MCP in Cursor IDE

## Issue: Playwright MCP Server Not Appearing in MCP Tools

### Current Status

✅ Playwright MCP is installed globally (v0.0.37)
✅ Configuration has been added to Cursor settings file
❌ Playwright MCP server not visible in Cursor IDE MCP Tools section

### Troubleshooting Steps

#### 1. **Verify Cursor IDE Restart**

-   **COMPLETELY CLOSE** Cursor IDE (not just minimize)
-   Wait 5-10 seconds
-   Reopen Cursor IDE
-   Go to Settings > MCP & Integrations

#### 2. **Manual Configuration (Recommended)**

If the automatic configuration isn't working, add manually:

1. In Cursor IDE, go to **Settings** (Ctrl+, or Cmd+,)
2. Search for **"MCP"**
3. Click **"New MCP Server"** (the + button)
4. Fill in the details:
    - **Name**: `playwright`
    - **Command**: `npx`
    - **Arguments**: `@playwright/mcp`
5. Click **Save**
6. **Restart Cursor IDE completely**

#### 3. **Check Configuration File**

The configuration should be in:

```
C:\Users\PC CUSTOM\AppData\Roaming\Cursor\User\settings.json
```

Look for this section:

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

#### 4. **Alternative Configuration Formats**

Try these different formats in your settings.json:

**Format 1 - Chat MCP Servers:**

```json
{
    "chat.mcp.servers": {
        "playwright": {
            "command": "npx",
            "args": ["@playwright/mcp"]
        }
    }
}
```

**Format 2 - MCP Object:**

```json
{
    "mcp": {
        "servers": {
            "playwright": {
                "command": "npx",
                "args": ["@playwright/mcp"]
            }
        }
    }
}
```

**Format 3 - Direct Configuration:**

```json
{
    "mcp.servers.playwright": {
        "command": "npx",
        "args": ["@playwright/mcp"]
    }
}
```

#### 5. **Check Cursor IDE Version**

Ensure you're using a recent version of Cursor IDE that supports MCP servers:

-   Go to Help > About
-   Check version number
-   Update if necessary

#### 6. **Verify Node.js and npm**

```bash
node --version
npm --version
npx @playwright/mcp --version
```

#### 7. **Test Playwright MCP Installation**

```bash
node scripts/test-playwright-mcp.js
```

#### 8. **Check for Workspace Settings**

Create a `.vscode/settings.json` file in your project root:

```json
{
    "mcp.servers": {
        "playwright": {
            "command": "npx",
            "args": ["@playwright/mcp"]
        }
    }
}
```

#### 9. **Alternative: Use Cursor CLI (if available)**

```bash
# If Cursor has a CLI tool
cursor --add-mcp-server playwright npx @playwright/mcp
```

#### 10. **Check Cursor IDE Logs**

1. Open Command Palette (Ctrl+Shift+P)
2. Type "Developer: Show Logs"
3. Check for any MCP-related errors

### Expected Behavior After Success

Once properly configured, you should see:

-   **"playwright"** listed in MCP Tools section
-   Toggle switch next to it (can be enabled/disabled)
-   Status showing "Connected" or "Available"

### Testing the Integration

After successful configuration, test with these commands:

1. **Basic Test**: "Take a screenshot of https://example.com"
2. **Laravel Test**: "Test my Filament admin panel"
3. **Action Board Test**: "Validate the Kanban board functionality"

### Fallback Solution

If all else fails, you can still use Playwright MCP through:

1. **Command Line**: `npx @playwright/mcp`
2. **Laravel API**: Use the endpoints we created (`/api/playwright/*`)
3. **Direct Integration**: Use the `PlaywrightMcpService` in your Laravel code

### Common Issues and Solutions

#### Issue: "Command not found: npx"

**Solution**: Ensure Node.js and npm are properly installed and in PATH

#### Issue: "Playwright MCP not found"

**Solution**: Reinstall with `npm install -g @playwright/mcp`

#### Issue: "Permission denied"

**Solution**: Run Cursor IDE as administrator (Windows) or with sudo (Linux/Mac)

#### Issue: "Settings file not writable"

**Solution**: Check file permissions on the settings directory

### Support Resources

-   **Cursor IDE Documentation**: Check official MCP server documentation
-   **Playwright MCP GitHub**: https://github.com/microsoft/playwright-mcp
-   **Laravel Boost Documentation**: Use `mcp_laravel-boost_search-docs`

### Next Steps

1. Try the manual configuration first (most reliable)
2. If that doesn't work, try the alternative configuration formats
3. Check Cursor IDE version and update if necessary
4. Use the Laravel API endpoints as a fallback
5. Test the integration once it appears in MCP Tools

The key is ensuring Cursor IDE completely restarts after any configuration changes.
