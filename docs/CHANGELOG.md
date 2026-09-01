# CHANGELOG

## 2026-08-28
- Phase 2 - Asset Taxonomy implementation complete
- Added `asset_categories` and `asset_types` tables (id, code, name, FK, tracking_type, status, soft deletes, timestamps)
- Created `App\Enums\AssetTrackingType` enum (individual, quantity) for type-safe tracking type values
- Added `AssetCategory` and `AssetType` Eloquent models with relationships, soft deletes, and enum casts
- Implemented thin controllers `AssetCategoryController` and `AssetTypeController` (index/store/show/update/destroy)
- Added `AssetCategoryService` and `AssetTypeService` business logic (with delete-conflict for non-empty categories)
- Implemented Form Request validation classes (`AssetCategoryRequest`, `StoreAssetCategoryRequest`, `UpdateAssetCategoryRequest`, `AssetTypeRequest`, `StoreAssetTypeRequest`, `UpdateAssetTypeRequest`)
- Validation rules enforce unique (code, name) considering soft deletes and unique (asset_category_id, name) for AssetType
- tracking_type is required and validated against enum values; invalid values rejected
- Added `AssetCategoryPolicy` and `AssetTypePolicy` using `ChecksLocationPermission` trait
- Added API resources `AssetCategoryResource` and `AssetTypeResource`
- New permissions: `asset_categories.view/create/update/delete`, `asset_types.view/create/update/delete`
- New seeder `AssetTaxonomyPermissionSeeder` (idempotent, safe to re-run) assigned to Super Admin, Admin, Inventory Manager roles
- New seeder `AssetTaxonomySeeder` with seed categories and types (Computer Equipment, Furniture, Electrical, Accessory, Network, Other)
- Added factories `AssetCategoryFactory` and `AssetTypeFactory` for testing
- Added `tests/Feature/Api/V1/AssetTypeApiTest.php` (12 tests covering auth, validation, CRUD, tracking_type, category relationship, filters)
- Expanded `tests/Feature/Api/V1/AssetTaxonomyTest.php` for categories (7 tests)
- Vue API client `resources/js/api/taxonomy.js` mirrors Phase 1 pattern
- Vue Pinia store `resources/js/stores/taxonomy.js` for AssetCategory/AssetType data
- Reusable Vue component `resources/js/components/TaxonomyForm.vue` with drawer pattern (kind=category|type)
- New page `resources/js/pages/TaxonomyPage.vue` at route `/taxonomy` with categories/types tabs, search, edit, delete
- New route `/taxonomy` registered in `resources/js/router/index.js`
- Added navigation link to /taxonomy from Explorer page header

## 2026-08-26
- Fixed RoomTypePolicy to use correct `room_types.*` permission names instead of incorrect `rooms.*` names
- Added missing `room_types.view`, `room_types.create`, `room_types.update`, `room_types.delete` permissions to LocationPermissionSeeder
- Updated Inventory Manager, IT Manager, and Viewer roles to include room type permissions
- Fixed ExplorerPage.vue RoomType CRUD buttons to use `room_types.*` instead of `rooms.*` permissions
- Fixed indentation in LocationPermissionSeeder.php (Viewer role line)
- Verified ConflictException â†’ 409 error handling works correctly (backend + frontend)
- All Phase 1 Location Hierarchy tests passing (7 tests, 25 assertions)

## 2026-08-24
- Created AI continuity memory from the approved School Asset & Inventory Management System architecture.
- Added AI takeover protocol.
- Added architecture rules, domain rules, transactional rules, API structure, frontend structure, permissions, roadmap, risks and handoff rules.
- Implementation status remains UNKNOWN until the actual repository is inspected.
