<laravel-boost-guidelines>
=== .ai/inertia-react rules ===

## Inertia + React Conventions (Frontend)

Base conventions for Laravel + Inertia + React projects. Follow these unless the
file you are editing clearly already does something else — in that case match the
surrounding file and mention the divergence.

### Structure

```
resources/js/
  pages/         one file per Inertia page, mirrors the Inertia::render() path
  components/    shared + domain components
  layouts/       app-layout, auth-layout, ...
  hooks/         reusable hooks
  types/         shared TS types
  lib/ utils/    helpers
```

- Page files are **kebab-case** and mirror the server path exactly:
  `Inertia::render('submissions/index')` → `resources/js/pages/submissions/index.tsx`.
- Pages are default exports. Props are destructured in the signature:
  `export default function Index({ submissions }) { ... }`.
- Domain components live in `components/<domain>/`, shared UI in `components/ui/`.
- Prefer `.tsx` and type props for anything new.

### URLs — always Wayfinder, never strings

Every URL comes from a generated Wayfinder function. No string literals, no
hand-built query strings.

```tsx
import SubmissionsController from '@/actions/App/Http/Controllers/SubmissionsController';

SubmissionsController.index().url
SubmissionsController.store().url
SubmissionsController.destroy(id).url
ToggleSubmissionOfferController(submission.id).url
```

The alias differs per project (`@/actions/...` or `@/wayfinder/...`) — check a
sibling file. Single-action controllers are imported as the function itself.

Breadcrumbs use it too:

```tsx
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Submissions', href: SubmissionsController.index().url },
];
```

### Create & edit — ask before building

When the user asks for a **create button on a listing page**, or an **edit action on
a row**, do not pick the shape yourself. **Stop and ask** — the answer changes the
routes, the controller methods, and the file layout, and is expensive to undo.

Ask, for create:

> Should "Create" open a **dialog on the listing page**, or navigate to a
> **separate create page**?

Ask, for edit:

> Should "Edit" open a **dialog**, or navigate to a **separate edit page**?

Then, if both exist and their fields overlap:

> Create and edit look like the same form — should they **share one component**, or
> stay separate?

Ask these together in one go when the request covers both. Don't ask again for the
same resource once answered.

#### Dialog

- File: `components/<resource>/create-<resource>-dialogue.tsx` — or
  `create-or-update-<resource>.tsx` when it handles both.
- No `create` / `edit` routes and no `create()` / `edit()` controller methods are
  needed — only `store` and `update`.
- The listing page owns the open state and the record being edited:

```tsx
const [openModal, setOpenModal] = useState(false);
const [current, setCurrent] = useState<Department | undefined>(undefined);
```

- The dialog receives the record as an **optional** prop; `undefined` means create.
- Close in `onSuccess` and `reset()` — never before the request resolves.
- Options the listing page needs for the form's selects (dropdown sources) come from
  the index controller as deferred props, so the dialog doesn't fetch on open.

#### Separate page

- Files: `pages/<resource>/create.tsx` and `pages/<resource>/edit.tsx`.
- Needs the full explicit route set — `<resource>.create` and `<resource>.edit`
  (see the CRUD mapping in the Laravel guidelines) — and matching controller methods
  returning `Inertia::render`.
- The row action is a `<Link>` to the Wayfinder URL, not a click handler.
- Redirect back to the index after a successful save.

#### Shared form

When create and edit take the same fields, extract the fields into one component and
let the caller own submission:

- `components/<resource>/<resource>-form.tsx` holds the inputs and receives the
  `useForm` instance (or `data` / `setData` / `errors`) as props.
- The parent — dialog or page — owns `useForm`, decides `post` vs `put`, and picks
  the URL from the presence of the record:

```tsx
const isEdit = Boolean(department);
const method = isEdit ? put : post;
const url = (isEdit ? update : store).url(isEdit ? { department: department.id } : {});
```

- Don't duplicate field markup across `create.tsx` and `edit.tsx`.

### Mutations — always `useForm`

Every create/update/delete/toggle goes through `useForm`. Do not use `router.post`
for form submissions and do not hand-roll fetch calls.

```tsx
const { data, setData, post, processing, errors, reset } = useForm({
    title: '',
    price: '',
});

const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post(SuggestionsController.store().url);
};
```

