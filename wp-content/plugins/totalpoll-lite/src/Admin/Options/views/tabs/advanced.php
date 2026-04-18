<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" ng-model="$ctrl.options.advanced.inlineCss">
			<?php esc_html_e( 'Always embed CSS with HTML.', 'totalpoll' ); ?>
        </label>

        <p class="totalpoll-feature-tip"><?php esc_html_e( "This option might be useful when WordPress isn't running on standard filesystem.", 'totalpoll' ); ?></p>
    </div>
</div>
<div class="totalpoll-settings-item totalpoll-pro-badge-container">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" disabled>
			<?php esc_html_e( 'Render full poll when listed in polls archive.', 'totalpoll' ); ?>
        </label>

        <p class="totalpoll-feature-tip"><?php esc_html_e( "This option will render all polls instead of showing titles only.", 'totalpoll' ); ?></p>
    </div>
    <?php TotalPoll( 'upgrade-to-pro' ); ?>
</div>
<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" ng-model="$ctrl.options.advanced.disableArchive">
			<?php esc_html_e( 'Disable polls archive.', 'totalpoll' ); ?>
        </label>
    </div>
</div>
<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" ng-model="$ctrl.options.advanced.uninstallAll">
			<?php esc_html_e( 'Remove all data on uninstall.', 'totalpoll' ); ?>
        </label>

        <p class="totalpoll-warning"><?php esc_html_e( "Heads up! This will remove all TotalPoll data including options, cache files and polls.", 'totalpoll' ); ?></p>
    </div>
</div>

<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" ng-model="$ctrl.options.advanced.disableLog">
			<?php esc_html_e( 'Disable the logs.', 'totalpoll' ); ?>
        </label>

        <p class="totalpoll-warning"><?php esc_html_e( "Heads up! This will break some limitations such as IP and Authenticated User.", 'totalpoll' ); ?></p>
    </div>
</div>

<!-- on delete cascade logs -->
<!--<div class="totalpoll-settings-item">-->
<!--    <div class="totalpoll-settings-field">-->
<!--        <label>-->
<!--            <input type="checkbox" name="" ng-model="$ctrl.options.advanced.deleteCascadeLogs">-->
<!--			--><?php //esc_html_e( 'On delete cascade logs with their entries.', 'totalpoll' ); ?>
<!--        </label>-->
<!---->
<!--        <p class="totalpoll-warning">--><?php //esc_html_e( "Heads up! This will delete your entries with their related logs.", 'totalpoll' ); ?><!--</p>-->
<!--    </div>-->
<!--</div>-->
