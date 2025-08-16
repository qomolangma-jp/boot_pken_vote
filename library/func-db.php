<?php

function db_myform_reply($limit=100){
    global $wpdb;
    $table = $wpdb->prefix . 'my_form_reply_history';
    $base = "SELECT * FROM %s";
    $query = sprintf($base, $table);
    //echo $query;
    $list = $wpdb->get_results($query, ARRAY_A);
    return $list;
}






$table = 'wp_my_form_reply_history';
$col = array(
    'user_id' => 1,
);
$limit = 25;
$start = 0; // 0件目から取得
$order = 'ORDER BY fm_re_id ASC';
$join = null;

$list = db_all($table, $col, $limit, $start, $order, $join);
print_r($list);
echo $list[0]['str'];
echo '<br>';
$row = db_row($table, $col, $order, $join);
print_r($row);
echo $row['str'];
echo '<br>';
exit;

/*-----------------------SELECT---------------------*/
function db_all($table, $col, $limit=999, $start=0, $order=NULL, $join=NULL){
    global $wpdb;
    $base = "SELECT * FROM %s %s %s %s %s";
    $query = sprintf($base, $table, $join, db_where($col), $order, db_limit($limit, $start));
    
    $list = $wpdb->get_results($query, ARRAY_A);
    return $list;
}

function db_row($table, $col, $order=NULL, $join=NULL){
    global $wpdb;
    $base = "SELECT * FROM %s %s %s %s";
    $query = sprintf($base, $table, $join, db_where($col), $order);
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