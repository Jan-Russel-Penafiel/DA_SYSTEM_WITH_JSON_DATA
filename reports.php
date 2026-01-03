<?php
ob_start();
session_start();

require_once __DIR__ . '/classes/Region.php';
require_once __DIR__ . '/classes/Farmer.php';
require_once __DIR__ . '/classes/FarmerRepository.php';
require_once __DIR__ . '/classes/Statistics.php';
require_once __DIR__ . '/classes/Exporter.php';
require_once __DIR__ . '/includes/layout.php';

// Initialize
$repository = new FarmerRepository();
$statistics = new Statistics($repository);
$regions = Region::getAllRegions();
$fertilizerTypes = $repository->getUniqueFertilizerTypes();

// Get all available years from the database
$allFarmersForYears = $repository->getAllFarmers();
$availableYears = [];
foreach ($allFarmersForYears as $farmer) {
    $year = date('Y', strtotime($farmer->getDateApproved()));
    if (!in_array($year, $availableYears)) {
        $availableYears[] = $year;
    }
}
rsort($availableYears); // Sort years in descending order (newest first)

// Handle report generation
$reportType = isset($_GET['type']) ? $_GET['type'] : '';
$selectedRegion = isset($_GET['region']) ? trim($_GET['region']) : '';
$selectedYear = isset($_GET['year']) ? trim($_GET['year']) : '';

if (isset($_GET['generate'])) {
    $farmers = [];
    
    // Set date range if year is selected
    $dateFrom = '';
    $dateTo = '';
    if (!empty($selectedYear)) {
        $dateFrom = $selectedYear . '-01-01';
        $dateTo = $selectedYear . '-12-31';
    }
    
    switch ($reportType) {
        case 'all':
            if (!empty($selectedYear)) {
                $farmers = $repository->searchFarmers('', '', '', $dateFrom, $dateTo);
            } else {
                $farmers = $repository->getAllFarmers();
            }
            break;
        case 'region':
            if (!empty($selectedRegion)) {
                $farmers = $repository->searchFarmers('', $selectedRegion, '', $dateFrom, $dateTo);
            }
            break;
        default:
            if (!empty($selectedYear)) {
                $farmers = $repository->searchFarmers('', '', '', $dateFrom, $dateTo);
            } else {
                $farmers = $repository->getAllFarmers();
            }
    }

    if (isset($_GET['format'])) {
        if ($_GET['format'] === 'csv') {
            $filename = 'da_report_' . ($reportType ?: 'all');
            if (!empty($selectedRegion)) {
                $filename .= '_' . $selectedRegion;
            }
            if (!empty($selectedYear)) {
                $filename .= '_' . $selectedYear;
            }
            Exporter::toCSV($farmers, $filename);
            exit;
        } elseif ($_GET['format'] === 'print') {
            echo Exporter::toPrintable($farmers);
            exit;
        }
    }
}

renderHeader('reports');
renderFlashMessage();
?>

