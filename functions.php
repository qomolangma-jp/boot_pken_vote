<?php
//libraryの読み込み
require_once(get_stylesheet_directory().'/library/func-post.php');
require_once(get_stylesheet_directory().'/library/func-admin.php');
require_once(get_stylesheet_directory().'/library/func-db.php');
require_once(get_stylesheet_directory().'/library/func-mig.php');

add_action('rest_api_init', function () {
  register_rest_field('member', 'profile', [
    'get_callback' => function ($obj) {
      return get_post_meta($obj['id'], 'profile', true);
    },
    'schema' => ['type' => 'string']
  ]);
});

/**
 * REST全応答にCORSヘッダーを付ける + プリフライトを204で返す
 * - エラー時/成功時/OPTIONS すべてに適用
 * - Originは必要に応じて固定（例：http://localhost:3000）
 */

// 必要なら定数化（末尾スラなしで一致）
if ( ! defined('CUR_ALLOWED_ORIGIN') ) {
  define('CUR_ALLOWED_ORIGIN', 'http://localhost:3000');
}

// WPデフォのCORS送信を外し、自前で統一
add_action('rest_api_init', function() {
  remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
  add_filter('rest_pre_serve_request', function($served, $result, $request) {
    header('Access-Control-Allow-Origin: ' . CUR_ALLOWED_ORIGIN);
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Signature');
    header('Access-Control-Allow-Credentials: true');
    return $served;
  }, 10, 3);
}, 15);

// /wp-json/... 宛てのプリフライトを早期204
add_action('init', function() {
  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS' && strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) {
    header('Access-Control-Allow-Origin: ' . CUR_ALLOWED_ORIGIN);
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Signature');
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
    status_header(204);
    exit;
  }
});
