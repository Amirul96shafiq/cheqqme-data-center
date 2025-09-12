#!/bin/bash

# Playwright MCP Server Startup Script
# This script starts the Playwright MCP server with project-specific configuration

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Starting Playwright MCP Server for CheqqMe Data Center...${NC}"

# Check if config file exists
if [ ! -f "playwright-mcp.config.json" ]; then
    echo -e "${RED}Error: playwright-mcp.config.json not found!${NC}"
    echo -e "${YELLOW}Please ensure the config file is in the project root.${NC}"
    exit 1
fi

# Create output directories if they don't exist
mkdir -p playwright-sessions
mkdir -p playwright-user-data

# Start the server with configuration
echo -e "${GREEN}Configuration:${NC}"
echo -e "  Browser: Chrome (headless)"
echo -e "  Viewport: 1280x720"
echo -e "  Action Timeout: 5s"
echo -e "  Navigation Timeout: 60s"
echo -e "  Output Directory: ./playwright-sessions"
echo -e "  User Data Directory: ./playwright-user-data"
echo ""

echo -e "${YELLOW}Starting server...${NC}"
echo -e "${YELLOW}Press Ctrl+C to stop the server${NC}"
echo ""

# Start the Playwright MCP server
npx @playwright/mcp \
    --config playwright-mcp.config.json \
    --browser chrome \
    --headless \
    --timeout-action 5000 \
    --timeout-navigation 60000 \
    --viewport-size "1280,720" \
    --output-dir "./playwright-sessions" \
    --user-data-dir "./playwright-user-data" \
    --save-session \
    --save-trace


