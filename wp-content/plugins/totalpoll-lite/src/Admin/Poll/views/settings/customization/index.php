<div class="totalpoll-tabs-container">
    <div class="totalpoll-tabs-content">
        <!-- Customization -->
        <div class="totalpoll-tab-content active">
            <div class="totalpoll-customization">
                <img src="<?php echo $this->env['url'] . 'assets/dist/images/editor/customization.svg'; ?>" alt="Customization">

                <div class="title"><?php echo wp_kses( __( 'Customization?<br>We have got your back!', 'totalpoll' ), [ 'a' => [ 'href' => [], 'target' => [] ] ] ); ?></div>
                <div class="copy"><?php esc_html_e( 'If you need custom feature just let us know we will be happy to serve you!', 'totalpoll' ); ?></div>

				<?php
				$url = add_query_arg(
					[
						'utm_source'   => 'in-app',
						'utm_medium'   => 'editor-settings-tab',
						'utm_campaign' => 'totalpoll',
					],
					$this->env['links.customization']
				);
				?>
                <a href="<?php echo esc_attr( $url ); ?>" target="_blank" class="button button-primary button-large"><?php esc_html_e( 'Get a quote', 'totalpoll' ); ?></a>
            </div>
        </div>
    </div>
</div>
