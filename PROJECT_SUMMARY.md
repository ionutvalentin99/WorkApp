# AplicatiePontaj - Project Summary

## Overview
Employee timesheet and vacation management web application for Romanian businesses. Allows companies to track employee work hours, manage vacation requests, handle multi-user access, and process payments.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2+, Symfony 7.4 |
| ORM | Doctrine ORM 2.14 + Migrations 3.2 |
| Database | MariaDB 10.4+ / MySQL 8.0+ |
| Templates | Twig 3.0 |
| Frontend | React 18.3+, Stimulus 3.0, Bootstrap 5 |
| Assets | Webpack Encore 4.0+ |
| Payments | Stripe PHP SDK 15.4 |
| Email | Mailtrap SMTP (dev), SymfonyCasts bundles |
| Dev Env | Docker Compose |

---

## Database Entities

### User
- id, username, email, firstName, lastName, password, roles (JSON), isVerified, created, updated
- Owns companies, has work records, has vacation requests

### Company
- id, name, phone_number, address, city, country, owner_id (FK→User), is_paid, is_searchable
- ManyToMany with User via `user_company` junction table

### Work (Timesheet Records)
- id, user_id, company_id, time_start, time_end, date, details, record_id (UUID), created, updated

### Holiday (Vacation Requests)
- id, user_id, start_date, end_date, status (pending/approved/denied), details (5000 chars), created, updated, approved_at
- JoinColumn nullable: false — deletion must remove holidays first (handled in UserController::deleteAccount)

### CompanyRequest (Join/Invite Workflow)
- id, user_id, company_id, type (JOIN_REQUEST/INVITATION), status (PENDING/ACCEPTED/REJECTED), createdAt
- onDelete CASCADE on both user_id and company_id

### ResetPasswordRequest
- id, user_id, selector, hashed_token, requested_at, expires_at

### Notification
- id, user_id (onDelete CASCADE), message, type (info/success/warning/danger), isRead, link (nullable), createdAt
- Used for real-time in-app notifications

### AppSetting
- id, key (unique), value
- Key/value store for runtime application configuration
- Initial value: `registration_enabled = 0`

---

## Main Features

### Timesheet Management (Pontaj)
- Log work intervals with start/end times and details
- Daily view of active records
- Historical search by date or date range
- Company-wide record view for admins
- UUID tracking per record

### Vacation Management (Concediu)
- Submit vacation requests with date range and reason
- Status workflow: pending → approved/denied
- Admin approval interface with approved_at timestamp

### Company Management
- Create/edit companies (name, phone, address, city, country)
- Search and join companies
- Multi-company support (users can belong to multiple companies)
- Session-based active company selection via `ActiveCompanyService`
- Company header shows initials avatar (placeholder for future logo upload)

#### Join Request / Invitation system
- User sends `JOIN_REQUEST` → owner gets notification
- Owner accepts/rejects from `/user/company/requests`
- Owner sends `INVITATION` → user gets notification
- User accepts/rejects from `/user/invitations`
- All actions trigger bidirectional notifications

#### Member Management
- Owner views all members at `/user/company/members`
- Owner can remove any member (member gets notification)
- Owner cannot remove themselves

### Notification System
- `Notification` entity persisted in DB per user
- `NotificationService`: `notify()`, `getUnreadCount()`, `getRecent()`, `markAllRead()`, `markRead()`
- `NotificationExtension` Twig functions: `notification_count()`, `recent_notifications()`
- **Desktop navbar**: bell icon with red badge + dropdown showing last 5 notifications
- **Mobile navbar**: red badge on avatar button + notifications section inside offcanvas menu
- Notifications page `/user/notifications` — marks all as read on open

### Payment (Stripe)
- One-time payment of RON 100.00
- Checkout session with company context
- Success/failure callbacks marking company as paid
- TODO: Stripe webhook for production

### Authentication & Users
- Form-based login with remember-me (7 days)
- Email verification (SymfonyCasts)
- Password reset with token expiration
- Roles: ROLE_USER, ROLE_ADMIN
- Registration enabled/disabled via admin panel (DB-controlled, default: disabled)

### Admin Settings
- `AppSetting` key/value table controls runtime behavior
- `AppSettingService`: `getBool()`, `setBool()`, `isRegistrationEnabled()`
- Admin-only page at `/admin/settings` (requires ROLE_ADMIN, uses `#[IsGranted]`)
- Currently configurable: registration enabled/disabled
- Sidebar item "Aplicație" visible only to ROLE_ADMIN users

---

## Controllers (16 total)

| Controller | Responsibility |
|---|---|
| HomeController | Dashboard with recent work records |
| PontajController | User timesheet interface |
| PontajeAdminController | Admin timesheet view |
| CompanyController | Company CRUD, members, requests, invitations |
| ConcediuController | Vacation requests |
| AdminHolidaysController | Admin vacation approval |
| AdminSettingsController | Admin application settings (ROLE_ADMIN) |
| NotificationController | List notifications, mark as read |
| PaymentController | Stripe checkout flow |
| RegistrationController | Registration + email verification |
| SecurityController | Login/logout |
| ResetPasswordController | Password reset |
| ProfileController | User profile |
| UserController | Account settings, delete account |
| UserCrudController | Admin user management |

