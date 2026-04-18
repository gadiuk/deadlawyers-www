<customizer-control
        type="checkbox"
        label="<?php esc_html_e( 'AJAX', 'totalpoll' ); ?>"
        ng-model="$root.settings.design.behaviours.ajax"
        help="<?php esc_html_e( 'Load poll in-place without reloading the whole page.', 'totalpoll' ); ?>"></customizer-control>
<customizer-control
        type="checkbox"
        label="<?php esc_html_e( 'Scroll up after vote submission', 'totalpoll' ); ?>"
        ng-model="$root.settings.design.behaviours.scrollUp"
        help="<?php esc_html_e( 'Scroll up to poll viewport after submitting a vote.', 'totalpoll' ); ?>"></customizer-control>

<customizer-control
        type="checkbox"
        label="<?php esc_html_e( 'Display fields before questions', 'totalpoll' ); ?>"
        ng-model="$root.settings.design.behaviours.fieldsFirst"></customizer-control>
<customizer-control
        type="checkbox"
        label="<?php esc_html_e( 'Disable modal', 'totalpoll' ); ?>"
        ng-model="$root.settings.design.behaviours.disableModal"></customizer-control>


<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" disabled>
			<?php esc_html_e( 'One-click vote', 'totalpoll' ); ?>
			<?php TotalPoll( 'upgrade-to-pro' ); ?>
            <span class="totalpoll-feature-details" tooltip="<?php esc_html_e( 'The user will be able to vote by clicking on the choice directly.', 'totalpoll' ); ?>">?</span>
        </label>
    </div>

    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" disabled>
			<?php esc_html_e( 'Question by question', 'totalpoll' ); ?>
			<?php TotalPoll( 'upgrade-to-pro' ); ?>
            <span class="totalpoll-feature-details" tooltip="<?php esc_html_e( 'Display questions one by one.', 'totalpoll' ); ?>">?</span>
        </label>
    </div>
</div>

