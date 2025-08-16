<?php
$list = db_myform_reply();
//print_r($list);
?>
<div id="page-webentry" class="wrp-my_admin wrap">
    <h1 class="page-title"><?php echo get_admin_page_title(); ?></h1>
    <div class="page-content">
        <div class="form-group">
            <h2>回答一覧</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>投稿者</th>
                        <th>質問</th>
                        <th>回答内容</th>
                        <th>回答日時</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $item): ?>
                        <tr>
                            <td>
                                <?php echo esc_html($item['fm_re_id']); ?>
                                <a class="btn btn-primary btn-sm" href="<?php echo admin_url('edit.php?post_type=myform&page=myform_row&post_id=' . $item['fm_re_id']); ?>">編集</a>
                            </td>
                            <td><?php echo esc_html($item['user_id']); ?></td>
                            <td><?php echo esc_html($item['post_id']); ?></td>
                            <td>
                                <?php echo esc_html($item['answer']); ?>
                                <br>
                                <small><?php echo esc_html($item['str']); ?></small>
                            </td>
                            <td><?php echo esc_html(date('Y-m-d H:i:s', strtotime($item['created']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>