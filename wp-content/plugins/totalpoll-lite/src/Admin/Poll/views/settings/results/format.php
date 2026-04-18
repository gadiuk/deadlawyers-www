<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Votes format', 'totalpoll' ); ?>
            <span class="totalpoll-feature-details"
                  tooltip="<?php esc_html_e( 'How votes are presented', 'totalpoll' ); ?>">?</span>
        </label>
        <input type="text" class="totalpoll-settings-field-input widefat" ng-model="editor.settings.results.format">
        <p class="totalpoll-feature-tip" ng-non-bindable>
            <bdi>
				<?php esc_html_e( '{{votes}} for votes number.', 'totalpoll' ); ?>
            </bdi>
        </p>
        <p class="totalpoll-feature-tip" ng-non-bindable>
            <bdi>
				<?php esc_html_e( '{{votesPercentage}} for votes percentage.', 'totalpoll' ); ?>
            </bdi>
        </p>
        <p class="totalpoll-feature-tip" ng-non-bindable>
            <bdi>
				<?php esc_html_e( '{{votesWithLabel}} for votes with label.', 'totalpoll' ); ?>
            </bdi>
        </p>
        <p class="totalpoll-feature-tip" ng-non-bindable>
            <bdi>
				<?php esc_html_e( '{{votesTotal}} for total votes.', 'totalpoll' ); ?>
            </bdi>
        </p>
        <p class="totalpoll-feature-tip" ng-non-bindable>
            <bdi>
				<?php esc_html_e( '{{votesTotalWithLabel}} for total votes with label.', 'totalpoll' ); ?>
            </bdi>
        </p>
    </div>
</div>
