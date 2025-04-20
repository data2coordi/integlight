#!/bin/bash

clear
#sudo docker exec -it dev_wp_env_wordpress_1 bash \
#    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit"


# Dockerコンテナ内で指定したテストファイルを実行
sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_slider_creSectionTest.php"
