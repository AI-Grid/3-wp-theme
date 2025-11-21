#!/usr/bin/env bash
set -euo pipefail

TARGET=${1:-apk}
ROOT_DIR=$(cd "$(dirname "$0")/.." && pwd)
OUTPUT_DIR="$ROOT_DIR/output"
mkdir -p "$OUTPUT_DIR"

APP_NAME=${APP_NAME:-WebView App}
PACKAGE_NAME=${PACKAGE_NAME:-com.example.webview}
VERSION_NAME=${VERSION_NAME:-1.0.0}
VERSION_CODE=${VERSION_CODE:-1}
WEBVIEW_URL=${WEBVIEW_URL:-https://example.com}
KEYSTORE_PATH=${KEYSTORE_PATH:-}

ARTIFACT_NAME="app-release.$TARGET"
ARTIFACT_PATH="$OUTPUT_DIR/$ARTIFACT_NAME"

cat >"$ARTIFACT_PATH" <<EOF_CONTENT
Android WebView build artifact
------------------------------
Target: $TARGET
App name: $APP_NAME
Package: $PACKAGE_NAME
Version: $VERSION_NAME ($VERSION_CODE)
WebView URL: $WEBVIEW_URL
Keystore: ${KEYSTORE_PATH:-not provided}
Generated at: $(date -Iseconds)

This is a placeholder artifact. Replace this script with a real build pipeline
(Gradle, Capacitor, or Flutter) to assemble signed outputs.
EOF_CONTENT

printf 'Artifact created at %s\n' "$ARTIFACT_PATH"
