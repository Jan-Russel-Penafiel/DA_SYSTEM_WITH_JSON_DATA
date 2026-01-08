<?php

class Farmer {
    private int $id;
    private string $rsbsaId;
    private string $firstName;
    private string $lastName;
    private string $middleName;
    private string $barangay;
    private string $municipality;
    private string $province;
    private string $regionCode;
    private string $fertilizerType;
    private float $fertilizerQuantity;
    private string $dateApproved;
    private string $status;
    private string $contactNumber;
    private float $farmArea;

    public function __construct(
        int $id,
        string $rsbsaId,
        string $firstName,
        string $lastName,
        string $middleName,
        string $barangay,
        string $municipality,
        string $province,
        string $regionCode,
        string $fertilizerType,
        float $fertilizerQuantity,
        string $dateApproved,
        string $status,
        string $contactNumber,
        float $farmArea
    ) {
        $this->id = $id;
        $this->rsbsaId = $rsbsaId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->middleName = $middleName;
        $this->barangay = $barangay;
        $this->municipality = $municipality;
        $this->province = $province;
        $this->regionCode = $regionCode;
        $this->fertilizerType = $fertilizerType;
        $this->fertilizerQuantity = $fertilizerQuantity;
        $this->dateApproved = $dateApproved;
        $this->status = $status;
        $this->contactNumber = $contactNumber;
        $this->farmArea = $farmArea;
    }

    // Getters
    public function getId(): int {
        return $this->id;
    }

    public function getRsbsaId(): string {
        return $this->rsbsaId;
    }

    public function getFirstName(): string {
        return $this->firstName;
    }

    public function getLastName(): string {
        return $this->lastName;
    }

    public function getMiddleName(): string {
        return $this->middleName;
    }

    public function getFullName(): string {
        return $this->lastName . ', ' . $this->firstName . ' ' . $this->middleName;
    }

    public function getBarangay(): string {
        return $this->barangay;
    }

    public function getMunicipality(): string {
        return $this->municipality;
    }

    public function getProvince(): string {
        return $this->province;
    }

    public function getRegionCode(): string {
        return $this->regionCode;
    }

    public function getFullAddress(): string {
        return $this->barangay . ', ' . $this->municipality . ', ' . $this->province;
    }

    public function getFertilizerType(): string {
        return $this->fertilizerType;
    }

    public function getFertilizerQuantity(): float {
        return $this->fertilizerQuantity;
    }

    public function getDateApproved(): string {
        return $this->dateApproved;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getContactNumber(): string {
        return $this->contactNumber;
    }

    public function getFarmArea(): float {
        return $this->farmArea;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'rsbsa_id' => $this->rsbsaId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'middle_name' => $this->middleName,
            'full_name' => $this->getFullName(),
            'barangay' => $this->barangay,
            'municipality' => $this->municipality,
            'province' => $this->province,
            'region_code' => $this->regionCode,
            'full_address' => $this->getFullAddress(),
            'fertilizer_type' => $this->fertilizerType,
            'fertilizer_quantity' => $this->fertilizerQuantity,
            'date_approved' => $this->dateApproved,
            'status' => $this->status,
            'contact_number' => $this->contactNumber,
            'farm_area' => $this->farmArea,
        ];
    }
}
