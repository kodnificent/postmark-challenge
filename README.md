## 🛠️ Local Development Setup

Clause Clarity is powered by Laravel Octane (Swoole), queue workers, and inbound email support via Postmark.  
The development environment runs fully in Docker.

---

### ✅ Requirements

- [Docker](https://www.docker.com/) installed
- [Docker Compose](https://docs.docker.com/compose/install/)

---

### 🚀 Getting Started

#### 1. Clone the repository

```bash
git clone https://github.com/kodnificent/postmark-challenge.git
cd postmark-challenge
```

#### 2. Copy the example environment file
```bash
cp .env.example .env
```

Update the following variables as needed:

- `APP_KEY` (can be generated via artisan below)
- `OPENAI_API_KEY` (required for contract analysis)
- `EMAIL_FORWADING_DOMAIN` (used for email-based reviews)

#### 3. Start the containers
```bash
docker-compose up -d
```

This starts:
- Laravel app with Nginx and Octane
- MySQL database
- Redis server

Services will be exposed on the following ports:

Service	Port
- App (Octane)	http://localhost
- MySQL	3306
- Redis	6379

#### 4. Install PHP dependencies
```bash
docker-compose exec app composer install
```

#### 5. Generate the app key
```bash
docker-compose exec app php artisan key:generate
```

#### 6. Run database migrations
```bash
docker-compose exec app php artisan migrate
```
⛔ You do not need to run queue:work.
👷 Queue workers and Octane are automatically handled by supervisord inside the container.

### 🧪 Testing Email
To test the inbound email workflow:

1. Send an email with a PDF attachment to an address like yourusername@inbound.kodnificent.xyz

2. Ensure the username matches an existing users.username record in the database

3. The system will automatically:
- Decode the PDF
- Extract the text
- Create a Review for the user
- Dispatch the contract for analysis via OpenAI

### 🛑 Shut Down Containers
```bash
docker-compose down
```
