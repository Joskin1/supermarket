#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

read_nativephp_version() {
    php -r '$contents = file_get_contents("config/nativephp.php"); preg_match("/'\''version'\''\s*=>\s*'\''([^'\'']+)'\''/", $contents, $matches); echo $matches[1] ?? "";'
}

bump_patch_version() {
    local current_version="$1"

    php -r '
        $version = $argv[1];
        $parts = explode(".", $version);

        if (count($parts) !== 3 || array_filter($parts, fn ($part) => ! ctype_digit($part))) {
            fwrite(STDERR, "Expected semantic version like 1.0.28, got {$version}.\n");
            exit(1);
        }

        $parts[2] = (string) (((int) $parts[2]) + 1);
        echo implode(".", $parts);
    ' "$current_version"
}

write_release_version() {
    local version="$1"

    php -r '
        $version = $argv[1];

        $configPath = "config/nativephp.php";
        $config = file_get_contents($configPath);
        $config = preg_replace("/('\''version'\''\s*=>\s*'\'')[^'\'']+('\'',)/", "\${1}{$version}\${2}", $config, 1);
        file_put_contents($configPath, $config);

        foreach (["nativephp/electron/package.json", "nativephp/electron/package-lock.json"] as $path) {
            $json = json_decode(file_get_contents($path), true);
            $json["version"] = $version;

            if (isset($json["packages"][""])) {
                $json["packages"][""]["version"] = $version;
            }

            file_put_contents(
                $path,
                json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
            );
        }
    ' "$version"
}

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    echo "This command must be run inside the git repository."
    exit 1
fi

if [[ -n "$(git status --porcelain)" ]]; then
    echo "Your working tree is not clean."
    echo "Commit your changes first, then run: composer release:windows"
    exit 1
fi

MODE="${1:-}"
BRANCH="$(git branch --show-current)"

if [[ -z "$BRANCH" ]]; then
    echo "You are not on a branch. Checkout your release branch first."
    exit 1
fi

if [[ "$MODE" == "--bump-patch" ]]; then
    CURRENT_VERSION="$(read_nativephp_version)"
    VERSION="$(bump_patch_version "$CURRENT_VERSION")"

    echo "Bumping Windows release version: $CURRENT_VERSION -> $VERSION"
    write_release_version "$VERSION"

    git add config/nativephp.php nativephp/electron/package.json nativephp/electron/package-lock.json
    git commit -m "chore: release v$VERSION"
else
    VERSION="$MODE"
fi

if [[ -z "$VERSION" ]]; then
    VERSION="$(read_nativephp_version)"
fi

TAG="v${VERSION#v}"
PACKAGE_VERSION="$(node -p "require('./nativephp/electron/package.json').version")"

if [[ -z "${TAG#v}" ]]; then
    echo "Could not read the NativePHP app version from config/nativephp.php."
    exit 1
fi

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
