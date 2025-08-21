<?php

function db_myform_reply($post_id){
    //postの詳細
    $post = get_post($post_id);

    $group = get_field('fm_group', $post_id);
    $form = [];
    $i = 0;
    while ($i < 3) {
        $field_name = 'fm_form_' . ($i + 1);
        $i++;
        $row = get_field($field_name, $post_id);
        if (empty($row['fm_label'])) {
            continue;
        }
        $form[] = $row;
    }

    //回答の詳細
    $table = 'wp_my_form_reply_history';
    $select = 'wp_my_form_reply_history.*, wp_users.display_name';
    $col = [
        'post_id' => $post_id,
    ];
    $order = 'ORDER BY fm_re_id DESC';
    $join = 'LEFT JOIN wp_users ON wp_users.ID = user_id';
    $limit = 2000; // 1ページあたりの件数
    $start = 0; // 0件目から取得
    $list = db_all($table, $col, $select, $order, $join, $limit, $start);

    //回答の集計
    $reply_cc = count($list);
    $cc_array = [
        'Total' => $reply_cc,
    ];

    if ($reply_cc > 0) {
        foreach($list as $key => $d) {
            $answer = $d['answer'];
            $cc_array[$answer] = isset($cc_array[$answer]) ? $cc_array[$answer] + 1 : 1; 
        }
    }
    print_r($cc_array);

    $data = [
        'post' => $post,
        'group' => $group,
        'form' => $form,
        'list' => $list,
        'cc_array' => $cc_array,
    ];

    return $data;
}

/*-----------------------USER & Member---------------------*/
function db_get_member_with_user($user){
    if (!$user || !$user->ID) {
        return null;
    }

    // カスタム投稿memberでauthor = user_idの投稿を1件取得
    $args = [
        'post_type'      => 'member',
        'author'         => $user->ID,
        'post_status'    => 'publish',
        'posts_per_page' => 1,
    ];
    $query = new WP_Query($args);
    
    $member_post = null;
    if ($query->have_posts()) {
        $member_post = $query->posts[0];
    }

    $group = get_field('mb_group', $member_post->ID);

    // ユーザー情報とmember投稿を組み合わせて返す
    $data = [
        'id'    => $user->ID,
        'name'  => $user->display_name,
        'email' => $user->user_email,
        'member_post' => $member_post,
        'member_id'   => $member_post ? $member_post->ID : null,
        // 必要に応じてACFフィールドも追加
        'grade'       => $member_post ? get_field('mb_group_now_class', $member_post->ID) : null,
        'last_name'   => $member_post ? get_field('mb_group_name_2nd', $member_post->ID) : null,
        'first_name'  => $member_post ? get_field('mb_group_name_1st', $member_post->ID) : null,
        'last_kana'   => $member_post ? get_field('mb_group_kana_2nd', $member_post->ID) : null,
        'first_kana'  => $member_post ? get_field('mb_group_kana_1st', $member_post->ID) : null,
    ];

    return $data;
}


/*-----------------------SELECT---------------------*/
function db_all($table, $col, $select='*', $order=NULL, $join=NULL, $limit=999, $start=0){
    global $wpdb;
    $base = "SELECT %s FROM %s %s %s %s %s";
    $query = sprintf($base, $select, $table, $join, db_where($col), $order, db_limit($limit, $start));
    $list = $wpdb->get_results($query, ARRAY_A);
    return $list;
}

function db_row($table, $col, $select='*', $order=NULL, $join=NULL){
    global $wpdb;
    $base = "SELECT %s FROM %s %s %s %s";
    $query = sprintf($base, $select, $table, $join, db_where($col), $order);
    $row = $wpdb->get_row($query, ARRAY_A);
    return $row;
}

function db_where($col){
    $where = '';
    $i = 0;
    if($col){
        foreach($col as $k => $d){
            if($i == 0){
                $where .= "WHERE ";
            }else{
                $where .= " AND ";
            }

            if(is_array($d)){
                $d3 = '';
                foreach($d as $k2 => $d2){
                    if($k2 == 0){
                        $d3 .= "'{$d2}'";
                    }else{
                        $d3 .= ",'{$d2}'";
                    }
                }
                $where .= "{$k} in({$d3})";
            }elseif(strpos($k,'=') !== false){
                $where .= "{$k} '{$d}'";
            }elseif(strpos($d,'FIND_IN_SET') !== false){
                $where .= $d;
            }elseif(strpos($d,'NULL') !== false){
                $where .= "{$k} {$d}";
            }else{
                $where .= "{$k} = '{$d}'";
            }
            $i++;
        }
    }
    return $where;
}

function db_limit($limit, $start){
    $sql = '';
    if($limit){
        $sql .= "LIMIT {$start}, {$limit}";
    }
    return $sql;
}


/*-----------------------UPDATE INSERT DELETE---------------------*/
function db_insert($table, $data, $format=array()){
    global $wpdb;
    $wpdb->insert($table, $data, $format);
    return $wpdb->insert_id;
}

function db_update($table, $data, $tg, $format=NULL, $tg_format=NULL){
    global $wpdb;
    $wpdb->update($table, $data, $tg, $format, $tg_format);
}

function db_delete($table, $tg, $tg_format=NULL){
    global $wpdb;
    $wpdb->delete($table, $tg, $tg_format);
}