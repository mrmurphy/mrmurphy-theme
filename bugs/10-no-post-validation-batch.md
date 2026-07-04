# GET Batch Endpoint Discloses Like State for Non-Published Posts

**Severity:** LOW
**Lines:** 328–354
**File:** `inc/likes.php`

## Problem

The batch endpoint does not validate that a `post_id` corresponds to a published (or even existing) post. An attacker can probe arbitrary IDs:

- A draft/private post with existing likes returns `count > 0`, confirming its existence
- Non-existent IDs return `{"count":0,"liked":false}`, allowing ID range enumeration

## Fix

Validate post_ids against `get_post()` with a publish status check, matching the toggle endpoint's behavior at line 377. Or at minimum filter out IDs that don't correspond to published posts.
