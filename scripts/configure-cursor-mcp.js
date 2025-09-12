#!/usr/bin/env node

/**
 * Cursor IDE MCP Configuration Script
 * This script helps configure Playwright MCP server in Cursor IDE
 */

const fs = require("fs");
const path = require("path");
const os = require("os");

console.log("🔧 Configuring Playwright MCP Server for Cursor IDE...\n");

// Get the appropriate settings file path based on OS
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

// Check if Playwright MCP is installed
function checkPlaywrightMCPInstallation() {
    console.log("1. Checking Playwright MCP installation...");

    const { spawn } = require("child_process");

    return new Promise((resolve) => {
        const testProcess = spawn("npx", ["@playwright/mcp", "--version"], {
            stdio: "pipe",
            shell: true,
        });

        testProcess.stdout.on("data", (data) => {
            const version = data.toString().trim();
            console.log(`   ✅ Playwright MCP version: ${version}`);
        });

        testProcess.on("close", (code) => {
            resolve(code === 0);
        });

        testProcess.on("error", () => {
            resolve(false);
        });
    });
}

// Read existing Cursor settings
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

// Add Playwright MCP configuration to settings
function addPlaywrightMCPConfig(settings) {
    console.log("2. Adding Playwright MCP configuration...");

    if (!settings["mcp.servers"]) {
        settings["mcp.servers"] = {};
    }

    // Check if playwright is already configured
    if (settings["mcp.servers"]["playwright"]) {
        console.log("   ℹ️  Playwright MCP server already configured");
        return settings;
    }

    settings["mcp.servers"]["playwright"] = {
        command: "npx",
        args: ["@playwright/mcp"],
        env: {
            NODE_ENV: "development",
        },
    };

    console.log("   ✅ Added Playwright MCP server configuration");
    return settings;
}

// Write settings back to file
function writeCursorSettings(settingsPath, settings) {
    console.log("3. Writing configuration to Cursor settings...");

    try {
        // Ensure directory exists
        const dir = path.dirname(settingsPath);
        if (!fs.existsSync(dir)) {
            fs.mkdirSync(dir, { recursive: true });
            console.log(`   📁 Created directory: ${dir}`);
        }

        // Write settings file
        fs.writeFileSync(settingsPath, JSON.stringify(settings, null, 2));
        console.log(`   ✅ Configuration written to: ${settingsPath}`);
        return true;
    } catch (error) {
        console.log(`   ❌ Failed to write settings: ${error.message}`);
        return false;
    }
}

// Generate manual configuration instructions
function generateManualInstructions() {
    console.log("\n📋 Manual Configuration Instructions:");
    console.log("If automatic configuration failed, follow these steps:");
    console.log("");
    console.log("1. Open Cursor IDE");
    console.log("2. Go to Settings (Ctrl+, or Cmd+,)");
    console.log('3. Search for "MCP"');
    console.log('4. Click "Add new MCP Server"');
    console.log("5. Enter the following:");
    console.log("   Name: playwright");
    console.log("   Command: npx");
    console.log("   Arguments: @playwright/mcp");
    console.log("6. Save and restart Cursor IDE");
    console.log("");
    console.log("Alternative: Edit settings file manually:");
    console.log(`   ${getCursorSettingsPath()}`);
    console.log("   Add this configuration:");
    console.log(
        JSON.stringify(
            {
                "mcp.servers": {
                    playwright: {
                        command: "npx",
                        args: ["@playwright/mcp"],
                    },
                },
            },
            null,
            2
        )
    );
}

// Main execution
async function main() {
    try {
        // Check Playwright MCP installation
        const isInstalled = await checkPlaywrightMCPInstallation();

        if (!isInstalled) {
            console.log("   ❌ Playwright MCP not found");
            console.log("   📦 Installing Playwright MCP...");

            const { spawn } = require("child_process");
            const installProcess = spawn(
                "npm",
                ["install", "-g", "@playwright/mcp"],
                {
                    stdio: "inherit",
                    shell: true,
                }
            );

            installProcess.on("close", (code) => {
                if (code === 0) {
                    console.log("   ✅ Playwright MCP installed successfully");
                    configureCursor();
                } else {
                    console.log("   ❌ Failed to install Playwright MCP");
                    generateManualInstructions();
                }
            });
        } else {
            configureCursor();
        }
    } catch (error) {
        console.log(`❌ Error: ${error.message}`);
        generateManualInstructions();
    }
}

function configureCursor() {
    try {
        const settingsPath = getCursorSettingsPath();
        console.log(`   📍 Settings path: ${settingsPath}`);

        // Read existing settings
        const settings = readCursorSettings(settingsPath);

        // Add Playwright MCP configuration
        const updatedSettings = addPlaywrightMCPConfig(settings);

        // Write settings back
        const success = writeCursorSettings(settingsPath, updatedSettings);

        if (success) {
            console.log("\n🎉 Configuration completed successfully!");
            console.log("\n📖 Next steps:");
            console.log("1. Restart Cursor IDE");
            console.log("2. Start Playwright MCP server:");
            console.log("   - Windows: scripts\\start-playwright-mcp.bat");
            console.log("   - Linux/Mac: ./scripts/start-playwright-mcp.sh");
            console.log("3. Test the integration:");
            console.log("   node scripts/test-playwright-mcp.js");
            console.log("4. Enable in Laravel .env:");
            console.log("   PLAYWRIGHT_MCP_ENABLED=true");
        } else {
            generateManualInstructions();
        }
    } catch (error) {
        console.log(`❌ Configuration failed: ${error.message}`);
        generateManualInstructions();
    }
}

// Run the script
main();


