const express = require("express");
const sqlite3 = require("sqlite3").verbose();
const { v4: uuidv4 } = require("uuid");
const bcrypt = require("bcrypt");
const cors = require("cors");
const app = express();
const port = 5000;

require("dotenv").config();

// Middleware
app.use(cors());
app.use(express.json());

// Path to your Laravel SQLite database
const DB_PATH = "../database/database.sqlite";
const db = new sqlite3.Database(DB_PATH, (err) => {
    if (err) {
        console.error("Failed to connect to SQLite:", err.message);
    } else {
        console.log("Connected to SQLite database.");
    }
});

// In-memory API key store (for demo)
let apiKey = process.env.MCP_API_KEY || "default-key";
console.log("Using MCP API Key:", apiKey);

// Middleware to check API key
app.use("/api", (req, res, next) => {
    const key = req.headers["x-api-key"] || req.query.api_key;
    console.log(`🔐 API Key check for ${req.method} ${req.path}`);
    console.log(`🔑 Received key: "${key}", Expected: "${apiKey}"`);
    console.log(`📋 All headers:`, req.headers);

    if (key !== apiKey) {
        console.log(`❌ Invalid API key`);
        return res.status(401).json({ error: "Invalid API key" });
    }
    console.log(`✅ Valid API key`);
    next();
});

// Health check endpoint (no auth required)
app.get("/health", (req, res) => {
    res.json({ status: "OK", timestamp: new Date().toISOString() });
});

// API Documentation endpoint
app.get("/", (req, res) => {
    res.json({
        message: "Laravel MCP API Server",
        version: "1.0.0",
        endpoints: {
            health: "GET /health",
            context: "POST /api/context",

            tasks: {
                list: "GET /api/tasks",
                create: "POST /api/tasks",
                get: "GET /api/tasks/:id",
                update: "PUT /api/tasks/:id",
                delete: "DELETE /api/tasks/:id",
            },
            comments: {
                list: "GET /api/comments",
                create: "POST /api/comments",
                get: "GET /api/comments/:id",
                delete: "DELETE /api/comments/:id",
            },
            clients: {
                list: "GET /api/clients",
                create: "POST /api/clients",
                get: "GET /api/clients/:id",
                update: "PUT /api/clients/:id",
                delete: "DELETE /api/clients/:id",
            },
            projects: {
                list: "GET /api/projects",
                create: "POST /api/projects",
                get: "GET /api/projects/:id",
                update: "PUT /api/projects/:id",
                delete: "DELETE /api/projects/:id",
            },
            documents: {
                list: "GET /api/documents",
                create: "POST /api/documents",
                get: "GET /api/documents/:id",
                update: "PUT /api/documents/:id",
                delete: "DELETE /api/documents/:id",
            },
            importantUrls: {
                list: "GET /api/important-urls",
                create: "POST /api/important-urls",
                get: "GET /api/important-urls/:id",
                update: "PUT /api/important-urls/:id",
                delete: "DELETE /api/important-urls/:id",
            },
            phone_numbers: {
                list: "GET /api/phone-numbers",
                create: "POST /api/phone-numbers",
                get: "GET /api/phone-numbers/:id",
                update: "PUT /api/phone-numbers/:id",
                delete: "DELETE /api/phone-numbers/:id",
            },
            users: {
                list: "GET /api/users",
                create: "POST /api/users",
                get: "GET /api/users/:id",
                update: "PUT /api/users/:id",
                delete: "DELETE /api/users/:id",
            },
            activitylogs: {
                list: "GET /api/activitylogs",
                create: "POST /api/activitylogs",
                get: "GET /api/activitylogs/:id",
                update: "PUT /api/activitylogs/:id",
                delete: "DELETE /api/activitylogs/:id",
            },
        },
        authentication:
            "Include 'x-api-key' header or 'api_key' query parameter",
    });
});

