<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name=""
                   ng-model="editor.settings.vote.limitations.period.enabled"
                   ng-checked="editor.settings.vote.limitations.period.enabled">
			<?php esc_html_e( 'Time period', 'totalpoll' ); ?>
        </label>
    </div>
</div>
<div class="totalpoll-settings-item-advanced"
     ng-class="{active: editor.settings.vote.limitations.period.enabled}">
    <div class="totalpoll-settings-item totalpoll-settings-item-inline">
        <div class="totalpoll-settings-field">
            <label class="totalpoll-settings-field-label">
				<?php esc_html_e( 'Start date', 'totalpoll' ); ?>
                <span class="totalpoll-feature-details"
                      tooltip="<?php esc_html_e( 'Voting will be closed before reaching this date.', 'totalpoll' ); ?>">?</span>
            </label>
            <input type="text" datetime-picker="<?php echo esc_attr( json_encode( [ 'format' => 'Y-m-d H:i', 'humanFormat' => $dateTimeFormat ] ) ); ?>"
                   class="totalpoll-settings-field-input widefat"
                   ng-model="editor.settings.vote.limitations.period.start">
        </div>

        <div class="totalpoll-settings-field">
            <label class="totalpoll-settings-field-label">
				<?php esc_html_e( 'End date', 'totalpoll' ); ?>
                <span class="totalpoll-feature-details"
                      tooltip="<?php esc_html_e( 'Voting will be closed after reaching this date.', 'totalpoll' ); ?>">?</span>
            </label>
            <input type="text" datetime-picker="<?php echo esc_attr( json_encode( [ 'format' => 'Y-m-d H:i', 'humanFormat' => $dateTimeFormat ] ) ); ?>"
                   class="totalpoll-settings-field-input widefat"
                   ng-model="editor.settings.vote.limitations.period.end">
        </div>
    </div>
</div>
<!-- Membership -->
<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label class="">
            <input type="checkbox" name=""
                   ng-model="editor.settings.vote.limitations.membership.enabled"
                   ng-checked="editor.settings.vote.limitations.membership.enabled"
                   disabled>
			<?php esc_html_e( 'Membership', 'totalpoll' ); ?>
            <?php TotalPoll( 'upgrade-to-pro' ); ?>
        </label>
    </div>
</div>
<div class="totalpoll-settings-item-advanced"
     ng-class="{active: editor.settings.vote.limitations.membership.enabled}">
    <div class="totalpoll-settings-item">
        <div class="totalpoll-settings-field">
            <label class="totalpoll-settings-field-label" for="">
				<?php esc_html_e( 'Required membership roles', 'totalpoll' ); ?>
                <span class="totalpoll-feature-details"
                      tooltip="<?php esc_html_e( 'The membership types that can vote.', 'totalpoll' ); ?>">?</span>
            </label>
            <select id="totalpoll-settings-limitations-membership-type"
                    class="totalpoll-settings-field-input widefat" multiple size="7"
                    ng-model="editor.settings.vote.limitations.membership.roles">
				<?php foreach ( get_editable_roles() as $role => $details ): ?>
                    <option value="<?php echo esc_attr( $role ); ?>" <?php selected( in_array( $role, [] ), true ); ?>><?php echo translate_user_role( $details['name'] ); ?></option>
				<?php endforeach; ?>
            </select>

            <p class="totalpoll-feature-tip"><?php esc_html_e( 'Hold Control/Command for multiple selection.', 'totalpoll' ); ?></p>
        </div>
    </div>
</div>
<!-- Quota -->
<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name=""
                   ng-model="editor.settings.vote.limitations.quota.enabled"
                   ng-checked="editor.settings.vote.limitations.quota.enabled"
                   disabled>
			<?php esc_html_e( 'Quota', 'totalpoll' ); ?>
            <?php TotalPoll( 'upgrade-to-pro' ); ?>
        </label>
    </div>
</div>
<div class="totalpoll-settings-item-advanced"
     ng-class="{active: editor.settings.vote.limitations.quota.enabled}">
    <div class="totalpoll-settings-item">
        <div class="totalpoll-settings-field">
            <label class="totalpoll-settings-field-label" for="">
				<?php esc_html_e( 'Number of votes', 'totalpoll' ); ?>
                <span class="totalpoll-feature-details"
                      tooltip="<?php esc_html_e( 'The quota where the poll will not accept votes after reaching.', 'totalpoll' ); ?>">?</span>
            </label>
            <input type="number" min="0" step="1"
                   class="totalpoll-settings-field-input widefat"
                   ng-model="editor.settings.vote.limitations.quota.value">
        </div>
    </div>
</div>

<!-- Region -->
<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name=""
                   ng-model="editor.settings.vote.limitations.region.enabled"
                   ng-checked="editor.settings.vote.limitations.region.enabled"
                   disabled>
			<?php esc_html_e( 'Region (IP based)', 'totalpoll' ); ?>
            <?php TotalPoll( 'upgrade-to-pro' ); ?>
        </label>
    </div>
</div>
<div class="totalpoll-settings-item-advanced"
     ng-class="{active: editor.settings.vote.limitations.region.enabled}">

    <table class="wp-list-table widefat striped"
           ng-controller="RepeaterCtrl as $ctrl"
           ng-init="$ctrl.items = editor.settings.vote.limitations.region.rules">
        <thead>
        <tr>
            <th class="totalpoll-width-15"><?php esc_html_e( 'Type', 'totalpoll' ); ?></th>
            <th class="widefat"><?php esc_html_e( 'IP', 'totalpoll' ); ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <tr ng-repeat="item in $ctrl.items track by $index">
            <td>
                <select class="totalpoll-settings-field-input widefat" ng-model="item.type">
                    <option value="allow"><?php esc_html_e( 'Allow', 'totalpoll' ); ?></option>
                    <option value="deny"><?php esc_html_e( 'Deny', 'totalpoll' ); ?></option>
                </select>
            </td>
            <td>
                <input type="text" class="totalpoll-settings-field-input widefat" ng-model="item.ip">
            </td>
            <td>
                <div class="button-group">
                    <button type="button" class="button button-icon" ng-click="$ctrl.moveUp($index)"
                            ng-disabled="$index === 0">
                        <span class="dashicons dashicons-arrow-up-alt2"></span>
                    </button>
                    <button type="button" class="button button-icon" ng-click="$ctrl.moveDown($index)"
                            ng-disabled="$index === $ctrl.items.length - 1">
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                    <button type="button" class="button button-danger" ng-click="$ctrl.deleteItem($index)">
						<?php esc_html_e( 'Delete', 'totalpoll' ); ?>
                    </button>
                </div>
            </td>
        </tr>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="3">
                <div class="textright">
                    <button type="button" class="button button-primary" ng-click="$ctrl.addItem({type: 'allow'})">
						<?php esc_html_e( 'Add new rule', 'totalpoll' ); ?>
                    </button>
                </div>
            </td>
        </tr>
        </tfoot>
    </table>
</div>
