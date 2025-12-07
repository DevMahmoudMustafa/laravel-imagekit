# Publishing Guide - Laravel ImageKit

This guide will help you publish the Laravel ImageKit package to Packagist.

## Prerequisites

1. **GitHub Repository**: The package must be in a GitHub repository
2. **Packagist Account**: Create an account at https://packagist.org
3. **GitHub Token**: For automatic updates (optional but recommended)

## Step-by-Step Publishing Process

### Step 1: Prepare the Repository

```bash
# Navigate to the package directory
cd packages/DevMahmoudMustafa/ImageKit

# Ensure all files are committed
git add .
git commit -m "Prepare v1.0.0 for release"

# Create and push the version tag
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
git push origin --tags
```

### Step 2: Create GitHub Repository (if not exists)

1. Go to https://github.com/new
2. Repository name: `laravel-imagekit`
3. Description: "Laravel ImageKit - A comprehensive image processing package for Laravel"
4. Set to **Public** (required for Packagist)
5. **DO NOT** initialize with README, .gitignore, or license (they already exist)
6. Click "Create repository"

### Step 3: Push to GitHub

```bash
# If repository doesn't exist locally
git remote add origin https://github.com/YOUR_USERNAME/laravel-imagekit.git

# Push all branches and tags
git push -u origin main
git push origin --tags
```

### Step 4: Submit to Packagist

1. Go to https://packagist.org/packages/submit
2. Enter repository URL: `https://github.com/YOUR_USERNAME/laravel-imagekit`
3. Click "Check" to validate
4. Click "Submit" to publish

### Step 5: Enable Auto-Update (Recommended)

1. Go to your package page on Packagist
2. Click "Settings"
3. Add GitHub Service Hook:
   - Go to your GitHub repository
   - Settings → Webhooks → Add webhook
   - Payload URL: `https://packagist.org/api/github?username=YOUR_PACKAGIST_USERNAME`
   - Content type: `application/json`
   - Secret: (Get from Packagist settings)
   - Events: Select "Just the push event"
   - Click "Add webhook"

OR use Packagist API token:

1. Get your API token from Packagist (Profile → Show API Token)
2. Go to GitHub repository → Settings → Secrets → Actions
3. Add secret: `PACKAGIST_API_TOKEN` with your token
4. Add secret: `PACKAGIST_USERNAME` with your Packagist username

### Step 6: Verify Installation

After publishing, test the installation:

```bash
composer require devmahmoudmustafa/laravel-imagekit
```

## Post-Publishing Checklist

- [ ] Package appears on Packagist
- [ ] Installation works via Composer
- [ ] README displays correctly
- [ ] All badges work
- [ ] Version tag is recognized
- [ ] Auto-update webhook is configured

## Updating the Package

For future releases:

```bash
# Update version in CHANGELOG.md and README.md
# Commit changes
git add .
git commit -m "Prepare v1.0.1"

# Create new tag
git tag -a v1.0.1 -m "Release version 1.0.1"
git push origin v1.0.1
git push origin --tags

# Packagist will auto-update if webhook is configured
# Or manually update at: https://packagist.org/packages/YOUR_PACKAGE/update
```

## Important Notes

1. **Version Tags**: Always use semantic versioning (v1.0.0, v1.0.1, v1.1.0, v2.0.0)
2. **Stability**: Package is set to `"minimum-stability": "stable"` for production use
3. **README**: Make sure README.md is comprehensive and well-formatted
4. **License**: MIT License is specified in composer.json
5. **Tests**: All 132 tests pass successfully

## Package Information

- **Package Name**: `devmahmoudmustafa/laravel-imagekit`
- **Current Version**: 1.0.0
- **License**: MIT
- **PHP Requirement**: ^8.2
- **Laravel Requirement**: ^9.0|^10.0|^11.0|^12.0

## Support

If you encounter any issues during publishing, check:
- Packagist documentation: https://packagist.org/about
- GitHub repository visibility (must be public)
- Git tags are pushed correctly
- composer.json is valid

---

**Good luck with your package release! 🚀**

