You are my senior Laravel + Filament engineer.

I need you to redesign the Daily Sales Excel workflow in my supermarket system and implement the changes cleanly, safely, and step by step.

Work like a careful senior engineer:
- think before changing code
- inspect the current implementation first
- do not guess route names, page methods, or existing architecture
- keep changes production-minded
- keep the code scalable and testable
- do not move to the next task until the current task is finished and verified

Very important:
All work must be done in ATOMIC TASKS.
Each task must be completed, verified, and summarized before moving to the next one.

==================================================
PROJECT CONTEXT
==================================================

Stack:
- Laravel
- Filament
- MySQL
- Docker
- Laravel Excel / PhpSpreadsheet if already in use

Current supermarket workflow already includes:
- categories
- products
- stock entries
- current stock tracking
- sales import batches
- daily sales import/export
- sales records
- reporting

==================================================
THE PROBLEM TO FIX
==================================================

The current daily sales export/import workflow is not practical for real supermarket operations.

Right now, the exported sales template is effectively encouraging a “one row per product for the whole day” style.

That means if:
- a customer buys 2 units of a product
- and later another customer buys the same product again

the staff would have to go back to the same row and edit:
- quantity sold
- total amount
- maybe other values

This is NOT good for real daily transaction entry.

It is error-prone, confusing, and not audit-friendly.

==================================================
NEW WORKFLOW WE HAVE AGREED ON
==================================================

We are changing the sales file to a SALES LOG approach.

Hard business rule:
- every sale entry gets its own row
- repeated sale of the same product must create a NEW ROW
- staff must NEVER go back and overwrite a previous row for the same product

Example:
If Zara Gold Perfume is sold 3 different times in a day, it should appear on 3 separate rows.

This is the new workflow and must be implemented exactly.

==================================================
NEW EXPORT STRUCTURE
==================================================

The exported workbook should now use TWO SHEETS.

--------------------------------------------------
SHEET 1: Product Reference
--------------------------------------------------

Purpose:
A reference sheet for staff to see available products and codes.

Required columns:
- product_code
- category
- product_name
- unit_price

Rules:
- include active products only
- this sheet is for reference, not transaction entry
- format it clearly
- make it easy to read
- if possible, freeze the header row
- if sensible, make columns auto-width or readable
- if easy and stable, protect this sheet from accidental edits

--------------------------------------------------
SHEET 2: Sales Entry Log
--------------------------------------------------

Purpose:
This is the actual sheet staff will use throughout the day.

Core rule:
- one sale = one row
- repeated sale of same product = another row
- do not aggregate daily quantity manually in the file

Required columns for the sales log:
- date
- time
- product_code
- product_name
- unit_price
- quantity_sold
- total_amount
- note

Business meaning:
- date = date of that sale entry
- time = time of that sale entry
- product_code = main identifier
- product_name = display/reference value
- unit_price = actual selling price used for that entry
- quantity_sold = quantity sold in that specific transaction row
- total_amount = unit_price × quantity_sold
- note = optional

==================================================
IMPORTANT DESIGN DECISIONS
==================================================

1. product_code is the trusted identifier
The system must rely primarily on product_code, not product_name.

2. product_name is informational
It can be auto-filled, lookup-filled, or validated lightly, but product_code is the authoritative key.

3. unit_price should remain editable
Because the actual selling price may differ in real life.
Do NOT make it hard-locked to the current system price.
However, it may be prefilled from the product reference/default product selling price.

4. total_amount should be derived from unit_price × quantity_sold
If formula support is stable, populate it automatically in the workbook.
If formula support is not reliable enough, calculate and validate it during import.

5. category is NOT required in the sales-entry sheet
It already exists in the product reference sheet.
Do not add unnecessary duplication to the entry sheet unless there is a very strong reason.

==================================================
PREFERRED UX IN THE EXCEL FILE
==================================================

If it can be implemented robustly with the current export stack, do this:

For Sheet 2 (Sales Entry Log):
- allow staff to enter product_code
- automatically show/fill product_name from the reference sheet
- optionally prefill unit_price from the reference sheet, but still allow edits
- calculate total_amount automatically from unit_price × quantity_sold

Use stable spreadsheet techniques only.
Do NOT introduce fragile workbook behavior.

If dropdowns or formulas become too brittle, the fallback is:
- keep the workbook simple
- keep the reference sheet accurate
- rely on strong import validation

