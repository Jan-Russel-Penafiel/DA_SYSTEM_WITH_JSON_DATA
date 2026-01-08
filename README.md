# 🌾 DA-FBMS (Fertilizer Beneficiary Management System)

A web-based management system for tracking and managing approved farmer beneficiaries of the Department of Agriculture's fertilizer distribution program across all regions of the Philippines.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Chart.js](https://img.shields.io/badge/Chart.js-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white)

---

## 📋 Table of Contents

- [Features](#-features)
- [Screenshots](#-screenshots)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Project Structure](#-project-structure)
- [Usage](#-usage)
- [Technologies Used](#-technologies-used)
- [Contributing](#-contributing)
- [License](#-license)

---

## ✨ Features

### 🏠 Home Page
- Quick search functionality for beneficiaries
- Display of top-performing region
- Recent approvals list
- Regional summary statistics

### 📊 Analytics Dashboard
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

### 👥 Beneficiaries Management
- Advanced search and filtering:
  - Search by name, RSBSA ID, or location
  - Filter by region
  - Filter by fertilizer type
  - Filter by date range
- Sortable columns
- Pagination with customizable results per page
- View detailed beneficiary profiles
- Export functionality (CSV, Print)

### 📄 Reports & Export
- Quick report generation:
  - All beneficiaries report
  - Region-specific reports
- Export formats:
  - CSV download
  - Printable format
- Summary statistics overview
- Regional breakdown

### 👤 Beneficiary Profile View
- Complete farmer information
- Location details (Barangay, Municipality, Province, Region)
- RSBSA ID
- Contact information
- Farm area details
- Fertilizer allocation information
- Approval status and date

---

## 💻 Requirements

- **PHP** 7.4 or higher
- **Web Server**: Apache (XAMPP, WAMP, LAMP) or similar
- **Web Browser**: Chrome, Firefox, Safari, or Edge (latest versions)

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

3. **Start your web server**
   - For XAMPP: Start Apache from XAMPP Control Panel
   - For LAMP: `sudo service apache2 start`

4. **Access the application**
   ```
   http://localhost/da/
   ```

---

## 📁 Project Structure

```
da/
├── index.php              # Home page with quick search
├── dashboard.php          # Analytics dashboard with charts
├── beneficiaries.php      # Beneficiaries list with filters
├── reports.php            # Reports generation and export
├── view.php               # Individual beneficiary profile view
├── api/                   # API endpoints (if applicable)
├── classes/               # PHP class files
│   ├── Exporter.php       # CSV and print export functionality
│   ├── Farmer.php         # Farmer entity class
│   ├── FarmerRepository.php # Data access and mock data
│   ├── FertilizerType.php # Fertilizer type definitions
│   ├── Region.php         # Region definitions for Philippines
│   ├── Session.php        # Session management
│   └── Statistics.php     # Statistical calculations
└── includes/
    └── layout.php         # Shared layout components (header, footer)
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

---

## 🛠 Technologies Used

| Technology | Purpose |
|------------|---------|
| **PHP 7.4+** | Server-side scripting |
| **Tailwind CSS** | UI styling and responsive design |
| **Chart.js** | Interactive data visualization |
| **Font Awesome** | Icons and visual elements |
| **HTML5/CSS3** | Markup and styling |
| **JavaScript** | Client-side interactivity |

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

## 📊 Data Structure

### Farmer/Beneficiary Entity
- **RSBSA ID**: Unique identifier from Registry System for Basic Sectors in Agriculture
- **Personal Info**: First name, Last name, Middle name
- **Location**: Barangay, Municipality, Province, Region
- **Contact**: Phone number
- **Farm Details**: Farm area (hectares)
- **Fertilizer Info**: Type, Quantity (kg)
- **Status**: Approval status and date

### Supported Regions
- NCR (National Capital Region)
- CAR (Cordillera Administrative Region)
- Regions I through XII
- BARMM (Bangsamoro Autonomous Region in Muslim Mindanao)

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

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

---

<p align="center">
  <img src="https://img.shields.io/badge/Made%20with-❤️-red?style=for-the-badge" alt="Made with love">
  <img src="https://img.shields.io/badge/For-Filipino%20Farmers-green?style=for-the-badge" alt="For Filipino Farmers">
</p>
