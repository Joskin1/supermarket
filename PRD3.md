Codex Prompt — Stage 3: Daily Sales Excel Export, Import, Validation, and Stock Deduction

You are my senior Laravel + Filament implementation agent.

Stage 1 and Stage 2 already exist or are in progress:

Laravel installed
Docker configured
MySQL configured
Filament installed
authentication working
sudo and admin roles working
inventory core implemented:
categories
products
stock entries
current stock
low-stock logic
dashboard widgets

Now implement Stage 3.

This phase is for the daily sales Excel workflow only.

Do not implement yet:

weekly summary reports
monthly summary reports
supplier management
barcode scanning
full POS/live checkout
customer-facing storefront
advanced analytics
receipt printing
multi-branch support

This stage must focus on:

daily sales template export
offline daily sales recording
Excel upload/import
import validation
import batch tracking
sales record storage
stock deduction
import error handling
daily totals and daily overview
secure, scalable, and user-friendly admin workflow
Main Business Context

This supermarket does not want to depend on live internet all day.

So the workflow is:

At the beginning of the day, export a sales Excel file from the system.
Staff use that file offline during the day.
For each sale, they fill rows such as:
date
product
product code
selling price
quantity sold
At the end of the day, they upload the completed file to the web application.
The system validates the file.
The system stores the sales rows.
The system deducts sold quantities from current stock.
The system shows the daily sales total and the daily import result.

This is the exact workflow I want.

Critical Business Rules
Rule 1 — Product already exists

Products are created during inventory setup and stock management.

The sales file should not create products.

A sales row must map to an existing product.

If the product does not exist, that row must fail validation.

Rule 2 — Product code is the main identifier

The sales import should primarily identify items by product code / SKU, not only by product name.

Product name may be present in the file for readability, but SKU is the trusted key.

Rule 3 — Sales import reduces stock

Every valid sales row must deduct quantity from the product’s current stock.

Example:

current stock = 12
quantity sold = 4
new stock = 8
Rule 4 — Invalid rows must not affect stock

If a row fails validation, it must not create a sales record and must not reduce stock.

Rule 5 — No silent negative stock

If a row would reduce stock below zero, that row should fail by default.

For this MVP, safer behavior is:

reject the row
report the reason clearly

Do not silently allow negative stock.

Rule 6 — One uploaded file becomes one import batch

Every uploaded daily sales file should generate a batch record so the system can track:

file name
upload date/time
uploaded by
date range inside file if applicable
total rows
successful rows
failed rows
total quantity sold
total sales amount
processing status
Stage 3 Goal

Build a reliable daily sales workflow inside Filament so the supermarket owner can:

export a daily sales template
use it offline
upload it later
validate every row safely
store successful rows
deduct stock correctly
see daily totals
review failed rows clearly
trust the inventory after every import
Recommended Technical Direction

Use the existing Laravel + Filament stack.

Excel handling

Use Laravel Excel because it supports:

heading-row imports
row validation
chunk reading
batch inserts
queued chunk processing if the file sizes later grow.
Filament admin workflow

Use Filament custom pages/resources and file upload fields for the upload UI. Filament also supports export/import-oriented admin flows and custom pages cleanly.

File storage

Store uploaded files through Laravel’s filesystem abstraction, not custom manual file handling.

Transactions

Use database transactions around stock deduction and sales-row creation logic to prevent inconsistent stock when a row or chunk fails.

Architecture Expectations

Do not bury all import logic inside a Filament page class.

Keep important logic in dedicated classes such as:

ExportDailySalesTemplateAction
ProcessSalesImportAction
SalesImportValidator
SalesImportRowProcessor
CreateSalesImportBatchAction
SalesImportSummaryService

Filament should mostly handle:

upload form
export trigger
result display
table/list screens
daily summary widgets

Business rules must remain reusable and testable outside the UI.

Atomic Task Plan

Implement in this order unless there is a strong technical reason to adjust slightly.

TASK 1 — Review Stage 2 Inventory Core and Confirm Constraints
Goal

Inspect the existing product and stock structure before implementing sales import.

Do
inspect product schema
inspect stock entry logic
inspect current stock logic
inspect Filament navigation
inspect auth/role handling
inspect whether Laravel Excel is already installed
inspect whether queue support exists already
inspect whether Filament notifications or table widgets are in use
Output

Before major work, state:

what already exists
what assumptions you are making
what constraints Stage 3 must respect
Acceptance
no duplicate architecture
Stage 3 builds cleanly on Stage 2
TASK 2 — Design and Create Database Schema for Sales Imports
Goal

