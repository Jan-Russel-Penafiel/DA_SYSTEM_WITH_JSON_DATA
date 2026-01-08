<?php
ob_start();
session_start();

require_once __DIR__ . '/classes/Region.php';
require_once __DIR__ . '/classes/Farmer.php';
require_once __DIR__ . '/classes/FarmerRepository.php';
require_once __DIR__ . '/classes/Exporter.php';
require_once __DIR__ . '/includes/layout.php';

// Initialize
$repository = new FarmerRepository();
$regions = Region::getAllRegions();
$fertilizerTypes = $repository->getUniqueFertilizerTypes();

// Get filter parameters
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedRegion = isset($_GET['region']) ? trim($_GET['region']) : '';
$selectedFertilizer = isset($_GET['fertilizer']) ? trim($_GET['fertilizer']) : '';
$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$sortBy = isset($_GET['sort']) ? trim($_GET['sort']) : 'name';
$sortOrder = isset($_GET['order']) ? trim($_GET['order']) : 'asc';

// Handle export
if (isset($_GET['export'])) {
    $allFarmers = $repository->searchFarmers($searchTerm, $selectedRegion, $selectedFertilizer, $dateFrom, $dateTo, $sortBy, $sortOrder);
    
    if ($_GET['export'] === 'csv') {
        Exporter::toCSV($allFarmers, 'da_beneficiaries');
        exit;
    } elseif ($_GET['export'] === 'print') {
        echo Exporter::toPrintable($allFarmers);
        exit;
    }
}

// Perform search
$allFarmers = $repository->searchFarmers($searchTerm, $selectedRegion, $selectedFertilizer, $dateFrom, $dateTo, $sortBy, $sortOrder);
$totalResults = count($allFarmers);
$farmers = $allFarmers;

// Build query string for filters
$queryParams = array_filter([
    'search' => $searchTerm,
    'region' => $selectedRegion,
    'fertilizer' => $selectedFertilizer,
    'date_from' => $dateFrom,
    'date_to' => $dateTo,
    'sort' => $sortBy,
    'order' => $sortOrder
]);
$queryString = http_build_query($queryParams);

renderHeader('beneficiaries');
renderFlashMessage();
?>

