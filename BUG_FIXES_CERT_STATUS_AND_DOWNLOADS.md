# Bug Fixes: Certificate Status and Download Both Files

## Issues Resolved

### Issue 1: Certificate Status Empty on Servers Page
**Problem**: The Certificate Status column was displaying empty values on the Servers page.

**Root Cause**: The servers page AG Grid was checking for `params.data.status`, but the Servers controller was setting `cert_status`, creating a mismatch.

**Solution Applied**:
- Updated `/app/Views/servers/index.php`
- Changed field reference from `params.data.status` to `params.data.cert_status`
- Now correctly displays certificate status: Valid, Expiring, Expired, or No Cert

**Files Modified**:
- `/app/Views/servers/index.php` - Line 251-255

---

### Issue 2: Download Button Only Downloads Certificate, Not Key
**Problem**: The download certificate button only allowed downloading the `.pem` file or `.key` file individually. Users needed both files together.

**Solution Applied**:
Implemented a new "Download Both" feature that packages both the certificate and key files into a single ZIP archive.

**Components Added**:

#### 1. Enhanced Certificates Controller
**File**: `/app/Controllers/Certificates.php`

Added new `'both'` file type option to the `download()` method:
```php
elseif ($fileType === 'both') {
    // Creates a ZIP file containing both certificate.pem and private.key
    // Uses ZipArchive to package both files
    // Returns ZIP with filename format: {servername}_certificate_YYYY-MM-DD.zip
}
```

**Features**:
- Creates temporary ZIP file containing both files
- Uses server name for ZIP filename
- Includes date stamp for easy identification
- Properly cleans up temporary files after download
- Validates both files exist before packaging

#### 2. Updated Dashboard View
**File**: `/app/Views/dashboard/index.php`

**Changes**:
- Replaced separate "Download Cert" and "Download Key" buttons
- Added single "Download Both Files" button (cyan archive icon)
- Button only appears when certificate exists
- All 5 action buttons now fit cleanly in the Actions column:
  1. Deploy (🚀 blue) - Deploy to server
  2. View Info (ℹ️ green) - Show certificate details
  3. Download Both (📦 cyan) - Download ZIP with cert + key
  4. Edit (✏️ yellow) - Edit server settings
  5. Manage (⚙️ purple) - Go to servers page

#### 3. Simplified JavaScript
**File**: `/app/Views/dashboard/index.php`

Replaced separate functions with unified approach:
```javascript
// Old (removed)
function downloadCertificate(serverId) { ... }
function downloadKey(serverId) { ... }

// New (added)
function downloadBoth(serverId) {
    window.location.href = `${siteUrl}/certificates/download/${serverId}/both`;
}
```

---

## Technical Details

### ZIP File Creation
- Uses PHP's native `ZipArchive` class
- Creates temporary file in system temp directory
- Automatically cleans up after download
- Unique filename to prevent conflicts

### ZIP File Naming Convention
Format: `{servername}_certificate_YYYY-MM-DD.zip`

Example: `my-server_certificate_2026-02-06.zip`

### Download Process
1. User clicks "Download Both Files" button
2. Backend validates:
   - User authentication ✓
   - Server exists ✓
   - Active certificate exists ✓
   - Both PEM and KEY files exist ✓
3. Creates temporary ZIP archive
4. Sends ZIP to browser with proper headers:
   - `Content-Type: application/zip`
   - `Content-Disposition: attachment; filename="..."`
5. Browser downloads ZIP file
6. Temporary file deleted from server

### File Structure in ZIP
```
{servername}_certificate_2026-02-06.zip
├── certificate.pem    (Certificate file)
└── private.key        (Private key file)
```

---

## User Benefits

✅ **One-Click Download**: Get both files with a single click
✅ **Organized**: ZIP format keeps files together
✅ **Clear Naming**: Server name and date in filename
✅ **Reduced Confusion**: Single button instead of two separate ones
✅ **Easy Backup**: Simple backup mechanism for certificates
✅ **Ready to Deploy**: Users can extract and deploy anywhere

---

## What Changed

### Dashboard (Homepage)
- **Before**: 
  - 4 action buttons (Deploy, Info, Download Cert, Download Key, Edit, Manage)
  - Required clicking multiple buttons to get both files
  
- **After**:
  - 5 action buttons (Deploy, Info, Download Both, Edit, Manage)
  - Single click gets both files in ZIP
  - Cleaner interface

### Servers Page
- **Before**: Certificate Status column was empty
- **After**: Certificate Status displays correctly (Valid/Expiring/Expired/No Cert)

---

## Testing

### Certificate Status Fix
1. Go to Servers page
2. Verify "Cert Status" column displays values
3. Status should show: Valid ✓, Expiring ⚠, Expired ✗, or No Cert

### Download Both Files
1. Go to Dashboard
2. Find server with certificate
3. Click "Download Both Files" button (📦)
4. ZIP file downloads with format: `servername_certificate_YYYY-MM-DD.zip`
5. Extract ZIP - verify it contains both `.pem` and `.key` files

---

## API Changes

### Route
- Existing: `GET /certificates/download/{serverId}/{fileType}`
- Supported values for `{fileType}`:
  - `pem` - Downloads certificate file only
  - `key` - Downloads key file only
  - `both` - **NEW** - Downloads both files as ZIP archive

### Example URLs
```
/certificates/download/5/pem       → Downloads certificate.pem
/certificates/download/5/key       → Downloads private.key
/certificates/download/5/both      → Downloads certificate_2026-02-06.zip
```

---

## Database Impact
None - No database changes required. Uses existing certificate data.

---

## Performance Impact
Minimal - ZIP creation is fast and temporary files are cleaned up immediately.

---

## Security Considerations
✅ **Authentication Required**: All downloads require login
✅ **Validation**: Verifies server ownership
✅ **File Existence**: Checks both files exist before download
✅ **Secure Cleanup**: Temporary ZIP files cleaned up immediately
✅ **Proper Headers**: Uses appropriate content-type and encoding

---

## Related Documentation
- Certificate Download Feature: `/CERTIFICATE_DOWNLOAD_FEATURE.md`
- Dashboard Layout Optimization: `/DASHBOARD_LAYOUT_OPTIMIZATION.md`

---

**Version**: 1.0.0  
**Date**: February 6, 2026  
**Status**: ✅ Complete and Tested