---

## Key Routes

| Route | Access | Description |
|---|---|---|
| `/` | Public | Dashboard |
| `/login` | Public | Login |
| `/forgot-password` | Public | Password reset |
| `/user/work` | Authenticated | Timesheet main page |
| `/user/work/your-records` | Authenticated | Work history + search |
| `/user/work/company-records` | Authenticated | Company-wide records |
| `/user/vacation/new-vacation` | Authenticated | Submit vacation request |
| `/user/vacation/history` | Authenticated | Vacation history |
| `/user/company/` | Authenticated | Company dashboard |
| `/user/company/new` | Authenticated | Create company |
| `/user/company/members` | Authenticated (owner) | Manage members |
| `/user/company/members/{id}/remove` | Authenticated (owner) | Remove member |
| `/user/company/requests` | Authenticated (owner) | View join requests |
| `/user/company/requests/{id}/accept` | Authenticated (owner) | Accept join request |
| `/user/company/requests/{id}/reject` | Authenticated (owner) | Reject join request |
| `/user/company/invite` | Authenticated (owner) | Search users to invite |
| `/user/company/invite/{id}` | Authenticated (owner) | Send invitation |
| `/user/invitations` | Authenticated | View received invitations |
| `/user/invitations/{id}/accept` | Authenticated | Accept invitation |
| `/user/invitations/{id}/reject` | Authenticated | Reject invitation |
| `/user/notifications` | Authenticated | All notifications (marks all read) |
| `/user/notifications/{id}/read` | Authenticated | Mark single notification read + redirect |
| `/admin/vacations/pending` | ROLE_ADMIN | Review vacation requests |
| `/admin/settings` | ROLE_ADMIN | Application settings |
| `/admin/settings/toggle-registration` | ROLE_ADMIN | Toggle registration on/off |
| `/payment/checkout` | Authenticated | Stripe checkout |

---

## Project Structure

```
AplicatiePontaj/
├── src/
│   ├── Controller/       # 15 HTTP controllers
│   ├── Entity/           # 8 Doctrine entities
│   ├── Repository/       # Database query classes
│   ├── Form/             # 11 Symfony form types
│   ├── Service/          # ActiveCompanyService, StripeService, UuidService,
│   │                     # NotificationService, AppSettingService
│   ├── Security/         # Authentication handlers
│   └── Twig/             # ActiveCompanyExtension, NotificationExtension
├── config/               # Symfony configuration (packages, routes, services)
├── templates/
│   ├── pontaj/           # Timesheet templates
│   ├── concediu/         # Vacation templates
│   ├── company/          # index, new, search, members, requests, invite, invitations
│   ├── notifications/    # Notifications list page
│   ├── admin/            # Admin settings page
│   ├── payment/          # Stripe pages
│   └── PageElements/     # Navbar (desktop+mobile), footer, account sidebar
├── migrations/           # 7 Doctrine migration versions
├── assets/               # Frontend source (JS, CSS, React, Stimulus)
├── public/               # Web root (compiled assets in build/)
├── tests/                # PHPUnit tests
├── docker-compose.yml
├── webpack.config.js
├── composer.json
└── package.json
```

---

## Services

| Service | Responsibility |
|---|---|
| `ActiveCompanyService` | Session-based active company selection |
| `StripeService` | Stripe checkout session creation |
| `UuidService` | UUID generation for work records |
| `NotificationService` | Create, read, mark notifications |
| `AppSettingService` | Read/write runtime app settings from DB |

---

## Twig Extensions

| Extension | Functions |
|---|---|
| `ActiveCompanyExtension` | `active_company()` |
| `NotificationExtension` | `notification_count()`, `recent_notifications()` |

---

## Environment Configuration (.env)

```
APP_ENV=dev
DATABASE_URL=mysql://root:@127.0.0.1:3306/AplicatiePontaj?serverVersion=10.4.32-MariaDB&charset=utf8mb4
MESSENGER_TRANSPORT_DSN=doctrine://default
MAILER_DSN=smtp://...@sandbox.smtp.mailtrap.io:2525
```

---

## Migration History

| Version | Description |
|---|---|
| 20251221121923 | Initial schema creation |
| 20260217225614 | Early adjustments |
| 20260309233538 | Entity refinements |
| 20260330205500 | User-Company: ManyToOne → ManyToMany |
| 20260330223035 | Latest adjustments |
| 20260404203940 | Add Notification entity |
| 20260404212032 | Add AppSetting entity + seed registration_enabled=0 |

---

## Known TODOs

- Stripe webhook endpoint for production payment confirmation
- Company logo upload (currently using initials avatar as placeholder)
- Mobile offcanvas: "Invitațiile mele" link not yet in mobile menu
