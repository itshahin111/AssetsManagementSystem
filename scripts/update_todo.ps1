$path = 'C:\Users\Shahin\Desktop\Laravel\Inventory\docs\TODO.md'
$content = Get-Content -Raw $path
$newSection = [System.Environment]::NewLine + [System.Environment]::NewLine + '## Phase 2 — Asset Taxonomy' + [System.Environment]::NewLine + '- [x] AssetCategory' + [System.Environment]::NewLine + '- [x] AssetType' + [System.Environment]::NewLine + '- [x] tracking_type' + [System.Environment]::NewLine + '- [x] CRUD' + [System.Environment]::NewLine + '- [x] Seeders' + [System.Environment]::NewLine + '- [x] Vue forms/pages' + [System.Environment]::NewLine + '- [x] Permissions' + [System.Environment]::NewLine + '- [x] Policies' + [System.Environment]::NewLine + '- [x] Tests' + [System.Environment]::NewLine + [System.Environment]::NewLine
$content = [regex]::Replace($content, '(?ms)## Phase 2.*?(?=## Phase 3)', $newSection)
[System.IO.File]::WriteAllText($path, $content)
