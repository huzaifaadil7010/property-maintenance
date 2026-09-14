# Property Maintenance — Project Overview

## Document purpose

This document is the high-level source of truth for the **Property Maintenance** SaaS MVP. It is written to give AI tools, designers, product advisers, marketers, and future collaborators enough context to understand the business, users, workflows, product boundaries, and visual direction without reading the source code.

This overview describes the functionality currently implemented in the application as of September 2026. It does not present roadmap ideas as completed features.

## Product summary

Property Maintenance is a multi-tenant SaaS application for organizing residential property operations and maintenance work. It brings property owners, residents, and maintenance technicians into one connected workflow, from the moment a problem is reported until the resident confirms that it has been resolved.

The product replaces fragmented communication through calls, messages, spreadsheets, and informal follow-ups with a clear system of record. Owners can oversee their portfolio and workforce, residents can report and track issues, and technicians can manage assigned work with the context they need.

The central product promise is:

> Manage properties, units, residents, technicians, and maintenance work through one clear, accountable workflow.

## Public landing page

The public welcome page presents the product as one accountable residential-maintenance workflow. It combines a dashboard preview with a problem-and-solution comparison, core capabilities, the five-step **Report → Assign → Start Work → Complete → Confirm** lifecycle, role-specific benefits, pricing content, and final calls to action.

Its responsive header links directly to the Features, Workflow, Roles, and Pricing sections, with a mobile navigation drawer. Guests are directed to log in or create an owner account, while authenticated users are directed to their dashboard. The page follows the warm-premium light visual system used throughout the application. Pricing, trial, testimonials, and performance figures shown on this page are marketing content only; billing and subscription management are not implemented in the current MVP.

## Business model and market position

Property Maintenance is designed as a business-to-business SaaS product for residential property owners and property-management operations. The owner is the primary customer and administrative user. Residents and technicians use focused role-specific workspaces provided through the owner's organization.

The MVP is best suited to small and growing property portfolios that need more structure than messaging apps and spreadsheets but do not need the cost or complexity of a large enterprise property-management suite.

The current product focuses specifically on maintenance operations and the supporting property, unit, resident, and technician records. It is not a rent collection, lease administration, accounting, or general community-management platform.

## Primary users

### Property owner

The owner creates the organization and operates the administrative workspace. The owner can see organization-wide information, manage portfolio records, create resident and technician accounts, oversee maintenance requests, assign technicians, and review an audit-style activity history.

### Resident

The resident lives in a unit through an active occupancy record. The resident has a simplified workspace for seeing their current residence, reporting maintenance issues, tracking their own requests, reviewing evidence and progress, and accepting or rejecting completed work.

### Technician

The technician belongs to the organization and has a defined trade specialty and availability state. The technician receives assigned jobs, sees only their own workload, starts work, and submits completion evidence.

### Manager role status

A manager role exists in the product's domain model, but a complete manager-management interface and manager-access workspace are not part of the currently shipped MVP. Current organization administration is restricted to the owner role. Future product suggestions must not describe manager administration as an available feature unless it is explicitly labeled as future work.

## Tenant and access model

The organization is the SaaS tenant and represents one property-management business. An organization can contain multiple properties, and each property can contain multiple units. A property is a portfolio resource, not a separate tenant.

Business data is isolated by the current organization. Users share one authentication system but are directed to different workspaces according to their role:

- Owners use the organization workspace.
- Residents use the resident workspace.
- Technicians use the technician workspace.

Role boundaries are intentional. Residents cannot open the organization or technician areas, technicians cannot access another technician's jobs, and organization data is restricted to its owning tenant. Residents can see only requests they submitted, while technicians can see only requests assigned to them.

## Owner and organization workspace

### Organization dashboard

The organization dashboard gives the owner a concise operational overview rather than an analytics-heavy experience. It includes:

- Total properties.
- Total units.
- Vacant and occupied unit counts.
- Active resident count.
- Available technician count.
- Open, in-progress, and completed maintenance-request totals.
- A recent organization activity feed.
- Quick links into important operational areas.

