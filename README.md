# Carte Tabé API

## Overview

**Carte Tabé API** powers the entire Carte Tabé ecosystem, a multi-merchant gift card platform designed for Africa.

The platform enables organizations, businesses, and individuals to issue, distribute, redeem, and manage digital gift cards while providing a secure and scalable payment experience.

The API handles:

* Gift card lifecycle management
* Beneficiary onboarding and authentication
* Merchant integrations
* Payments and settlements
* Transaction tracking
* Payout processing
* Notifications
* Fraud prevention mechanisms
* Real-time events 
* ....

### Clients

* 📱 Mobile Application
* 🏪 Partner Portal
* 🧑‍💼 Administration Dashboard
* 🔌 Third-Party Integrations

---

# Architecture

This project follows a light **Domain-Driven Design (DDD)** approach.

The objective is to keep business rules independent from infrastructure concerns while remaining practical and easy to maintain.

## Core Principles

* Separation of Domain, Application and Infrastructure layers
* Business-first design
* Event-driven workflows
* Queue-based background processing
* API-first architecture
* Secure-by-default implementation

### Project Structure

The codebase follows a pragmatic Domain-Driven Design (DDD) approach while leveraging Laravel's ecosystem.

app/
├── Domains/
│   ├── GiftCards/
│   │   ├── Entities/
│   │   ├── Events/
│   │   ├── Services/
│   │   ├── UseCases/
│   │   └── ValueObjects/
│   │
│   └── Users/
│       ├── DTO/
│       ├── Entities/
│       ├── UseCases/
│       └── ValueObjects/
│
├── Application/
│   ├── Actions/
│   ├── DTOs/
│   ├── Services/
│   └── UseCases/
│
├── Infrastructure/
│   ├── External
│   │   └── Payment/
│   │       ├── DTO/
│   │       └── ValueObjects/
│   └── Persistence/
│
├── Events/
├── Listeners/
├── Jobs/
├── Policies/
├── Notifications/
├── Observers/
├── Console/
├── Providers/
└── Repositories/
    └── BaseRepository.php

#### Main Domains

* Gift Cards
* Transactions
* Payments
* Beneficiaries
* Customers
* Partners
* Enterprises
* Payouts
* Notifications
* Authentication

---

# Technology Stack

| Category         | Technology                 |
| ---------------- | ---------------------------|
| Framework        | Laravel 10                 |
| Language         | PHP 8.1                    |
| Database         | MySQL 8                    |
| Cache            | Redis                      |
| Queue System     | Laravel Queues + Redis     |
| Authentication   | Laravel Passport           |
| Realtime         | Pusher                     |
| Documentation    | Swagger(OpenAPI) + Postman |
| Storage          | Amazon S3                  |
| Containerization | Docker                     |
| CI/CD            | GitHub Actions             |
| Deployment       | Railway                    |


# Key Features

## Gift Card Management

* Gift card issuance
* Activation workflow
* Balance management
* Redemption tracking
* Transaction history
* Others ...


## Authentication

* OTP Verification
* Access Tokens
* Role-Based Access Control (RBAC)

Supported roles:

* Super Admin
* Admin
* Partner
* Customer

## Payments

Integration-ready architecture supporting:

* Wave
* Orange Money
* Free Money
* Additional Mobile Money providers

## Notifications

* SMS
* WhatsApp
* Push Notifications
* Email Notifications

## Real-Time Events

Using Pusher and Laravel Broadcasting:

* Transaction updates
* Payment status changes
* Wallet events
* Administrative notifications

---

# Security

Security is a first-class concern throughout the platform.

Implemented protections include:

* OTP rate limiting
* Request validation
* Role-based authorization
* Sensitive data encryption
* Audit trails
* Transaction replay protection
* Idempotent payment workflows
* Fraud monitoring hooks

## Replay Protection

Financial operations are protected against duplicate execution caused by:

* Network retries
* Double-click submissions
* Provider callbacks duplication

The platform implements safeguards to guarantee operation uniqueness and consistency.

---

# Development Environment

## Prerequisites

* PHP 8.4+
* Composer
* Docker
* Docker Compose
* MySQL
* Redis

---

# Installation

## Clone Repository

```bash
git clone <repository-url>
cd TabeWebAPI
```

## Install Dependencies

```bash
composer install
```

## Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Configure:

```env
DB_*
REDIS_*
PASSPORT_*
AWS_*
PUSHER_*
TWILIO_*
```

---

# Docker Setup

The project includes a complete Docker-based development environment.

Start services:

```bash
docker compose up -d
```

Build containers:

```bash
docker compose build
```

Stop containers:

```bash
docker compose down
```

Services available:

* Laravel Application
* MySQL
* Redis

---

# Database

Run migrations:

```bash
php artisan migrate
```

Run seeders:

```bash
php artisan db:seed
```

Refresh database:

```bash
php artisan migrate:fresh --seed
```

---

# Queue Workers

Start queue workers:

```bash
php artisan queue:work
```

The platform uses queues extensively for:

* Notifications
* Payout processing
* Payment integrations
* Heavy background jobs

---

# API Documentation

Swagger documentation is available at:

```text
/api/documentation
```

Documentation includes:

* Endpoint specifications
* Authentication flows
* Request examples
* Response schemas
* Error handling

---

# Testing

Run all tests:

```bash
php artisan test
```

Run coverage:

```bash
php artisan test --coverage
```

Testing includes:

* Feature Tests
* Unit Tests
* API Tests
* Business Rule Validation

---

# Continuous Integration

GitHub Actions automatically executes:

* Dependency installation
* Static checks
* Test suite execution
* Build validation

Every pull request must pass CI checks before merging.

---

# Deployment

Production deployment is currently automated through Railway.

Deployment pipeline:

```text
GitHub
    ↓
GitHub Actions
    ↓
Railway
    ↓
Production Environment
```

Environment-specific configuration is managed through Railway Variables.

---

# Storage

Files are stored using Amazon S3.

Supported assets:

* User documents
* Gift card media
* Partner media 
* Generated exports

The application supports direct cloud storage integration.

---

# Monitoring & Logging

The platform provides:

* Laravel Logs
* Queue Monitoring
* Failed Job Tracking
* Exception Reporting

Useful commands:

```bash
php artisan queue:failed

php artisan queue:retry all
```

---

# Contributing

1. Create a feature branch
2. Implement changes
3. Add tests
4. Submit Pull Request

---

# Author

**Mohamed Thioune**

Senior Backend Engineer
Software Developer Consultant

Founder of BIRDs 🇸🇳

*"Maxbird codeur a la casquette"*

---

# License

Private and proprietary software.

Copyright © Carte Tabé.

---