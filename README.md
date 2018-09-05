# WP Rest API Custom Endpoints

This library is a simple API for registering your own custom endpoints for the [WP REST API](https://developer.wordpress.org/rest-api/). Currently this library only supports custom post type endpoints.

## Register an endpoint

```php
$controller = new RestAPI\APIController( 'my-post-type-slug' );
$controller->registerRoute();
```

## Viewing endpoint

Once registered, your API endpoint will be available at:

```
/wp-json/{your-namespace}/{api-version}/{my-post-type-slug}/
```

You can pass WP_Query args as URL params:

```
/wp-json/{your-namespace}/{api-version}/{my-post-type-slug}/?posts_per_page=2&paged=1

## Filtering endpoint args

For filtering args, for example for a certain post type, use the `my_theme_parse_query_args` filter.

```php
function test($args, $params) {
  $args['posts_per_page'] = 1;
  return $args;
}
add_filter('my_theme_parse_query_args', 'test', 10, 2);
```