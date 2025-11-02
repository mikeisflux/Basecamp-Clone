# Basecamp-Style User Management CLI - Instructions for Claude Code

## Project Overview
Build a command-line interface (CLI) application that replicates Basecamp's user invitation and administration system. This system allows account owners and administrators to invite different types of users, manage permissions, and control subscription features.

## Core Features Required

### 1. User Invitation System

#### Three User Types:
The system must support three distinct user invitation types with different permission levels:

**A. Coworker (Full Employee)**
- Can create projects
- Can add others to projects
- Can act as administrators
- Can be full-time, part-time, or volunteer
- Form fields needed:
  - Full name (required)
  - Email address (required)
  - Job title (optional)
  - Company/organization (pre-filled with account org name, e.g., "Divinity Comics")

**B. Outside Collaborator/Partner/Contractor/Guest**
- Can collaborate on projects
- **CANNOT** create projects
- **CANNOT** invite people to the account
- **CANNOT** add people to projects
- **CANNOT** be admins
- Form fields needed:
  - Full name (required)
  - Email address (required)
  - Job title (optional)
  - Company/organization (user can type their own org name)

**C. Client**
- Can access projects created for them
- **CANNOT** create their own projects
- **CANNOT** invite or add new people
- **CANNOT** become admins
- Owner can hide parts of projects from them (work in progress)
- Form fields needed:
  - Full name (required)
  - Email address (required)
  - Job title (optional)
  - Company/organization (user can type their own org name)

#### Invitation Flow:
1. User selects invitation type (radio buttons in UI, menu options in CLI)
2. User fills out the appropriate form
3. Option to "Add a personal note to the invitation email"
4. "Email invitation now..." button/command
5. Alternative option at bottom: "Need to add several coworkers at once? Invite them with a link instead."
6. After sending invite, show confirmation: "Invitation emailed to [name]"
7. Prompt user to either:
   - "Set up which projects they can see"
   - "Not now, I'll do this later"

### 2. Project Access Control System

When setting up project access for an invited user, implement:

**Access Levels:**
- **On the project**: User's avatar shows up at the top, they'll be notified about Chat messages
- **Just following**: User won't be notified unless someone specifically @mentions them, assigns them a to-do, or loops them into a thread

**Interface Requirements:**
- List all projects with member counts
- Each project has:
  - Checkbox to grant access
  - Dropdown to select access level (On the project / Just following)
- Bulk actions:
  - "Check all" / "Check none" buttons
  - "All on" / "All following" buttons
- Show project name and current member count for each project

**Example Projects (for Divinity Comics):**
- 2026 Divinity Comics Calendar (5 people)
- Apparel Design (2 people)
- Barbarian (2 people)
- Batpool (11 people)
- Billie the Kid (9 people)
- Building Campaigns (2 people)
- Challenge Coins (2 people)
- Dead Sexy #2 (3 people)
- Devilish (2 people)
- Divinity Art Prints/Unused Pieces (2 people)
- X-FEM #1 (2 people)
- X-FEM #2 (3 people)

**Bottom Section:**
"What happens next?"
"We'll send [name] a single email listing all the projects you've added them to. They will then be able to see everything in those projects, start posting, and interact with the rest of the team. If they haven't signed into Basecamp before, they'll get instructions on how to join."

"Save changes for [name]" button

### 3. Adminland Dashboard

The admin interface should display:

**Header:**
- "🔧 Adminland"
- "Manage your Basecamp account"

**Upgrade Section:**
- "Upgrades available"
- "Make Basecamp even better with upgrades."
- "See your options" link

**Administrators Section:**
- Show all administrators with their avatars/initials
- Example: "MW Mike Wheeler" and avatar "Mindy Wheeler"

**Administrator Capabilities:**
"You're an admin, so you can..."
- 👥 Manage people
- 🔧 Add/remove administrators
- 👤 Invite coworkers with a link
- 👥 Manage groups
- 🏢 Manage companies
- 🔧 Rename project tools
- 📝 Change message categories
- ➡️ Move projects from Basecamp 2 to Basecamp 4
- 🔀 Merge people

**Account Owners Section:**
- Show account owner(s) with avatars/initials
- Example: "MW Mike Wheeler"

