# Postman – API Testing Setup for InvestorApi

This directory contains Postman configuration files to help you test the InvestorApi project quickly and consistently.

## 📁 Files

- `InvestorApi.postman_environment.json` – Postman environment with useful variables (e.g., `base_url`, `jwt_token`, `Authorization`)
- `InvestorApi.postman_collection.json` – A ready-to-use Postman collection with:
    - `🔐 Login (JWT)` – Authenticates a user and stores the JWT token
    - `✅ Ping` – Simple health-check endpoint
    - `👤 Users` – Manage user resources (list, get by ID, create, update, delete)
    - `💰 Symbols` – Manage symbol resources (list, get by ID, create, update, delete)
    - `📦 UserAssets` – Manage user asset resources (list, get by ID, create, update, delete)

## 🚀 How to Use

1. Open [Postman](https://www.postman.com/)
2. Import both files from this folder:
    - Environment: `InvestorApi.postman_environment.json`
    - Collection: `InvestorApi.postman_collection.json`
3. Select the environment **InvestorApi** from the environment dropdown.
4. Run the request **🔐 Login (JWT)** to obtain a JWT token and store it in the environment.

## 🧪 Sample Credentials (adjust as needed)

```json
{
  "email": "admin@example.com",
  "password": "root"
}
```
