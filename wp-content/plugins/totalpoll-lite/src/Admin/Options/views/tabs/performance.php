<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" ng-model="$ctrl.options.performance.async.enabled">
			<?php esc_html_e( 'Asynchronous loading', 'totalpoll' ); ?>
        </label>

        <p class="totalpoll-feature-tip"><?php esc_html_e( 'This can be useful when you would like to bypass cache mechanisms and plugins.', 'totalpoll' ); ?></p>
    </div>
</div>
<div class="totalpoll-settings-item totalpoll-pro-badge-container">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" ng-model="$ctrl.options.performance.fullChecks.enabled">
			<?php esc_html_e( 'Full checks on page load.', 'totalpoll' ); ?>
        </label>

        <p class="totalpoll-feature-tip"><?php esc_html_e( 'This may put high load on your server because TotalPoll will hit the database frequently.', 'totalpoll' ); ?></p>
    </div>
    <?php TotalPoll( 'upgrade-to-pro' ); ?>
</div>
<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <button type="button" class="button" ng-click="$ctrl.purge('cache')" ng-disabled="$ctrl.isPurging('cache') || $ctrl.isPurged('cache')">
            <span ng-if="$ctrl.isPurgeReady('cache')"><?php esc_html_e( 'Clear cache', 'totalpoll' ); ?></span>
            <span ng-if="$ctrl.isPurging('cache')"><?php esc_html_e( 'Clearing', 'totalpoll' ); ?></span>
            <span ng-if="$ctrl.isPurged('cache')"><?php esc_html_e( 'Cleared', 'totalpoll' ); ?></span>
        </button>
    </div>
</div>