**Account Owner Capabilities:**
"You're an account owner, so you can..."
- 💰 Handle billing, invoices, packages, and upgrades (Next payment: $30 on November 26, 2025)
- 💾 Manage storage
- 👑 Add/remove account owners
- ✏️ Rename this account (Divinity Comics)
- 🗑️ View everything in the trash
- 📋 Reassign someone's to-dos
- 🔑 Access any project
- 📥 Export data from this account
- 🔧 Manage public items
- ⏸️ Pause or cancel this account

### 4. Subscription/Upgrade System

**Base Plan:**
- Current payment: $30/month

**Timesheet Add-on ($50/month per team):**
Features:
- Track time on projects, to-dos, and more
- View total hours by project or person
- Create custom reports
- Export timesheets in CSV format
- Video: "How Timesheet works"
- "Buy Timesheet" button
- Price note: "You currently pay $30/month, so your new total will be $80."
- Billing: "You won't be charged until November 26, 2025. Your account will be instantly updated, and you can remove this upgrade any time."
- Tax info: "A number of localities require us to collect sales tax on Basecamp subscriptions. Learn more about these taxes."
- "If your company is officially tax-exempt, contact our support team to request an exemption."

**Admin Pro Pack ($50/month for admins and owners):**
Features:
- Choose who can send pings
- Choose who can turn on public links
- Choose who can archive and delete projects, docs, and more
- Choose who can change the people on a project
- Choose who can change project settings
- Limit editing comments and chats to 15 minutes
- Clean Sweep: Archive completed to-dos and cards automatically
- Set Out of Office for others
- Require two-factor authentication
- Change Ping & Chat history settings
- "Buy the Admin Pro Pack" button
- Same pricing and billing notes as Timesheet

### 5. User Status Display

**Top Right Corner:**
- User avatar/initials (e.g., "MW" in purple circle)
- "🔧 Adminland" link
- User should be able to see their logged-in status

### 6. Main Navigation

Should include:
- 🏠 Home
- 📋 Lineup
- 🏓 Pings
- 📬 Hey!
- 🕐 Activity
- 😊 My Stuff
- 🔍 Find

---

## Technical Implementation Guide

### Data Structure

Create a data model that tracks:

```json
{
  "organization": "Divinity Comics",
  "users": [
    {
      "id": "unique_id",
      "full_name": "Name",
      "email": "email@domain.com",
      "initials": "XX",
      "role": "owner|admin|coworker|contractor|client",
      "job_title": "optional",
      "organization": "Company Name",
      "invited_date": "ISO date",
      "invite_accepted": true/false
    }
  ],
  "projects": [
    {
      "id": "unique_id",
      "name": "Project Name",
      "member_count": 5,
      "members": [
        {
          "user_id": "user_id",
          "access_level": "on_project|just_following"
        }
      ]
    }
  ],
  "subscription": {
    "base_price": 30,
    "next_billing_date": "2025-11-26",
    "timesheet_enabled": false,
    "admin_pro_pack_enabled": false,
    "total_monthly": 30
  }
}
```

### CLI Command Structure

Organize commands hierarchically:

```bash
basecamp-cli
├── login
├── invite
│   ├── coworker
│   ├── contractor
│   └── client
├── manage
│   ├── people
│   ├── administrators
│   ├── groups
│   ├── companies
│   └── projects
├── adminland
│   ├── view
│   ├── billing
│   ├── storage
│   └── upgrades
├── projects
│   ├── list
│   ├── access [user_id]
│   └── add-member
└── subscription
    ├── upgrade [timesheet|admin-pro-pack]
    └── billing-info
```

### Implementation Steps

**Phase 1: Core Setup**
1. Set up project structure with proper CLI framework (Click, argparse, or Typer recommended)
2. Create data persistence layer (JSON file or SQLite database)
3. Implement user authentication/session management
4. Build basic menu navigation system

**Phase 2: User Management**
5. Implement three invitation flows with appropriate form validation
6. Create email simulation (in CLI, just show what would be sent)
7. Build user listing and viewing capabilities
8. Implement role-based permission checks

**Phase 3: Project Access Control**
9. Create project listing with member counts
10. Build project access assignment interface
11. Implement bulk operations (check all, set all to following, etc.)
12. Create confirmation messages and next-step prompts

