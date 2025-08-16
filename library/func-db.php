<?php

function db_myform_reply($post_id){
    //postの詳細
    $post = get_post($post_id);
    $slugs = [
        'fm_group' => 'fm_group',
        'fm_form_1' => 'fm_form_1',
        'fm_form_2' => 'fm_form_2',
        'fm_form_3' => 'fm_form_3', // 追加
    ];

    $group = [];
    foreach ($slugs as $slug => $field_name) {
        $group[$slug] = get_field($field_name, $post_id);
    }

    //回答の詳細
    $table = 'wp_my_form_reply_history';
    $select = 'wp_my_form_reply_history.*, wp_users.display_name';
    $col = [
        'post_id' => $post_id,
    ];
    $order = 'ORDER BY fm_re_id DESC';
    $join = 'LEFT JOIN wp_users ON wp_users.ID = user_id';
    $limit = 100; // 1ページあたりの件数
    $start = 0; // 0件目から取得
    $list = db_all($table, $col, $select, $order, $join, $limit, $start);




    //回答の集計
    //print_r($list);

    $data = [
        'post' => $post,
        'group' => $group,
        'list' => $list,
    ];

    return $data;
}


/*
$table = 'wp_my_form_reply_history';
$col = array(
    'user_id' => 1,
);
$limit = 25;
$start = 0; // 0件目から取得
$order = 'ORDER BY fm_re_id DESC';
$join = null;

$list = db_all($table, $col, $limit, $start, $order, $join);
print_r($list);
echo $list[0]['str'];
echo '<br>';
$row = db_row($table, $col, $order, $join);
print_r($row);
echo $row['str'];
echo '<br>';
*/

/*-----------------------SELECT---------------------*/
function db_all($table, $col, $select='*', $order=NULL, $join=NULL, $limit=999, $start=0){
    global $wpdb;
    $base = "SELECT %s FROM %s %s %s %s %s";
    $query = sprintf($base, $select, $table, $join, db_where($col), $order, db_limit($limit, $start));
    echo $query;
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