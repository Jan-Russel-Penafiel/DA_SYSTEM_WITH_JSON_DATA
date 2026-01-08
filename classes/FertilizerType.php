<?php

class FertilizerType {
    private int $id;
    private string $code;
    private string $name;
    private string $composition;
    private float $pricePerKg;

    public function __construct(int $id, string $code, string $name, string $composition, float $pricePerKg) {
        $this->id = $id;
        $this->code = $code;
        $this->name = $name;
        $this->composition = $composition;
        $this->pricePerKg = $pricePerKg;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getCode(): string {
        return $this->code;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getComposition(): string {
        return $this->composition;
    }

    public function getPricePerKg(): float {
        return $this->pricePerKg;
    }

    public function getFullName(): string {
        return $this->name . ' (' . $this->composition . ')';
    }

    public static function getAllTypes(): array {
        return [
            new FertilizerType(1, 'UREA', 'Urea', '46-0-0', 28.50),
            new FertilizerType(2, 'COMPLETE', 'Complete Fertilizer', '14-14-14', 32.00),
            new FertilizerType(3, 'AMMSUL', 'Ammonium Sulfate', '21-0-0', 22.00),
            new FertilizerType(4, 'MOP', 'Muriate of Potash', '0-0-60', 45.00),
            new FertilizerType(5, 'AMMPHOS', 'Ammonium Phosphate', '16-20-0', 35.00),
            new FertilizerType(6, 'ORGANIC', 'Organic Fertilizer', 'Natural', 18.00),
        ];
    }

    public static function getByCode(string $code): ?FertilizerType {
        foreach (self::getAllTypes() as $type) {
            if ($type->getCode() === $code) {
                return $type;
            }
        }
        return null;
    }
}