Dashboard data is loaded progressively so the main interface can appear quickly while summary data is prepared.

### Property management

Owners can view, search, paginate, create, edit, and delete properties. Each property records:

- Property name.
- Property type; the current MVP supports apartments.
- Street address.
- City.
- Total unit count shown in the listing.
- Creation date.

Property create and edit actions use focused dialogs within the listing experience. Deletion uses confirmation to reduce accidental removal.

### Unit management

Owners can view, search, paginate, create, edit, and delete units across their properties. Each unit records:

- Parent property.
- Unit name or number.
- Floor.
- Operational status.
- Creation date.

Supported unit statuses are **Vacant**, **Occupied**, and **Under maintenance**. Unit names must be unique within their property. Unit create and edit actions are handled in dialogs, and destructive actions require confirmation.

### Resident management

Owners can view, search, paginate, and create residents. The resident directory shows:

- Name.
- Email address.
- Phone number.
- Current property.
- Current unit.
- Move-in date.

When creating a resident, the owner selects a property and an available unit. The system creates the user account, organization membership, resident role, and active occupancy together. It prevents two active residents from being assigned to the same unit and marks the selected unit as occupied.

The new resident receives a queued account email containing their login email, a temporary password, and a sign-in link. The resident can then update their credentials through account settings.

The occupancy relationship is historical by design: residence is represented separately instead of permanently attaching a resident directly to a unit.

### Technician management

Owners can view, search, paginate, and create technicians. The technician directory shows:

- Name.
- Email address.
- Phone number.
- Trade specialty.
- Number of assigned maintenance requests.
- Availability state.

Supported specialties are **Plumbing**, **Electrical**, **Air conditioning**, **Carpentry**, and **General maintenance**. A technician is represented by one technician role plus a specialty, rather than separate roles for every trade.

Creating a technician establishes the user account, organization membership, technician role, specialist profile, and availability state. The technician receives a queued email with a temporary password and sign-in link.

### Organization maintenance-request management

Owners can view, search, and paginate all maintenance requests in their organization. The listing provides operational context including:

- Request title.
- Property and unit.
- Resident.
- Assigned technician, when present.
- Category.
- Priority.
- Current status.
- Creation date.

The owner can open a request to review its complete details, issue description, location, resident, assigned technician, issue photos, completion photos, technician notes, actual cost, important timestamps, and chronological status history.

From the detail experience, the owner can:

- Assign an available technician.
- Reassign a request to a different available technician.
- Add an optional assignment note.
- Update the request status after a technician has been assigned.
- Add optional notes to status changes.
- Review who made each recorded status change.

Assigning a technician sends that technician a queued email containing the job's essential context.

### Organization activity history

The product maintains a human-readable organization activity feed. The dashboard shows recent events, and the full activity page loads older events progressively through infinite scrolling.

Tracked activity includes:

- Property creation, updates, and deletion.
- Unit creation, updates, and deletion.
- Resident account creation.
- Technician account creation.
- Maintenance request creation.
- Technician assignment.
- Administrative status updates.
- Technician work started and completed.
- Resident resolution confirmation.
- Resident request reopening.

Each event communicates what happened, who acted, and when it happened. Color and icon treatments distinguish event categories while keeping the feed calm and readable.

## Resident workspace

### Resident dashboard

The resident dashboard provides a personal maintenance snapshot. It includes:

- Current property and unit information resolved from the resident's active occupancy.
- Open request count.
- In-progress request count.
- Completed request count.
- Five recent maintenance requests.
- A prominent **Report an Issue** action.

If a resident has no active residence, the interface presents a safe empty state and prevents issue submission instead of exposing unrelated property or unit choices.

### Reporting an issue

Residents report an issue through an in-page dialog. They provide:

- A short title.
- A maintenance category.
- A priority.
- A full description.
- At least one issue photo.

