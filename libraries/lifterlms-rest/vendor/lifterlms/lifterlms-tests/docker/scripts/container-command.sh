#!/bin/bash

# Adapted from: https://github.com/devowlio/wp-react-starter/blob/600b0fcdca469394fc51b6d0f5b457d9737aae0b/devops/docker-compose/docker-compose.yml

# Run original docker-entrypoint.sh because it is overwritten with "command"
docker-entrypoint.sh apache2
__dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Install wp-cli.
which wp && echo "Already installed: skipping wp-cli installation."
if ! $( which wp ); then

	echo "Installing wp-cli"
	curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
	chmod +x wp-cli.phar
	mv wp-cli.phar /usr/local/bin/wp
	echo "Success: wp-cli installed!"

fi

# Install apt dependencies.
# - netcat so we can ensure the DB is ready before running cli commands.
# - mariadb-client so mysqldump can be used by WP-CLI
which netcat && echo "Already installed: skipping apt dependency installation."
if ! $( which netcat ); then
	apt update
	apt -y install netcat mariadb-client
fi

# Ensure DB is ready before proceeding.
while ! nc -z mysql 3306; do sleep 1; done;

# Ensure that wp-content directory ownership is fixed.
# When bind mounting a local plugin directory into the wp-content/plugins directory we create permissions issues.
# See https://github.com/docker-library/wordpress/issues/436.
echo "Fixing directory permissions."
chown www-data:www-data /var/www/html/wp-content /var/www/html/wp-content/plugins
echo "Directory permissions fixed."

# Setup the WordPress Install.
# Run the following scripts only when WordPress is started at first time
# Use always --allow-root because docker runs the service as root user
if ! $(wp --allow-root core is-installed); then

    # Install WordPress itself.
    wp --allow-root core install \
    	--path="/var/www/html" \
    	--url="http://localhost:${WORDPRESS_PORT}" \
    	--title="${WORDPRESS_TITLE}" \
    	--admin_user="${WORDPRESS_USER_NAME}" \
    	--admin_password="${WORDPRESS_USER_PASS}" \
    	--admin_email="${WORDPRESS_USER_EMAIL}"

    # Update WP.
    wp --allow-root core update
    wp --allow-root core update-db

    # Setup wp-config.php.
    wp --allow-root config set WP_DEBUG false --raw
    wp --allow-root config set FS_METHOD direct # see https://git.io/fj4IK, https://git.io/fj4Ii

    # Permalink structure.
    wp --allow-root rewrite structure '/%postname%/' --hard

    # Remove default plugins.
    wp --allow-root plugin delete akismet hello

fi

# Main CMD from https://git.io/fj4Fe
apache2-foreground
