#!/bin/bash

# Build script for mrmurphy-theme
# Commits changes, pushes to git, and creates a zip file

set -e

THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_NAME="mrmurphy-theme"
ZIP_NAME="${THEME_NAME}.zip"
PARENT_DIR="$(dirname "$THEME_DIR")"

echo "🎨 Building ${THEME_NAME}..."

# Check if there are uncommitted changes
if ! git diff-index --quiet HEAD --; then
    echo "📝 Uncommitted changes detected."
    read -p "Do you want to commit and push? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "📦 Staging changes..."
        git add -A
        
        echo "💾 Committing changes..."
        read -p "Enter commit message (or press Enter for default): " COMMIT_MSG
        if [ -z "$COMMIT_MSG" ]; then
            COMMIT_MSG="Update theme files"
        fi
        git commit -m "$COMMIT_MSG"
        
        echo "🚀 Pushing to remote..."
        git push
    else
        echo "⏭️  Skipping commit/push. Creating zip from current state..."
    fi
else
    echo "✅ No uncommitted changes. Creating zip from current state..."
fi

# Remove old zip if it exists
if [ -f "${PARENT_DIR}/${ZIP_NAME}" ]; then
    echo "🗑️  Removing old zip file..."
    rm "${PARENT_DIR}/${ZIP_NAME}"
fi

# Create zip file
echo "📦 Creating zip file..."
cd "$PARENT_DIR"
zip -r "${ZIP_NAME}" "${THEME_NAME}" \
    -x "*.git*" \
    -x "*/.git/*" \
    -x "*/node_modules/*" \
    -x "*.DS_Store" \
    -x "*/.DS_Store" \
    -x "*/.cursor/*" \
    -x "*/.claude/*" \
    -x "*.log" \
    > /dev/null

echo "✅ Build complete!"
echo "📦 Zip file created: ${PARENT_DIR}/${ZIP_NAME}"
