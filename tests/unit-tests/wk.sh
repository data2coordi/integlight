#!/bin/bash

clear



sudo docker exec -it dev_wp_env_wordpress_1 bash \
   -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_slider_outerAssetsTest.php"


exit 

sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit"

exit

sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_slider_creSectionTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_sliderTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_HeaderTypeSelecterTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_headerImage_updSectionTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
   -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_slider_settingTest.php"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
   -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_slider_applyHeaderTextStyleTest.php"
