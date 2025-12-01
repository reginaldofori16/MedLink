# MedLink - Healthcare Prescription Management System

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://www.mysql.com/)

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [System Architecture](#system-architecture)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [Project Structure](#project-structure)
- [User Roles](#user-roles)
- [API Documentation](#api-documentation)
- [Contributing](#contributing)
- [License](#license)

## 🎯 Overview

**MedLink** is a comprehensive healthcare prescription management system designed to connect patients, hospitals, and pharmacies in a secure, efficient digital ecosystem. The platform addresses critical healthcare challenges in Africa, particularly combating counterfeit medicines and ensuring prescription compliance.

### Key Objectives

- **Anti-Counterfeit Medicine**: Verify and track genuine medicines through licensed pharmacies
- **Prescription Compliance**: Ensure proper prescription verification by licensed pharmacists
- **Healthcare Accessibility**: Make healthcare accessible to both urban and rural communities
- **Digital Transformation**: Modernize prescription management and delivery processes

## ✨ Features

### For Patients
- 📝 Submit prescriptions online with image upload
- 🏥 Select from verified hospitals
- 💊 Track prescription status in real-time
- 💳 Secure payment processing (Mobile Money, Cards, Bank Transfer)
- 📦 Delivery and pickup options
- 📊 View prescription history and timeline

### For Hospitals
- ✅ Review and verify patient prescriptions
- 📋 Request clarifications from patients
- 🏥 Manage prescription workflow
- 📊 View hospital prescription statistics
- 🔄 Transfer prescriptions to pharmacies

### For Pharmacies
- 📥 Receive prescriptions from hospitals
- 🔍 Review and process prescriptions
- 💰 Set pricing and await payment
- 📦 Mark orders as ready for pickup/delivery
- ✅ Complete prescription fulfillment
- 📊 Track pharmacy performance

### For Administrators
- 📊 Comprehensive dashboard with analytics
- 👥 Manage users (patients, hospitals, pharmacies)
- 📈 View system statistics and trends
- 🔍 Search and filter all data
- ⚙️ Approve/reject hospital and pharmacy registrations
- 📋 Monitor all prescriptions and payments

## 🏗️ System Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                      Client Layer                            │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │ Patients │  │Hospitals │  │Pharmacies│  │  Admin   │   │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘   │
└───────┼─────────────┼─────────────┼─────────────┼─────────┘
        │             │             │             │
        └─────────────┴─────────────┴─────────────┘
                      │
        ┌─────────────▼─────────────┐
        │    Presentation Layer      │
        │  (HTML/CSS/JavaScript)     │
        └─────────────┬─────────────┘
                      │
        ┌─────────────▼─────────────┐
        │   Application Layer        │
        │  ┌────────┐  ┌─────────┐ │
        │  │Actions │  │Controllers││
        │  └────┬───┘  └────┬─────┘ │
        │       │            │       │
        │  ┌────▼────────────▼────┐ │
        │  │    Business Logic     │ │
        │  │      (Classes)        │ │
        │  └───────────┬───────────┘ │
        └───────────────┼─────────────┘
                        │
        ┌───────────────▼─────────────┐
        │      Data Access Layer       │
        │    (Database Connection)     │
        └───────────────┬─────────────┘
                        │
        ┌───────────────▼─────────────┐
        │      Database Layer          │
        │      (MySQL Database)        │
        └──────────────────────────────┘
```

### MVC Architecture Pattern

MedLink follows a **Model-View-Controller (MVC)** architecture:

- **Model**: `classes/` - Business logic and data models
- **View**: `view/` - User interface templates
- **Controller**: `controllers/` and `actions/` - Request handling and business logic

### Data Flow Diagram

```
User Request
    │
    ▼
┌─────────────────┐
│   View Layer    │  (HTML/PHP Templates)
│  (Frontend)     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Action Layer   │  (Request Handlers)
│  (API Endpoints)│
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Controller/     │  (Business Logic)
│ Class Layer     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Database Layer  │  (MySQL)
│  (Data Storage) │
└─────────────────┘
```

## 🛠️ Technology Stack

### Backend
- **PHP 7.4+** - Server-side scripting
- **MySQL 5.7+** - Relational database management
- **MySQLi** - Database connectivity

### Frontend
- **HTML5** - Markup language
- **CSS3** - Styling and layout
- **JavaScript (ES6+)** - Client-side interactivity
- **AJAX** - Asynchronous data fetching

### Additional Technologies
- **Paystack API** - Payment processing
- **Session Management** - User authentication
- **File Upload** - Prescription image handling

## 📦 Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/LAMP (for local development)

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/MedLink.git
cd MedLink
```

### Step 2: Database Configuration

1. Create a MySQL database:
```sql
CREATE DATABASE medlink_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Update database credentials in `settings/db_cred.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'medlink_db');
```

### Step 3: Import Database Schema

```bash
mysql -u your_username -p medlink_db < settings/database_schema.sql
```

### Step 4: Configure Web Server

**For XAMPP:**
- Place project in `xampp/htdocs/MedLink/`
- Access via `http://localhost/MedLink/`

**For Apache:**
- Configure virtual host pointing to project directory
- Ensure mod_rewrite is enabled

### Step 5: Set Permissions

```bash
chmod 755 uploads/
chmod 644 settings/db_cred.php
```

## 🗄️ Database Setup

### Core Tables

1. **patients** - Patient/user information
2. **hospitals** - Hospital registration and details
3. **pharmacies** - Pharmacy registration and details
4. **prescriptions** - Prescription records
5. **prescription_medicines** - Medicine details for each prescription
6. **prescription_timeline** - Status change history

### Database Schema

See `settings/database_schema.sql` for complete schema definition.

## 📁 Project Structure

```
MedLink/
├── actions/                 # API endpoints and request handlers
│   ├── login_*.php         # Authentication actions
│   ├── register_*.php       # Registration actions
│   ├── get_*.php           # Data retrieval actions
│   └── update_*.php        # Data update actions
├── classes/                 # Business logic classes
│   ├── customer_class.php
│   ├── hospital_class.php
│   ├── pharmacy_class.php
│   └── prescription_class.php
├── controllers/              # Request controllers
│   ├── customer_controller.php
│   ├── hospital_controller.php
│   └── pharmacy_controller.php
├── css/                      # Stylesheets
│   ├── style.css
│   ├── admin.css
│   └── *.css
├── js/                       # JavaScript files
│   ├── login.js
│   └── register.js
├── view/                     # View templates
│   ├── index.php            # Landing page
│   ├── login.php            # Login page
│   ├── register.php         # Registration page
│   ├── patients.php         # Patient dashboard
│   ├── hospital.php         # Hospital dashboard
│   ├── pharmacy.php         # Pharmacy dashboard
│   └── admin.php            # Admin dashboard
├── settings/                 # Configuration files
│   ├── core.php             # Core functions
│   ├── db_class.php         # Database class
│   ├── db_cred.php          # Database credentials
│   └── database_schema.sql  # Database schema
├── uploads/                  # Uploaded files directory
└── README.md                # This file
```

## 👥 User Roles

### 1. Patient
- Register and manage profile
- Submit prescriptions
- Track prescription status
- Make payments
- View prescription history

### 2. Hospital
- Register and get verified
- Review patient prescriptions
- Request clarifications
- Transfer prescriptions to pharmacies
- View hospital statistics

### 3. Pharmacy
- Register and get verified
- Receive prescriptions
- Review and price prescriptions
- Process payments
- Mark orders ready for pickup/delivery
- Complete fulfillment

### 4. Administrator
- Manage all users
- Approve/reject registrations
- View system analytics
- Monitor all prescriptions
- Manage system settings

## 🔌 API Documentation

### Authentication Endpoints

#### Patient Login
```
POST /actions/login_customer_action.php
Body: { email, password }
Response: { status, message, user_data }
```

#### Hospital Login
```
POST /actions/login_hospital_action.php
Body: { government_id, password }
Response: { status, message, hospital_data }
```

#### Pharmacy Login
```
POST /actions/login_pharmacy_action.php
Body: { government_id, password }
Response: { status, message, pharmacy_data }
```

### Data Retrieval Endpoints

#### Get Patient Prescriptions
```
GET /actions/get_patient_prescriptions_action.php
Headers: Session required
Response: { status, prescriptions: [...] }
```

#### Get All Prescriptions (Admin)
```
GET /actions/get_all_prescriptions_action.php
Headers: Admin session required
Response: { status, prescriptions: [...] }
```

#### Get Hospitals
```
GET /actions/get_hospitals_action.php
Headers: Session required
Response: { status, hospitals: [...] }
```

### Update Endpoints

#### Update Prescription Status
```
POST /actions/update_prescription_status_action.php
Body: { prescription_id, status, timeline_text }
Response: { status, message }
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards

- Follow PSR-12 coding standards
- Add comments for complex logic
- Use meaningful variable names
- Write descriptive commit messages

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 📞 Contact & Support

- **Project Repository**: [GitHub Repository](https://github.com/yourusername/MedLink)
  > **Note**: Update the repository link above with your actual GitHub repository URL
  
- **Documentation**: See `docs/` directory for detailed documentation
  - [System Analysis and Design](docs/SYSTEM_ANALYSIS_AND_DESIGN.md)
  - [Architecture Documentation](docs/ARCHITECTURE.md)
  - [Installation Guide](docs/INSTALLATION_GUIDE.md)
  - [API Documentation](docs/API_DOCUMENTATION.md)
  
- **Issues**: Report issues via GitHub Issues

## 🙏 Acknowledgments

- Healthcare professionals who provided domain expertise
- Open-source community for tools and libraries
- All contributors to the MedLink project

---

**MedLink** - Connecting patients with verified hospitals and licensed pharmacies for safe, affordable access to genuine medicines across Africa.
