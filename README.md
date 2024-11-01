# Wiremo Wordpress plugin

This is Wiremo plugin for WordPress. This plugin was created via gutenberg blocks using [create-guten-block](https://github.com/ahmadawais/create-guten-block).

## Prerequisites

This service use: PHP, JavaScript and NodeJS.

## Starting

For development should be installed XAMPP PHP development environment.

1. Create a local WordPress site using XAMPP. Using [guide](https://www.wpbeginner.com/wp-tutorials/how-to-create-a-local-wordpress-site-using-xampp).
2. Archive `woocommerce-plugin` and insert as plugin in the site.
3. Activate Wiremo plugin from plugins page.

## CI

CI workflow name is `wordpress-plugin` (see .circleci/config.yml)

## Deployment

Deployment is done using docker swarm (see `.deploy/wiremo/deploy.sh` for more details).

## FAQ

For frequently issues see: [Wordpress FAQ](https://wiremo.atlassian.net/wiki/spaces/WIR/pages/318898177/Woo+WordPress+FAQ)
