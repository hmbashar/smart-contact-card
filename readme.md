# Smart Contact Card

Shareable contact cards with QR codes and vCard via shortcode, Gutenberg block, and Elementor widget.

## Features

- 📇 Professional contact card display
- 📱 QR code generation for easy sharing
- 💾 Downloadable vCard (.vcf) files
- 🎨 Multiple design options (default and minimal)
- 🔧 Easy implementation via shortcode
- 🎛️ Elementor widget support
- 📞 Support for multiple contact methods (Phone, Email, WhatsApp, Telegram, IMO, Skype, WeChat)
- 🌐 Schema.org markup for better SEO
- 📱 Fully responsive design

## How to Use

### Shortcode Usage

Add a contact card anywhere using the shortcode:

```
[smartcc_contact name="John Doe" title="CEO" org="Company Name" phone="+1234567890" email="john@example.com" website="https://example.com"]
```

#### Available Shortcode Parameters

- **Identity:** name, title, org, avatar
- **Contacts:** phone, email, website, address
- **Messaging Apps:** whatsapp, telegram, imo, skype, wechat
- **Display:** button, design (default/minimal_qr)
- **QR Options:** qr_for (vcard/whatsapp/telegram/imo/skype/wechat/url), qr_url, qr_text

### Elementor Widget Usage

1. Edit your page with Elementor.
2. Search for "Smart Contact Card" in the widget panel.
3. Drag the widget into your layout.
4. Fill in the contact details and customize the design using the widget controls.

### Design Options

- `design="default"` (full card with all details)
- `design="minimal_qr"` (minimal card with avatar, name, phone, email, and custom QR)

### QR Code Options

You can customize what the QR code encodes:
- vCard (default) - Complete contact information
- WhatsApp - Direct WhatsApp chat link
- Telegram - Telegram profile link
- IMO - IMO messaging link
- Skype - Skype profile link
- WeChat - WeChat contact link
- URL - Custom URL or website

## Developer Friendly

The plugin uses modern PHP practices with PSR-4 autoloading and is built with extensibility in mind. Developers can easily customize and extend functionality.

## Installation

1. Upload the `smart-contact-card` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the `[smartcc_contact]` shortcode in your posts/pages or use the Elementor widget
4. Customize the contact card with the available parameters

## FAQ

### How do I add a contact card to my page?

Simply use the shortcode:

```
[smartcc_contact
  name="Your Name"
  title="Your Title"
  org="Your Company"
  avatar="https://example.com/avatar.jpg"
  phone="+1234567890"
  email="you@example.com"
  website="https://example.com"
  address="123 Main St, City, State, ZIP"
  whatsapp="+1234567890"
  telegram="@yourusername"
  imo="your_imo_id"
  skype="your_skype_id"
  wechat="your_wechat_id"
  button="Save Contact (.vcf)"
  layout="card"
  qr_for="vcard"
  qr_url=""
  qr_text=""
  design="default"
]
```

### Can I customize the design?

Yes! The plugin offers a default design and a minimal design. Set `design="minimal_qr"` in the shortcode for the minimal layout.

### Does it work with Elementor?

Yes! The plugin includes an Elementor widget that you can drag and drop into your pages.

### Can I customize the QR code?

Yes! You can choose what the QR code encodes using the `qr_for` parameter (vcard, whatsapp, telegram, etc.) or provide your own QR code image with `qr_url`.

### Is the plugin GDPR compliant?

The plugin doesn't store any user data. QR codes are generated using a third-party service (QuickChart.io) but no personal data is sent to external servers except the contact information you explicitly provide for QR code generation.

### How do I use custom QR codes?

Use the `qr_url` parameter to specify your own QR code image URL, or use `qr_text` to specify custom text to be encoded.

## Screenshots

1. Default contact card design with all contact methods
2. Minimal contact card design with custom QR code
3. Elementor widget settings panel
4. Example of a contact card with QR code

## Changelog

### 1.0.0
- Initial release
- Shortcode support with extensive customization options
- Elementor widget integration
- Multiple contact methods support
- QR code generation for various platforms
- vCard download functionality
- Responsive design
- Schema.org markup

## Upgrade Notice

### 1.0.0
Initial release of Smart Contact Card plugin.

## Third-Party Services

This plugin uses [QuickChart.io](https://quickchart.io) for QR code generation. When a QR code is displayed, the contact information is sent to QuickChart.io to generate the QR code image. By using this plugin, you acknowledge:

- [QuickChart.io Terms of Service](https://quickchart.io/documentation/)
- [QuickChart.io Privacy](https://quickchart.io/privacy/)

No data is stored on external servers, and QR codes are generated on-the-fly when pages are viewed.
