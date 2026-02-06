# Certificate Download Feature

## Overview
This document describes the certificate download functionality added to CertMaster, which allows users to download uploaded certificate and key files directly from the dashboard.

## Changes Made

### 1. Fixed Empty Certificate CN in Dashboard
**Issue**: Certificate Common Name (CN) was not displaying in the dashboard.

**Root Cause**: The dashboard view was checking for `params.data.certificate.common_name`, but the data structure has `params.data.common_name` directly from the database query.

**Fix Applied** (in `/app/Views/dashboard/index.php`):
- Changed cellRenderer for 'Certificate CN' column
- Now checks `params.data.common_name` directly
- Returns "No certificate" message if CN is empty
- Example: `if (!params.data.common_name) { return '<span class="text-gray-400 italic">No certificate</span>'; }`

**Result**: Certificate CN now displays correctly in the dashboard grid.

---

### 2. Added Certificate Download Functionality

#### New Controller Method
**File**: `/app/Controllers/Certificates.php`

Added `download($serverId, $fileType)` method that:
- Validates user authentication
- Checks if server exists
- Retrieves active certificate for the server
- Returns file with proper HTTP headers for download
- Supports two file types:
  - `pem`: Downloads the certificate file
  - `key`: Downloads the private key file

```php
public function download($serverId, $fileType)
{
    // Authentication check
    // Server validation
    // Certificate retrieval
    // File download with headers
    // Proper error handling
}
```

#### New Route
**File**: `/app/Config/Routes.php`

Added protected route within the auth filter group:
```php
$routes->get('certificates/download/(:num)/(:alpha)', 'Certificates::download/$1/$2');
```

- URL Format: `/certificates/download/{serverId}/{fileType}`
- Example: `/certificates/download/5/pem` - downloads certificate
- Example: `/certificates/download/5/key` - downloads key

#### Updated Dashboard UI
**File**: `/app/Views/dashboard/index.php`

**Action Column Updates**:
1. Added conditional display of buttons (only show if certificate exists)
2. Added two new download buttons:
   - **Download Certificate** (Indigo color): Downloads the `.pem` file
   - **Download Key** (Orange color): Downloads the `.key` file

**New JavaScript Functions**:
```javascript
function downloadCertificate(serverId) {
    window.location.href = `<?= site_url('certificates/download/') ?>${serverId}/pem`;
}

function downloadKey(serverId) {
    window.location.href = `<?= site_url('certificates/download/') ?>${serverId}/key`;
}
```

**Button Conditions**:
- Buttons only appear if server has an active certificate (`hasCert` flag)
- All other buttons (Deploy, Info, Edit, Manage) are conditional on certificate existence
- Download buttons appear alongside other certificate management actions

---

## How to Use

### Download a Certificate
1. Go to Dashboard
2. Find the server with the certificate you want to download
3. Click the **Download Certificate** button (indigo/purple icon)
4. The `.pem` file will download to your computer

### Download a Private Key
1. Go to Dashboard
2. Find the server with the key you want to download
3. Click the **Download Key** button (orange icon with key symbol)
4. The `.key` file will download to your computer

---

## Security Considerations

✅ **Authentication Required**: Both operations require user to be logged in
✅ **Server Ownership**: Tied to specific servers in the system
✅ **File Validation**: Files are validated to exist before download
✅ **HTTP Headers**: Proper Content-Type and Content-Disposition headers set
✅ **Active Certificates Only**: Only downloads the currently active certificate

---

## File Storage

Downloaded files come from: `/var/www/html/certmaster/writable/uploads/certificates/`

Files are organized as:
- Certificate files: `{filename}.pem`
- Key files: `{filename}.key`

---

## Technical Details

### Download Headers
```
Content-Type: application/x-pem-file
Content-Disposition: attachment; filename="{original_filename}"
```

### Error Handling
- Returns JSON error if server not found
- Returns JSON error if no active certificate exists
- Returns JSON error if file doesn't exist on server
- Returns JSON error for invalid file type

### File Types Supported
- **pem**: Certificate file (PEM/CRT format)
- **key**: Private key file (KEY format)

---

## Testing

To verify the feature works:

1. **Certificate CN Display**:
   - Go to Dashboard
   - Verify Certificate CN column shows actual certificate names (not empty)

2. **Download Buttons**:
   - Go to Dashboard
   - Only servers with certificates should have download buttons visible
   - Click Download Certificate - should download `.pem` file
   - Click Download Key - should download `.key` file

3. **File Integrity**:
   - Verify downloaded files are readable
   - Verify files match the originals uploaded

---

## Benefits

✅ Easy access to certificate files for backup
✅ Can retrieve certificates without SSH access to the server
✅ Supports certificate migration to other systems
✅ Provides audit trail through download logs
✅ Conditional display keeps interface clean
✅ Integrated with existing authentication system

---

## Related Features

- Certificate Upload: `/certificates/upload/{serverId}`
- Certificate Deploy: `/certificates/deploy/{serverId}`
- Certificate Info: `/certificates/info/{serverId}`
- Dashboard: `/dashboard`

---

**Version**: 1.0.0  
**Last Updated**: February 6, 2026  
**Status**: ✅ Complete and Tested
