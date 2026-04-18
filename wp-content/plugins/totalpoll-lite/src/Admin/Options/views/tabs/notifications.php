<div class="totalpoll-settings-item totalpoll-pro-badge-container">
    <p class="totalpoll-feature-tip" ng-non-bindable><?php esc_html_e( 'Poll title: {{title}}', 'totalpoll' ); ?></p>
    <p class="totalpoll-feature-tip" ng-non-bindable><?php esc_html_e( 'Choices: {{choices}}', 'totalpoll' ); ?></p>
    <p class="totalpoll-feature-tip" ng-non-bindable><?php esc_html_e( 'User IP: {{ip}}', 'totalpoll' ); ?></p>
    <p class="totalpoll-feature-tip" ng-non-bindable><?php esc_html_e( 'User browser: {{browser}}', 'totalpoll' ); ?></p>
    <p class="totalpoll-feature-tip" ng-non-bindable><?php esc_html_e( 'Vote date: {{date}}', 'totalpoll' ); ?></p>

    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Title', 'totalpoll' ); ?>
        </label>
        <input type="text" class="totalpoll-settings-field-input widefat" disabled>
    </div>
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Plain text body', 'totalpoll' ); ?>
        </label>
        <textarea type="text" class="totalpoll-settings-field-input widefat" disabled></textarea>
    </div>
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'HTML template', 'totalpoll' ); ?>
        </label>
        <textarea type="text" class="totalpoll-settings-field-input widefat" disabled></textarea>
    </div>
    <?php TotalPoll( 'upgrade-to-pro' ); ?>
</div>
