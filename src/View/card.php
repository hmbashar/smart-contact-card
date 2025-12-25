<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Professional, responsive contact card template.
 * Vars:
 * - $name, $title, $org, $avatar
 * - $links (array of ['label','href','type'])
 * - $button, $vcard_url, $download_name
 * - $qr_src (string or empty)
 */
?>
<article class="smartcc-card" itemscope itemtype="https://schema.org/Person" role="region" aria-label="<?php echo esc_attr($name); ?>">
  <header class="smartcc-header">
    <?php if (!empty($avatar)): ?>
      <img class="smartcc-avatar" src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy" decoding="async" />
    <?php else: ?>
      <div class="smartcc-avatar smartcc-avatar--fallback" aria-hidden="true"><?php echo esc_html(strtoupper(mb_substr($name,0,1))); ?></div>
    <?php endif; ?>
    <div class="smartcc-titleblock">
      <h3 class="smartcc-name" itemprop="name"><?php echo esc_html($name); ?></h3>
      <?php if ($title || $org): ?>
        <p class="smartcc-role">
          <?php if ($title): ?><span itemprop="jobTitle"><?php echo esc_html($title); ?></span><?php endif; ?>
          <?php if ($title && $org): ?> · <?php endif; ?>
          <?php if ($org): ?><span itemprop="worksFor"><?php echo esc_html($org); ?></span><?php endif; ?>
        </p>
      <?php endif; ?>
    </div>
  </header>

  <?php if (!empty($links)): ?>
    <ul class="smartcc-links">
      <?php foreach ($links as $smartcc_link): ?>
        <li class="smartcc-link smartcc-link--<?php echo esc_attr($smartcc_link['type']); ?>">
          <a href="<?php echo esc_url($smartcc_link['href']); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr($smartcc_link['label']); ?>">
            <span class="smartcc-chip"><?php echo esc_html($smartcc_link['label']); ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <footer class="smartcc-footer">
    <a class="smartcc-btn" href="<?php echo esc_url($vcard_url); ?>" download="<?php echo esc_attr($download_name); ?>">
      <?php echo esc_html($button); ?>
    </a>
    <?php if (!empty($qr_src)): ?>
      <figure class="smartcc-qrwrap">
        <img class="smartcc-qr" src="<?php echo esc_url($qr_src); ?>" alt="QR code for <?php echo esc_attr($name); ?>" loading="lazy" decoding="async" />
      </figure>
    <?php endif; ?>
  </footer>
</article>
