# E2E Test Coverage

Run the full suite: `npm run test:e2e`
Open Playwright UI debugger: `npm run test:e2e:ui`
Run all browsers/viewports: `npm run test:e2e:all`
View HTML report: `npm run test:e2e:report`

## Prerequisites

1. Start the Laravel dev server first: `php artisan serve`
2. Set credentials in env vars (or accept defaults):
   - `E2E_EMAIL` (default: `admin@example.com`)
   - `E2E_PASSWORD` (default: `password`)

---

## Coverage Inventory

| # | Spec File | Page / Feature | Test | Notes |
|---|-----------|---------------|------|-------|
| 1 | 01-auth | Login | Renders form elements | |
| 2 | 01-auth | Login | Empty submit validation | |
| 3 | 01-auth | Login | Invalid credentials error | |
| 4 | 01-auth | Login | Malformed email error | |
| 5 | 01-auth | Login | Successful login → dashboard | |
| 6 | 01-auth | Login | Remember-me checkbox | |
| 7 | 01-auth | Login | Forgot password link | |
| 8 | 01-auth | Register | All fields render | |
| 9 | 01-auth | Register | Link from login → register | |
| 10 | 01-auth | Register | Link from register → login | |
| 11 | 01-auth | Register | Mismatched passwords error | |
| 12 | 01-auth | Forgot Password | Page renders | |
| 13 | 01-auth | Forgot Password | Unknown email submission | |
| 14 | 01-auth | Session | Unauthenticated /dashboard → login | |
| 15 | 01-auth | Session | Unauthenticated /sanghs → login | |
| 16 | 01-auth | Session | Unauthenticated /profile → login | |
| 17 | 01-auth | Session | Logout works | |
| 18 | 01-auth | Session | Post-logout /dashboard → login | |
| 19 | 02-navigation | Sidebar | All nav items visible | |
| 20 | 02-navigation | Sidebar | Dashboard link navigates | |
| 21 | 02-navigation | Sidebar | Sanghs link navigates | |
| 22 | 02-navigation | Sidebar | Groups link navigates | |
| 23 | 02-navigation | Sidebar | Meetings link navigates | |
| 24 | 02-navigation | Sidebar | Folders link navigates | |
| 25 | 02-navigation | Sidebar | Settings link navigates | |
| 26 | 02-navigation | Sidebar | Links link navigates | |
| 27 | 02-navigation | Navbar | Brand logo visible | |
| 28 | 02-navigation | Navbar | Brand logo → dashboard | |
| 29 | 02-navigation | Navbar | Search field present | |
| 30 | 02-navigation | Navbar | Notification bell visible | |
| 31 | 02-navigation | Navbar | Profile dropdown opens | |
| 32 | 02-navigation | Navbar | Profile dropdown items | |
| 33 | 02-navigation | Navbar | Profile link navigates | |
| 34 | 02-navigation | Breadcrumbs | Sanghs breadcrumb visible | |
| 35 | 02-navigation | Root | / redirects to /dashboard | |
| 36 | 03-dashboard | Dashboard | Page title correct | |
| 37 | 03-dashboard | Dashboard | All stat cards rendered | |
| 38 | 03-dashboard | Dashboard | Total Fee Collections card | |
| 39 | 03-dashboard | Dashboard | Stat cards have numeric values | |
| 40 | 03-dashboard | Dashboard | Date filter form present | |
| 41 | 03-dashboard | Dashboard | "Today" quick range | |
| 42 | 03-dashboard | Dashboard | "7d" quick range | |
| 43 | 03-dashboard | Dashboard | "30d" quick range | |
| 44 | 03-dashboard | Dashboard | Custom date filter | |
| 45 | 03-dashboard | Dashboard | Open Calendar button | |
| 46 | 03-dashboard | Dashboard | No console errors | |
| 47 | 04-sanghs | Sanghs Index | Title / header | |
| 48 | 04-sanghs | Sanghs Index | Create New button | |
| 49 | 04-sanghs | Sanghs Index | Export button | |
| 50 | 04-sanghs | Sanghs Index | Template button | |
| 51 | 04-sanghs | Sanghs Index | Import modal opens | |
| 52 | 04-sanghs | Sanghs Index | Table/list renders | |
| 53 | 04-sanghs | Sanghs Index | Total Records counter | |
| 54 | 04-sanghs | Sanghs Index | Pagination present | |
| 55 | 04-sanghs | Filters | Vibhag dropdown | |
| 56 | 04-sanghs | Filters | District dropdown | |
| 57 | 04-sanghs | Filters | Status dropdown options | |
| 58 | 04-sanghs | Filters | Ownership dropdown | |
| 59 | 04-sanghs | Filters | From Date field | |
| 60 | 04-sanghs | Filters | To Date field | |
| 61 | 04-sanghs | Filters | Apply Filters submits | |
| 62 | 04-sanghs | Filters | Reset clears filters | |
| 63 | 04-sanghs | Filters | Date range filter | |
| 64 | 04-sanghs | Filters | Vibhag → District JS auto-filter | |
| 65 | 04-sanghs | Create | Form renders | |
| 66 | 04-sanghs | Create | Empty submit validation | |
| 67 | 04-sanghs | Create | Cancel returns to index | |
| 68 | 04-sanghs | Export | Export link has query params | |
| 69 | 04-sanghs | Export | Download starts | |
| 70 | 04-sanghs | Show | Clicking record navigates | |
| 71 | 05-profile | Profile | Page heading | |
| 72 | 05-profile | Profile | Name pre-filled | |
| 73 | 05-profile | Profile | Email pre-filled | |
| 74 | 05-profile | Profile | Save Profile button | |
| 75 | 05-profile | Profile | Reset button | |
| 76 | 05-profile | Profile | Photo upload accepts images | |
| 77 | 05-profile | Profile | Change Password form present | |
| 78 | 05-profile | Profile | Change Password button | |
| 79 | 05-profile | Profile | Wrong current password error | |
| 80 | 05-profile | Profile | Invalid email update error | |
| 81 | 06-meetings | Meetings | Page renders | |
| 82 | 06-meetings | Meetings | Create Meeting button | |
| 83 | 06-meetings | Create | Form all fields | |
| 84 | 06-meetings | Create | Empty submit validation | |
| 85 | 06-meetings | Create | Valid create → success | |
| 86 | 06-meetings | Create | Cancel → list | |
| 87 | 07-groups | Groups | Index renders | |
| 88 | 07-groups | Groups | Create Group button | |
| 89 | 07-groups | Groups | Export CSV present | |
| 90 | 07-groups | Create | Name field present | |
| 91 | 07-groups | Create | Empty submit validation | |
| 92 | 07-groups | Create | Valid create navigates | |
| 93 | 08-settings | Settings | Global settings page | |
| 94 | 08-settings | Settings | Save button | |
| 95 | 08-settings | Sangh Fees | Page renders | |
| 96 | 08-settings | Sangh Fees | Fee form/table present | |
| 97 | 08-settings | Sangh Fees | Add slab button | |
| 98 | 09-admin | User Roles | Page accessible | |
| 99 | 09-admin | Activity Logs | Page accessible | |
| 100 | 09-admin | Activity Logs | Table or empty state | |
| 101 | 10-search | Search | Input in navbar | |
| 102 | 10-search | Search | Query navigates to /search | |
| 103 | 10-search | Search | Results page no 500 | |
| 104 | 10-search | Search | Empty query loads | |
| 105 | 10-search | Search | No-results term loads | |
| 106 | 11-responsive | Responsive | Dashboard no overflow (desktop) | |
| 107 | 11-responsive | Responsive | Sanghs no overflow (tablet/mobile) | |
| 108 | 11-responsive | Responsive | Mobile menu toggle visible | |
| 109 | 11-responsive | Responsive | Login usable on mobile | |
| 110 | 11-responsive | Responsive | Profile form usable on mobile | |
| 111 | 11-responsive | Responsive | Meetings create usable on mobile | |
| 112 | 12-journeys | Journey | Login→Dashboard→Profile→Logout | |
| 113 | 12-journeys | Journey | Login→Sanghs→Filter→Reset | |
| 114 | 12-journeys | Journey | Create Meeting→Appears in list | |
| 115 | 12-journeys | Journey | Global search results | |
| 116 | 12-journeys | Journey | Create Group→Appears in list | |
| 117 | 12-journeys | Journey | Settings→Save→No error | |
| 118 | 13-links | Links | Page renders | |
| 119 | 13-links | Links | Create button present | |
| 120 | 13-folders | Folders | Page renders | |
| 121 | 13-folders | Folders | Create button present | |
| 122 | 13-folders | Folders | No 500 error | |

---

## Browsers / Viewports

| Project | Browser | Viewport |
|---------|---------|----------|
| chromium-desktop | Chrome | 1280×720 |
| tablet | Chrome (iPad) | 768×1024 |
| mobile | Chrome (iPhone 14) | 390×844 |

---

## How to Run

```powershell
# 1. Start Laravel dev server (keep running in another terminal)
php artisan serve

# 2. Run the desktop suite (fastest, pre-push check)
npm run test:e2e

# 3. Run all browsers including tablet + mobile
npm run test:e2e:all

# 4. Open interactive UI debugger
npm run test:e2e:ui

# 5. View the HTML report after a run
npm run test:e2e:report
```
