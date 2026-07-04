# Error-Logged Content May Include Sensitive Schema Info

**Severity:** LOW
**Lines:** 276, 291
**File:** `inc/likes.php`

## Problem

`$wpdb->last_error` is written directly to `error_log` on insert/delete failure. Table names (`wp_mmb_likes`) and constraint violation details are logged. If error logs are exposed or shipped to a third-party service, this is a minor schema-information leak.

## Fix

Log only the error code or a generic message ("DB write failed for post N") instead of the raw `$wpdb->last_error` string, which may contain schema details.
