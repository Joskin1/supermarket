MVP 2 — Barcode-Based Stock Intake System
Objective

Allow supermarket staff to:

scan new products
fetch product details automatically
add stock quickly
reduce manual typing massively
Core Workflow
Scan barcode
→ Check local DB
→ If exists:
     Add stock
→ Else:
     Fetch product details from API
→ Confirm product
→ Save product
→ Add stock quantity
Recommended APIs
Primary API
UPCitemdb

Reason:

large database
practical supermarket coverage
easy API
packaged goods support
cosmetics support
beverages support
Secondary API
Open Food Facts

Reason:

food products
nutrition products
grocery support
Final Lookup Strategy
Step 1

Always check:

local database first
Step 2

If not found:
call external API.

Step 3

Cache successful results locally.

Never repeatedly call API for same barcode.

Product Table Structure
Required Fields
field	purpose
barcode	scanned identifier
sku	internal supermarket code
product_name	product name
category_id	local category
selling_price	selling price
cost_price	buying price
stock_quantity	available stock
Stock Intake Flow
Existing Product
Steps
Scan barcode
Product found locally
Ask:
quantity received
optional updated prices
Save stock movement
Increase stock
New Product
Steps
Scan barcode
No local match
API lookup runs
Prefill:
product name
brand
barcode
suggested category
User confirms/edit
User enters prices
Save product
Add stock quantity
Product Not Found Anywhere
Steps
Scan barcode
No API match
Open manual product form
Barcode prefilled
User completes details
Save product
Best Filament Architecture
Use:
Custom Filament Page

Reason:

workflow-based
multiple states
dynamic scanning
conditional UI
better UX
Recommended Page
ReceiveStockPage
Atomic Tasks — MVP 2
Phase 1 — Barcode Infrastructure
Tasks
add barcode support
barcode validation
barcode indexing
Phase 2 — Local Product Lookup
Tasks
barcode search service
local-first lookup logic
instant product detection
Phase 3 — External API Integration
Tasks
UPCitemdb integration
Open Food Facts fallback
response normalization
API error handling
caching
Phase 4 — Receive Stock Workflow
Tasks
barcode scanner input
existing product flow
new product flow
manual fallback flow
Phase 5 — Stock Management
Tasks
stock movement logging
quantity increment logic
audit logging
transactional updates
Phase 6 — Security & Scalability
Tasks
API rate limiting
queue-ready architecture
database indexing
activity logs
exception monitoring
Final Recommendation
Build Order
FIRST

Build:

offline Excel barcode sales workflow

Reason:

immediate operational value
simplest deployment
no cashier retraining
internet-independent
SECOND

Build:

barcode-assisted stock intake system

Reason:

removes massive manual setup work
speeds up onboarding products
scalable into future POS system
Production-Minded Conclusion

This architecture gives you:

offline-first operations
barcode scanning support
minimal typing
scalable inventory foundation
future POS readiness
fast MVP delivery
low operational complexity
realistic supermarket workflow