// Example MCP endpoint
app.post("/api/context", (req, res) => {
    const { userId } = req.body;
    db.get("SELECT * FROM users WHERE id = ?", [userId], (err, row) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        res.json({ context: row });
    });
});

// USERS ENDPOINTS
app.get("/api/users", (req, res) => {
    db.all(
        "SELECT id, name, email, created_at, updated_at FROM users",
        [],
        (err, rows) => {
            if (err) {
                return res.status(500).json({ error: err.message });
            }
            res.json({ data: rows });
        }
    );
});

app.get("/api/users/:id", (req, res) => {
    const { id } = req.params;
    db.get(
        "SELECT id, name, email, created_at, updated_at FROM users WHERE id = ?",
        [id],
        (err, row) => {
            if (err) {
                return res.status(500).json({ error: err.message });
            }
            if (!row) {
                return res.status(404).json({ error: "User not found" });
            }
            res.json({ data: row });
        }
    );
});

app.post("/api/users", async (req, res) => {
    const { name, email, password } = req.body;

    if (!name || !email || !password) {
        return res.status(400).json({
            error: "Name, email, and password are required.",
        });
    }

    if (password.trim() === "") {
        return res.status(400).json({
            error: "Password cannot be empty.",
        });
    }

    try {
        const hashedPassword = await bcrypt.hash(password, 10);
        const laravelHash = hashedPassword.replace("$2b$", "$2y$");
        const now = new Date().toISOString();

        db.run(
            "INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, ?, ?)",
            [name, email, laravelHash, now, now],
            function (err) {
                if (err) {
                    return res.status(500).json({ error: err.message });
                }
                res.status(201).json({
                    data: { id: this.lastID, name, email, created_at: now },
                });
            }
        );
    } catch (err) {
        res.status(500).json({ error: "Password hashing failed." });
    }
});

app.put("/api/users/:id", (req, res) => {
    const { id } = req.params;
    const { name, email } = req.body;
    const now = new Date().toISOString();

    if (!name || !email) {
        return res.status(400).json({
            error: "Name and email are required.",
        });
    }

    db.run(
        "UPDATE users SET name = ?, email = ?, updated_at = ? WHERE id = ?",
        [name, email, now, id],
        function (err) {
            if (err) {
                return res.status(500).json({ error: err.message });
            }
            if (this.changes === 0) {
                return res.status(404).json({ error: "User not found" });
            }
            res.json({ data: { id, name, email, updated_at: now } });
        }
    );
});

app.delete("/api/users/:id", (req, res) => {
    const { id } = req.params;

    db.run("DELETE FROM users WHERE id = ?", [id], function (err) {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        if (this.changes === 0) {
            return res.status(404).json({ error: "User not found" });
        }
        res.json({ message: "User deleted successfully" });
    });
});

// TASKS ENDPOINTS
app.get("/api/tasks", (req, res) => {
    db.all("SELECT * FROM tasks", [], (err, rows) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        res.json({ data: rows });
    });
});

app.get("/api/tasks/:id", (req, res) => {
    const { id } = req.params;
    db.get("SELECT * FROM tasks WHERE id = ?", [id], (err, row) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        if (!row) {
            return res.status(404).json({ error: "Task not found" });
        }
        res.json({ data: row });
    });
});

app.post("/api/tasks", (req, res) => {
    const { title, description, status, user_id } = req.body;
    const now = new Date().toISOString();

    if (!title) {
        return res.status(400).json({ error: "Title is required." });
    }

    db.run(
        "INSERT INTO tasks (title, description, status, user_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)",
        [
            title,
            description || null,
            status || "pending",
            user_id || null,
            now,
            now,
        ],
        function (err) {
            if (err) {
                return res.status(500).json({ error: err.message });
            }
            res.status(201).json({
                data: {
                    id: this.lastID,
                    title,
                    description,
                    status: status || "pending",
                    user_id: user_id || null,
                    created_at: now,
                },
            });
        }
    );
});

