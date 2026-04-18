<?php if ( empty( $minimal ) ): ?>
    <div class="totalpoll-settings-item">
        <div class="totalpoll-settings-field">
            <label>
                <input type="checkbox" name="" ng-model="$ctrl.options.general.showCredits.enabled">
				<?php esc_html_e( 'Spread the love by adding "Powered by TotalPoll" underneath the polls.', 'totalpoll' ); ?>
            </label>
        </div>
    </div>
<?php endif; ?>

<div class="totalpoll-settings-item totalpoll-pro-badge-container">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" ng-model="$ctrl.options.general.structuredData.enabled">
			<?php esc_html_e( 'Structured Data', 'totalpoll' ); ?>
        </label>

        <p class="totalpoll-feature-tip"><?php echo wp_kses(__( 'Improve your appearance in search engine through <a href="https://developers.google.com/search/docs/guides/intro-structured-data" target="_blank">Structured Data</a> implementation..', 'totalpoll' ), ['a' => ['href' => [], 'target' =>[]]]); ?></p>
    </div>
    <?php TotalPoll( 'upgrade-to-pro' ); ?>
</div>
