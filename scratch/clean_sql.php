<?php
$sql = file_get_contents('schema_fandaqah_utf8.sql');

// 1. Remove procedures
$lines = explode("\n", $sql);
$cleanLines = [];
$inProcedure = false;
foreach ($lines as $index => $line) {
    if (strpos($line, 'CREATE PROCEDURE') !== false) {
        $inProcedure = true;
    }
    
    if (!$inProcedure) {
        $cleanLines[] = $line;
    }
    
    // Simplistic end detection: Procedures in this dump end with 'END;'
    if ($inProcedure && trim($line) === 'END;') {
        $inProcedure = false;
    }
}
$sql = implode("\n", $cleanLines);

// 2. Replacements
$replacements = [
    'int(10) unsigned' => 'bigint(20) unsigned',
    'int(11)' => 'bigint(20) unsigned',
    'TABLE IF NOT EXISTS `customer` (' => 'TABLE IF NOT EXISTS `customers` (',
    'REFERENCES `customer` (`id`)' => 'REFERENCES `customers` (`id`)',
];

foreach ($replacements as $search => $replace) {
    $sql = str_replace($search, $replace, $sql);
}

file_put_contents('schema_fandaqah_ready.sql', $sql);
echo "SQL Prepared: schema_fandaqah_ready.sql\n";
