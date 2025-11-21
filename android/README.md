# Android WebView builder

This directory bundles a lightweight Android WebView builder toolkit that pairs with the included WordPress plugin. The stack is intentionally minimal so you can wire it into a real Android pipeline (Gradle, Capacitor, Flutter, or your preferred framework) while still giving site admins a way to kick off builds and manage signing keys.

## Contents
- `app-builder-plugin/` – WordPress plugin that surfaces the admin/user panel.
- `scripts/` – helper scripts for builds and SDK refreshes.
- `output/` – default location for generated keystores, build logs, and artifacts.

## Quick start
1. Copy `android/app-builder-plugin` into your WordPress `wp-content/plugins` directory and activate **Android WebView Builder**.
2. In **Settings → Android Builder**, fill in the publishing profile (app name, package ID, version fields, and WebView URL).
3. Generate a keystore from the admin screen. The resulting PEM file is saved to `android/output/app-release-keystore.pem`.
4. Trigger a build (APK or AAB). Placeholder artifacts are written to `android/output/app-release.apk` or `android/output/app-release.aab` with the captured metadata.
5. Run SDK maintenance as needed to refresh cached metadata (placeholder hook; replace with `sdkmanager --update`).

> The shell scripts are intentionally simple so they run in most environments. Replace `scripts/build-webview.sh` with your production-grade pipeline once you are ready to assemble a signed binary.

## Automatic SDK upgrades
The `scripts/refresh-sdk.sh` command appends a timestamped log entry to `output/sdk-refresh.log`. Swap the placeholder body with your preferred SDK update command to keep toolchains current.

## Keystore generation
The plugin uses PHP's OpenSSL extension to generate a 2048-bit RSA keypair and self-signed certificate, storing them together as `app-release-keystore.pem`. Update or replace this logic if you prefer Java KeyStore (`.jks`) files or cloud-based signing.

## API surface
The admin panel lives at `wp-admin/admin.php?page=android-app-builder` after activation and exposes three actions:
- Save publishing profile
- Generate keystore
- Trigger build (APK/AAB)
- Refresh SDK metadata

Outputs are written to the `android/output` directory so they can be synced or collected by your CI/CD system.
