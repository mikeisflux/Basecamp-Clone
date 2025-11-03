# Dropbox Integration Testing Guide for ProjectFOB

## How can we try your app's Dropbox integration?

Thank you for reviewing our Dropbox integration! This guide will walk you through testing the Dropbox cloud storage integration in ProjectFOB.

---

## 1. Access the Application

**Live Application URL**: https://projectfob.com

**Test Account Credentials** (for Dropbox reviewers):
- Email: `dropbox-testing@projectfob.com`
- Password: `DropboxTest2025!`

This test account has a **Business+ subscription** which includes cloud storage integration features.

---

## 2. Navigate to Cloud Storage Settings

Once logged in:

1. Click on your **profile icon** in the top-right corner
2. Select **"Admin"** from the dropdown menu
3. In Adminland, click on **"Cloud Storage"** in the left sidebar
   - Or navigate directly to: https://projectfob.com/projectfob/settings/cloud-storage

---

## 3. Connect Dropbox Integration

On the Cloud Storage settings page:

1. Locate the **"Dropbox"** integration card
2. Click the **"Connect to Dropbox"** button
3. You will be redirected to Dropbox's OAuth authorization page
4. **Authorize the app** with your Dropbox account
5. You'll be redirected back to ProjectFOB with a success message

**What happens during OAuth:**
- We request `files.content.write` and `files.content.read` permissions
- Access tokens are stored securely (encrypted in our database)
- We use offline access to obtain refresh tokens
- State parameter is used for CSRF protection

---

## 4. Test File Upload to Dropbox

After connecting Dropbox:

### Option A: Upload from Project Files

1. Navigate to any project: https://projectfob.com/projectfob/projects/{project-slug}
2. Click on the **"Files"** tool
3. Click **"Upload File"**
4. In the upload dialog, select **"Save to Dropbox"**
5. Choose a file from your computer
6. Click **"Upload"**

**Expected behavior:**
- File uploads to your Dropbox account
- File appears in `/Apps/ProjectFOB/` folder
- File is also indexed in ProjectFOB for easy access
- Upload progress indicator shows during upload

### Option B: Attach File to Message

1. Navigate to **Messages** in any project
2. Create a new message
3. Click **"Attach File"**
4. Select **"Upload to Dropbox"**
5. Choose a file
6. Post the message

**Expected behavior:**
- File uploads to Dropbox
- Message shows file attachment with Dropbox icon
- Clicking the file opens it from Dropbox

---

## 5. Test File Sync and Retrieval

### View Files Stored in Dropbox

1. Go to **Cloud Storage settings**
2. Under the Dropbox section, click **"View Files"**
3. You'll see a list of all files synced to Dropbox

**Expected behavior:**
- Files are organized by project
- Each file shows: name, size, upload date
- Click any file to preview/download
- Files open directly from Dropbox

### Access Files from Dropbox Directly

1. Open your **Dropbox account** separately
2. Navigate to `/Apps/ProjectFOB/`
3. You should see folders for each project
4. Files uploaded from ProjectFOB appear here

**Folder structure:**
```
/Apps/ProjectFOB/
  ├── Project-Name-1/
  │   ├── document.pdf
  │   └── image.png
  ├── Project-Name-2/
  │   └── spreadsheet.xlsx
  └── ...
```

---

## 6. Test Automatic Sync

When files are added to Dropbox outside ProjectFOB:

1. Open **Dropbox** in your browser or desktop app
2. Navigate to `/Apps/ProjectFOB/{project}/`
3. Add a new file to this folder
4. Return to **ProjectFOB → Cloud Storage → View Files**
5. Click **"Sync Now"**

**Expected behavior:**
- ProjectFOB detects new files from Dropbox
- Files appear in the project's file list
- Metadata (name, size, modified date) is updated

---

## 7. Test Settings and Configuration

### Storage Preferences

1. Go to **Cloud Storage settings**
2. Under Dropbox section, find **"Default Save Location"**
3. Toggle between:
   - **"Save to Dropbox by default"** (all uploads go to Dropbox)
   - **"Ask each time"** (user chooses per upload)

**Expected behavior:**
- Setting saves immediately
- Future uploads respect the preference
- No page reload required

### Folder Organization

1. In **Cloud Storage settings → Dropbox**
2. Click **"Configure Folders"**
3. Choose folder organization:
   - **"By Project"** (default) - `/Apps/ProjectFOB/Project-Name/`
   - **"By Date"** - `/Apps/ProjectFOB/2025/11/`
   - **"Flat"** - `/Apps/ProjectFOB/` (all files in root)

**Expected behavior:**
- New uploads use the selected organization
- Existing files remain in current structure
- Setting persists across sessions

---

## 8. Test Disconnection

To test disconnecting Dropbox:

1. Go to **Cloud Storage settings**
2. Under Dropbox, click **"Disconnect"**
3. Confirm the action

