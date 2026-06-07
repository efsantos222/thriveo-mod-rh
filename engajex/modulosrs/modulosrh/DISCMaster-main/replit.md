# Overview

A web-based DISC personality assessment calculator that processes Excel files containing behavioral questionnaire responses. The application calculates DISC profile scores (Dominance, Influence, Steadiness, Conscientiousness) for individuals and generates comprehensive statistical analysis with multiple export formats including basic and detailed reporting capabilities.

# User Preferences

Preferred communication style: Simple, everyday language.

# System Architecture

## Frontend Architecture
- **Technology**: HTML5 with Bootstrap 5 for responsive UI
- **JavaScript**: Vanilla JS for file upload handling and DOM manipulation
- **Styling**: Custom CSS with gradient backgrounds and modern card-based layout
- **File Upload**: Drag-and-drop interface with file validation for Excel files (.xlsx, .xls)

## Backend Architecture
- **Framework**: Flask (Python) with modular route structure
- **File Processing**: Pandas for Excel file parsing and data manipulation
- **PDF Generation**: ReportLab for creating downloadable assessment reports
- **Architecture Pattern**: MVC-style separation with dedicated calculator class
- **Error Handling**: Comprehensive validation for file structure and data integrity

## Data Processing
- **Input Format**: Excel files with ID, Name, and 28 questionnaire columns (7 per DISC profile)
- **Validation**: Strict column count and data type validation
- **Calculation Logic**: Dedicated DISCCalculator class for profile scoring
- **Statistics**: Aggregated analysis across all individuals in the dataset
- **Export Options**: Multiple formats (Excel, PDF, CSV) with basic and detailed analysis modes

## File Management
- **Upload Strategy**: Temporary file storage with automatic cleanup
- **Security**: Werkzeug secure filename handling
- **Size Limits**: 16MB maximum file upload size
- **Storage**: Local filesystem with configurable upload directory

## Security Considerations
- **Session Management**: Flask sessions with configurable secret key
- **File Validation**: Extension-based filtering and secure filename processing
- **Proxy Support**: ProxyFix middleware for deployment behind reverse proxies

# External Dependencies

## Core Libraries
- **Flask**: Web framework for HTTP handling and templating
- **Pandas**: Excel file processing and data manipulation
- **NumPy**: Numerical computations for DISC calculations
- **ReportLab**: PDF generation for assessment reports
- **Werkzeug**: Security utilities and file handling

## Frontend Dependencies
- **Bootstrap 5**: CSS framework via CDN
- **Font Awesome 6**: Icon library via CDN
- **XLSX.js**: Client-side Excel file handling (referenced but not actively used)

## Infrastructure
- **Python Runtime**: Standard library modules (os, logging, tempfile, json)
- **File System**: Local storage for temporary file processing
- **No Database**: Application operates entirely on uploaded file data without persistent storage

# Recent Changes (August 2025)

## Behavioral Analysis System (Offline-First)
- **Individual Profile Analysis**: Comprehensive behavioral insights using integrated DISC expertise knowledge base
- **Automated Insights**: Detailed analysis of strengths, improvement opportunities, and development areas for each profile type
- **Team Dynamics Analysis**: Intelligent assessment of team composition with management recommendations based on profile distribution
- **Interactive UI**: Tabbed interface displaying behavioral analysis with strengths, improvements, development, and executive summary
- **Enhanced Export Reports**: All export formats include comprehensive behavioral analysis data in Excel, PDF, and CSV
- **Robust System**: 100% offline functionality with extensive pre-built analysis templates for all DISC combinations
- **Professional Analysis**: Management tips, communication styles, and ideal work environments for each profile type

## WordPress Integration
- **Complete Integration Package**: Ready-to-use PHP code for WordPress functions.php integration
- **Shortcode System**: Multiple shortcode options for easy embedding ([disc_analyzer_v2], [disc_analyzer])
- **Administrative Interface**: WordPress admin panel for configuration and URL management  
- **Widget Support**: Sidebar widget for displaying DISC analyzer in any widget area
- **Responsive Design**: Mobile-friendly iframe embedding with automatic height adjustment
- **Template Functions**: PHP functions for developers to embed in custom themes
- **Security Features**: URL validation, sanitization, and origin checking for iframe communication

## Enhanced Export System
- **Multiple Export Formats**: Added support for Excel, PDF, and CSV exports
- **Basic vs Detailed Reports**: Two-tier reporting system with basic summaries and detailed analysis
- **Advanced Excel Reports**: Multi-sheet workbooks with individual results, statistics, and profile analysis
- **Enhanced PDF Reports**: Detailed reports with executive summaries and comprehensive statistics
- **CSV Export**: Clean data format for further analysis in external tools
- **Improved Error Handling**: Robust file handling with proper cleanup and error messages
- **Better UI**: Organized export interface with grouped options for different report types