<?php
/**
 * Campus360 Enterprise ERP - Security Headers & Protection Rules
 */

class SecurityHeaders {
    public static function apply() {
        if (headers_sent()) return;

        // Anti-Clickjacking Protection
        header('X-Frame-Options: DENY');

        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');

        // Enable Browser XSS Filtering
        header('X-XSS-Protection: 1; mode=block');

        // Strict Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self';");
    }
}
