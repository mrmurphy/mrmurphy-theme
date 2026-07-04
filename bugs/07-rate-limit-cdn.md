# IP-Based Rate Limiting Broken Behind Proxy/CDN

**Severity:** MEDIUM
**Lines:** 311
**File:** `inc/likes.php`

## Problem

`$_SERVER['REMOTE_ADDR']` is the IP of the immediate upstream, which behind Cloudflare, Fastly, or any CDN is the CDN edge IP, not the real client. Every user behind the same CDN edge node shares a single rate-limit bucket, while an attacker using rotating proxies appears as many different IPs and bypasses the limit entirely.

## Fix

If the site is behind a proxy, use `$_SERVER['HTTP_X_FORWARDED_FOR']` or `$_SERVER['HTTP_CF_CONNECTING_IP']` (Cloudflare) instead of, or as a fallback from, `REMOTE_ADDR`. Sanitize the header value as it can contain multiple comma-separated IPs.