Supported maintenance categories are **Plumbing**, **Electrical**, **Air conditioning**, **Carpentry**, and **General**. Supported priorities are **Low**, **Normal**, **High**, and **Urgent**.

The resident never chooses the organization, property, unit, or resident identity. The system derives all of that context from the authenticated resident and their active occupancy, which reduces mistakes and prevents cross-property submissions.

Image uploads provide previews, upload progress, removal, error feedback, and cleanup of unused temporary files. A successfully submitted issue begins with **Open** status and triggers a queued email to the organization owner.

### My Requests

Residents can search and paginate their personal maintenance history. Each entry shows its title, category, priority, status, unit, and creation date. The list is strictly scoped to the signed-in resident.

### Request details and resolution review

The resident can open one of their requests to see:

- Issue description and location.
- Category, priority, and current status.
- Original issue photos.
- Assigned technician, when available.
- Completion photos and technician notes.
- Actual cost, when recorded.
- Full status timeline, including the person responsible for each transition.

When a technician marks work as completed, only the resident can make the final decision:

- **Confirm Resolution** changes the request from Completed to Closed.
- **Reopen Issue** changes the request from Completed to Reopened and requires an explanation.

This resident confirmation step prevents technical completion from automatically being treated as customer acceptance.

## Technician workspace

### Technician dashboard

The technician dashboard gives a job-focused view of personal workload. It includes:

- Assigned job count.
- In-progress job count.
- Completed job count.
- Up to five current active jobs, combining Assigned and In Progress work.

Active job cards show the practical context needed to decide what to work on next, including title, priority, category, property, unit, and status.

### My Jobs

Technicians can search and paginate their complete assigned-job history. They can narrow the list to **Assigned**, **In Progress**, or **Completed** jobs. The listing includes title, property, unit, category, priority, status, and creation date.

The system enforces technician ownership on the server; changing a URL cannot reveal another technician's job.

### Job details

The job detail experience provides:

- Issue title and description.
- Category and priority.
- Property and unit.
- Resident name and contact information.
- Original issue photos with a focused image viewer.
- Current status and status history.
- Completion details and completion photos when present.

Technicians cannot assign or reassign jobs and cannot close requests on behalf of residents.

### Start and complete work

A technician can start a job only while it is in **Assigned** status. Starting work changes it to **In Progress** and records the transition in both the request timeline and organization activity history.

A technician can complete only an in-progress job. Completion requires:

- Completion notes describing the work performed.
- Actual cost, including zero when appropriate.
- At least one completion photo.

The completion form supports image previews, upload progress, removal, cleanup, and error handling. Completing a job changes its status to **Completed**, records the completion time and status history, logs organization activity, and sends the resident a queued email. The technician cannot perform the final close; that decision remains with the resident.

## Maintenance workflow

The core lifecycle is:

1. **Open** — a resident has reported an issue.
2. **Assigned** — the owner has assigned an available technician.
3. **In Progress** — the assigned technician has started work.
4. **Completed** — the technician has submitted completion evidence.
5. **Closed** — the resident has confirmed the resolution.

If the completed work has not resolved the issue, the resident can move it to **Reopened** with an explanation. The owner can then reassign or update it so work can continue.

Every meaningful transition is preserved in a status history with the previous status, new status, actor, optional notes, and timestamp. Broader business events are also recorded in the organization activity feed.

## Search, lists, and responsive behavior

Operational listings use server-side pagination and search so they remain practical as an organization grows. Search understands the relevant context for each area—for example, property and unit information on maintenance requests, occupancy information for residents, and specialties for technicians.

List pages provide responsive desktop tables and mobile-friendly card presentations. Filters and search are reflected in the URL where appropriate, allowing filtered views to survive refreshes and be shared. Loading skeletons, empty states, disabled processing states, confirmation dialogs, and clear validation feedback are part of the standard experience.

## Authentication, security, and account settings

The application uses one shared authentication system across all roles. Current account capabilities include:

