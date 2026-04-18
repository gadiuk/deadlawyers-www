<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <p>
			<?php esc_html_e( 'Block based on', 'totalpoll' ); ?>
            <span
                    class="totalpoll-feature-details"
                    tooltip="<?php esc_html_e( 'The methods used to block exceeding the limits of voting.', 'totalpoll' ); ?>">?</span>
        </p>
        <div class="totalpoll-settings-field">
            <label>
                <input type="checkbox" name=""
                       ng-model="editor.settings.vote.frequency.cookies.enabled">
				<?php esc_html_e( 'Cookies', 'totalpoll' ); ?>
            </label>
        </div>
        <div class="totalpoll-settings-field">
            <label> <input type="checkbox" name=""
                           disabled>
				<?php esc_html_e( 'IP', 'totalpoll' ); ?>
                <?php TotalPoll( 'upgrade-to-pro' ); ?>
            </label>
        </div>
        <div class="totalpoll-settings-field">
            <label> <input type="checkbox" name=""
                           disabled>
				<?php esc_html_e( 'Authenticated user', 'totalpoll' ); ?>
                <?php TotalPoll( 'upgrade-to-pro' ); ?>
            </label>
        </div>
    </div>
</div>
<div class="totalpoll-settings-item totalpoll-settings-item-inline">
    <div class="totalpoll-settings-field" ng-if="editor.settings.vote.frequency.cookies.enabled">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Votes per session', 'totalpoll' ); ?>
            <span class="totalpoll-feature-details"
                  tooltip="<?php esc_html_e( 'How many times can the user vote using the same session.', 'totalpoll' ); ?>">?</span>
        </label>
        <input type="number" min="0" step="1" class="totalpoll-settings-field-input widefat"
               ng-model="editor.settings.vote.frequency.perSession"
               ng-disabled="!(editor.settings.vote.frequency.cookies.enabled || editor.settings.vote.frequency.ip.enabled || editor.settings.vote.frequency.user.enabled)">
    </div>

    <div class="totalpoll-settings-field" ng-if="editor.settings.vote.frequency.user.enabled">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Votes per user', 'totalpoll' ); ?>
            <span class="totalpoll-feature-details"
                  tooltip="<?php esc_html_e( 'How many times can the authenticated user vote.', 'totalpoll' ); ?>">?</span>
        </label>
        <input type="number" min="0" step="1" class="totalpoll-settings-field-input widefat"
               ng-model="editor.settings.vote.frequency.perUser"
               ng-disabled="!(editor.settings.vote.frequency.cookies.enabled || editor.settings.vote.frequency.ip.enabled || editor.settings.vote.frequency.user.enabled)">
    </div>

    <div class="totalpoll-settings-field" ng-if="editor.settings.vote.frequency.ip.enabled">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Votes per IP', 'totalpoll' ); ?>
            <span class="totalpoll-feature-details"
                  tooltip="<?php esc_html_e( 'How many times can the user vote using the same IP.', 'totalpoll' ); ?>">?</span>
        </label>
        <input type="number" min="0" step="1" class="totalpoll-settings-field-input widefat"
               ng-model="editor.settings.vote.frequency.perIP">
    </div>
</div>
<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Timeout', 'totalpoll' ); ?>
            <span class="totalpoll-feature-details" tooltip="<?php esc_html_e( 'The time period before the user can vote again.', 'totalpoll' ); ?>">?</span>
        </label>
        <div class="totalpoll-settings-timeout">
            <label class="totalpoll-settings-timeout-value" ng-repeat="(key, value) in editor.presets.timeout">
                <input type="radio" ng-checked="editor.settings.vote.frequency.timeout == key" ng-click="editor.setTimeout(key)" name="frequencyTimeout"/>{{ value }}
            </label>
            <label class="totalpoll-settings-timeout-value">
                <input type="radio" name="frequencyTimeout" ng-checked="editor.isCustomTimeout()" ng-click="!editor.isCustomTimeout() && editor.setTimeout(1)"/><?php echo esc_html_e('Custom (minutes)', 'totalpoll'); ?>
            </label>
        </div>
        <input type="number" min="0" step="1" class="totalpoll-settings-field-input widefat"
               ng-model="editor.settings.vote.frequency.timeout"
               ng-model-options="{ updateOn : 'blur' }"
               ng-show="editor.isCustomTimeout()"
               ng-disabled="!(editor.settings.vote.frequency.cookies.enabled || editor.settings.vote.frequency.ip.enabled || editor.settings.vote.frequency.user.enabled)">
        <p class="totalpoll-feature-tip">
			<?php esc_html_e( 'After this period, users will be able to vote again. To lock the vote permanently, use 0 as a value.', 'totalpoll' ); ?>
        </p>
        <p class="totalpoll-warning" ng-if="editor.settings.vote.frequency.timeout == 0">
			<?php esc_html_e( 'Heads up! The database will be filled with permanent records which may affect the overall performance.', 'totalpoll' ); ?>
        </p>
    </div>
</div>
