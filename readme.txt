=== Smart Contact Card ===
Contributors: hmbashar
Tags: contact card, vcard, qr code, business card, elementor
Requires at least: 5.8
Tested up to: 7.0
Stable tag: 1.1.3
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shareable contact cards with QR codes and vCard via shortcode, Gutenberg block, and Elementor widget.


== Description ==

Smart Contact Card allows you to create professional, shareable contact cards on your WordPress site. Display contact information beautifully with QR codes and downloadable vCard files.

**Features:**

* 📇 Professional contact card display
* 📱 QR code generation for easy sharing
* 💾 Downloadable vCard (.vcf) files
* 🎨 Multiple design options (default and minimal)
* 🔧 Easy implementation via shortcode
* 🎛️ Elementor widget support
* 📞 Support for multiple contact methods (Phone, Email, WhatsApp, Telegram, IMO, Skype, WeChat)
* 🌐 Schema.org markup for better SEO
* 📱 Fully responsive design

== How to Use ==

**Shortcode Usage:**

Add a contact card anywhere using the shortcode:

`[smartcc_contact name="John Doe" title="CEO" org="Company Name" phone="+1234567890" email="john@example.com" website="https://example.com"]`

**Available Shortcode Parameters:**

* **Identity:** name, title, org, avatar
* **Contacts:** phone, email, website, address
* **Messaging Apps:** whatsapp, telegram, imo, skype, wechat
* **Display:** button, design (default/minimal_qr)
* **QR Options:** qr_for (vcard/mecard/whatsapp/telegram/imo/skype/wechat/url), qr_url, qr_text

**Elementor Widget Usage:**

1. Edit your page with Elementor.
2. Search for "Smart Contact Card" in the widget panel.
3. Drag the widget into your layout.
4. Fill in the contact details and customize the design using the widget controls.

**Design Options:**

- `design="default"` (full card with all details)
- `design="minimal_qr"` (minimal card with avatar, name, phone, email, and custom QR)

**QR Code Options:**

You can customize what the QR code encodes:
* vCard (default) - Full contact QR with name, title, company, phone, email, website (best for iPhone)
* MeCard - Compact legacy payload for older Android scanners
* WhatsApp - Direct WhatsApp chat link
* Telegram - Telegram profile link
* IMO - IMO messaging link
* Skype - Skype profile link
* WeChat - WeChat contact link
* URL - Custom URL or website

**Developer Friendly:**

The plugin uses modern PHP practices with PSR-4 autoloading and is built with extensibility in mind. Developers can easily customize and extend functionality.

== Installation ==

1. Upload the `smart-contact-card` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the `[smartcc_contact]` shortcode in your posts/pages or use the Elementor widget
4. Customize the contact card with the available parameters

== Frequently Asked Questions ==

= How do I add a contact card to my page? =

Simply use the shortcode: `[smartcc_contact
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
]`

= Can I customize the design? =

Yes! The plugin offers a default design and a minimal design. Set `design="minimal_qr"` in the shortcode for the minimal layout.

= Does it work with Elementor? =

Yes! The plugin includes an Elementor widget that you can drag and drop into your pages.

= Can I customize the QR code? =

Yes! You can choose what the QR code encodes using the `qr_for` parameter (vcard, whatsapp, telegram, etc.) or provide your own QR code image with `qr_url`.

= Is the plugin GDPR compliant? =

The plugin doesn't store any user data. QR codes are generated using a third-party service (QuickChart.io) but no personal data is sent to external servers except the contact information you explicitly provide for QR code generation.

= How do I use custom QR codes? =

Use the `qr_url` parameter to specify your own QR code image URL, or use `qr_text` to specify custom text to be encoded.

== Screenshots ==

1. Default contact card design with all contact methods
2. Minimal contact card design with custom QR code
3. Elementor widget settings panel
4. Example of a contact card with QR code

== Changelog ==

= 1.1.3 =
* Fixed: iPhone QR scans missing title and company by using a real vCard payload by default (MeCard kept as qr_for="mecard").
* Fixed: iPhone Save Contact often omitting the avatar by embedding a resized JPEG PHOTO in the downloaded .vcf when the image can be fetched.
* Improved: Elementor QR payload options now distinguish vCard vs MeCard.

= 1.1.2 =
* Fixed: QR code contact fields showing empty on Android scanners by restoring the compact MECard payload for default contact QR codes.
* Fixed: iPhone scanner URL issue caused by escaped or pasted smart-quote URL values.
* Improved: Website, avatar, and custom QR URL cleanup for shortcode and vCard download values.
* Improved: vCard downloads now include the avatar URL as a PHOTO URI when provided.

= 1.1.0 =
* Fixed: Shortcode not rendering in Gutenberg shortcode block and classic text editor
* Fixed: Shortcode registration timing issue causing silent failures
* Fixed: Line breaks in shortcodes preventing proper rendering
* Fixed: Missing vendor directory causing plugin to fail silently on server
* Improved: Better error handling and messaging when dependencies are missing
* Improved: Autoloader check to prevent initialization without required files

= 1.0.0 =
* Initial release
* Shortcode support with extensive customization options
* Elementor widget integration
* Multiple contact methods support
* QR code generation for various platforms
* vCard download functionality
* Responsive design
* Schema.org markup

== Upgrade Notice ==

= 1.1.3 =
Fixes remaining iPhone contact QR issues: title/company in scanned QR, and avatar in downloaded vCards.

= 1.1.2 =
Fixes QR contact scanning compatibility on Android and iPhone, including malformed website URLs from pasted smart quotes.

= 1.1.0 =
Bug fix release: Fixes shortcode rendering issues in Gutenberg and classic editor. Improves server deployment reliability.

= 1.0.0 =
Initial release of Smart Contact Card plugin.

== Third-Party Services ==

This plugin uses QuickChart.io (https://quickchart.io) for QR code generation. When a QR code is displayed, the contact information is sent to QuickChart.io to generate the QR code image. By using this plugin, you acknowledge:

* QuickChart.io Terms of Service: https://quickchart.io/documentation/
* QuickChart.io Privacy: https://quickchart.io/privacy/

No data is stored on external servers, and QR codes are generated on-the-fly when pages are viewed.
