# MVP Database Structure

## Purpose

This schema supports the minimal property maintenance portfolio MVP.

The main tenant is `organizations`.

Use Laravel naming conventions:

- Plural `snake_case` table names
- Singular `snake_case` foreign keys
- `id` as the default primary key
- `created_at` and `updated_at` where normal Eloquent timestamps are useful

Use PHP backed enums with string database columns.

## 1. Architecture decisions

- `organizations` is the tenant table.
- Major organization-owned business tables contain `organization_id`.
- `users` is shared globally.
- Users join organizations through `organization_user`.
- `users.current_organization_id` stores the active organization context and is nullable.
- Spatie Laravel Permission uses organization-scoped team permissions.
- Configure the Spatie team foreign key as `organization_id`.
- Use `UserRole` for the role enum.
- Property is not a tenant.
- Do not add `current_property_id`.
- Property selection is a route/query filter inside the current organization.
- Use one `technician` role and store the trade in `technician_profiles.specialty`.
- Do not create an `invitations` table.
- Owner/manager creates resident and technician credentials and sends a signed setup/login URL.

## 2. Final MVP table list

### Core application

```text
users
organizations
organization_user
properties
units
occupancies
technician_profiles
```

### Maintenance

```text
maintenance_requests
maintenance_request_attachments
maintenance_request_status_logs
```

### Spatie permission

```text
roles
permissions
model_has_roles
model_has_permissions
role_has_permissions
```

### Laravel support

```text
notifications
password_reset_tokens
sessions
```

`sessions` is required only when `SESSION_DRIVER=database`.

---

## 3. `users`

Stores every authenticated user: owner, manager, resident, and technician.

| Column | Type | Null / Default | Purpose |
|---|---|---|---|
| `id` | `bigint unsigned` | PK | Primary key |
| `current_organization_id` | `foreignId` | nullable | Current tenant context |
| `name` | `string` | required | Display name |
| `email` | `string` | required | Login email |
| `email_verified_at` | `timestamp` | nullable | Email verification time |
| `password` | `string` | required | Hashed password |
| `phone` | `string` | nullable | General contact number |
| `remember_token` | `string` | nullable | Laravel remember token |
| `created_at` | `timestamp` | required | Created time |
| `updated_at` | `timestamp` | required | Updated time |

Indexes and constraints:

- Unique index on `email`.
- Index `current_organization_id`.
- `current_organization_id` references `organizations.id`.
- Prefer `nullOnDelete()` for `current_organization_id`.

Migration note:

Because `users.current_organization_id` references `organizations`, create the organization table first or add the nullable foreign key in a later migration.

---

## 4. `organizations`

Main tenant table representing a property-management business.

| Column | Type | Null / Default | Purpose |
|---|---|---|---|
| `id` | `bigint unsigned` | PK | Primary key |
| `name` | `string` | required | Organization name |
| `slug` | `string` | required | URL-friendly identifier |
| `email` | `string` | nullable | Business email |
| `phone` | `string` | nullable | Business phone |
| `created_at` | `timestamp` | required | Created time |
| `updated_at` | `timestamp` | required | Updated time |

Indexes and constraints:

- Unique index on `slug`.

---

## 5. `organization_user`

Membership pivot connecting users to organizations.

Roles are handled by Spatie Permission, not by this pivot.

| Column | Type | Null / Default | Purpose |
|---|---|---|---|
| `id` | `bigint unsigned` | PK | Membership primary key |
| `organization_id` | `foreignId` | required | Organization |
| `user_id` | `foreignId` | required | Member user |
| `is_active` | `boolean` | default `true` | Active membership |
| `created_at` | `timestamp` | required | Created time |
| `updated_at` | `timestamp` | required | Updated time |

Indexes and constraints:

- Unique composite index on `organization_id`, `user_id`.

---

## 6. `properties`

Stores buildings or managed locations inside an organization.

| Column | Type | Null / Default | Purpose |
|---|---|---|---|
| `id` | `bigint unsigned` | PK | Primary key |
| `organization_id` | `foreignId` | required | Owning tenant |
| `name` | `string` | required | Property name |
| `type` | `string` | default `apartment` | Property type |
| `address` | `string` | required | Street address |
| `city` | `string` | required | City |
| `created_at` | `timestamp` | required | Created time |
| `updated_at` | `timestamp` | required | Updated time |

Indexes and constraints:

- Index `organization_id`.
- Optional unique composite index on `organization_id`, `name`.

Property is not a tenant and does not require a `current_property_id` user column.

---

## 7. `units`

Stores apartments or units inside a property.

