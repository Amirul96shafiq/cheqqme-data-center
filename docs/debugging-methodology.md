# Debugging Methodology for CheQQme Data Center

## Overview

This document outlines a systematic approach for debugging complex issues, particularly those involving multiple components, data flow, and user interactions. It includes specific guidelines for console log management during the debugging and cleanup process.

## Console Log Management

### During Debugging/Testing/Troubleshooting

When checking, fixing, troubleshooting, or testing bug fixes, **temporarily enable relevant console logs** by uncommenting them:

```javascript
// Example: Temporarily enable specific debugging logs
console.log("Debug: User presence status changed:", user); // Uncomment for testing
console.log("Debug: Conversation loading:", conversationId); // Uncomment for testing
console.log("Debug: File upload processing:", fileData); // Uncomment for testing
console.log("Debug: Livewire event triggered:", event); // Uncomment for testing
console.log("Debug: MutationObserver detected changes"); // Uncomment for testing
```

### After Issues are Resolved

When user says **"all good can clean up or refactor"**, include **disabling console logs** (commenting them out) as part of the cleanup process:

**IMPORTANT**: Always **comment out** console logs instead of deleting them to preserve debugging capability for future use.

```javascript
// Comment out debug logs after testing - DO NOT DELETE
// console.log("Debug: User presence status changed:", user);
// console.log("Debug: Conversation loading:", conversationId);
// console.log("Debug: File upload processing:", fileData);
// console.log("Debug: Livewire event triggered:", event);
// console.log("Debug: MutationObserver detected changes");
```

**Why comment instead of delete?**

-   Preserves debugging infrastructure for future troubleshooting
-   Easy to re-enable by uncommenting when needed
-   Maintains context of what was being debugged
-   No need to recreate debugging code from scratch

### Log Categories to Enable During Debugging

-   **Presence Status**: User join/leave events, status changes, channel connections
-   **Chatbot**: Session initialization, conversation loading, message handling
-   **File Upload**: Drag-drop detection, file processing, upload status
-   **Livewire**: DOM updates, mutation observer events, theme changes
-   **Network**: API requests, response handling, authentication
-   **Form Processing**: Field validation, auto-fill operations, submission status

### Important Notes

-   **Always preserve `console.error()` and `console.warn()`** - these are critical for error tracking
-   **Only disable informational `console.log()` statements** for debugging/tracing
-   **Re-enable logs temporarily** when testing fixes or investigating issues
-   **Clean up logs** as part of the final refactoring step after confirming fixes work
-   **Document in your response** when you've uncommented logs for debugging and remind user to clean up when done

---

## The "Layer-by-Layer" Debugging Approach

### 1. **Start with User Observation**

-   **What**: User reports specific behavior (e.g., "individual mentions not highlighted")
-   **Why**: User observations provide the most accurate description of the actual problem
-   **Action**: Document the exact issue with screenshots/descriptions

### 2. **Verify Current State**

-   **What**: Check the actual data in the database/system
-   **Why**: Distinguish between "old data" vs "new data" issues
-   **Action**: Use `tinker` commands to inspect recent records
-   **Example**:
    ```bash
    php artisan tinker --execute="
    \$recentComments = \App\Models\Comment::latest()->take(3)->get(['id', 'comment', 'mentions']);
    foreach(\$recentComments as \$comment) {
        echo 'Mentions: ' . json_encode(\$comment->mentions) . PHP_EOL;
    }
    "
    ```

### 3. **Test Individual Components**

-   **What**: Test each component in isolation
-   **Why**: Isolate where the problem occurs in the data flow
-   **Action**: Create test cases for each method/function
-   **Example**:
    ```bash
    php artisan tinker --execute="
    \$mentions = \App\Models\Comment::extractMentions('@Everyone @User');
    echo 'Extracted: ' . json_encode(\$mentions) . PHP_EOL;
    "
    ```

### 4. **Trace Data Flow**

-   **What**: Follow data through the entire pipeline
-   **Why**: Identify where data gets lost or corrupted
-   **Flow Example**:
    1. User types "@Everyone @User"
    2. Frontend sends to backend
    3. `extractMentions()` processes text
    4. `addComment()` merges mentions
    5. Database stores mentions
    6. `getRenderedCommentAttribute()` renders HTML
    7. Frontend displays highlighted text

### 5. **Identify the Root Cause**

