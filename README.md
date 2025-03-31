
# InvestorApi

InvestorApi is a Symfony-based API designed to manage and track your personal investment portfolio. It allows you to organize assets across multiple symbols such as PLN, USD, BTC, ETH, AAPL, MSFT, and more. You can easily add, manage, and monitor the current value of your assets using real-time market data.

## 🌟 Features

- Manage multiple assets in your investment portfolio.
- Associate assets with symbols (currencies, stocks, cryptocurrencies).
- Access real-time quotations for symbols.
- Track the current value of your portfolio.

## 🛠️ Built with

- [Symfony](https://symfony.com/) – PHP framework.
- [Docker](https://www.docker.com/) – Containerized development environment.
- [PostgreSQL](https://www.postgresql.org/) – Database.
- [Nginx](https://www.nginx.com/) – Web server.

## 🐳 Docker Setup

This project uses Docker for a simple and consistent development environment:

```yaml
services:
  db:
    image: postgres:16
    environment:
      POSTGRES_USER: ${POSTGRES_USER}
      POSTGRES_PASSWORD: ${POSTGRES_PASSWORD}
      POSTGRES_DB: ${POSTGRES_DB}
    ports:
      - "5432:5432"
    volumes:
      - db-data:/var/lib/postgresql/data

  nginx:
    image: nginx:alpine
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
      - ./build/nginx/nginx.conf:/etc/nginx/nginx.conf
    depends_on:
      - php

  php:
    build:
      context: .
      dockerfile: ./build/Dockerfile
    volumes:
      - .:/var/www/html
    working_dir: /var/www/html
    depends_on:
      - db

volumes:
  db-data:
```

### 🚀 How to Run

1. Clone the repository:

```bash
git clone https://github.com/MichalWrzesinski/InvestorApi.git
```

2. Navigate to the project directory:

```bash
cd InvestorApi
```

3. Start Docker containers:

```bash
docker compose up -d
```

4. Access the application at:

```
http://localhost:8080
```

## 📖 API Documentation

Detailed API documentation and testing setup are available via [Postman](https://www.postman.com/). You can find the necessary files and further instructions in:

- `doc/postman/README.md`
- `doc/postman/InvestorApi.postman_environment.json`
- `doc/postman/InvestorApi.postman_collection.json`

## 📜 License

This project is licensed under the [MIT License](LICENSE).

## ✍️ Author

Michał Wrzesiński

🔗 https://webhome.pl