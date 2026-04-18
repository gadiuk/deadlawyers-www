<div class="totalpoll-tabs-container has-tabs totalpoll-settings">
    <div class="totalpoll-tabs">
		<?php
		/**
		 * Fires before settings tabs.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/before/admin/editor/settings/tabs', $this );
		?>

		<?php $firstTab = key( $settingsTabs ) ?>
		<?php foreach ( $settingsTabs as $tabId => $tab ): ?>
            <div track="{ event : '<?php echo esc_attr($tabId); ?>', target: 'poll-settings' }" class="totalpoll-tabs-item <?php echo $tabId == $firstTab ? 'active' : ''; ?>" tab-switch="editor>settings>general><?php echo esc_attr( $tabId ); ?>">
                <span class="dashicons dashicons-<?php echo esc_attr( $tab['icon'] ); ?>"></span>
				<?php echo esc_html( $tab['label'] ); ?>
            </div>
		<?php endforeach; ?>

		<?php
		/**
		 * Fires after settings tabs.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/after/admin/editor/settings/tabs', $this );
		?>
    </div>
    <div class="totalpoll-tabs-content">
		<?php foreach ( $settingsTabs as $tabId => $tab ): ?>
            <div class="totalpoll-tab-content <?php echo $tabId == $firstTab ? 'active' : ''; ?>" tab="editor>settings>general><?php echo esc_attr( $tabId ); ?>">
				<?php
				/**
				 * Fires before settings tab content.
				 *
				 * @since 4.0.0
				 */
				do_action( 'totalpoll/actions/before/admin/editor/settings/tabs/content', $tabId );

				$path = empty( $tab['file'] ) ? __DIR__ . "/{$tabId}/index.php" : $tab['file'];
				if ( file_exists( $path ) ):
					include_once $path;
                elseif ( ! empty( $tab['tabs'] ) ):
					include __DIR__ . '/subtab.php';
				endif;

				/**
				 * Fires after settings tab content.
				 *
				 * @since 4.0.0
				 */
				do_action( 'totalpoll/actions/after/admin/editor/settings/tabs/content', $tabId );
				?>
            </div>
		<?php endforeach; ?>
    </div>
</div>

<script type="text/ng-template" id="votes-template-variables">
        <p><strong><?php
        esc_html_e('Template variables', 'totalpoll'); ?></strong></p>
        <p class="totalpoll-feature-tip" ng-non-bindable>
            <?php
            esc_html_e('{{totalVotes}} Number of votes', 'totalpoll'); ?>
        </p>
        <p class="totalpoll-feature-tip" ng-non-bindable>
            <?php
            esc_html_e('{{totalVotesWithLabel}} Number of votes with label', 'totalpoll'); ?>
        </p>
        <?php
        for ($i = 0; $i < $this->poll->getQuestionsCount(); $i++) : $index = $i + 1; ?>
        <p class="totalpoll-feature-tip" ng-non-bindable>
            <?php echo sprintf(esc_html__('{{totalVotesForQuestion%d}} Number of votes for question %1$d', 'totalpoll'), $index); ?>
        </p>
        <p class="totalpoll-feature-tip" ng-non-bindable>
            <?php echo sprintf(esc_html__('{{totalVotesWithLabelForQuestion%d}} Number of votes with label for question %1$d', 'totalpoll'), $index); ?> <br>
        </p>
        <?php endfor; ?>
</script>
