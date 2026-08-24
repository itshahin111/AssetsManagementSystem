# AI PROJECT MEMORY — School Asset & Inventory Management System

> PURPOSE: This file is the persistent source of truth for AI coding agents working on this project.
> IMPORTANT: Read this file before making changes. Do not assume project state from chat history.

---

## 1. PROJECT IDENTITY

Project: School Asset & Inventory Management System

Goal:
Build a production-oriented asset and inventory management system for a school.

Primary concerns:
- Buildings, floors, rooms and location hierarchy
- Asset categories and asset types
- Individual and quantity-tracked assets
- Asset location history
- Asset transfers
- Maintenance
- Staff assignments
- Vendors and purchases
- Attachments
- Audit logs
- RBAC
- Dashboard and reports
- Future QR-code integration

---

## 2. AUTHORITATIVE ARCHITECTURE

### Frontend
Vue 3 SPA
- Vite
- Pinia
- Vue Router
- Tailwind CSS
- Axios

### Backend
Laravel API
- API prefix: /api/v1
- Sanctum authentication
- Thin Controllers
- Form Requests for validation
- Services for business logic
- Policies for authorization
- Events/Listeners for audit and future notifications
- DTOs where useful
- Eloquent Models
- Selective Repository pattern

### Database
MySQL 8
- normalized schema
- foreign keys
- indexes
- transactions
- InnoDB
- utf8mb4

### Authorization
- Spatie Permission for roles/permissions
- Laravel Policies for record-level authorization and scope rules

### API responses
Use API Resources for all responses.
Never return raw Eloquent models directly from API endpoints.

Standard response:
{
  "success": true,
  "data": {},
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 134
  },
  "message": null
}

Errors:
{
  "success": false,
  "errors": {}
}

Relevant HTTP statuses:
- 422 validation
- 403 authorization
- 404 not found
- 409 business conflict

---

## 3. NON-NEGOTIABLE ARCHITECTURE RULES

1. Controllers must remain thin.
2. Business logic belongs in Services.
3. Use Form Requests for request validation.
4. Use Policies for record-level authorization.
5. Use API Resources for API output.
6. Use DB transactions for multi-step mutations.
7. Do not introduce repositories everywhere.
8. Repository pattern is primarily intended for complex Asset search/report queries.
9. Preserve existing architecture before proposing a new architecture.
10. Reuse existing models, services, components and utilities.
11. Do not create duplicate functionality.
12. Do not rewrite unrelated working code.
13. Do not change database structure without checking existing migrations and relationships.
14. Do not change API contracts casually.
15. Never bypass authorization because the frontend hides a button.
16. API authorization is the real security boundary.
17. Audit-sensitive mutations must remain traceable.
18. Transfer and other historical mutations must preserve history.
19. Use PHP backed enums for defined state machines where the project already follows this design.
20. Store UTC timestamps in the database and convert for display.
21. Never expose private attachment files directly from public/.
22. Ask for clarification instead of guessing when requirements conflict with the architecture.

---

## 4. SECURITY RULES

Authentication:
- Sanctum
- All API endpoints require auth except login.

Authorization:
- Spatie Permission handles permission storage/checks.
- Laravel Policies handle record-specific rules.

File uploads:
- Store attachments outside public/.
- Validate MIME type, extension and size server-side.
- Never trust client-provided filenames.
- Serve private files through permission-checked/signed routes.

Audit:
- Mutations should be auditable.
- Audit logs are append-only.
- Never silently remove historical audit information.

---

## 5. CORE DOMAIN MODEL

### Location hierarchy

Building
  -> Floor
      -> RoomType
          -> Room

Key relationships:
- Building hasMany Floors
- Floor belongsTo Building
- Floor hasMany Rooms
- Room belongsTo Building
- Room belongsTo Floor
- Room belongsTo RoomType

Integrity rule:
A Room's floor must belong to the same building referenced by the Room.

---

### Asset taxonomy

AssetCategory
  -> AssetType
      -> Asset

AssetType has:
- tracking_type = individual | quantity

Individual asset rules:
- quantity is normally 1
- asset_tag is required
- serial_number may be used

Quantity asset rules:
- quantity can be N
- conditional validation must prevent invalid serialized/quantity combinations

---

### Asset location

Asset.current_location_id is a denormalized pointer for fast reads.

The source of truth is asset_locations where:
- is_current = true

Invariant:
Exactly one current location row should exist for an asset at any time.

Because MySQL does not provide the required partial unique index behavior for this design, enforce the invariant inside transactional service logic.

---

## 6. CRITICAL TRANSACTIONAL RULE — ASSET TRANSFER

Asset transfer is a core transactional operation.

Expected flow:

POST /api/v1/asset-transfers

