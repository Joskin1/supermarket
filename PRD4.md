Codex Prompt — Stage 4: Reporting Layer, Weekly Summaries, Trends, and Low-Stock Reporting

You are my senior Laravel + Filament implementation agent.

Stage 1, Stage 2, and Stage 3 already exist or are in progress:

Laravel installed
Docker configured
MySQL configured
Filament installed
authentication working
sudo and admin roles working
inventory core implemented
daily sales Excel export/import implemented
sales import batches implemented
sales records implemented
stock deduction implemented
daily totals implemented

Now implement Stage 4.

This phase is for:

daily reporting
weekly summaries
sales charts
low-stock reporting
fast reporting architecture
production-minded reporting pages
secure and scalable reporting logic

Do not implement yet:

full accounting
supplier analytics
barcode module
receipt printing
customer-facing storefront
multi-branch support
advanced forecasting / AI prediction
mobile app
full BI/data warehouse

This stage must focus on:

daily sales reporting pages
weekly summary reporting
reporting summary tables / read models
sales charts and trend widgets
low-stock and out-of-stock reporting
top-selling products and category performance
exportable report views where practical
secure and maintainable report architecture
MAIN BUSINESS CONTEXT

This supermarket uses:

products
stock entries
daily sales Excel uploads
sales import batches
sales records
current stock tracking

The owner now wants to:

see what was sold today
see what was sold during any selected date range
see weekly sales totals
see which products sell most
see which categories perform best
see low-stock items quickly
see out-of-stock items quickly
understand trends without reading raw database tables

The owner is not deeply technical.

So the reporting system must be:

simple
clear
fast
easy to filter
easy to trust
CORE DESIGN DIRECTION

Use this architecture:

sales_records remains the source of truth.
Reporting pages should not always run heavy raw aggregation directly on the full sales table.
Build summary tables / reporting projections for faster dashboard and reporting reads.
Keep reporting calculation logic in dedicated services/actions/commands.
Keep Filament focused on presenting and filtering the reports.

Very important:
Do not bury all reporting logic in Filament widgets and page classes.

Use dedicated classes such as:

BuildDailySalesSummariesAction
BuildWeeklySalesSummariesAction
SalesReportingService
LowStockReportingService
RefreshReportingSummariesCommand
SalesTrendService
STAGE 4 GOAL

Build a production-minded reporting layer so the supermarket owner can:

open a daily report page
filter sales by date or date range
see daily totals
see weekly summaries
see top products
see category sales performance
see sales trends in charts
see low-stock and out-of-stock reports
export useful report data where practical
get a system that remains fast as data grows
CRITICAL BUILD PRINCIPLES

Follow these strictly:

work in atomic tasks
one clear task at a time
keep code production-minded
separate reporting calculations from UI
prefer clarity over cleverness
use strong validation for filters and inputs
use secure defaults
design for future growth
keep the supermarket owner’s usability in mind
avoid building a bloated analytics platform too early
IMPORTANT ARCHITECTURE DECISION

Use these two decisions as hard rules for this stage:

Decision 1 — Summary tables

Use summary tables / read models instead of calculating every report directly from sales_records on every request.

Decision 2 — Export format

Support only:

XLSX
CSV

Do not implement PDF export for reports in this stage.

Reason:

summary tables will keep reports and charts faster as data grows
XLSX/CSV is enough for business reporting now
PDF can wait until the reporting layer is stable
REPORTING ARCHITECTURE REQUIREMENT

Implement reporting with a read-model / summary-table approach.

The app already has sales_records.

Now add summary structures so that:

dashboards stay fast
daily/weekly summaries are easy to query
charts do not re-scan huge raw tables unnecessarily
reporting can scale better later

Recommended summary tables:

daily_sales_summaries
one row per day
daily_product_sales_summaries
one row per product per day
daily_category_sales_summaries
one row per category per day

If you think a lighter version is better for this stage, explain it first.
But the preferred implementation is to add all three because they make daily reports, weekly summaries, charts, and low-stock insights easier and faster.

