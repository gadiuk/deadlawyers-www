<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Above', 'totalpoll' ); ?>
            <span class="totalpoll-feature-details"
                  tooltip="<?php esc_html_e( 'This content will be shown above question and choices.', 'totalpoll' ); ?>">?</span>
        </label>
        <progressive-textarea ng-model="editor.settings.content.vote.above" uid="vote-above"></progressive-textarea>
    </div>
</div>
<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Below', 'totalpoll' ); ?>
            <span class="totalpoll-feature-details"
                  tooltip="<?php esc_html_e( 'This content will be shown below question and choices.', 'totalpoll' ); ?>">?</span>
        </label>
        <progressive-textarea ng-model="editor.settings.content.vote.below" uid="vote-below"></progressive-textarea>
    </div>
</div>
<div class="totalpoll-settings-item" ng-include="'votes-template-variables'"></div>