- `processing` drives disabled buttons and spinners — never a separate loading state.
- `errors.<field>` renders through the shared `InputError` component.
- Close modals and `reset()` inside `onSuccess`, never optimistically before the request:

```tsx
method(url, {
    onSuccess: () => {
        reset();
        setOpenModal(false);
    },
    onError: (errors) => {
        if (errors.cannot_submit) {
            toast.error(errors.cannot_submit);
        }
    },
});
```

- Use `form.transform()` to shape the payload just before submit rather than
  storing derived values in state:

```tsx
form.transform((prev) => ({ ...prev, is_offerable: !selected.is_offerable }));
form.post(ToggleSubmissionOfferController(selected.id).url);
```

- Name multiple forms on one page by purpose: `const rejectForm = useForm({ note: '' })`.
- A combined create/edit form takes an optional record and picks method + URL from
  it — see "Shared form" above.

- Deletes use `useForm({}).delete` with `only` scoping, so the list refreshes without
  a full page reload:

```tsx
destroy(DepartmentsController.destroy(id).url, {
    only: ['departments'],
    onSuccess: () => setOpenDeleteModal(false),
});
```

### Filtering, searching, pagination — `router.reload` + a `useEffect` watcher

This is the standard for every list page. Never navigate with `router.get` /
`router.visit` to apply a filter, and never call the reload directly from an
`onChange` handler.

**The pattern:**

1. Hold **all** filter values in one state object.
2. A `useEffect` watches that object — it is the *only* place the request is fired.
3. Handlers only call `setFilters`. They never call the fetch function.
4. Debounce text input inside the effect (or via a debounced setter).
5. Reload **partially** with `only: [...]` naming the props the server should recompute.

```tsx
const [filters, setFilters] = useState({ search: '', status: '' });

useEffect(() => {
    const timeout = setTimeout(() => {
        router.reload({
            only: ['projects'],
            data: {
                search: filters.search,
                status: filters.status,
                page: 1,
            },
        });
    }, 300);

    return () => clearTimeout(timeout);
}, [filters]);

const handleSearchChange = (e) => {
    setFilters((prev) => ({ ...prev, search: e.target.value }));
};

const handleStatusChange = (status) => {
    setFilters((prev) => ({ ...prev, status: status === 'all' ? '' : status }));
};
```

Why the watcher: one code path fires the request, so every filter composes
automatically and no combination gets missed when a new filter is added.

**Details:**

- Reset to `page: 1` whenever a filter changes; keep the page only for pagination.
- Guard against the redundant first fire by comparing to the previous value when the
  effect would otherwise reload on mount:

```tsx
const prevFilters = useRef(filters);

useEffect(() => {
    if (JSON.stringify(prevFilters.current) === JSON.stringify(filters)) return;
    prevFilters.current = filters;

    filterSurveys();
}, [filters]);
```

- Seed state from the URL so a shared/refreshed link keeps its filters:

```tsx
const params = new URLSearchParams(window.location.search);
const [search, setSearch] = useState(params.get('search') ?? '');
```

- Debounce free-text at 300–500ms. Either `setTimeout` + cleanup inside the effect,
  or a `useMemo`'d `debounce()` whose only job is to update state — cancel it on unmount.
- Show a loading state via `onFinish`, not a manual flag around the call:

```tsx
router.reload({
    only: ['surveys'],
    data: { ...filters },
    onFinish: () => setFetching(false),
});
```

- Pagination uses the same mechanism:

```tsx
const handlePageChange = (page: number) => {
    router.reload({ only: ['departments'], data: { page } });
};
```

- `only` must name every deferred prop that depends on the filters, and nothing else.

**Exception — shared DataTable pages.** Where a project has a generic `DataTable`
that emits params via `onParamsChange`, those pages drive an imperative
`reloadData()` off a `paramsRef` instead. That is intentional. Follow the
surrounding pattern when editing those pages; don't convert them to the watcher.
Use the watcher for new hand-built filter UIs.

### Deferred props

Server-deferred props arrive after first paint. Always render a skeleton or empty
state for them — never a bare `undefined` access.

```tsx
{!cards ? <Skeleton className="h-32 w-full" /> : <CardList cards={cards} />}
```

### Enums

Import the shared/generated enum constants rather than duplicating string unions,
and compare against them instead of literals. The location varies per project
(`@/types`, `@/enums`, or the Wayfinder output) — check a sibling file.