| Column | Type | Null / Default | Purpose |
|---|---|---|---|
| `id` | `bigint unsigned` | PK | Primary key |
| `organization_id` | `foreignId` | required | Direct tenant scope |
| `property_id` | `foreignId` | required | Parent property |
| `name` | `string` | required | Unit label, e.g. A-101 |
| `floor` | `string` | nullable | Floor label |
| `status` | `string` | default `vacant` | Unit status |
| `created_at` | `timestamp` | required | Created time |
| `updated_at` | `timestamp` | required | Updated time |

Indexes and constraints:

- Unique composite index on `property_id`, `name`.
- Index `organization_id`, `status`.

---

## 8. `occupancies`

Links residents to units and preserves move-in/move-out history.

| Column | Type | Null / Default | Purpose |
|---|---|---|---|
| `id` | `bigint unsigned` | PK | Primary key |
| `organization_id` | `foreignId` | required | Direct tenant scope |
| `unit_id` | `foreignId` | required | Occupied unit |
| `resident_id` | `foreignId` | required | User with resident role |
| `starts_at` | `date` | required | Move-in date |
| `ends_at` | `date` | nullable | Move-out date |
| `status` | `string` | default `active` | Occupancy status |
| `created_at` | `timestamp` | required | Created time |
| `updated_at` | `timestamp` | required | Updated time |

Indexes and constraints:

- Index `organization_id`, `resident_id`.
- Index `unit_id`, `status`.
- For the MVP, enforce one active occupancy per unit through application validation.

A resident can have multiple occupancy records over time.

The same unit can have different residents over time.

---

## 9. `technician_profiles`

Stores technician-only business details.

| Column | Type | Null / Default | Purpose |
|---|---|---|---|
| `id` | `bigint unsigned` | PK | Primary key |
| `organization_id` | `foreignId` | required | Direct tenant scope |
| `user_id` | `foreignId` | required | User with technician role |
| `specialty` | `string` | required | Technician trade |
| `phone` | `string` | nullable | Work contact number |
| `is_available` | `boolean` | default `true` | Simple availability |
| `created_at` | `timestamp` | required | Created time |
| `updated_at` | `timestamp` | required | Updated time |

Indexes and constraints:

- Unique composite index on `organization_id`, `user_id`.
- Index `organization_id`, `specialty`, `is_available`.

Do not create separate plumber or electrician profile tables.

---

## 10. `maintenance_requests`

Main maintenance workflow table.

| Column | Type | Null / Default | Purpose |
|---|---|---|---|
| `id` | `bigint unsigned` | PK | Primary key |
| `organization_id` | `foreignId` | required | Direct tenant scope |
| `property_id` | `foreignId` | required | Property at report time |
| `unit_id` | `foreignId` | required | Unit at report time |
| `resident_id` | `foreignId` | required | Reporting resident |
| `assigned_technician_id` | `foreignId` | nullable | Assigned technician user |
| `title` | `string` | required | Short issue title |
| `description` | `text` | required | Issue details |
| `category` | `string` | required | Maintenance category |
| `priority` | `string` | default `normal` | Request priority |
| `status` | `string` | default `open` | Workflow status |
| `completion_notes` | `text` | nullable | Technician completion summary |
| `actual_cost` | `decimal(12,2)` | nullable | Optional repair cost |
| `completed_at` | `timestamp` | nullable | Technician completion time |
| `closed_at` | `timestamp` | nullable | Resident confirmation time |
| `created_at` | `timestamp` | required | Created time |
| `updated_at` | `timestamp` | required | Updated time |

Indexes:

- `organization_id`, `status`
- `assigned_technician_id`, `status`
- `resident_id`, `status`
- `property_id`, `unit_id`

---

## 11. `maintenance_request_attachments`

Stores issue and completion photos/files.

| Column | Type | Null / Default | Purpose |
|---|---|---|---|
| `id` | `bigint unsigned` | PK | Primary key |
| `organization_id` | `foreignId` | required | Direct tenant scope |
| `maintenance_request_id` | `foreignId` | required | Parent request |
| `uploaded_by` | `foreignId` | required | Uploading user |
| `type` | `string` | default `issue` | Attachment type |
| `file_path` | `string` | required | Storage path |
| `original_name` | `string` | required | Original file name |
| `mime_type` | `string` | nullable | File MIME type |
| `size` | `bigint unsigned` | nullable | Size in bytes |
| `created_at` | `timestamp` | required | Uploaded time |
| `updated_at` | `timestamp` | required | Updated time |

Indexes:

- `maintenance_request_id`, `type`
- `organization_id`

---

## 12. `maintenance_request_status_logs`

Immutable history of maintenance request status changes.

