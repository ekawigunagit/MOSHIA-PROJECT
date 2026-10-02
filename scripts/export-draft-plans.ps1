$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
Push-Location $root
try {
    $json = @'
<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo json_encode([
    'products' => App\Modules\Core\Catalog\Models\Product::orderBy('id')->get(['id','slug','title'])->toArray(),
    'plans' => App\Modules\Core\Billing\Models\Plan::with('product:id,slug,title')->where('status','draft')->orderBy('product_id')->orderBy('id')->get(['id','product_id','name','description','status'])->toArray(),
], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
'@ | php
    if ($LASTEXITCODE -ne 0) { throw 'Could not read draft plans.' }
    $data = ($json -join "") | ConvertFrom-Json
} finally { Pop-Location }

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$localTime = [TimeZoneInfo]::ConvertTimeBySystemTimeZoneId([DateTime]::UtcNow, 'SE Asia Standard Time')
$exportDirectory = Join-Path $root 'exports'
New-Item -ItemType Directory -Force -Path $exportDirectory | Out-Null
$path = Join-Path $exportDirectory ("MOSHIA_DRAFT_PAKET_" + $localTime.ToString('yyyy-MM-dd_HHmmss') + '.xlsx')
$planCount = @($data.plans).Count
function Escape-Xml($value) { [System.Security.SecurityElement]::Escape([string]$value) }
function Column-Name([int]$number) {
    $name = ''
    while ($number -gt 0) {
        $number--
        $name = [char](65 + ($number % 26)) + $name
        $number = [int][Math]::Floor($number / 26)
    }
    return $name
}
function Sheet-Xml($headers, $rows, $widths, [int]$editableFrom = 999, [string]$validation = '', [bool]$filter = $true) {
    $cols = ''
    for ($i=0; $i -lt $headers.Count; $i++) {
        $cols += '<col min="' + ($i+1) + '" max="' + ($i+1) + '" width="' + $widths[$i] + '" customWidth="1"/>'
    }
    $rowXml = '<row r="1" ht="32" customHeight="1">'
    for ($i=0; $i -lt $headers.Count; $i++) {
        $rowXml += '<c r="' + (Column-Name ($i+1)) + '1" t="inlineStr" s="1"><is><t>' + (Escape-Xml $headers[$i]) + '</t></is></c>'
    }
    $rowXml += '</row>'
    $r=2
    foreach ($row in $rows) {
        $rowXml += '<row r="' + $r + '" ht="60" customHeight="1">'
        for ($i=0; $i -lt $headers.Count; $i++) {
            $style = if (($i+1) -ge $editableFrom) { 3 } else { 2 }
            $value = $row[$i]
            $reference = (Column-Name ($i+1)) + $r
            if ($value -is [int] -or $value -is [long] -or $value -is [double] -or $value -is [decimal]) {
                $rowXml += '<c r="' + $reference + '" s="' + $style + '"><v>' + [string]$value + '</v></c>'
            } else {
                # Inline strings preserve text beginning with =, +, -, or @ as text.
                $rowXml += '<c r="' + $reference + '" t="inlineStr" s="' + $style + '"><is><t xml:space="preserve">' + (Escape-Xml $value) + '</t></is></c>'
            }
        }
        $rowXml += '</row>'
        $r++
    }
    $range = 'A1:' + (Column-Name $headers.Count) + [Math]::Max(1, $r-1)
    $autoFilter = if ($filter) { '<autoFilter ref="' + $range + '"/>' } else { '' }
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><dimension ref="' + $range + '"/><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A2" sqref="A2"/></sheetView></sheetViews><sheetFormatPr defaultRowHeight="18"/><cols>' + $cols + '</cols><sheetData>' + $rowXml + '</sheetData>' + $autoFilter + $validation + '<pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/><pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/></worksheet>'
}
function List-Validation($range, $options) {
    return '<dataValidation type="list" allowBlank="1" showErrorMessage="1" errorTitle="Pilih dari daftar" error="Gunakan pilihan yang tersedia." sqref="' + $range + '"><formula1>&quot;' + $options + '&quot;</formula1></dataValidation>'
}
function Number-Validation($range, $type) {
    return '<dataValidation type="' + $type + '" operator="greaterThanOrEqual" allowBlank="1" showErrorMessage="1" errorTitle="Nilai tidak valid" error="Isi angka nol atau lebih; kosong jika belum ditentukan." sqref="' + $range + '"><formula1>0</formula1></dataValidation>'
}
$guide = New-Object System.Collections.ArrayList
[void]$guide.Add(@('Tanggal ekspor', $localTime.ToString('yyyy-MM-dd HH:mm:ss') + ' Asia/Jakarta'))
[void]$guide.Add(@('Jumlah draft database', [string]$planCount + '. Sheet Draft Database berisi data asli, tanpa paket atau harga rekaan.'))
[void]$guide.Add(@('Mulai mengisi', 'Gunakan sheet Isian Paket. Sel kuning untuk masukan Anda. Identitas produk bersumber dari database.'))
[void]$guide.Add(@('Jika belum ada draft', 'Disediakan satu baris TEMPLATE per produk. Nama dan harga kosong. Baris ini belum merupakan paket yang tersimpan di aplikasi.'))
[void]$guide.Add(@('Menambah paket', 'Salin baris produk yang sesuai, kosongkan ID Draft jika paket baru, lalu buat Ref Isian yang unik.'))
[void]$guide.Add(@('Harga dan mata uang', 'Harga berupa angka tanpa simbol atau pemisah ribuan. Pilih mata uang. Kosong = belum diputuskan; nol = gratis jika memang itu keputusan Anda.'))
[void]$guide.Add(@('Model tagihan', 'Sekali bayar, Bulanan, Tahunan, atau Per penggunaan. Tentukan satu per baris; buat baris lain untuk varian harga.'))
[void]$guide.Add(@('Masa aktif dan trial', 'Isi jumlah hari. Kosong jika belum ditentukan. Trial 0 berarti tanpa trial. Jangan mengartikan durasi 0 sebagai selamanya; jelaskan akses tanpa kedaluwarsa di catatan.'))
[void]$guide.Add(@('Kuota', 'Isi sheet Kuota menggunakan Ref Isian yang sama. Tentukan metrik, batas, satuan dan periode. Nilai kosong bukan berarti unlimited; tulis kebijakan unlimited pada catatan.'))
[void]$guide.Add(@('Contoh metrik kuota', 'Wedding: undangan aktif/tamu/storage; Jastip: trip/order; Photo Booth: event/foto; Restaurant: cabang/meja. Ini contoh, bukan batas yang sudah ditetapkan.'))
[void]$guide.Add(@('Pajak', 'Pilih termasuk, belum termasuk, atau belum ditentukan. Perhitungan pajak tidak diaktifkan lewat file ini.'))
[void]$guide.Add(@('Status keputusan', 'Belum ditentukan atau Siap ditinjau hanya untuk diskusi. Tidak sama dengan status publikasi paket di aplikasi.'))
[void]$guide.Add(@('Tidak mengubah aplikasi', 'Workbook tidak mempunyai macro, tidak mengirim data, dan tidak mengimpor harga otomatis. Paket tetap draft; pembayaran dan pembelian belum diaktifkan.'))
[void]$guide.Add(@('Setelah diisi', 'Kirim kembali file ini untuk ditinjau sebelum aturan paket, harga, trial, kuota dan billing diterapkan.'))

$sourceRows = New-Object System.Collections.ArrayList
$inputRows = New-Object System.Collections.ArrayList
$quotaRows = New-Object System.Collections.ArrayList
foreach ($plan in $data.plans) {
    [void]$sourceRows.Add(@($plan.id,$plan.product_id,$plan.product.slug,$plan.product.title,$plan.name,$plan.description,$plan.status))
    [void]$inputRows.Add(@(('DRAFT-'+$plan.id),$plan.id,$plan.product_id,$plan.product.slug,$plan.product.title,$plan.name,$plan.description,'','','','','','','','','Belum ditentukan'))
}
foreach ($product in $data.products) {
    if (@($data.plans | Where-Object { $_.product_id -eq $product.id }).Count -eq 0) {
        [void]$inputRows.Add(@(('TEMPLATE-'+$product.id),'',$product.id,$product.slug,$product.title,'','','','','','','','','','','Belum ditentukan'))
    }
}
foreach ($row in $inputRows) { [void]$quotaRows.Add(@($row[0],'','','','','')) }
for ($i=0; $i -lt 10; $i++) { [void]$quotaRows.Add(@('','','','','','')) }

$validations = '<dataValidations count="6">' +
    (List-Validation 'H2:H501' 'Sekali bayar,Bulanan,Tahunan,Per penggunaan') +
    (List-Validation 'I2:I501' 'IDR,USD,SGD,MYR') +
    (Number-Validation 'J2:J501' 'decimal') +
    (Number-Validation 'K2:L501' 'whole') +
    (List-Validation 'N2:N501' 'Termasuk,Belum termasuk,Belum ditentukan') +
    (List-Validation 'P2:P501' 'Belum ditentukan,Siap ditinjau') + '</dataValidations>'
$quotaValidations = '<dataValidations count="2">' +
    (Number-Validation 'C2:C501' 'decimal') +
    (List-Validation 'E2:E501' 'Per event,Per bulan,Per tahun,Selama masa aktif,Tanpa reset') + '</dataValidations>'
$sheets = @(
    @{ name='Panduan'; xml=(Sheet-Xml @('Keterangan','Petunjuk') $guide @(28,115) 999 '' $false) },
    @{ name='Draft Database'; xml=(Sheet-Xml @('ID Draft','ID Produk','Slug Produk','Produk','Nama Draft','Deskripsi','Status DB') $sourceRows @(13,13,22,27,30,65,15)) },
    @{ name='Isian Paket'; xml=(Sheet-Xml @('Ref Isian','ID Draft (jika ada)','ID Produk','Slug Produk','Produk','Nama Paket','Deskripsi Paket','Model Tagihan','Mata Uang','Harga','Masa Aktif (hari)','Trial (hari)','Fitur / Dukungan','Pajak','Catatan','Status Keputusan') $inputRows @(20,19,12,20,26,28,50,22,15,20,20,17,50,23,50,24) 6 $validations) },
    @{ name='Kuota'; xml=(Sheet-Xml @('Ref Isian','Metrik Kuota','Batas','Satuan','Periode Reset','Catatan') $quotaRows @(20,32,20,22,25,65) 2 $quotaValidations) }
)

$styles = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<fonts count="2"><font><sz val="11"/><color rgb="FF202027"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>
<fills count="5"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFBA2432"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF0F1F4"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFF2CC"/><bgColor indexed="64"/></patternFill></fill></fills>
<borders count="1"><border><left/><right/><top/><bottom style="hair"><color rgb="FFD8DADF"/></bottom><diagonal/></border></borders>
<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
<cellXfs count="4"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf><xf numFmtId="0" fontId="0" fillId="3" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf><xf numFmtId="0" fontId="0" fillId="4" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf></cellXfs>
<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>
'@
$workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><bookViews><workbookView activeTab="0"/></bookViews><sheets>'
$relationships = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
for ($i=1; $i -le $sheets.Count; $i++) {
    $workbook += '<sheet name="' + $sheets[$i-1].name + '" sheetId="' + $i + '" r:id="rId' + $i + '"/>'
    $relationships += '<Relationship Id="rId' + $i + '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' + $i + '.xml"/>'
    $contentTypes += '<Override PartName="/xl/worksheets/sheet' + $i + '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
}
$workbook += '</sheets></workbook>'
$relationships += '<Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>'
$contentTypes += '</Types>'
$zip = [System.IO.Compression.ZipFile]::Open($path, [System.IO.Compression.ZipArchiveMode]::Create)
function Add-Part($name, $xml) {
    # Validate XML before writing the workbook package.
    [void][xml]$xml
    $entry = $zip.CreateEntry($name)
    $writer = New-Object System.IO.StreamWriter($entry.Open(), ([System.Text.UTF8Encoding]::new($false)))
    try { $writer.Write($xml) } finally { $writer.Dispose() }
}
try {
    Add-Part '[Content_Types].xml' $contentTypes
    Add-Part '_rels/.rels' '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>'
    Add-Part 'xl/workbook.xml' $workbook
    Add-Part 'xl/_rels/workbook.xml.rels' $relationships
    Add-Part 'xl/styles.xml' $styles
    for ($i=1; $i -le $sheets.Count; $i++) { Add-Part ("xl/worksheets/sheet" + $i + ".xml") $sheets[$i-1].xml }
} finally { $zip.Dispose() }

Write-Output ("Draft database: " + $planCount)
Write-Output ("Rows ready to fill: " + $inputRows.Count)
Write-Output $path
