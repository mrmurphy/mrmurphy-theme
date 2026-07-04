# Repeated Recount Queries Inside Migration Loop (N+1)

**Severity:** LOW
**Lines:** 243
**File:** `inc/likes.php`

## Problem

`mrmurphy_likes_recount()` is called inside the foreach loop for each migrated/deleted row. If a user has 100 anonymous likes to migrate, this runs 100 `COUNT(*)` queries + 100 `UPDATE postmeta` queries when a single recount per affected post_id (or one batch at the end) would suffice.

## Fix

Collect all affected post_ids during the loop, then run `mrmurphy_likes_recount()` once per unique post_id after the loop completes.