```tsx
import { SubmissionStatus } from '@/types';

disabled={row.original.status?.value !== SubmissionStatus.APPROVED}
```

### General

- Reuse the shared UI primitives (`components/ui/*`) before writing a new component;
  check for an existing one first.
- Confirmation dialogs go through the shared `ConfirmationModal`, wired to
  `form.processing` for its busy state.
- Toasts (`sonner`) are for client-side and error feedback; server success messages
  come back as Inertia flash props.
- `<Head title="..." />` on every page.
- Memoize derived values (`pageCount`, filtered option lists) with `useMemo`.
- Never build a URL by string concatenation — see Wayfinder above.

=== .ai/instructions rules ===

## Tests

Do not create, modify, or add test cases unless the prompt explicitly asks for tests.

## Static Analysis and PHPDoc

Do not install, configure, or run PHPStan or Larastan unless explicitly requested.

Do not add PHPDoc blocks, array-shape annotations, generic types, or other comments solely for static analysis. Prefer native PHP types and clear code.

Do not remove or modify existing PHPDoc unless required by the requested change.

=== .ai/laravel rules ===

## Laravel Conventions (Backend)

Base conventions for Laravel + Inertia + React projects. Follow these unless the
file you are editing clearly already does something else — in that case match the
surrounding file and mention the divergence.

### Layering

Every write request flows through the same four layers. Do not skip one.

```
Route  →  Controller  →  FormRequest  →  Data (DTO)  →  Action  →  Model
```

- **Controller** — HTTP only: validate, build the DTO, call the Action, return a response.
- **FormRequest** — all validation and authorization, including business-rule checks
  in `after()`. Never validate inline in a controller.
- **Data** — a `Spatie\LaravelData\Data` DTO. The only shape passed into an Action.
- **Action** — one business operation, one class.

A controller method should be readable in a few lines:

```php
public function store(SubmissionsRequest $request): Response
{
    $data = SubmissionData::from($request->validated());

    CreateSubmission::handle($data, Auth::user());

    return Inertia::flash('success', 'Submission created successfully')->back();
}
```

### Routing

**Never use `Route::resource()`.** Declare every route explicitly, one line per
action — but keep the **exact URLs and route names that `Route::resource()` would
have generated**. Explicit means visible, not different: `php artisan route:list`
should look identical to the resource equivalent.

The canonical CRUD mapping every explicit route must match:

| Verb | URI | Action | Route name |
| --- | --- | --- | --- |
| GET | `submissions` | `index` | `submissions.index` |
| GET | `submissions/create` | `create` | `submissions.create` |
| POST | `submissions` | `store` | `submissions.store` |
| GET | `submissions/{submission}` | `show` | `submissions.show` |
| GET | `submissions/{submission}/edit` | `edit` | `submissions.edit` |
| PUT/PATCH | `submissions/{submission}` | `update` | `submissions.update` |
| DELETE | `submissions/{submission}` | `destroy` | `submissions.destroy` |

Write it grouped with `prefix()` + `as()` so the segment and name prefix appear once:

```php
Route::prefix('submissions')->as('submissions.')->group(function () {
    Route::get('/', [SubmissionsController::class, 'index'])->name('index');
    Route::get('/create', [SubmissionsController::class, 'create'])->name('create');
    Route::post('/', [SubmissionsController::class, 'store'])->name('store');
    Route::get('/{submission}', [SubmissionsController::class, 'show'])->name('show');
    Route::get('/{submission}/edit', [SubmissionsController::class, 'edit'])->name('edit');
    Route::put('/{submission}', [SubmissionsController::class, 'update'])->name('update');
    Route::delete('/{submission}', [SubmissionsController::class, 'destroy'])->name('destroy');
});
```

Rules that follow from this:

- **Declare only the actions that exist.** The benefit of explicit routing is that
  the file lists exactly what is reachable — no `->only()` / `->except()` needed.
- **`create` and `edit` are not automatic.** They exist only when the UI navigates to
  a dedicated page. If create/edit happen in a dialog on the listing page, declare
  `store` and `update` only — there is no page to render, so no route and no
  controller method. Confirm which shape the UI uses before adding them (see
  "Create & edit — ask before building" in the Inertia + React guidelines).
- **Order matters.** Static segments before wildcards: `/create` must be declared
  before `/{submission}`, or `create` will bind as a model key.
- **Do not invent URL shapes.** No `/{submission}/show`, `/{submission}/update`, or
  `/{submission}/delete` — the verb already distinguishes them. Match the table.
