<?php

require_once __DIR__ . '/Farmer.php';
require_once __DIR__ . '/Region.php';
require_once __DIR__ . '/QRCodeGenerator.php';

class FarmerRepository {
    private array $farmers = [];
    private string $dataFile;
    private static ?FarmerRepository $instance = null;

    public function __construct() {
        $this->dataFile = __DIR__ . '/../data/farmers.json';
        $this->loadFromJson();
    }

    public static function getInstance(): FarmerRepository {
        if (self::$instance === null) {
            self::$instance = new FarmerRepository();
        }
        return self::$instance;
    }

    /**
     * Load farmers from JSON file
     */
    private function loadFromJson(): void {
        if (!file_exists($this->dataFile)) {
            $this->farmers = [];
            $this->saveToJson();
            return;
        }

        $jsonContent = file_get_contents($this->dataFile);
        $data = json_decode($jsonContent, true);

        if (!is_array($data)) {
            $this->farmers = [];
            return;
        }

        $this->farmers = [];
        foreach ($data as $item) {
            $this->farmers[] = new Farmer(
                (int)$item['id'],
                $item['rsbsa_id'],
                $item['first_name'],
                $item['last_name'],
                $item['middle_name'],
                $item['barangay'],
                $item['municipality'],
                $item['province'],
                $item['region_code'],
                $item['fertilizer_type'],
                (float)$item['fertilizer_quantity'],
                $item['date_approved'],
                $item['status'],
                $item['contact_number'],
                (float)$item['farm_area'],
                $item['verification_token'] ?? ''
            );
        }
    }

