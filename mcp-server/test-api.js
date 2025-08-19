const http = require("http");

// Test configuration
const baseUrl = "http://localhost:5000";
const apiKey = "a3948589-2129-4a55-a5cc-7a6f841a8187";

// Helper function to make HTTP requests
function makeRequest(path, method = "GET", data = null, headers = {}) {
    return new Promise((resolve, reject) => {
        const finalHeaders = {
            "Content-Type": "application/json",
            "x-api-key": headers["x-api-key"] || apiKey,
            ...headers,
        };

        console.log(`🔍 Making ${method} request to ${path}`);
        console.log(`🔑 Headers:`, finalHeaders);

        const options = {
            hostname: "localhost",
            port: 5000,
            path: path,
            method: method,
            headers: finalHeaders,
        };

        const req = http.request(options, (res) => {
            let body = "";
            res.on("data", (chunk) => {
                body += chunk;
            });
            res.on("end", () => {
                try {
                    const response = JSON.parse(body);
                    resolve({ status: res.statusCode, data: response });
                } catch (e) {
                    resolve({ status: res.statusCode, data: body });
                }
            });
        });

        req.on("error", (err) => {
            reject(err);
        });

        if (data) {
            req.write(JSON.stringify(data));
        }
        req.end();
    });
}

// Test functions
async function testHealth() {
    console.log("\n🔍 Testing Health Endpoint...");
    try {
        const response = await makeRequest("/health", "GET", null, {});
        console.log("✅ Health check:", response.data);
    } catch (error) {
        console.log("❌ Health check failed:", error.message);
    }
}

async function testUsersAPI() {
    console.log("\n👥 Testing Users API...");

    try {
        // List users
        console.log("📋 Listing users...");
        const listResponse = await makeRequest("/api/users", "GET");
        console.log("✅ List users:", listResponse.status, listResponse.data);

        // Create user
        console.log("➕ Creating user...");
        const userData = {
            name: "Test User",
            email: "test@example.com",
            password: "password123",
        };
        const createResponse = await makeRequest(
            "/api/users",
            "POST",
            userData
        );
        console.log(
            "✅ Create user:",
            createResponse.status,
            createResponse.data
        );

        if (createResponse.data.data && createResponse.data.data.id) {
            const userId = createResponse.data.data.id;

            // Get user by ID
            console.log(`👤 Getting user ${userId}...`);
            const getResponse = await makeRequest(
                `/api/users/${userId}`,
                "GET"
            );
            console.log("✅ Get user:", getResponse.status, getResponse.data);

            // Update user
            console.log(`✏️ Updating user ${userId}...`);
            const updateData = {
                name: "Updated User",
                email: "updated@example.com",
            };
            const updateResponse = await makeRequest(
                `/api/users/${userId}`,
                "PUT",
                updateData
            );
            console.log(
                "✅ Update user:",
                updateResponse.status,
                updateResponse.data
            );

            // Delete user
            console.log(`🗑️ Deleting user ${userId}...`);
            const deleteResponse = await makeRequest(
                `/api/users/${userId}`,
                "DELETE"
            );
            console.log(
                "✅ Delete user:",
                deleteResponse.status,
                deleteResponse.data
            );
        }
    } catch (error) {
        console.log("❌ Users API test failed:", error.message);
    }
}

async function testTasksAPI() {
    console.log("\n📋 Testing Tasks API...");

    try {
        // List tasks
        console.log("📋 Listing tasks...");
        const listResponse = await makeRequest("/api/tasks", "GET");
        console.log("✅ List tasks:", listResponse.status, listResponse.data);

        // Create task
        console.log("➕ Creating task...");
        const taskData = {
            title: "Test Task",
            description: "Test Description",
            status: "pending",
            user_id: null,
        };
        const createResponse = await makeRequest(
            "/api/tasks",
            "POST",
            taskData
        );
        console.log(
            "✅ Create task:",
            createResponse.status,
            createResponse.data
        );

        if (createResponse.data.data && createResponse.data.data.id) {
            const taskId = createResponse.data.data.id;

            // Get task by ID
            console.log(`📋 Getting task ${taskId}...`);
            const getResponse = await makeRequest(
                `/api/tasks/${taskId}`,
                "GET"
            );
            console.log("✅ Get task:", getResponse.status, getResponse.data);

            // Update task
            console.log(`✏️ Updating task ${taskId}...`);
            const updateData = {
                title: "Updated Task",
                description: "Updated Description",
                status: "in_progress",
                user_id: null,
            };
            const updateResponse = await makeRequest(
                `/api/tasks/${taskId}`,
                "PUT",
                updateData
            );
            console.log(
                "✅ Update task:",
                updateResponse.status,
                updateResponse.data
            );

            // Delete task
            console.log(`🗑️ Deleting task ${taskId}...`);
            const deleteResponse = await makeRequest(
                `/api/tasks/${taskId}`,
                "DELETE"
            );
            console.log(
                "✅ Delete task:",
                deleteResponse.status,
                deleteResponse.data
            );
        }
    } catch (error) {
        console.log("❌ Tasks API test failed:", error.message);
    }
}

