$inputFile = [System.IO.File]::OpenRead('d:\carlo\dowloads\u157921072_pastesRP (10).sql.gz')
$gzipStream = New-Object System.IO.Compression.GzipStream($inputFile, [System.IO.Compression.CompressionMode]::Decompress)
$outputFile = [System.IO.File]::Create('d:\carlo\dowloads\temp_import.sql')
$gzipStream.CopyTo($outputFile)
$outputFile.Close()
$gzipStream.Close()
$inputFile.Close()
Write-Host "Descomprimido OK - Archivo listo para importar"
