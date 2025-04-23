#!/bin/bash

clear




sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_outerAssets_InteglightDeferCssTest.php"





exit 

sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit"

exit
##### integlight_functions s
sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_InteglightTableOfContentsTest.php"



sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_InteglightSetupPlusTest.php"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_InteglightCommonCssAssetsTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
   -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_InteglightCommonJsAssetsTest.php"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_InteglightBreadcrumbTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_Integlight_SEO_MetaTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_Integlight_PostHelperTest.php"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/nteglight_functions_Integlight_Excerpt_CustomizerTest.php"



##### integlight_functions e

##### integlight_customizer_slider s
sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_slider_creSectionTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_sliderTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_slider_HeaderTypeSelecterTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_slider_headerImage_updSectionTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
   -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_slider_settingTest.php"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
   -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_slider_applyHeaderTextStyleTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
   -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_slider_outerAssetsTest.php"

##### integlight_customizer_slider e