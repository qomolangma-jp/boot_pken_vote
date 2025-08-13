<?php

function data_myform_reply($limit=100){
    global $wpdb;
    $table = $wpdb->prefix . 'my_form_reply_history';
    $base = "SELECT * FROM %s";
    $query = sprintf($base, $table);
    //echo $query;
    $list = $wpdb->get_results($query, ARRAY_A);
    return $list;
}