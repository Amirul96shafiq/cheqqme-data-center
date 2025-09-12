@echo off
REM Playwright MCP Server Startup Script for Windows
REM This script starts the Playwright MCP server with project-specific configuration

echo Starting Playwright MCP Server for CheQQme Data Center...

REM Check if config file exists
if not exist "playwright-mcp.config.json" (
    echo Error: playwright-mcp.config.json not found!
    echo Please ensure the config file is in the project root.
    pause
    exit /b 1
)

REM Create output directories if they don't exist
if not exist "playwright-sessions" mkdir playwright-sessions
if not exist "playwright-user-data" mkdir playwright-user-data

REM Display configuration
echo Configuration:
echo   Browser: Chrome (headless)
echo   Viewport: 1280x720
echo   Action Timeout: 5s
echo   Navigation Timeout: 60s
echo   Output Directory: ./playwright-sessions
echo   User Data Directory: ./playwright-user-data
echo.

echo Starting server...
echo Press Ctrl+C to stop the server
echo.

REM Start the Playwright MCP server
npx @playwright/mcp ^
    --config playwright-mcp.config.json ^
    --browser chrome ^
    --headless ^
    --timeout-action 5000 ^
    --timeout-navigation 60000 ^
    --viewport-size "1280,720" ^
    --output-dir "./playwright-sessions" ^
    --user-data-dir "./playwright-user-data" ^
    --save-session ^
    --save-trace

pause