app.put("/api/tasks/:id", (req, res) => {
    const { id } = req.params;
    const { title, description, status, user_id } = req.body;
    const now = new Date().toISOString();

    if (!title) {
        return res.status(400).json({ error: "Title is required." });
    }

    db.run(
        "UPDATE tasks SET title = ?, description = ?, status = ?, user_id = ?, updated_at = ? WHERE id = ?",
        [title, description, status, user_id, now, id],
        function (err) {
            if (err) {
                return res.status(500).json({ error: err.message });
            }
            if (this.changes === 0) {
                return res.status(404).json({ error: "Task not found" });
            }
            res.json({
                data: {
                    id,
                    title,
                    description,
                    status,
                    user_id,
                    updated_at: now,
                },
            });
        }
    );
});

app.delete("/api/tasks/:id", (req, res) => {
    const { id } = req.params;

    db.run("DELETE FROM tasks WHERE id = ?", [id], function (err) {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        if (this.changes === 0) {
            return res.status(404).json({ error: "Task not found" });
        }
        res.json({ message: "Task deleted successfully" });
    });
});

// COMMENTS ENDPOINTS
app.get("/api/comments", (req, res) => {
    db.all("SELECT * FROM comments", [], (err, rows) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        res.json({ data: rows });
    });
});

app.get("/api/comments/:id", (req, res) => {
    const { id } = req.params;
    db.get("SELECT * FROM comments WHERE id = ?", [id], (err, row) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        if (!row) {
            return res.status(404).json({ error: "Comment not found" });
        }
        res.json({ data: row });
    });
});

app.post("/api/comments", (req, res) => {
    const { content, task_id, user_id } = req.body;
    const now = new Date().toISOString();

    if (!content || !task_id) {
        return res.status(400).json({
            error: "Content and task_id are required.",
        });
    }

    db.run(
        "INSERT INTO comments (content, task_id, user_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?)",
        [content, task_id, user_id || null, now, now],
        function (err) {
            if (err) {
                return res.status(500).json({ error: err.message });
            }
            res.status(201).json({
                data: {
                    id: this.lastID,
                    content,
                    task_id,
                    user_id: user_id || null,
                    created_at: now,
                },
            });
        }
    );
});

app.delete("/api/comments/:id", (req, res) => {
    const { id } = req.params;

    db.run("DELETE FROM comments WHERE id = ?", [id], function (err) {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        if (this.changes === 0) {
            return res.status(404).json({ error: "Comment not found" });
        }
        res.json({ message: "Comment deleted successfully" });
    });
});

// PHONE NUMBERS ENDPOINTS
app.get("/api/phone-numbers", (req, res) => {
    db.all("SELECT * FROM phone_numbers", [], (err, rows) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        res.json({ data: rows });
    });
});

app.get("/api/phone-numbers/:id", (req, res) => {
    const { id } = req.params;
    db.get("SELECT * FROM phone_numbers WHERE id = ?", [id], (err, row) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        if (!row) {
            return res.status(404).json({ error: "Phone number not found" });
        }
        res.json({ data: row });
    });
});

app.post("/api/phone-numbers", (req, res) => {
    const { name, phone_number, description } = req.body;
    const now = new Date().toISOString();

    if (!name || !phone_number) {
        return res.status(400).json({
            error: "Name and phone number are required.",
        });
    }

    db.run(
        "INSERT INTO phone_numbers (name, phone_number, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?)",
        [name, phone_number, description || null, now, now],
        function (err) {
            if (err) {
                return res.status(500).json({ error: err.message });
            }
            res.status(201).json({
                data: {
                    id: this.lastID,
                    name,
                    phone_number,
                    description,
                    created_at: now,
                },
            });
        }
    );
});

