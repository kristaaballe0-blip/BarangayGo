<?php
// Staff authorization is checked by the front controller before export.
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="barangay-transactions.xls"');
echo '<?xml version="1.0" encoding="UTF-8"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="Transactions"><Table>';
$rows = [['Request', 'Resident', 'Document', 'Status', 'Fee PHP', 'Paid PHP', 'Created']];
foreach ($requests as $r) {
    $rows[] = [
        $r['id'],
        $r['resident'],
        $r['document'],
        $r['status'],
        $r['fee'] / 100,
        ($r['paid'] ?? 0) / 100,
        $r['created_at'],
    ];
}
foreach ($rows as $row) {
    echo '<Row>';
    foreach ($row as $v) {
        echo '<Cell><Data ss:Type="String">' .
            htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8') .
            '</Data></Cell>';
    }
    echo '</Row>';
}
echo '</Table></Worksheet></Workbook>';
exit();
