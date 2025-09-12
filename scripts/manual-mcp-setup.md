# Manual MCP Setup Guide for Cursor IDE

## Step-by-Step Manual Configuration

Since the automatic configuration isn't showing the Playwright MCP server in the UI, follow these manual steps:

### 1. **Open Cursor IDE Settings**

-   Press `Ctrl + ,` (Windows/Linux) or `Cmd + ,` (Mac)
-   Or go to File > Preferences > Settings

### 2. **Navigate to MCP Settings**

-   In the search bar, type: `MCP`
-   Click on **"MCP & Integrations"** in the left sidebar

### 3. **Add New MCP Server**

-   In the **"MCP Tools"** section, click the **"New MCP Server"** button (the + icon)
-   This should open a dialog box

### 4. **Fill in the Configuration**

In the dialog that opens, enter exactly:

```
Name: playwright
Command: npx
Arguments: @playwright/mcp
```

### 5. **Save and Restart**

-   Click **"Save"** or **"Add"**
-   **COMPLETELY CLOSE** Cursor IDE (not just minimize)
-   Wait 5 seconds
-   Reopen Cursor IDE

### 6. **Verify Installation**

-   Go back to Settings > MCP & Integrations
-   Look for **"playwright"** in the MCP Tools section
-   It should appear with a toggle switch

## Alternative: Edit Settings File Directly

If the UI method doesn't work, you can edit the settings file directly:

### 1. **Close Cursor IDE Completely**

### 2. **Edit the Settings File**

Open this file in a text editor:

```
C:\Users\PC CUSTOM\AppData\Roaming\Cursor\User\settings.json
```

### 3. **Add This Configuration**

Add this to the end of the file (before the closing `}`):

```json
,
  "mcp.servers": {
    "playwright": {
      "command": "npx",
      "args": ["@playwright/mcp"],
      "env": {
        "NODE_ENV": "development"
      }
    }
  }
```

### 4. **Save and Restart**

-   Save the file
-   Reopen Cursor IDE
-   Check Settings > MCP & Integrations

## Troubleshooting

### If Still Not Visible:

1. **Check Cursor IDE Version**

    - Go to Help > About
    - Ensure you're using a recent version

2. **Try Different Configuration Format**

    ```json
    "chat.mcp.servers": {
      "playwright": {
        "command": "npx",
        "args": ["@playwright/mcp"]
      }
    }
    ```

3. **Verify Playwright MCP Installation**

    ```bash
    npx @playwright/mcp --version
    ```

4. **Check Node.js Installation**
    ```bash
    node --version
    npm --version
    ```

## Testing

Once the Playwright MCP server appears in the MCP Tools section:

1. **Enable it** by toggling the switch
2. **Test with these commands**:
    - "Take a screenshot of https://example.com"
    - "Test my Laravel application"
    - "Navigate to the admin panel and validate the login form"

## Fallback Options

If MCP integration still doesn't work:

1. **Use Laravel API endpoints** we created (`/api/playwright/*`)
2. **Use command line directly**: `npx @playwright/mcp`
3. **Use the PlaywrightMcpService** in your Laravel code

The Laravel integration will work regardless of Cursor IDE MCP configuration.
