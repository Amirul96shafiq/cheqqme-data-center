#!/usr/bin/env node

/**
 * Fix Cursor IDE MCP Configuration
 * This script tries different configuration formats for Playwright MCP
 */

const fs = require("fs");
const path = require("path");
const os = require("os");

console.log("🔧 Fixing Cursor IDE MCP Configuration...\n");

function getCursorSettingsPath() {
    const platform = os.platform();
    let settingsPath;

    switch (platform) {
        case "win32":
            settingsPath = path.join(
                os.homedir(),
                "AppData",
                "Roaming",
                "Cursor",
                "User",
                "settings.json"
            );
            break;
        case "darwin":
            settingsPath = path.join(
                os.homedir(),
                "Library",
                "Application Support",
                "Cursor",
                "User",
                "settings.json"
            );
            break;
        case "linux":
            settingsPath = path.join(
                os.homedir(),
                ".config",
                "Cursor",
                "User",
                "settings.json"
            );
            break;
        default:
            throw new Error(`Unsupported platform: ${platform}`);
    }

    return settingsPath;
}

function readCursorSettings(settingsPath) {
    if (fs.existsSync(settingsPath)) {
        try {
            const content = fs.readFileSync(settingsPath, "utf8");
            return JSON.parse(content);
        } catch (error) {
            console.log(
                `   ⚠️  Could not parse existing settings: ${error.message}`
            );
            return {};
        }
    }
    return {};
}

function updateMCPConfiguration(settings) {
    console.log("Updating MCP configuration with multiple formats...");

    // Remove existing MCP configuration
    delete settings["mcp.servers"];
    delete settings["mcp"];
    delete settings["chat.mcp.servers"];

    // Try different configuration formats
    const configs = [
        // Format 1: Standard MCP servers
        {
            key: "mcp.servers",
            value: {
                playwright: {
                    command: "npx",
                    args: ["@playwright/mcp"],
                    env: {
                        NODE_ENV: "development",
                    },
                },
            },
        },
        // Format 2: Chat MCP servers
        {
            key: "chat.mcp.servers",
            value: {
                playwright: {
                    command: "npx",
                    args: ["@playwright/mcp"],
                    env: {
                        NODE_ENV: "development",
                    },
                },
            },
        },
        // Format 3: MCP object
        {
            key: "mcp",
            value: {
                servers: {
                    playwright: {
                        command: "npx",
                        args: ["@playwright/mcp"],
                        env: {
                            NODE_ENV: "development",
                        },
                    },
                },
            },
        },
    ];

    // Apply all configurations
    configs.forEach((config) => {
        settings[config.key] = config.value;
        console.log(`   ✅ Added ${config.key} configuration`);
    });

    // Also try the legacy format
    settings["mcp.servers.playwright"] = {
        command: "npx",
        args: ["@playwright/mcp"],
        env: {
            NODE_ENV: "development",
        },
    };

    console.log("   ✅ Added legacy mcp.servers.playwright configuration");

    return settings;
}

function writeCursorSettings(settingsPath, settings) {
    console.log("Writing updated configuration...");

    try {
        // Ensure directory exists
        const dir = path.dirname(settingsPath);
        if (!fs.existsSync(dir)) {
            fs.mkdirSync(dir, { recursive: true });
        }

        // Write settings file with proper formatting
        fs.writeFileSync(settingsPath, JSON.stringify(settings, null, 2));
        console.log(`   ✅ Configuration written to: ${settingsPath}`);
        return true;
    } catch (error) {
        console.log(`   ❌ Failed to write settings: ${error.message}`);
        return false;
    }
}

function main() {
    try {
        const settingsPath = getCursorSettingsPath();
        console.log(`📍 Settings path: ${settingsPath}`);

        // Read existing settings
        const settings = readCursorSettings(settingsPath);

        // Update MCP configuration
        const updatedSettings = updateMCPConfiguration(settings);

        // Write settings back
        const success = writeCursorSettings(settingsPath, updatedSettings);

        if (success) {
            console.log("\n🎉 Configuration updated successfully!");
            console.log("\n📖 Next steps:");
            console.log("1. COMPLETELY CLOSE Cursor IDE (not just minimize)");
            console.log("2. Wait 5 seconds");
            console.log("3. Reopen Cursor IDE");
            console.log("4. Go to Settings > MCP & Integrations");
            console.log("5. Look for 'playwright' in MCP Tools section");
            console.log("\n🔧 If still not visible, try manual configuration:");
            console.log("   - Click 'New MCP Server' in Cursor settings");
            console.log("   - Name: playwright");
            console.log("   - Command: npx");
            console.log("   - Arguments: @playwright/mcp");
        } else {
            console.log("\n❌ Configuration update failed");
        }
    } catch (error) {
        console.log(`❌ Error: ${error.message}`);
    }
}

main();