Add the schema needed for daily sales imports and row storage.

Required Tables
sales_import_batches

Columns should include at minimum:

id
batch_code unique
file_name
file_path nullable if stored
original_file_name nullable if useful
uploaded_by foreign key to users
status string or enum-like field
sales_date_from nullable
sales_date_to nullable
total_rows default 0
successful_rows default 0
failed_rows default 0
total_quantity_sold default 0
total_sales_amount decimal default 0
notes nullable
processed_at nullable
timestamps
sales_records

Columns should include at minimum:

id
batch_id foreign key
product_id foreign key
product_code_snapshot
category_snapshot
product_name_snapshot
unit_price decimal
quantity_sold integer
total_amount decimal
sales_date date
note nullable
created_by nullable or uploaded_by snapshot if useful
timestamps
sales_import_failures

Recommended for auditability and review.

Columns should include at minimum:

id
batch_id foreign key
row_number
raw_row_json or raw_row text/json
error_messages text/json
product_code nullable
product_name nullable
sales_date nullable
timestamps
Requirements
use proper foreign keys
add indexes where useful
batch code should be unique
prices must be decimal, never float
failure storage should be reviewable and useful for support/debugging
Acceptance
migrations run successfully
schema supports traceability and daily totals
schema is ready for future weekly reporting
TASK 3 — Install and Configure Laravel Excel Properly
Goal

Set up Excel import/export support correctly.

Do
install Laravel Excel if not already installed
configure it cleanly
keep implementation future-ready for larger files
Requirements

Use Laravel Excel features appropriately, especially:

heading row support
validation concerns
chunk reading for memory control
batch inserts where helpful
queued/chunked processing as an upgrade path for bigger files later.
Acceptance
package is installed cleanly
export and import classes can be built on top of it
implementation direction is consistent with package capabilities
TASK 4 — Design the Daily Sales Excel Template
Goal

Define the exact Excel structure that staff will use daily.

Required Columns

At minimum:

date
product_code
category
product_name
unit_price
quantity_sold
total_amount
note optional
Important rules
product_code is the main identifier
category and product_name are mainly for human readability
total_amount may either be:
user-filled and validated
or auto-calculated by the system during import
Preferred MVP decision

Use:

unit_price
quantity_sold
system calculates total_amount if not present or verifies it if present
Export approach decision

Decide and implement one of these:

Option A — Blank structured template

Only the headings are exported.

Option B — Prefilled daily sales sheet

Export a sheet already populated with:

product code
category
product name
current selling price

Then staff only fill:

quantity sold
notes if needed
Preferred implementation

Use Option B because it reduces typing mistakes and helps non-technical users. This is an application-level design choice based on your workflow, not a package rule.

Acceptance
template structure is clear
staff can use it offline easily
error-prone manual typing is reduced
TASK 5 — Build Daily Sales Template Export Logic
Goal

Allow admin/sudo to export the daily sales file from Filament.

Requirements

Build an export flow that:

exports an XLSX file
includes the daily sales headings
preferably preloads current active products
includes product code, category, product name, and current selling price
leaves quantity and optional notes for staff to fill
may optionally include one row per active product
Output columns in preferred export
date default blank or today
product_code
category
product_name
unit_price
quantity_sold blank
total_amount blank or formula if you choose
note blank
Technical direction

This can be implemented with Laravel Excel export classes. Filament also has export actions, but because this is a custom domain-specific sheet rather than a generic table export, a dedicated export class/action is likely cleaner. Filament’s export tooling exists, but it is more generic and notification-driven.

Acceptance
export can be triggered from the admin panel
downloaded file has the correct format
prefilled rows are accurate
TASK 6 — Build a Filament Custom Page or Resource for Sales Import
Goal

Create the admin UI for daily sales upload.

Requirements

Build a clean Filament page or resource where admin/sudo can:

upload a daily sales Excel file
see instructions for the expected format
submit the file for processing
see batch result summary
open past batches
review failed rows

A custom Filament page is acceptable and supported for this kind of workflow.

Form fields
file upload
optional notes
optional checkbox or toggle if needed for strict validation mode
File upload requirements
accept only allowed file types such as .xlsx and optionally .csv
use Laravel/Filament file upload cleanly
store file through Laravel filesystem abstraction.
Acceptance
upload page is clean and understandable
user can submit a file successfully
file is stored safely
page is ready to show results
TASK 7 — Implement Sales Import Validation Logic
Goal

