#!/bin/bash

clear


sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/template"






exit

sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/template"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
    -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/function"


##### template



sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit \
  tests/unit-tests/template_SingleTemplateTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit \
  tests/unit-tests/template_SidebarTemplateTest.php"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit \
  tests/unit-tests/template_SearchTemplateTest.php"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit \
  tests/unit-tests/template_PageTemplateTest.php"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit \
  tests/unit-tests/template_IndexTemplateTest.php"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit \
  tests/unit-tests/template_HomeTemplateTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit \
  tests/unit-tests/FunctionsTest.php"




sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit \
  tests/unit-tests/template_HeaderTemplateTest.php"







##### template_parts


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/template_parts_ContentTemplateTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/template_parts_ContentSlideTemplateTest.php"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/template_parts_ContentPageTemplateTest.php"



sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit \
  tests/unit-tests/template_parts_ContentNoneTemplateTest.php"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit \
  tests/unit-tests/template_parts_ContentArcTemplateTest.php"




##### inc のその他
sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/custom_header_InteglightCustomHeaderTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/template_tags_TemplateTagsTest.php"



##### integlight_functions s

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_FunctionsTest.php"



##### integlight_functions_base s

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_base_InteglightCustomizerBaseTest.php"

##### integlight_functions_block s

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_block_InteglightFunctionsBlockTest.php"



##### integlight_customizer s


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_integlight_customizer_sidebarTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_integlight_customizer_themeColorTest.php"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_customizer_Integlight_Customizer_FooterTest.php"


##### integlight_functions_outerAssets s


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_outerAssets_InteglightDeferCssTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_outerAssets_InteglightFrontendStylesTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_outerAssets_InteglightEditorStylesTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_outerAssets_InteglightFrontendScriptsTest.php"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_outerAssets_InteglightEditorScriptsTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_outerAssets_InteglightDeferJsTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/integlight_functions_outerAssets_InteglightMoveScriptsTest.php"



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