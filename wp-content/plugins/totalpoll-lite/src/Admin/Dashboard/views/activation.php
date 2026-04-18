<script type="text/ng-template" id="dashboard-activation-component-template">
    <div class="totalpoll-box totalpoll-box-activation">
        <div class="totalpoll-box-section">
            <div class="totalpoll-row">
                <div class="totalpoll-column">
                    
                    
                    <div class="totalpoll-box-content">
                        <img src="<?php echo esc_attr( $this->env['url'] ); ?>assets/dist/images/activation/updates-off.svg"
                             class="totalpoll-box-activation-image">
                        <div class="totalpoll-box-title">
							<?php esc_html_e( 'Pro Version Required', 'totalpoll' ); ?>
                        </div>
                        <div class="totalpoll-box-description">
							<?php esc_html_e( 'Kindly install the Pro version before activating your license. Get ready to enjoy advanced features!',
							                  'totalpoll' ); ?>
                        </div>

                        <a href="https://totalsuite.net/account/?utm_source=in-app&utm_medium=activation-tab&utm_campaign=totalpoll"
                           class="button button-primary button-large totalpoll-box-composed-form-button w-100">
							<?php esc_html_e( 'Download from TotalSuite.net', 'totalpoll' ); ?>
                        </a>
                    </div>
                    
                </div>
                <div class="totalpoll-column">
                    <img src="<?php echo esc_attr( $this->env['url'] ); ?>assets/dist/images/activation/how-to.svg"
                         alt="Get license code">
                </div>
            </div>
        </div>
    </div>
</script>
