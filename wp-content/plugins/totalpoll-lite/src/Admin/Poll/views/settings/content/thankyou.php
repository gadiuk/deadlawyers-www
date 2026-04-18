<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Content', 'totalpoll' ); ?>
        </label>
        <progressive-textarea ng-model="editor.settings.content.thankyou.content" uid="thankyou"></progressive-textarea>
    </div>
</div>

<div class="totalpoll-settings-item" ng-include="'votes-template-variables'"></div>
