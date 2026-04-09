Codex Prompt — Stage 2: Inventory Core (Rewritten for Your Exact Workflow)

You are my senior Laravel + Filament implementation agent.

Stage 1 already exists or is in progress:

Laravel installed
Docker environment configured
MySQL configured
Filament installed
authentication working
sudo and admin roles available
first sudo user seeded
basic authorization foundation in place

Now implement Stage 2 of this supermarket system.

This phase is for the core inventory foundation only.

Do not implement yet:

Excel export
Excel import
daily sales upload
sales records
weekly summaries
monthly summaries
supplier management
barcode scanning
POS checkout
customer-facing storefront
advanced analytics

This stage must focus on:

categories
products
stock entries
current stock logic
low-stock and out-of-stock logic
Filament resources
inventory dashboard widgets
secure and scalable structure
Main Business Context

This is for a supermarket.

The supermarket sells goods under categories.

Example:

Category: Cosmetics
Product Group: Perfume
Product Name: Zara Gold Perfume 50ml
Very important business rule

A product should be created once and reused many times.

Example:

first time: create product Zara Gold Perfume 50ml
next time the supermarket buys more of the same item, do not create a new product again
instead, go to Stock Entries
search for the existing product
select it
add the newly purchased quantity
save

Example:

current stock = 5
quantity added = 7
new current stock = 12

So the logic is:

Create product once
Add stock many times

This is the exact behavior I want.

Product Goal for Stage 2

Build the inventory core so that the supermarket owner can:

create categories
create products under categories
search existing products easily
add stock to an existing product
optionally create a new product during stock entry if it does not exist
always see current stock
know low-stock items
know out-of-stock items
work comfortably inside Filament admin
Critical Build Principles

Follow these strictly:

work in atomic tasks
one clear task at a time
keep code clean and production-minded
use Laravel conventions
keep Filament resources simple and user-friendly
separate business logic from UI where sensible
validate everything
use policies / role-aware access where appropriate
design for future Excel workflow
avoid shortcuts that will make Stage 3 messy
Domain Rules
Categories

Categories are top-level groupings.

Examples:

Cosmetics
Toiletries
Groceries
Beverages
Household Items
Products

A product is a specific sellable item.

Not:

Perfume

But:

Zara Gold Perfume 50ml
Body Mist Classic 100ml
Nivea Roll-On Men
Product Group

A product may also have a broader grouping such as:

Perfume
Soap
Rice
Toothpaste

This helps with organization and filtering.

Stock Entries

Whenever new stock comes in, the user should record a stock entry.

A stock entry should:

point to an existing product
add a new quantity to current stock
record cost price at that time
record selling price at that time
record stock date
record who created it
preserve history
Current Stock

For Stage 2, store current_stock directly on the products table for fast reads.

But also:

keep stock entry history
use transactions
never update stock casually from many places
Reorder Level

Each product should have a reorder_level.

Rules:

if current_stock <= reorder_level, mark it as low stock
if current_stock == 0, mark it as out of stock
Core UX Rule for Stock Entry

This is a major requirement.

When creating a stock entry in Filament:

Preferred flow
user opens Stock Entry form
user searches for an existing product using a searchable select
user selects the product
user enters quantity added
user enters cost price
user enters selling price
user saves
If product does not exist

The form should support creating a new product from inside the stock-entry flow in a clean way.

Preferred UX:

searchable product select
plus “Create new product” option/modal
after creation, the new product is selected automatically
user then continues the stock entry

This matches Filament’s documented relationship select patterns and new-option modal support.

Architecture Expectations

Do not bury all business rules inside Filament resource classes.

Keep important logic in:

actions
services
support/domain classes
policies
model helpers only where appropriate

Filament should mostly handle:

forms
tables
filters
actions
dashboard widgets
navigation
Atomic Task Plan

Implement in this order unless there is a strong technical reason to adjust slightly.

TASK 1 — Review Existing Stage 1 and Confirm Conventions
Goal

Inspect the Stage 1 setup and align Stage 2 with existing conventions.

Do
inspect auth and role structure
inspect Filament panel/provider setup
inspect Docker/database config
inspect whether a role/permission package already exists
inspect app namespaces and conventions
inspect seeded sudo/admin handling
Output

Before major changes, state:

what already exists
what assumptions you are making
what conventions you will follow in Stage 2
Acceptance
no duplicate setup
no conflicting conventions
Stage 2 fits naturally into Stage 1
TASK 2 — Design and Create Database Schema for Inventory Core
Goal

Create the schema for categories, products, and stock entries.

Required Tables
categories

Columns:

id
name
slug
description nullable
is_active boolean default true
timestamps
products

Columns:

id
category_id foreign key
product_group nullable
name
slug
sku unique
brand nullable
variant nullable
description nullable
purchase_price decimal
selling_price decimal
current_stock integer default 0
reorder_level integer default 0
unit_of_measure string default pcs
is_active boolean default true
timestamps
stock_entries

