<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * 設定：Next.js と共有するシークレット
 * 本番では .env や wp-config.php から読み込むのが安全
 */
define('CUR_SHARED_SECRET', 'abcd1234efgh5678'); // 例: 任意の長い文字列

/**
 * 署名検証
 */
function cur_verify_signature( $raw_body, $signature_header ) {
    if ( empty($signature_header) ) return false;
    $calc = hash_hmac('sha256', $raw_body, CUR_SHARED_SECRET);
    // ヘッダは "sha256=..." でも "..." だけでもOKにしておく
    $sig = preg_replace('/^sha256=/', '', $signature_header);
    return hash_equals($calc, $sig);
}

/**
 * CORS 設定（ローカル用）
 */
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/register', [
        'methods'  => ['POST', 'OPTIONS'],
        'callback' => 'cur_handle_register',
        'permission_callback' => '__return_true', // 外部から叩けるように
    ]);
});

function cur_handle_register( WP_REST_Request $request ) {

    // 生のボディと署名を取得
    $raw = $request->get_body();
    $sig  = isset($_SERVER['HTTP_X_SIGNATURE']) ? $_SERVER['HTTP_X_SIGNATURE'] : '';

    if ( ! cur_verify_signature($raw, $sig) ) {
        return new WP_REST_Response(['error' => 'invalid signature'], 401);
    }

    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_REST_Response(['error' => 'invalid json'], 400);
    }

    // 受け取るフィールド（必要に応じて増やす）
    $grade           = isset($data['grade']) ? sanitize_text_field($data['grade']) : '';
    $class           = isset($data['class']) ? sanitize_text_field($data['class']) : '';
    $last            = isset($data['lastName']) ? sanitize_text_field($data['lastName']) : '';
    $first           = isset($data['firstName']) ? sanitize_text_field($data['firstName']) : '';
    $last_kana       = isset($data['lastNameKana']) ? sanitize_text_field($data['lastNameKana']) : '';
    $first_kana      = isset($data['firstNameKana']) ? sanitize_text_field($data['firstNameKana']) : '';
    $email           = isset($data['email']) ? sanitize_email($data['email']) : '';
    $password        = wp_generate_password(12, true); // パスワード自動生成（必要なら変更）
    $username        = $email ? sanitize_user( current( explode('@', $email) ), true ) : '';
    $line_id         = 'Ua08801bcbe21d7c2985ed58d24006472'; // 必要なら外す

    // バリデーション
    if ( empty($email) ) {
        return new WP_REST_Response(['error' => 'email is required'], 422);
    }
    if ( email_exists($email) ) {
        return new WP_REST_Response(['error' => 'email already exists'], 409);
    }

    // username が未指定なら email から生成
    $base = $username;
    $i = 1;
    while ( username_exists($username) ) {
        $username = $base . $i;
        $i++;
    }

    // ユーザー作成
    $user_id = wp_insert_user([
        'user_login'   => $username,
        'user_pass'    => $password,
        'user_email'   => $email,
        'first_name'   => $first,
        'last_name'    => $last,
        'role'         => 'subscriber',
        'display_name' => $last . ' ' . $first,
    ]);

    if ( is_wp_error($user_id) ) {
        return new WP_REST_Response(['error' => $user_id->get_error_message()], 500);
    }

    // 任意メタデータを保存
    if ( $line_id ) {
        update_user_meta($user_id, 'line_user_id', $line_id);
    }

    // カスタム投稿 "member" を作成
    $member_post = [
        'post_title'  => $last . ' ' . $first,
        'post_type'   => 'member',
        'post_status' => 'publish',
        'post_author' => $user_id,
    ];
    $member_post_id = wp_insert_post($member_post);

    if (!is_wp_error($member_post_id)) {
        // ACFグループ"mb_group"のサブフィールドに保存
        update_field('mb_group_now_class', $grade .'-'. $class, $member_post_id); // 例: "3-1"
        update_field('mb_group_name_2nd', $last, $member_post_id);
        update_field('mb_group_name_1st', $first, $member_post_id);
        update_field('mb_group_kana_2nd', $last_kana, $member_post_id);
        update_field('mb_group_kana_1st', $first_kana, $member_post_id);
    }

    return new WP_REST_Response([
        'ok'            => true,
        'user_id'       => (int)$user_id,
        'username'      => $username,
        'email'         => $email,
        'grade'         => $grade,
        'class'         => $class,
        'lastName'      => $last,
        'firstName'     => $first,
        'lastNameKana'  => $last_kana,
        'firstNameKana' => $first_kana,
    ], 201);
};


add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/me', [
        'methods'  => ['GET', 'POST'],
        'callback' => 'cur_handle_me',
        'permission_callback' => '__return_true', // 認証不要で外部から叩けるように
    ]);
});

function cur_handle_me(WP_REST_Request $request) {
    // POSTの場合はline_idで検索
    if ($request->get_method() === 'POST') {
        $params = $request->get_json_params();
        $line_id = isset($params['line_id']) ? sanitize_text_field($params['line_id']) : '';
        if (empty($line_id)) {
            return new WP_REST_Response(['error' => 'line_id is required'], 400);
        }

        // line_idでユーザー検索
        $user_query = new WP_User_Query([
            'meta_key'   => 'line_user_id',
            'meta_value' => $line_id,
            'number'     => 1,
        ]);
        $users = $user_query->get_results();
        if (empty($users)) {
            return new WP_REST_Response(['error' => 'user not found'], 404);
        }
        $user = $users[0];
        return db_get_member_with_user($user); // ユーザー情報とmember投稿を取得
    }

    // GETの場合はログインユーザー情報
    $user = wp_get_current_user();
    if (!$user || !$user->ID) {
        return new WP_REST_Response(['error' => 'not_logged_in'], 401);
    }
    return db_get_member_with_user($user); // ユーザー情報とmember投稿を取得
}

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/survey_history', [
        'methods'  => ['GET'],
        'callback' => 'cur_handle_survey_history',
        'permission_callback' => '__return_true',
    ]);
});

function cur_handle_survey_history(WP_REST_Request $request) {
    $user_id = $request->get_param('user_id');
    if (empty($user_id)) {
        return new WP_REST_Response(['error' => 'user_id is required'], 400);
    }

    // post_type = myform で、投稿者が$user_idの投稿を取得
    $args = [
        'post_type'      => 'myform',
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];
    $query = new WP_Query($args);

    $history = [];
    foreach ($query->posts as $post) {
        $history[] = [
            'id'    => $post->ID,
            'title' => get_the_title($post),
            'date'  => get_the_date('Y-m-d', $post),
            // 必要に応じて他のフィールドも追加
        ];
    }

    return $history;
}
