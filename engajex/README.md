# TestProf Engaja (Thriveo Engajex)

Platform for Employee Engagement, Corporate Culture Management, and Organizational Climate Intelligence.

## 📋 Overview

TestProf Engaja is a web-based system designed to measure and improve employee engagement through various interactive tools, AI-generated insights, and management dashboards. It allows companies to track mood, manage culture, conduct pulse surveys, and visualize talent allocation.

## 🚀 Key Features

### 1. **Pulse & Climate (MLPT System)**
- **Monthly Pulse Surveys:** Create and distribute quick engagement surveys.
- **AI Sentiment Analysis:** Uses AI to analyze open-ended comments from surveys, identifying sentiments (Positive/Negative) and key themes.
- **Vote Page:** Public-facing page for anonymous employee voting (`mlpt_pulse_vote.php`).
- **Trust Index™ Simulator:** Interactive tool to simulate and project the company's Trust Index score based on 5 pillars (Credibility, Respect, Impartiality, Pride, Camaraderie).

### 2. **Culture & Intellect**
- **Culture Board:** Digital board to define and display the company's Purpose, Mission, Vision, Principles, Values, and Organizational Culture.
- **Manager Editing:** Managers can update these core definitions, visible to all employees.

### 3. **Employee Management**
- **CRUD Operations:** Register, edit, and manage employees.
- **CSV Import:** Bulk import of employees via Excel/CSV files.
- **Roles:** Support for `Employee`, `Manager`, `Responsible` (Admin), and `Admin` roles.
- **Team Allocation:** Assign employees to managers for hierarchical reporting.

### 4. **Mood Tracker**
- **Daily Mood Check:** Employees can register their daily mood (1-5 scale) with optional notes.
- **Team Dashboard:** Managers can view the average mood and recent log history of their direct reports (7-day window).

### 5. **Talent Matrix (9-Box)**
- **Allocation Tool:** Visual interface to classify employees into performance/potential quadrants (e.g., High Potential/High Performance). [(Visible in Allocation Help)]

### 6. **Gamification & Social**
- **EngajaCoins:** Internal currency system for rewards.
- **Bingo:** Interactive games for engagement.
- **Celebrations:** Birthday and anniversary tracking.
- **Connections:** Tools for internal networking.

### 7. **HR Indicators & Dashboard**
- **12 Key Metric Tables:** Tracks Hire/Fire, Headcount, Payroll Cost, Overtime, Mood, Feedback, Time-to-Fill, Vacancies, Savings, Realocation, and Training.
- **CSV Data Import:** Flexible tool (`rh_indicators_input.php`) to bulk upload historical data for any of the 12 tables, with auto-column mapping and date conversion (DD/MM/YYYY -> YYYY-MM-DD).
- **Comprehensive Dashboard:** Interactive visualization (`rh_dashboard.php`) featuring:
    - Dedicated charts for all 11+ metrics.
    - Year selector for historical analysis.
    - Detailed evolution of Headcount (CLT, PJ, COOP, SCP, Interns).

## 🛠️ Technical Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL Database
- Apache/Nginx Web Server

### Installation
1. **Database Config:**
   - Configure database credentials in `config.php`.

2. **Database Initialization:**
   - Import the initial SQL schema.
   - **Critical Updates:** Run the following scripts in your browser once to ensure the database schema is up to date:
     - `update_db_mlpt_ai.php`: Adds `ai_summary` column for Pulse functionality.
     - `update_db_mood.php`: Adds `company_id` column to `mood_tracker`.
     - `update_db_rh_indicators.php`: Creates tables for HR Indicators.
     - `update_db_zbb.php`: Creates tables for Zero-Based Budgeting (ZBB) module.
     - `update_db_gptw.php`: Creates tables for GPTW (Great Place to Work) indicators.

### Directory Structure
- `/modules/`: Contains core feature modules (`mlpt_maturity.php`, `board_culture.php`, `mood.php`, etc.).
- `/zbb/`: Zero-Based Budgeting module (Dashboard, Budget Management, API).
- `/includes/`: Reusable components (Sidebar, authentication checks).
- `/assets/`: CSS styles and images.
- `mlpt_pulse_vote.php`: Standalone access point for pulse surveys.

## 🤖 AI Integration
The system simulates (or integrates with) AI services to provide:
- **Sentiment Analysis:** Automatically summarizing qualitative feedback from pulse surveys.
- **Insights:** Providing contextual feedback on dashboard metrics.

## 📝 Usage Notes
- **Admin Access:** Users with the 'Responsible' or 'Admin' role have full access to settings and management tools.
- **Manager Access:** Can manage their specific teams and view team-specific dashboards (Mood, Allocation).
- **Employee Access:** Can view Culture, vote in surveys, register mood, and participate in social features.
