<?php
function mig_db_table($table, $do){
    global $wpdb;

    // テーブル名をプレフィックス付きで設定 wp_ + my_form_reply_history
    $table_name = $wpdb->prefix . $table;

    if ($do === 'create') {
        $charset_collate = $wpdb->get_charset_collate();

        switch($table){
            case 'my_form_reply_history':
                $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
                    `fm_re_id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `user_id` bigint(20) NOT NULL,
                    `post_id` bigint(20) NOT NULL,
                    `answer` varchar(255) NOT NULL,
                    `str` text NULL DEFAULT NULL,
                    `history` text NULL DEFAULT NULL,
                    `created` datetime NULL DEFAULT current_timestamp,
                    `updated` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY fm_re_id (`fm_re_id`),
                    INDEX `index_$table`(`user_id`, `post_id`, `answer`, `updated`) USING BTREE
                    ) $charset_collate;";

                break;

            default:
                return false;
        }
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        return true;

    } elseif ($do === 'delete') {
        // テーブル削除クエリ
        $sql = "DROP TABLE IF EXISTS `$table_name`;";
        return $wpdb->query($sql);

    } elseif ($do === 'truncate') {
        // テーブルのデータを削除
        $sql = "TRUNCATE TABLE `$table_name`;";

        return $wpdb->query($sql);
    }    

    return false;
}