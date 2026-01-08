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

// Handle report generation
$reportType = isset($_GET['type']) ? $_GET['type'] : '';
$selectedRegion = isset($_GET['region']) ? trim($_GET['region']) : '';

if (isset($_GET['generate'])) {
    $farmers = [];
    
    switch ($reportType) {
        case 'all':
            $farmers = $repository->getAllFarmers();
            break;
        case 'region':
            if (!empty($selectedRegion)) {
                $farmers = $repository->searchFarmers('', $selectedRegion);
            }
            break;
        default:
            $farmers = $repository->getAllFarmers();
    }

    if (isset($_GET['format'])) {
        if ($_GET['format'] === 'csv') {
            $filename = 'da_report_' . ($reportType ?: 'all');
            if (!empty($selectedRegion)) {
                $filename .= '_' . $selectedRegion;
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Report Types -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Quick Reports -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-da-dark-green mb-4 flex items-center">
                    <i class="fas fa-bolt mr-2 text-yellow-500"></i>Quick Reports
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 hover:border-da-green transition">
                        <div class="text-center">
                            <div class="inline-block bg-green-100 rounded-full p-4 mb-3">
                                <i class="fas fa-users text-da-green text-2xl"></i>
                            </div>
                            <h3 class="font-bold text-gray-800 mb-2">All Beneficiaries</h3>
                            <p class="text-sm text-gray-500 mb-4">Export complete list of all approved beneficiaries</p>
                            <div class="flex justify-center space-x-2">
                                <a href="?generate=1&type=all&format=csv" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm">
                                    <i class="fas fa-file-csv mr-1"></i>CSV
                                </a>
                                <a href="?generate=1&type=all&format=print" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                                    <i class="fas fa-print mr-1"></i>Print
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 hover:border-da-green transition">
                        <div class="text-center">
                            <div class="inline-block bg-blue-100 rounded-full p-4 mb-3">
                                <i class="fas fa-chart-bar text-blue-600 text-2xl"></i>
                            </div>
                            <h3 class="font-bold text-gray-800 mb-2">Summary Statistics</h3>
                            <p class="text-sm text-gray-500 mb-4">View detailed analytics and charts</p>
                            <a href="dashboard.php" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                                <i class="fas fa-chart-line mr-1"></i>View Dashboard
                            </a>
                        </div>
                    </div>
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
                    <div class="flex flex-col md:flex-row gap-3">
                        <select name="region" required class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green bg-white">
                            <option value="">Select Region...</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?= htmlspecialchars($region->getCode()) ?>">
                                    Region <?= htmlspecialchars($region->getCode()) ?> - <?= htmlspecialchars($region->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="format" value="csv" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-file-csv mr-1"></i>Export CSV
                        </button>
                        <button type="submit" name="format" value="print" formtarget="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-print mr-1"></i>Print
                        </button>
                    </div>
                </form>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2 mt-4">
                    <?php 
                    $byRegion = $statistics->getBeneficiariesByRegion();
                    foreach ($regions as $region): 
                        $count = $byRegion[$region->getCode()] ?? 0;
                    ?>
                        <a href="?generate=1&type=region&region=<?= urlencode($region->getCode()) ?>&format=csv" 
                           class="p-3 bg-gray-50 rounded-lg hover:bg-green-50 transition text-center group"
                           title="Download <?= htmlspecialchars($region->getName()) ?> Report">
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

<?php renderFooter(); ?>
