# Per-Post Rate Limit Allows High Aggregate Throughput

**Severity:** MEDIUM
**Lines:** 312
**File:** `inc/likes.php`

## Problem

The rate-limit key includes `post_id`, so an attacker gets 0.5 req/s per post. With 200 post IDs, they can send 100 requests/second total. This is sufficient for rapid count manipulation, race-condition exploitation, or load injection.

## Fix

Add a global rate limit (per-IP, regardless of post_id) in addition to the per-post limit. Or reduce the per-post window from 2 seconds to something longer.
