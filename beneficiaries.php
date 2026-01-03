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
$fertilizerTypes = ['Urea (46-0-0)', 'Complete Fertilizer (14-14-14)', 'Ammonium Sulfate (21-0-0)', 
                    'Muriate of Potash (0-0-60)', 'Ammonium Phosphate (16-20-0)', 'Organic Fertilizer'];

// Handle CRUD Operations
$message = '';
$messageType = '';

// CREATE - Add new farmer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        // Get text values for province and municipality (from hidden fields or select text)
        $province = !empty($_POST['province_text']) ? $_POST['province_text'] : $_POST['province'];
        $municipality = !empty($_POST['municipality_text']) ? $_POST['municipality_text'] : $_POST['municipality'];
        
        $dateApproved = $_POST['date_approved'] ?? date('Y-m-d');
        
        $result = $repository->createFarmer([
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'middle_name' => $_POST['middle_name'] ?? '',
            'barangay' => $_POST['barangay'],
            'municipality' => $municipality,
            'province' => $province,
            'region_code' => $_POST['region_code'],
            'fertilizer_type' => $_POST['fertilizer_type'],
            'fertilizer_quantity' => $_POST['fertilizer_quantity'],
            'date_approved' => $dateApproved,
            'contact_number' => $_POST['contact_number'],
            'farm_area' => $_POST['farm_area']
        ]);
        
        if ($result) {
            // Redirect to the year tab of the newly added beneficiary
            $newYear = date('Y', strtotime($dateApproved));
            header("Location: beneficiaries.php?year=" . $newYear . "&msg=added");
            exit;
        } else {
            $message = 'Failed to add beneficiary.';
            $messageType = 'error';
        }
    }
    
    // UPDATE - Edit farmer
    if ($_POST['action'] === 'update' && isset($_POST['id'])) {
        // Get text values for province and municipality
        $province = !empty($_POST['province_text']) ? $_POST['province_text'] : $_POST['province'];
        $municipality = !empty($_POST['municipality_text']) ? $_POST['municipality_text'] : $_POST['municipality'];
        
        $dateApproved = $_POST['date_approved'];
        
        $result = $repository->updateFarmer((int)$_POST['id'], [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'middle_name' => $_POST['middle_name'] ?? '',
            'barangay' => $_POST['barangay'],
            'municipality' => $municipality,
            'province' => $province,
            'region_code' => $_POST['region_code'],
            'fertilizer_type' => $_POST['fertilizer_type'],
            'fertilizer_quantity' => $_POST['fertilizer_quantity'],
            'date_approved' => $dateApproved,
            'contact_number' => $_POST['contact_number'],
            'farm_area' => $_POST['farm_area']
        ]);
        
        if ($result) {
            // Redirect to the year tab of the updated beneficiary
            $updatedYear = date('Y', strtotime($dateApproved));
            header("Location: beneficiaries.php?year=" . $updatedYear . "&msg=updated");
            exit;
        } else {
            $message = 'Failed to update beneficiary.';
            $messageType = 'error';
        }
    }
    
    // DELETE - Remove farmer
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $result = $repository->deleteFarmer((int)$_POST['id']);
        
        if ($result) {
            $message = 'Beneficiary deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete beneficiary.';
            $messageType = 'error';
        }
    }
    
    // Reload repository to get updated data
    $repository = new FarmerRepository();
}

// Handle success messages from redirects
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') {
        $message = 'Beneficiary added successfully!';
        $messageType = 'success';
    } elseif ($_GET['msg'] === 'updated') {
        $message = 'Beneficiary updated successfully!';
        $messageType = 'success';
    }
}

// Get filter parameters
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedRegion = isset($_GET['region']) ? trim($_GET['region']) : '';
$selectedFertilizer = isset($_GET['fertilizer']) ? trim($_GET['fertilizer']) : '';
$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$selectedYear = isset($_GET['year']) ? trim($_GET['year']) : '';
$sortBy = isset($_GET['sort']) ? trim($_GET['sort']) : 'name';
$sortOrder = isset($_GET['order']) ? trim($_GET['order']) : 'asc';

// Get all available years from the database for year tabs
$allFarmersForYears = $repository->getAllFarmers();
$availableYears = [];
foreach ($allFarmersForYears as $farmer) {
    $year = date('Y', strtotime($farmer->getDateApproved()));
    if (!in_array($year, $availableYears)) {
        $availableYears[] = $year;
    }
}
rsort($availableYears); // Sort years in descending order (newest first)

