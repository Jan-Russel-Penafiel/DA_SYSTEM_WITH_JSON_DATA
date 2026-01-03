# 🌾 DA-FBMS (Fertilizer Beneficiary Management System)

A web-based management system for tracking and managing approved farmer beneficiaries of the Department of Agriculture's fertilizer distribution program across all regions of the Philippines. Features QR code verification for secure beneficiary authentication.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Chart.js](https://img.shields.io/badge/Chart.js-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white)
![JSON](https://img.shields.io/badge/JSON-000000?style=for-the-badge&logo=json&logoColor=white)

---

## 📋 Table of Contents

- [Features](#-features)
- [Screenshots](#-screenshots)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Project Structure](#-project-structure)
- [Usage](#-usage)
- [API Documentation](#-api-documentation)
- [QR Code Verification](#-qr-code-verification)
- [Technologies Used](#-technologies-used)
- [Data Storage](#-data-storage)
- [Contributing](#-contributing)
- [License](#-license)

---

## ✨ Features

### 🏠 Home Page (`index.php`)
- Quick search functionality for beneficiaries
- Display of top-performing region
- Recent approvals list
- Regional summary statistics

### 📊 Analytics Dashboard (`dashboard.php`)
- **Summary Statistics**
  - Total farmers/beneficiaries count
  - Total fertilizer distributed (kg)
  - Average fertilizer per farmer
  - Total farm area coverage (hectares)
  - Estimated total value (₱)
- **Interactive Charts**
  - Beneficiaries by region (bar chart)
  - Fertilizer type distribution (pie chart)
  - Monthly approvals trend (line chart)
- Top provinces ranking
- Detailed regional statistics table

### 👥 Beneficiaries Management (`beneficiaries.php`)
- Advanced search and filtering:
  - Search by name, RSBSA ID, or location
  - Filter by region
  - Filter by fertilizer type
  - Filter by date range
- Sortable columns
- Pagination with customizable results per page
- View detailed beneficiary profiles
- Export functionality (CSV, Print)

### 📄 Reports & Export (`reports.php`)
- Quick report generation:
  - All beneficiaries report
  - Region-specific reports
- Export formats:
  - CSV download
  - Printable format
- Summary statistics overview
- Regional breakdown

### 👤 Beneficiary Profile View (`view.php`)
- Complete farmer information
- Location details (Barangay, Municipality, Province, Region)
- RSBSA ID with unique verification token
- Contact information
- Farm area details
- Fertilizer allocation information
- Approval status and date
- **QR Code Display** - Unique QR code for beneficiary verification
- Print/Download QR code functionality

### 🔐 QR Code Verification System (`verify.php`)
- **Real-time QR Code Scanner**
  - Camera-based scanning using device camera
  - Multiple camera support (front/back)
  - Live preview with scan region indicator
- **Manual Entry Option**
  - Manual token entry for verification
  - Support for direct farmer ID lookup
- **Verification Results**
  - Real-time validation of beneficiary status
  - Display of farmer details upon successful verification
  - Security token validation using SHA-256 hashing
- **API Integration** for external verification systems

---

## 💻 Requirements

- **PHP** 7.4 or higher
- **Composer** (for dependency management)
- **Web Server**: Apache (XAMPP, WAMP, LAMP) or similar
- **Web Browser**: Chrome, Firefox, Safari, or Edge (latest versions)
- **Camera Access**: Required for QR code scanning feature

---

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Jan-Russel-Penafiel/DA_SYSTEM_WITH_MOCKDATA.git
   ```

2. **Move to web server directory**
   ```bash
   # For XAMPP (Windows)
   mv DA_SYSTEM_WITH_MOCKDATA C:/xampp/htdocs/da
   
   # For LAMP (Linux)
   mv DA_SYSTEM_WITH_MOCKDATA /var/www/html/da
   ```

3. **Install dependencies**
   ```bash
   cd C:/xampp/htdocs/da
   composer install
   ```
   
   This will install:
   - `bacon/bacon-qr-code` - QR code generation library
   - `dasprid/enum` - Enum support for PHP

4. **Start your web server**
   - For XAMPP: Start Apache from XAMPP Control Panel
   - For LAMP: `sudo service apache2 start`

5. **Access the application**
   ```
   http://localhost/da/
   ```

---

## 📁 Project Structure

```
da/
├── index.php                 # Home page with quick search
├── dashboard.php             # Analytics dashboard with charts
├── beneficiaries.php         # Beneficiaries list with filters
├── reports.php               # Reports generation and export
├── view.php                  # Individual beneficiary profile with QR code
├── verify.php                # QR code scanner and verification page
├── composer.json             # PHP dependencies configuration
├── README.md                 # Project documentation
│
├── api/                      # REST API endpoints
│   └── verify.php            # Beneficiary verification API
│
├── classes/                  # PHP class files
│   ├── Exporter.php          # CSV and print export functionality
│   ├── Farmer.php            # Farmer entity class
│   ├── FarmerRepository.php  # Data access layer (JSON-based)
│   ├── FertilizerType.php    # Fertilizer type definitions
│   ├── QRCodeGenerator.php   # QR code generation and verification
│   ├── Region.php            # Region definitions for Philippines
│   ├── Session.php           # Session management utilities
│   └── Statistics.php        # Statistical calculations
│
├── data/                     # Data storage
│   └── farmers.json          # Farmer beneficiary records (JSON database)
│
├── includes/                 # Shared components
│   └── layout.php            # Header, footer, navigation, flash messages
│
└── vendor/                   # Composer dependencies
    ├── autoload.php          # Composer autoloader
    ├── bacon/bacon-qr-code/  # QR code generation library
    ├── dasprid/enum/         # Enum support library
    └── composer/             # Composer internals
```

---

## 📖 Usage

### Searching for Beneficiaries
1. Navigate to the **Home** page or **Beneficiaries** page
2. Enter search terms (name, RSBSA ID, or location)
3. Optionally select filters (region, fertilizer type, date range)
4. Click "Search Beneficiaries"

### Viewing Dashboard Analytics
1. Click on **Dashboard** in the navigation
2. View summary cards for quick statistics
3. Analyze charts for regional and fertilizer distribution
4. Review top provinces and detailed regional tables

### Generating Reports
1. Navigate to **Reports** page
2. Select report type:
   - All Beneficiaries
   - By Region
3. Choose export format:
   - **CSV**: Download spreadsheet
   - **Print**: Open printable version

### Viewing Beneficiary Details
1. From the Beneficiaries list, click on a farmer's name or the "View" button
2. View complete profile including:
   - Personal information
   - Location details
   - Farm and fertilizer information
   - Approval status
   - **QR Code** for verification

### Verifying Beneficiaries via QR Code
1. Navigate to **Verify** page
2. **Using Camera Scanner**:
   - Allow camera access when prompted
   - Select camera (if multiple available)
   - Point camera at beneficiary's QR code
   - System automatically reads and verifies
3. **Using Manual Entry**:
   - Enter the verification token manually
   - Click "Verify" button
4. View verification result with farmer details

---

## 🔌 API Documentation

### Verify Beneficiary Endpoint

**URL:** `POST /api/verify.php`

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "farmer_id": 1,
  "rsbsa_id": "RSBSA-NCR-000001",
  "token": "DA-BEN-BE4B7F2082135C30"
}
```

**Success Response (200):**
```json
{
  "valid": true,
  "message": "Verification successful",
  "farmer": {
    "id": 1,
    "rsbsa_id": "RSBSA-NCR-000001",
    "first_name": "Juan",
    "last_name": "Dela Cruz",
    "middle_name": "Santos",
    "barangay": "Bagong Silang",
    "municipality": "Caloocan City",
    "province": "Metro Manila",
    "region_code": "NCR",
    "fertilizer_type": "Urea (46-0-0)",
    "fertilizer_quantity": 50,
    "date_approved": "2025-06-15",
    "status": "Approved",
    "farm_area": 2.5
  },
  "status": "Approved"
}
```

**Error Response (400/404):**
```json
{
  "valid": false,
  "message": "Invalid token or farmer not found"
}
```

**CORS Headers:**
- `Access-Control-Allow-Origin: *`
- `Access-Control-Allow-Methods: POST, OPTIONS`
- `Access-Control-Allow-Headers: Content-Type`

---

## 🔐 QR Code Verification

### How It Works

1. **Token Generation**
   - Each farmer receives a unique verification token
   - Token format: `DA-BEN-XXXXXXXXXXXXXXXX` (16-character hash)
   - Generated using SHA-256: `hash(farmer_id + rsbsa_id + secret_key)`

2. **QR Code Data Structure**
   ```json
   {
     "system": "DA_FERTILIZER_BENEFICIARY",
     "version": "1.0",
     "farmer_id": 1,
     "rsbsa_id": "RSBSA-NCR-000001",
     "token": "DA-BEN-BE4B7F2082135C30",
     "generated": "2025-06-15 10:30:00"
   }
   ```

3. **Verification Process**
   - QR code is scanned or token is entered manually
   - System extracts farmer_id, rsbsa_id, and token
   - Token is validated against the regenerated hash
   - Farmer details are retrieved and displayed

### Security Features
- Tokens are cryptographically generated using SHA-256
- Tokens are stored with farmer records for validation
- API supports CORS for cross-origin requests
- Secure token comparison using `hash_equals()` to prevent timing attacks

---

## 🛠 Technologies Used

| Technology | Purpose |
|------------|---------|
| **PHP 7.4+** | Server-side scripting |
| **Composer** | Dependency management |
| **Tailwind CSS** | UI styling and responsive design |
| **Chart.js** | Interactive data visualization |
| **Font Awesome** | Icons and visual elements |
| **HTML5/CSS3** | Markup and styling |
| **JavaScript** | Client-side interactivity |
| **BaconQrCode** | QR code generation (SVG) |
| **JSON** | Data storage format |

### Dependencies (via Composer)

| Package | Version | Purpose |
|---------|---------|---------|
| `bacon/bacon-qr-code` | ^3.0 | QR code generation with SVG support |
| `dasprid/enum` | ^1.0 | Enumeration support for PHP |

---

## 💾 Data Storage

### JSON Database (`data/farmers.json`)

The system uses a JSON file-based storage for farmer records. This approach provides:
- **Simplicity**: No database server required
- **Portability**: Easy to backup and transfer
- **Human-readable**: Can be edited manually if needed

### Farmer Record Schema

```json
{
  "id": 1,
  "rsbsa_id": "RSBSA-NCR-000001",
  "first_name": "Juan",
  "last_name": "Dela Cruz",
  "middle_name": "Santos",
  "barangay": "Bagong Silang",
  "municipality": "Caloocan City",
  "province": "Metro Manila",
  "region_code": "NCR",
  "fertilizer_type": "Urea (46-0-0)",
  "fertilizer_quantity": 50,
  "date_approved": "2025-06-15",
  "status": "Approved",
  "contact_number": "09171234567",
  "farm_area": 2.5,
  "verification_token": "DA-BEN-BE4B7F2082135C30"
}
```

### Field Descriptions

| Field | Type | Description |
|-------|------|-------------|
| `id` | Integer | Unique identifier |
| `rsbsa_id` | String | Registry System for Basic Sectors in Agriculture ID |
| `first_name` | String | Farmer's first name |
| `last_name` | String | Farmer's last name |
| `middle_name` | String | Farmer's middle name |
| `barangay` | String | Barangay (village) name |
| `municipality` | String | Municipality/City name |
| `province` | String | Province name |
| `region_code` | String | Region code (NCR, CAR, I-XII, BARMM) |
| `fertilizer_type` | String | Type of fertilizer allocated |
| `fertilizer_quantity` | Integer | Quantity in kilograms |
| `date_approved` | Date | Approval date (YYYY-MM-DD) |
| `status` | String | Approval status (Approved/Pending/Rejected) |
| `contact_number` | String | Mobile phone number |
| `farm_area` | Float | Farm area in hectares |
| `verification_token` | String | Unique QR verification token |

---

## 🎨 Color Scheme

The application uses the official Department of Agriculture colors:

| Color | Hex Code | Usage |
|-------|----------|-------|
| DA Green | `#228B22` | Primary color |
| DA Dark Green | `#006400` | Headers, accents |
| DA Light Green | `#90EE90` | Highlights |
| DA Gold | `#FFD700` | Badges, awards |

---

## 📊 Supported Regions

| Code | Region Name |
|------|-------------|
| NCR | National Capital Region |
| CAR | Cordillera Administrative Region |
| I | Ilocos Region |
| II | Cagayan Valley |
| III | Central Luzon |
| IV-A | CALABARZON |
| IV-B | MIMAROPA |
| V | Bicol Region |
| VI | Western Visayas |
| VII | Central Visayas |
| VIII | Eastern Visayas |
| IX | Zamboanga Peninsula |
| X | Northern Mindanao |
| XI | Davao Region |
| XII | SOCCSKSARGEN |
| XIII | Caraga |
| BARMM | Bangsamoro Autonomous Region in Muslim Mindanao |

---

## 📋 Class Reference

### `Farmer.php`
Entity class representing a farmer beneficiary with getters/setters for all properties.

### `FarmerRepository.php`
Data access layer for CRUD operations on farmer records.
- `getAllFarmers()`: Retrieve all farmers
- `getFarmerById(int $id)`: Get farmer by ID
- `searchFarmers(array $criteria)`: Search with filters
- `migrateExistingFarmersTokens()`: Generate tokens for existing records

### `QRCodeGenerator.php`
Handles QR code generation and verification.
- `generateVerificationToken(int $farmerId, string $rsbsaId)`: Create unique token
- `generateQRCodeSVG(string $data, int $size)`: Generate SVG QR code
- `generateQRCodeDataURI(string $data, int $size)`: Generate base64 data URI
- `verifyToken(int $farmerId, string $rsbsaId, string $token)`: Validate token
- `parseQRData(string $qrData)`: Extract data from QR content

### `Statistics.php`
Statistical calculations for dashboard.
- Regional distribution
- Fertilizer type breakdown
- Monthly trends
- Aggregate calculations

### `Exporter.php`
Export functionality for reports.
- CSV generation
- Print-friendly formatting

### `Region.php`
Philippine region definitions and utilities.

### `FertilizerType.php`
Fertilizer type definitions (Urea, Complete, Ammonium Sulfate, etc.)

### `Session.php`
Session management and flash message handling.

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Add PHPDoc comments for new methods
- Update documentation for new features
- Test QR code functionality with multiple devices

---

## 📝 License

This project is for educational and demonstration purposes.

---

## 👨‍💻 Author

**Jan Russel Penafiel**

- GitHub: [@Jan-Russel-Penafiel](https://github.com/Jan-Russel-Penafiel)

---

## 🙏 Acknowledgments

- Department of Agriculture, Republic of the Philippines
- All Filipino farmers and agricultural workers
- [BaconQrCode](https://github.com/Bacon/BaconQrCode) - QR Code Generation Library

---

<p align="center">
  <img src="https://img.shields.io/badge/Made%20with-❤️-red?style=for-the-badge" alt="Made with love">
  <img src="https://img.shields.io/badge/For-Filipino%20Farmers-green?style=for-the-badge" alt="For Filipino Farmers">
</p>
