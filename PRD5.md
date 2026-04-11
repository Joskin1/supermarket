Codex Prompt — Stage 5: Safety, Corrections, Auditability, and Operations Hardening

You are my senior Laravel + Filament implementation agent.

Stages 1–4 already exist or are in progress:

Laravel installed
Docker configured
MySQL configured
Filament installed
authentication working
sudo and admin roles working
inventory core implemented
stock entries implemented
daily sales Excel export/import implemented
sales import batches implemented
sales records implemented
stock deduction implemented
reporting layer implemented or in progress

Now implement Stage 5.

This phase is for:

stock adjustments
activity / audit log
failed import review and retry workflow
duplicate import protection hardening
manual stock check page
database backup and recovery plan support
basic application settings/config page
operational safety and integrity improvements

Do not implement yet:

supplier module
returns as a full separate business module unless needed minimally
barcode scanning
receipt printing
multi-branch support
customer storefront
advanced accounting
forecasting / AI features
mobile app

This stage must focus on making the MVP safe, correctable, auditable, and operationally trustworthy.

MAIN BUSINESS CONTEXT

The supermarket can already:

create categories and products
add stock through stock entries
export/import daily sales sheets
deduct stock from sales
view reports

Now we need the real-world correction and safety layer.

In real use, the owner will face situations like:

stock was entered wrongly
an item got damaged
an item expired
physical stock does not match system stock
a sales file upload had failed rows
someone uploaded the same file twice
prices or settings need small adjustments
the owner needs to know exactly who changed what
the business needs backup and recovery readiness

This stage should make the system feel safe enough for real business use.

STAGE 5 GOAL

Build the operational hardening layer so the supermarket owner can:

correct stock safely
review why imports failed
retry corrected imports without confusion
avoid duplicate imports corrupting stock
see who changed important records
perform manual stock checks
maintain basic system settings
run backups and understand recovery steps
trust the system more in daily operations
CRITICAL BUILD PRINCIPLES

Follow these strictly:

work in atomic tasks
one clear task at a time
be production-minded
use transactions for critical write flows
validate everything server-side
do not trust UI-only guards
keep corrections traceable
preserve auditability
design for future growth
keep Filament UI simple for a non-technical owner

Laravel supports transaction-based grouped writes, validation, filesystem disks, logging channels, and scheduling, so this stage should use those capabilities directly.

IMPORTANT ARCHITECTURE DECISIONS

Use these decisions as hard rules:

Decision 1 — Stock corrections must be explicit

Do not let users silently edit product current_stock directly as the normal correction method.

Use dedicated stock adjustment records so every correction is traceable.

Decision 2 — Auditability matters

Important actions should be recorded in an activity log.

Decision 3 — Failed import handling should be recoverable

Users should be able to inspect failures clearly and retry after correction.

Decision 4 — Duplicate uploads should be blocked or strongly warned

The system must reduce the risk of deducting stock twice from the same file.

Decision 5 — Backups should be operationally planned now

Even if the UI is small, the app must have a real backup and recovery approach.

RECOMMENDED TOOLING DIRECTION
Audit log

A dedicated activity-log approach is appropriate here. Spatie’s laravel-activitylog package supports logging activity and model events and also supports configuration of logged changes.

Backups

A Laravel-native backup approach is appropriate here. Spatie’s laravel-backup package supports database dumps and filesystem backups and can be run via artisan commands.

Custom admin tools

Filament custom pages are a strong fit for:

failed import review
retry flow
stock check page
settings page
backup status page if needed.
ATOMIC TASK PLAN
TASK 1 — Review Stages 1–4 and Confirm Safety Gaps
Goal

Inspect the current implementation and identify the exact places where corrections, retries, audit logging, and settings must plug in.

Do
inspect product/current stock update paths
inspect stock entry logic
inspect sales import batch flow
inspect duplicate import protection
inspect reporting refresh hooks
inspect auth/role setup
inspect existing logs
inspect current config/settings approach
inspect whether file hash tracking already exists
Output