// If year is selected, set date range filter
if (!empty($selectedYear)) {
    $dateFrom = $selectedYear . '-01-01';
    $dateTo = $selectedYear . '-12-31';
}

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
    'date_from' => empty($selectedYear) ? $dateFrom : '',
    'date_to' => empty($selectedYear) ? $dateTo : '',
    'year' => $selectedYear,
    'sort' => $sortBy,
    'order' => $sortOrder
]);
$queryString = http_build_query($queryParams);

renderHeader('beneficiaries');
renderFlashMessage();
?>

<div class="container mx-auto px-4">
    <!-- Alert Message -->
    <?php if (!empty($message)): ?>
    <div class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700' ?>">
        <div class="flex items-center">
            <i class="fas <?= $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
            <span><?= htmlspecialchars($message) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Page Title -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-da-dark-green flex items-center">
                <i class="fas fa-users mr-3"></i>Approved Beneficiaries
            </h1>
            <p class="text-gray-600 mt-1">Search and manage approved farmer beneficiaries</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-2">
            <button onclick="openAddModal()" class="bg-da-green hover:bg-da-dark-green text-white px-4 py-2 rounded-lg font-semibold transition flex items-center text-sm">
                <i class="fas fa-plus mr-2"></i>Add Beneficiary
            </button>
            <button onclick="exportBeneficiariesXLSX()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition flex items-center text-sm">
                <i class="fas fa-file-excel mr-2"></i>Export XLSX
            </button>
            <button onclick="printBeneficiaries()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition flex items-center text-sm">
                <i class="fas fa-print mr-2"></i>Print
            </button>
        </div>
    </div>

    <!-- Advanced Search Form -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <form method="GET" action="" id="searchForm">
            <!-- Preserve year selection when using search form -->
            <input type="hidden" name="year" value="<?= htmlspecialchars($selectedYear) ?>">
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

    <!-- Year Tabs -->
    <div class="bg-white rounded-xl shadow-md p-4 mb-6">
        <h3 class="text-sm font-semibold text-gray-600 mb-3">
            <i class="fas fa-calendar-alt mr-2"></i>Filter by Year:
        </h3>
        <div class="flex flex-wrap gap-2">
            <a href="?<?= http_build_query(array_merge($queryParams, ['year' => ''])) ?>" 
               class="px-4 py-2 rounded-lg text-sm font-medium <?= empty($selectedYear) ? 'bg-da-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?> transition">
                <i class="fas fa-list mr-1"></i>All Years
            </a>
            <?php foreach ($availableYears as $year): ?>
                <a href="?<?= http_build_query(array_merge($queryParams, ['year' => $year, 'date_from' => '', 'date_to' => ''])) ?>" 
                   class="px-4 py-2 rounded-lg text-sm font-medium <?= $selectedYear === (string)$year ? 'bg-da-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?> transition">
                    <i class="fas fa-calendar mr-1"></i><?= $year ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($selectedYear)): ?>
        <div class="mt-3 text-sm text-gray-600">
            <i class="fas fa-info-circle mr-1"></i>Showing beneficiaries approved in <strong><?= htmlspecialchars($selectedYear) ?></strong>
        </div>
        <?php endif; ?>
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
                <a href="?region=<?= urlencode($region->getCode()) ?>&search=<?= urlencode($searchTerm) ?>&year=<?= urlencode($selectedYear) ?>&sort=<?= urlencode($sortBy) ?>&order=<?= urlencode($sortOrder) ?>" 
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
            <?php if (!empty($searchTerm) || !empty($selectedRegion) || !empty($selectedFertilizer) || !empty($selectedYear)): ?>
                <span class="text-gray-500">
                    (Filtered<?php if (!empty($searchTerm)): ?> by "<?= htmlspecialchars($searchTerm) ?>"<?php endif; ?>
                    <?php if (!empty($selectedRegion)): ?> in Region <?= htmlspecialchars($selectedRegion) ?><?php endif; ?>
                    <?php if (!empty($selectedYear)): ?> for Year <?= htmlspecialchars($selectedYear) ?><?php endif; ?>)
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
                                    <div class="flex justify-center space-x-1">
                                        <a href="view.php?id=<?= $farmer->getId() ?>" class="inline-flex items-center px-2 py-1 bg-da-green text-white text-xs font-semibold rounded hover:bg-da-dark-green transition" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button onclick="openEditModal(<?= htmlspecialchars(json_encode($farmer->toArray())) ?>)" class="inline-flex items-center px-2 py-1 bg-blue-500 text-white text-xs font-semibold rounded hover:bg-blue-600 transition" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="openDeleteModal(<?= $farmer->getId() ?>, '<?= htmlspecialchars($farmer->getFullName()) ?>')" class="inline-flex items-center px-2 py-1 bg-red-500 text-white text-xs font-semibold rounded hover:bg-red-600 transition" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    </div>