    /**
     * Save farmers to JSON file
     */
    private function saveToJson(): bool {
        $data = [];
        foreach ($this->farmers as $farmer) {
            $data[] = [
                'id' => $farmer->getId(),
                'rsbsa_id' => $farmer->getRsbsaId(),
                'first_name' => $farmer->getFirstName(),
                'last_name' => $farmer->getLastName(),
                'middle_name' => $farmer->getMiddleName(),
                'barangay' => $farmer->getBarangay(),
                'municipality' => $farmer->getMunicipality(),
                'province' => $farmer->getProvince(),
                'region_code' => $farmer->getRegionCode(),
                'fertilizer_type' => $farmer->getFertilizerType(),
                'fertilizer_quantity' => $farmer->getFertilizerQuantity(),
                'date_approved' => $farmer->getDateApproved(),
                'status' => $farmer->getStatus(),
                'contact_number' => $farmer->getContactNumber(),
                'farm_area' => $farmer->getFarmArea(),
                'verification_token' => $farmer->getVerificationToken()
            ];
        }

        $dir = dirname($this->dataFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }

    /**
     * Get next available ID
     */
    private function getNextId(): int {
        $maxId = 0;
        foreach ($this->farmers as $farmer) {
            if ($farmer->getId() > $maxId) {
                $maxId = $farmer->getId();
            }
        }
        return $maxId + 1;
    }

    /**
     * Generate RSBSA ID
     */
    private function generateRsbsaId(string $regionCode): string {
        $count = 1;
        foreach ($this->farmers as $farmer) {
            if ($farmer->getRegionCode() === $regionCode) {
                $count++;
            }
        }
        return 'RSBSA-' . $regionCode . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    // ==================== CRUD OPERATIONS ====================

    /**
     * CREATE - Add a new farmer
     */
    public function createFarmer(array $data): ?Farmer {
        $id = $this->getNextId();
        $rsbsaId = $this->generateRsbsaId($data['region_code']);
        
        // Generate verification token
        $verificationToken = QRCodeGenerator::generateVerificationToken($id, $rsbsaId);

        $farmer = new Farmer(
            $id,
            $rsbsaId,
            trim($data['first_name']),
            trim($data['last_name']),
            trim($data['middle_name'] ?? ''),
            trim($data['barangay']),
            trim($data['municipality']),
            trim($data['province']),
            trim($data['region_code']),
            trim($data['fertilizer_type']),
            (float)$data['fertilizer_quantity'],
            $data['date_approved'] ?? date('Y-m-d'),
            $data['status'] ?? 'Approved',
            trim($data['contact_number']),
            (float)$data['farm_area'],
            $verificationToken
        );

        $this->farmers[] = $farmer;
        
        if ($this->saveToJson()) {
            return $farmer;
        }
        
        // Remove farmer if save failed
        array_pop($this->farmers);
        return null;
    }

    /**
     * READ - Get all farmers
     */
    public function getAllFarmers(): array {
        return $this->farmers;
    }

    /**
     * READ - Get farmer by ID
     */
    public function getFarmerById(int $id): ?Farmer {
        foreach ($this->farmers as $farmer) {
            if ($farmer->getId() === $id) {
                return $farmer;
            }
        }
        return null;
    }

    /**
     * READ - Get farmer by RSBSA ID
     */
    public function getFarmerByRsbsaId(string $rsbsaId): ?Farmer {
        foreach ($this->farmers as $farmer) {
            if ($farmer->getRsbsaId() === $rsbsaId) {
                return $farmer;
            }
        }
        return null;
    }

    /**
     * UPDATE - Update an existing farmer
     */
    public function updateFarmer(int $id, array $data): bool {
        foreach ($this->farmers as $index => $farmer) {
            if ($farmer->getId() === $id) {
                $updatedFarmer = new Farmer(
                    $id,
                    $farmer->getRsbsaId(), // Keep original RSBSA ID
                    trim($data['first_name'] ?? $farmer->getFirstName()),
                    trim($data['last_name'] ?? $farmer->getLastName()),
                    trim($data['middle_name'] ?? $farmer->getMiddleName()),
                    trim($data['barangay'] ?? $farmer->getBarangay()),
                    trim($data['municipality'] ?? $farmer->getMunicipality()),
                    trim($data['province'] ?? $farmer->getProvince()),
                    trim($data['region_code'] ?? $farmer->getRegionCode()),
                    trim($data['fertilizer_type'] ?? $farmer->getFertilizerType()),
                    (float)($data['fertilizer_quantity'] ?? $farmer->getFertilizerQuantity()),
                    $data['date_approved'] ?? $farmer->getDateApproved(),
                    $data['status'] ?? $farmer->getStatus(),
                    trim($data['contact_number'] ?? $farmer->getContactNumber()),
                    (float)($data['farm_area'] ?? $farmer->getFarmArea()),
                    $farmer->getVerificationToken() // Keep original verification token
                );

                $this->farmers[$index] = $updatedFarmer;
                return $this->saveToJson();
            }
        }
        return false;
    }

    /**
     * DELETE - Remove a farmer by ID
     */
    public function deleteFarmer(int $id): bool {
        foreach ($this->farmers as $index => $farmer) {
            if ($farmer->getId() === $id) {
                array_splice($this->farmers, $index, 1);
                return $this->saveToJson();
            }
        }
        return false;
    }

    // ==================== SEARCH & FILTER ====================

    /**
     * Search farmers with filters
     */
    public function searchFarmers(
        string $searchTerm = '', 
        string $regionCode = '', 
        string $fertilizerType = '',
        string $dateFrom = '',
        string $dateTo = '',
        string $sortBy = 'date_approved',
        string $sortOrder = 'desc'
    ): array {
        $results = $this->farmers;

        // Filter by region
        if (!empty($regionCode)) {
            $results = array_filter($results, function(Farmer $farmer) use ($regionCode) {
                return $farmer->getRegionCode() === $regionCode;
            });
        }

        // Filter by fertilizer type
        if (!empty($fertilizerType)) {
            $results = array_filter($results, function(Farmer $farmer) use ($fertilizerType) {
                return str_contains(strtolower($farmer->getFertilizerType()), strtolower($fertilizerType));
            });
        }

        // Filter by date range
        if (!empty($dateFrom)) {
            $results = array_filter($results, function(Farmer $farmer) use ($dateFrom) {
                return strtotime($farmer->getDateApproved()) >= strtotime($dateFrom);
            });
        }
        if (!empty($dateTo)) {
            $results = array_filter($results, function(Farmer $farmer) use ($dateTo) {
                return strtotime($farmer->getDateApproved()) <= strtotime($dateTo);
            });
        }

        // Filter by search term
        if (!empty($searchTerm)) {
            $searchTerm = strtolower($searchTerm);
            $results = array_filter($results, function(Farmer $farmer) use ($searchTerm) {
                return str_contains(strtolower($farmer->getFullName()), $searchTerm) ||
                       str_contains(strtolower($farmer->getRsbsaId()), $searchTerm) ||
                       str_contains(strtolower($farmer->getMunicipality()), $searchTerm) ||
                       str_contains(strtolower($farmer->getProvince()), $searchTerm) ||
                       str_contains(strtolower($farmer->getBarangay()), $searchTerm) ||
                       str_contains(strtolower($farmer->getContactNumber()), $searchTerm);
            });
        }

        // Sort results
        $results = array_values($results);
        usort($results, function(Farmer $a, Farmer $b) use ($sortBy, $sortOrder) {
            $valA = '';
            $valB = '';
            
            switch ($sortBy) {
                case 'name':
                    $valA = $a->getLastName();
                    $valB = $b->getLastName();
                    break;
                case 'region':
                    $valA = $a->getRegionCode();
                    $valB = $b->getRegionCode();
                    break;
                case 'fertilizer_qty':
                    $valA = $a->getFertilizerQuantity();
                    $valB = $b->getFertilizerQuantity();
                    break;
                case 'farm_area':
                    $valA = $a->getFarmArea();
                    $valB = $b->getFarmArea();
                    break;
                case 'date_approved':
                default:
                    $valA = strtotime($a->getDateApproved());
                    $valB = strtotime($b->getDateApproved());
                    break;
            }
            
            if ($sortOrder === 'asc') {
                return $valA <=> $valB;
            }
            return $valB <=> $valA;
        });

        return $results;
    }

    /**
     * Get farmers by region
     */
    public function getFarmersByRegion(string $regionCode): array {
        return array_filter($this->farmers, function(Farmer $farmer) use ($regionCode) {
            return $farmer->getRegionCode() === $regionCode;
        });
    }

    // ==================== STATISTICS ====================

    public function getTotalFarmers(): int {
        return count($this->farmers);
    }

    public function getTotalByRegion(): array {
        $totals = [];
        foreach ($this->farmers as $farmer) {
            $regionCode = $farmer->getRegionCode();
            if (!isset($totals[$regionCode])) {
                $totals[$regionCode] = 0;
            }
            $totals[$regionCode]++;
        }
        return $totals;
    }

    public function getTotalFertilizerDistributed(): float {
        $total = 0;
        foreach ($this->farmers as $farmer) {
            $total += $farmer->getFertilizerQuantity();
        }
        return $total;
    }

    public function getUniqueFertilizerTypes(): array {
        $types = [];
        foreach ($this->farmers as $farmer) {
            $types[$farmer->getFertilizerType()] = true;
        }
        return array_keys($types);
    }

    public function getUniqueProvinces(): array {
        $provinces = [];
        foreach ($this->farmers as $farmer) {
            $provinces[$farmer->getProvince()] = true;
        }
        ksort($provinces);
        return array_keys($provinces);
    }

    public function getUniqueMunicipalities(): array {
        $municipalities = [];
        foreach ($this->farmers as $farmer) {
            $municipalities[$farmer->getMunicipality()] = true;
        }
        ksort($municipalities);
        return array_keys($municipalities);
    }

    // ==================== QR CODE & VERIFICATION ====================

    /**
     * Get farmer by verification token
     */
    public function getFarmerByToken(string $token): ?Farmer {
        foreach ($this->farmers as $farmer) {
            if ($farmer->getVerificationToken() === $token) {
                return $farmer;
            }
        }
        return null;
    }

    /**
     * Verify a farmer's token
     */
    public function verifyFarmerToken(int $farmerId, string $rsbsaId, string $token): array {
        $farmer = $this->getFarmerById($farmerId);
        
        if (!$farmer) {
            return ['valid' => false, 'message' => 'Farmer not found', 'farmer' => null];
        }
        
        if ($farmer->getRsbsaId() !== $rsbsaId) {
            return ['valid' => false, 'message' => 'RSBSA ID mismatch', 'farmer' => null];
        }
        
        if ($farmer->getVerificationToken() !== $token) {
            return ['valid' => false, 'message' => 'Invalid verification token', 'farmer' => null];
        }
        
        // Check verification using QRCodeGenerator
        $isValid = QRCodeGenerator::verifyToken($farmerId, $rsbsaId, $token);
        
        if (!$isValid) {
            return ['valid' => false, 'message' => 'Token verification failed', 'farmer' => null];
        }
        
        return [
            'valid' => true, 
            'message' => 'Beneficiary verified successfully',
            'farmer' => $farmer,
            'status' => $farmer->getStatus()
        ];
    }

    /**
     * Generate tokens for existing farmers without tokens
     */
    public function migrateExistingFarmersTokens(): int {
        $count = 0;
        $modified = false;
        
        foreach ($this->farmers as $index => $farmer) {
            if (empty($farmer->getVerificationToken())) {
                $token = QRCodeGenerator::generateVerificationToken($farmer->getId(), $farmer->getRsbsaId());
                
                // Create new farmer with token
                $updatedFarmer = new Farmer(
                    $farmer->getId(),
                    $farmer->getRsbsaId(),
                    $farmer->getFirstName(),
                    $farmer->getLastName(),
                    $farmer->getMiddleName(),
                    $farmer->getBarangay(),
                    $farmer->getMunicipality(),
                    $farmer->getProvince(),
                    $farmer->getRegionCode(),
                    $farmer->getFertilizerType(),
                    $farmer->getFertilizerQuantity(),
                    $farmer->getDateApproved(),
                    $farmer->getStatus(),
                    $farmer->getContactNumber(),
                    $farmer->getFarmArea(),
                    $token
                );
                
                $this->farmers[$index] = $updatedFarmer;
                $count++;
                $modified = true;
            }
        }
        
        if ($modified) {
            $this->saveToJson();
        }
        
        return $count;
    }

    /**
     * Reload data from JSON (useful after external changes)
     */
    public function reload(): void {
        $this->loadFromJson();
    }
}