1. Validate request.
2. Validate source equals current location.
3. Validate quantity <= available quantity.
4. Authorize using AssetTransferPolicy.
5. Start DB transaction.
6. Lock current asset_locations row using SELECT ... FOR UPDATE / lockForUpdate().
7. Re-check available quantity.
8. Close current location when full quantity moves.
9. For partial transfer, decrement source quantity.
10. Create new destination asset_locations row.
11. Update assets.current_location_id.
12. Create asset_transfers ledger record.
13. Fire AssetTransferred event.
14. Audit listener writes audit_logs.
15. Commit transaction.
16. Return AssetTransferResource.

Concurrent transfers must not corrupt history.
Second conflicting request should fail with an appropriate conflict response such as 409.

---

## 7. DATABASE DOMAIN TABLES

Core tables:
- buildings
- floors
- room_types
- rooms
- asset_categories
- asset_types
- assets
- asset_locations
- asset_transfers
- maintenance_records
- asset_assignments
- vendors
- purchases
- purchase_items
- asset_attachments
- audit_logs
- users

Permission tables come from Spatie Permission migrations.

Important fields/rules:

### buildings
- name
- code unique
- description
- status active/inactive
- sort_order
- soft deletes

### floors
- building_id
- name
- level
- sort_order
- status
- unique building_id + name
- soft deletes

### rooms
- building_id
- floor_id
- room_type_id nullable
- name
- room_number nullable
- code unique
- capacity nullable
- status
- soft deletes
- building/floor consistency required

### asset_categories
- name unique
- code unique
- description
- status
- soft deletes

### asset_types
- asset_category_id
- name
- code unique
- tracking_type individual/quantity
- description
- status
- soft deletes

### assets
- asset_type_id
- asset_tag unique nullable
- serial_number unique nullable
- name
- brand
- model
- quantity
- unit
- purchase_date
- purchase_price
- warranty_expiry
- condition
- status
- description
- current_location_id nullable
- created_by
- updated_by nullable
- soft deletes

Asset statuses:
active
in_storage
under_maintenance
damaged
lost
retired
disposed

Asset conditions:
new
good
fair
poor
damaged

### asset_locations
- asset_id
- building_id
- floor_id
- room_id nullable
- quantity
- started_at
- ended_at nullable
- is_current
- created_by
- index asset_id + is_current

### asset_transfers
Append-only ledger.
Do not update/delete historical transfer records.

Contains:
- asset_id
- from building/floor/room
- to building/floor/room
- quantity
- reason
- reference
- transferred_by
- transferred_at
- notes

### maintenance_records
- asset_id
- vendor_id nullable
- issue
- description
- reported_at
- started_at
- completed_at
- cost
- status
- resolution
- notes
- created_by
- soft deletes

Maintenance statuses:
reported
in_progress
completed
cancelled

### asset_assignments
- asset_id
- user_id
- assigned_by
- assigned_at
- returned_at
- status
- notes
- soft deletes

Assignment statuses:
active
returned
lost

### vendors
- name
- contact_person
- phone
- email
- address
- notes
- status
- soft deletes

### purchases
- purchase_number unique
- vendor_id
- purchase_date
- invoice_number
- subtotal
- tax
- discount
- total
- status
- notes
- created_by
- soft deletes

Purchase statuses:
draft
ordered
received
cancelled

### purchase_items
- purchase_id
- asset_type_id
- quantity
- unit_price
- total

### asset_attachments
- asset_id
- type
- original_name
- path
- mime_type
- size
- uploaded_by
- soft deletes

Attachment types:
invoice
warranty
purchase_doc
repair_doc
photo
other

### audit_logs
Append-only.
- user_id nullable
- action
- model_type
- model_id
- old_values
- new_values
- ip_address
- user_agent
- created_at

No updated_at.

---

## 8. MODEL RELATIONSHIP RULES

Asset:
- belongsTo AssetType
- belongsTo current AssetLocation
- hasMany AssetLocation
- hasMany AssetTransfer
- hasMany MaintenanceRecord
- hasMany AssetAssignment
- hasMany AssetAttachment
- belongsTo User as createdBy
- belongsTo User as updatedBy
- audit relationship

AssetLocation:
- belongsTo Asset
- belongsTo Building
- belongsTo Floor
- belongsTo Room nullable
- belongsTo User as createdBy
- current scope where is_current = true

AssetTransfer:
- belongsTo Asset
- from/to Building
- from/to Floor
- from/to Room
- belongsTo User as transferredBy

User:
- uses Spatie HasRoles
- hasMany created assets
- hasMany assignments
- hasMany audit logs

Use SoftDeletes and HasFactory where defined by the architecture.

---

## 9. API STRUCTURE

Base:
 /api/v1

Auth:
POST /auth/login
POST /auth/logout
GET /auth/me

Location:
- /buildings
- /floors
- /room-types
- /rooms

