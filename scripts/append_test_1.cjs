// Append test methods to AssetTaxonomyTest.php
const fs = require('fs');
const path = 'tests/Feature/Api/V1/AssetTaxonomyTest.php';

function makeTest(name, lines) {
  const body = lines.map(l => '    ' + l).join('\n');
  return `    public function ${name}(): void\n    {\n${body}\n    }\n\n`;
}

const tests = [
  makeTest('test_an_authorized_user_can_create_and_list_asset_categories', [
    '$this->actingAs($this->user, \'sanctum\')',
    '->postJson(\'/api/v1/asset-categories\', [',
    '\'name\'        => \'Test Equipment\',',
    '\'code\'        => \'TEST\',',
    '\'description\' => \'A test category\',',
    '\'status\'      => \'active\',',
    '])',
    '->assertCreated()',
    '->assertJsonPath(\'success\', true)',
    '->assertJsonPath(\'data.code\', \'TEST\')',
    '->assertJsonPath(\'data.name\', \'Test Equipment\');',
    '',
    '$this->actingAs($this->user, \'sanctum\')',
    '->getJson(\'/api/v1/asset-categories\')',
    '->assertOk()',
    '->assertJsonPath(\'success\', true)',
    '->assertJsonPath(\'meta.total\', 1)',
    '->assertJsonPath(\'data.0.name\', \'Test Equipment\');',
  ]),
];

fs.appendFileSync(path, tests.join(''), 'utf-8');
console.log('Test 1 appended');
