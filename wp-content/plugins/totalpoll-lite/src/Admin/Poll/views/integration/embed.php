<div class="totalpoll-integration-steps totalpoll-pro-badge-container">
    <div class="totalpoll-integration-steps-item">
        <div class="totalpoll-integration-steps-item-number">
            <div class="totalpoll-integration-steps-item-number-circle">1</div>
        </div>
        <div class="totalpoll-integration-steps-item-content">
            <h3 class="totalpoll-h3">
				<?php esc_html_e( 'Copy code', 'totalpoll' ); ?>
            </h3>
            <p>
				<?php esc_html_e( 'Start by copying the following HTML code:', 'totalpoll' ); ?>
            </p>
            <div class="totalpoll-integration-steps-item-copy">
				<?php $iframe = esc_attr( sprintf( '<iframe id="%s" src="%s" frameborder="0" allowtransparency="true" width="100%%" height="400"></iframe>', 'totalpoll-iframe-' . get_the_ID(), add_query_arg( [ 'embed' => true ], get_permalink() ) ) ); ?>
				<?php $script = esc_attr( sprintf( "<script>window.addEventListener('message', function (event) {if (event.data.totalpoll && event.data.totalpoll.action === 'resizeHeight') {document.querySelector('#totalpoll-iframe-%1\$d').height = event.data.totalpoll.value;}}, false);document.querySelector('#totalpoll-iframe-%1\$d').contentWindow.postMessage({totalpoll: {action: 'requestHeight'}}, '*');</script>",
					get_the_ID() ) ); ?>
                <input name="" type="text" readonly onfocus="this.setSelectionRange(0, this.value.length)" value="<?php echo $iframe . $script; ?>" disabled>
                <button class="button button-primary button-large" type="button" copy-to-clipboard="<?php echo $iframe . $script; ?>">
					<?php esc_html_e( 'Copy', 'totalpoll' ); ?>
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
				<?php esc_html_e( 'Paste the code', 'totalpoll' ); ?>
            </h3>
            <p>
				<?php esc_html_e( 'Paste the copied code into an HTML page.', 'totalpoll' ); ?>
            </p>
        </div>
    </div>
    <div class="totalpoll-integration-steps-item">
        <div class="totalpoll-integration-steps-item-number">
            <div class="totalpoll-integration-steps-item-number-circle">3</div>
        </div>
        <div class="totalpoll-integration-steps-item-content">
            <h3 class="totalpoll-h3">
				<?php esc_html_e( 'Preview', 'totalpoll' ); ?>
            </h3>
            <p>
				<?php esc_html_e( 'Open the page which you have pasted the code in and test poll functionality.', 'totalpoll' ); ?>
            </p>
        </div>
    </div>
    <?php TotalPoll( 'upgrade-to-pro' ); ?>
</div>