Before major work, state:

what already exists
what safety gaps remain
what assumptions Stage 5 will follow
Acceptance
no duplicate architecture
Stage 5 fits cleanly into the existing system
TASK 2 — Create Database Schema for Stock Adjustments
Goal

Add a dedicated stock adjustment module.

Required table: stock_adjustments

Columns should include at minimum:

id
product_id foreign key
adjustment_type string or enum-like value
quantity_delta integer
previous_stock integer
new_stock integer
reason string or enum-like value
note nullable
adjusted_by foreign key to users
adjustment_date datetime or timestamp
related_reference nullable
timestamps
Recommended adjustment_type values
increase
decrease
Recommended reason values
damaged
expired
missing
correction
stock_count_reconciliation
system_fix
other
Requirements
use foreign keys
add useful indexes
keep schema future-friendly
store both previous and new stock for traceability
Acceptance
migrations run successfully
adjustments are fully traceable
TASK 3 — Implement Stock Adjustment Domain Logic
Goal

Create safe reusable business logic for stock adjustments.

Build

Create dedicated classes such as:

CreateStockAdjustmentAction
StockAdjustmentService
This logic must:
validate adjustment intent
load product safely
calculate previous stock
calculate new stock
reject invalid negative outcomes unless explicitly allowed
create stock adjustment record
update product current_stock
run inside a database transaction

Laravel transactions should be used here because stock update + adjustment record must succeed or fail together.

Rules
stock cannot silently go below zero
every adjustment must require a reason
every adjustment must record who made it
do not allow direct raw stock overwrite as the normal path
Acceptance
adjustments are transactional
audit trail is preserved
negative stock is prevented by default
TASK 4 — Build Filament Resource or Custom Page for Stock Adjustments
Goal

Create a simple admin UI for stock adjustments.

Requirements

Admin/sudo should be able to:

create a stock adjustment
select a product
see current stock before adjustment
choose increase or decrease
enter quantity
choose reason
enter note
save adjustment
review adjustment history
UX expectations
product field should be searchable
current stock should be visible before save
warnings should be clear
labels should be human-readable
Table columns
date
product
reason
adjustment type
quantity delta
previous stock
new stock
adjusted by
Acceptance
owner can fix stock safely
history is easy to review
TASK 5 — Add Activity / Audit Log
Goal

Record important actions across the app.

Recommended approach

Use a dedicated audit/activity log approach. A package such as Spatie activitylog is suitable because it supports logging activity and model events and configurable logging of changes.

Important events to log

At minimum:

product created
product updated
stock entry created
stock adjustment created
sales import uploaded
sales import processed
failed import retried
settings changed
login/logout events if practical
critical report rebuild/manual maintenance actions if practical
Requirements
log causer/user where available
log subject/model where applicable
log meaningful descriptions
avoid noisy useless logs
avoid logging secrets or sensitive credentials
Acceptance
key actions are traceable
logs are useful, not spammy
TASK 6 — Build Audit Log Review Screen in Filament
Goal

Let admin/sudo review important system activity.

Requirements

Create a review page/resource showing:

date/time
user
action description
subject type
subject identifier
changed properties if useful
filter by date
filter by user
filter by action/module
UX expectations
readable and searchable
no raw technical clutter unless expanded
useful for investigating problems
Acceptance
owner/developer can understand who changed what
TASK 7 — Harden Duplicate Import Protection
Goal

Make duplicate sales import protection stronger and clearer.

Requirements

Review and improve duplicate detection using:

file hash
original filename
import date range
possibly row-count sanity checks
Preferred behavior
compute and store file hash
if same hash already exists in a processed batch, block by default
allow only sudo override if you decide an override is needed
surface a very clear duplicate warning
Important

Do not silently accept duplicate uploads.

Acceptance
duplicate daily file uploads are strongly prevented
accidental double stock deduction risk is reduced
TASK 8 — Build Failed Import Review Page
Goal

