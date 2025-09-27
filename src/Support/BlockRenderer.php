<?php
namespace Smartcc\Support;

class BlockRenderer
{
    public static function render_contact_card(array $attributes, string $content): string
    {
        $atts = [];
        foreach ($attributes as $k => $v) {
            if ($v === '' || $v === null || is_array($v)) continue;
            $atts[] = $k . '="' . esc_attr($v) . '"';
        }
        return do_shortcode('[smartcc_contact ' . implode(' ', $atts) . ']');
    }
}