-   **What**: Find the exact line/condition causing the issue
-   **Why**: Fix the source, not just symptoms
-   **Common Issues**:
    -   Early returns skipping processing
    -   Override logic instead of merge logic
    -   Missing null checks
    -   Incorrect data types

### 6. **Fix Systematically**

-   **What**: Make minimal, targeted changes
-   **Why**: Avoid introducing new bugs
-   **Approach**:
    -   Fix the root cause
    -   Test the fix in isolation
    -   Test the complete flow
    -   Verify with real data

### 7. **Verify the Complete Solution**

-   **What**: Test the entire user journey
-   **Why**: Ensure the fix works end-to-end
-   **Action**: Create test scenarios that match user behavior

## Debugging Tools and Commands

### Database Inspection

```bash
# Check recent records
php artisan tinker --execute="
\$recent = \App\Models\Comment::latest()->take(5)->get(['id', 'comment', 'mentions', 'created_at']);
foreach(\$recent as \$c) {
    echo 'ID: ' . \$c->id . ' | Mentions: ' . json_encode(\$c->mentions) . PHP_EOL;
}
"
```

### Method Testing

```bash
# Test individual methods
php artisan tinker --execute="
\$result = \App\Models\Comment::extractMentions('@Everyone @User test');
echo 'Result: ' . json_encode(\$result) . PHP_EOL;
"
```

### Rendering Testing

```bash
# Test complete rendering
php artisan tinker --execute="
\$comment = new \App\Models\Comment();
\$comment->comment = '<p>@Everyone @User test</p>';
\$comment->mentions = ['@Everyone', 1];
\$rendered = \$comment->rendered_comment;
echo 'Rendered: ' . \$rendered . PHP_EOL;
"
```

## Common Debugging Patterns

### Pattern 1: Early Return Issues

**Problem**: Method returns early, skipping important processing
**Solution**: Restructure logic to process all cases before returning

### Pattern 2: Override vs Merge

**Problem**: New data overwrites existing data instead of merging
**Solution**: Use `array_merge()` instead of direct assignment

### Pattern 3: Data Type Mismatches

**Problem**: String vs Integer comparisons fail
**Solution**: Ensure consistent data types throughout the flow

### Pattern 4: Missing Null Checks

**Problem**: Code assumes data exists when it might be null
**Solution**: Add defensive checks and fallbacks

## Best Practices

1. **Always test with real data** - Don't rely on assumptions
2. **Use systematic logging** - Temporarily uncomment debug logs at key points
3. **Test incrementally** - Fix one issue at a time
4. **Verify end-to-end** - Test the complete user journey
5. **Document findings** - Keep track of what was tested and what worked
6. **Clean up after** - **Comment out** debug logs when user confirms fixes work (never delete them)
7. **Preserve debugging infrastructure** - Keep commented logs for future troubleshooting needs

## Example: The Mention Highlighting Fix

### Problem

Individual user mentions not highlighted when "@Everyone" is present

### Debugging Process

1. **User Observation**: "@Amirul Shafiq Harun" not highlighted in image
2. **Verify State**: Checked recent comments - only `["@Everyone"]` in mentions
3. **Test Components**:
    - `extractMentions()` returned only `["@Everyone"]`
    - Rendering worked when both mentions present
4. **Root Cause**: Early return in `extractMentions()` skipped individual user processing
5. **Fix**: Restructured logic to process both @Everyone and individual mentions
6. **Verify**: Tested complete flow with real data

### Key Lesson

The issue wasn't in the rendering or merging logic - it was in the initial data extraction. This highlights the importance of testing each layer independently.

## When to Use This Approach

-   Complex user interactions with multiple steps
-   Data flow issues across multiple components
-   Issues that work in isolation but fail in combination
-   Problems where the symptom doesn't clearly point to the cause
-   Issues involving both frontend and backend components

## AI Assistant Workflow

### When Debugging Issues:

1. **Identify relevant log files** that might contain debugging information
2. **Temporarily uncomment console.log statements** related to the issue
3. **Document which logs were enabled** in your response
4. **Test the fix** with logs enabled
5. **When user confirms fix works**, remind them to clean up logs

### When User Says "All Good":

1. **Include log cleanup** as part of refactoring tasks
2. **Comment out debug logs** that were temporarily enabled (never delete them)
3. **Ensure critical error logs remain** untouched
4. **Verify no debug noise** remains in console
5. **Preserve debugging infrastructure** for future troubleshooting needs

## Remember

> "The bug is usually not where you think it is. Test systematically, not randomly. Enable logs when debugging, clean them up when done."