<div class="container mx-auto px-4">
    <!-- Page Title -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-da-dark-green flex items-center">
                <i class="fas fa-users mr-3"></i>Approved Beneficiaries
            </h1>
            <p class="text-gray-600 mt-1">Search and manage approved farmer beneficiaries</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-2">
            <a href="?<?= $queryString ?>&export=csv" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition flex items-center text-sm">
                <i class="fas fa-file-csv mr-2"></i>Export CSV
            </a>
            <a href="?<?= $queryString ?>&export=print" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition flex items-center text-sm">
                <i class="fas fa-print mr-2"></i>Print
            </a>
        </div>
    </div>

    <!-- Advanced Search Form -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <form method="GET" action="" id="searchForm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Search Input -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" id="search" name="search" value="<?= htmlspecialchars($searchTerm) ?>"
                            placeholder="Name, RSBSA ID, Location..."
                            class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <!-- Region Filter -->
                <div>
                    <label for="region" class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                    <select id="region" name="region" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green bg-white">
                        <option value="">All Regions</option>
                        <?php foreach ($regions as $region): ?>
                            <option value="<?= htmlspecialchars($region->getCode()) ?>" <?= $selectedRegion === $region->getCode() ? 'selected' : '' ?>>
                                <?= htmlspecialchars($region->getCode()) ?> - <?= htmlspecialchars($region->getName()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Fertilizer Type -->
                <div>
                    <label for="fertilizer" class="block text-sm font-medium text-gray-700 mb-1">Fertilizer Type</label>
                    <select id="fertilizer" name="fertilizer" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green bg-white">
                        <option value="">All Types</option>
                        <?php foreach ($fertilizerTypes as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= $selectedFertilizer === $type ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sort By -->
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                    <select id="sort" name="sort" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green bg-white">
                        <option value="date_approved" <?= $sortBy === 'date_approved' ? 'selected' : '' ?>>Date Approved</option>
                        <option value="name" <?= $sortBy === 'name' ? 'selected' : '' ?>>Name</option>
                        <option value="region" <?= $sortBy === 'region' ? 'selected' : '' ?>>Region</option>
                        <option value="fertilizer_qty" <?= $sortBy === 'fertilizer_qty' ? 'selected' : '' ?>>Fertilizer Quantity</option>
                        <option value="farm_area" <?= $sortBy === 'farm_area' ? 'selected' : '' ?>>Farm Area</option>
                    </select>
                </div>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($dateTo) ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green">
                </div>
                <div>
                    <label for="order" class="block text-sm font-medium text-gray-700 mb-1">Order</label>
                    <select id="order" name="order" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green bg-white">
                        <option value="desc" <?= $sortOrder === 'desc' ? 'selected' : '' ?>>Descending</option>
                        <option value="asc" <?= $sortOrder === 'asc' ? 'selected' : '' ?>>Ascending</option>
                    </select>
                </div>
                
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="bg-da-green hover:bg-da-dark-green text-white px-6 py-2 rounded-lg font-semibold transition flex items-center">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <a href="beneficiaries.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold transition flex items-center">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Quick Region Filters -->
    <div class="bg-white rounded-xl shadow-md p-4 mb-6">
        <h3 class="text-sm font-semibold text-gray-600 mb-3">
            <i class="fas fa-filter mr-2"></i>Quick Region Filters:
        </h3>
        <div class="flex flex-wrap gap-2">
            <a href="beneficiaries.php" class="px-3 py-1 rounded-full text-sm font-medium <?= empty($selectedRegion) ? 'bg-da-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?> transition">
                All
            </a>
            <?php foreach ($regions as $region): ?>
                <a href="?region=<?= urlencode($region->getCode()) ?>&search=<?= urlencode($searchTerm) ?>&sort=<?= urlencode($sortBy) ?>&order=<?= urlencode($sortOrder) ?>" 
                   class="px-3 py-1 rounded-full text-sm font-medium <?= $selectedRegion === $region->getCode() ? 'bg-da-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?> transition"
                   title="<?= htmlspecialchars($region->getName()) ?>">
                    <?= htmlspecialchars($region->getCode()) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Results Summary -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 bg-gray-50 rounded-lg p-4">
        <div class="text-gray-700">
            <span class="font-semibold"><?= number_format($totalResults) ?></span> result<?= $totalResults !== 1 ? 's' : '' ?> found
            <?php if (!empty($searchTerm) || !empty($selectedRegion) || !empty($selectedFertilizer)): ?>
                <span class="text-gray-500">
                    (Filtered<?php if (!empty($searchTerm)): ?> by "<?= htmlspecialchars($searchTerm) ?>"<?php endif; ?>
                    <?php if (!empty($selectedRegion)): ?> in Region <?= htmlspecialchars($selectedRegion) ?><?php endif; ?>)
                </span>
            <?php endif; ?>
        </div>
        <div class="text-gray-600 mt-2 md:mt-0">
            <i class="fas fa-sort-alpha-down mr-1"></i>Sorted alphabetically by name
        </div>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
        <?php if (empty($farmers)): ?>
            <div class="p-12 text-center">
                <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
                <p class="text-gray-500 text-lg">No beneficiaries found matching your search criteria.</p>
                <a href="beneficiaries.php" class="mt-4 inline-block text-da-green hover:text-da-dark-green font-semibold">
                    <i class="fas fa-arrow-left mr-1"></i>View all beneficiaries
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-da-dark-green text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">RSBSA ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Full Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Location</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Region</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Fertilizer</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Qty (kg)</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Area (ha)</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($farmers as $index => $farmer): ?>
                            <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' ?> hover:bg-green-50 transition">
                                <td class="px-4 py-3">
                                    <span class="text-sm font-mono text-da-dark-green font-semibold">
                                        <?= htmlspecialchars($farmer->getRsbsaId()) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="bg-da-light-green rounded-full p-2 mr-3">
                                            <i class="fas fa-user text-da-dark-green text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($farmer->getFullName()) ?></p>
                                            <p class="text-xs text-gray-500"><?= htmlspecialchars($farmer->getContactNumber()) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <p><?= htmlspecialchars($farmer->getBarangay()) ?></p>
                                    <p class="text-xs text-gray-400"><?= htmlspecialchars($farmer->getMunicipality()) ?>, <?= htmlspecialchars($farmer->getProvince()) ?></p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                        <?= htmlspecialchars($farmer->getRegionCode()) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($farmer->getFertilizerType()) ?></td>
                                <td class="px-4 py-3 text-center text-sm font-semibold text-gray-800"><?= number_format($farmer->getFertilizerQuantity()) ?></td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600"><?= number_format($farmer->getFarmArea(), 1) ?></td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600"><?= date('M d, Y', strtotime($farmer->getDateApproved())) ?></td>
                                <td class="px-4 py-3 text-center">
                                    <a href="view.php?id=<?= $farmer->getId() ?>" class="inline-flex items-center px-3 py-1 bg-da-green text-white text-xs font-semibold rounded-lg hover:bg-da-dark-green transition">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    </div>

<?php renderFooter(); ?>
