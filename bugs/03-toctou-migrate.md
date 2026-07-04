# TOCTOU Race Condition in `mrmurphy_likes_migrate_identifier()`

**Severity:** HIGH
**Lines:** 218–244
**File:** `inc/likes.php`

## Problem

The migrate function loops over anonymous likes and for each row does SELECT (line 220) → UPDATE/DELETE (lines 231/234). No transaction wraps this. Two concurrent migration calls (or a migration concurrent with a toggle) can cause:

- An anonymous row gets updated to `user:<id>` while another process also updates it (double count)
- Row lost entirely due to overlapping delete-and-reinsert
- Cached count permanently diverges from actual row count

## Fix

Wrap the entire migration loop in a `$wpdb->query('START TRANSACTION') ... COMMIT` block, or use `INSERT ... ON DUPLICATE KEY UPDATE` to make the merge atomic per row.
