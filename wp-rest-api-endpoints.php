<?php

namespace RPD\RestAPI;

class APIController {

  /**
   * The post type.
   *
   * @var string
   */
  public $post_type;

  /**
   * The namespace for our endpoint.
   *
   * @var string
   */
  public $namespace = '/my-namespace';

  /**
   * Our API version.
   *
   * @var string
   */
  public $api_version = '/v1';

  /**
   * Initialize with a post type
   */
  public function __construct( $post_type ) {
    $this->post_type = $post_type;
  }

  /**
   * Get the endpoint base.
   *
   * @return string
   */
  private function getPostTypePluralLabel() {
    $obj = get_post_type_object( $this->post_type );
    return $obj->rest_base ? $obj->rest_base : strtolower($obj->labels->name);
  }

  /**
   * Register a single API route.
   *
   * @return void
   */
  public function registerRoute() {
    $namespace = $this->namespace . $this->api_version;
    $route = '/' . $this->getPostTypePluralLabel();
    register_rest_route( $namespace, $route, array(
      array(
        'methods'   => 'GET',
        'callback'  => array( $this, 'getItems' ),
      ),
    ));
  }

  /**
   * Loads php template prepared for returning in JSON response.
   *
   * @param int | $id - the post ID
   * @param string | $post_type - for naming the tempalate
   * @return html
   */
  private function loadTemplatePart( $id ) {

    $filename = get_template_directory().'/templates/content-'.$this->post_type.'.php';

    if ( !file_exists($filename) ) {
      return '';
    }

    ob_start();
    include($filename);
    return ob_get_clean();
  }

  /**
   * Build args for our query.
   *
   * @param array $params
   * @return array
   */
  private function buildQueryArgs( $params ) {

    // set some defaults
    $args = [
      'post_type' => $this->post_type,
      'posts_per_page' => 100,
    ];

    // process any additional params from our rest request.
    if( $params ) {
      foreach( $params as $param => $value ) {
        $args[$param] = htmlentities($value);
      }
    }

    return $args;
  }

  /**
   * Do a WP_Query.
   *
   * @param array | $args
   * @return WP_Query object
   */
  private function doQuery( $args ) {
    return new \WP_Query($args);
  }

  /**
   * Grabs all posts and outputs them as a rest response.
   *
   * @param WP_REST_Request $request Current request.
   * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
   */
  public function getItems( $request ) {

    $type = $this->post_type;
    $params = $request->get_query_params();
    $data = [];

    // filter the args before doing query
    $args = apply_filters('my_theme_parse_query_args', $this->buildQueryArgs($params), $params);

    // do a query w/ filtered args
    $query = $this->doQuery($args);
    $total_posts = $posts->found_posts;
    $max_pages = ceil( $total_posts / (int) $args['posts_per_page'] );

    // process the posts
    if ( !empty($query->posts) ) {
      foreach( $query->posts as $post => $item ) {
        $data[] = [
          'id' => $item->ID,
          'title' => $item->post_title,
          'html' => $this->loadTemplatePart( $item->ID )
        ];
      }
    } else {
      $data = [];
    }

    // make sure we are returning a WP_REST_Response object
    $response = rest_ensure_response( $data );

    // set response headers for pagination
    $response->header( 'X-WP-Total', (int) $total_posts );
    $response->header( 'X-WP-TotalPages', (int) $max_pages );

    return $response;
  }

}