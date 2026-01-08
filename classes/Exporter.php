<?php

class Exporter {
    
    public static function toCSV(array $farmers, string $filename = 'beneficiaries'): void {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, [
            'RSBSA ID',
            'Last Name',
            'First Name',
            'Middle Name',
            'Barangay',
            'Municipality',
            'Province',
            'Region',
            'Contact Number',
            'Farm Area (ha)',
            'Fertilizer Type',
            'Quantity (kg)',
            'Date Approved',
            'Status'
        ]);
        
        // Data rows
        foreach ($farmers as $farmer) {
            fputcsv($output, [
                $farmer->getRsbsaId(),
                $farmer->getLastName(),
                $farmer->getFirstName(),
                $farmer->getMiddleName(),
                $farmer->getBarangay(),
                $farmer->getMunicipality(),
                $farmer->getProvince(),
                $farmer->getRegionCode(),
                $farmer->getContactNumber(),
                $farmer->getFarmArea(),
                $farmer->getFertilizerType(),
                $farmer->getFertilizerQuantity(),
                $farmer->getDateApproved(),
                $farmer->getStatus()
            ]);
        }
        
        fclose($output);
        exit;
    }

    public static function toPrintable(array $farmers): string {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>DA Beneficiaries Report</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 10px; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h1 { margin: 0; color: #006400; }
                .header p { margin: 5px 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
                th { background-color: #006400; color: white; }
                tr:nth-child(even) { background-color: #f2f2f2; }
                .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Department of Agriculture</h1>
                <p>Republic of the Philippines</p>
                <p><strong>Fertilizer Beneficiaries Report</strong></p>
                <p>Generated: ' . date('F d, Y h:i A') . '</p>
            </div>
            <button class="no-print" onclick="window.print()">Print Report</button>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>RSBSA ID</th>
                        <th>Full Name</th>
                        <th>Location</th>
                        <th>Region</th>
                        <th>Fertilizer</th>
                        <th>Qty (kg)</th>
                        <th>Farm Area</th>
                        <th>Date Approved</th>
                    </tr>
                </thead>
                <tbody>';
        
        $count = 1;
        foreach ($farmers as $farmer) {
            $html .= '<tr>
                <td>' . $count++ . '</td>
                <td>' . htmlspecialchars($farmer->getRsbsaId()) . '</td>
                <td>' . htmlspecialchars($farmer->getFullName()) . '</td>
                <td>' . htmlspecialchars($farmer->getFullAddress()) . '</td>
                <td>' . htmlspecialchars($farmer->getRegionCode()) . '</td>
                <td>' . htmlspecialchars($farmer->getFertilizerType()) . '</td>
                <td>' . number_format($farmer->getFertilizerQuantity()) . '</td>
                <td>' . number_format($farmer->getFarmArea(), 2) . ' ha</td>
                <td>' . date('M d, Y', strtotime($farmer->getDateApproved())) . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
            </table>
            <div class="footer">
                <p>Total Beneficiaries: ' . count($farmers) . '</p>
                <p>This is a computer-generated report from the DA Fertilizer Beneficiary Management System</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }
}
