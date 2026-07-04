# Codex Instructions For This Project

This is a Laravel 13 Docker-based ERP project hosted on a VPS and deployed from this repository.

After every successful code change:
1. Run the relevant verification command when possible.
2. Run `git status`.
3. Commit the change with a clear message.
4. Push to GitHub `origin main`.

Do not commit or push:
- `.env`
- database data
- uploaded files
- secrets
- Cloudflare tunnel tokens
- Docker volume contents

Before any destructive command, ask for confirmation.

Common commands:
- `docker compose run --rm artisan migrate`
- `docker compose run --rm artisan config:clear`
- `docker compose run --rm artisan optimize:clear`
- `docker compose run --rm composer install`