- **Singular route parameter, matching the model** for route model binding:
  `{submission}`, not `{id}` or `{submissions}`.
- Bind by a non-key column inline where needed: `/{card:slug}`.
- Plural, kebab-case URI segments: `department-teams`, `tasting-requests`.

- Every **non-CRUD verb** gets its own explicit route and a single-action
  `__invoke` controller, named `<resource>.<verb>` under the same group:

```php
Route::prefix('offers')->as('offers.')->group(function () {
    // ... CRUD routes ...
    Route::post('/{offer}/accept', AcceptOfferController::class)->name('accept');
    Route::post('/{offer}/reject', RejectOfferController::class)->name('reject');
});
```

  Sub-resource listings keep the same convention: `GET offers/requested` →
  `offers.requested`, declared **before** `/{offer}` so it isn't swallowed by the
  wildcard.

- Constrain route params with enum values, never magic strings:

```php
Route::get('/{type?}', [SurveysController::class, 'index'])
    ->whereIn('type', [SurveyStatusEnum::SCHEDULED->value, SurveyStatusEnum::DRAFT->value]);
```

- Every route is named. Middleware is applied via groups, not per-route repetition.
- Split large route files by area (`web.php`, `admin.php`, `settings.php`, `auth.php`)
  and `require` them from `web.php`.

### Controllers