ATOMIC TASK PLAN
TASK 1 — Review Stage 3 Data Model and Confirm Reporting Assumptions

Goal:
Inspect the existing schema and confirm what Stage 4 can rely on.

Do:

inspect sales_records
inspect sales_import_batches
inspect products
inspect categories
inspect current_stock and reorder_level
inspect existing dashboard widgets
inspect current Filament navigation
inspect whether export helpers already exist

Before making major changes, state:

what already exists
what assumptions you are making
what constraints the reporting layer must respect

Acceptance:

no duplicate structures
Stage 4 fits naturally into Stage 3
TASK 2 — Design and Create Reporting Summary Tables

Goal:
Create the reporting schema for fast read operations.

Required tables:

A. daily_sales_summaries

Columns at minimum:

id
sales_date unique
total_transactions_count
total_quantity_sold
total_sales_amount decimal
batches_count default 0
created_at
updated_at
B. daily_product_sales_summaries

Columns at minimum:

id
sales_date
product_id foreign key
product_code_snapshot
product_name_snapshot
category_id nullable if useful
category_snapshot
total_quantity_sold
total_sales_amount decimal
transactions_count
created_at
updated_at

Add appropriate unique/index strategy so duplicate summary rows do not occur accidentally.

C. daily_category_sales_summaries

Columns at minimum:

id
sales_date
category_id nullable if useful
category_snapshot
total_quantity_sold
total_sales_amount decimal
transactions_count
created_at
updated_at

Requirements:

use proper foreign keys where useful
use indexes for date and lookup columns
prices must be decimal, never float
design uniqueness so summary rebuilds stay safe
keep migrations clean and future-friendly

Acceptance:

migrations run successfully
summary tables support fast daily/weekly reporting
TASK 3 — Build Reporting Summary Refresh Logic

Goal:
Create the logic that fills and refreshes the summary tables from sales_records.

Build dedicated classes such as:

BuildDailySalesSummariesAction
BuildDailyProductSalesSummariesAction
BuildDailyCategorySalesSummariesAction

This logic must:

read from sales_records
aggregate by date / product / category
upsert into summary tables safely
be idempotent
support rebuilding for:
a single date
a date range
full rebuild if needed

Important:
Use transactions where grouped updates should succeed together.

Do not make summary generation depend only on page loads.

Acceptance:

summary rebuilds are repeatable
summaries stay accurate
logic is reusable from command, import hook, or admin action
TASK 4 — Add Summary Refresh Trigger Strategy

Goal:
Decide how summaries stay in sync.

Preferred implementation:
Use both:

refresh summaries immediately after a sales import batch is successfully processed
add an artisan command for manual rebuild/backfill
add scheduler support for nightly verification/rebuild if appropriate

Create:

php artisan reports:refresh-summaries

with useful options such as:

--date=
--from=
--to=
--full

Important:
The command should be safe and production-minded.

Acceptance:

summaries can refresh after imports
summaries can be rebuilt manually
future nightly scheduling is easy
TASK 5 — Build Daily Sales Report Page

Goal:
Create a dedicated daily reporting page in Filament.

This should not just be a tiny widget.
It should be a proper report screen.

Page should allow:

filter by exact date
optionally filter by date range
see total sales amount
see total quantity sold
see number of transactions
see products sold that day
see category breakdown
see top-selling products for the selected date
see batches that contributed to that day if useful

Preferred implementation:
Use a custom Filament page with:

header stats
chart widget(s)
table widget(s)
filter controls

Important:
Keep layout clean and understandable for a non-technical supermarket owner.

Acceptance:

daily report page is clear
filters work
totals match source data
page loads reasonably fast
TASK 6 — Build Weekly Summary Page

Goal:
Create a reporting page for weekly business summary.

The weekly summary should help the owner understand:

total sales amount this week
total quantity sold this week
average daily sales this week
best-selling products this week
best-performing categories this week
days with strongest and weakest sales
week-over-week comparison if practical

Important design decision:
Define “week” clearly.

Preferred default:

