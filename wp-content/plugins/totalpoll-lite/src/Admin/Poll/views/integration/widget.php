<div class="totalpoll-integration-steps" ng-controller="SidebarIntegrationCtrl as $ctrl">
    <div class="totalpoll-integration-steps-item">
        <div class="totalpoll-integration-steps-item-number">
            <div class="totalpoll-integration-steps-item-number-circle">1</div>
        </div>
        <div class="totalpoll-integration-steps-item-content">
            <h3 class="totalpoll-h3">
                <?php esc_html_e('Add it to sidebar', 'totalpoll'); ?>
            </h3>
            <p>
                <?php esc_html_e('Start by adding this poll to one of available sidebars:', 'totalpoll'); ?>
            </p>
            <div class="totalpoll-integration-steps-item-copy">
                <select name="" ng-model="$ctrl.sidebar" ng-options="sidebar as sidebar.name for sidebar in information.sidebars">
                    <option value=""><?php esc_html_e('Select a sidebar', 'totalpoll'); ?></option>
                </select>
                <button type="button" class="button button-primary button-large" ng-disabled="!$ctrl.sidebar || $ctrl.sidebar.inserted"
                        ng-click="$ctrl.addWidgetToSidebar()">
                    <span ng-if="!$ctrl.sidebar.inserted"><?php esc_html_e( 'Insert', 'totalpoll' ); ?></span>
                    <span ng-if="$ctrl.sidebar.inserted"><?php esc_html_e( 'Inserted', 'totalpoll' ); ?></span>
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
                <?php esc_html_e('Preview', 'totalpoll'); ?>
            </h3>
            <p>
                <?php esc_html_e('Open the page which you have the same sidebar and test poll functionality.', 'totalpoll'); ?>
            </p>
        </div>
    </div>
</div>
