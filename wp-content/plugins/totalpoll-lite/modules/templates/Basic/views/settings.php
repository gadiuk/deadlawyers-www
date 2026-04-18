<customizer-tabs>
    <customizer-tab target="container"><?php esc_html_e( 'Container', 'totalpoll' ); ?></customizer-tab>
    <customizer-tab target="question"><?php esc_html_e( 'Question', 'totalpoll' ); ?></customizer-tab>
    <customizer-tab target="choice"><?php esc_html_e( 'Choice', 'totalpoll' ); ?></customizer-tab>
    <customizer-tab target="votesbar"><?php esc_html_e( 'Votes bar', 'totalpoll' ); ?></customizer-tab>
    <customizer-tab target="form"><?php esc_html_e( 'Form', 'totalpoll' ); ?></customizer-tab>
    <customizer-tab target="message"><?php esc_html_e( 'Message', 'totalpoll' ); ?></customizer-tab>
    <customizer-tab target="button"><?php esc_html_e( 'Button', 'totalpoll' ); ?></customizer-tab>
</customizer-tabs>

<customizer-tab-content name="container">
    <customizer-tabs>
        <customizer-tab target="colors"><?php esc_html_e( 'Colors', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="padding"><?php esc_html_e( 'Padding', 'totalpoll' ); ?></customizer-tab>
    </customizer-tabs>

    <customizer-tab-content name="colors">
        <customizer-control type="color" label="<?php esc_attr_e( 'Background', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.container.colors.background"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="padding">
        <customizer-control type="padding" label="<?php esc_attr_e( 'Padding', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.container.padding"></customizer-control>
    </customizer-tab-content>
</customizer-tab-content>

<customizer-tab-content name="question">
    <customizer-tabs>
        <customizer-tab target="colors"><?php esc_html_e( 'Colors', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="border"><?php esc_html_e( 'Border', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="padding"><?php esc_html_e( 'Padding', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="text"><?php esc_html_e( 'Text', 'totalpoll' ); ?></customizer-tab>
    </customizer-tabs>

    <customizer-tab-content name="colors">
        <customizer-control type="color" label="<?php esc_attr_e( 'Background', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.question.colors.background"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Color', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.question.colors.color"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Border', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.question.colors.border"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="border">
        <customizer-control type="border" label="<?php esc_attr_e( 'Border', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.question.border"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="padding">
        <customizer-control type="padding" label="<?php esc_attr_e( 'Padding', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.question.padding"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="text">
        <customizer-control type="typography" label="<?php esc_attr_e( 'Text', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.question.text"></customizer-control>
    </customizer-tab-content>

</customizer-tab-content>

<customizer-tab-content name="choice">

    <customizer-tabs>
        <customizer-tab target="colors"><?php esc_html_e( 'Colors', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="border"><?php esc_html_e( 'Border', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="padding"><?php esc_html_e( 'Padding', 'totalpoll' ); ?></customizer-tab>
    </customizer-tabs>

    <customizer-tab-content name="colors">
        <customizer-control type="color" label="<?php esc_attr_e( 'Background', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.choice.colors.background"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Color', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.choice.colors.color"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Border', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.choice.colors.border"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Background (Hover)', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.choice.colors.backgroundHover"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Color (Hover)', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.choice.colors.colorHover"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Border (Hover)', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.choice.colors.borderHover"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Background (Checked)', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.choice.colors.backgroundChecked"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Color (Checked)', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.choice.colors.colorChecked"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Border (Checked)', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.choice.colors.borderChecked"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="border">
        <customizer-control type="border" label="<?php esc_attr_e( 'Border', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.choice.border"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="padding">
        <customizer-control type="padding" label="<?php esc_attr_e( 'Padding', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.choice.padding"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="text">
        <customizer-control type="typography" label="<?php esc_attr_e( 'Text', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.choice.text"></customizer-control>
    </customizer-tab-content>

</customizer-tab-content>

<customizer-tab-content name="votesbar">

    <customizer-tabs>
        <customizer-tab target="size"><?php esc_html_e( 'Size', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="colors"><?php esc_html_e( 'Colors', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="border"><?php esc_html_e( 'Border', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="padding"><?php esc_html_e( 'Padding', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="text"><?php esc_html_e( 'Text', 'totalpoll' ); ?></customizer-tab>
    </customizer-tabs>

    <customizer-tab-content name="colors">
        <customizer-control type="color" label="<?php esc_attr_e( 'Background (Start)', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.votesbar.colors.backgroundStart"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Background (End)', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.votesbar.colors.backgroundEnd"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Color', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.votesbar.colors.color"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Border', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.votesbar.colors.border"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="border">
        <customizer-control type="border" label="<?php esc_attr_e( 'Border', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.votesbar.border"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="padding">
        <customizer-control type="padding" label="<?php esc_attr_e( 'Padding', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.votesbar.padding"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="text">
        <customizer-control type="typography" label="<?php esc_attr_e( 'Text', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.votesbar.text"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="size">
        <customizer-control type="text" label="<?php esc_attr_e( 'Height', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.votesbar.size.height"></customizer-control>
    </customizer-tab-content>

</customizer-tab-content>

<customizer-tab-content name="form">

    <customizer-tabs>
        <customizer-tab target="colors"><?php esc_html_e( 'Colors', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="padding"><?php esc_html_e( 'Padding', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="text"><?php esc_html_e( 'Text', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="label"><?php esc_html_e( 'Label', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="input"><?php esc_html_e( 'Input', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="error"><?php esc_html_e( 'Errors', 'totalpoll' ); ?></customizer-tab>
    </customizer-tabs>

    <customizer-tab-content name="colors">
        <customizer-control type="color" label="<?php esc_attr_e( 'Background', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.colors.background"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Color', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.colors.color"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="padding">
        <customizer-control type="padding" label="<?php esc_attr_e( 'Padding', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.padding"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="text">
        <customizer-control type="typography" label="<?php esc_attr_e( 'Text', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.text"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="label">
        <customizer-tabs>
            <customizer-tab target="colors"><?php esc_html_e( 'Colors', 'totalpoll' ); ?></customizer-tab>
            <customizer-tab target="padding"><?php esc_html_e( 'Padding', 'totalpoll' ); ?></customizer-tab>
            <customizer-tab target="text"><?php esc_html_e( 'Text', 'totalpoll' ); ?></customizer-tab>
        </customizer-tabs>

        <customizer-tab-content name="colors">
            <customizer-control type="color" label="<?php esc_attr_e( 'Color', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.label.colors.color"></customizer-control>
        </customizer-tab-content>

        <customizer-tab-content name="padding">
            <customizer-control type="padding" label="<?php esc_attr_e( 'Padding', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.label.padding"></customizer-control>
        </customizer-tab-content>

        <customizer-tab-content name="text">
            <customizer-control type="typography" label="<?php esc_attr_e( 'Text', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.label.text"></customizer-control>
        </customizer-tab-content>

    </customizer-tab-content>

    <customizer-tab-content name="input">
        <customizer-tabs>
            <customizer-tab target="colors"><?php esc_html_e( 'Colors', 'totalpoll' ); ?></customizer-tab>
            <customizer-tab target="border"><?php esc_html_e( 'Border', 'totalpoll' ); ?></customizer-tab>
            <customizer-tab target="padding"><?php esc_html_e( 'Padding', 'totalpoll' ); ?></customizer-tab>
            <customizer-tab target="text"><?php esc_html_e( 'Text', 'totalpoll' ); ?></customizer-tab>
        </customizer-tabs>

        <customizer-tab-content name="colors">
            <customizer-control type="color" label="<?php esc_attr_e( 'Background', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.input.colors.background"></customizer-control>
            <customizer-control type="color" label="<?php esc_attr_e( 'Color', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.input.colors.color"></customizer-control>
            <customizer-control type="color" label="<?php esc_attr_e( 'Border', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.input.colors.border"></customizer-control>
        </customizer-tab-content>

        <customizer-tab-content name="border">
            <customizer-control type="border" label="<?php esc_attr_e( 'Border', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.input.border"></customizer-control>
        </customizer-tab-content>

        <customizer-tab-content name="padding">
            <customizer-control type="padding" label="<?php esc_attr_e( 'Padding', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.input.padding"></customizer-control>
        </customizer-tab-content>

        <customizer-tab-content name="text">
            <customizer-control type="typography" label="<?php esc_attr_e( 'Text', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.input.text"></customizer-control>
        </customizer-tab-content>

    </customizer-tab-content>

    <customizer-tab-content name="error">

        <customizer-tabs>
            <customizer-tab target="colors"><?php esc_html_e( 'Colors', 'totalpoll' ); ?></customizer-tab>
            <customizer-tab target="padding"><?php esc_html_e( 'Padding', 'totalpoll' ); ?></customizer-tab>
            <customizer-tab target="text"><?php esc_html_e( 'Text', 'totalpoll' ); ?></customizer-tab>
        </customizer-tabs>

        <customizer-tab-content name="colors">
            <customizer-control type="color" label="<?php esc_attr_e( 'Background', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.error.colors.background"></customizer-control>
            <customizer-control type="color" label="<?php esc_attr_e( 'Color', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.error.colors.color"></customizer-control>
        </customizer-tab-content>

        <customizer-tab-content name="padding">
            <customizer-control type="padding" label="<?php esc_attr_e( 'Padding', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.error.padding"></customizer-control>
        </customizer-tab-content>

        <customizer-tab-content name="text">
            <customizer-control type="typography" label="<?php esc_attr_e( 'Text', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.form.error.text"></customizer-control>
        </customizer-tab-content>

    </customizer-tab-content>

</customizer-tab-content>

<customizer-tab-content name="message">

    <customizer-tabs>
        <customizer-tab target="colors"><?php esc_html_e( 'Colors', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="padding"><?php esc_html_e( 'Padding', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="text"><?php esc_html_e( 'Text', 'totalpoll' ); ?></customizer-tab>
    </customizer-tabs>

    <customizer-tab-content name="colors">
        <customizer-control type="color" label="<?php esc_attr_e( 'Background', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.message.colors.background"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Color', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.message.colors.color"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Background (error)', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.message.colors.backgroundError"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Color (error)', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.message.colors.colorError"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Border', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.message.colors.border"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="border">
        <customizer-control type="border" label="<?php esc_attr_e( 'Border', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.message.border"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="padding">
        <customizer-control type="padding" label="<?php esc_attr_e( 'Padding', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.container.padding"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="text">
        <customizer-control type="typography" label="<?php esc_attr_e( 'Text', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.button.text"></customizer-control>
    </customizer-tab-content>

</customizer-tab-content>

<customizer-tab-content name="button">
    <customizer-tabs>
        <customizer-tab target="colors"><?php esc_html_e( 'Colors', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="padding"><?php esc_html_e( 'Padding', 'totalpoll' ); ?></customizer-tab>
        <customizer-tab target="text"><?php esc_html_e( 'Text', 'totalpoll' ); ?></customizer-tab>
    </customizer-tabs>

    <customizer-tab-content name="colors">
        <customizer-control type="color" label="<?php esc_attr_e( 'Background', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.button.colors.background"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Color', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.button.colors.color"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Border', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.button.colors.border"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Background (hover)', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.button.colors.backgroundHover"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Color (hover)', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.button.colors.colorHover"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Border (hover)', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.button.colors.borderHover"></customizer-control>

        <customizer-control type="color" label="<?php esc_attr_e( 'Primary background', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.button.colors.backgroundPrimary"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Primary border', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.button.colors.borderPrimary"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Primary color', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.button.colors.colorPrimary"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Primary background (hover)', 'totalpoll' ); ?>"
                            ng-model="$root.settings.design.custom.button.colors.backgroundPrimaryHover"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Primary border (hover)', 'totalpoll' ); ?>"
                            ng-model="$root.settings.design.custom.button.colors.borderPrimaryHover"></customizer-control>
        <customizer-control type="color" label="<?php esc_attr_e( 'Primary color (hover)', 'totalpoll' ); ?>"
                            ng-model="$root.settings.design.custom.button.colors.colorPrimaryHover"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="padding">
        <customizer-control type="padding" label="<?php esc_attr_e( 'Padding', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.button.padding"></customizer-control>
    </customizer-tab-content>

    <customizer-tab-content name="text">
        <customizer-control type="typography" label="<?php esc_attr_e( 'Text', 'totalpoll' ); ?>" ng-model="$root.settings.design.custom.button.text"></customizer-control>
    </customizer-tab-content>

</customizer-tab-content>
