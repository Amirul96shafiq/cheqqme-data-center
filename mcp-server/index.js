const express = require("express");
const sqlite3 = require("sqlite3").verbose();
const { v4: uuidv4 } = require("uuid");
const bcrypt = require("bcrypt");
const app = express();
const port = 5000;

require("dotenv").config();

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
app.use((req, res, next) => {
    const key = req.headers["x-api-key"] || req.query.api_key;
    if (key !== apiKey) {
        return res.status(401).json({ error: "Invalid API key" });
    }
    next();
});

// Example MCP endpoint
app.post("/api/context", (req, res) => {
    // Example: fetch user by ID from SQLite
    const { userId } = req.body;
    db.get("SELECT * FROM users WHERE id = ?", [userId], (err, row) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        res.json({ context: row });
    });
});

// GET users
app.get("/api/users", (req, res) => {
    db.all("SELECT * FROM users", [], (err, rows) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        res.json({ data: rows });
    });
});

// GET all Important URLs
app.get("/api/important-urls", (req, res) => {
    db.all("SELECT * FROM important_urls", [], (err, rows) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        res.json({ data: rows });
    });
});

// GET all Tasks
app.get("/api/tasks", (req, res) => {
    db.all("SELECT * FROM tasks", [], (err, rows) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        res.json({ data: rows });
    });
});

// GET all Comments
app.get("/api/comments", (req, res) => {
    db.all("SELECT * FROM comments", [], (err, rows) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        res.json({ data: rows });
    });
});

// POST user
app.post("/api/users", async (req, res) => {
    const { name, email, password } = req.body;
    if (!name || !email || !password) {
        return res
            .status(400)
            .json({ error: "Name, email, and password are required." });
    }
    if (!password || password.trim() === "") {
        return res.status(400).json({
            error: "Name, email, and a non-empty password are required.",
        });
    }
    try {
        const hashedPassword = await bcrypt.hash(password, 10);
        // Replace $2b$ with $2y$ for Laravel compatibility
        const laravelHash = hashedPassword.replace("$2b$", "$2y$");
        db.run(
            "INSERT INTO users (name, email, password) VALUES (?, ?, ?)",
            [name, email, laravelHash],
            function (err) {
                if (err) {
                    return res.status(500).json({ error: err.message });
                }
                res.json({ id: this.lastID, name, email });
            }
        );
    } catch (err) {
        res.status(500).json({ error: "Password hashing failed." });
    }
});

app.listen(port, () => {
    console.log(`MCP server running at http://127.0.0.1:${port}/api`);
});
