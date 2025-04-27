#!/bin/sh

PROJECT_DIR="/var/www/log-ingestion"

# Copy pre-built Symfony project only if not already present
if [ ! -f "$PROJECT_DIR/composer.json" ]; then
  echo "📦 Copying Symfony project to mounted volume..."
  mkdir -p "$PROJECT_DIR"
  cp -R /symfony-template/. "$PROJECT_DIR"
else
  echo "✅ Symfony project already exists. Skipping copy."
fi

cd "$PROJECT_DIR"

if ! grep -q DATABASE_URL .env.local 2>/dev/null; then
  echo "DATABASE_URL=${DATABASE_URL}" > .env.local
fi

echo "⏳ Waiting for MySQL..."
until php bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1; do
  sleep 2
done

php bin/console doctrine:migrations:migrate --no-interaction || true

php -S 0.0.0.0:8000 -t public