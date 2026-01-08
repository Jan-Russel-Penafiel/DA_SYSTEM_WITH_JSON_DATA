<?php

require_once __DIR__ . '/Farmer.php';
require_once __DIR__ . '/Region.php';

class FarmerRepository {
    private array $farmers = [];
    private static ?FarmerRepository $instance = null;

    public function __construct() {
        $this->loadMockData();
    }

    public static function getInstance(): FarmerRepository {
        if (self::$instance === null) {
            self::$instance = new FarmerRepository();
        }
        return self::$instance;
    }

    private function loadMockData(): void {
        $mockData = [
            // NCR
            ['NCR', 'Bagong Silang', 'Caloocan City', 'Metro Manila'],
            ['NCR', 'Novaliches', 'Quezon City', 'Metro Manila'],
            ['NCR', 'Fairview', 'Quezon City', 'Metro Manila'],
            
            // CAR
            ['CAR', 'Poblacion', 'La Trinidad', 'Benguet'],
            ['CAR', 'Buyagan', 'La Trinidad', 'Benguet'],
            ['CAR', 'Pico', 'La Trinidad', 'Benguet'],
            ['CAR', 'Alapang', 'La Trinidad', 'Benguet'],
            ['CAR', 'Balili', 'La Trinidad', 'Benguet'],
            
            // Region I
            ['I', 'San Nicolas', 'Batac', 'Ilocos Norte'],
            ['I', 'Poblacion', 'Laoag City', 'Ilocos Norte'],
            ['I', 'Baay', 'Batac', 'Ilocos Norte'],
            ['I', 'Lacub', 'Batac', 'Ilocos Norte'],
            ['I', 'Quiling Norte', 'Batac', 'Ilocos Norte'],
            
            // Region II
            ['II', 'Centro', 'Tuguegarao City', 'Cagayan'],
            ['II', 'Annafunan', 'Tuguegarao City', 'Cagayan'],
            ['II', 'Carig', 'Tuguegarao City', 'Cagayan'],
            ['II', 'Ugac Norte', 'Tuguegarao City', 'Cagayan'],
            ['II', 'Leonarda', 'Tuguegarao City', 'Cagayan'],
            
            // Region III
            ['III', 'Dolores', 'San Fernando', 'Pampanga'],
            ['III', 'Maimpis', 'San Fernando', 'Pampanga'],
            ['III', 'Sindalan', 'San Fernando', 'Pampanga'],
            ['III', 'Bical', 'Mabalacat', 'Pampanga'],
            ['III', 'San Francisco', 'Mabalacat', 'Pampanga'],
            ['III', 'Camachiles', 'Mabalacat', 'Pampanga'],
            
            // Region IV-A
            ['IV-A', 'Barangay I', 'Calamba', 'Laguna'],
            ['IV-A', 'Parian', 'Calamba', 'Laguna'],
            ['IV-A', 'Canlubang', 'Calamba', 'Laguna'],
            ['IV-A', 'Mayapa', 'Calamba', 'Laguna'],
            ['IV-A', 'Real', 'Calamba', 'Laguna'],
            ['IV-A', 'Bucal', 'Calamba', 'Laguna'],
            
            // Region IV-B
            ['IV-B', 'Poblacion', 'Calapan', 'Oriental Mindoro'],
            ['IV-B', 'Parang', 'Calapan', 'Oriental Mindoro'],
            ['IV-B', 'Guinobatan', 'Calapan', 'Oriental Mindoro'],
            ['IV-B', 'Managpi', 'Calapan', 'Oriental Mindoro'],
            
            // Region V
            ['V', 'Daraga', 'Legazpi City', 'Albay'],
            ['V', 'Rawis', 'Legazpi City', 'Albay'],
            ['V', 'Bagumbayan', 'Legazpi City', 'Albay'],
            ['V', 'Bonot', 'Legazpi City', 'Albay'],
            ['V', 'Cruzada', 'Legazpi City', 'Albay'],
            
            // Region VI
            ['VI', 'Arevalo', 'Iloilo City', 'Iloilo'],
            ['VI', 'Jaro', 'Iloilo City', 'Iloilo'],
            ['VI', 'Mandurriao', 'Iloilo City', 'Iloilo'],
            ['VI', 'Molo', 'Iloilo City', 'Iloilo'],
            ['VI', 'La Paz', 'Iloilo City', 'Iloilo'],
            ['VI', 'Lapuz', 'Iloilo City', 'Iloilo'],
            
            // Region VII
            ['VII', 'Lahug', 'Cebu City', 'Cebu'],
            ['VII', 'Mabolo', 'Cebu City', 'Cebu'],
            ['VII', 'Talamban', 'Cebu City', 'Cebu'],
            ['VII', 'Banilad', 'Cebu City', 'Cebu'],
            ['VII', 'Guadalupe', 'Cebu City', 'Cebu'],
            
            // Region VIII
            ['VIII', 'Abucay', 'Tacloban City', 'Leyte'],
            ['VIII', 'Apitong', 'Tacloban City', 'Leyte'],
            ['VIII', 'Cabalawan', 'Tacloban City', 'Leyte'],
            ['VIII', 'Sagkahan', 'Tacloban City', 'Leyte'],
            ['VIII', 'Marasbaras', 'Tacloban City', 'Leyte'],
            
            // Region IX
            ['IX', 'Baliwasan', 'Zamboanga City', 'Zamboanga del Sur'],
            ['IX', 'Canelar', 'Zamboanga City', 'Zamboanga del Sur'],
            ['IX', 'Tetuan', 'Zamboanga City', 'Zamboanga del Sur'],
            ['IX', 'Sta. Maria', 'Zamboanga City', 'Zamboanga del Sur'],
            ['IX', 'Pasonanca', 'Zamboanga City', 'Zamboanga del Sur'],
            
            // Region X
            ['X', 'Bulua', 'Cagayan de Oro', 'Misamis Oriental'],
            ['X', 'Carmen', 'Cagayan de Oro', 'Misamis Oriental'],
            ['X', 'Kauswagan', 'Cagayan de Oro', 'Misamis Oriental'],
            ['X', 'Lapasan', 'Cagayan de Oro', 'Misamis Oriental'],
            ['X', 'Macasandig', 'Cagayan de Oro', 'Misamis Oriental'],
            
            // Region XI
            ['XI', 'Agdao', 'Davao City', 'Davao del Sur'],
            ['XI', 'Buhangin', 'Davao City', 'Davao del Sur'],
            ['XI', 'Calinan', 'Davao City', 'Davao del Sur'],
            ['XI', 'Toril', 'Davao City', 'Davao del Sur'],
            ['XI', 'Talomo', 'Davao City', 'Davao del Sur'],
            ['XI', 'Matina', 'Davao City', 'Davao del Sur'],
            
            // Region XII
            ['XII', 'Apopong', 'General Santos', 'South Cotabato'],
            ['XII', 'Bula', 'General Santos', 'South Cotabato'],
            ['XII', 'Calumpang', 'General Santos', 'South Cotabato'],
            ['XII', 'Dadiangas', 'General Santos', 'South Cotabato'],
            ['XII', 'Fatima', 'General Santos', 'South Cotabato'],
            
            // Region XIII
            ['XIII', 'Ampayon', 'Butuan City', 'Agusan del Norte'],
            ['XIII', 'Bancasi', 'Butuan City', 'Agusan del Norte'],
            ['XIII', 'Banza', 'Butuan City', 'Agusan del Norte'],
            ['XIII', 'Libertad', 'Butuan City', 'Agusan del Norte'],
            ['XIII', 'Doongan', 'Butuan City', 'Agusan del Norte'],
            
            // BARMM
            ['BARMM', 'Tamontaka', 'Cotabato City', 'Maguindanao'],
            ['BARMM', 'Rosary Heights', 'Cotabato City', 'Maguindanao'],
            ['BARMM', 'Poblacion', 'Cotabato City', 'Maguindanao'],
            ['BARMM', 'Bagua', 'Cotabato City', 'Maguindanao'],
        ];

        $firstNames = ['Juan', 'Pedro', 'Maria', 'Jose', 'Rosa', 'Antonio', 'Carmen', 'Ricardo', 'Luz', 'Manuel', 
                       'Elena', 'Roberto', 'Ana', 'Carlos', 'Teresa', 'Francisco', 'Lourdes', 'Eduardo', 'Gloria', 'Miguel',
                       'Rodrigo', 'Josefa', 'Ramon', 'Corazon', 'Fernando', 'Imelda', 'Benigno', 'Cynthia', 'Renato', 'Leticia'];
        $lastNames = ['Dela Cruz', 'Santos', 'Reyes', 'Garcia', 'Mendoza', 'Torres', 'Flores', 'Gonzales', 'Ramos', 'Cruz', 
                      'Bautista', 'Aquino', 'Fernandez', 'Lopez', 'Martinez', 'Perez', 'Rivera', 'Villanueva', 'Castro', 'Domingo',
                      'Mercado', 'Tolentino', 'Pascual', 'Manalo', 'Aguilar', 'Santiago', 'Dizon', 'Morales', 'Navarro', 'Espinosa'];
        $middleNames = ['Abella', 'Buenaventura', 'Concepcion', 'Delos Santos', 'Evangelista', 'Francisco', 'Galang', 'Hernandez', 
                        'Ignacio', 'Jacinto', 'Katigbak', 'Lacson', 'Macapagal', 'Natividad', 'Ocampo', 'Panganiban', 'Quizon', 
                        'Romulo', 'Salazar', 'Tanedo'];
        $fertilizerTypes = ['Urea (46-0-0)', 'Complete Fertilizer (14-14-14)', 'Ammonium Sulfate (21-0-0)', 
                            'Muriate of Potash (0-0-60)', 'Ammonium Phosphate (16-20-0)', 'Organic Fertilizer'];
        $cropTypes = ['Rice', 'Corn', 'Vegetables', 'Coconut', 'Sugarcane', 'Banana', 'Mango', 'Coffee'];

        $id = 1;
        foreach ($mockData as $data) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $middleName = $middleNames[array_rand($middleNames)];
            $fertilizerType = $fertilizerTypes[array_rand($fertilizerTypes)];
            $fertilizerQty = rand(3, 10) * 5;
            $farmArea = rand(5, 50) / 10;
            $dateApproved = date('Y-m-d', strtotime('-' . rand(1, 365) . ' days'));
            $contactNumber = '09' . rand(10, 99) . rand(100, 999) . rand(1000, 9999);

            $rsbsaId = 'RSBSA-' . $data[0] . '-' . str_pad($id, 6, '0', STR_PAD_LEFT);

            $this->farmers[] = new Farmer(
                $id,
                $rsbsaId,
                $firstName,
                $lastName,
                $middleName,
                $data[1],
                $data[2],
                $data[3],
                $data[0],
                $fertilizerType,
                $fertilizerQty,
                $dateApproved,
                'Approved',
                $contactNumber,
                $farmArea
            );
            $id++;
        }
    }

    public function getAllFarmers(): array {
        return $this->farmers;
    }

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

        // Filter by search term (name, RSBSA ID, municipality, province)
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

    public function getFarmerById(int $id): ?Farmer {
        foreach ($this->farmers as $farmer) {
            if ($farmer->getId() === $id) {
                return $farmer;
            }
        }
        return null;
    }

    public function getFarmerByRsbsaId(string $rsbsaId): ?Farmer {
        foreach ($this->farmers as $farmer) {
            if ($farmer->getRsbsaId() === $rsbsaId) {
                return $farmer;
            }
        }
        return null;
    }

    public function getFarmersByRegion(string $regionCode): array {
        return array_filter($this->farmers, function(Farmer $farmer) use ($regionCode) {
            return $farmer->getRegionCode() === $regionCode;
        });
    }

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
}
