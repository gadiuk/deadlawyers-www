<div class="totalpoll-settings-item totalpoll-pro-badge-container" ng-controller="NotificationsCtrl as $ctrl">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'OneSignal App ID', 'totalpoll' ); ?> - <a href="https://onesignal.com/" target="_blank"><?php esc_html_e( 'Get one', 'totalpoll' ); ?></a>
        </label>
        <input type="text" class="totalpoll-settings-field-input widefat" ng-model="editor.settings.notifications.push.appId" dir="ltr">
    </div>
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'OneSignal API Key', 'totalpoll' ); ?>
        </label>
        <input type="text" class="totalpoll-settings-field-input widefat" ng-model="editor.settings.notifications.push.apiKey" dir="ltr">
    </div>
    <div class="totalpoll-settings-field">
        <button type="button" class="button button-primary"
                ng-disabled="$ctrl.pushCompleted || !editor.settings.notifications.push.appId || !editor.settings.notifications.push.apiKey"
                ng-click="$ctrl.setupPushService()">
            <i18>Setup push notification</i18>
        </button>
    </div>
    <?php TotalPoll( 'upgrade-to-pro' ); ?>
</div>
<div class="totalpoll-settings-item totalpoll-pro-badge-container">
    <p>
		<?php esc_html_e( 'Send notification when', 'totalpoll' ); ?>
    </p>
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" ng-model="editor.settings.notifications.push.on.newVote" ng-checked="editor.settings.notifications.push.on.newVote">
			<?php esc_html_e( 'New vote has been casted', 'totalpoll' ); ?>
        </label>
    </div>
    <?php TotalPoll( 'upgrade-to-pro' ); ?>
</div>
