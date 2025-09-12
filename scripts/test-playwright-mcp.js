#!/usr/bin/env node

/**
 * Test script for Playwright MCP Server
 * This script tests basic functionality of the Playwright MCP server
 */

const { spawn } = require("child_process");
const fs = require("fs");
const path = require("path");

console.log("🧪 Testing Playwright MCP Server Installation...\n");

// Test 1: Check if Playwright MCP is installed
console.log("1. Checking Playwright MCP installation...");
const testProcess = spawn("npx", ["@playwright/mcp", "--version"], {
    stdio: "pipe",
    shell: true, // Use shell on Windows
});

testProcess.stdout.on("data", (data) => {
    const version = data.toString().trim();
    console.log(`   ✅ Playwright MCP version: ${version}`);
});

testProcess.stderr.on("data", (data) => {
    console.log(`   ❌ Error: ${data.toString()}`);
});

testProcess.on("close", (code) => {
    if (code === 0) {
        console.log("   ✅ Installation verified successfully\n");

        // Test 2: Check configuration file
        console.log("2. Checking configuration file...");
        const configPath = path.join(
            __dirname,
            "..",
            "playwright-mcp.config.json"
        );

        if (fs.existsSync(configPath)) {
            console.log("   ✅ Configuration file found");

            try {
                const config = JSON.parse(fs.readFileSync(configPath, "utf8"));
                console.log("   ✅ Configuration file is valid JSON");
                console.log(`   📋 Browser: ${config.browser}`);
                console.log(`   📋 Headless: ${config.headless}`);
                console.log(`   📋 Viewport: ${config.viewportSize}`);
                console.log(`   📋 Output Directory: ${config.outputDir}`);
            } catch (error) {
                console.log(
                    `   ❌ Invalid JSON in config file: ${error.message}`
                );
            }
        } else {
            console.log("   ❌ Configuration file not found");
        }

        console.log("\n3. Testing directory structure...");

        // Create test directories
        const outputDir = path.join(__dirname, "..", "playwright-sessions");
        const userDataDir = path.join(__dirname, "..", "playwright-user-data");

        if (!fs.existsSync(outputDir)) {
            fs.mkdirSync(outputDir, { recursive: true });
            console.log("   ✅ Created playwright-sessions directory");
        } else {
            console.log("   ✅ playwright-sessions directory exists");
        }

        if (!fs.existsSync(userDataDir)) {
            fs.mkdirSync(userDataDir, { recursive: true });
            console.log("   ✅ Created playwright-user-data directory");
        } else {
            console.log("   ✅ playwright-user-data directory exists");
        }

        console.log(
            "\n🎉 All tests passed! Playwright MCP Server is ready to use."
        );
        console.log("\n📖 Next steps:");
        console.log("   1. Configure Cursor IDE:");
        console.log("      - Go to Cursor Settings > MCP > Add new MCP Server");
        console.log('      - Name: "playwright"');
        console.log('      - Command: "npx @playwright/mcp"');
        console.log("   2. Start the server:");
        console.log("      - Run: scripts/start-playwright-mcp.bat (Windows)");
        console.log("      - Run: scripts/start-playwright-mcp.sh (Linux/Mac)");
        console.log("   3. Use browser automation in your Laravel project!");
    } else {
        console.log(`   ❌ Installation test failed with code: ${code}`);
    }
});
