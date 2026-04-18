<div id="totalpoll-modules" class="wrap totalpoll-page" ng-app="modules">
    <h1 class="totalpoll-page-title"><?php esc_html_e( 'Modules', 'totalpoll' ); ?></h1>
    <div class="totalpoll-page-tabs">
        <div class="totalpoll-page-tabs-item active" tab-switch="modules>install">
			<?php esc_html_e( 'Install', 'totalpoll' ); ?>
        </div>
        <div class="totalpoll-page-tabs-item" tab-switch="modules>templates">
			<?php esc_html_e( 'Templates', 'totalpoll' ); ?>
        </div>
        <div class="totalpoll-page-tabs-item" tab-switch="modules>extensions">
			<?php esc_html_e( 'Extensions', 'totalpoll' ); ?>
        </div>
    </div>
    <modules-manager ng-show="isCurrentTab('modules>templates') || isCurrentTab('modules>extensions')" type="isCurrentTab('modules>templates') ? 'templates' : 'extensions'"></modules-manager>
    <modules-installer tab="modules>install" class="active"></modules-installer>

	<?php include __DIR__ . '/install.php'; ?>
	<?php include __DIR__ . '/manager.php'; ?>
</div>
