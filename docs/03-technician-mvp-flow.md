# Technician MVP Flow

## Purpose

The technician uses the same authentication system but has a separate job-focused workspace.

The technician belongs to the organization through `organization_user`, has the `technician` role, and has technician-specific details in `technician_profiles`.

Use `UserRole::TECHNICIAN`.

## 1. Account setup

A technician does not register publicly in the MVP.

Owner or a permitted manager creates the technician.

Flow:

1. Create the `users` record with full credentials.
2. Attach the user to the organization through `organization_user`.
3. Assign the `technician` role using Spatie team permissions.
4. Set `users.current_organization_id`.
5. Create the `technician_profiles` record.
6. Store specialty, phone, and availability.
7. Send a signed setup/login URL by email.
8. Technician logs in through the main `/login` page.
9. Redirect the technician to `/technician/dashboard`.

There is no `invitations` table.

## 2. Technician role and specialty

Use one Spatie role:

```text
technician
```

Store trade type in:

```text
technician_profiles.specialty
```

Example specialties:

```text
plumbing
electrical
air_conditioning
carpentry
general_maintenance
```

A plumber is therefore:

- A user
- An organization member
- Has the `technician` role
- Has `technician_profiles.specialty = plumbing`

## 3. Technician dashboard

Minimum dashboard content:

- Assigned jobs count
- In-progress jobs count
- Completed jobs count
- Current assigned jobs

Each job can show:

- Title
- Priority
- Property
- Unit
- Category
- Status

No charts are required.

## 4. My jobs

The technician can only see requests where:

```text
assigned_technician_id = authenticated user id
```

Minimum filters:

- Assigned
- In Progress
- Completed

Do not add scheduling or calendar features in the MVP.

## 5. Job details

Minimum job details:

- Issue title
- Description
- Category
- Priority
- Property
- Unit
- Resident name and contact
- Issue photos
- Status history
- Completion details

The technician cannot reassign the request.

## 6. Start work

For an assigned request, the technician can click `Start Work`.

Change:

```text
assigned → in_progress
```

Create a maintenance request status log.

## 7. Complete work

The technician enters:

- Completion notes
- Optional actual cost
- Optional completion photos

Then clicks `Complete Work`.

Change:

```text
in_progress → completed
```

Set `completed_at`.

Create a status log and notify the resident.

The technician does not close the request.

The resident confirms resolution or reopens the issue.

## 8. Technician pages

| Page | Purpose |
|---|---|
| Dashboard | View workload summary |
| My Jobs | View assigned maintenance requests |
| Job Details | Review and update an assigned job |
| Complete Job | Submit completion details |
| Profile | Update phone and availability |

Specialty is managed by the owner or manager in the MVP.

## 9. Access restrictions

The technician:

- Cannot access `/organization/*`.
- Cannot access `/resident/*`.
- Cannot create properties or units.
- Cannot create residents, managers, or technicians.
- Cannot assign or reassign maintenance requests.
- Cannot view jobs assigned to another technician.
- Cannot close requests for residents.
- Cannot access another organization's data.

Suggested route area:

```text
/technician/*
```

Use:

- `auth`
- current organization context
- `role:technician`

Also enforce request ownership through Laravel policies.

## 10. MVP exclusions

Do not add:

- Job bidding
- Job acceptance flow
- Scheduling calendar
- GPS tracking
- Time tracking
- Inventory or parts usage
- Technician payments
- Payroll
