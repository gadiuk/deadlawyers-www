<div class="totalpoll-settings-item totalpoll-pro-badge-container">
    <div class="totalpoll-settings-field">
        <label> <input type="checkbox" name="" disabled>
			<?php esc_html_e( 'reCaptcha by Google', 'totalpoll' ); ?>
        </label>
    </div>
    <?php TotalPoll( 'upgrade-to-pro' ); ?>
</div>
<div class="totalpoll-settings-item-advanced" ng-class="{active: $ctrl.options.services.recaptcha.enabled}">
    <div class="totalpoll-settings-item">
        <div class="totalpoll-settings-field">
            <label class="totalpoll-settings-field-label">
				<?php esc_html_e( 'Site key', 'totalpoll' ); ?>
            </label>
            <input type="text" class="totalpoll-settings-field-input widefat" ng-model="$ctrl.options.services.recaptcha.key">
        </div>
    </div>
    <div class="totalpoll-settings-item">
        <div class="totalpoll-settings-field">
            <label class="totalpoll-settings-field-label">
				<?php esc_html_e( 'Site secret', 'totalpoll' ); ?>
            </label>
            <input type="text" class="totalpoll-settings-field-input widefat" ng-model="$ctrl.options.services.recaptcha.secret">
        </div>
    </div>
    <div class="totalpoll-settings-item">
        <div class="totalpoll-settings-field">
            <label> <input type="checkbox" name="" ng-model="$ctrl.options.services.recaptcha.invisible">
				<?php esc_html_e( 'Enable invisible captcha.', 'totalpoll' ); ?>
            </label>
        </div>
    </div>
</div>
