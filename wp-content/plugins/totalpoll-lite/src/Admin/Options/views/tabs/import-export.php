<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <div class="button-group">
            <a href="<?php echo esc_attr( admin_url( 'import.php?import=wordpress' ) ); ?>" class="button">
				<?php esc_html_e( 'Import data', 'totalpoll' ); ?>
            </a>
            <a href="<?php echo esc_attr( admin_url( 'export.php?content=poll&download' ) ); ?>" class="button">
				<?php esc_html_e( 'Export data', 'totalpoll' ); ?>
            </a>
            <button type="button" class="button" ng-click="$ctrl.downloadSettings()">
				<?php esc_html_e( 'Export settings', 'totalpoll' ); ?>
            </button>
        </div>
        <p class="totalpoll-feature-tip">
			<?php esc_html_e( 'TotalPoll uses standard WordPress import/export mechanism.', 'totalpoll' ); ?>
        </p>
    </div>
</div>
<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <textarea class="widefat" name="" rows="10" placeholder="<?php esc_attr_e( 'Drag and drop settings file or copy then paste its content here.', 'totalpoll' ); ?>" ng-model="$ctrl.import.content" ng-disabled="$ctrl.isImporting()"></textarea>
    </div>
    <div class="totalpoll-settings-field">
        <button type="button" class="button" ng-click="$ctrl.importSettings()" ng-disabled="!$ctrl.isImportReady()">
            <span ng-if="!$ctrl.isImporting() && !$ctrl.isImported()"><?php esc_html_e( 'Import settings', 'totalpoll' ); ?></span>
            <span ng-if="$ctrl.isImporting()"><?php esc_html_e( 'Importing', 'totalpoll' ); ?></span>
            <span ng-if="$ctrl.isImported()"><?php esc_html_e( 'Imported', 'totalpoll' ); ?></span>
        </button>
    </div>
</div>