Asset taxonomy:
- /asset-categories
- /asset-types

Assets:
- /assets
- /assets/{id}
- /assets/{id}/locations
- /assets/{id}/audit-log

Transfers:
- /asset-transfers
- /asset-transfers/{id}

Maintenance:
- /maintenance-records

Assignments:
- /asset-assignments
- /asset-assignments/{id}/return

Vendors:
- /vendors

Purchases:
- /purchases
- /purchases/{id}

Attachments:
- /assets/{id}/attachments
- /attachments/{id}

Reports:
- /reports/inventory
- /reports/inventory/building/{id}
- /reports/inventory/floor/{id}
- /reports/inventory/room/{id}
- /reports/inventory/category/{id}
- /reports/status
- /reports/condition
- /reports/movement
- /reports/maintenance
- /reports/lost
- /reports/disposed
- /reports/warranty-expiry
- /reports/{type}/export?format=csv|xlsx|pdf

Audit:
- /audit-logs

Dashboard:
- /dashboard/summary
- /dashboard/charts

Users/Roles:
- /users
- /roles
- /permissions

Always inspect the existing route implementation before adding a route.

---

## 10. FRONTEND ARCHITECTURE

Vue structure:

src/
- api/
- stores/
- router/
- layouts/
- components/
- pages/
- composables/
- utils/

API modules:
- http.js
- buildings.js
- floors.js
- rooms.js
- assets.js
- transfers.js
- maintenance.js
- assignments.js
- vendors.js
- purchases.js
- reports.js
- auditLogs.js
- users.js
- roles.js

Pinia:
- auth
- ui
- buildings
- rooms
- assets
- transfers

Router:
- route table
- auth guard
- permission guard

Layouts:
- AdminLayout.vue
- AuthLayout.vue

Reusable components:
- DataTable
- Modal
- ConfirmDialog
- Toast
- Pagination
- FilterBar
- EmptyState
- AssetForm
- AssetTable
- AssetLocationBadge
- TransferForm
- LocationPicker
- BuildingTree
- FloorPanel
- RoomAssetList

Important frontend rules:
- LocationPicker must support Building -> Floor -> Room cascading selection.
- DataTable should remain reusable.
- Server-side pagination/filter/sort should be used for large lists.
- Pinia stores state that must survive navigation.
- Local component state should be preferred for state that does not need persistence.
- UI permission checks improve UX but never replace backend authorization.

---

## 11. ROLES AND PERMISSIONS

Roles:
- Super Admin
- Admin
- Inventory Manager
- IT Manager
- Staff
- Viewer

Permission groups:
- buildings.*
- floors.*
- rooms.*
- assets.view
- assets.create
- assets.update
- assets.delete
- assets.transfer
- maintenance.*
- reports.view
- users.manage
- roles.manage
- audit.view

Important scoped rules:
- IT Manager can update only IT-category asset types through policy.
- Staff can view/return only their own asset assignments.
- Policy rules must not be implemented only in frontend code.

---

## 12. DEVELOPMENT PHASES

Phase 0:
- Laravel + Vue scaffolding
- Sanctum
- Spatie
- base configuration
- CI skeleton

Phase 1:
- Building
- Floor
- RoomType
- Room
- migrations
- models
- policies
- Form Requests
- API Resources
- controllers
- seeders
- tests
- Vue Explorer and CRUD

Phase 2:
- AssetCategory
- AssetType
- tracking_type
- CRUD
- seeders
- Vue forms

Phase 3:
- Asset
- AssetLocation
- initial placement
- search/filter
- Asset List
- Create/Edit
- Detail Overview

Phase 4:
- Asset Transfer
- transaction-safe movement history
- Transfer UI
- Movement History

Phase 5:
- Maintenance
- Asset Assignments

Phase 6:
- Vendors
- Purchases
- Purchase Items

Phase 7:
- Attachments
- secure upload/download
- Documents tab

Phase 8:
- Audit logging
- Auditable trait
- observer
- listeners
- audit viewer

Phase 9:
- RBAC hardening
- User/Role UI

Phase 10:
- Dashboard
- charts
- global search

Phase 11:
- Reporting
- CSV/Excel/PDF

Phase 12:
- QR code hook points

Phase 13:
- tests
- OpenAPI documentation
- performance
- N+1 audit
- security review

Each phase should include:
- migrations
- models
- policies
- Form Requests
- Resources
- controllers
- tests
- seeders
- Vue pages
- verification checklist

Do not skip ahead without checking dependencies.

---

## 13. KNOWN RISKS AND REQUIRED MITIGATIONS

### Current location uniqueness
Use transaction + row lock.

### Room/building mismatch
Validate floor belongs to selected building.

### Partial quantity transfer
If full quantity moves, close source location.
If partial, decrement source and create destination location row.