**Phase 4: Adminland Features**
13. Build Adminland dashboard view
14. Implement all administrator capabilities as functional commands
15. Implement all account owner capabilities as functional commands
16. Create role-checking to ensure only authorized users can access features

**Phase 5: Subscription System**
17. Implement upgrade viewing system
18. Create upgrade purchase flow (simulation)
19. Calculate pricing changes (base + add-ons)
20. Show billing information and dates

**Phase 6: Polish**
21. Add comprehensive help text for all commands
22. Implement input validation and error handling
23. Create confirmation prompts for destructive actions
24. Add color coding and formatting for better UX

### Key Implementation Notes

**User Role Hierarchy:**
- Owner > Admin > Coworker > Contractor > Client
- Owners can do everything
- Admins can do most things except billing and ownership changes
- Coworkers have project creation rights
- Contractors and Clients have limited access

**Permission Matrix:**
Create a permissions system that enforces:
- Who can invite which types of users
- Who can access which administrative functions
- Who can modify projects and settings
- Who can view billing information

**Data Validation:**
- Email format validation
- Required vs optional fields
- Unique email addresses per account
- Valid project access levels
- Valid user roles

**User Experience:**
- Clear prompts and instructions
- Confirmation before destructive actions
- Success/error messages
- Navigation breadcrumbs
- Option to cancel operations
- Back/return options in nested menus

**Security Considerations:**
- Store sensitive data securely
- Implement session timeouts
- Log administrative actions
- Validate all user inputs
- Prevent privilege escalation

---

## Example Workflows

### Workflow 1: Inviting a Coworker
```
$ basecamp-cli invite coworker

=== Invite a Coworker to Divinity Comics ===

People who work at Divinity Comics are the only people who can create 
projects, add others to projects, and act as administrators.

Full name: John Doe
Email address: john@divinitycomics.com
Job title (optional): Artist
Company/organization: Divinity Comics

Add a personal note? (y/n): n

✓ Invitation emailed to John Doe (john@divinitycomics.com)

What would you like to do next?
1. Set up which projects they can see
2. Not now, I'll do this later

Choice:
```

### Workflow 2: Setting Project Access
```
$ basecamp-cli projects access john@divinitycomics.com

=== What can john@divinitycomics.com access? ===

First, check off the projects they should be able to access.
Then, decide if they should be "On the project" (their avatar will 
show up at the top) or "Just following" (they won't be notified 
unless someone specifically @mentions them).

Bulk actions: [a] Check all  [n] Check none  
             [o] All on  [f] All following

[ ] 2026 Divinity Comics Calendar (5 people) - Just following ▼
[ ] Apparel Design (2 people) - Just following ▼
[x] Barbarian (2 people) - On the project ▼
[x] Batpool (11 people) - On the project ▼
[ ] Billie the Kid (9 people) - Just following ▼

[s] Save changes  [c] Cancel

What happens next?
We'll send John Doe a single email listing all the projects you've 
added them to. They will then be able to see everything in those 
projects, start posting, and interact with the rest of the team.
```

### Workflow 3: Viewing Adminland
```
$ basecamp-cli adminland

=== 🔧 Adminland ===
Manage your Basecamp account

[!] Upgrades available
    Make Basecamp even better with upgrades.
    Type 'upgrade' to see your options

--- Administrators ---
MW  Mike Wheeler
    Mindy Wheeler

You're an admin, so you can:
1. Manage people
2. Add/remove administrators
3. Invite coworkers with a link
4. Manage groups
5. Manage companies
6. Rename project tools
7. Change message categories
8. Move projects from Basecamp 2 to Basecamp 4
9. Merge people

--- Account Owners ---
MW  Mike Wheeler

You're an account owner, so you can:
10. Handle billing, invoices, packages, and upgrades
    (Next payment: $30 on November 26, 2025)
11. Manage storage
12. Add/remove account owners
13. Rename this account (Divinity Comics)
14. View everything in the trash
15. Reassign someone's to-dos
16. Access any project
17. Export data from this account
18. Manage public items
19. Pause or cancel this account

Enter command number or 'q' to quit:
```

