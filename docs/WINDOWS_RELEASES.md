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

2. Commit and push the changes.

3. Create and push a matching version tag:

```bash
git tag v1.0.28
git push origin v1.0.28
```

GitHub Actions will run `.github/workflows/build-desktop.yml` on `windows-latest`, build the NativePHP Windows installer, and attach only Windows release files to the GitHub Release.

## Manual Release Build

You can also start the same workflow from GitHub Actions using **Build Desktop App -> Run workflow**. Manual runs upload the Windows installer as a workflow artifact. Tagged runs also create a GitHub Release.