Validate every row before it affects inventory.

Required row validation rules

At minimum:

date is required and valid
product_code is required
product_code must exist in products table
quantity_sold is required, numeric/integer, and greater than 0
unit_price is required and numeric
product_name may be compared for warning or informational mismatch
category may be compared for warning or informational mismatch
if total_amount is present, it must match or be close to unit_price * quantity_sold according to chosen rule
Important validation behavior

Rows must fail if:

product code is missing
product code is unknown
quantity sold is invalid
unit price is invalid
sale date is invalid
stock would go below zero

Laravel Excel supports row validation and collecting failures after import, especially when combined with batch inserts.

Important design decision

Choose whether name/category mismatches should:

fail the row strictly
or pass with a warning if product_code is valid
Preferred MVP decision
product_code is authoritative
if name/category differ, store warning if useful, but do not fail solely for that
fail only when trusted identifiers or stock rules fail
Acceptance
invalid rows are captured clearly
stock is untouched for invalid rows
failure messages are readable
TASK 8 — Implement Batch Creation and Processing Workflow
Goal

Every upload should produce a batch record and a controlled processing flow.

Build

Create a process like:

validate file at upload level
store uploaded file
create sales_import_batch
read file rows
validate rows
save failures to sales_import_failures
save valid rows to sales_records
deduct stock for valid rows
update batch totals and status
mark batch as processed
Status examples
uploaded
processing
processed
processed_with_failures
failed
Important

Do not just upload and process invisibly with no tracking.

Acceptance
every upload has a batch record
batches are reviewable
statuses are accurate
TASK 9 — Implement Sales Row Processing and Stock Deduction Logic
Goal

Create the reusable domain logic that converts valid rows into sales records and deducts stock.

Very important rule

Do not place all stock deduction logic only inside the Filament page.

Build

Create a dedicated class such as:

ProcessSalesImportAction
SalesImportRowProcessor
ApplySalesRecordToInventoryAction
Required behavior

For each valid row:

find product by product code
verify stock is sufficient
create sales_record
deduct quantity_sold from product current_stock
do this safely in a transaction

Laravel recommends using database transactions so grouped operations roll back together on failure.

Important batch integrity decision

Choose how processing should behave if a file contains both valid and invalid rows.

Option A

Reject the entire file if any row fails.

Option B

Process valid rows, record invalid rows separately, and finish with partial-success status.

Preferred MVP decision

Use Option B:

process valid rows
record invalid rows
mark batch as processed_with_failures if needed

This is more practical for daily supermarket operations.

Acceptance
valid rows create sales records
stock deduction is correct
invalid rows do not affect stock
logic is reusable and testable
TASK 10 — Add Duplicate Upload Protection
Goal

Reduce accidental re-import of the same daily file.

Requirements

Implement reasonable duplicate-detection protection.

Possible approaches:

file hash
original file name + upload timestamp guard
batch metadata comparison
explicit warning if the same file content has already been processed
Preferred MVP behavior
compute a file hash on upload
if same hash already exists in a processed batch, warn or block according to chosen policy
Preferred MVP decision

Warn clearly and block by default.

Acceptance
accidental duplicate imports are less likely
duplicate processing does not silently corrupt stock
TASK 11 — Add Daily Totals, Batch Summary, and Review Screens
Goal

Let the user review what happened after each import.

Required batch summary data

For each batch, show:

batch code
uploaded by
file name
processed status
total rows
successful rows
failed rows
total quantity sold
total sales amount
sales date range if available
processed time
Add list/detail screens

The admin should be able to:

view all past import batches
open one batch
see valid sales rows
see failed rows
understand what went wrong
Dashboard / widget expectations

Add useful Stage 3 widgets such as:

sales imported today
total amount sold today
total quantity sold today
recent import batches
batches with failures

Filament supports stats overview and table widgets for this type of dashboard presentation.

Acceptance
import result is easy to understand
past imports are reviewable
daily numbers are visible
TASK 12 — Authorization, Security, and File Handling Review
Goal

Ensure the import workflow is safe.

Current roles
sudo
admin
For now

Both can:

export daily sales template
upload daily sales file
view import batches
view failures
view sales records
Security requirements
validate file type and size
store file safely using Laravel filesystem
do not trust spreadsheet values blindly
validate server-side
do not expose sensitive internal exceptions to users
keep uploaded file paths controlled
protect all routes/pages behind auth/role checks
use least-privilege design for future extension

