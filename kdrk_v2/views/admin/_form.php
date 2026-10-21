<?php
/**
 * Panel formları için ortak alan üreticileri (haber, slayt).
 * Kullanım: ['v' => $v, 'err' => $err, 'input' => $input, 'textarea' => $textarea, 'select' => $select] = (require __DIR__ . '/../_form.php')($old, $row, $errors);
 * Değer önceliği: hata sonrası dönen form değeri → kayıt → varsayılan.
 */
return static function (array $old, ?array $row, array $errors): array {
    $v = static function (string $k, mixed $default = '') use ($old, $row): string {
        if (array_key_exists($k, $old)) {
            return (string) $old[$k];
        }
        if ($row !== null && array_key_exists($k, $row) && $row[$k] !== null) {
            return (string) $row[$k];
        }
        return (string) $default;
    };
    $err = static fn (string $k): string => isset($errors[$k]) ? '<span class="f-err" id="err-' . $k . '">' . e($errors[$k]) . '</span>' : '';
    $inv = static fn (string $k): string => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
    $label = static fn (string $k, string $text, array $opt): string => '<label for="f-' . $k . '">' . e($text) . (!empty($opt['req']) ? ' <span class="req" aria-hidden="true">*</span>' : '') . '</label>';
    $hint = static fn (array $opt): string => isset($opt['hint']) ? '<span class="f-hint">' . e($opt['hint']) . '</span>' : '';
    $attrs = static function (array $opt): string {
        $s = '';
        foreach ($opt['attrs'] ?? [] as $n => $val) {
            $s .= ' ' . $n . '="' . e((string) $val) . '"';
        }
        return $s . (!empty($opt['req']) ? ' required' : '');
    };

    $input = static fn (string $k, string $text, string $type = 'text', array $opt = []): string =>
        '<div class="field' . (!empty($opt['span']) ? ' col-span' : '') . '">' . $label($k, $text, $opt)
        . '<input id="f-' . $k . '" name="' . $k . '" type="' . $type . '" value="' . e($v($k, $opt['default'] ?? '')) . '"' . $attrs($opt) . $inv($k) . '>'
        . $hint($opt) . $err($k) . '</div>';

    $textarea = static fn (string $k, string $text, array $opt = []): string =>
        '<div class="field col-span">' . $label($k, $text, $opt)
        . '<textarea id="f-' . $k . '" name="' . $k . '" rows="' . ($opt['rows'] ?? 4) . '"' . $attrs($opt) . $inv($k) . '>' . e($v($k, $opt['default'] ?? '')) . '</textarea>'
        . $hint($opt) . $err($k) . '</div>';

    /** @param array<string, string> $choices değer => etiket */
    $select = static function (string $k, string $text, array $choices, array $opt = []) use ($v, $err, $inv, $label, $hint, $attrs): string {
        $cur = $v($k, $opt['default'] ?? '');
        $html = '<div class="field' . (!empty($opt['span']) ? ' col-span' : '') . '">' . $label($k, $text, $opt) . '<select id="f-' . $k . '" name="' . $k . '"' . $attrs($opt) . $inv($k) . '>';
        foreach ($choices as $val => $lab) {
            $html .= '<option value="' . e((string) $val) . '"' . ((string) $val === $cur ? ' selected' : '') . '>' . e($lab) . '</option>';
        }
        return $html . '</select>' . $hint($opt) . $err($k) . '</div>';
    };

    return compact('v', 'err', 'inv', 'input', 'textarea', 'select');
};