- Public owner registration, which creates the initial organization.
- Email-and-password login.
- Email verification.
- Forgotten-password and password-reset flows.
- Password confirmation for sensitive settings.
- Two-factor authentication with authenticator apps and recovery codes.
- Passkey enrollment and management.
- Login and security-action rate limiting.
- Profile name and email updates.
- Password updates.
- Account deletion with confirmation.
- Role-aware redirection to the correct workspace after authentication.

Resident and technician accounts are provisioned by the owner rather than publicly registered. Sensitive account and operational actions are validated on the server, and role, organization, occupancy, and assignment boundaries are enforced independently of the interface.

## Notifications and communication

The MVP uses queued email notifications for essential operational moments:

- A resident receives their new account credentials and sign-in link.
- A technician receives their new account credentials and sign-in link.
- The organization owner is notified when a resident reports a maintenance issue.
- A technician is notified when a request is assigned to them.
- A resident is notified when a technician completes their request.

The product intentionally does not include live chat. Communication is structured around the request description, transition notes, completion notes, photos, email alerts, and the permanent history attached to each request.

## Core business information

At a high level, the product maintains the following connected records:

- **Organizations:** tenant businesses that own all portfolio data.
- **Users and memberships:** shared identities connected to organizations and roles.
- **Properties:** managed residential locations within an organization.
- **Units:** individual occupiable spaces within properties.
- **Occupancies:** the historical connection between a resident and a unit.
- **Technician profiles:** specialty, contact, and availability information.
- **Maintenance requests:** reported issues, assignment, status, cost, and completion details.
- **Media:** issue and completion images associated with maintenance requests.
- **Status logs:** the immutable timeline of request transitions.
- **Activity logs:** the organization-wide history of important business actions.

## High-level product architecture

Property Maintenance is a modern server-driven web application built with Laravel and React through Inertia. The backend owns authentication, authorization, validation, tenant isolation, business workflows, persistence, email notifications, and media handling. The React interface provides responsive dashboards, searchable lists, dialogs, uploads, timelines, and role-specific navigation without requiring a separate public API architecture.

Key platform foundations include:

- Laravel 13 for the application and business layer.
- Inertia.js 3 and React 19 for the client experience.
- Tailwind CSS 4 for the design system.
- Laravel Fortify for authentication and account security.
- Organization-aware Spatie roles and permissions.
- Spatie Media Library for maintenance images and responsive image output.
- Spatie Activitylog for the organization history.
- Queued Laravel notifications for operational email.
- Typed route generation so the frontend stays aligned with backend routes.

These implementation choices matter as product context, but external recommendations should remain framework-agnostic unless technical advice is explicitly requested.

## Design direction

### Overall theme

The accepted visual direction is a **light, warm-premium SaaS interface**. It should feel calm, trustworthy, polished, and operationally clear—not cold, overly corporate, playful, or visually noisy.

The design is intended to make a practical maintenance product feel considered and high quality while preserving fast scanning and straightforward actions.

### Color language

- Warm off-white page backgrounds create a softer canvas than pure gray or clinical white.
- Clean white cards sit above the canvas with restrained borders and soft depth.
- Deep green-tinted ink is used for primary text instead of absolute black.
- Emerald and teal form the core brand and action colors.
- Muted sage and warm neutral tones support secondary surfaces.
- Status colors are semantic but calm: amber and orange for attention, cyan or indigo for active work, green for completion, and rose for reopened or destructive states.
- Color is used to clarify hierarchy and meaning, not as decoration everywhere.

### Shape, depth, and spacing

- Cards and controls use comfortably rounded corners.
- Borders are subtle, warm, and lower contrast.
- Shadows are soft and lightly green-tinted, creating depth without looking heavy.
- Interactive cards may lift very slightly on hover.
- Layouts use generous but efficient spacing and clear grouping.
- The interface avoids dense visual chrome and oversized decorative elements inside operational screens.

### Typography and iconography

