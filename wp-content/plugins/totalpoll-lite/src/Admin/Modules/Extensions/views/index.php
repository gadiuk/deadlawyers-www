<div id="totalpoll-modules" class="wrap totalpoll-page" ng-app="modules" ng-controller="ModulesCtrl as modules">
    <h1 class="totalpoll-page-title">
		<?php esc_html_e( 'Extensions', 'totalpoll' ); ?>
        <button type="button" ng-click="modules.toggleInstaller()" class="page-title-action" role="button"><span class="upload"><?php esc_html_e( 'Upload', 'totalpoll' ); ?></span></button>
    </h1>
    <modules-installer ng-if="modules.isInstallerVisible()"></modules-installer>
    <div class="totalpoll-page-tabs">
        <div class="totalpoll-page-tabs-item active" tab-switch="modules>installed">
			<?php esc_html_e( 'Installed', 'totalpoll' ); ?>
        </div>
        <div class="totalpoll-page-tabs-item" tab-switch="modules>store">
			<?php esc_html_e( 'Store', 'totalpoll' ); ?>
        </div>
        <div class="totalpoll-page-tabs-item right" ng-click="modules.refresh()">
            <span class="dashicons dashicons-update"></span>
			<?php esc_html_e( 'Refresh', 'totalpoll' ); ?>
        </div>
    </div>
    <modules-manager type="'extensions'"></modules-manager>

	<?php include __DIR__ . '/../../views/install.php'; ?>
	<?php include __DIR__ . '/../../views/manager.php'; ?>
</div>
