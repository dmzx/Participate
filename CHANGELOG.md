## Participate Changelog

### 1.0.1 - 2026-09-07

- Fixed a stored XSS: participant names and colours were output unescaped in the participants bar (page load and AJAX update). Names are now rendered through phpBB's get_username_string().
- The participate link now carries a link hash and the controller verifies it, so a third-party page can no longer sign a member up or off (CSRF).
- The controller now enforces what the button only hid: the extension must be enabled, the forum included, the user logged in with reply permission, and the topic must exist. It always returns a response (JSON for AJAX, redirect to the topic otherwise) instead of a blank HTTP 500 when opened directly.
- ACP "Reset Participate Table" now uses phpBB's confirm_box() and a portable DELETE instead of TRUNCATE (which SQLite does not support).
- ACP page shows the version from the extension metadata instead of a third-party badge image; unique element ids.
- composer.json: PHP 7.2 floor (phpBB 3.3 requirement), phpbb/phpbb moved out of require.

### 1.0.0 - 2025-01-17

- First release.
