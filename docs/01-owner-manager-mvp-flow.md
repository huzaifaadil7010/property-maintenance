# Owner and Manager MVP Flow

## Purpose

The owner and manager share the same organization workspace.

The **organization is the main tenant**. An organization can manage many properties. Properties are not separate tenants; they are resources inside the current organization.

The owner has full access. Managers use the same pages but only see or perform actions allowed by their Spatie permissions.

## 1. Owner registration and organization creation

Only an owner registers publicly in the MVP.

Registration flow:

1. Owner registers through the main `/register` page.
2. Create the `users` record.
3. Create the `organizations` record.
4. Attach the user to the organization through `organization_user`.
5. Assign the `owner` role in the current organization using Spatie team permissions.
6. Set `users.current_organization_id` to the created organization.
7. Redirect the owner to the organization dashboard.

Use `UserRole::OWNER`.

## 2. Organization context

`organizations` is the tenant table.

The authenticated user's `current_organization_id` is used to resolve the current tenant context.

Organization-owned business data is scoped by `organization_id`.

Do not accept `organization_id` from normal frontend forms. Derive it from the current organization context.

### Property context

A property is **not another tenant**.

Do not add `current_property_id` to `users` for the MVP.

The organization dashboard can have a property selector:

- All Properties
- Green View Apartments
- City Tower

The selected property is only a filter/context inside the current organization.

Use a route parameter or query parameter, for example:

```text
/organization/dashboard?property=12
```

or:

```text
/organization/properties/12/dashboard
```

Always validate that the selected property belongs to the current organization.

## 3. Organization dashboard

Minimum dashboard content:

- Total properties
- Total units
- Open maintenance requests
- In-progress maintenance requests
- Completed maintenance requests
- Recent maintenance requests

No charts are required for the MVP.

When a property is selected, dashboard statistics and lists are filtered by that property.

When `All Properties` is selected, show organization-wide data.

## 4. Manager and staff management

The owner can create a manager.

Manager creation flow:

1. Owner enters name, email, password, and basic user details.
2. Create the `users` record.
3. Attach the user to the organization through `organization_user`.
4. Assign the `manager` role using Spatie team permissions.
5. Assign required permissions.
6. Set the manager's `current_organization_id`.
7. Send a signed setup/login URL by email.

There is no `invitations` table in the MVP.

Owner and manager use the same organization dashboard and pages.

The difference is authorization:

- Owner has full access.
- Manager access is permission-based.

Example permissions:

```text
properties.view
properties.create
properties.update
units.manage
residents.manage
technicians.manage
maintenance_requests.view
maintenance_requests.assign
organization.update
```

Keep the permission list minimal and add permissions only when the MVP page requires them.

## 5. Property management

Owner or permitted manager can create multiple properties.

Minimum property fields:

- Name
- Type
- Address
- City

Flow:

1. Open Properties.
2. Create a property.
3. The backend assigns the current `organization_id`.
4. Redirect to the property details or units page.

A property belongs to exactly one organization.

## 6. Unit management

Each property has many units/apartments.

Minimum unit fields:

- Name or unit number
- Floor
- Status

Example unit names:

```text
A-101
A-102
B-201
```

Flow:

1. Open a property.
2. Open Units.
3. Create the unit.
4. The backend derives `organization_id` from the current organization and `property_id` from the selected property.

## 7. Resident creation and occupancy

Property and unit must exist before assigning a resident.

Resident creation flow:

1. Owner or permitted manager opens a unit.
2. Click `Add Resident`.
3. Enter resident name, email, password, phone, and move-in date.
4. Create the `users` record.
5. Attach the user to the organization through `organization_user`.
6. Assign the `resident` role using Spatie team permissions.
7. Set `users.current_organization_id`.
8. Create an active `occupancies` record for the selected unit.
9. Update the unit status to `occupied`.
10. Send a signed setup/login URL by email.

The `occupancies` table keeps the history between residents and units.

Do not store `resident_id` directly on `units`.

## 8. Technician creation

Owner or permitted manager creates a technician with full credentials.

Technician creation flow:

1. Enter name, email, password, and basic user details.
2. Create the `users` record.
3. Attach the user to the organization through `organization_user`.
4. Assign the `technician` role using Spatie team permissions.
5. Set `users.current_organization_id`.
6. Create the `technician_profiles` record.
7. Store the technician specialty such as plumbing or electrical.
8. Send a signed setup/login URL by email.

Use one `technician` role.

Do not create `plumber`, `electrician`, or `ac_technician` roles unless their permissions become different.

Trade type belongs in `technician_profiles.specialty`.

## 9. Maintenance request management

A resident creates a maintenance request from the resident workspace.

The manager can:

- View organization maintenance requests.
- Filter by property and status.
- Open request details.
- Assign a technician.
- Review status history and completion details.

Assignment flow:

1. Open an `open` or `reopened` request.
2. Select an available technician.
3. Validate that the technician belongs to the current organization.
4. Assign the technician.
5. Change status to `assigned`.
6. Create a status log.
7. Send a notification to the technician.

## 10. Main organization pages

Minimum MVP pages:

| Page | Purpose |
|---|---|
| Dashboard | Organization statistics and recent requests |
| Properties | List and create properties |
| Property Details | View property summary |
| Units | Manage property units |
| Residents | View and create residents |
| Managers | View and create managers |
| Technicians | View and create technicians |
| Maintenance Requests | Review and filter requests |
| Maintenance Request Details | Assign technician and review progress |
| Organization Settings | Update basic organization details |

## 11. Route and access model

Use one authentication system.

Suggested route area:

```text
/organization/*
```

Access requires:

- `auth`
- current organization context
- `owner` or `manager` role
- permission middleware where required

Owner and manager use the same pages and layouts.

Do not create a separate manager dashboard.

## 12. MVP exclusions

Do not add:

- Billing or subscriptions
- Vendor companies
- Inventory or spare parts
- Technician GPS tracking
- Payroll
- Advanced analytics
- Property-level tenancy
- `current_property_id`
- Separate login systems
