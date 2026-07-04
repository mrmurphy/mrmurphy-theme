# Identifier Spoofing — Anonymous User Can Impersonate Any WordPress User

**Severity:** CRITICAL
**Lines:** 129–133, 346, 381–383, 395
**File:** `inc/likes.php`

## Problem

`mrmurphy_likes_resolve_identifier()` returns the raw `$client_id` for logged-out users. An attacker sends `client_id=user:42` and the system treats them as user 42 with no authentication:

- Like/unlike as any user — POST with `client_id=user:42` inserts/deletes rows with `user:42` as the identifier
- Query any user's liked state — GET with `client_id=user:42` returns the `liked` boolean for every requested post
- User-ID enumeration — User IDs are sequential; iterating `user:1`, `user:2`, etc. leaks valid user IDs
- Denial of like — If attacker likes post X as `user:42` before user 42 does, the UNIQUE constraint makes user 42's genuine like a no-op

`sanitize_text_field` on line 369 does nothing to prevent this — `user:42` passes through unchanged.

## Fix

In `mrmurphy_likes_resolve_identifier()`, strip any `user:` prefix from the client-supplied identifier and replace it with the actual logged-in user's ID. The function should never trust a client-supplied `user:` identifier.
