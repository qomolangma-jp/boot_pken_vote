<?php
$slugs = [
    'fm_group' => 'fm_group',
    'fm_form_1' => 'fm_form_1',
    'fm_form_2' => 'fm_form_2',
    'fm_form_3' => 'fm_form_3', // 追加
];

$data = [];
foreach ($slugs as $slug => $field_name) {
    $data[$slug] = get_field($field_name, 40);
}

$value = $data['fm_form_1']['fm_value'];
$radio = explode(',', $value);


?>

<h2><?php echo $data['fm_group']['fm_title'] ?></h2>
<p><?php echo nl2br($data['fm_group']['fm_text']); ?></p>

<form>
    <?php foreach(['fm_form_1', 'fm_form_2', 'fm_form_3'] as $form_key): ?>
        <?php
            $form = $data[$form_key];
            // fm_labelが空ならスキップ
            if (empty($form['fm_label'])) {
                continue;
            }
            $options = isset($form['fm_value']) ? explode(',', $form['fm_value']) : [];
        ?>
        <div class="form-group">
            <label for="<?php echo $form_key; ?>"><?php echo $form['fm_label']; ?></label><br>
            <?php if($form['fm_type'] == 'text'): ?>
                <input type="text" class="form-control" id="<?php echo $form_key; ?>" name="<?php echo $form_key; ?>" value="">
            <?php elseif($form['fm_type'] == 'textarea'): ?>
                <textarea class="form-control" id="<?php echo $form_key; ?>" name="<?php echo $form_key; ?>"></textarea>
            <?php elseif($form['fm_type'] == 'radio'): ?>
                <?php foreach($options as $option): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="<?php echo $form_key; ?>" id="<?php echo $form_key . '_' . esc_attr($option); ?>" value="<?php echo esc_attr($option); ?>">
                        <label class="form-check-label" for="<?php echo $form_key . '_' . esc_attr($option); ?>">
                            <?php echo esc_attr($option); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</form>