**Expected behavior:**
- Access tokens are deleted from our database
- Future upload attempts prompt to reconnect
- **Files remain in your Dropbox** (we don't delete)
- ProjectFOB retains file metadata/links

---

## 9. Test Error Handling

### Test Expired Token Refresh

1. Connect Dropbox
2. Wait for access token to expire (or manually revoke in Dropbox)
3. Try to upload a file

**Expected behavior:**
- ProjectFOB detects expired token
- Automatically refreshes using refresh token
- Upload proceeds seamlessly
- User sees no interruption

### Test Network Failure

1. Connect Dropbox
2. Disconnect your internet
3. Try to upload a file

**Expected behavior:**
- Upload fails gracefully
- Error message: "Upload failed. Please check your connection and try again."
- File is not lost (remains in queue)
- Retry option available

### Test Storage Quota

1. Connect Dropbox with a free account (2GB limit)
2. Try to upload a file that exceeds remaining quota

**Expected behavior:**
- Upload fails before attempting
- Error message: "Not enough storage space in Dropbox"
- Suggests upgrading Dropbox plan or using local storage

---

## 10. Security and Privacy

### Data Handling

**What we store:**
- Dropbox access token (encrypted)
- Dropbox refresh token (encrypted)
- File metadata (name, size, path, modified date)
- User's Dropbox account ID

**What we DON'T store:**
- Dropbox password
- File contents (stored only in Dropbox)
- Personal information beyond account ID

### Token Security

- All tokens encrypted at rest using industry-standard encryption
- Tokens transmitted only over HTTPS
- Token refresh happens automatically before expiration
- Tokens deleted immediately upon disconnection

### Permissions

Our app requests minimal permissions:
- `files.content.write` - Upload and modify files
- `files.content.read` - Read and download files
- We do NOT request:
  - Account info beyond basic ID
  - Access to files outside `/Apps/ProjectFOB/`
  - Sharing or collaboration permissions

---

## 11. Feature Availability

Dropbox integration is available on:
- ✅ **Business+ Plan** ($79/month)
- ✅ **Enterprise Plan** ($199/month)
- ❌ Starter, Professional, Business (upgrade required)

Users on lower tiers see:
- Dropbox option grayed out
- "Upgrade to Business+" prompt
- Links to pricing page

---

## 12. Platform Support

Our Dropbox integration works on:
- ✅ Web browsers (Chrome, Firefox, Safari, Edge)
- ✅ Desktop (via browser)
- ✅ Mobile browsers (responsive design)
- ⏳ Native mobile apps (coming soon)

---

## 13. Support and Documentation

**User Documentation**:
- In-app help: Click "?" icon on Cloud Storage page
- Knowledge base: https://projectfob.com/docs/cloud-storage
- Video tutorial: https://projectfob.com/tutorials/dropbox-integration

**Contact Support**:
- Email: support@projectfob.com
- Live chat: Available in-app (bottom-right corner)
- Response time: < 24 hours

**For Dropbox App Review Team**:
- Technical contact: dev@projectfob.com
- App review queries: dropbox-review@projectfob.com

---

## 14. Technical Details

### OAuth Flow

1. User clicks "Connect to Dropbox"
2. Redirect to: `https://www.dropbox.com/oauth2/authorize`
3. Parameters:
   - `client_id`: [Your Dropbox App Key]
   - `redirect_uri`: `https://projectfob.com/wp-json/projectfob/v1/integrations/dropbox/callback`
   - `response_type`: `code`
   - `token_access_type`: `offline`
   - `state`: [CSRF token]
4. User authorizes
5. Dropbox redirects to callback with `code`
6. We exchange code for access + refresh tokens
7. Tokens stored securely
8. User redirected to success page

### API Endpoints Used

- `POST /oauth2/token` - Token exchange and refresh
- `POST /2/files/upload` - File upload
- `POST /2/files/list_folder` - List files
- `POST /2/files/get_metadata` - File info
- `POST /2/files/download` - File download

### Rate Limiting

We respect Dropbox API rate limits:
- Max 120 requests/minute per user
- Implement exponential backoff on 429 errors
- Queue system for batch operations

---

## 15. Test Checklist

For comprehensive testing, verify:

- [ ] OAuth authorization flow completes successfully
- [ ] Access tokens stored and encrypted properly
- [ ] File upload to Dropbox works
- [ ] Files appear in correct Dropbox folder structure
- [ ] File retrieval and download works
- [ ] Automatic token refresh on expiry
- [ ] Sync detects new files added directly to Dropbox
- [ ] Settings (default location, folder structure) save correctly
- [ ] Disconnection removes tokens and prevents uploads
- [ ] Error messages are clear and helpful
- [ ] Works on different browsers and devices
- [ ] Plan restrictions enforced (Business+ required)
- [ ] Privacy Policy and Terms of Service accessible

---

## Questions or Issues?

If you encounter any problems during testing or have questions:

**For App Review Team**:
- Email: dropbox-review@projectfob.com
- Include: Test account email, steps to reproduce, screenshots

**Expected Response**: Within 2 business hours during review period

---

## Additional Notes

**Test Environment**:
- Our staging environment: https://staging.projectfob.com (if needed)
- All OAuth callbacks use production URLs
- SSL/TLS enabled on all endpoints

**Compliance**:
- GDPR compliant (data export/deletion available)
- CCPA compliant
- SOC 2 Type II certified (in progress)

Thank you for reviewing our Dropbox integration! We've designed it to provide a seamless, secure experience for our users while respecting Dropbox's guidelines and best practices.

---

**Last Updated**: November 3, 2025
**Version**: 2.9.0
**App Review Contact**: dropbox-review@projectfob.com
