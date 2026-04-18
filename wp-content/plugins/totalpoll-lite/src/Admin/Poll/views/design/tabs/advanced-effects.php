<div class="totalpoll-settings-item">
	<div class="totalpoll-settings-field">
		<label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Transition', 'totalpoll' ); ?>
		</label>

		<p>
			<label>
				<input type="radio" name="" value="none" ng-model="$root.settings.design.effects.transition">
				<?php esc_html_e( 'None', 'totalpoll' ); ?>
			</label>
			&nbsp;&nbsp;
			<label>
				<input type="radio" name="" value="fade" ng-model="$root.settings.design.effects.transition">
				<?php esc_html_e( 'Fade', 'totalpoll' ); ?>
			</label>
			&nbsp;&nbsp;
			<label>
				<input type="radio" name="" value="slide" ng-model="$root.settings.design.effects.transition">
				<?php esc_html_e( 'Slide', 'totalpoll' ); ?>
			</label>
		</p>

	</div>
</div>
<div class="totalpoll-settings-item">
	<div class="totalpoll-settings-field">
		<label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Animation duration', 'totalpoll' ); ?>
			<span class="totalpoll-feature-details" tooltip="<?php esc_html_e( 'Animation and transition duration', 'totalpoll' ); ?>">?</span>
		</label>
		<input type="text" class="totalpoll-settings-field-input widefat" ng-model="$root.settings.design.effects.duration">
	</div>
</div>
