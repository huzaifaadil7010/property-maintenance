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
