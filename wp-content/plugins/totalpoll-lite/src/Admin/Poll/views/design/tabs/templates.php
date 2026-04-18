<?php $customization = apply_filters( 'totalpoll/filters/admin/editor/templates/customization', true ); ?>
<customizer-tabs>
    <customizer-tab ng-repeat="template in $ctrl.getTemplates()" target="{{ template.id }}">
        {{ template.name }}
        <button type="button" class="button button-small"
                ng-class="{'button-primary': $ctrl.isTemplate(template.id)}"
                ng-disabled="$ctrl.isTemplate(template.id)"
                track="{ event : 'use-template', target: '{{ template.id }}' }"
                ng-click="$ctrl.changeTemplateTo(template, $event)">
            <span ng-if="!$ctrl.isTemplate(template.id)"><?php esc_html_e( 'Use', 'totalpoll' ); ?></span>
            <span ng-if="$ctrl.isTemplate(template.id)"><?php esc_html_e( 'Active', 'totalpoll' ); ?></span>
        </button>
    </customizer-tab>
</customizer-tabs>

<customizer-tab-content ng-repeat="template in $ctrl.getTemplates()" name="{{template.id}}" class="totalpoll-design-tabs-content-template">
    <div class="totalpoll-design-tabs-content-template-image">
        <img ng-src="{{template.images.cover}}" ng-attr-alt="{{template.name}}">
    </div>
    <div class="totalpoll-design-tabs-content-template-description" ng-bind="template.description"></div>
    <div class="totalpoll-design-tabs-content-template-meta">
        <div>
			<?php esc_html_e( 'By', 'totalpoll' ); ?>
            <a ng-href="{{template.author.url}}" target="_blank">{{template.author.name}}</a>
            &nbsp;&bullet;&nbsp;<?php esc_html_e( 'Version', 'totalpoll' ); ?>
            : {{template.version}}
        </div>

        <button type="button" class="button button-small"
                ng-class="{'button-primary': $ctrl.isTemplate(template.id)}"
                ng-disabled="$ctrl.isTemplate(template.id)"
                ng-click="$ctrl.changeTemplateTo(template, $event)">
            <span ng-if="!$ctrl.isTemplate(template.id)"><?php esc_html_e( 'Use', 'totalpoll' ); ?></span>
            <span ng-if="$ctrl.isTemplate(template.id)"><?php esc_html_e( 'Active', 'totalpoll' ); ?></span>
        </button>
    </div>
</customizer-tab-content>
