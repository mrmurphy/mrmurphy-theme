# `Cache-Control: public` Leaks Per-User Like State

**Severity:** MEDIUM
**Lines:** 357
**File:** `inc/likes.php`

## Problem

The GET batch endpoint sets `Cache-Control: public, max-age=60`. If a CDN or reverse proxy caches this response by URL path, one user's `liked` booleans (keyed by their `client_id`) can be served to another user. The `public` directive explicitly allows caching at shared caches.

## Fix

Change `public` to `private` or `no-cache` so the response is not cached by shared caches. The response varies per-user by `client_id` and must not be served to other users.
