<div class="totalpoll-integration">
    <div class="totalpoll-integration-tabs">
		<?php $firstIntegrationTab = key( $integrationTabs ) ?>
		<?php foreach ( $integrationTabs as $integrationTabId => $integrationTab ): ?>
            <div track="{ event : '<?php echo esc_html( $integrationTabId ); ?>', target: 'poll-integration' }" class="totalpoll-integration-tabs-item <?php echo $integrationTabId == $firstIntegrationTab ? 'active' : ''; ?>" tab-switch="editor>integration>methods><?php echo esc_attr( $integrationTabId ); ?>">
                <div class="totalpoll-integration-tabs-item-icon">
                    <span class="dashicons dashicons-<?php echo esc_attr( $integrationTab['icon'] ); ?>"></span>
                </div>
                <div class="totalpoll-integration-tabs-item-title">
                    <h3 class="totalpoll-h3">
						<?php echo esc_html( $integrationTab['label'] ); ?>
                    </h3>
                    <p>
						<?php echo esc_html( $integrationTab['description'] ); ?>
                    </p>
                </div>
            </div>
		<?php endforeach; ?>
    </div>
	<?php foreach ( $integrationTabs as $integrationTabId => $integrationTab ): ?>
        <div class="totalpoll-integration-tabs-content <?php echo $integrationTabId == $firstIntegrationTab ? 'active' : ''; ?>" tab="editor>integration>methods><?php echo esc_attr( $integrationTabId ); ?>">
			<?php
			/**
			 * Fires before integration tab content.
			 *
			 * @since 4.0.0
			 */
			do_action( "totalpoll/actions/before/admin/editor/integration/tabs/content/{$integrationTabId}" );

			$path = empty( $integrationTab['file'] ) ? __DIR__ . "/{$integrationTabId}.php" : $integrationTab['file'];
			if ( file_exists( $path ) ):
				include_once $path;
			endif;

			/**
			 * Fires after integration tab content.
			 *
			 * @since 4.0.0
			 */
			do_action( "totalpoll/actions/after/admin/editor/integration/tabs/content/{$integrationTabId}" );
			?>
        </div>
	<?php endforeach; ?>
</div>