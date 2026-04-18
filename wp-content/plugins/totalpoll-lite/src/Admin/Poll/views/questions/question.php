<script type="text/ng-template" id="question-component-template">
    <h3 class="totalpoll-h3">
		<?php esc_html_e( 'Content', 'totalpoll' ); ?>
    </h3>
	<?php
	/**
	 * Fires before question content.
	 *
	 * @since 4.0.0
	 */
	do_action( 'totalpoll/actions/before/admin/questions/content' );
	?>
    <progressive-textarea ng-model="$ctrl.item.content" uid="{{ $ctrl.item.uid }}" rows="2"></progressive-textarea>
	<?php
	/**
	 * Fires after question content.
	 *
	 * @since 4.0.0
	 */
	do_action( 'totalpoll/actions/after/admin/questions/content' );
	?>

    <h3 class="totalpoll-h3">
		<?php esc_html_e( 'Settings', 'totalpoll' ); ?>
    </h3>
	<?php
	/**
	 * Fires before question settings.
	 *
	 * @since 4.0.0
	 */
	do_action( 'totalpoll/actions/before/admin/questions/settings' );
	?>
    <div class="totalpoll-settings-item totalpoll-settings-item-inline">
        <div class="totalpoll-settings-field">
            <label class="totalpoll-settings-field-label">
                <span bindings="{minimum: '$ctrl.item.settings.selection.minimum'}"><?php esc_html_e( 'User must vote at least for {{minimum}} choice(s).', 'totalpoll' ); ?></span>
            </label>
            <input type="number"
                   min="0"
                   step="1"
                   class="totalpoll-settings-field-input widefat"
                   ng-attr-max="{{ $ctrl.item.choices.length || 1 }}"
                   ng-model="$ctrl.item.settings.selection.minimum"
                   ng-model-options="{ allowInvalid: true, debounce: 1 }"
                   ng-change="$ctrl.checkSettings()">
        </div>
        <div class="totalpoll-settings-field">
            <label class="totalpoll-settings-field-label">
                <span bindings="{maximum: '$ctrl.item.settings.selection.maximum'}"><?php esc_html_e( 'User can vote for up to {{maximum}} choice(s).', 'totalpoll' ); ?></span>
            </label>
            <input type="number" min="1" step="1"
                   class="totalpoll-settings-field-input widefat"
                   ng-attr-max="{{ $ctrl.item.choices.length || 1 }}"
                   ng-model="$ctrl.item.settings.selection.maximum"
                   ng-model-options="{ allowInvalid: true, debounce: 1 }"
                   ng-change="$ctrl.checkSettings()">
        </div>
        <div class="totalpoll-settings-field totalpoll-pro-badge-container">
            <label class="totalpoll-settings-field-label">
				<?php esc_html_e( 'Allow custom choice (other field).', 'totalpoll' ); ?>
            </label>
            <select name="" class="totalpoll-settings-field-input widefat" disabled>
                <option value=""><?php esc_html_e( 'No', 'totalpoll' ); ?></option>
                <option value="visible"><?php esc_html_e( 'Yes', 'totalpoll' ); ?></option>
                <option value="hidden"><?php esc_html_e( 'Yes, but hide it until reviewed below.', 'totalpoll' ); ?></option>
            </select>
            <?php TotalPoll( 'upgrade-to-pro' ); ?>
        </div>
    </div>
	<?php
	/**
	 * Fires after question settings.
	 *
	 * @since 4.0.0
	 */
	do_action( 'totalpoll/actions/after/admin/questions/settings' );
	?>

    <h3 class="totalpoll-h3">
		<?php esc_html_e( 'Choices', 'totalpoll' ); ?>
    </h3>
	<?php
	/**
	 * Fires before question choices.
	 *
	 * @since 4.0.0
	 */
	do_action( 'totalpoll/actions/before/admin/questions/choices' );
	?>
    <choices items="$ctrl.item.choices" class="totalpoll-droppable-parent"></choices>
	<?php
	/**
	 * Fires after question choices.
	 *
	 * @since 4.0.0
	 */
	do_action( 'totalpoll/actions/after/admin/questions/choices' );
	?>

</script>
