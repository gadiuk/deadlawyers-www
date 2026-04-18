<script type="text/ng-template" id="dashboard-my-account-component-template">
    <div class="totalpoll-box totalpoll-box-activation">
        <div class="totalpoll-box-section">
            <div class="totalpoll-row">
                <div class="totalpoll-column">
                    <div class="totalpoll-box-content" ng-if="$ctrl.account.status">
                        <img src="<?php echo esc_attr( $this->env['url'] ); ?>assets/dist/images/activation/updates-on.svg" class="totalpoll-box-activation-image">
                        <div class="totalpoll-box-title"><?php esc_html_e( 'Account Linked!', 'totalpoll' ); ?></div>
                        <div class="totalpoll-box-description"><?php esc_html_e( 'Your account has been linked successfully.', 'totalpoll' ); ?></div>
                        <table class="wp-list-table widefat striped">
                            <tr>
                                <td><strong><?php esc_html_e( 'Linked account', 'totalpoll' ); ?></strong></td>
                            </tr>
                            <tr>
                                <td>{{$ctrl.account.email}}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="totalpoll-box-content" ng-if="!$ctrl.account.status">
                        <img src="<?php echo esc_attr( $this->env['url'] ); ?>assets/dist/images/activation/updates-off.svg" class="totalpoll-box-activation-image">
                        <div class="totalpoll-box-title"><?php esc_html_e( 'Your TotalSuite Account', 'totalpoll' ); ?></div>
                        <div class="totalpoll-box-description"><?php esc_html_e( 'Link your account purchases using an access token.', 'totalpoll' ); ?></div>
                        <div class="totalpoll-box-composed-form-error" ng-if="$ctrl.error">{{$ctrl.error}}</div>
                        <form class="totalpoll-box-composed-form" ng-submit="$ctrl.validate()">
                            <input type="text" class="totalpoll-box-composed-form-field" placeholder="<?php esc_attr_e( 'Access Token', 'totalpoll' ) ?>" ng-model="$ctrl.account.access_token">
                            <button type="submit" class="button button-primary button-large totalpoll-box-composed-form-button" ng-if="$ctrl.account.access_token" ng-disabled="!$ctrl.account.access_token || $ctrl.isProcessing()">{{
                                $ctrl.isProcessing() ? '<?php esc_html_e( 'Linking...', 'totalpoll' ); ?>' : '<?php esc_html_e( 'Connect', 'totalpoll' ); ?>' }}
                            </button>
                            <button type="button" class="button button-primary button-large totalpoll-box-composed-form-button" ng-if="!$ctrl.account.access_token" ng-click="$ctrl.openSignInPopup('<?php echo esc_js( $this->env['links.signin-account'] ); ?>')">
								<?php esc_html_e( 'Get Access Token', 'totalpoll' ); ?>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="totalpoll-column">
                    <img src="<?php echo esc_attr( $this->env['url'] ); ?>assets/dist/images/activation/how-to.svg" alt="Get license code">
                </div>
            </div>
        </div>
    </div>
</script>
