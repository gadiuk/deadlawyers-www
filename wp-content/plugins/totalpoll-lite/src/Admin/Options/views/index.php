<?php $minimal = apply_filters( 'totalpoll/filters/admin/dashboard/minimal', false ); ?>
<div id="totalpoll-options" class="wrap totalpoll-page" ng-app="options" ng-controller="OptionsCtrl as $ctrl">
    <div class="totalpoll-row" style="align-items: center">
        <div class="totalpoll-column">
            <h1 class="totalpoll-page-title"><?php esc_html_e( 'Options', 'totalpoll' ); ?></h1>
        </div>
        <div class="totalpoll-column textright">
            <button type="button" class="button button-primary" ng-click="$ctrl.saveOptions()" ng-disabled="$ctrl.isProcessing() || $ctrl.isProcessed()">
                <span ng-if="$ctrl.isProcessing() && !$ctrl.isProcessed()"><?php esc_html_e('Saving ...', 'totalpoll'); ?></span>
                <span ng-if="$ctrl.isProcessed()"><?php esc_html_e('Saved', 'totalpoll'); ?></span>
                <span ng-if="!$ctrl.isProcessing() && !$ctrl.isProcessed()"><?php esc_html_e('Save changes', 'totalpoll'); ?></span>
            </button>
        </div>
    </div>
    <div class="totalpoll-tabs-container has-tabs totalpoll-settings totalpoll-options" ng-class="{ 'totalpoll-processing' : $ctrl.isProcessing(), 'totalpoll-successful' : $ctrl.isProcessed() }">
        <div class="totalpoll-tabs">
			<?php $firstTab = key( $tabs ) ?>
			<?php foreach ( $tabs as $tabId => $tab ): ?>
                <div track="{ event : '<?php echo esc_html($tabId) ?>', target: 'options' }" class="totalpoll-tabs-item <?php echo $tabId == $firstTab ? 'active' : ''; ?>" tab-switch="options><?php echo esc_attr( $tabId ); ?>">
                    <span class="dashicons dashicons-<?php echo esc_attr( $tab['icon'] ); ?>"></span>
					<?php echo esc_html( $tab['label'] ); ?>
                </div>
			<?php endforeach; ?>
        </div>
        <div class="totalpoll-tabs-content">
			<?php foreach ( $tabs as $tabId => $tab ): ?>
                <div class="totalpoll-tab-content <?php echo $tabId == $firstTab ? 'active' : ''; ?>" tab="options><?php echo esc_attr( $tabId ); ?>">
                    <div class="totalpoll-tabs-container">
                        <div class="totalpoll-tab-content active">
							<?php
							/**
							 * Fires before options tab content.
							 *
							 * @since 4.0.0
							 */
							do_action( 'totalpoll/actions/before/admin/options/tabs/content', $tabId );

							$path = empty( $tab['file'] ) ? __DIR__ . "/tabs/{$tabId}.php" : $tab['file'];
							if ( file_exists( $path ) ):
								include_once $path;
							endif;

							/**
							 * Fires after options tab content.
							 *
							 * @since 4.0.0
							 */
							do_action( 'totalpoll/actions/after/admin/options/tabs/content', $tabId );
							?>
                        </div>
                    </div>
                </div>
			<?php endforeach; ?>
        </div>
    </div>
    <p class="alignright">
        <button type="button" class="button button-primary" ng-click="$ctrl.saveOptions()" ng-disabled="$ctrl.isProcessing() || $ctrl.isProcessed()">
            <span ng-if="$ctrl.isProcessing() && !$ctrl.isProcessed()"><?php esc_html_e('Saving ...', 'totalpoll'); ?></span>
            <span ng-if="$ctrl.isProcessed()"><?php esc_html_e('Saved', 'totalpoll'); ?></span>
            <span ng-if="!$ctrl.isProcessing() && !$ctrl.isProcessed()"><?php esc_html_e('Save changes', 'totalpoll'); ?></span>
        </button>
    </p>
</div>
