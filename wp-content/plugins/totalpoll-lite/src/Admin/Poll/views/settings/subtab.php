<div class="totalpoll-tabs-container">
    <div class="totalpoll-tabs">
		<?php $firstSubtab = key( $tab['tabs'] ) ?>
		<?php foreach ( $tab['tabs'] as $subTabId => $subtab ): ?>
            <div class="totalpoll-tabs-item <?php echo $subTabId == $firstSubtab ? 'active' : ''; ?>" tab-switch="editor>settings>general><?php echo esc_attr( $tabId ); ?>><?php echo esc_attr( $subTabId ); ?>">
                <span class="dashicons dashicons-<?php echo esc_attr( $subtab['icon'] ); ?>"></span>
				<?php echo esc_html( $subtab['label'] ); ?>
            </div>
		<?php endforeach; ?>
    </div>
    <div class="totalpoll-tabs-content">
		<?php foreach ( $tab['tabs'] as $subTabId => $subtab ): ?>
            <div class="totalpoll-tab-content <?php echo $subTabId == $firstSubtab ? 'active' : ''; ?>" tab="editor>settings>general><?php echo esc_attr( $tabId ); ?>><?php echo esc_attr( $subTabId ); ?>">
				<?php
				/**
				 * Fires before settings sub tab content.
				 *
				 * @since 4.0.0
				 */
				do_action( 'totalpoll/actions/before/admin/editor/settings/tabs/content', "{$tabId}-{$subTabId}" );

				$path = empty( $subtab['file'] ) ? __DIR__ . "/{$tabId}/{$subTabId}.php" : $subtab['file'];
				if ( file_exists( $path ) ):
					include_once $path;
				endif;

				/**
				 * Fires after settings sub tab content.
				 *
				 * @since 4.0.0
				 */
				do_action( 'totalpoll/actions/after/admin/editor/settings/tabs/content', "{$tabId}-{$subTabId}" );
				?>
            </div>
		<?php endforeach; ?>
    </div>
</div>