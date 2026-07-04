# TOCTOU Race Condition in `mrmurphy_likes_apply()`

**Severity:** HIGH
**Lines:** 262–295
**File:** `inc/likes.php`

## Problem

The SELECT (`mrmurphy_has_liked` at line 262) and the subsequent INSERT/DELETE (lines 265/281) are not atomic. Two concurrent requests both see `$has = false`, both attempt INSERT — one succeeds, the other hits the UNIQUE constraint and silently fails. The cached count (`_mmb_like_count`) is not updated on the failed INSERT, so it becomes stale.

## Fix

Use `$wpdb->query()` with `INSERT ... ON DUPLICATE KEY` or wrap the operation in a DB transaction. For DELETE, check `$wpdb->rows_affected` instead of a separate SELECT first.
