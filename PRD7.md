MVP 1 — Offline Excel Barcode Sales System
Objective

Build the offline-first Excel sales workflow with barcode support.

This is the first MVP because:

it solves the supermarket’s biggest operational problem immediately
works without internet
fastest to deploy
easiest for staff to learn
scalable later into live POS
Core Workflow
Export Excel
→ Work offline all day
→ Scan barcode into Excel
→ Product auto-populates
→ Enter quantity
→ Total auto-calculates
→ Upload completed file
→ Laravel imports sales
→ Stock reduces automatically
Final Excel Structure
Sheet 1 — Product Reference
Purpose

Acts as the lookup database inside Excel.

Columns
barcode	sku	category	product_name	unit_price
Sheet 2 — Daily Sales Entry
Columns
date	time	barcode	sku	product_name	unit_price	quantity_sold	total_amount	note
Excel Workflow
Scenario 1 — Barcode Scan
Step 1

Cashier clicks:

barcode column
Step 2

Cashier scans product.

Scanner types:

6151031287645
Step 3

Excel auto-fills:

SKU
product name
unit price

using formulas.

Step 4

Cashier enters:

quantity_sold
Step 5

Excel auto-calculates:

total_amount
Scenario 2 — Manual SKU Entry

If barcode damaged:

cashier types SKU manually
same auto-fill happens
Excel Formula Logic
Lookup Priority
Formula logic
IF barcode exists
→ lookup by barcode

ELSE
→ lookup by SKU
Formula Targets

Auto-fill:

SKU
product_name
unit_price

Auto-calculate:

total_amount
Recommended Excel Technology
Use:
XLOOKUP
fallback INDEX/MATCH

Reason:

modern
faster
cleaner formulas
Barcode Scanner Requirements
Hardware

Use:

USB barcode scanner
OR
Bluetooth barcode scanner

Must support:

HID keyboard mode
keyboard wedge mode
Recommended Scanner Behavior

Configure scanner to:

append ENTER after scan

This allows:

scan
move automatically to next field/row

Faster cashier workflow.

Excel Protection Rules
Editable Columns ONLY

Cashier can edit:

barcode
sku
quantity_sold
note
Protected Columns

Protect:

formulas
product_name
unit_price
total_amount
Import Workflow
Upload Process
Steps
Upload completed Excel file
Laravel validates rows
Valid rows imported
Failed rows separated
Stock reduced safely
Import report generated
Import Validation
Required
barcode OR sku
quantity > 0
Validation Checks
product exists
stock available
row not corrupted
no duplicate import
Database Actions
On Successful Import
Create:
sales record
import batch record
audit log
Update:
stock quantity
Atomic Tasks — MVP 1
Phase 1 — Database
Tasks
add barcode field to products
add barcode unique index
add import batch table
add imported sales table
Phase 2 — Excel Export
Tasks
generate Product Reference sheet
generate Daily Sales sheet
add formulas
protect formula columns
auto-format workbook
Phase 3 — Barcode Logic
Tasks
barcode lookup formulas
SKU fallback formulas
total auto-calculation
date defaults
time shortcut instructions
Phase 4 — Import Engine
Tasks
validate rows
process imports safely
reduce stock
track failed rows
generate import summary
Phase 5 — Security
Tasks
file validation
transaction-safe imports
duplicate upload prevention
audit logs