Create a strong review flow for failed rows.

Requirements

Admin/sudo should be able to:

open an import batch
view failed rows clearly
see row number
see raw row data
see failure reasons
filter failures by reason if useful
download failure details if practical
UX expectations
failure reasons must be human-readable
page must help the user fix the spreadsheet, not confuse them
Acceptance
failed imports are understandable
owner can see what needs correction
TASK 9 — Build Retry Workflow for Corrected Imports
Goal

Allow corrected failed imports to be retried in a clean way.

Important design rule

Do not mutate the original failed rows in place in a hidden way.

Preferred workflow
user fixes the spreadsheet externally
uploads a corrected file as a new batch
system links it to the previous failed batch optionally through metadata
Optional enhancement

Allow exporting failed rows into a correction template.

Requirements
retry must still go through full validation
retry must still create a new batch
retry must still respect duplicate detection
retry must not duplicate already-processed valid rows from a previous batch accidentally
Acceptance
retry flow is safe
history remains understandable
no hidden stock corruption occurs
TASK 10 — Build Manual Stock Check / Stock Count Page
Goal

Create a lightweight stock check page for reconciliation work.

This page should allow:
search product by name or sku
filter by category
see current stock
see reorder level
see stock status
optionally enter physically counted quantity for comparison
optionally generate a suggested adjustment preview without auto-applying it
Important

This should be a manual review tool, not a silent overwrite tool.

Preferred design

A custom Filament page is appropriate here because this is a workflow page, not plain CRUD. Filament custom pages are designed for this kind of admin screen.

Acceptance
owner can check stock easily
reconciliation becomes simpler
TASK 11 — Add Basic Settings / Configuration Module
Goal

Create a lightweight settings area for essential app/business configuration.

Settings to support now

At minimum:

supermarket name
supermarket logo nullable
currency
default low-stock threshold fallback if needed
business contact fields if useful
report/export display name
timezone if needed
Requirements
keep it small
validate all settings server-side
store uploaded assets through Laravel filesystem disks, not hardcoded paths. Laravel’s filesystem abstraction is the correct foundation for this.
Acceptance
owner can manage basic business settings
settings are persisted safely
TASK 12 — Add Backup and Recovery Support
Goal

Put real operational backup support in place.

Recommended approach

Use a Laravel-native backup workflow. Spatie laravel-backup supports backing up files and a database dump, and its docs recommend configuring a dedicated backup disk.

Requirements

Implement at minimum:

backup package installation/config if not already present
backup disk configuration
artisan backup command support
scheduled backups through Laravel scheduler
cleanup/pruning support if applicable
README / ops notes explaining restore expectations

Laravel’s scheduler is the right place to automate recurring backup jobs.

Important

Even if full restore UI is not built, the application must include:

backup run instructions
backup location strategy
recovery notes
environment considerations
Acceptance
backups can run
backup storage is configured
recovery notes exist
scheduled backup path is ready
TASK 13 — Strengthen Validation, Logging, and Error Handling in Critical Flows
Goal

Review the dangerous paths and harden them.

Review these flows
stock adjustment creation
sales import upload
sales import processing
retry upload
settings updates
backup triggers
any manual maintenance command exposed in admin tools
Requirements
use Laravel validation consistently server-side. Laravel supports rich validation rules and unique checks.
use safe logging channels/config appropriately; Laravel’s logging system is channel-based.
avoid exposing stack traces in user-facing admin pages
use human-readable error messages
avoid logging secrets
ensure protected actions require authenticated authorized users
Acceptance
dangerous flows are hardened
logs are useful
errors are safer and clearer
TASK 14 — Add Tests for Stage 5 Safety Features
Goal

Protect the safety layer with automated tests.

Minimum tests required

Add tests for:

