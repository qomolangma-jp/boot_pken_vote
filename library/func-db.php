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
$tg = [
    'fm_re_id' => 6,
];
$data = [
    'answer' => 'NO',
];
db_update($table, $data, $tg);
print_r($tg);
exit;

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