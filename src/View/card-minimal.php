<?php
/**
 * Minimal QR design.
 * Shows ONLY: avatar, name, phone, email, and custom QR image (qr_url).
 * Expects: $name, $phone, $email, $avatar, $qr_src
 */
?>
<article class="smartcc-card smartcc-card--minimal" role="region" aria-label="<?php echo esc_attr($name); ?>">
    <div class="smartcc-minimal-grid">
        <div class="smartcc-minimal-left">
            <header class="smartcc-minimal-header">
                <?php if (!empty($avatar)): ?>
                    <img class="smartcc-avatar" src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($name); ?>"
                        loading="lazy" decoding="async" />
                <?php else: ?>
                    <div class="smartcc-avatar smartcc-avatar--fallback" aria-hidden="true">
                        <?php echo esc_html(strtoupper(mb_substr($name, 0, 1))); ?>
                    </div>
                <?php endif; ?>

                <h3 class="smartcc-name"><?php echo esc_html($name); ?></h3>
            </header>

            <ul class="smartcc-minimal-list">
                <?php if (!empty($phone)): ?>
                    <li><a href="<?php echo esc_url('tel:' . $phone); ?>"><?php echo esc_html($phone); ?></a></li>
                <?php endif; ?>
                <?php if (!empty($email)): ?>
                    <li><a href="<?php echo esc_url('mailto:' . $email); ?>"><?php echo esc_html($email); ?></a></li>
                <?php endif; ?>
            </ul>
        </div>

        <?php if (!empty($qr_src)): ?>
            <div class="smartcc-minimal-right">
                <img class="smartcc-qr" src="<?php echo esc_url($qr_src); ?>" alt="QR code for <?php echo esc_attr($name); ?>"
                    loading="lazy" decoding="async" />
            </div>
        <?php endif; ?>
    </div>
</article>