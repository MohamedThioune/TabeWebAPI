# Carte Tabé – Backend API

## Overview

**Carte Tabé API** is the backend service powering the Carte Tabé ecosystem — a multi-merchant gift card platform.
This API handles the full lifecycle of gift cards, including issuance, activation, authentication, payments, transactions, payouts, notifications, and fraud-prevention workflows.

It serves multiple clients:

* 📱 Mobile applications (wallet for beneficiaries)
* 🏪 Partner / merchant portals
* 🧑‍💼 Admin back-office

The API is designed to be secure, scalable, and ready for financial-grade workflows.

---

## Architecture & Design

This project follows a **lightweight Domain-Driven Design (DDD)** approach.

The goal is to keep the codebase:

* **Domain-focused**
* **Readable and maintainable**
* **Pragmatic**, without over-engineering

### Key principles applied:

* Clear separation between **Domain**, **Application**, and **Infrastructure**
* Business logic isolated from frameworks when possible
* Explicit domain concepts (GiftCard, Transaction, Partner, Payout, etc.)
* Use of Laravel features where they bring real value (Queues, Events, Jobs, Policies)

This is **DDD-inspired**, not a strict or academic implementation.

---

## Tech Stack

* **Framework**: Laravel
* **Database**: MySQL
* **Cache / Queues**: Redis
* **Authentication**: OTP (SMS / WhatsApp), API tokens(Passport)
* **Documentation**: Swagger / OpenAPI
* **Payments**: Mobile Money providers (Wave, Orange Money, Free Money, etc.)

---

## 🚀 Getting Started

### 1️⃣ Clone the repository

```bash
git clone https://github.com/your-organization/carte-tabe-api.git
cd TabeWebAPI
```

---

### 2️⃣ Install dependencies

```bash
composer install
```

---

### 3️⃣ Environment configuration

Copy the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Update your `.env` file with the correct values:

* Database credentials
* Redis configuration
* Twilio credentials
* Payment provider keys
* Storage (S3 or local)

---

### 4️⃣ Database migrations

Run migrations to set up the database schema:

```bash
php artisan migrate
```

(Optional) Seed demo data:

```bash
php artisan db:seed
```

---

### 5️⃣ Start the application

Run the local development server:

```bash
php artisan serve
```

The API will be available at:

```
http://localhost:8000
```

---

## Testing

Run the test suite using PHPUnit or Pest:

```bash
php artisan test
```

Make sure your testing database is properly configured in `.env.testing`.

---

## 📖 API Documentation (Swagger)

The API documentation is available via **Swagger / OpenAPI**.

After starting the application, access it at:

```
http://localhost:8000/api/documentation
```

The documentation provides:

* Available endpoints
* Request/response formats
* Authentication requirements
* Error codes and examples

---

## Security Notes

* Sensitive data is encrypted at rest
* OTP verification is rate-limited
* Transactions are protected against replay attacks
* Role-based access control (Admin, Partner, Enterprise, Customer)

This API is designed with **financial and fraud risks in mind**.

---

## Author

**Mohamed Thioune**
Senior Backend Engineer – Laravel
Software Developer Consultant

---

## License

This project is licensed under the `MaxBird, Codeur a la casquette`.

"Software developer because super hero is not a job title".