Columns:

id
product_id foreign key
quantity_added integer
unit_cost_price decimal
unit_selling_price decimal
stock_date date
reference nullable
note nullable
created_by foreign key to users table nullable if needed
timestamps
Requirements
use proper foreign keys
use indexes where sensible
enforce unique sku
use decimal for prices, never float
use integer stock unless there is a strong reason otherwise
write migrations cleanly and clearly
Acceptance
migrations run successfully
schema is future-friendly for Stage 3 Excel work
schema supports fast stock queries
TASK 3 — Create Eloquent Models, Relationships, and Helpful Scopes
Goal

Create models and relationships cleanly.

Models
Category
Product
StockEntry
Relationships
Category
hasMany products
Product
belongsTo category
hasMany stockEntries
StockEntry
belongsTo product
belongsTo creator user if implemented
Helpful Scopes / Helpers

Add only helpful ones, such as:

active categories
active products
low stock products
out of stock products

Possible model helpers:

isLowStock(): bool
isOutOfStock(): bool
stockStatus(): string

Do not overbuild.

Acceptance
relationships are correct
code stays readable
helpers improve clarity
TASK 4 — Implement the Stock Entry Business Logic as a Dedicated Action/Service
Goal

Create a safe, reusable stock entry creation flow.

Very important rule

Stock mutation logic must not live only inside Filament.

Build

Create a dedicated class such as:

CreateStockEntryAction
or
StockEntryService
It must:
validate stock entry payload
create the stock entry
increase product current_stock
handle price update decisions clearly
use database transactions
be reusable in Filament and future imports
Required stock rule

When a stock entry is created:

product stock increases by quantity_added
entry is saved historically
product stock updates immediately
Price rule decision

Decide and document one of these:

Option A

Stock entry prices are historical only, and product prices are managed separately.

Option B

When stock is added, the user may choose to update the product’s current purchase/selling price from that stock entry.

Preferred implementation

Use Option B in a controlled way:

allow stock-entry form to include a toggle like:
update product prices with these values
if checked, update product purchase/selling price
if not checked, keep stock entry prices historical only

This gives flexibility without magical behavior.

Acceptance
stock entry creation is transactional
product stock updates correctly
logic is testable and reusable
logic is not tightly coupled to Filament UI
TASK 5 — Build Category Resource in Filament
Goal

Create a simple, clean category management resource.

Requirements

Admin/sudo should be able to:

create category
edit category
list categories
activate/deactivate category
Form Fields
name
slug auto-generated but editable
description
is_active
Table Columns
name
slug
active status
products count if efficient
created_at
UX expectations
simple labels
clean layout
no clutter
Acceptance
category CRUD works
UI is clean and understandable
TASK 6 — Build Product Resource in Filament
Goal

Create a clear product management resource.

Important business rule

This resource is for creating the product once.

It is not the main place for repeatedly adding stock.

Repeated stock additions should happen through Stock Entries.

Product form fields

At minimum:

category
product_group
name
sku
brand
variant
description
purchase_price
selling_price
reorder_level
unit_of_measure
is_active
Current stock handling

For clarity and auditability:

do not make product creation depend on “starting stock” language
product may be created with current_stock = 0
stock should then be added through Stock Entries
if you include current stock field at all, handle carefully and do not encourage bypassing stock history
My preferred direction
create product with zero stock
add quantity later through stock entries only
Table columns
name
sku
category
product_group
selling_price
current_stock
reorder_level
stock status badge
active status
updated_at
Filters
category
active/inactive
low stock
out of stock
Search
name
sku
brand if useful
Acceptance
product CRUD works
filters and search work
stock status is clear
UI is simple for the supermarket owner
TASK 7 — Build Stock Entry Resource in Filament
Goal

Create stock entry management exactly around the real business flow.

Core UX requirement

The stock-entry form should be the main place for adding new stock to existing products.

Form behavior
product field must be a searchable relationship select
user should be able to type product name/SKU and quickly find existing product
preloading may be used if sensible, but do not hurt performance
if product does not exist, user should be able to create a new product from the same form via create-option modal
after creating the new product, the select should use that product automatically

This matches official Filament select capabilities like searchable relationship selects, preloading, createOptionForm, and createOptionUsing.

Form fields
product
quantity_added
unit_cost_price
unit_selling_price
stock_date
reference
note
optional toggle: update product prices with this stock entry
Important

Creation of stock entry must go through the service/action from Task 4.

Do not duplicate stock mutation logic inside Filament.

Table columns
stock_date
product name
sku
quantity added
unit cost price
unit selling price
created by
created_at
Filters
by date
by category
by product
Acceptance
stock entry creation updates product stock correctly
history is visible
existing products are easy to find
new products can be created inline when necessary
UI stays simple
TASK 8 — Add Inventory Dashboard Widgets
Goal

