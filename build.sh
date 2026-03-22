#!/bin/bash
#
# Build script for PostmarkApp Email Integrator
# Creates a dist directory with only the files needed for WordPress.org

set -e

PLUGIN_SLUG="postmarkapp-email-integrator"
DIST_DIR="dist/$PLUGIN_SLUG"

# Clean previous build
rm -rf dist

# Create dist directory
mkdir -p "$DIST_DIR"

# Copy plugin files
cp postmarkapp.php "$DIST_DIR/"
cp index.php "$DIST_DIR/"
cp readme.txt "$DIST_DIR/"
cp -r js "$DIST_DIR/"

echo "Build complete: $DIST_DIR"
