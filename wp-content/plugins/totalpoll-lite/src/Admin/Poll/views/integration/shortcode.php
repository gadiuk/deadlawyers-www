<div class="totalpoll-integration-steps">
    <div class="totalpoll-integration-steps-item">
        <div class="totalpoll-integration-steps-item-number">
            <div class="totalpoll-integration-steps-item-number-circle">1</div>
        </div>
        <div class="totalpoll-integration-steps-item-content">
            <h3 class="totalpoll-h3">
                <?php esc_html_e('Copy shortcode', 'totalpoll'); ?>
            </h3>
            <p>
                <?php esc_html_e('Start by copying the following shortcode:', 'totalpoll'); ?>
            </p>
			<?php $shortcode = esc_attr( sprintf( '[totalpoll id="%d"]', get_the_ID() ) ); ?>
			<?php $resultsShortcode = esc_attr( sprintf( '[totalpoll id="%d" screen="results"]', get_the_ID() ) ); ?>
            <div class="totalpoll-integration-steps-item-copy">
                <input name="" type="text" readonly onfocus="this.setSelectionRange(0, this.value.length)" value="<?php echo $shortcode; ?>">
                <button type="button" class="button button-primary" copy-to-clipboard="<?php echo $shortcode; ?>">
                    <?php esc_html_e('Copy', 'totalpoll'); ?>
                </button>
            </div>
            <div class="totalpoll-integration-steps-item-copy">
                <input name="" type="text" readonly onfocus="this.setSelectionRange(0, this.value.length)" value="<?php echo $resultsShortcode; ?>">
                <button type="button" class="button button-primary" copy-to-clipboard="<?php echo $resultsShortcode; ?>">
                    <?php esc_html_e('Copy', 'totalpoll'); ?>
                </button>
            </div>
        </div>
    </div>
    <div class="totalpoll-integration-steps-item">
        <div class="totalpoll-integration-steps-item-number">
            <div class="totalpoll-integration-steps-item-number-circle">2</div>
        </div>
        <div class="totalpoll-integration-steps-item-content">
            <h3 class="totalpoll-h3">
                <?php esc_html_e('Paste the shortcode', 'totalpoll'); ?>
            </h3>
            <p>
                <?php esc_html_e('Paste the copied shortcode into an area that support shortcodes like pages and posts.', 'totalpoll'); ?>
            </p>
        </div>
    </div>
    <div class="totalpoll-integration-steps-item">
        <div class="totalpoll-integration-steps-item-number">
            <div class="totalpoll-integration-steps-item-number-circle">3</div>
        </div>
        <div class="totalpoll-integration-steps-item-content">
            <h3 class="totalpoll-h3">
                <?php esc_html_e('Preview', 'totalpoll'); ?>
            </h3>
            <p>
                <?php esc_html_e('Open the page which you have pasted the shortcode in and test poll functionality.', 'totalpoll'); ?>
            </p>
        </div>
    </div>
</div>
