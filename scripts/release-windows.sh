#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    echo "This command must be run inside the git repository."
    exit 1
fi

if [[ -n "$(git status --porcelain)" ]]; then
    echo "Your working tree is not clean."
    echo "Commit your changes first, then run: composer release:windows"
    exit 1
fi

BRANCH="$(git branch --show-current)"

if [[ -z "$BRANCH" ]]; then
    echo "You are not on a branch. Checkout your release branch first."
    exit 1
fi

VERSION="${1:-}"

if [[ -z "$VERSION" ]]; then
    VERSION="$(php -r '$config = require "config/nativephp.php"; echo $config["version"];')"
fi

TAG="v${VERSION#v}"
PACKAGE_VERSION="$(node -p "require('./nativephp/electron/package.json').version")"

if [[ "$PACKAGE_VERSION" != "${TAG#v}" ]]; then
    echo "Version mismatch:"
    echo "  config/nativephp.php: ${TAG#v}"
    echo "  nativephp/electron/package.json: ${PACKAGE_VERSION}"
    echo "Make them match before releasing."
    exit 1
fi

if git rev-parse "$TAG" >/dev/null 2>&1; then
    echo "Tag $TAG already exists locally."
    exit 1
fi

if git ls-remote --exit-code --tags origin "refs/tags/$TAG" >/dev/null 2>&1; then
    echo "Tag $TAG already exists on origin."
    exit 1
fi

echo "Pushing branch $BRANCH..."
git push origin "$BRANCH"

echo "Creating tag $TAG..."
git tag "$TAG"

echo "Pushing tag $TAG..."
git push origin "$TAG"

echo "Done. GitHub Actions will build the Windows installer and create the release."
