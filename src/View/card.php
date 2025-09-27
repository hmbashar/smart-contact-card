<?php
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
      <img class="smartcc-avatar" src="<?php echo $avatar; ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy" decoding="async" />
    <?php else: ?>
      <div class="smartcc-avatar smartcc-avatar--fallback" aria-hidden="true"><?php echo strtoupper(mb_substr($name,0,1)); ?></div>
    <?php endif; ?>
    <div class="smartcc-titleblock">
      <h3 class="smartcc-name" itemprop="name"><?php echo $name; ?></h3>
      <?php if ($title || $org): ?>
        <p class="smartcc-role">
          <?php if ($title): ?><span itemprop="jobTitle"><?php echo $title; ?></span><?php endif; ?>
          <?php if ($title && $org): ?> · <?php endif; ?>
          <?php if ($org): ?><span itemprop="worksFor"><?php echo $org; ?></span><?php endif; ?>
        </p>
      <?php endif; ?>
    </div>
  </header>

  <?php if (!empty($links)): ?>
    <ul class="smartcc-links">
      <?php foreach ($links as $L): ?>
        <li class="smartcc-link smartcc-link--<?php echo esc_attr($L['type']); ?>">
          <a href="<?php echo esc_url($L['href']); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr($L['label']); ?>">
            <span class="smartcc-chip"><?php echo esc_html($L['label']); ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <footer class="smartcc-footer">
    <a class="smartcc-btn" href="<?php echo esc_url($vcard_url); ?>" download="<?php echo esc_attr($download_name); ?>">
      <?php echo $button; ?>
    </a>
    <?php if (!empty($qr_src)): ?>
      <figure class="smartcc-qrwrap">
        <img class="smartcc-qr" src="<?php echo $qr_src; ?>" alt="QR code for <?php echo esc_attr($name); ?>" loading="lazy" decoding="async" />
      </figure>
    <?php endif; ?>
  </footer>
</article>