Laravel’s filesystem and upload handling should be used instead of ad hoc path manipulation.

Acceptance
import routes/pages are protected
files are handled safely
validation is strict
TASK 13 — Write Tests for the Daily Sales Workflow
Goal

Protect the core business rules of this stage.

Minimum tests required

Add tests for:

daily sales template export returns correct columns
sales import batch is created on upload
valid row creates sales record
valid row deducts stock correctly
unknown product code fails
invalid quantity fails
insufficient stock fails
invalid rows do not affect stock
mixed valid/invalid file processes valid rows and records failures
duplicate file detection works
batch totals are computed correctly
authorized users can access import flow
unauthorized users cannot access import flow
Strong focus

Test stock deduction and partial-failure behavior thoroughly.

Acceptance
tests are meaningful
import workflow is protected against regressions
Stage 4 reporting can build safely on this
TASK 14 — Review UX, Performance, and Code Quality
Goal

Polish Stage 3 before completion.

Do
review page labels and instructions
keep upload flow simple for non-technical users
make failure messages human-readable
review navigation grouping
review table readability
check eager loading and N+1 issues
ensure large files do not load recklessly into memory
use chunk reading where appropriate for memory control.
update README if needed
Suggested navigation
Dashboard
Inventory
Categories
Products
Stock Entries
Sales
Daily Sales Export
Sales Imports
Sales Records
Acceptance
admin panel feels coherent
processing flow is understandable
codebase is ready for Stage 4
Technical Requirements
Excel package usage

Use Laravel Excel with the right concerns for this workflow:

heading row handling
validation
chunk reading
batch inserts where sensible
queued/chunked processing as a future-ready path.
File upload and storage

Use Filament/Laravel upload handling and Laravel filesystem storage.

Transactions

Use DB::transaction() or equivalent around grouped operations that must succeed or roll back together.

Filament UI

Use custom pages/resources, table widgets, and stats widgets where they fit naturally.

Data Design Preferences
Stock quantity

Use integer stock quantities for this MVP.

Prices

Use decimal columns, never float.

Product identity

Use product code / SKU as the main import identifier.

Historical snapshots

Store snapshots on each sales record:

product code snapshot
category snapshot
product name snapshot

This protects historical data if product names later change.

Validation and Edge Cases to Handle

The application must handle these cases well:

blank file upload
wrong file type
missing heading row
wrong column names
blank product code
unknown product code
invalid date
blank quantity
negative quantity
zero quantity
invalid price
quantity sold greater than current stock
duplicate upload of same file
same product appearing many times in a file
inconsistent product name with valid product code
partial file failure
repeated upload attempt after corrections
Important design decision for repeated product rows in one file

If the same product appears multiple times in a single uploaded file:

either process each row independently
or aggregate rows by product/date before deduction
Preferred MVP decision

Process each row independently, but carefully and in order.
This is simpler and easier to audit.

Performance Expectations

Even though this is MVP:

Do
index useful columns
paginate batch and sales lists
avoid obvious N+1 queries
use chunk reading for non-trivial imports
keep file-processing memory usage under control.
Do not
prematurely build a heavy queue architecture unless needed now
overcomplicate with distributed processing yet
Future-ready note

If files grow larger later, Laravel Excel supports queued chunk imports, which can be introduced without redesigning the whole concept.

Expected Output Format

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

C. Workflow summary

Explain the end-to-end user flow:

export template
fill offline
upload
validate
process valid rows
review failures
see totals
D. Verification checklist

Include:

export works
upload works
batches are created
valid rows are stored
invalid rows are rejected
stock deducts correctly
duplicate upload protection works
daily totals are correct
widgets load
tests pass
E. Next step recommendation

Recommend the Stage 4 path.

Acceptance Criteria for Stage 3

This phase is complete only if all of these are true:

admin/sudo can export a daily sales template
exported sheet has the correct columns
uploaded sales file creates an import batch
every valid row creates a sales record
every valid row deducts stock correctly
invalid rows are captured with useful reasons
invalid rows do not affect stock
product code is the trusted identifier
duplicate upload protection exists
daily totals and batch summaries are visible
import history is reviewable
authorization remains clean and secure
tests protect the important business rules
the system is ready for later weekly/monthly summaries
Explicit Constraints

Do not implement yet:

weekly summaries
monthly summaries
supplier module
barcode scanning
POS checkout
storefront
advanced analytics
receipt printing

Stay focused on the daily Excel sales workflow.