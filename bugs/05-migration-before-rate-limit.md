# Migration Runs Before Rate Limit

**Severity:** MEDIUM
**Lines:** 389
**File:** `inc/likes.php`

## Problem

`mrmurphy_likes_migrate_identifier()` issues a SELECT on the entire likes table for the given client_id before the rate limit is checked. An attacker who replays a `client_id` with many associated anonymous rows can cause O(n) DB work per request (SELECT + N*(SELECT+UPDATE/DELETE)) without being rate-limited at all. The rate limit only gates `mrmurphy_likes_apply`, not the migration.

## Fix

Move `mrmurphy_likes_migrate_identifier()` to after the rate limit check, or add a separate rate limit / debounce for the migration path.
