<customizer-tabs>
    <customizer-tab target="primary"><?php esc_html_e( 'Primary', 'totalpoll' ); ?></customizer-tab>
    <customizer-tab target="secondary"><?php esc_html_e( 'Secondary', 'totalpoll' ); ?></customizer-tab>
    <customizer-tab target="accent"><?php esc_html_e( 'Accent', 'totalpoll' ); ?></customizer-tab>
    <customizer-tab target="gray"><?php esc_html_e( 'Gray', 'totalpoll' ); ?></customizer-tab>
    <customizer-tab target="dark"><?php esc_html_e( 'Dark', 'totalpoll' ); ?></customizer-tab>
</customizer-tabs>

<customizer-tab-content name="primary">
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Primary', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.primary"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Primary (Contrast)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.primaryContrast"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Primary (Light)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.primaryLight"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Primary (Lighter)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.primaryLighter"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Primary (Dark)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.primaryDark"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Primary (Darker)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.primaryDarker"></customizer-control>
</customizer-tab-content>

<customizer-tab-content name="secondary">
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Secondary', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.secondary"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Secondary (Contrast)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.secondaryContrast"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Secondary (Light)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.secondaryLight"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Secondary (Lighter)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.secondaryLighter"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Secondary (Dark)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.secondaryDark"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Secondary (Darker)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.secondaryDarker"></customizer-control>
</customizer-tab-content>

<customizer-tab-content name="accent">
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Accent', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.accent"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Accent (Contrast)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.accentContrast"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Accent (Light)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.accentLight"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Accent (Lighter)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.accentLighter"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Accent (Dark)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.accentDark"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Accent (Darker)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.accentDarker"></customizer-control>
</customizer-tab-content>

<customizer-tab-content name="gray">
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Gray', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.gray"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Gray (Contrast)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.grayContrast"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Gray (Light)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.grayLight"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Gray (Lighter)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.grayLighter"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Gray (Dark)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.grayDark"></customizer-control>
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Gray (Darker)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.grayDarker"></customizer-control>
</customizer-tab-content>

<customizer-tab-content name="dark">
    <customizer-control
            type="color"
            label="<?php esc_attr_e( 'Dark (Body Text)', 'totalpoll' ); ?>"
            ng-model="$ctrl.settings.colors.dark"></customizer-control>
</customizer-tab-content>
