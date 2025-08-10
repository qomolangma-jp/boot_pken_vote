<?php
//libraryの読み込み
require_once(get_stylesheet_directory().'/library/func-admin.php');



add_action('rest_api_init', function () {
  register_rest_field('member', 'profile', [
    'get_callback' => function ($obj) {
      return get_post_meta($obj['id'], 'profile', true);
    },
    'schema' => ['type' => 'string']
  ]);
});
