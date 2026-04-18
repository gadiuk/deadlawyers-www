<div class="totalpoll-row">
    <div class="totalpoll-column" ng-repeat="(pluginId, plugin) in $ctrl.migration.plugins">
        <div class="totalpoll-box-media">
            <div class="totalpoll-box-media-image">
                <img ng-src="{{plugin.image}}" alt="{{plugin.name}}">
            </div>
            <div class="totalpoll-box-media-body">
                <div style="flex: 1">
                    <div class="totalpoll-box-media-title">{{plugin.name}}</div>
                    <div class="totalpoll-box-media-description" ng-if="!$ctrl.isMigrationProcessing(plugin) && !$ctrl.isMigrationFinished(plugin)">
                        <span ng-if="!$ctrl.isMigrationFinished(plugin) && !$ctrl.hasPollsForMigration(plugin)"><?php echo esc_html_e( 'No polls to migrate.', 'totalpoll' ); ?></span>
                        <span ng-if="$ctrl.isMigrationFinished(plugin)"><?php echo esc_html_e( 'Polls migrated successfully.', 'totalpoll' ); ?></span>
                        <span ng-if="!$ctrl.isMigrationFinished(plugin) && !$ctrl.isMigrationProcessing(plugin) && $ctrl.hasPollsForMigration(plugin)">{{plugin.total - plugin.done}} <?php echo esc_html_e( 'Polls to migrate.', 'totalpoll' ); ?></span>
                    </div>
                    <div class="totalpoll-migration-progress" ng-if="$ctrl.isMigrationFinished(plugin) || $ctrl.isMigrationProcessing(plugin)">
                        <div class="totalpoll-migration-progress-container">
                            <div class="totalpoll-migration-progress-bar" ng-style="{width: $ctrl.getMigrationProgress(plugin)}"></div>
                        </div>
                        <div class="totalpoll-migration-progress-text">{{$ctrl.getMigrationProgress(plugin)}}</div>
                    </div>
                </div>
                <div class="totalpoll-box-media-actions" ng-if="!$ctrl.isMigrationProcessing(plugin) && !$ctrl.isMigrationFinished(plugin)">
                    <button type="button" class="button button-large button-primary"
                            ng-disabled="!$ctrl.hasPollsForMigration(plugin) || $ctrl.isMigrationProcessing(plugin) || $ctrl.isMigrationFinished(plugin)"
                            ng-click="$ctrl.migratePolls(pluginId, plugin)">
                        <span ng-if="!$ctrl.isMigrationFinished(plugin) && !$ctrl.isMigrationProcessing(plugin)"><?php esc_html_e( 'Migrate', 'totalpoll' ); ?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
