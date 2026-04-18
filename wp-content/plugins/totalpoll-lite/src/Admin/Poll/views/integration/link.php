<div class="totalpoll-integration-steps">
	<div class="totalpoll-integration-steps-item">
		<div class="totalpoll-integration-steps-item-number">
			<div class="totalpoll-integration-steps-item-number-circle">1</div>
		</div>
		<div class="totalpoll-integration-steps-item-content">
			<h3 class="totalpoll-h3">
				<?php esc_html_e('Copy the link', 'totalpoll'); ?>
			</h3>
			<p>
				<?php esc_html_e('Start by copying the following link:', 'totalpoll'); ?>
			</p>
			<div class="totalpoll-integration-steps-item-copy">
				<?php $permalink = esc_attr( get_permalink() ); ?>
				<input name="" type="text" readonly onfocus="this.setSelectionRange(0, this.value.length)" value="<?php echo $permalink; ?>">
				<button type="button" class="button button-primary button-large" copy-to-clipboard="<?php echo $permalink; ?>">
					<?php esc_html_e('Copy', 'totalpoll'); ?>
				</button>
			</div>
		</div>
	</div>
	<div class="totalpoll-integration-steps-item">
		<div class="totalpoll-integration-steps-item-number">
			<div class="totalpoll-integration-steps-item-number-circle">2</div>
		</div>
		<div class="totalpoll-integration-steps-item-content">
			<h3 class="totalpoll-h3">
				<?php esc_html_e('Paste the link', 'totalpoll'); ?>
			</h3>
			<p>
				<?php esc_html_e('Paste the copied link anywhere like pages and posts.', 'totalpoll'); ?>
			</p>
		</div>
	</div>
	<div class="totalpoll-integration-steps-item">
		<div class="totalpoll-integration-steps-item-number">
			<div class="totalpoll-integration-steps-item-number-circle">3</div>
		</div>
		<div class="totalpoll-integration-steps-item-content">
			<h3 class="totalpoll-h3">
				<?php esc_html_e('Preview', 'totalpoll'); ?>
			</h3>
			<p>
				<?php esc_html_e('Open the page which you have pasted the link in and test poll functionality.', 'totalpoll'); ?>
			</p>
		</div>
	</div>
</div>