<div class="container mx-auto px-4">
    <!-- Page Title -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-da-dark-green flex items-center">
            <i class="fas fa-file-alt mr-3"></i>Reports & Export
        </h1>
        <p class="text-gray-600 mt-1">Generate and export reports for fertilizer distribution data</p>
    </div>

    <!-- Year Filter Tabs -->
    <div class="bg-white rounded-xl shadow-md p-4 mb-6">
        <h3 class="text-sm font-semibold text-gray-600 mb-3">
            <i class="fas fa-calendar-alt mr-2"></i>Filter Export Data by Year:
        </h3>
        <div class="flex flex-wrap gap-2">
            <a href="?" 
               class="px-4 py-2 rounded-lg text-sm font-medium <?= empty($selectedYear) ? 'bg-da-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?> transition">
                <i class="fas fa-list mr-1"></i>All Years
            </a>
            <?php foreach ($availableYears as $year): ?>
                <a href="?year=<?= $year ?>" 
                   class="px-4 py-2 rounded-lg text-sm font-medium <?= $selectedYear === (string)$year ? 'bg-da-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?> transition">
                    <i class="fas fa-calendar mr-1"></i><?= $year ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($selectedYear)): ?>
        <div class="mt-3 text-sm text-gray-600">
            <i class="fas fa-info-circle mr-1"></i>Exporting data for year <strong><?= htmlspecialchars($selectedYear) ?></strong> only
        </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Report Types -->
        <div class="lg:col-span-2 space-y-6">
            <!-- All Beneficiaries Data -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                    <h2 class="text-xl font-bold text-da-dark-green flex items-center">
                        <i class="fas fa-users mr-2 text-green-500"></i>All Beneficiaries Data
                        <?php if (!empty($selectedYear)): ?>
                            <span class="ml-2 text-sm font-normal text-gray-500">(<?= htmlspecialchars($selectedYear) ?>)</span>
                        <?php endif; ?>
                    </h2>
                    <div class="flex gap-2 mt-3 md:mt-0">
                        <button onclick="exportAllBeneficiariesXLSX()" 
                           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm">
                            <i class="fas fa-file-excel mr-1"></i>Export XLSX
                        </button>
                        <button onclick="printAllBeneficiaries()" 
                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                            <i class="fas fa-print mr-1"></i>Print
                        </button>
                    </div>
                </div>
                
                <?php
                // Get all farmers (filtered by year if selected)
                if (!empty($selectedYear)) {
                    $dateFrom = $selectedYear . '-01-01';
                    $dateTo = $selectedYear . '-12-31';
                    $allFarmers = $repository->searchFarmers('', '', '', $dateFrom, $dateTo);
                } else {
                    $allFarmers = $repository->getAllFarmers();
                }
                ?>
                
                <p class="text-sm text-gray-600 mb-4">
                    <i class="fas fa-info-circle mr-1"></i>Showing <strong><?= count($allFarmers) ?></strong> beneficiaries
                    <?= !empty($selectedYear) ? ' for year ' . htmlspecialchars($selectedYear) : '' ?>
                </p>
                
                <div class="overflow-x-auto max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Name</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">RSBSA ID</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Region</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Fertilizer</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 uppercase">Qty (kg)</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Date Approved</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($allFarmers)): ?>
                                <tr>
                                    <td colspan="8" class="px-3 py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-3xl mb-2"></i>
                                        <p>No beneficiaries found<?= !empty($selectedYear) ? ' for ' . htmlspecialchars($selectedYear) : '' ?></p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $counter = 1; foreach ($allFarmers as $farmer): ?>
                                    <tr class="hover:bg-green-50 transition">
                                        <td class="px-3 py-2 text-gray-500"><?= $counter++ ?></td>
                                        <td class="px-3 py-2 text-gray-800 font-medium">
                                            <?= htmlspecialchars($farmer->getFirstName() . ' ' . $farmer->getLastName()) ?>
                                        </td>
                                        <td class="px-3 py-2 text-gray-600 font-mono text-xs">
                                            <?= htmlspecialchars($farmer->getRsbsaId()) ?>
                                        </td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">
                                                <?= htmlspecialchars($farmer->getRegionCode()) ?>
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">
                                            <?= htmlspecialchars($farmer->getFertilizerType()) ?>
                                        </td>
                                        <td class="px-3 py-2 text-center font-bold text-da-dark-green">
                                            <?= number_format($farmer->getFertilizerQuantity()) ?>
                                        </td>
                                        <td class="px-3 py-2 text-gray-600">
                                            <?= date('M d, Y', strtotime($farmer->getDateApproved())) ?>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <a href="view.php?id=<?= $farmer->getId() ?>" 
                                               class="text-blue-600 hover:text-blue-800" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Regional Reports -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-da-dark-green mb-4 flex items-center">
                    <i class="fas fa-map mr-2 text-blue-500"></i>Regional Reports
                </h2>
                <p class="text-sm text-gray-600 mb-4">Generate reports filtered by specific region</p>
                
                <form method="GET" action="" class="mb-4">
                    <input type="hidden" name="generate" value="1">
                    <input type="hidden" name="type" value="region">
                    <input type="hidden" name="year" value="<?= htmlspecialchars($selectedYear) ?>">
                    <div class="flex flex-col md:flex-row gap-3">
                        <select name="region" required class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green bg-white">
                            <option value="">Select Region...</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?= htmlspecialchars($region->getCode()) ?>">
                                    Region <?= htmlspecialchars($region->getCode()) ?> - <?= htmlspecialchars($region->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" onclick="exportRegionalXLSX()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-file-excel mr-1"></i>Export XLSX
                        </button>
                        <button type="button" onclick="printRegionalReport()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-print mr-1"></i>Print
                        </button>
                    </div>
                </form>
                <?php if (!empty($selectedYear)): ?>
                <p class="text-xs text-gray-500 mb-2"><i class="fas fa-filter mr-1"></i>Filtered by year: <?= htmlspecialchars($selectedYear) ?></p>
                <?php endif; ?>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2 mt-4">
                    <?php 
                    // Get counts filtered by year if selected
                    if (!empty($selectedYear)) {
                        $dateFrom = $selectedYear . '-01-01';
                        $dateTo = $selectedYear . '-12-31';
                        $filteredFarmers = $repository->searchFarmers('', '', '', $dateFrom, $dateTo);
                        $byRegion = [];
                        foreach ($filteredFarmers as $farmer) {
                            $regionCode = $farmer->getRegionCode();
                            if (!isset($byRegion[$regionCode])) {
                                $byRegion[$regionCode] = 0;
                            }
                            $byRegion[$regionCode]++;
                        }
                    } else {
                        $byRegion = $statistics->getBeneficiariesByRegion();
                    }
                    foreach ($regions as $region): 
                        $count = $byRegion[$region->getCode()] ?? 0;
                    ?>
                        <a href="?generate=1&type=region&region=<?= urlencode($region->getCode()) ?><?= !empty($selectedYear) ? '&year=' . $selectedYear : '' ?>&format=csv" 
                           class="p-3 bg-gray-50 rounded-lg hover:bg-green-50 transition text-center group"
                           title="Download <?= htmlspecialchars($region->getName()) ?> Report<?= !empty($selectedYear) ? ' (' . $selectedYear . ')' : '' ?>">
                            <span class="text-xs font-semibold text-gray-700 group-hover:text-da-dark-green">
                                <?= htmlspecialchars($region->getCode()) ?>
                            </span>
                            <p class="text-lg font-bold text-da-green"><?= $count ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Fertilizer Type Reports -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-da-dark-green mb-4 flex items-center">
                    <i class="fas fa-box mr-2 text-yellow-500"></i>Fertilizer Distribution Summary
                </h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Fertilizer Type</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Beneficiaries</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Total Qty (kg)</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Avg/Farmer (kg)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php 
                            $byFertilizer = $statistics->getBeneficiariesByFertilizerType();
                            foreach ($byFertilizer as $type => $data): 
                            ?>
                                <tr class="hover:bg-green-50 transition">
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        <i class="fas fa-cube text-da-green mr-2"></i>
                                        <?= htmlspecialchars($type) ?>
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm font-bold text-da-dark-green"><?= number_format($data['count']) ?></td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-700"><?= number_format($data['quantity']) ?></td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-700"><?= number_format($data['quantity'] / $data['count'], 1) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-gray-100">
                            <tr>
                                <td class="px-4 py-3 text-sm font-bold text-gray-800">Total</td>
                                <td class="px-4 py-3 text-center text-sm font-bold text-da-dark-green"><?= number_format($statistics->getTotalBeneficiaries()) ?></td>
                                <td class="px-4 py-3 text-center text-sm font-bold text-gray-800"><?= number_format($statistics->getTotalFertilizerDistributed()) ?></td>
                                <td class="px-4 py-3 text-center text-sm font-bold text-gray-800"><?= number_format($statistics->getAverageFertilizerPerFarmer(), 1) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Summary Card -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-da-dark-green mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>Data Summary
                </h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                        <span class="text-sm text-gray-600">Total Beneficiaries</span>
                        <span class="font-bold text-da-dark-green"><?= number_format($statistics->getTotalBeneficiaries()) ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                        <span class="text-sm text-gray-600">Regions Covered</span>
                        <span class="font-bold text-blue-600"><?= count($byRegion) ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                        <span class="text-sm text-gray-600">Total Fertilizer</span>
                        <span class="font-bold text-yellow-600"><?= number_format($statistics->getTotalFertilizerDistributed()) ?> kg</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                        <span class="text-sm text-gray-600">Total Farm Area</span>
                        <span class="font-bold text-purple-600"><?= number_format($statistics->getTotalFarmArea(), 1) ?> ha</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                        <span class="text-sm text-gray-600">Est. Total Value</span>
                        <span class="font-bold text-red-600">₱<?= number_format($statistics->getEstimatedTotalValue()) ?></span>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-da-dark-green mb-4 flex items-center">
                    <i class="fas fa-question-circle mr-2 text-blue-500"></i>Help
                </h2>
                <div class="space-y-3 text-sm text-gray-600">
                    <p><strong class="text-gray-800">CSV Export:</strong> Downloads data as a spreadsheet-compatible file.</p>
                    <p><strong class="text-gray-800">Print:</strong> Opens a printer-friendly view of the report.</p>
                    <p><strong class="text-gray-800">Regional Reports:</strong> Click on a region code to download its specific report.</p>
                </div>
            </div>

            <!-- Last Updated -->
            <div class="bg-gradient-to-br from-da-dark-green to-da-green rounded-xl shadow-md p-6 text-white">
                <h2 class="text-lg font-bold mb-2 flex items-center">
                    <i class="fas fa-clock mr-2"></i>Last Updated
                </h2>
                <p class="text-da-light-green text-2xl font-bold"><?= date('F d, Y') ?></p>
                <p class="text-da-light-green text-sm"><?= date('h:i A') ?></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.1/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
// Store farmers data for PDF generation
const farmersData = <?= json_encode(array_map(function($f) {
    return [
        'rsbsa_id' => $f->getRsbsaId(),
        'name' => $f->getFullName(),
        'location' => $f->getFullAddress(),
        'region' => $f->getRegionCode(),
        'fertilizer' => $f->getFertilizerType(),
        'quantity' => $f->getFertilizerQuantity(),
        'farm_area' => $f->getFarmArea(),
        'date_approved' => date('M d, Y', strtotime($f->getDateApproved()))
    ];
}, $allFarmers)) ?>;

const selectedYear = '<?= htmlspecialchars($selectedYear) ?>';

function exportAllBeneficiariesXLSX() {
    // Prepare data with headers
    const headers = ['#', 'RSBSA ID', 'Full Name', 'Location', 'Region', 'Fertilizer Type', 'Quantity (kg)', 'Farm Area (ha)', 'Date Approved'];
    const data = farmersData.map((f, i) => [
        i + 1,
        f.rsbsa_id,
        f.name,
        f.location,
        f.region,
        f.fertilizer,
        f.quantity,
        f.farm_area,
        f.date_approved
    ]);
    
    // Add headers as first row
    data.unshift(headers);
    
    // Create worksheet
    const ws = XLSX.utils.aoa_to_sheet(data);
    
    // Set column widths
    ws['!cols'] = [
        { wch: 5 },   // #
        { wch: 20 },  // RSBSA ID
        { wch: 25 },  // Full Name
        { wch: 40 },  // Location
        { wch: 10 },  // Region
        { wch: 30 },  // Fertilizer Type
        { wch: 12 },  // Quantity
        { wch: 12 },  // Farm Area
        { wch: 15 }   // Date Approved
    ];
    
    // Create workbook
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Beneficiaries');
    
    // Generate filename
    let filename = 'DA_Beneficiaries_Report';
    if (selectedYear) filename += '_' + selectedYear;
    filename += '.xlsx';
    
    // Save file
    XLSX.writeFile(wb, filename);
}

function exportRegionalXLSX() {
    const regionSelect = document.querySelector('select[name="region"]');
    const selectedRegion = regionSelect.value;
    
    if (!selectedRegion) {
        alert('Please select a region first');
        return;
    }
    
    const regionName = regionSelect.options[regionSelect.selectedIndex].text;
    const filteredData = farmersData.filter(f => f.region === selectedRegion);
    
    if (filteredData.length === 0) {
        alert('No beneficiaries found for the selected region');
        return;
    }
    
    // Prepare data with headers
    const headers = ['#', 'RSBSA ID', 'Full Name', 'Location', 'Fertilizer Type', 'Quantity (kg)', 'Farm Area (ha)', 'Date Approved'];
    const data = filteredData.map((f, i) => [
        i + 1,
        f.rsbsa_id,
        f.name,
        f.location,
        f.fertilizer,
        f.quantity,
        f.farm_area,
        f.date_approved
    ]);
    
    data.unshift(headers);
    
    const ws = XLSX.utils.aoa_to_sheet(data);
    ws['!cols'] = [
        { wch: 5 },
        { wch: 20 },
        { wch: 25 },
        { wch: 40 },
        { wch: 30 },
        { wch: 12 },
        { wch: 12 },
        { wch: 15 }
    ];
    
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Regional Report');
    
    let filename = 'DA_Report_' + selectedRegion;
    if (selectedYear) filename += '_' + selectedYear;
    filename += '.xlsx';
    
    XLSX.writeFile(wb, filename);
}

function printAllBeneficiaries() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4'); // Landscape for more columns
    
    const pageWidth = doc.internal.pageSize.getWidth();
    const centerX = pageWidth / 2;
    
    // Header
    doc.setFontSize(18);
    doc.setTextColor(0, 100, 0);
    doc.setFont('helvetica', 'bold');
    doc.text('Department of Agriculture', centerX, 15, { align: 'center' });
    
    doc.setFontSize(10);
    doc.setTextColor(102, 102, 102);
    doc.setFont('helvetica', 'normal');
    doc.text('Republic of the Philippines', centerX, 21, { align: 'center' });
    
    doc.setFontSize(12);
    doc.setTextColor(0, 0, 0);
    doc.setFont('helvetica', 'bold');
    doc.text('Fertilizer Beneficiaries Report' + (selectedYear ? ' (' + selectedYear + ')' : ''), centerX, 28, { align: 'center' });
    
    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(102, 102, 102);
    doc.text('Generated: ' + new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }), centerX, 34, { align: 'center' });
    
    // Table data
    const tableData = farmersData.map((f, i) => [
        i + 1,
        f.rsbsa_id,
        f.name,
        f.location,
        f.region,
        f.fertilizer,
        f.quantity + ' kg',
        f.farm_area + ' ha',
        f.date_approved
    ]);
    
    // Generate table
    doc.autoTable({
        startY: 40,
        head: [['#', 'RSBSA ID', 'Full Name', 'Location', 'Region', 'Fertilizer', 'Qty', 'Farm Area', 'Date Approved']],
        body: tableData,
        theme: 'grid',
        headStyles: { fillColor: [0, 100, 0], textColor: 255, fontStyle: 'bold', fontSize: 8 },
        bodyStyles: { fontSize: 7 },
        alternateRowStyles: { fillColor: [242, 242, 242] },
        styles: { cellPadding: 2 },
        tableWidth: 'auto',
        margin: { left: 5, right: 5 }
    });
    
    // Footer
    const finalY = doc.lastAutoTable.finalY + 10;
    doc.setFontSize(9);
    doc.setTextColor(102, 102, 102);
    doc.text('Total Beneficiaries: ' + farmersData.length, centerX, finalY, { align: 'center' });
    doc.setFontSize(8);
    doc.text('This is a computer-generated report from the DA Fertilizer Beneficiary Management System', centerX, finalY + 5, { align: 'center' });
    
    // Save PDF
    doc.save('DA_Beneficiaries_Report' + (selectedYear ? '_' + selectedYear : '') + '.pdf');
}

