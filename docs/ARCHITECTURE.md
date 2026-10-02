# Moshia architecture alignment

Source: http://72.62.240.106:8787/moshia-ZquktHzrKNCnAat1/
Audit date: 2026-10-02 (Asia/Jakarta).

## Scope and sequence

Keep Laravel 12, Breeze, Vue 3, Inertia, Tailwind 3 and the existing black/red design.
The approved sequence is Core first, then Wedding MVP. This change implements the
workspace/catalog/access foundation, not the entire Core or product roadmap.

## Implemented foundation

- Core Catalog: one database registry (`core_products`) for landing, dashboard and
  admin. Migration 2026_10_02_000002 snapshots the original four products with stable
  legacy string IDs and preserves their content, phases, and order. A follow-up
  migration (2026_10_02_000003) converts these to unique slugs and adds an
  auto-increment numeric primary key; it preserves all existing catalog data. config/moshia.php now
  only defines allowed editor statuses/icons; it is no longer a product data source.
- Verified super-admins can edit title, summary, description, icon, sort order, and
  visibility through `/admin/products`, guarded by ProductPolicy. Product IDs, slugs, and
  roadmap phases are immutable through HTTP. No create/delete routes are exposed;
  adding business modules and retiring products require separate lifecycle work.
- `planned` displays as Segera hadir; `hidden` removes cards from landing and user
  dashboard while keeping them visible in admin. There is no live/active product
  option until products ship. Empty catalogs have an explicit frontend message.
  Hiding a catalog entry does not revoke existing entitlements. ProductCatalog::find
  deliberately resolves hidden slugs too. Future product routes must still enforce
  readiness and authorization independently of this catalog visibility setting.
- Core Tenancy: workspaces and memberships, atomic owner/member creation,
  membership-checked selection, and server-side active-workspace resolution.
- Registration creates the first workspace in the same transaction as account and role.
  Existing users create a workspace from their dashboard; no silent bulk data migration.
- Email verification is required through MustVerifyEmail. Registration sends a
  signed expiring verification link and redirects to the verification notice.
  Dashboard, admin and workspace endpoints require verified email; product.access
  also rejects unverified users. Profile remains accessible for email correction.
  Mail transport failures leave accounts pending with a recoverable resend flow.
- Existing verified accounts remain unchanged. Gmail SMTP is configured and the
  user confirmed real registration email delivery and verification worked.
  Verification email uses branded HTML and plain-text templates; inbox appearance
  of the new template has not yet been verified. Domain SMTP uses the same configuration.
  See docs/EMAIL.md for setup, limitations, and migration instructions.
- Core Entitlement: database-backed ProductAccess contract, active/trial date checks,
  tenant membership checks, and product.access middleware for future product routes.
- Global super-admin grants access to the admin overview, user/role and catalog management. It does not bypass
  workspace membership or create paid entitlements.
- Admin overview links to user and catalog editors; no business product is launched.
- Login defaults to `/admin` for super-admin and `/dashboard` for customers.
  Authenticated visits to login/register and the landing dashboard link use the
  same role-based home. Admins can still explicitly open their own workspace.
- Intended login destinations are consumed once and restricted to local dashboard,
  profile, and (for super-admin only) admin pages. Unknown or external destinations
  fall back to the role home. Add future product destinations only with their
  authorization checks; route middleware remains the final access boundary.
- Admin overview includes global user/workspace/catalog counts, available only
  behind the admin role middleware. These counts grant no tenant/product access.
- Core Identity now provides `/admin/users`: paginated name/email search, role
  filtering, and editing account name/email plus assignments of existing web roles.
  UserPolicy and admin middleware protect reads and writes. Passwords and tokens
  are omitted from account payloads; changed email clears email verification.
- Admins cannot change their own roles. Updates lock the super-admin role row and
  recheck the actor from the database inside the transaction before applying roles
  and identity together. This serializes role changes through this action; SQLite
  feature tests do not validate MySQL row-lock concurrency.
