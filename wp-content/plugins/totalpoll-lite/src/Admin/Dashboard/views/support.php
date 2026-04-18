<script type="text/ng-template" id="dashboard-support-component-template">
    <div class="totalpoll-row">
        <div class="totalpoll-column">
            <div class="totalpoll-box totalpoll-box-support-channel">
                <div class="totalpoll-box-section">
                    <img class="totalpoll-box-support-channel-image" src="<?php echo esc_attr( $this->env['url'] ); ?>assets/dist/images/support/customer-support.svg">
                    <div class="totalpoll-box-title"><?php esc_html_e( 'Customer Support', 'totalpoll' ); ?></div>
                    <div class="totalpoll-box-description"><?php esc_html_e( 'Our support team is here to help you.', 'totalpoll' ); ?></div>
                    <a href="<?php echo esc_attr( $this->env['links.support'] ); ?>" target="_blank" class="button button-primary button-large"><?php esc_html_e( 'Get Support', 'totalpoll' ); ?></a>
                </div>
            </div>
        </div>
        <div class="totalpoll-column totalpoll-column-third" ng-repeat="section in $ctrl.sections">
            <dashboard-links
                    heading="section.title"
                    description="section.description"
                    links="section.links">
            </dashboard-links>
        </div>
    </div>
</script>
<script type="text/ng-template" id="dashboard-links-component-template">
    <div class="totalpoll-box totalpoll-box-links" style="min-height: 300px">
        <div class="totalpoll-box-section">
            <div class="totalpoll-box-title">{{ $ctrl.heading }}</div>
            <div class="totalpoll-box-description">{{ $ctrl.description }}</div>
        </div>
        <div class="totalpoll-box-links-item" ng-repeat="link in $ctrl.links">
            <a href="{{ link.url }}" target="_blank" title="{{link.title}}">{{ link.title }}</a>
        </div>
    </div>
</script>
