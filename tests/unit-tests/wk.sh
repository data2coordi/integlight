
clear
sudo docker exec -it dev_wp_env_wordpress_1 bash -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit"
