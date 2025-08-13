<?php
/*----------POST----------*/
function post_admin_submit(){
    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["form_type"])){
		if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], -1)){
			echo 'セキュリティ警告！<br>もう一度フォーム送信を行ってください';
			exit;
		}

		$form_type = $_POST["form_type"];
		switch($form_type){
			case 'admin_mig_db':
				if (!isset($_POST['table']) || !isset($_POST['do'])) {
					echo 'テーブル名と操作が指定されていません。<br>';
					exit;
				}
				
				$table = sanitize_text_field($_POST['table']);
				$do = sanitize_text_field($_POST['do']);
				
				if (!in_array($do, ['create', 'delete', 'truncate'])) {
					echo '無効な操作です。<br>';
					exit;
				}
				
				if (!mig_db_table($table, $do)) {
					echo 'データベース操作に失敗しました。<br>';
					exit;
				}				
				
				break;

			default:
				break;
		}

		echo '操作が完了しました。<br>';
		exit;
	}
}
add_action('_admin_menu', 'post_admin_submit');


/*----------base----------*/
function my_admin_style(){
    wp_enqueue_style( 'my_admin_style', get_stylesheet_directory_uri().'/assets/css/admin.css');
}
add_action( 'admin_enqueue_scripts', 'my_admin_style' );

/*----------dashbord----------*/
function remove_dashboard_widget() {
	remove_action( 'welcome_panel','wp_welcome_panel' ); // ようこそ
 	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' ); // 概要
 	remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' ); // アクティビティ
 	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' ); // クイックドラフト
 	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' ); // WordPress イベントとニュース
 	remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' ); // アクティビティ
} 
add_action('wp_dashboard_setup', 'remove_dashboard_widget' );

// オリジナルウィジェットを追加
function my_custom_dashboard_widgets() {
    global $wp_meta_boxes;
    wp_add_dashboard_widget('admin_mywj_help', '管理画面メニュー', 'dashboard_text');
}
add_action('wp_dashboard_setup', 'my_custom_dashboard_widgets');

//ウィジェットのPHP
function dashboard_text() {
    $html = get_template_part('sec_admin/admin-dashbord');
    echo $html;
}

//PAGE 追加ページ
function print_mypage($page) {
    echo get_template_part('sec_admin/admin-'.$page);
}
