#!/bin/bash

# Build script for mrmurphy-theme
# Commits changes, pushes to git, bumps version, and creates a zip file

set -e

THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_NAME="$(basename "$THEME_DIR")"
ZIP_NAME="${THEME_NAME}.zip"
ZIP_PATH="${THEME_DIR}/${ZIP_NAME}"

echo "🎨 Building ${THEME_NAME}..."

# Function to bump version (patch version)
bump_version() {
    local file=$1
    local current_version=$(grep -oP 'Version:\s*\K[0-9]+\.[0-9]+\.[0-9]+' "$file" 2>/dev/null || grep -oP "MRMURPHY_VERSION',\s*'\K[0-9]+\.[0-9]+\.[0-9]+" "$file" 2>/dev/null || echo "1.0.0")
    local major=$(echo $current_version | cut -d. -f1)
    local minor=$(echo $current_version | cut -d. -f2)
    local patch=$(echo $current_version | cut -d. -f3)
    patch=$((patch + 1))
    local new_version="${major}.${minor}.${patch}"
    
    if [[ "$file" == *"style.css" ]]; then
        sed -i '' "s/Version: [0-9]\+\.[0-9]\+\.[0-9]\+/Version: ${new_version}/" "$file"
    elif [[ "$file" == *"functions.php" ]]; then
        sed -i '' "s/MRMURPHY_VERSION', '[0-9]\+\.[0-9]\+\.[0-9]\+'/MRMURPHY_VERSION', '${new_version}'/" "$file"
    fi
    
    echo "$new_version"
}

# Check for existing uncommitted changes (before version bump)
HAS_EXISTING_CHANGES=false
if ! git diff-index --quiet HEAD --; then
    HAS_EXISTING_CHANGES=true
    echo "📝 Existing uncommitted changes detected."
    read -p "Do you want to commit and push existing changes first? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "📦 Staging existing changes..."
        git add -A
        
        echo "💾 Committing existing changes..."
        read -p "Enter commit message (or press Enter for default): " COMMIT_MSG
        if [ -z "$COMMIT_MSG" ]; then
            COMMIT_MSG="Update theme files"
        fi
        git commit -m "$COMMIT_MSG"
        
        echo "🚀 Pushing to remote..."
        git push
    else
        echo "⏭️  Keeping existing changes uncommitted..."
    fi
fi

# Bump version number
echo "🔢 Bumping version number..."
NEW_VERSION=$(bump_version "${THEME_DIR}/style.css")
bump_version "${THEME_DIR}/functions.php" > /dev/null
echo "   Version bumped to ${NEW_VERSION}"

# Commit version bump separately
echo "💾 Committing version bump..."
git add style.css functions.php
git commit -m "Bump version to ${NEW_VERSION}" --no-verify
echo "✅ Version bump committed"

# Remove old zip if it exists
if [ -f "${ZIP_PATH}" ]; then
    echo "🗑️  Removing old zip file..."
    rm "${ZIP_PATH}"
fi

# Create zip file
echo "📦 Creating zip file..."
cd "$THEME_DIR"
zip -r "${ZIP_NAME}" . \
    -x "*.git*" \
    -x ".git/*" \
    -x "*/node_modules/*" \
    -x "*.DS_Store" \
    -x ".DS_Store" \
    -x ".cursor/*" \
    -x ".claude/*" \
    -x "*.log" \
    -x "${ZIP_NAME}" \
    > /dev/null

echo "✅ Build complete!"
echo "📦 Zip file created: ${ZIP_PATH}"
