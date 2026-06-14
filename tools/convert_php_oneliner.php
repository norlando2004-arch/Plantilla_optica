<?php
$file = __DIR__ . '/../resources/views/gafas/show.blade.php';
$content = file_get_contents($file);

$result = '';
$i = 0;
$len = strlen($content);

while ($i < $len) {
    $pos = strpos($content, '@php(', $i);
    if ($pos === false) {
        $result .= substr($content, $i);
        break;
    }
    $result .= substr($content, $i, $pos - $i);

    // Find matching closing ) using balanced paren matching
    $start = $pos + 4; // position of the opening (
    $depth = 0;
    $j = $start;
    $inSingle = false;
    $inDouble = false;
    while ($j < $len) {
        $c = $content[$j];
        if (!$inSingle && !$inDouble) {
            if ($c === "'") $inSingle = true;
            elseif ($c === '"') $inDouble = true;
            elseif ($c === '(') $depth++;
            elseif ($c === ')') {
                $depth--;
                if ($depth === 0) break;
            }
        } elseif ($inSingle) {
            if ($c === "'" && ($j === 0 || $content[$j-1] !== '\\')) $inSingle = false;
        } elseif ($inDouble) {
            if ($c === '"' && ($j === 0 || $content[$j-1] !== '\\')) $inDouble = false;
        }
        $j++;
    }

    // Extract inner expression (strip outer parens)
    $inner = substr($content, $start + 1, $j - $start - 1);
    $result .= "@php\n" . $inner . "\n@endphp";
    $i = $j + 1;
}

file_put_contents($file, $result);
echo "Done.\n";
echo "Remaining @php(: " . substr_count($result, '@php(') . "\n";
echo "Total @endphp: " . substr_count($result, '@endphp') . "\n";
