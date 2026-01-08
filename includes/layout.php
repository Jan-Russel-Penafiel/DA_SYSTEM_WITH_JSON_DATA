<?php
// Header component
function renderHeader($currentPage = 'home') {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department of Agriculture - Fertilizer Beneficiary System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'da-green': '#228B22',
                        'da-dark-green': '#006400',
                        'da-light-green': '#90EE90',
                        'da-gold': '#FFD700',
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar-link.active {
            background: linear-gradient(90deg, rgba(34,139,34,0.2) 0%, rgba(34,139,34,0) 100%);
            border-left: 4px solid #228B22;
        }
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .modal {
            transition: opacity 0.25s ease;
        }
        .modal-content {
            transition: transform 0.25s ease;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Top Header -->
    <header class="bg-gradient-to-r from-da-dark-green to-da-green shadow-lg fixed top-0 left-0 right-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="flex items-center space-x-3">
                        <div class="bg-white p-2 rounded-full">
                            <i class="fas fa-seedling text-da-green text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-white text-xl font-bold">Department of Agriculture</h1>
                            <p class="text-da-light-green text-xs">Republic of the Philippines</p>
                        </div>
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="index.php" class="px-4 py-2 rounded-lg text-white hover:bg-white/20 transition flex items-center <?= $currentPage === 'home' ? 'bg-white/20' : '' ?>">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    <a href="dashboard.php" class="px-4 py-2 rounded-lg text-white hover:bg-white/20 transition flex items-center <?= $currentPage === 'dashboard' ? 'bg-white/20' : '' ?>">
                        <i class="fas fa-chart-line mr-2"></i>Dashboard
                    </a>
                    <a href="beneficiaries.php" class="px-4 py-2 rounded-lg text-white hover:bg-white/20 transition flex items-center <?= $currentPage === 'beneficiaries' ? 'bg-white/20' : '' ?>">
                        <i class="fas fa-users mr-2"></i>Beneficiaries
                    </a>
                    <a href="reports.php" class="px-4 py-2 rounded-lg text-white hover:bg-white/20 transition flex items-center <?= $currentPage === 'reports' ? 'bg-white/20' : '' ?>">
                        <i class="fas fa-file-alt mr-2"></i>Reports
                    </a>
                </nav>

                <div class="flex items-center space-x-3">
                    <span class="text-white text-sm hidden lg:block">
                        <i class="fas fa-calendar mr-1"></i><?= date('F d, Y') ?>
                    </span>
                    <button class="md:hidden text-white" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-da-dark-green border-t border-da-green">
            <div class="container mx-auto px-4 py-2">
                <a href="index.php" class="block py-2 text-white hover:text-da-gold"><i class="fas fa-home mr-2"></i>Home</a>
                <a href="dashboard.php" class="block py-2 text-white hover:text-da-gold"><i class="fas fa-chart-line mr-2"></i>Dashboard</a>
                <a href="beneficiaries.php" class="block py-2 text-white hover:text-da-gold"><i class="fas fa-users mr-2"></i>Beneficiaries</a>
                <a href="reports.php" class="block py-2 text-white hover:text-da-gold"><i class="fas fa-file-alt mr-2"></i>Reports</a>
            </div>
        </div>
    </header>

    <!-- Sub Header -->
    <div class="bg-da-gold py-2 mt-16 fixed top-0 left-0 right-0 z-40" style="margin-top: 64px;">
        <div class="container mx-auto px-4">
            <p class="text-da-dark-green text-center font-semibold text-xs sm:text-sm">
                <i class="fas fa-bullhorn mr-1 sm:mr-2"></i>
                <span class="hidden sm:inline">Fertilizer Beneficiary Management System - Approved Farmers Registry</span>
                <span class="sm:hidden">FBMS - Approved Farmers Registry</span>
            </p>
        </div>
    </div>

    <main class="pt-28 pb-8">
<?php
}

function renderFooter() {
?>
    </main>

    <!-- Footer -->
    <footer class="bg-da-dark-green text-white">
        <div class="container mx-auto px-4 py-6 sm:py-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <div class="text-center sm:text-left">
                    <div class="flex items-center justify-center sm:justify-start space-x-3 mb-3 sm:mb-4">
                        <div class="bg-white p-2 rounded-full">
                            <i class="fas fa-seedling text-da-green text-lg sm:text-xl"></i>
                        </div>
                        <span class="font-bold text-base sm:text-lg">DA Philippines</span>
                    </div>
                    <p class="text-da-light-green text-xs sm:text-sm">
                        Promoting agricultural development and food security for all Filipinos.
                    </p>
                </div>
                <div class="text-center sm:text-left">
                    <h4 class="font-bold mb-2 sm:mb-4 text-sm sm:text-base">Contact Information</h4>
                    <ul class="text-da-light-green text-xs sm:text-sm space-y-1 sm:space-y-2">
                        <li><i class="fas fa-map-marker-alt mr-1 sm:mr-2"></i><span class="hidden sm:inline">Elliptical Road, Diliman, </span>Quezon City</li>
                        <li><i class="fas fa-phone mr-1 sm:mr-2"></i>(02) 8273-2474</li>
                        <li><i class="fas fa-envelope mr-1 sm:mr-2"></i>osec@da.gov.ph</li>
                    </ul>
                </div>
                <div class="text-center sm:text-left sm:col-span-2 lg:col-span-1">
                    <h4 class="font-bold mb-2 sm:mb-4 text-sm sm:text-base">System Info</h4>
                    <ul class="text-da-light-green text-xs sm:text-sm space-y-1 sm:space-y-2 flex flex-wrap justify-center sm:justify-start lg:block gap-x-4 lg:gap-0">
                        <li><i class="fas fa-code-branch mr-1 sm:mr-2"></i>Version 2.0</li>
                        <li><i class="fas fa-clock mr-1 sm:mr-2"></i><?= date('h:i A') ?></li>
                        <li><i class="fas fa-server mr-1 sm:mr-2"></i>Online</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-da-green mt-6 sm:mt-8 pt-4 sm:pt-6 text-center text-da-light-green text-xs sm:text-sm">
                <p class="hidden sm:block">&copy; <?= date('Y') ?> Department of Agriculture - Fertilizer Beneficiary Management System. All rights reserved.</p>
                <p class="sm:hidden">&copy; <?= date('Y') ?> DA - FBMS. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Flash message auto-hide
        const flashMessage = document.getElementById('flashMessage');
        if (flashMessage) {
            setTimeout(() => {
                flashMessage.style.opacity = '0';
                setTimeout(() => flashMessage.remove(), 300);
            }, 5000);
        }
    </script>
</body>
</html>
<?php
}

function renderFlashMessage() {
    require_once __DIR__ . '/../classes/Session.php';
    $flash = Session::getFlash();
    if ($flash) {
        $bgColor = $flash['type'] === 'success' ? 'bg-green-500' : ($flash['type'] === 'error' ? 'bg-red-500' : 'bg-blue-500');
        $icon = $flash['type'] === 'success' ? 'fa-check-circle' : ($flash['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
        echo '<div id="flashMessage" class="fixed top-24 right-4 z-50 ' . $bgColor . ' text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2 fade-in transition-opacity duration-300">
                <i class="fas ' . $icon . '"></i>
                <span>' . htmlspecialchars($flash['message']) . '</span>
                <button onclick="this.parentElement.remove()" class="ml-4 hover:text-gray-200"><i class="fas fa-times"></i></button>
              </div>';
    }
}
?>