### Concurrent transfer
Use lockForUpdate inside transaction.

### Structural delete
Do not delete locations containing active assets without reassignment/force workflow.

### Large asset list
Use indexes, eager loading, pagination and filtering.
Consider full-text search if LIKE becomes slow.

### File upload security
Private storage + MIME/extension/size validation + signed/authorized download.

### Audit log growth
Append-only indexed log. Consider archival/partitioning at large scale.

### Timezone
Store UTC; convert for school timezone at presentation layer.

### Quantity-based damaged/lost/retired
Requires quantity splitting rather than applying a full-batch status blindly.

---

## 14. DESIGN RECOMMENDATIONS TO PRESERVE

1. Quantity-splitting for status changes should be treated as a first-class design concern.
2. Consider idempotency keys for transfer/assignment POST requests.
3. Cache rarely changing lookup data.
4. Consider explicit location status for in-transit/unassigned intake.
5. Keep API versioning from day one.
6. Keep OpenAPI spec versioned with the project.
7. Consider adding nullable QR payload/generated timestamp fields early if approved.
8. Keep event names stable for future notifications.
9. Queue large exports when data volume requires it.

Do not implement recommendations automatically unless they are part of the current approved task.

---

## 15. CURRENT PROJECT STATE

The source architecture document is a DESIGN document. It states that implementation code was not included and that approval was expected before Phase 0/1 implementation.

Therefore:
- Treat architecture as approved design intent only.
- Do not claim a feature is implemented merely because it exists in this memory.
- Before coding, inspect the actual repository.
- The actual repository state always takes precedence for implementation status.
- If repository code conflicts with this architecture, report the conflict before making broad changes.

Current implementation status:
UNKNOWN — must be inspected from the repository.

---

## 16. AI TAKEOVER PROTOCOL

Every new AI agent must follow this sequence.

### STEP 1 — Read
Read:
- docs/AI_MEMORY.md
- docs/PROJECT_CONTEXT.md if present
- docs/ARCHITECTURE.md if present
- docs/AI_RULES.md if present
- docs/TODO.md if present
- docs/CHANGELOG.md if present

### STEP 2 — Inspect
Inspect:
- git status
- current branch
- project structure
- relevant routes
- relevant migrations
- relevant models
- relevant services
- relevant controllers
- relevant Vue components
- relevant tests

### STEP 3 — Report
Before editing, report:
1. current implementation state
2. relevant files
3. architecture being followed
4. missing pieces
5. proposed changes

### STEP 4 — Implement
Only implement the requested task.
Do not refactor unrelated code.

### STEP 5 — Verify
Run appropriate:
- backend tests
- frontend tests if present
- npm build
- static/lint checks if present
- Laravel checks relevant to the task

### STEP 6 — Document
Update:
- TODO.md
- CHANGELOG.md
- PROJECT_CONTEXT.md when project state materially changes

### STEP 7 — Git
Show:
- git diff summary
- files changed
- suggested commit message

Never commit/push unless explicitly requested by the user.

---

## 17. AI BEHAVIOR RULES

The AI must:
- inspect before editing
- preserve architecture
- make minimal changes
- explain conflicts
- verify changes
- keep documentation current
- avoid duplicate implementations
- protect historical records
- preserve authorization
- preserve API contracts
- preserve database integrity

The AI must not:
- blindly rewrite the project
- delete working features
- invent missing requirements
- bypass tests
- expose private files
- put secrets in source control
- modify unrelated files
- replace architecture with personal preference
- assume the design document equals the current implementation

---

## 18. TAKEOVER PROMPT

Use this prompt when switching from Codex to Cline/Gemini/another AI:

"Take over this existing School Asset & Inventory Management System.

First read docs/AI_MEMORY.md and any project documentation under docs/.

Do not modify files yet.

Inspect the actual repository and Git state.

Determine:
- what is implemented
- what is incomplete
- where the previous agent stopped
- which TODO item is next
- which files are relevant
- whether the repository matches the architecture memory

If there is a mismatch between documentation and actual code, report it before changing architecture.

Then propose the smallest safe implementation plan for the next task.

Do not start coding until the current state is understood."

---

## 19. HANDOFF FORMAT

When finishing a task, the AI should leave this information:

### Completed
- ...

### Files changed
- ...

### Tests/checks
- ...

### Known issues
- ...

### TODO updated
- ...

### Next recommended task
- ...

### Git commit suggestion
feat: ...

---

## 20. SOURCE OF TRUTH PRIORITY

When information conflicts, use this priority:

1. Actual database/repository code and migrations
2. Approved current project requirements
3. docs/AI_MEMORY.md
4. docs/ARCHITECTURE.md
5. TODO/CHANGELOG
6. AI assumptions

Never use AI assumptions above actual project code.

---

END OF AI MEMORY