### Workflow 4: Upgrading to Timesheet
```
$ basecamp-cli subscription upgrade timesheet

=== Add Timesheet to Basecamp ===

Give your team the power to track time spent on projects.

What's included:
• Track time on projects, to-dos, and more
• View total hours by project or person
• Create custom reports
• Export timesheets in CSV format

💰 $50/month (For everyone on your team)

You currently pay $30/month, so your new total will be $80.

⏰ You won't be charged until November 26, 2025.
   Your account will be instantly updated, and you can 
   remove this upgrade any time.

Confirm purchase? (yes/no):
```

---

## Testing Requirements

Create test scenarios for:

1. **User Invitation**
   - Invite each type of user (coworker, contractor, client)
   - Validate form fields
   - Test email generation
   - Test duplicate email prevention

2. **Project Access**
   - Assign single project access
   - Bulk assign projects
   - Change access levels
   - Remove project access

3. **Permissions**
   - Verify role-based access controls
   - Test privilege escalation prevention
   - Verify admins can't access owner-only features
   - Verify contractors/clients have limited access

4. **Subscription Management**
   - View current subscription
   - Upgrade to Timesheet
   - Upgrade to Admin Pro Pack
   - View billing calculations

5. **Adminland Functions**
   - Test all admin capabilities
   - Test all owner capabilities
   - Verify proper authorization checks

---

## Additional Features to Consider

**Nice-to-Have Enhancements:**
- Link generation for bulk coworker invites
- Group management system
- Company management (for external orgs)
- Project tool renaming
- Message categories customization
- Two-factor authentication setup
- Out of office settings
- Public links management
- Data export functionality
- Trash viewing and restoration
- To-do reassignment
- Storage management and monitoring

**UI/UX Improvements:**
- Color-coded output (success=green, error=red, info=blue)
- Progress indicators for long operations
- Confirmation summaries before final submission
- Inline help text (? for help)
- Command history
- Tab completion
- Search functionality for users/projects

---

## File Structure Suggestion

```
basecamp-cli/
├── README.md
├── requirements.txt
├── setup.py
├── basecamp_cli/
│   ├── __init__.py
│   ├── main.py              # Entry point
│   ├── cli/
│   │   ├── __init__.py
│   │   ├── invite.py        # Invitation commands
│   │   ├── projects.py      # Project management
│   │   ├── adminland.py     # Admin functions
│   │   └── subscription.py  # Billing/upgrades
│   ├── models/
│   │   ├── __init__.py
│   │   ├── user.py          # User model
│   │   ├── project.py       # Project model
│   │   └── subscription.py  # Subscription model
│   ├── data/
│   │   ├── __init__.py
│   │   └── storage.py       # Data persistence
│   ├── auth/
│   │   ├── __init__.py
│   │   └── permissions.py   # Role-based access
│   └── utils/
│       ├── __init__.py
│       ├── validation.py    # Input validation
│       └── formatting.py    # Output formatting
└── tests/
    ├── test_invite.py
    ├── test_projects.py
    ├── test_adminland.py
    └── test_subscription.py
```

---

## Success Criteria

The implementation is complete when:

✅ All three user types can be invited with appropriate forms
✅ Project access can be assigned and modified for any user
✅ Adminland displays all admin and owner capabilities
✅ Role-based permissions properly restrict access
✅ Subscription upgrades can be simulated with correct pricing
✅ All workflows match the Basecamp interface logic
✅ Data persists between CLI sessions
✅ Error handling prevents invalid operations
✅ Help text is available for all commands
✅ Code is well-documented and maintainable

---

## Priority Order

**Must Have (P0):**
- User invitation (all 3 types)
- Project access control
- Basic adminland view
- Role-based permissions
- Data persistence

**Should Have (P1):**
- Full adminland capabilities
- Subscription/upgrade system
- User management (list, edit, remove)
- Bulk project operations

**Nice to Have (P2):**
- Groups and companies
- Advanced admin features
- Link-based invitations
- Data export

**Future Enhancement (P3):**
- Integration with actual email service
- Database backend
- API for external access
- Web UI companion

---

This specification provides everything Claude Code needs to build a fully functional Basecamp-style user management CLI that accurately replicates the invitation system, permission model, project access controls, and administrative features shown in your screenshots.
