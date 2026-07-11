# Resident MVP Flow

## Purpose

The resident uses the same authentication system as every other user but has a separate resident workspace.

The resident belongs to the organization through `organization_user`, has the `resident` role, and is connected to a unit through an active `occupancies` record.

Use `UserRole::RESIDENT`.

## 1. Account setup

A resident does not register publicly in the MVP.

Resident setup is handled by the owner or a permitted manager.

Flow:

1. Property already exists.
2. Unit already exists.
3. Owner or manager creates the resident user with credentials.
4. Attach the user to the organization through `organization_user`.
5. Assign the `resident` role using Spatie team permissions.
6. Set `users.current_organization_id`.
7. Create an active occupancy for the selected unit.
8. Send a signed setup/login URL by email.
9. Resident logs in through the main `/login` page.
10. Redirect the resident to `/resident/dashboard`.

There is no `invitations` table.

## 2. Resident dashboard

Minimum dashboard content:

- Current property name
- Current unit number
- Open requests count
- In-progress requests count
- Completed requests count
- Recent maintenance requests
- `Report an Issue` primary action

No charts are required.

The current residence is resolved from the resident's active occupancy.

## 3. Report an issue

The resident opens `Report an Issue`.

Minimum fields:

- Title
- Category
- Priority
- Description
- Optional photos

The resident does not select:

- Organization
- Property
- Unit
- Resident

The backend derives these values from the authenticated resident and active occupancy.

Create the maintenance request with status `open`.

## 4. My requests

The resident can only see maintenance requests where:

```text
resident_id = authenticated user id
```

Minimum list columns:

- Title
- Category
- Priority
- Status
- Unit
- Created date

## 5. Request details

Minimum request detail content:

- Issue title and description
- Category
- Priority
- Property and unit
- Issue photos
- Assigned technician, when assigned
- Current status
- Status history
- Technician completion notes
- Completion photos

The resident cannot assign or change the technician.

## 6. Request lifecycle

Resident workflow:

```text
Open
→ Assigned
→ In Progress
→ Completed
→ Closed
```

When the technician marks the request `completed`, the resident can:

- Confirm Resolution
- Reopen Issue

### Confirm Resolution

Change:

```text
completed → closed
```

Set `closed_at`.

### Reopen Issue

Change:

```text
completed → reopened
```

The resident provides a short note explaining why the issue is not resolved.

Create a status log.

## 7. Resident pages

| Page | Purpose |
|---|---|
| Dashboard | View residence and maintenance summary |
| Report an Issue | Create a maintenance request |
| My Requests | View personal requests |
| Request Details | Track one maintenance request |
| Profile | Update basic personal details and password |

## 8. Access restrictions

The resident:

- Cannot access `/organization/*`.
- Cannot access `/technician/*`.
- Cannot manage properties or units.
- Cannot manage residents or staff.
- Cannot select another organization.
- Cannot select another property or unit when reporting an issue.
- Can only view their own maintenance requests.
- Can only close or reopen a completed request.

Suggested route area:

```text
/resident/*
```

Use:

- `auth`
- current organization context
- `role:resident`

Also enforce access through Laravel policies.

## 9. MVP exclusions

Do not add:

- Rent payments
- Lease management
- Community announcements
- Live chat
- Household members
- Resident-to-resident communication
- Advanced reviews or surveys
