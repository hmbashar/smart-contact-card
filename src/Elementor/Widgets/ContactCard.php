<?php
namespace Smartcc\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class ContactCard extends Widget_Base
{
    public function get_name(){ return 'smartcc_contact_card'; }
    public function get_title(){ return __('SmartCC Contact Card','smart-contact-card'); }
    public function get_icon(){ return 'eicon-person'; }
    public function get_categories(){ return ['general']; }

    protected function register_controls(){
        $this->start_controls_section('content', ['label'=>__('Content','smart-contact-card')]);
        foreach (['name','title','org','phone','email','website','address','whatsapp','telegram','imo','skype','qrtext','button'] as $field) {
            $this->add_control($field, [
                'label' => ucfirst($field),
                'type'  => Controls_Manager::TEXT,
                'default' => '',
            ]);
        }
        $this->add_control('qr', [
            'label' => __('QR Mode','smart-contact-card'),
            'type'  => Controls_Manager::SELECT,
            'options' => [ 'inline_vcard'=>'Inline vCard', 'custom'=>'Custom Payload' ],
            'default' => 'inline_vcard',
        ]);
        $this->add_control('layout', [
            'label' => __('Layout','smart-contact-card'),
            'type'  => Controls_Manager::SELECT,
            'options' => [ 'card'=>'Card', 'inline'=>'Inline' ],
            'default' => 'card',
        ]);
        $this->end_controls_section();
    }

    protected function render(){
        $s = $this->get_settings_for_display();
        $atts = [];
        foreach ($s as $k=>$v) {
            if ($v === '' || is_array($v)) continue;
            $atts[] = $k.'="'.esc_attr($v).'"';
        }
        echo do_shortcode('[smartcc_contact '.implode(' ', $atts).']');
    }
}
