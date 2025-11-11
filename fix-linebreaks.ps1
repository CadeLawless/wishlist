$content = Get-Content 'comprehensive-test-matrix.tsv' -Raw
$content = $content -replace '(\d+\. [^\t]*?)\r?\n(\d+\. [^\t]*?)\r?\n(\d+\. [^\t]*?)(?=\t)', '$1 | $2 | $3'
$content = $content -replace '(\d+\. [^\t]*?)\r?\n(\d+\. [^\t]*?)(?=\t)', '$1 | $2'
Set-Content 'comprehensive-test-matrix-fixed.tsv' -Value $content
