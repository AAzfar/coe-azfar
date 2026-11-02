#!/usr/bin/env sh
# Brings up the whole FreshKeep Market stack (database, API, website, pgweb)
# from a fresh clone. Safe to run again at any time.
set -eu

cd "$(dirname "$0")"

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker is not installed or not on PATH. Install Docker Desktop (with WSL 2 integration on Windows) and try again." >&2
  exit 1
fi

if ! docker info >/dev/null 2>&1; then
  echo "Docker is installed but not running. Start Docker Desktop and try again." >&2
  exit 1
fi

if [ ! -f src/backend/.env ]; then
  echo "Creating src/backend/.env from .env.example"
  cp src/backend/.env.example src/backend/.env
fi

echo "Building and starting containers (the first run takes a few minutes)..."
docker compose up --build --detach --wait

echo
echo "FreshKeep Market is running:"
echo "  Website   http://localhost:3000"
echo "  API       http://localhost:8000/api/products"
echo "  Database  http://localhost:8081 (pgweb)"
echo
echo "Follow logs with: docker compose logs -f backend frontend"
echo "Stop with:        docker compose down"