But the primary goal must still be achieved:
- one sale per row
- repeat sales on separate rows
- product_code-driven import

==================================================
IMPORT LOGIC CHANGES REQUIRED
==================================================

The import logic must be updated to support the new sales-log structure.

New hard rules:

1. The import must process EACH SALES ROW independently.
2. The same product_code may appear multiple times in the same uploaded file.
3. That is valid and expected.
4. Blank rows should be skipped cleanly, without creating failures.
5. Partially filled invalid rows should fail with clear reasons.
6. Import must still create one tracked batch per uploaded file.
7. Duplicate file protection must remain active.

==================================================
CRITICAL STOCK DEDUCTION RULE
==================================================

This is extremely important.

If the same product appears multiple times in one uploaded file, stock deduction must respect row order and cumulative effect.

Example 1:
- current stock = 10
- row 1 = quantity 3
- row 2 = quantity 2
Both should pass.
Final stock = 5.

Example 2:
- current stock = 4
- row 1 = quantity 3
- row 2 = quantity 2
Row 1 should pass.
Row 2 should fail because remaining stock after row 1 is only 1.

This means the importer must not validate every row only against the original database stock.
It must process rows in order and maintain running stock state safely.

Implement this carefully and transactionally.

==================================================
DATABASE / DOMAIN CHANGES TO CONSIDER
==================================================

Review the current schema and update it only where necessary.

I want the cleanest design.

Likely good changes include:

1. Keep sales_date as DATE only
Because this represents the day of the sale.

2. Add sales_time as nullable TIME if not already present
Because we now want one row per transaction entry and time matters.

3. Consider adding source_row_number if useful
This can help trace imported rows back to the uploaded file order.

Do not make random schema changes.
Inspect current migrations and models first, then decide the cleanest update.

==================================================
FILAMENT / UI CHANGES REQUIRED
==================================================

Update the Daily Sales Export page so the UI matches the new workflow.

The page must no longer imply:
- one row per product
- edit quantity repeatedly on the same row

Instead, it should clearly explain:
1. download workbook
2. use Product Reference sheet for guidance
3. enter every sale on a new row in Sales Entry Log
4. upload completed workbook
5. system validates rows, stores valid rows, and flags invalid ones

Also:
- inspect the current page class before editing the Blade
- do not call methods in Blade that do not exist in the page class
- if helper methods like getDownloadUrl() or getUploadUrl() are needed, define them properly in the page class
- verify actual route names before using them

Make the page clean, readable, and professional in Filament.

==================================================
ATOMIC TASK PLAN
==================================================

Follow this exact order unless there is a strong technical reason not to.

--------------------------------------------------
TASK 1 — Review Current Implementation
--------------------------------------------------

Goal:
Understand exactly how the current export/import workflow works before editing anything.

Do:
- inspect the Daily Sales Export page class
- inspect the Daily Sales Export Blade view
- inspect export classes
- inspect import classes
- inspect sales batch processing logic
- inspect sales_records schema and model casts
- inspect route names used for export/upload
- inspect current tests covering sales workflow

Output:
Before changing code, clearly state:
- how the current system works
- where the “one row per product” behavior is enforced or implied
- what parts must change
- what parts can remain

Do not proceed until this review is complete.

--------------------------------------------------
TASK 2 — Redesign the Export Workbook Structure
--------------------------------------------------

Goal:
Change the export from the old structure to the new 2-sheet workbook.

Implement:
- Sheet 1: Product Reference
- Sheet 2: Sales Entry Log

Requirements:
- Product Reference must include active products only
- Sales Entry Log must support one sale per row
- use the exact new sales-entry columns agreed above
- make workbook output stable and readable

If formulas / lookup helpers are added:
- keep them simple and reliable
- do not break compatibility for common Excel use

Verify:
- exported workbook has two sheets
- headers are correct
- sheet names are clean and clear
- content matches business intent

Do not move on until export structure is correct.

--------------------------------------------------
TASK 3 — Update Import Validation for the New Sales Log
--------------------------------------------------

Goal:
Make the importer understand and validate the new row-per-sale format.

Requirements:
- accept multiple rows for the same product_code in one file
- skip fully blank rows
- fail partial invalid rows with clear reasons
- validate:
  - date
  - time if present
  - product_code exists
  - quantity_sold > 0
  - unit_price is numeric
- do not rely on product_name as the authority

Decide clearly how total_amount will be handled:
- if present, verify it or normalize it
- if missing, compute it from unit_price × quantity_sold

