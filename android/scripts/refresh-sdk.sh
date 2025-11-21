#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR=$(cd "$(dirname "$0")/.." && pwd)
OUTPUT_DIR="$ROOT_DIR/output"
mkdir -p "$OUTPUT_DIR"

LOG_FILE="$OUTPUT_DIR/sdk-refresh.log"
{
  echo "Refreshed Android SDK metadata at $(date -Iseconds)"
  echo "This is a placeholder hook. Replace with sdkmanager --update when available."
} >> "$LOG_FILE"

printf 'SDK metadata refresh complete. See %s\n' "$LOG_FILE"