- The primary typeface is Instrument Sans with a modern system-font fallback.
- Headings are confident, compact, and slightly tightly tracked.
- Body copy is clear, calm, and readable.
- Lucide-style line icons support navigation, summaries, actions, and activity types.
- Icons clarify meaning but do not replace labels for important actions.

### Shared interface patterns

The visual system consistently uses:

- A role-aware sidebar and shared application shell.
- Page headers with a small contextual eyebrow, direct title, and short supporting description.
- Summary statistic cards.
- Searchable, paginated tables on larger screens.
- Responsive record cards on smaller screens.
- Compact status and priority badges.
- Dialog-based create, edit, assignment, confirmation, and completion tasks.
- Skeletons for progressively loaded information.
- Purposeful empty states with helpful next-step language.
- Toasts and inline messages for actionable feedback.
- Accessible focus states and reduced-motion consideration.

### Theme status

The warm-premium light theme is the active product presentation. The appearance page currently communicates that theme switching is paused while this light visual system is finalized. External design work should therefore treat the light theme as the source direction and should not invent a dark landing-page identity unless requested.

## Product voice and content style

The product voice is calm, direct, supportive, and operational. It favors plain language such as **Report an Issue**, **Start Work**, **Complete Work**, and **Confirm Resolution**. Copy should tell users what is happening and what they can do next without sounding technical.

Marketing language should emphasize clarity, accountability, faster coordination, portfolio visibility, and a connected experience for every role. It should avoid unsupported promises about automation, artificial intelligence, predictive analytics, real-time messaging, payments, or enterprise-scale capabilities.

## Current MVP boundaries

The following capabilities are intentionally not part of the implemented MVP:

- Billing, subscriptions, and pricing management.
- Rent payments, rent collection, or accounting.
- Lease documents and lease lifecycle management.
- Public registration for residents or technicians.
- A completed manager-management interface or permission-management UI.
- Organization profile/settings management beyond personal account settings.
- Property detail pages or property-specific dashboards.
- Live chat or resident-to-technician messaging.
- Scheduling calendars, appointments, or technician dispatch planning.
- GPS tracking, routes, or live technician location.
- Time tracking, payroll, technician payments, or job bidding.
- Inventory, spare parts, purchase orders, or vendor-company management.
- Community announcements, household members, surveys, or resident social features.
- Advanced analytics, forecasting, or automated maintenance intelligence.
- Native mobile applications.

These boundaries are important when producing product recommendations. Suggestions may identify them as future opportunities, but they must not be described as current functionality.

## Guidance for AI tools and future collaborators

When using this document as context:

1. Treat the organization as the tenant and the property as a resource inside it.
2. Treat the owner as the current administrative customer and buying persona.
3. Keep residents and technicians in simple, focused, role-specific experiences.
4. Preserve the resident's final approval in the maintenance lifecycle.
5. Emphasize the end-to-end workflow and shared accountability as the core differentiator.
6. Use only implemented capabilities as present-tense product claims.
7. Clearly label unimplemented suggestions as future ideas or roadmap opportunities.
8. Preserve the warm-premium light visual direction in landing pages, mockups, and UI recommendations.
9. Prefer credible, specific language over exaggerated enterprise or AI-driven claims.
10. Keep recommendations aligned with a focused property-maintenance product rather than expanding it into an all-purpose property-management system.

## One-paragraph reusable context

Property Maintenance is a warm-premium, multi-tenant SaaS for residential property owners that connects owners, residents, and technicians through one accountable maintenance workflow. Owners manage properties, units, resident occupancy, technicians, requests, assignments, status changes, and organization activity; residents report issues with photos, track their own requests, and confirm or reopen completed work; technicians manage only their assigned jobs, start work, and submit notes, cost, and completion photos. The product uses a calm light interface with off-white surfaces, deep ink text, emerald/teal branding, subtle depth, responsive role-based dashboards, and clear semantic statuses. Its current MVP is intentionally focused on maintenance coordination and excludes rent, leases, billing, chat, scheduling, GPS, inventory, payroll, advanced analytics, and other broad property-management functions.