<!-- Add/Edit Modal -->
<div id="farmerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="min-h-screen px-4 py-8 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl">
            <div class="bg-gradient-to-r from-da-dark-green to-da-green p-4 rounded-t-xl">
                <h2 id="modalTitle" class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-user-plus mr-2"></i>Add New Beneficiary
                </h2>
            </div>
            <form id="farmerForm" method="POST" action="" class="p-6">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="farmerId" value="">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Personal Information -->
                    <div class="md:col-span-2">
                        <h3 class="font-semibold text-gray-700 mb-2 border-b pb-1"><i class="fas fa-user mr-2"></i>Personal Information</h3>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" id="first_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                        <input type="text" name="middle_name" id="middle_name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" id="last_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number <span class="text-red-500">*</span></label>
                        <input type="text" name="contact_number" id="contact_number" required placeholder="09XXXXXXXXX"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green">
                    </div>
                    
                    <!-- Address Information -->
                    <div class="md:col-span-2 mt-4">
                        <h3 class="font-semibold text-gray-700 mb-2 border-b pb-1"><i class="fas fa-map-marker-alt mr-2"></i>Address Information</h3>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Region <span class="text-red-500">*</span></label>
                        <select name="region_code" id="region_code" required onchange="loadProvinces(this.value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green bg-white">
                            <option value="">Select Region</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?= htmlspecialchars($region->getCode()) ?>" data-psgc="<?= htmlspecialchars($region->getPsgcCode()) ?>">
                                    <?= htmlspecialchars($region->getCode()) ?> - <?= htmlspecialchars($region->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Province <span class="text-red-500">*</span></label>
                        <select name="province" id="province" required onchange="loadMunicipalities(this.value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green bg-white">
                            <option value="">Select Province</option>
                        </select>
                        <input type="hidden" name="province_text" id="province_text" value="">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Municipality/City <span class="text-red-500">*</span></label>
                        <select name="municipality" id="municipality" required onchange="loadBarangays(this.value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green bg-white">
                            <option value="">Select Municipality/City</option>
                        </select>
                        <input type="hidden" name="municipality_text" id="municipality_text" value="">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Barangay <span class="text-red-500">*</span></label>
                        <select name="barangay" id="barangay" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green bg-white">
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                    
                    <!-- Farm Information -->
                    <div class="md:col-span-2 mt-4">
                        <h3 class="font-semibold text-gray-700 mb-2 border-b pb-1"><i class="fas fa-tractor mr-2"></i>Farm & Fertilizer Information</h3>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Farm Area (hectares) <span class="text-red-500">*</span></label>
                        <input type="number" name="farm_area" id="farm_area" required step="0.1" min="0.1"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fertilizer Type <span class="text-red-500">*</span></label>
                        <select name="fertilizer_type" id="fertilizer_type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green bg-white">
                            <option value="">Select Type</option>
                            <?php foreach ($fertilizerTypes as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fertilizer Quantity (kg) <span class="text-red-500">*</span></label>
                        <input type="number" name="fertilizer_quantity" id="fertilizer_quantity" required min="1"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Approved <span class="text-red-500">*</span></label>
                        <input type="date" name="date_approved" id="date_approved" required value="<?= date('Y-m-d') ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-da-green text-white rounded-lg hover:bg-da-dark-green transition">
                        <i class="fas fa-save mr-2"></i>Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
        <div class="bg-red-500 p-4 rounded-t-xl">
            <h2 class="text-xl font-bold text-white flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>Confirm Delete
            </h2>
        </div>
        <div class="p-6">
            <p class="text-gray-700 mb-4">Are you sure you want to delete this beneficiary?</p>
            <p class="font-semibold text-gray-900 mb-4" id="deletefarmerName"></p>
            <p class="text-sm text-red-600 mb-4"><i class="fas fa-warning mr-1"></i>This action cannot be undone.</p>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteId" value="">
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// PSGC API Base URL
const PSGC_API = 'https://psgc.gitlab.io/api';

// Store data for edit mode
let editModeData = null;

// Load Provinces based on selected Region
async function loadProvinces(regionCode) {
    const provinceSelect = document.getElementById('province');
    const municipalitySelect = document.getElementById('municipality');
    const barangaySelect = document.getElementById('barangay');
    
    // Reset dependent dropdowns
    provinceSelect.innerHTML = '<option value="">Loading...</option>';
    municipalitySelect.innerHTML = '<option value="">Select Municipality/City</option>';
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
    
    if (!regionCode) {
        provinceSelect.innerHTML = '<option value="">Select Province</option>';
        return;
    }
    
    // Get PSGC code from selected option
    const regionSelect = document.getElementById('region_code');
    const selectedOption = regionSelect.options[regionSelect.selectedIndex];
    const psgcCode = selectedOption.getAttribute('data-psgc');
    
    if (!psgcCode) {
        provinceSelect.innerHTML = '<option value="">Select Province</option>';
        return;
    }
    
    try {
        const response = await fetch(`${PSGC_API}/regions/${psgcCode}/provinces.json`);
        const provinces = await response.json();
        
        provinceSelect.innerHTML = '<option value="">Select Province</option>';
        
        // Sort provinces alphabetically
        provinces.sort((a, b) => a.name.localeCompare(b.name));
        
        provinces.forEach(province => {
            const option = document.createElement('option');
            option.value = province.code;
            option.textContent = province.name;
            option.setAttribute('data-name', province.name);
            provinceSelect.appendChild(option);
        });
        
        // For NCR, also load cities/municipalities directly
        if (regionCode === 'NCR') {
            const citiesResponse = await fetch(`${PSGC_API}/regions/${psgcCode}/cities-municipalities.json`);
            const cities = await citiesResponse.json();
            
            provinceSelect.innerHTML = '<option value="">Select District/City</option>';
            cities.sort((a, b) => a.name.localeCompare(b.name));
            
            cities.forEach(city => {
                const option = document.createElement('option');
                option.value = city.code;
                option.textContent = city.name;
                option.setAttribute('data-name', city.name);
                option.setAttribute('data-is-ncr', 'true');
                provinceSelect.appendChild(option);
            });
        }
        
        // If in edit mode, set the saved value
        if (editModeData && editModeData.province) {
            setSelectByText(provinceSelect, editModeData.province);
            if (provinceSelect.value) {
                await loadMunicipalities(provinceSelect.value);
            }
        }
        
    } catch (error) {
        console.error('Error loading provinces:', error);
        provinceSelect.innerHTML = '<option value="">Error loading provinces</option>';
    }
}

// Load Municipalities/Cities based on selected Province
async function loadMunicipalities(provinceCode) {
    const municipalitySelect = document.getElementById('municipality');
    const barangaySelect = document.getElementById('barangay');
    const provinceSelect = document.getElementById('province');
    
    // Update hidden field with province name
    const selectedProvince = provinceSelect.options[provinceSelect.selectedIndex];
    if (selectedProvince) {
        document.getElementById('province_text').value = selectedProvince.getAttribute('data-name') || selectedProvince.textContent;
    }
    
    // Reset dependent dropdowns
    municipalitySelect.innerHTML = '<option value="">Loading...</option>';
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
    
    if (!provinceCode) {
        municipalitySelect.innerHTML = '<option value="">Select Municipality/City</option>';
        return;
    }
    
    // Check if NCR (province is actually a city)
    const isNCR = selectedProvince && selectedProvince.getAttribute('data-is-ncr') === 'true';
    
    if (isNCR) {
        // For NCR, load barangays directly since "province" is actually a city
        municipalitySelect.innerHTML = '<option value="">N/A for NCR</option>';
        document.getElementById('municipality_text').value = selectedProvince.getAttribute('data-name') || selectedProvince.textContent;
        await loadBarangaysForCity(provinceCode);
        return;
    }
    
    try {
        const response = await fetch(`${PSGC_API}/provinces/${provinceCode}/cities-municipalities.json`);
        const municipalities = await response.json();
        
        municipalitySelect.innerHTML = '<option value="">Select Municipality/City</option>';
        
        // Sort municipalities alphabetically
        municipalities.sort((a, b) => a.name.localeCompare(b.name));
        
        municipalities.forEach(municipality => {
            const option = document.createElement('option');
            option.value = municipality.code;
            option.textContent = municipality.name;
            option.setAttribute('data-name', municipality.name);
            municipalitySelect.appendChild(option);
        });
        
        // If in edit mode, set the saved value
        if (editModeData && editModeData.municipality) {
            setSelectByText(municipalitySelect, editModeData.municipality);
            if (municipalitySelect.value) {
                await loadBarangays(municipalitySelect.value);
            }
        }
        
    } catch (error) {
        console.error('Error loading municipalities:', error);
        municipalitySelect.innerHTML = '<option value="">Error loading municipalities</option>';
    }
}

// Load Barangays based on selected Municipality/City
async function loadBarangays(municipalityCode) {
    const barangaySelect = document.getElementById('barangay');
    const municipalitySelect = document.getElementById('municipality');
    
    // Update hidden field with municipality name
    const selectedMunicipality = municipalitySelect.options[municipalitySelect.selectedIndex];
    if (selectedMunicipality) {
        document.getElementById('municipality_text').value = selectedMunicipality.getAttribute('data-name') || selectedMunicipality.textContent;
    }
    
    barangaySelect.innerHTML = '<option value="">Loading...</option>';
    
    if (!municipalityCode) {
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        return;
    }
    
    try {
        const response = await fetch(`${PSGC_API}/cities-municipalities/${municipalityCode}/barangays.json`);
        const barangays = await response.json();
        
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        
        // Sort barangays alphabetically
        barangays.sort((a, b) => a.name.localeCompare(b.name));
        
        barangays.forEach(barangay => {
            const option = document.createElement('option');
            option.value = barangay.name;
            option.textContent = barangay.name;
            barangaySelect.appendChild(option);
        });
        
        // If in edit mode, set the saved value
        if (editModeData && editModeData.barangay) {
            setSelectByText(barangaySelect, editModeData.barangay);
            editModeData = null; // Clear edit mode data after setting all values
        }
        
    } catch (error) {
        console.error('Error loading barangays:', error);
        barangaySelect.innerHTML = '<option value="">Error loading barangays</option>';
    }
}

// Load Barangays for NCR cities
async function loadBarangaysForCity(cityCode) {
    const barangaySelect = document.getElementById('barangay');
    
    barangaySelect.innerHTML = '<option value="">Loading...</option>';
    
    try {
        const response = await fetch(`${PSGC_API}/cities-municipalities/${cityCode}/barangays.json`);
        const barangays = await response.json();
        
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        
        // Sort barangays alphabetically
        barangays.sort((a, b) => a.name.localeCompare(b.name));
        
        barangays.forEach(barangay => {
            const option = document.createElement('option');
            option.value = barangay.name;
            option.textContent = barangay.name;
            barangaySelect.appendChild(option);
        });
        
        // If in edit mode, set the saved value
        if (editModeData && editModeData.barangay) {
            setSelectByText(barangaySelect, editModeData.barangay);
            editModeData = null;
        }
        
    } catch (error) {
        console.error('Error loading barangays:', error);
        barangaySelect.innerHTML = '<option value="">Error loading barangays</option>';
    }
}

// Helper function to set select by text content
function setSelectByText(selectElement, text) {
    for (let i = 0; i < selectElement.options.length; i++) {
        if (selectElement.options[i].textContent.toLowerCase() === text.toLowerCase() ||
            selectElement.options[i].getAttribute('data-name')?.toLowerCase() === text.toLowerCase()) {
            selectElement.selectedIndex = i;
            return true;
        }
    }
    return false;
}

// Reset address dropdowns
function resetAddressDropdowns() {
    document.getElementById('province').innerHTML = '<option value="">Select Province</option>';
    document.getElementById('municipality').innerHTML = '<option value="">Select Municipality/City</option>';
    document.getElementById('barangay').innerHTML = '<option value="">Select Barangay</option>';
}

// Modal Functions
function openAddModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus mr-2"></i>Add New Beneficiary';
    document.getElementById('formAction').value = 'create';
    document.getElementById('farmerId').value = '';
    document.getElementById('farmerForm').reset();
    document.getElementById('date_approved').value = new Date().toISOString().split('T')[0];
    resetAddressDropdowns();
    editModeData = null;
    document.getElementById('farmerModal').classList.remove('hidden');
}

async function openEditModal(farmer) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit mr-2"></i>Edit Beneficiary';
    document.getElementById('formAction').value = 'update';
    document.getElementById('farmerId').value = farmer.id;
    document.getElementById('first_name').value = farmer.first_name;
    document.getElementById('middle_name').value = farmer.middle_name;
    document.getElementById('last_name').value = farmer.last_name;
    document.getElementById('contact_number').value = farmer.contact_number;
    document.getElementById('farm_area').value = farmer.farm_area;
    document.getElementById('fertilizer_type').value = farmer.fertilizer_type;
    document.getElementById('fertilizer_quantity').value = farmer.fertilizer_quantity;
    document.getElementById('date_approved').value = farmer.date_approved;
    
    // Store edit data for cascading dropdowns
    editModeData = {
        province: farmer.province,
        municipality: farmer.municipality,
        barangay: farmer.barangay
    };
    
    // Set region and trigger cascade
    document.getElementById('region_code').value = farmer.region_code;
    await loadProvinces(farmer.region_code);
    
    document.getElementById('farmerModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('farmerModal').classList.add('hidden');
}

function openDeleteModal(id, name) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deletefarmerName').textContent = name;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('farmerModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        closeDeleteModal();
    }
});

