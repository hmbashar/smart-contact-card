<?php
namespace Smartcc\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) exit;

class ContactCard extends Widget_Base
{
    public function get_name() {
        return 'smartcc_contact_card';
    }

    public function get_title() {
        return __('SmartCC Contact Card', 'smart-contact-card');
    }

    public function get_icon() {
        return 'eicon-person';
    }

    public function get_categories() {
        // you can create your own category later; 'general' works fine
        return ['general'];
    }

    protected function register_controls() {
        // Content: Identity
        $this->start_controls_section('section_identity', [
            'label' => __('Identity', 'smart-contact-card')
        ]);

        $this->add_control('name', [
            'label' => __('Name', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'default' => '',
            'placeholder' => 'Md Abul Bashar',
        ]);
        $this->add_control('title', [
            'label' => __('Title', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'default' => '',
            'placeholder' => 'Founder',
        ]);
        $this->add_control('org', [
            'label' => __('Organization', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'default' => '',
            'placeholder' => 'Nexiby LLC',
        ]);
        $this->add_control('avatar', [
            'label' => __('Avatar URL', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'input_type' => 'url',
            'placeholder' => 'https://example.com/avatar.jpg',
        ]);

        $this->end_controls_section();

        // Content: Contacts
        $this->start_controls_section('section_contacts', [
            'label' => __('Contacts', 'smart-contact-card')
        ]);

        $this->add_control('phone', [
            'label' => __('Phone', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'default' => '',
        ]);
        $this->add_control('email', [
            'label' => __('Email', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'input_type' => 'email',
            'default' => '',
        ]);
        $this->add_control('website', [
            'label' => __('Website', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'input_type' => 'url',
            'default' => '',
        ]);
        $this->add_control('address', [
            'label' => __('Address', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'default' => '',
        ]);

        $this->add_control('whatsapp', [
            'label' => __('WhatsApp (E.164)', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'placeholder' => '+8801...',
        ]);
        $this->add_control('telegram', [
            'label' => __('Telegram', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'placeholder' => '@username',
        ]);
        $this->add_control('imo', [
            'label' => __('imo', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'placeholder' => 'imo_id',
        ]);
        $this->add_control('skype', [
            'label' => __('Skype', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'placeholder' => 'skype.id',
        ]);
        $this->add_control('wechat', [
            'label' => __('WeChat', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'placeholder' => 'wechat_id',
        ]);

        $this->end_controls_section();

        // Content: Design & QR
        $this->start_controls_section('section_design', [
            'label' => __('Design & QR', 'smart-contact-card')
        ]);

        // exactly matches your shortcode attr; default keeps your existing design
        $this->add_control('design', [
            'label' => __('Design', 'smart-contact-card'),
            'type'  => Controls_Manager::SELECT,
            'options' => [
                'default'    => __('Default (full card)', 'smart-contact-card'),
                'minimal_qr' => __('Minimal + Custom QR only', 'smart-contact-card'),
            ],
            'default' => 'default',
        ]);

        $this->add_control('button', [
            'label' => __('Button text (default design)', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'default' => 'Save Contact (.vcf)',
            'condition' => [ 'design' => 'default' ],
        ]);

        // Map to your existing qr_for (only used by default design)
        $this->add_control('qr_for', [
            'label' => __('QR Payload (default design)', 'smart-contact-card'),
            'type'  => Controls_Manager::SELECT,
            'options' => [
                'vcard'    => 'vCard',
                'whatsapp' => 'WhatsApp',
                'telegram' => 'Telegram',
                'imo'      => 'imo',
                'skype'    => 'Skype',
                'wechat'   => 'WeChat',
                'url'      => 'URL / Best available',
            ],
            'default' => 'vcard',
            'condition' => [ 'design' => 'default' ],
        ]);

        // Custom QR image URL; for minimal_qr this is what shows
        $this->add_control('qr_url', [
            'label' => __('Custom QR image URL', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'input_type' => 'url',
            'placeholder' => 'https://cdn.example.com/qrs/your-qr.png',
        ]);

        // Optional explicit QR text (overrides auto payload when using default design)
        $this->add_control('qr_text', [
            'label' => __('QR Text Override (default design)', 'smart-contact-card'),
            'type'  => Controls_Manager::TEXT,
            'placeholder' => 'https://example.com/landing',
            'condition' => [ 'design' => 'default' ],
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();

        // Build shortcode attrs only for non-empty values
        $attrs = [];
        $keys = [
            'name','title','org','avatar',
            'phone','email','website','address',
            'whatsapp','telegram','imo','skype','wechat',
            'button','design','qr_for','qr_url','qr_text'
        ];

        foreach ($keys as $k) {
            if (!isset($s[$k])) continue;
            $v = is_string($s[$k]) ? trim($s[$k]) : $s[$k];
            if ($v === '' || $v === null) continue;
            $attrs[] = $k . '="' . esc_attr($v) . '"';
        }

        echo do_shortcode('[smartcc_contact ' . implode(' ', $attrs) . ']');
    }
}