function printRegionalReport() {
    const regionSelect = document.querySelector('select[name="region"]');
    const selectedRegion = regionSelect.value;
    
    if (!selectedRegion) {
        alert('Please select a region first');
        return;
    }
    
    const regionName = regionSelect.options[regionSelect.selectedIndex].text;
    const filteredData = farmersData.filter(f => f.region === selectedRegion);
    
    if (filteredData.length === 0) {
        alert('No beneficiaries found for the selected region');
        return;
    }
    
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4');
    
    const pageWidth = doc.internal.pageSize.getWidth();
    const centerX = pageWidth / 2;
    
    // Header
    doc.setFontSize(18);
    doc.setTextColor(0, 100, 0);
    doc.setFont('helvetica', 'bold');
    doc.text('Department of Agriculture', centerX, 15, { align: 'center' });
    
    doc.setFontSize(10);
    doc.setTextColor(102, 102, 102);
    doc.setFont('helvetica', 'normal');
    doc.text('Republic of the Philippines', centerX, 21, { align: 'center' });
    
    doc.setFontSize(12);
    doc.setTextColor(0, 0, 0);
    doc.setFont('helvetica', 'bold');
    doc.text('Regional Report: ' + regionName + (selectedYear ? ' (' + selectedYear + ')' : ''), centerX, 28, { align: 'center' });
    
    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(102, 102, 102);
    doc.text('Generated: ' + new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }), centerX, 34, { align: 'center' });
    
    // Table data
    const tableData = filteredData.map((f, i) => [
        i + 1,
        f.rsbsa_id,
        f.name,
        f.location,
        f.fertilizer,
        f.quantity + ' kg',
        f.farm_area + ' ha',
        f.date_approved
    ]);
    
    doc.autoTable({
        startY: 40,
        head: [['#', 'RSBSA ID', 'Full Name', 'Location', 'Fertilizer', 'Qty', 'Farm Area', 'Date Approved']],
        body: tableData,
        theme: 'grid',
        headStyles: { fillColor: [0, 100, 0], textColor: 255, fontStyle: 'bold', fontSize: 8 },
        bodyStyles: { fontSize: 7 },
        alternateRowStyles: { fillColor: [242, 242, 242] },
        styles: { cellPadding: 2 },
        tableWidth: 'auto',
        margin: { left: 5, right: 5 }
    });
    
    const finalY = doc.lastAutoTable.finalY + 10;
    doc.setFontSize(9);
    doc.setTextColor(102, 102, 102);
    doc.text('Total Beneficiaries: ' + filteredData.length, centerX, finalY, { align: 'center' });
    doc.setFontSize(8);
    doc.text('This is a computer-generated report from the DA Fertilizer Beneficiary Management System', centerX, finalY + 5, { align: 'center' });
    
    doc.save('DA_Report_' + selectedRegion + (selectedYear ? '_' + selectedYear : '') + '.pdf');
}
</script>

<?php renderFooter(); ?>
