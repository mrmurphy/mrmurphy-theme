# No Migration Completion Guard

**Severity:** MEDIUM
**Lines:** 197–206, 389
**File:** `inc/likes.php`

## Problem

`mrmurphy_likes_migrate_identifier()` is called on *every* toggle request. There is no option/flag tracking whether migration is complete. After all anonymous likes have been migrated, every subsequent toggle still issues a full `SELECT` for `client_id` against the likes table. An attacker can replay an old `client_id` indefinitely, causing this query on every request.

## Fix

After migration finds zero rows, set a short-lived transient or user meta flag to skip the query on subsequent requests. Or check a flag before calling migrate.