// Update hidden fields before form submission
document.getElementById('farmerForm').addEventListener('submit', function(e) {
    const provinceSelect = document.getElementById('province');
    const municipalitySelect = document.getElementById('municipality');
    
    // Get province text
    if (provinceSelect.selectedIndex > 0) {
        const selectedProvince = provinceSelect.options[provinceSelect.selectedIndex];
        document.getElementById('province_text').value = selectedProvince.getAttribute('data-name') || selectedProvince.textContent;
    }
    
    // Get municipality text
    if (municipalitySelect.selectedIndex > 0) {
        const selectedMunicipality = municipalitySelect.options[municipalitySelect.selectedIndex];
        document.getElementById('municipality_text').value = selectedMunicipality.getAttribute('data-name') || selectedMunicipality.textContent;
    }
});
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.1/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
// Store farmers data for PDF generation
const beneficiariesData = <?= json_encode(array_map(function($f) {
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
}, $farmers)) ?>;

const currentYear = '<?= htmlspecialchars($selectedYear) ?>';
const currentRegion = '<?= htmlspecialchars($selectedRegion) ?>';
const searchTerm = '<?= htmlspecialchars($searchTerm) ?>';

function exportBeneficiariesXLSX() {
    // Prepare data with headers
    const headers = ['#', 'RSBSA ID', 'Full Name', 'Location', 'Region', 'Fertilizer Type', 'Quantity (kg)', 'Farm Area (ha)', 'Date Approved'];
    const data = beneficiariesData.map((f, i) => [
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
    let filename = 'DA_Beneficiaries';
    if (currentYear) filename += '_' + currentYear;
    if (currentRegion) filename += '_' + currentRegion;
    filename += '.xlsx';
    
    // Save file
    XLSX.writeFile(wb, filename);
}

function printBeneficiaries() {
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
    
    let title = 'Fertilizer Beneficiaries Report';
    if (currentYear) title += ' (' + currentYear + ')';
    if (currentRegion) title += ' - Region ' + currentRegion;
    doc.text(title, centerX, 28, { align: 'center' });
    
    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(102, 102, 102);
    doc.text('Generated: ' + new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }), centerX, 34, { align: 'center' });
    
    // Table data
    const tableData = beneficiariesData.map((f, i) => [
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
    doc.text('Total Beneficiaries: ' + beneficiariesData.length, centerX, finalY, { align: 'center' });
    doc.setFontSize(8);
    doc.text('This is a computer-generated report from the DA Fertilizer Beneficiary Management System', centerX, finalY + 5, { align: 'center' });
    
    // Save PDF
    let filename = 'DA_Beneficiaries';
    if (currentYear) filename += '_' + currentYear;
    if (currentRegion) filename += '_' + currentRegion;
    doc.save(filename + '.pdf');
}
</script>

<?php renderFooter(); ?>
