<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Above', 'totalpoll' ); ?>
            <span class="totalpoll-feature-details"
                  tooltip="<?php esc_html_e( 'This content will be shown above results.', 'totalpoll' ); ?>">?</span>
        </label>
        <progressive-textarea ng-model="editor.settings.content.results.above" uid="results-above"></progressive-textarea>
    </div>
</div>
<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Below', 'totalpoll' ); ?>
            <span class="totalpoll-feature-details"
                  tooltip="<?php esc_html_e( 'This content will be shown below results.', 'totalpoll' ); ?>">?</span>
        </label>
        <progressive-textarea ng-model="editor.settings.content.results.below" uid="results-below"></progressive-textarea>
    </div>
</div>

<div class="totalpoll-settings-item" ng-include="'votes-template-variables'"></div>
