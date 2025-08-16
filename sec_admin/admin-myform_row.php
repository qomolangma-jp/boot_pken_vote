<?php
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
$data = db_myform_reply($post_id);
$post = $data['post'];
$group = $data['group'];
$list = $data['list'];
print_r($data);
?>

<?php if(empty($post)): ?>
<div class="sec">
    <div class="my-form">
        <div class="postbox acf-postbox">
            <div class="inside acf-fields -top">
                <div class="acf-field">
                    <h2>対象のデータが見つかりません。<br>
                    「フォーム一覧」から選択しなおしてください。
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div id="page-webentry" class="wrp-my_admin wrap">
    <div class="page-content">
        <div class="form-group">
            <h2>アンケート内容</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="180">label</th>
                        <th>value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>ポストタイトル(post_id)</th>
                        <td><?php echo esc_html($post->post_title); ?> (<?php echo esc_html($post->ID); ?>)</td>
                    </tr>
                    <tr>
                        <th>フォームタイトル</th>
                        <td><?php echo $group['fm_group']['fm_title']; ?></td>
                    </tr>
                    <tr>
                        <th>フォームの説明</th>
                        <td><?php echo nl2br($group['fm_group']['fm_text']); ?></td>
                    </tr>

                </tbody>
            </table>
        </div>

        <div class="form-group">
            <h2>回答一覧</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>回答者 (user_id)</th>
                        <th>回答内容</th>
                        <th>回答日時</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $item): ?>
                        <tr>
                            <td>
                                <?php echo esc_html($item['fm_re_id']); ?>
                            </td>
                            <td>
                                <?php echo esc_html($item['display_name']); ?>
                                (<?php echo esc_html($item['user_id']); ?>)
                            </td>
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

<?php endif; ?>