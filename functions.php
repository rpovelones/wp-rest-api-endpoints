<?php

use RPD\RestAPI;

/**
 * Function to register our new routes from the controller.
 *
 * @return void
 */
function my_theme_register_routes() {
  $controller = new RestAPI\APIController( 'post' );
  $controller->registerRoute();
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\my_theme_register_routes' );