- CRUD controllers are **plural, noun-based**, and keep the standard resource method
  names (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`) even though
  the routes are declared explicitly: `SubmissionsController`, `OffersController`.
  Include only the methods that have a route — dialog-based create/edit means no
  `create()` / `edit()` method at all.
- Single-action controllers are **verb-based** and end in `Controller`, with a single
  `__invoke()`: `AcceptOfferController`, `ToggleSubmissionOfferController`,
  `ExportEventsController`, `ApproveClientsController`.
- Controllers hold **no business logic** — no multi-step writes, no computation, no
  query building beyond `load()` / `loadCount()` on an already-bound model.
- Type-hint the return: `Response` (Inertia), `RedirectResponse`, `JsonResponse`.
- Authorize model access explicitly where a FormRequest doesn't already:

```php
Gate::authorize('view', $survey);
```

### Form requests

One FormRequest per action, in `app/Http/Requests/`, namespaced by domain once a
domain has several. All validation lives here — never inline in a controller.

- `rules()` covers **field-shape** validation: presence, type, format, uniqueness.
  Use `Rule::enum()`, `Rule::exists()`, `Rule::unique()` over string rules whenever
  the rule references a class or needs a constraint.

```php
public function rules(): array
{
    return [
        'name' => ['required', 'string'],
        'status' => ['required', Rule::enum(ProjectStatus::class)],
        'price' => ['required', 'decimal:0,2', 'min:0.01'],
    ];
}
```

#### Extra validation → `after()`

Anything that depends on **current model state, business rules, or cross-field
logic** goes in `after()`, not in `rules()` and not in the controller. `after()`
returns an array of callables that receive the `Validator`.

```php
public function after(): array
{
    return [
        function (Validator $validator) {
            if ($this->date('starts_at')->gt($this->date('ends_at'))) {
                $validator->errors()->add('ends_at', __('End date must be after the start date.'));
            }
        },
    ];
}
```

- Add errors with `$validator->errors()->add($key, $message)`.
- Use the real field name as the key when the error belongs to an input. For
  whole-request rejections use a consistent non-field key such as `cannot_submit`
  so the frontend can surface it as a toast rather than an inline field error.
- Wrap user-facing messages in `__()` on projects that are localized.
- Keep each rule in its own closure — one closure per business rule, not one
  closure with a chain of `if`s.
- Expensive lookups needed by the closure are resolved **before** the `return`, so
  they run once rather than per-callable.

#### Route params → `#[RouteParameter]`, never `$this->route()`

Inject the bound model into `after()` with the container attribute. Do **not** call
`$this->route('submission')` — the attribute is typed, resolves the already-bound
model, and makes the dependency visible in the signature.

```php
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Validation\Validator;

public function after(#[RouteParameter('submission')] Submission $submission): array
{
    return [
        function (Validator $validator) use ($submission) {
            if ($submission->status !== SubmissionStatus::APPROVED) {
                $validator->errors()->add('cannot_submit', __('Only approved submissions can be toggled.'));
            }
        },
    ];
}
```

- The attribute argument is the **route parameter name**, matching the URI segment
  (`{submission}` → `#[RouteParameter('submission')]`).
- Type-hint with the model class; the resolved instance is the same one the
  controller receives.
- When one request serves both store and update, make it nullable with a default so
  the create path still resolves:

```php
public function after(#[RouteParameter('survey')] ?Survey $survey = null): array
```

- Compare enum-cast attributes against **enum cases**, not `->value` strings:
  `$order->status !== OrderStatus::PENDING`.

### Actions (business logic)

One class, one operation, in `app/Actions/` — namespaced into a subfolder per domain
once a domain has several (`app/Actions/Survey/CreateSurvey.php`).

- The entry point is a **static `handle()`** method. Always.
- Signature order: DTO first, then models/context.
- Explicit return type on every `handle()`.

```php
class CreateSubmission
{
    /**
     * @throws Throwable
     */
    public static function handle(SubmissionData $data, User $user): Submission
    {
        return DB::transaction(function () use ($data, $user) {
            return $user->submissions()->create($data->toArray());
        });
    }
}
```

Naming — the verb tells you what it returns:

| Prefix | Purpose | Returns |
| --- | --- | --- |
| `Get*` | read / list | Builder, Collection, or `LengthAwarePaginator` |
| `Create*` / `Update*` / `Delete*` | persistence | the affected model |
| `Approve*` / `Reject*` / `Toggle*` / `Cancel*` | state transitions | the affected model |
| `Compute*` / `Process*` | derived data | array or DTO |

Rules:

- `Get*` actions return the **query builder** when the caller still needs to
  paginate, filter, or `clone()` it; return the paginator when they don't.
- Any operation touching more than one table wraps in `DB::transaction()`.
- Side effects (notifications, media, events) belong in the Action, not the controller.
- Actions may call other Actions. They must never touch `$request`.
- **Never call `Auth::user()` / `Auth::id()` inside an Action.** Pass the user in as
  an explicit parameter: `handle(SubmissionData $data, User $user)`. An Action that
  reads the session can't be reused from a job, command, or another user's context,
  and hides who it acts on. The controller resolves the user; the Action receives it.

### Data objects (Spatie Laravel Data)

- Every Action input is a `Data` DTO in `app/Data/`, named `<Thing>Data`.
- Use promoted constructor properties, required first, nullable-with-default after.
- **Type properties with the enum itself**, not a string.

```php
class SubmissionData extends Data
{
    public function __construct(
        public SubmissionType $type,
        public int $card_id,
        public GradingCompanyEnum $grading_company,
        public bool $is_offerable,
        public array $image_names,
        public ?int $card_variant_id = null,
        public ?float $price = null,
    ) {}
}
```

- Build from validated input only: `SubmissionData::from($request->validated())`.
- When the payload needs massaging, do it at the call site or in a named
  `fromRequest()` factory — not by reaching into the request from the DTO.

```php
$surveyData = SurveyData::from([
    ...$validated,
    'slug' => Str::slug($validated['name']),
    'starts_at' => Carbon::make($validated['starts_at']),
]);
```

- Use Spatie validation attributes for constraints the FormRequest can't express:

```php
#[Enum(SurveyStatusEnum::class)]
public ?array $statuses = null,
```

- Share a `CommonFilterData` DTO for list endpoints (page, perPage, filters, sort)
  with sane defaults behind getters (`getPerPage()`, `getPage()`).

### Enums

- Always **backed** enums, `string` unless the domain is genuinely numeric.
- Live in `app/Enums/`. Suffix with `Enum` or not — but be consistent within a project.
- `UPPER_SNAKE_CASE` cases, `kebab-case` or `snake_case` values.

```php
enum SubmissionStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
```

- Never compare against raw strings in application code — compare enum instances
  or use `->value` at the boundary.
- Put enum-derived behaviour (labels, colors, allowed transitions) on the enum
  as methods rather than in `match` blocks scattered across the app.

### Migrations

- **Never use `->enum()`.** Use `string` with an enum-backed default:

```php
$table->string('status')->default(SubmissionStatus::PENDING->value);
```

  `->enum()` requires a migration to add a value and breaks on several drivers.
  Always write `->value` explicitly — do not rely on implicit enum casting.

- Cast the column back to the enum on the model, so the string only exists in the DB.
- When modifying a column, restate **every** previously-defined attribute — anything
  omitted is dropped.
- Index the columns you filter and sort on, composite where the query is composite
  (general guidance, not a pattern extracted from existing code).

### Models

- Casts go in a **`casts()` method**, never the `$casts` property. Map every enum column:

```php
protected function casts(): array
{
    return [
        'type' => SubmissionType::class,
        'status' => SubmissionStatus::class,
        'is_offerable' => 'boolean',
        'approved_at' => 'datetime',
    ];
}
```

- Type-hint every relationship (`BelongsTo`, `HasMany`, `HasManyThrough`).
- Models hold relationships, casts, scopes, and accessors — not business logic.
- Reusable query constraints become scopes (`->completed()`, `->active()`) rather
  than repeated `where()` chains in Actions.

### Multi-tenancy (tenant-scoped models)

When an app is multi-tenant, **every model owned by a tenant gets one trait** and
nothing else. The goal: ordinary queries (`Asset::query()->get()`) already return
only the current tenant's rows, and new records get the tenant FK automatically.
No Action, controller, or scope should ever hand-write `where('tenant_id', ...)`.

The tenant may be called Tenant, Team, Organization, or Vendor — the pattern is
identical; name the trait, scope, and column to match (`BelongsToTenant` +
`TenantScope` + `tenant_id`).

#### The trait

Lives in `app/Traits/` (or `app/Concerns/`) and does exactly three things:
registers the global scope, auto-fills the FK on create, and defines the relation.

```php
namespace App\Traits;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            if ($model->getAttribute('tenant_id') !== null) {
                return;
            }

            if ($tenantId = TenantResolver::getId()) {
                $model->setAttribute('tenant_id', $tenantId);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
```

Rules:

- **Name the boot method `boot<TraitName>()`.** Laravel calls it automatically per
  trait. **Never define `booted()` or `boot()` inside a trait** — those collide with
  the model's own `booted()` and with every other trait that does the same, and only
  one survives. This is the single most common way tenant scoping silently stops
  working.
- **Resolve the tenant inside the `creating` closure**, never in the boot method
  body. Boot runs once per model class per request, often before auth is resolved —
  an `if (Auth::user())` wrapped around `static::creating(...)` will skip
  registering the hook entirely and records will be written with a null tenant.
- **Don't overwrite an explicitly set FK.** Bail out if the attribute is already
  populated, so seeders, imports, and cross-tenant admin writes still work.
- The trait declares the `belongsTo` relation so models don't each redefine it.

#### The scope

A dedicated `Scope` class in `app/Models/Scopes/`, never an inline closure — a class
can be referenced by name in `withoutGlobalScope()`.

```php
namespace App\Models\Scopes;

use App\Services\TenantResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! $tenantId = TenantResolver::getId()) {
            return;
        }

        $builder->where($model->getTable().'.tenant_id', $tenantId);
    }
}
```

Rules:

- **Always qualify the column with the table name** (`$model->getTable().'.tenant_id'`).
  Unqualified, the constraint breaks with "ambiguous column" the moment the query
  joins another tenant-scoped table.
- **No tenant resolved → apply nothing** and return early. Console commands, queued
  jobs, and webhooks run without a tenant context; a scope that filters on `null`
  returns empty sets and produces silent data loss.
- Cross-tenant roles (super-admin, platform staff) are handled **inside the scope**
  so callers stay ignorant of it — but keep the condition to a single, explicit check.

#### Resolving the current tenant

Centralize resolution in one class. Never read `Auth::user()->current_tenant_id`
directly from a scope, Action, or controller.

```php
TenantResolver::getId();     // FK value for scoping
TenantResolver::getModel();  // the Tenant model when you need its attributes
TenantResolver::set($tenant); // on switch
```

- Memoize per request — the scope runs on every query and must not re-hit the DB.
- Resolution order is the resolver's business (route param → session → user's
  `current_tenant_id` → first tenant), not the caller's.