Output:
Explain the exact validation rules you chose and why.

Verify:
- importer accepts repeated product rows
- importer skips blank template rows
- invalid rows are recorded cleanly

--------------------------------------------------
TASK 4 — Fix Stock Deduction Logic for Repeated Product Rows
--------------------------------------------------

Goal:
Make stock deduction correct when the same product appears many times in the same file.

Requirements:
- process rows in order
- maintain running stock effect within the batch
- do not validate each row only against original stock in DB
- use transactions where appropriate
- do not allow silent negative stock
- if later row exceeds remaining stock after earlier valid rows, fail that later row only

This task is critical.
Handle it like a senior engineer.

Verify using examples like:
- stock 10 with rows 3 + 2 = both pass
- stock 4 with rows 3 + 2 = first passes, second fails

Do not move on until this logic is correct and tested.

--------------------------------------------------
TASK 5 — Update Sales Record Persistence
--------------------------------------------------

Goal:
Store the new row-per-sale structure cleanly.

Inspect whether the database/model needs changes.
If needed, implement minimal clean updates such as:
- sales_time
- source_row_number

Requirements:
- preserve historical accuracy
- keep product_code snapshot / product_name snapshot if already used
- keep sales_date as date-only if that is the domain meaning
- avoid mixing date and datetime incorrectly

Verify:
- persisted records match business meaning
- model casts are correct
- database assertions are stable

--------------------------------------------------
TASK 6 — Update the Filament Daily Sales Export Page
--------------------------------------------------

Goal:
Make the UI explain the new workflow properly.

Requirements:
- explain the 2-sheet workbook clearly
- explain that every sale must be entered on a new row
- remove wording that implies editing the same row repeatedly
- keep page professional and easy to understand
- verify any Blade helper methods actually exist in the page class
- verify route names before use
- keep code clean

Verify:
- page loads without errors
- buttons/routes work
- workflow explanation matches the new behavior

--------------------------------------------------
TASK 7 — Update Tests
--------------------------------------------------

Goal:
Update or add tests so the new workflow is protected.

Add or update tests for:
1. export produces a 2-sheet workbook
2. product reference sheet contains active products only
3. sales entry sheet has the new columns
4. same product can appear multiple times in one import file
5. repeated product rows are processed correctly
6. running stock validation works across repeated rows in the same batch
7. blank rows are skipped cleanly
8. invalid partial rows create failures
9. import batch totals remain correct
10. duplicate upload protection still works
11. UI/page behavior still loads correctly if covered

Use meaningful assertions.
Do not weaken tests just to make them pass.

--------------------------------------------------
TASK 8 — Final QA and Cleanup
--------------------------------------------------

Goal:
Finish with a production-minded cleanup pass.

Do:
- review naming
- review route usage
- review page methods
- review workbook sheet names and headings
- review import error messages
- review model casts and migrations
- review transaction boundaries
- remove dead code
- update docs/comments if needed

Then rerun the relevant test suite.

==================================================
IMPORTANT IMPLEMENTATION RULES
==================================================

1. Do not change everything at once blindly.
2. Complete and verify each task before moving forward.
3. Do not break other working sales features.
4. Do not remove duplicate file protection.
5. Do not remove batch tracking.
6. Do not remove failure tracking.
7. Do not keep any UI wording that suggests “edit the same row again later.”
8. Keep code maintainable and production-minded.

==================================================
EXPECTED OUTPUT FORMAT
==================================================

For each atomic task:
1. state the task name
2. explain the goal
3. inspect current code first
4. make the change
5. show key files changed
6. explain why the change is correct
7. verify the task before moving on

At the end provide:

A. Root summary
- what changed in export
- what changed in import
- what changed in stock deduction
- what changed in UI

B. Files changed
List all changed files.

C. Verification summary
Show:
- export verified
- import verified
- repeated product rows verified
- running stock logic verified
- tests passing

D. Any migration notes
If schema changed, explain exactly what changed and why.

==================================================
FINAL INSTRUCTION
==================================================

Implement this redesign exactly as concluded:

- use a 2-sheet workbook
- use Product Reference sheet
- use Sales Entry Log sheet
- one sale = one row
- repeated sale of same product = another new row
- product_code is authoritative
- unit_price remains editable
- total_amount is derived from unit_price × quantity_sold
- import must process repeated product rows correctly and safely
- stock deduction must respect cumulative row order within the batch
