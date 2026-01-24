#!/bin/sh

echo "Running Laravel setup..."

# Ensure storage permissions
chmod -R 775 storage bootstrap/cache

# Link storage
echo "Linking storage..."
php artisan storage:link

# Run migrations only if role is app or undefined (default)
if [ -z "$CONTAINER_ROLE" ] || [ "$CONTAINER_ROLE" = "app" ]; then
    echo "Running migrations..."
    php artisan migrate --force
else
    echo "Skipping migrations (role: $CONTAINER_ROLE)"
fi

# Cache config
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "Laravel setup completed."