| Column | Type | Null / Default | Purpose |
|---|---|---|---|
| `id` | `bigint unsigned` | PK | Primary key |
| `organization_id` | `foreignId` | required | Direct tenant scope |
| `maintenance_request_id` | `foreignId` | required | Parent request |
| `changed_by` | `foreignId` | required | User who changed status |
| `from_status` | `string` | nullable | Previous status |
| `to_status` | `string` | required | New status |
| `notes` | `text` | nullable | Optional transition note |
| `created_at` | `timestamp` | required | Change time |

Indexes:

- `maintenance_request_id`, `created_at`
- `organization_id`

This table does not require `updated_at` because status history should be immutable.

---

## 13. Spatie role and permission tables

Enable Spatie team permissions and use `organization_id` as the team foreign key.

Logical structure:

| Table | Important columns | Purpose |
|---|---|---|
| `roles` | `id`, `organization_id`, `name`, `guard_name` | Organization-aware roles |
| `permissions` | `id`, `name`, `guard_name` | Application abilities |
| `model_has_roles` | `organization_id`, `role_id`, `model_type`, `model_id` | Organization-scoped role assignment |
| `model_has_permissions` | `organization_id`, `permission_id`, `model_type`, `model_id` | Direct scoped permission assignment |
| `role_has_permissions` | `permission_id`, `role_id` | Role permission mapping |

Recommended roles:

```text
owner
manager
resident
technician
```

Use the enum:

```php
enum UserRole: string
{
    case OWNER = 'owner';
    case MANAGER = 'manager';
    case RESIDENT = 'resident';
    case TECHNICIAN = 'technician';
}
```

Do not use `OrganizationRole`.

---

## 14. Laravel support tables

### `notifications`

Use Laravel database notifications for:

- Technician assignment
- Request completion
- Request reopening
- Request status changes when useful

### `password_reset_tokens`

Use Laravel password setup/reset support.

Signed setup/login URLs may be used when sending credentials or initial access instructions.

### `sessions`

Include when using:

```text
SESSION_DRIVER=database
```

---

## 15. Relationship summary

```text
Organization
├── Members through organization_user
├── Properties
│   └── Units
│       └── Occupancies
│           └── Resident users
├── Technician Profiles
│   └── Technician users
└── Maintenance Requests
    ├── Property
    ├── Unit
    ├── Resident
    ├── Assigned Technician
    ├── Attachments
    └── Status Logs
```

Main relationships:

- Organization has many properties.
- Property belongs to an organization.
- Property has many units.
- Unit has many occupancies over time.
- User belongs to many organizations through `organization_user`.
- Resident connects to a unit through `occupancies`.
- Technician-specific data belongs in `technician_profiles`.
- Maintenance request belongs to an organization, property, unit, and resident.
- Maintenance request optionally belongs to an assigned technician.

---

## 16. Current organization and property context

### Current organization

`users.current_organization_id` stores the active tenant context.

The application can resolve the current organization from the authenticated user.

All organization-scoped models should use this context for tenant filtering.

Also use policies and validation. Do not trust a global scope alone for authorization.

### Current property

Do not store `current_property_id`.

The property selector is a filter inside the organization workspace.

Example:

```text
All Properties
Green View Apartments
City Tower
```

Resolve the selected property from the route or query string and validate:

```text
property.organization_id = current organization id
```

When no property is selected, show data across all properties in the organization.

---

## 17. Required tenant-safety rules

- Never accept `organization_id` from normal frontend forms.
- Derive `organization_id` from the current organization context.
- Validate that property belongs to the current organization.
- Validate that unit belongs to the selected property and current organization.
- Validate that resident belongs to the current organization.
- Validate that technician belongs to the current organization.
- Resident request creation derives property and unit from active occupancy.
- Technician can only view requests assigned to their user id.
- Use Laravel policies in addition to role/permission route middleware.
- Owner and manager share pages, but manager actions remain permission-based.

---

## 18. Recommended enum values

### `UserRole`

```text
owner
manager
resident
technician
```

### `UnitStatus`

```text
vacant
occupied
under_maintenance
```

### `OccupancyStatus`

```text
active
ended
```

### `TechnicianSpecialty`

```text
plumbing
electrical
air_conditioning
carpentry
general_maintenance
```

### `MaintenanceCategory`

```text
plumbing
electrical
air_conditioning
carpentry
general
```

### `MaintenancePriority`

```text
low
normal
high
urgent
```

### `MaintenanceRequestStatus`

```text
open
assigned
in_progress
completed
closed
reopened
```

### `AttachmentType`

```text
issue
completion
```

---

## 19. Final MVP principle

Keep the system focused on one workflow:

```text
Owner registers
→ Organization created
→ Properties and units created
→ Residents and technicians created
→ Resident reports an issue
→ Manager assigns a technician
→ Technician starts and completes work
→ Resident confirms or reopens
```

Do not expand into billing, leases, inventory, vendors, scheduling, GPS, payroll, or advanced analytics before the core maintenance flow is complete.
