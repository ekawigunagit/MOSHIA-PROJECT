# Moshia architecture alignment

Source: http://72.62.240.106:8787/moshia-ZquktHzrKNCnAat1/
Audit date: 2026-10-02 (Asia/Jakarta).

## Scope and sequence

Keep Laravel 12, Breeze, Vue 3, Inertia, Tailwind 3 and the existing black/red design.
The approved sequence is Core first, then Wedding MVP. This change implements the
workspace/catalog/access foundation, not the entire Core or product roadmap.

## Implemented foundation

- Core Catalog: one product registry for landing, dashboard and admin.
- Core Tenancy: workspaces and memberships, atomic owner/member creation,
  membership-checked selection, and server-side active-workspace resolution.
- Registration creates the first workspace in the same transaction as account and role.
  Existing users create a workspace from their dashboard; no silent bulk data migration.
- Core Entitlement: database-backed ProductAccess contract, active/trial date checks,
  tenant membership checks, and product.access middleware for future product routes.
- Global super-admin grants access to the admin overview only. It does not bypass
  workspace membership or create paid entitlements.
- Admin overview is read-only; products remain explicitly planned.
- Existing users, role tables, Breeze controllers, and working layouts are retained.

## Module boundaries

app/Modules/Core/
  Catalog/       # product registry and dashboard composition
  Tenancy/       # workspace models/actions/resolution
  Entitlement/   # product-access contract and database implementation

Next Core domains: Identity, Billing, Notification, Media, Audit.
Existing Identity remains in app/Models/User and Breeze controllers for compatibility.
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
Run normal migrations before opening dashboard or registering. No migration:fresh.
The owner is also a member. Account deletion currently cascades its owned workspaces;
add ownership transfer/deletion safeguards before collaboration or paid data launches.
Member invitation/management is not exposed in this phase.

There is deliberately no HTTP endpoint that grants entitlements. Later, a verified,
idempotent billing action will grant/revoke them after authoritative payment events.
Never grant access because a browser reports successful checkout.

## Remaining Core work (before Wedding launch)

1. Agree tenant/member roles, ownership transfer, plan pricing, currency, trial and quotas.
2. Enable MustVerifyEmail after mail delivery is configured and existing-account
   verification policy is agreed. Existing User currently does not implement it;
   the verified route middleware alone does not enforce verification.
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