app.put("/api/phone-numbers/:id", (req, res) => {
    const { id } = req.params;
    const { name, phone_number, description } = req.body;
    const now = new Date().toISOString();

    if (!name || !phone_number) {
        return res.status(400).json({
            error: "Name and phone number are required.",
        });
    }

    db.run(
        "UPDATE phone_numbers SET name = ?, phone_number = ?, description = ?, updated_at = ? WHERE id = ?",
        [name, phone_number, description, now, id],
        function (err) {
            if (err) {
                return res.status(500).json({ error: err.message });
            }
            if (this.changes === 0) {
                return res.status(404).json({ error: "Phone number not found" });
            }
            res.json({
                data: {
                    id,
                    name,
                    phone_number,
                    description,
                    updated_at: now,
                },
            });
        }
    );
});

app.delete("/api/phone-numbers/:id", (req, res) => {
    const { id } = req.params;

    db.run("DELETE FROM phone_numbers WHERE id = ?", [id], function (err) {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        if (this.changes === 0) {
            return res.status(404).json({ error: "Phone number not found" });
        }
        res.json({ message: "Phone number deleted successfully" });
    });
});

// IMPORTANT URLS ENDPOINTS
app.get("/api/important-urls", (req, res) => {
    db.all("SELECT * FROM important_urls", [], (err, rows) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        res.json({ data: rows });
    });
});

app.get("/api/important-urls/:id", (req, res) => {
    const { id } = req.params;
    db.get("SELECT * FROM important_urls WHERE id = ?", [id], (err, row) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        if (!row) {
            return res.status(404).json({ error: "Important URL not found" });
        }
        res.json({ data: row });
    });
});

app.post("/api/important-urls", (req, res) => {
    const { title, url, description } = req.body;
    const now = new Date().toISOString();

    if (!title || !url) {
        return res.status(400).json({
            error: "Title and URL are required.",
        });
    }

    db.run(
        "INSERT INTO important_urls (title, url, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?)",
        [title, url, description || null, now, now],
        function (err) {
            if (err) {
                return res.status(500).json({ error: err.message });
            }
            res.status(201).json({
                data: {
                    id: this.lastID,
                    title,
                    url,
                    description,
                    created_at: now,
                },
            });
        }
    );
});

app.put("/api/important-urls/:id", (req, res) => {
    const { id } = req.params;
    const { title, url, description } = req.body;
    const now = new Date().toISOString();

    if (!title || !url) {
        return res.status(400).json({
            error: "Title and URL are required.",
        });
    }

    db.run(
        "UPDATE important_urls SET title = ?, url = ?, description = ?, updated_at = ? WHERE id = ?",
        [title, url, description, now, id],
        function (err) {
            if (err) {
                return res.status(500).json({ error: err.message });
            }
            if (this.changes === 0) {
                return res
                    .status(404)
                    .json({ error: "Important URL not found" });
            }
            res.json({
                data: {
                    id,
                    title,
                    url,
                    description,
                    updated_at: now,
                },
            });
        }
    );
});

app.delete("/api/important-urls/:id", (req, res) => {
    const { id } = req.params;

    db.run("DELETE FROM important_urls WHERE id = ?", [id], function (err) {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        if (this.changes === 0) {
            return res.status(404).json({ error: "Important URL not found" });
        }
        res.json({ message: "Important URL deleted successfully" });
    });
});

// Error handling middleware
app.use((err, req, res, next) => {
    console.error(err.stack);
    res.status(500).json({ error: "Something went wrong!" });
});

// 404 handler
app.use((req, res) => {
    res.status(404).json({ error: "Endpoint not found" });
});

// Graceful shutdown
process.on("SIGINT", () => {
    console.log("\nShutting down server...");
    db.close((err) => {
        if (err) {
            console.error("Error closing database:", err.message);
        } else {
            console.log("Database connection closed.");
        }
    });
    process.exit(0);
});

app.listen(port, () => {
    console.log(`MCP server running at http://127.0.0.1:${port}`);
    console.log(`API documentation available at http://127.0.0.1:${port}`);
    console.log(`Health check available at http://127.0.0.1:${port}/health`);
});
