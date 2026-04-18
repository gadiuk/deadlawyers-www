<div class="totalpoll-settings-item totalpoll-pro-badge-container">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Recipient email', 'totalpoll' ); ?>
        </label>
        <input type="text" class="totalpoll-settings-field-input widefat" disabled dir="ltr">
    </div>
    <?php TotalPoll( 'upgrade-to-pro' ); ?>
</div>
<div class="totalpoll-settings-item totalpoll-pro-badge-container">
    <p>
		<?php esc_html_e( 'Send notification when', 'totalpoll' ); ?>
    </p>
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" disabled ng-checked="editor.settings.notifications.email.on.newVote">
			<?php esc_html_e( 'New vote has been casted', 'totalpoll' ); ?>
        </label>
    </div>

    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" disabled ng-checked="editor.settings.notifications.email.on.newChoice">
			<?php esc_html_e( 'New choice has been added', 'totalpoll' ); ?>
        </label>
    </div>
    <?php TotalPoll( 'upgrade-to-pro' ); ?>
</div>