async function testCommentsAPI() {
    console.log("\n💬 Testing Comments API...");

    try {
        // List comments
        console.log("📋 Listing comments...");
        const listResponse = await makeRequest("/api/comments", "GET");
        console.log(
            "✅ List comments:",
            listResponse.status,
            listResponse.data
        );

        // Create a task first for the comment
        console.log("➕ Creating task for comment...");
        const taskData = {
            title: "Task for Comment",
            description: "Task Description",
            status: "pending",
            user_id: null,
        };
        const taskResponse = await makeRequest("/api/tasks", "POST", taskData);

        if (taskResponse.data.data && taskResponse.data.data.id) {
            const taskId = taskResponse.data.data.id;

            // Create comment
            console.log("➕ Creating comment...");
            const commentData = {
                content: "Test Comment",
                task_id: taskId,
                user_id: null,
            };
            const createResponse = await makeRequest(
                "/api/comments",
                "POST",
                commentData
            );
            console.log(
                "✅ Create comment:",
                createResponse.status,
                createResponse.data
            );

            if (createResponse.data.data && createResponse.data.data.id) {
                const commentId = createResponse.data.data.id;

                // Get comment by ID
                console.log(`💬 Getting comment ${commentId}...`);
                const getResponse = await makeRequest(
                    `/api/comments/${commentId}`,
                    "GET"
                );
                console.log(
                    "✅ Get comment:",
                    getResponse.status,
                    getResponse.data
                );

                // Delete comment
                console.log(`🗑️ Deleting comment ${commentId}...`);
                const deleteResponse = await makeRequest(
                    `/api/comments/${commentId}`,
                    "DELETE"
                );
                console.log(
                    "✅ Delete comment:",
                    deleteResponse.status,
                    deleteResponse.data
                );
            }

            // Clean up task
            console.log(`🗑️ Cleaning up task ${taskId}...`);
            await makeRequest(`/api/tasks/${taskId}`, "DELETE");
        }
    } catch (error) {
        console.log("❌ Comments API test failed:", error.message);
    }
}

async function testImportantUrlsAPI() {
    console.log("\n🔗 Testing Important URLs API...");

    try {
        // List important URLs
        console.log("📋 Listing important URLs...");
        const listResponse = await makeRequest("/api/important-urls", "GET");
        console.log(
            "✅ List important URLs:",
            listResponse.status,
            listResponse.data
        );

        // Create important URL
        console.log("➕ Creating important URL...");
        const urlData = {
            title: "Test URL",
            url: "https://example.com",
            description: "Test Description",
        };
        const createResponse = await makeRequest(
            "/api/important-urls",
            "POST",
            urlData
        );
        console.log(
            "✅ Create important URL:",
            createResponse.status,
            createResponse.data
        );

        if (createResponse.data.data && createResponse.data.data.id) {
            const urlId = createResponse.data.data.id;

            // Get important URL by ID
            console.log(`🔗 Getting important URL ${urlId}...`);
            const getResponse = await makeRequest(
                `/api/important-urls/${urlId}`,
                "GET"
            );
            console.log(
                "✅ Get important URL:",
                getResponse.status,
                getResponse.data
            );

            // Update important URL
            console.log(`✏️ Updating important URL ${urlId}...`);
            const updateData = {
                title: "Updated URL",
                url: "https://updated-example.com",
                description: "Updated Description",
            };
            const updateResponse = await makeRequest(
                `/api/important-urls/${urlId}`,
                "PUT",
                updateData
            );
            console.log(
                "✅ Update important URL:",
                updateResponse.status,
                updateResponse.data
            );

            // Delete important URL
            console.log(`🗑️ Deleting important URL ${urlId}...`);
            const deleteResponse = await makeRequest(
                `/api/important-urls/${urlId}`,
                "DELETE"
            );
            console.log(
                "✅ Delete important URL:",
                deleteResponse.status,
                deleteResponse.data
            );
        }
    } catch (error) {
        console.log("❌ Important URLs API test failed:", error.message);
    }
}

async function testAuthentication() {
    console.log("\n🔐 Testing Authentication...");

    try {
        // Test without API key
        console.log("🚫 Testing without API key...");
        const noKeyResponse = await makeRequest("/api/users", "GET", null, {
            "x-api-key": "",
        });
        console.log(
            "✅ No API key response:",
            noKeyResponse.status,
            noKeyResponse.data
        );

        // Test with invalid API key
        console.log("❌ Testing with invalid API key...");
        const invalidKeyResponse = await makeRequest(
            "/api/users",
            "GET",
            null,
            { "x-api-key": "invalid-key" }
        );
        console.log(
            "✅ Invalid API key response:",
            invalidKeyResponse.status,
            invalidKeyResponse.data
        );
    } catch (error) {
        console.log("❌ Authentication test failed:", error.message);
    }
}

// Main test runner
async function runTests() {
    console.log("🚀 Starting MCP Server API Tests...");
    console.log("📍 Server URL:", baseUrl);
    console.log("🔑 API Key:", apiKey);

    try {
        await testHealth();
        await testAuthentication();
        await testUsersAPI();
        await testTasksAPI();
        await testCommentsAPI();
        await testImportantUrlsAPI();

        console.log("\n🎉 All tests completed!");
    } catch (error) {
        console.log("\n💥 Test suite failed:", error.message);
    }
}

// Run tests
runTests();