- Role assignment uses existing roles (initially user/super-admin). Creating or
  deleting role definitions, editing permissions, deleting/suspending other accounts,
  and an audit log are not part of this phase. The existing profile self-deletion
  flow still needs the ownership/deletion policy described below before launch.
- Intended login URLs also support the admin users list and existing account edit
  pages after role/policy checks. Mobile and desktop navigation share these links.
- Existing users, role tables, Breeze controllers, and working layouts are retained.

## Module boundaries

app/Modules/Core/
  Catalog/       # product registry and dashboard composition
  Tenancy/       # workspace models/actions/resolution
  Entitlement/   # product-access contract and database implementation
  Identity/      # admin user management and role assignment policies

Next Core domains: Billing, Notification, Media, Audit.
Identity keeps app/Models/User and Breeze controllers for compatibility; admin
management lives in Core/Identity with Actions, Policies, Http, and Routes.
Do not rename users/roles tables solely for naming consistency.

Future product domains:
app/Modules/Products/{Wedding,Jastip,PhotoBooth,Restaurant}

Future integration adapters:
app/Modules/Integrations/{Payments,WhatsApp,AI,DNS,Printing}

Create these implementations when their vertical slice is developed, not empty
folders or mock integrations that appear functional.

Every future product-owned record must carry tenant_id and every query/policy must
be scoped to the resolved workspace. A workspace selector alone is not tenant
isolation. Products consume Core contracts and must not directly query each other's
tables. ProductAccess checks entitlement, not product readiness or usage quotas.

## Migration and behavior

New migration creates core_tenants, core_tenant_user, core_entitlements.
Catalog migration 2026_10_02_000002 adds core_products and seeds initial data once.
It was applied locally without changing existing account/workspace/entitlement row
counts. Re-running normal migrate skips it and does not overwrite admin catalog edits.
Run normal migrations before opening dashboard or registering. No migration:fresh.
The owner is also a member. Account deletion currently cascades its owned workspaces;
add ownership transfer/deletion safeguards before collaboration or paid data launches.
Member invitation/management is not exposed in this phase.

There is deliberately no HTTP endpoint that grants entitlements. Later, a verified,
idempotent billing action will grant/revoke them after authoritative payment events.
Never grant access because a browser reports successful checkout.

## Remaining Core work (before Wedding launch)

1. Agree tenant/member roles, ownership transfer, plan pricing, currency, trial and quotas.
2. Move from the working Gmail SMTP setup to domain SMTP when ready.
   MustVerifyEmail is enabled; existing unverified accounts must also verify,
   without mass updates to email_verified_at. Verify branded email across clients.
3. Plans/subscriptions/invoices and a selected payment adapter; signed/authenticated
   webhook verification, idempotent processing and lifecycle/reconciliation tests.
4. Quota accounting and atomic consumption, notification storage/queue, audit records.
5. Media contracts, private object storage, upload validation, and signed URLs.

## Product stages

- Wedding first: template -> edit content -> preview -> publish, scoped by tenant and entitlement.
- Jastip: trip -> order -> status -> WhatsApp.
- Photo Booth: capture -> frame -> render -> local print agent, AI later.
- Restaurant: menu/table/order, then kitchen, POS/payment/receipt, then queue/WhatsApp.

## Deployment

Local database queue/filesystem are not automatically switched to Redis/S3.
Production needs provisioned MySQL, Redis workers/scheduler, object storage, HTTPS/DNS,
mail and payment/WhatsApp providers, backup/restore and monitoring.
Do not treat missing infrastructure as installed or run commands against the blueprint server.
Every npm run build/dev still requires user approval via the tool approval button.

## Product identity separation

core_products.id is numeric and auto-incrementing; slug is a unique string.
Editor URL binding, landing anchors, and ProductAccess continue using slugs.
Payloads expose both id and slug. Ordering uses sort_order then slug.
core_entitlements.product_slug remains unchanged; a numeric product_id relation
is separate future work. MySQL migration preserved catalog content and entitlement
data; SQLite tests cover forward/reverse migration, numeric IDs, and slug uniqueness.
