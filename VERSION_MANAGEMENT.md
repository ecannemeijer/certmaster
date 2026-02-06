# Version Management

This document explains how version management works in CertMaster.

## Overview

CertMaster includes a comprehensive version management system that tracks the application version and displays it throughout the application.

## Version Information

### Current Version: 1.0.0

The application version is defined in multiple places:

1. **Environment File (`.env`)** - The primary source for version information
2. **Footer** - Displayed in the browser footer on every page
3. **API Endpoint** - Available via `/api/version` endpoint

## Configuration

### .env File

Version information is stored in the `.env` file:

```properties
#--------------------------------------------------------------------
# APPLICATION VERSION
#--------------------------------------------------------------------

app.version = '1.0.0'
app.name = 'CertMaster'
app.description = 'SSL Certificate Management System'
```

To update the version, simply modify the `app.version` value in the `.env` file:

```properties
app.version = '1.1.0'
```

## Accessing Version Information

### In Views (PHP)

Use the environment variable function:

```php
<?= env('app.version', '1.0.0') ?>
<?= env('app.name', 'CertMaster') ?>
<?= env('app.description', 'SSL Certificate Management System') ?>
```

### Via Helper Functions

Use the provided helper functions:

```php
getAppVersion()          // Returns: '1.0.0'
getAppName()            // Returns: 'CertMaster'
getAppDescription()     // Returns: 'SSL Certificate Management System'
getAppInfo()            // Returns: associative array with all info
```

To use the helper functions, include them in your controller:

```php
use App\Helpers\VersionHelper;

// Or in a view:
<?= getAppVersion() ?>
```

### Via API Endpoint

Make a GET request to the version API endpoint:

```bash
curl http://localhost/certmaster/public/api/version
```

Response:

```json
{
  "success": true,
  "name": "CertMaster",
  "version": "1.0.0",
  "description": "SSL Certificate Management System"
}
```

## Browser Display

The application version is displayed in the footer of every page in the bottom-right corner as `v1.0.0`.

## Version File Structure

### Config/Version.php

The `/app/Config/Version.php` file contains the version configuration array (alternative configuration method):

```php
return [
    'version' => '1.0.0',
    'name' => 'CertMaster',
    'description' => 'SSL Certificate Management System',
    'author' => 'Your Company',
];
```

## Updating the Version

### Step 1: Update .env

Edit the `.env` file and update the `app.version` value:

```properties
app.version = '1.1.0'
```

### Step 2: (Optional) Update Version.php

If you want to keep both sources in sync, also update `/app/Config/Version.php`:

```php
return [
    'version' => '1.1.0',
    'name' => 'CertMaster',
    'description' => 'SSL Certificate Management System',
    'author' => 'Your Company',
];
```

### Step 3: Commit Changes

Commit your changes to version control:

```bash
git add .env app/Config/Version.php
git commit -m "Bump version to 1.1.0"
```

## Version History

### Version 1.0.0

**Release Date:** February 6, 2026

**Features:**
- SSL Certificate Management
- Server Management
- User Management
- Certificate Deployment
- Deployment Logs
- Session Management
- AG Grid Tables with Search
- Live Certificate Checking
- SSH Key Generation

## Best Practices

1. **Semantic Versioning**: Follow semantic versioning (MAJOR.MINOR.PATCH)
   - MAJOR: Incompatible API changes
   - MINOR: New features (backward compatible)
   - PATCH: Bug fixes

2. **Version Format**: Always use X.Y.Z format (e.g., 1.0.0, not 1.0)

3. **Keep in Sync**: Update both `.env` and `Config/Version.php` when changing versions

4. **Document Changes**: Always document what changed in each version

5. **Git Tags**: Consider tagging releases in Git:
   ```bash
   git tag -a v1.0.0 -m "Release version 1.0.0"
   git push origin v1.0.0
   ```

## Testing

To test that version management is working correctly:

1. Check the footer of any page - should show the version
2. Call the API endpoint - should return version information
3. Verify `.env` has correct version value
4. Use helper functions in any view to display version

## Locations Where Version Appears

1. **Footer** - Every authenticated page shows version in bottom-right
2. **API** - `/api/version` endpoint provides JSON response
3. **Environment** - Available via `env()` function throughout the application
4. **Helper Functions** - Via `getAppVersion()` and related functions
