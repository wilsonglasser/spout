<?php

/**
 * Captures the golden XML entries from the fixture XLSX.
 *
 * Runs WITHOUT Composer (uses the bundled standalone autoloader) so it can be
 * executed at an older commit in a git worktree to produce the oracle output.
 *
 * Usage: php dump_golden.php <output-dir>
 */

require __DIR__ . '/../../src/SpoutX/Autoloader/autoload.php';
require __DIR__ . '/fixture_builder.php';

$outputDir = $argv[1] ?? (__DIR__ . '/golden');
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

$xlsxPath = tempnam(sys_get_temp_dir(), 'spoutx_fixture_') . '.xlsx';
spoutx_build_fixture_xlsx($xlsxPath);

// Entries whose content is deterministic. docProps/* and [Content_Types] are
// skipped (volatile timestamps / ordering not relevant to our refactor).
$entries = [
    'xl/worksheets/sheet1.xml',
    'xl/styles.xml',
    'xl/sharedStrings.xml',
    'xl/comments1.xml',
];

$zip = new ZipArchive();
if ($zip->open($xlsxPath) !== true) {
    fwrite(STDERR, "Cannot open produced xlsx\n");
    exit(1);
}

$written = [];
foreach ($entries as $entry) {
    $contents = $zip->getFromName($entry);
    if ($contents === false) {
        continue; // entry not present for this fixture
    }
    $target = $outputDir . '/' . str_replace('/', '__', $entry);
    file_put_contents($target, $contents);
    $written[] = $entry;
}
$zip->close();
unlink($xlsxPath);

echo 'Wrote golden entries: ' . implode(', ', $written) . "\n";
