<?php
/** Inline layout variant */
?>
<div class="smartcc-inline">
  <div>
    <div class="smartcc-name"><?php echo $name; ?></div>
    <?php if ($title || $org): ?>
      <div class="smartcc-sub"><?php echo $title; ?><?php echo $org ? ' – ' . esc_html($org) : ''; ?></div>
    <?php endif; ?>
    <?php if (!empty($links)): ?>
      <ul class="smartcc-links">
        <?php foreach ($links as $L): ?>
          <li><a target="_blank" rel="noopener" href="<?php echo esc_url($L['href']); ?>"><?php echo esc_html($L['label']); ?></a></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <div class="smartcc-actions">
      <a class="smartcc-btn" href="<?php echo esc_url($dataUrl); ?>"><?php echo $btn; ?></a>
    </div>
  </div>
  <div class="smartcc-qr" aria-label="QR Code"><?php echo $qrSvg; ?></div>
</div>
