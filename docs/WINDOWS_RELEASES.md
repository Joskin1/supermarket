# Windows Release Workflow

This project is set up for Linux local development and Windows-only GitHub releases.

## Local Linux Development

Use Linux for normal development and testing:

```bash
composer install
npm install
composer native:dev
```

If you want a local Linux desktop package for your own machine, build it locally only:

```bash
php artisan native:build linux
```

Do not upload Linux builds to GitHub Releases. The release workflow only publishes Windows installers.

## Windows GitHub Release

1. Update the app version in:
   - `config/nativephp.php`
   - `nativephp/electron/package.json`
   - `nativephp/electron/package-lock.json`

2. Commit the changes.

3. Run the release command:

```bash
composer release:windows
```

The command pushes your current branch, creates a matching version tag like `v1.0.28`, and pushes that tag. GitHub Actions will then run `.github/workflows/build-desktop.yml` on `windows-latest`, build the NativePHP Windows installer, and attach only Windows release files to the GitHub Release.

To automatically increase the patch version first, run:

```bash
composer release:windows:patch
```

For example, this changes `1.0.28` to `1.0.29`, commits the version bump, creates `v1.0.29`, pushes the branch, and pushes the tag.

## Manual Release Build

You can also start the same workflow from GitHub Actions using **Build Desktop App -> Run workflow**. Manual runs upload the Windows installer as a workflow artifact. Tagged runs also create a GitHub Release.