#### Querying tenant data

```php
// Correct — the scope is already applied
Asset::query()->where('status', AssetStatus::ACTIVE)->get();
$tenant->assets()->count();
```

```php
// Wrong — redundant, and drifts the moment the scope changes
Asset::query()->where('tenant_id', Auth::user()->current_tenant_id)->get();
```

- Escaping the scope is **deliberate and narrow** — admin dashboards, cross-tenant
  reports, and tenant provisioning only:

```php
Project::query()->withoutGlobalScope(TeamScope::class)->get();
```

  Name the scope class explicitly; never `withoutGlobalScopes()` with no argument,
  which also strips soft deletes and every other scope.

- **Queued jobs and console commands carry no tenant context.** Pass the tenant id
  into the job payload and set it explicitly, or query with the scope removed and an
  explicit `where`. Never assume the resolver works off the queue.
- Route model binding resolves **through** the global scope, so a URL carrying
  another tenant's id 404s automatically. Don't re-check ownership in the controller.
- Unique constraints must be composite with the tenant column
  (`$table->unique(['tenant_id', 'slug'])`), and every `Rule::unique()` needs a
  matching `->where('tenant_id', ...)` — validation does **not** run through the
  global scope.
- Index the tenant FK on every scoped table; it appears in the WHERE clause of
  literally every query against it.

