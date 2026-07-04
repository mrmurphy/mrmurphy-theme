# No Database Transaction Isolation

**Severity:** MEDIUM
**Lines:** 258–301, 197–245
**File:** `inc/likes.php`

## Problem

Neither `mrmurphy_likes_apply` nor `mrmurphy_likes_migrate_identifier` opens a DB transaction. Every read-then-write sequence is subject to concurrent corruption. With multiple users toggling likes simultaneously, count drift is inevitable.

## Fix

Wrap read-modify-write sequences in `$wpdb->query('START TRANSACTION')` / `COMMIT` blocks. This is especially important because the table uses a UNIQUE constraint that will silently swallow duplicate inserts under race conditions.
