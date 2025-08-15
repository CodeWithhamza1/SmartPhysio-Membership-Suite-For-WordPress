# SmartPhysio Membership Suite

A free membership management system for physiotherapy clinics that tracks engagement actions like Google Reviews, social follows, referrals, and shared contacts — with an eligibility system, shortcodes for frontend forms and status displays, and an admin dashboard with CSV export.

---

## Description
SmartPhysio Membership Suite helps physiotherapits and clinics run a free membership program where users can qualify based on specific engagement actions:

- Leaving a Google review
- Following all social media accounts
- Sharing clinic details with contacts
- Referring a treated patient

**The plugin:**
- Tracks these actions per member
- Determines eligibility automatically
- Lets users check their membership status
- Provides the clinic admin with an interface to update and verify member actions
- Exports membership data to CSV

---

## Features

### Frontend
- **Membership Application Form** with:
  - Full name, email, phone
  - Engagement checkboxes (Google review, social follow, shared contacts, referral)
  - Validation for required fields and email format
- **Membership Status Display:**
  - Shows verification status for each action
  - Displays eligibility (Eligible/Ineligible)
  - WhatsApp contact link for eligible members
- **Shortcodes:**
  - `[membership_form]` — displays the registration form
  - `[membership_status]` — shows status for logged-in user or provided email

### Admin
- **Custom Menu:** “Membership Suite”
- **Members Table:**
  - Name, Email, Phone
  - Engagement flags (toggle via checkboxes)
  - Eligibility dropdown (auto-set when all conditions met)
- **Filters:** All, Eligible, Ineligible
- **CSV Export:** Download all members’ data
- **Status Update:** Verify actions and set eligibility directly in the dashboard

### Technical
- Custom database table: `{prefix}sps_members`
- Auto-creates asset directories and default CSS/JS files on activation
- AJAX form submission with nonce protection
- Sanitization and validation for all user inputs
- Responsive design for forms and status display

---

## Requirements
- WordPress 5.8+ (tested up to 6.x)
- PHP 7.4+
- MySQL with CREATE TABLE privileges (on activation)

---

## Installation
1. Download or clone into `wp-content/plugins/smartphysio-membership-suite/`.
2. Activate via **Plugins → Installed Plugins** in WordPress.
3. On activation:
   - A custom database table is created.
   - Asset files (`style.css`, `admin-style.css`, JS) are generated if not present.
4. Add shortcodes to relevant pages:
   - Registration form: `[membership_form]`
   - Membership status: `[membership_status]`

---

## Usage

### Shortcodes
```shortcode
[membership_form]
```
Displays the membership registration form.

```shortcode
[membership_status email="example@example.com"]
```
Displays the membership status for the given email.  
If no email is provided and the user is logged in, their account email is used.

---

## Admin Dashboard
1. Navigate to **Membership Suite** in the WordPress admin menu.
2. Use the filter dropdown to view All, Eligible, or Ineligible members.
3. Update verification flags (Google Review, Social Follow, Shared Contacts, Referred Patient).
4. Eligibility auto-updates to “Eligible” only when all conditions are verified.
5. Export current list to CSV with the **Export to CSV** button.

---

## Configuration

### Eligibility Logic:
All four engagement actions must be verified for a member to become eligible.

### WhatsApp Contact Button:
- Default number: **+923000000000** (change in `render_membership_status()` if needed).

### Asset Customization:
- Frontend styles: `assets/css/style.css`
- Admin styles: `assets/css/admin-style.css`
- Frontend script: `assets/js/script.js`
- Admin script: `assets/js/admin-script.js`

---

## Data Export
- **Location:** Membership Suite Dashboard
- **Format:** CSV
- **Columns:** Name, Email, Phone, Google Review, Social Follow, Shared Contacts, Referred Patient, Eligibility

---

## Security
- Direct access blocked with `ABSPATH` check
- Nonce verification for all AJAX and admin actions
- Input sanitization for all form fields
- Email validation before database insert

---

## Internationalization
Currently English-only — ready for localization via standard WordPress functions (`__()`, `_e()`).

---

## Folder Structure
```
smartphysio-membership-suite/
├─ smartphysio-membership-suite.php
├─ assets/
│  ├─ css/
│  │  ├─ style.css
│  │  └─ admin-style.css
│  └─ js/
│     ├─ script.js
│     └─ admin-script.js
```

---

## FAQ

**Q:** Can users register multiple times?  
**A:** No. Email addresses are unique in the database.

**Q:** Can eligibility be granted manually?  
**A:** Yes. Admin can set `is_eligible` to “Eligible” from the dashboard.

**Q:** Is the referral automatically verified?  
**A:** No. Admin must verify “Referred Patient” manually.

---

## Changelog

**1.0.1**
- Initial public release
- Membership form with AJAX submission
- Admin dashboard with status management
- CSV export feature
- Responsive styles and asset auto-creation

**1.0.2**
- CSV export feature
- Engagement tracking with eligibility calculation
---

## Roadmap
- Email notifications on application/approval
- Search and pagination in the members list
- Frontend eligibility checker with login integration
- Integration with loyalty/rewards system
- REST API endpoints for external integration

---

## Contributing
1. Fork the repository
2. Create a feature branch
3. Commit changes with clear messages
4. Submit a pull request

---

## License
This plugin is licensed under the **GNU General Public License v2.0 or later**.  
You may modify and redistribute it under the same terms.  
License URI: [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

---

## Author
**Muhammad Hamza Yousaf**