### Inertia responses

- Render with the page path relative to `resources/js/pages`, no extension:

```php
return Inertia::render('submissions/index', [
    'submissions' => SubmissionResource::collection(GetSubmissions::handle($data, Auth::user())),
]);
```

- The **primary** prop (the list/record the page is about) is always eager.
- **Defer secondary props** — dropdown sources, charts, expensive aggregates — so
  the page paints immediately:

```php
'cards' => Inertia::defer(fn () => CardResource::collection(GetCards::handle()->get()))->once(),
'organizations' => Inertia::defer(fn () => GetOrganizations::handle()->get(), 'organizations'),
```

  Pass a group name to load related deferred props in one round trip. Use `->once()`
  for data that won't change during the page's lifetime (Inertia v2+).

- Redirect back with a flash message after every mutation:

```php
return Inertia::flash('success', 'Submission created successfully')->back();

return Inertia::flash([
    'success' => __('Successfully created survey.'),
    'survey'  => $survey->load(['organization'])->toArray(),
])->back();
```

  Older projects use `back()->with('success', ...)` — match the file you're in.

- When create/edit are dialogs on the listing page, the form's select options are
  deferred props on the **index** response — the dialog must not fetch on open.
- Wrap list and detail payloads in API Resources so the frontend contract is explicit.
- Route model binding everywhere; never look a model up by id inside a controller.

### Facades over global helpers

**Always use the facade when one exists.** Reach for a global helper function only
when there is genuinely no facade equivalent. Facades are importable, greppable,
IDE-navigable, and mockable in tests; the helpers are none of those.

```php
// Correct
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

Auth::user();
Auth::id();
DB::transaction(fn () => ...);
Log::error('Failed to update submission: '.$e->getMessage());
Cache::remember($key, $ttl, fn () => ...);
Storage::disk('public')->url($path);
Session::get('key');
Config::get('services.stripe.key');
Request::input('search');
```

```php
// Wrong — a facade exists for every one of these
auth()->user();
logger()->error(...);
cache()->remember(...);
session()->get('key');
config('services.stripe.key');
request()->input('search');
```

**Helpers with no facade equivalent — keep using these:**

`__()` / `trans()`, `route()`, `url()`, `abort()`, `abort_if()`, `now()`,
`today()`, `collect()`, `str()`, `data_get()`, `dispatch()`, `back()`,
`to_route()`, `redirect()`, `response()`, `old()`, `optional()`, `throw_if()`,
`base_path()` / `storage_path()` / `public_path()`.

Notes:

- Inside a FormRequest or controller, prefer `$this->...` / the injected `Request`
  over both the `Request` facade and `request()`.
- `back()` and `redirect()` are the idiomatic response helpers even though a
  `Redirect` facade exists — the Inertia response convention above uses them.
- Import facades with a `use` statement at the top; never call them fully-qualified
  inline (`\Illuminate\Support\Facades\Auth::user()`).

### General

- Use `php artisan make:*` to scaffold; `make:class` for plain classes.
- Constructor property promotion, explicit return types, typed parameters throughout.
- Curly braces on all control structures, even one-liners.
- PHPDoc blocks over inline comments; document `@throws` on Actions that can throw.
- Run `vendor/bin/pint --dirty` before finishing any PHP change.

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/pint (PINT) - v1
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/react (INERTIA_REACT) - v3
- react (REACT) - v19
- tailwindcss (TAILWINDCSS) - v4
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== wayfinder/v rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

</laravel-boost-guidelines>