use Monday to Sunday
but allow date-range override where practical

Page should allow:

current week
previous week
custom date range

Implementation guidance:
Weekly summary may be computed from daily_sales_summaries rather than raw sales_records wherever possible.

Acceptance:

weekly summary is correct
comparison logic is sane
page remains fast
TASK 7 — Build Sales Charts and Trend Widgets

Goal:
Create useful charts for understanding performance trends.

Add charts such as:

Daily sales amount trend over time
Daily quantity sold trend over time
Category sales distribution
Top products trend for selected period

Important:
Do not create decorative charts.
Every chart should answer a business question.

Preferred chart set:

line chart: sales amount by day
bar chart: quantity sold by day
bar or pie/donut equivalent: category contribution
top products chart: top N products by amount or quantity

Requirements:

charts must respect selected filters/date ranges where practical
chart queries should use summary tables where appropriate
labels should be readable
chart count should stay modest to avoid clutter

Acceptance:

charts are useful
charts are accurate
charts load well
charts do not duplicate the same insight unnecessarily
TASK 8 — Build Low-Stock and Out-of-Stock Reporting Page

Goal:
Create a dedicated stock health report.

This page should help the owner answer:

what items are low in stock
what items are out of stock
which categories have the most stock risk
what needs urgent restocking first

Include:

low-stock table
out-of-stock table
category filters
product search
reorder level visibility
current stock visibility
optional urgency sorting

Recommended stock status rules:

out of stock: current_stock == 0
low stock: current_stock > 0 and current_stock <= reorder_level

Important:
This page is not just inventory listing.
It is a reporting page focused on action.

Acceptance:

owner can quickly identify items to restock
filtering is useful
urgency is visible clearly
TASK 9 — Build Top-Selling Products and Category Performance Reporting

Goal:
Expose performance reporting in a practical way.

Create views/tables/charts for:

top-selling products by quantity
top-selling products by revenue
top-performing categories by revenue
top-performing categories by quantity

Allow filtering by:

date
date range
category where relevant

Important:
Use summary tables where possible.
Avoid scanning entire raw sales tables unnecessarily for routine reporting screens.

Acceptance:

product/category performance is easy to understand
sorting and filtering work
queries remain efficient
TASK 10 — Add Report Export Support

Goal:
Allow useful report exports.

For this stage, support only:

XLSX
CSV

Do not support:

PDF

Support exporting at least:

daily sales report
weekly summary report
low-stock report
top-selling products report

Requirements:

exports should respect active filters where practical
export actions must be authenticated and authorized
exported files must be readable and business-friendly

Acceptance:

filtered report data can be exported
exports are accurate
export logic stays stable and simple
TASK 11 — Add Dashboard Reporting Widgets

Goal:
Improve the main dashboard with operationally useful reporting stats.

Add or refine widgets such as:

sales today
quantity sold today
this week sales
low-stock count
out-of-stock count
top-selling product today or this week
recent sales import batches
recent low-stock items

Important:
Do not overcrowd the dashboard.
The main dashboard should remain useful at first glance.

Acceptance:

dashboard shows the most important operational metrics
widgets are accurate
layout remains clean
TASK 12 — Authorization and Security Review for Reporting

Goal:
Ensure reporting features are protected and production-minded.

Current roles:

sudo
admin

For now both can access:

report pages
report exports
trend widgets
low-stock reporting

Requirements:

protect all reporting pages behind auth and role checks
validate report filter inputs server-side
sanitize and validate export requests
do not expose internal exceptions to end users
avoid raw SQL where not necessary
if raw SQL is needed, parameterize it properly
keep report exports scoped and authenticated
avoid leaking data through careless query building

Acceptance:

reporting pages are protected
export endpoints are protected
filters are validated
implementation is safe
TASK 13 — Write Tests for Reporting Logic

Goal:
Protect the core business rules of Stage 4.

Minimum tests required:

