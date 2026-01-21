# Health Check Information System - Product Overview

## Project Purpose
A server loaning management system designed to track and manage the loaning of physical servers and security appliances for presales Proof of Concept (PoC) purposes. The system streamlines the workflow from email requests to asset tracking and return processing.

## Value Proposition
- **Centralized Asset Tracking**: Maintains comprehensive records of all loanable servers and security appliances
- **Simplified Workflow**: Eliminates complex approval processes in favor of direct loan recording by authorized staff
- **Real-time Status Management**: Tracks asset availability, active loans, and overdue items
- **Customer Relationship Management**: Links loans to customer companies for better relationship tracking

## Key Features & Capabilities

### Asset Management
- Complete CRUD operations for servers, security appliances, and network devices
- Asset categorization with detailed specifications (model, serial number, specifications)
- Availability status tracking with business logic validation
- Prevents deletion of assets with active loans

### Loan Management
- Direct loan recording by admin and engineer staff
- Customer company and contact tracking
- Three-status workflow: Active, Returned, Cancelled
- Overdue detection and tracking
- Loan history and search capabilities

### User Authentication & Roles
- Two-tier role system: Admin and Engineer
- Equal permissions for both roles (no hierarchical restrictions)
- Session-based authentication with proper security measures
- Role-based dashboard customization

### Business Intelligence
- Dashboard with key metrics and overdue alerts
- Active loans monitoring with customer details
- Asset utilization tracking
- Search and filtering across all entities

## Target Users & Use Cases

### Primary Users
- **Administrators**: Full system access for asset and loan management
- **Engineers**: Same permissions as administrators for operational flexibility

### Core Use Cases
1. **Presales Support**: Quick asset allocation for customer demonstrations
2. **Asset Tracking**: Real-time visibility into equipment location and status
3. **Customer Management**: Maintain records of which companies have borrowed equipment
4. **Operational Efficiency**: Streamlined process from request to return

### Workflow Integration
- Integrates with existing email-based request system
- Supports manual loan recording (no self-service portal)
- Aligns with actual business processes rather than theoretical workflows

## System Benefits
- **Operational Clarity**: Clear visibility into asset availability and loan status
- **Risk Management**: Prevents double-booking and tracks overdue items
- **Customer Service**: Quick access to customer loan history
- **Audit Trail**: Complete record of all loan transactions
- **Scalability**: Template-based architecture supports future enhancements