#!/bin/bash
# Concatenate theme CSS and JS source files into frontend bundles.
set -euo pipefail

THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ASSETS="${THEME_DIR}/assets"

CSS_SOURCES=(
  "${ASSETS}/css/variables.css"
  "${ASSETS}/css/base.css"
  "${ASSETS}/css/layout.css"
  "${ASSETS}/css/components.css"
  "${ASSETS}/css/navigation.css"
  "${ASSETS}/css/animations.css"
  "${ASSETS}/css/color-cycle.css"
)

JS_SOURCES=(
  "${ASSETS}/js/navigation.js"
  "${ASSETS}/js/theme-toggle.js"
  "${ASSETS}/js/embed-facade.js"
)

CSS_OUT="${ASSETS}/css/theme.bundle.css"
JS_OUT="${ASSETS}/js/theme.bundle.js"

: > "${CSS_OUT}"
for file in "${CSS_SOURCES[@]}"; do
  echo "/* --- $(basename "${file}") --- */" >> "${CSS_OUT}"
  cat "${file}" >> "${CSS_OUT}"
  echo "" >> "${CSS_OUT}"
done

: > "${JS_OUT}"
for file in "${JS_SOURCES[@]}"; do
  echo "/* --- $(basename "${file}") --- */" >> "${JS_OUT}"
  cat "${file}" >> "${JS_OUT}"
  echo "" >> "${JS_OUT}"
done

echo "Built ${CSS_OUT} ($(wc -c < "${CSS_OUT}" | tr -d ' ') bytes)"
echo "Built ${JS_OUT} ($(wc -c < "${JS_OUT}" | tr -d ' ') bytes)"
