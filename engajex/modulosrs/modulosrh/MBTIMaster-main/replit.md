# Overview

This is a full-stack web application for MBTI (Myers-Briggs Type Indicator) psychological assessment analysis. The application processes Excel files containing MBTI survey responses, calculates Z-scores for each personality dimension, determines MBTI types, and provides comprehensive statistical analysis and visualization of results.

The system is designed to handle bulk data processing for psychological research or organizational assessment purposes, with features for data import, analysis, storage, export capabilities, and detailed individual profile analysis.

## Recent Updates (January 2025)
- Added comprehensive offline profile analysis system for all 16 MBTI types
- Implemented detailed personality profiles with characteristics, strengths, improvement areas, and development points
- Created individual profile pages with career suggestions and work environment recommendations
- Added profile sharing and report generation functionality

# User Preferences

Preferred communication style: Simple, everyday language.

# System Architecture

## Frontend Architecture
- **Framework**: React with TypeScript, built using Vite for fast development and optimized builds
- **UI Components**: Shadcn/ui component library built on top of Radix UI primitives for accessible, customizable components
- **Styling**: Tailwind CSS with CSS variables for theming, including custom MBTI-specific color schemes
- **State Management**: TanStack Query (React Query) for server state management, caching, and synchronization
- **Routing**: Wouter for lightweight client-side routing
- **File Processing**: SheetJS (xlsx) library for Excel file parsing and manipulation
- **Form Handling**: React Hook Form with Zod for validation

## Backend Architecture
- **Runtime**: Node.js with Express.js framework
- **Language**: TypeScript with ES modules
- **API Design**: RESTful API with structured error handling and request/response logging
- **Data Validation**: Zod schemas for runtime type checking and API validation
- **Storage**: In-memory storage implementation with interface for future database integration
- **Development**: Hot reload with Vite integration for seamless full-stack development

## Data Storage Solutions
- **Current**: In-memory storage using Maps for development and testing
- **Database Ready**: Drizzle ORM configured with PostgreSQL schema definitions
- **Schema**: Structured tables for users and MBTI results with proper relationships and constraints
- **Migration**: Drizzle Kit for database schema management and migrations

## Authentication and Authorization
- **Session Management**: Express sessions with PostgreSQL session store (connect-pg-simple)
- **Architecture**: Prepared for user authentication with bcrypt password hashing
- **Security**: CORS configuration and secure session handling

## Data Processing Pipeline
- **Excel Import**: Multi-step validation and parsing of MBTI survey responses
- **Statistical Analysis**: Z-score calculations, type determination, and intensity classification
- **Batch Processing**: Bulk data operations for handling large datasets
- **Export Features**: Excel and JSON export capabilities with formatted results

# External Dependencies

## Database
- **Neon Database**: Serverless PostgreSQL provider for cloud database hosting
- **Drizzle ORM**: Type-safe database toolkit with PostgreSQL dialect support
- **Connection**: Environment-based database URL configuration

## UI and Styling
- **Radix UI**: Comprehensive component primitives for accessibility and customization
- **Tailwind CSS**: Utility-first CSS framework with PostCSS processing
- **Lucide React**: Icon library for consistent iconography
- **Class Variance Authority**: Type-safe variant API for component styling

## Development Tools
- **Vite**: Build tool with React plugin and runtime error overlay
- **TypeScript**: Static type checking with strict configuration
- **ESBuild**: Fast bundling for production builds
- **Replit Integration**: Development environment plugins and banner integration

## File Processing
- **SheetJS (xlsx)**: Excel file reading and writing capabilities
- **React Dropzone**: Drag-and-drop file upload interface

## Utility Libraries
- **Date-fns**: Date manipulation and formatting
- **clsx/twMerge**: Conditional CSS class management
- **Nanoid**: Unique ID generation for records