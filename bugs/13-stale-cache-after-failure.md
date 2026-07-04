# Stale Cache Count After Silent INSERT Failure

**Severity:** LOW
**Lines:** 275–278
**File:** `inc/likes.php`

## Problem

When the UNIQUE key prevents a duplicate INSERT (race condition from the TOCTOU issue), `$result = false` is logged but `mrmurphy_likes_recount()` is NOT called. The cached count (`_mmb_like_count`) remains incorrect. The response returns `mrmurphy_like_count()` which reads the stale meta. The count does not self-heal until the next successful mutation on that post.

## Fix

If INSERT fails due to a duplicate key (not a table-missing error), call `mrmurphy_likes_recount()` anyway to correct the count. Or use `INSERT ... ON DUPLICATE KEY UPDATE` which always succeeds.