Create useful dashboard widgets for inventory.

Required widgets

At minimum:

Total Categories
Total Products
Total Stock Quantity
Low-Stock Products Count
Out-of-Stock Products Count
Recent Stock Entries table/list
Optional: urgent restock items

Use Filament’s built-in widget patterns instead of custom dashboard hacks. Official docs support stats overview widgets and table widgets for this purpose.

Requirements
efficient queries
friendly labels
operational usefulness
clean layout
Acceptance
dashboard is useful on first login
widgets are accurate
loading is reasonable
TASK 9 — Authorization and Role Visibility for Stage 2
Goal

Keep resource access clean and future-ready.

Current roles
sudo
admin
For now

Both roles can access:

dashboard
categories
products
stock entries

But structure access cleanly so future roles can be added later without major rewrites.

Acceptance
access is protected
role-aware structure is in place
no careless exposure of admin resources
TASK 10 — Add Development Seeders for Realistic Local Testing
Goal

Make local development faster.

Seeders

Create optional development seeders for:

sample categories
sample products
sample stock entries
Requirements
realistic supermarket data
no junk filler
safe for repeated seeding where practical
development-focused, not production-required
Acceptance
local testing becomes faster
seeded data matches real use case
TASK 11 — Write Tests for Core Inventory Logic
Goal

Protect the important business rules.

Minimum tests

Add tests for:

category can be created
product can be created
duplicate sku is rejected
stock entry increases product current stock
low-stock logic works
out-of-stock logic works
stock entry creation is transactional
invalid stock entry payload is rejected
existing product can be selected for stock entry flow
creating stock entries does not require recreating the product
Strong focus

Test the stock mutation logic thoroughly.

Acceptance
tests are meaningful
core logic is protected
Stage 3 can build safely on this
TASK 12 — Review UX, Navigation, and Code Quality
Goal

Polish Stage 2 before completion.

Do
review navigation grouping
keep admin panel uncluttered
check form labels
check table readability
check filters and searches
remove dead code
check validation messages
confirm naming consistency
update README if needed
Suggested navigation
Dashboard
Inventory
Categories
Products
Stock Entries
Acceptance
panel feels coherent
codebase is clean
Stage 2 is ready for Stage 3
Technical Requirements
Framework conventions

Use modern Laravel conventions and build cleanly on the current Laravel release approach.

Product lookup in stock entry

Use Filament relationship-based searchable select for the product field, and support inline creation for missing products.

Dashboard

Use Filament stats overview and table/list widgets for inventory summary.

Data types
prices as decimal
stock as integer
booleans for active fields
unique index for sku
Slugs

Use slugs for readability if helpful, but SKU is the business identifier.

Unit of measure

Support a simple text field such as:

pcs
pack
carton
bottle

Do not overbuild conversion logic yet.

Security and Integrity Requirements

Follow these carefully:

validate all form inputs server-side
use mass assignment safely
use transactions for stock mutation
protect admin resources
do not rely on UI alone for validation
ensure duplicate sku cannot slip through
ensure stock history is not bypassed
keep authorization clean for future expansion
Performance Expectations

Even at this stage:

Do
add sensible indexes
use pagination
eager load relationships where needed
avoid obvious N+1 issues
keep dashboard queries efficient
keep searchable selects practical and not overloaded

Filament docs note that option limits, debounce, and preloading need to be used thoughtfully so selects do not become slow or memory-heavy.

Do not
prematurely over-optimize
introduce unnecessary caching yet
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

C. Verification checklist

Include:

migrations work
seeders work
category CRUD works
product CRUD works
stock entry creation works
current stock updates correctly
existing product can be searched and selected
inline new product creation from stock-entry flow works
dashboard widgets load
low stock / out of stock badges work
tests pass
D. Next step recommendation

Recommend the Stage 3 implementation path.

Acceptance Criteria for Stage 2

This phase is complete only if all of these are true:

categories can be created, edited, listed, and managed
products can be created, edited, listed, and filtered
every product belongs to a category
every product has a unique sku
stock entries can be created safely
creating a stock entry increases product current stock correctly
existing products can be searched and selected easily during stock entry
new products can be created inline from stock entry when needed
low-stock logic works correctly
out-of-stock logic works correctly
dashboard widgets show useful inventory information
authorization remains clean and future-ready
tests protect the important business rules
the codebase remains clean for Stage 3 Excel import/export work
Explicit Constraints

Do not implement yet:

Excel export
Excel import
daily sales upload
sales records
weekly summaries
supplier management
barcode scanning
POS workflow
customer-facing website
advanced analytics

Stay focused on the inventory core.

Final Instruction

Now implement Stage 2 in atomic steps, with production-minded quality, clean Laravel architecture, simple Filament UX, and exact support for this business rule:

Create product once. Search and select it later whenever new stock is added. Only create a new product if it truly does not exist yet.