summary tables can be built from sales_records
daily summary totals are correct
product daily summaries are correct
category daily summaries are correct
summary rebuild is idempotent
daily report page data matches underlying summaries
weekly summary calculations are correct
low-stock logic appears correctly in reporting page
out-of-stock logic appears correctly in reporting page
top-selling product ranking is correct
filtered report export returns expected rows
authorized users can access reports
unauthorized users cannot access reports

Strong focus:

aggregation correctness
date-range correctness
summary rebuild safety
export correctness

Acceptance:

important reporting logic is protected
refactoring can happen safely later
TASK 14 — UX, Performance, and Code Quality Review

Goal:
Polish Stage 4 before completion.

Do:

review page labels and wording
ensure filters are intuitive
ensure charts are readable
ensure tables paginate properly
review eager loading and N+1 issues
review indexes
review summary refresh performance
review navigation structure
remove dead code
update README if needed
verify mobile/table readability is acceptable

Suggested navigation:

Dashboard
Inventory
Categories
Products
Stock Entries
Sales
Daily Sales Export
Sales Imports
Sales Records
Reports
Daily Reports
Weekly Summary
Sales Trends
Low Stock Report
Top Products / Category Performance

Acceptance:

panel feels coherent
report pages are understandable
codebase remains clean for future growth
TECHNICAL REQUIREMENTS

Use modern Laravel conventions.

Use Filament for:

stats widgets
chart widgets
table widgets
custom reporting pages

Use summary tables for:

daily totals
weekly rollups
category performance
product performance

Use sales_records as the source of truth.

Use proper decimal handling for money.

Use integer quantities for this MVP.

Use transactions for grouped summary rebuild operations where needed.

Prefer Eloquent / query builder for maintainability.
If raw SQL is used for aggregation, keep it minimal, parameterized, and well-contained.

REPORTING RULES
A report must never silently disagree with source sales data.
Summary tables must be rebuildable from source records.
Daily totals should be traceable to raw sales records.
Weekly summaries should be derived from daily summaries where practical.
Low-stock report should derive from current stock and reorder level.
Out-of-stock report should derive from current stock only.
Exported reports should respect selected filters where practical.
Date filtering must be explicit and validated.
Report pages should remain useful even with moderate growth in data volume.
PERFORMANCE EXPECTATIONS

Be production-minded.

Do:

add indexes on date-heavy reporting columns
paginate large tables
use summary tables for repeated aggregations
eager load relationships where needed
avoid obvious N+1 issues
keep dashboard queries modest
keep charts backed by efficient grouped queries

Do not:

overbuild a full warehouse
add unnecessary caching everywhere
put expensive report queries on every request if summaries can handle them better
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

List new summary/reporting tables and core columns.

C. Reporting workflow summary

Explain:

how summaries are built
how daily reports work
how weekly summaries work
how low-stock reporting works
how exports work
D. Verification checklist

Include:

migrations work
summaries build correctly
daily report is accurate
weekly summary is accurate
charts load
low-stock report works
out-of-stock report works
exports work
tests pass
E. Next step recommendation

Recommend the Stage 5 path.

ACCEPTANCE CRITERIA FOR STAGE 4

This phase is complete only if all of these are true:

reporting summary tables exist and are functional
summary rebuild logic works safely
daily report page works
weekly summary page works
sales trend charts work
low-stock report works
out-of-stock report works
top products reporting works
category performance reporting works
report exports work where implemented
dashboard reporting widgets are useful and accurate
authorization remains clean and secure
tests protect the important reporting logic
the codebase remains clean and extendable
EXPLICIT CONSTRAINTS

Do not implement yet:

supplier analytics
accounting module
payroll
barcode system
customer storefront
multi-branch analytics
advanced forecasting
AI recommendations
mobile app

Stay focused on practical supermarket reporting.

FINAL INSTRUCTION

Now implement Stage 4 in atomic steps, with production-minded quality, strong reporting architecture, clean Filament UX, secure XLSX/CSV exports, accurate aggregations, and fast read performance.

Build it so the supermarket owner can confidently answer:

What did I sell today?
How much did I sell this week?
Which products are selling best?
Which categories are performing best?
What do I need to restock now?