stock adjustment increases stock correctly
stock adjustment decreases stock correctly
stock adjustment cannot reduce below zero by default
stock adjustment records previous and new stock correctly
activity log records key actions
duplicate import detection blocks same file hash
failed import review data is retrievable
retrying corrected import creates a new batch safely
settings can be updated by authorized user
unauthorized user cannot access settings / adjustment / audit pages
backup command configuration exists or backup commands can be invoked in expected environment
manual stock check page/query returns expected products
Acceptance
safety logic is protected
refactoring later stays safer
TASK 15 — UX, Performance, and Code Quality Review
Goal

Polish Stage 5 before completion.

Do
review labels and wording
keep forms simple
keep review pages readable
ensure tables paginate
ensure product search is fast enough
review indexes for adjustments, failures, logs
remove dead code
update README and ops notes
keep navigation coherent
Suggested navigation
Dashboard
Inventory
Categories
Products
Stock Entries
Stock Adjustments
Stock Check
Sales
Daily Sales Export
Sales Imports
Sales Records
Failed Import Review
Reports
Daily Reports
Weekly Summary
Sales Trends
Low Stock Report
System
Audit Log
Settings
Backup / Maintenance (if exposed)
Acceptance
panel feels coherent
Stage 5 feels like the trust layer of the app
codebase remains clean
TECHNICAL REQUIREMENTS

Use modern Laravel conventions.

Use Laravel features for:

transactions.
filesystem storage and uploads.
validation.
scheduler-based recurring jobs.
logging channels.

Use Filament for:

CRUD where appropriate
custom workflow pages where appropriate.

Use a package approach where sensible for:

activity log.
backups.

Do not hardcode storage paths or fragile maintenance logic.

SECURITY AND INTEGRITY RULES
Never allow silent stock overwrites as the normal correction path.
Every stock correction must be traceable.
Failed rows must not silently affect stock.
Duplicate imports must be blocked or clearly warned.
Important admin actions must be attributable to a user.
Uploaded files must be stored through controlled disks.
Recovery operations must be documented.
Authorization must remain tight for all maintenance tools.
Sensitive settings or secrets must never be exposed in logs.
Destructive or maintenance actions should be restricted to the appropriate role if you decide to separate sudo/admin capabilities further later.
EXPECTED OUTPUT FORMAT

Work step by step.

For each atomic task:

state the task name
state the goal
implement it
show important files created or changed
explain why
provide commands to run
explain how to verify it works

At the end provide:

A. File tree summary

Show important files added/changed.

B. Migration summary

List new tables and core columns.

C. Safety workflow summary

Explain:

how stock adjustment works
how failed import review works
how retry works
how duplicate detection works
how audit logging works
how settings work
how backups are triggered and maintained
D. Verification checklist

Include:

migrations work
stock adjustments work
audit logging works
duplicate detection works
failed import review works
retry flow works
stock check page works
settings work
backups can run
scheduler hooks are ready
tests pass
E. Next step recommendation

Recommend the next phase only if absolutely necessary.

ACCEPTANCE CRITERIA FOR STAGE 5

This phase is complete only if all of these are true:

stock adjustments can be created safely
stock adjustments update current stock correctly
stock adjustment history is traceable
activity/audit logging works for important actions
duplicate import protection is strong and clear
failed import review is usable
corrected import retry flow is safe
manual stock check page works
basic settings can be managed safely
backup workflow exists and is documented
critical flows are better validated and hardened
authorization remains secure
tests protect the important safety features
the MVP now feels operationally trustworthy
EXPLICIT CONSTRAINTS

Do not implement yet:

full returns module unless minimally necessary
supplier management
barcode scanning
receipt printing
multi-branch support
storefront
advanced accounting
forecasting
mobile app

Stay focused on the MVP safety and correction layer.


EXPLICIT CONSTRAINTS

Do not implement yet:

full returns module unless minimally necessary
supplier management
barcode scanning
receipt printing
multi-branch support
storefront
advanced accounting
forecasting
mobile app

Stay focused on the MVP safety and correction layer.