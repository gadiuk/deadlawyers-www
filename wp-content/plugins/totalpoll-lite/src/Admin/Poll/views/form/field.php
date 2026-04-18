<!-- GENERIC FIELD -->
<script type="text/ng-template" id="field-component-template">
    <div class="totalpoll-containable-list-item" ng-class="{'active': !$ctrl.isCollapsed()}">
        <div class="totalpoll-containable-list-item-toolbar">
            <div class="totalpoll-containable-list-item-toolbar-collapse" ng-click="$ctrl.toggleCollapsed()">
                <span class="totalpoll-containable-list-item-toolbar-collapse-text">{{ $ctrl.index + 1 }}</span>
                <span class="dashicons dashicons-arrow-up" ng-if="!$ctrl.isCollapsed()"></span>
                <span class="dashicons dashicons-arrow-down" ng-if="$ctrl.isCollapsed()"></span>
            </div>
			<?php
			/**
			 * Fires before field preview toolbar.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/before/admin/editor/fields/toolbar/preview', $this );
			?>
            <div class="totalpoll-containable-list-item-toolbar-preview" dnd-handle ng-click="$ctrl.toggleCollapsed()">
                <span class="totalpoll-containable-list-item-toolbar-preview-text">
                    {{ $ctrl.item.label || '<?php echo esc_js( esc_html__( '
                    Untitled
                    ', '
                    totalpoll
                    ' ) ); ?>' }}
	                <?php
	                /**
	                 * Fires after field preview toolbar text.
	                 *
	                 * @since 4.0.0
	                 */
	                do_action( 'totalpoll/actions/editor/fields/toolbar/preview/text', $this );
	                ?>
                </span>
                <span class="totalpoll-containable-list-item-toolbar-preview-type">
                    <span ng-if="$ctrl.item.type === 'text'"><?php esc_html_e( 'Text', 'totalpoll' ); ?></span>
                    <span ng-if="$ctrl.item.type === 'textarea'"><?php esc_html_e( 'Textarea', 'totalpoll' ); ?></span>
                    <span ng-if="$ctrl.item.type === 'select'"><?php esc_html_e( 'Select', 'totalpoll' ); ?></span>
                    <span ng-if="$ctrl.item.type === 'checkbox'"><?php esc_html_e( 'Checkbox', 'totalpoll' ); ?></span>
                    <span ng-if="$ctrl.item.type === 'radio'"><?php esc_html_e( 'Radio', 'totalpoll' ); ?></span>
                    <span ng-if="$ctrl.item.validations.filled.enabled"><?php esc_html_e( 'Required',
                                                                                          'totalpoll' ); ?></span>
					<?php
					/**
					 * Fires after field preview toolbar type.
					 *
					 * @since 4.0.0
					 */
					do_action( 'totalpoll/actions/editor/fields/toolbar/preview/type', $this );
					?>
                </span>
            </div>
			<?php
			/**
			 * Fires after field preview toolbar.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/after/admin/editor/fields/toolbar/preview', $this );
			?>

			<?php
			/**
			 * Fires before field delete button.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/before/admin/editor/fields/toolbar/delete', $this );
			?>
            <div class="totalpoll-containable-list-item-toolbar-delete">
                <button class="button button-danger button-small" type="button" ng-click="$ctrl.onDelete()">
					<?php esc_html_e( 'Delete', 'totalpoll' ); ?>
                </button>
            </div>
			<?php
			/**
			 * Fires after field delete button.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/after/admin/editor/fields/toolbar/delete', $this );
			?>
        </div>
        <div class="totalpoll-containable-list-item-editor" ng-hide="$ctrl.isCollapsed()">
            <dnd-nodrag>
                <div class="totalpoll-tabs-container">
                    <div class="totalpoll-tabs">
						<?php
						/**
						 * Fires before field tabs content.
						 *
						 * @since 4.0.0
						 */
						do_action( 'totalpoll/actions/before/admin/editor/fields/tabs', $this );
						?>
                        <div class="totalpoll-tabs-item active" tab-switch="editor>form>{{$ctrl.prefix('basic','>')}}">
							<?php esc_html_e( 'Basic', 'totalpoll' ); ?>
                        </div>
                        <div class="totalpoll-tabs-item" tab-switch="editor>form>{{$ctrl.prefix('validations','>')}}">
							<?php esc_html_e( 'Validations', 'totalpoll' ); ?>
                        </div>
                        <div class="totalpoll-tabs-item" tab-switch="editor>form>{{$ctrl.prefix('html','>')}}">
							<?php esc_html_e( 'HTML', 'totalpoll' ); ?>
                        </div>
						<?php
						/**
						 * Fires after field tabs content.
						 *
						 * @since 4.0.0
						 */
						do_action( 'totalpoll/actions/after/admin/editor/fields/tabs', $this );
						?>
                    </div>
                    <div class="totalpoll-tabs-content" ng-include="'field-type-' + $ctrl.item.type + '-template'">

                    </div>
                </div>
            </dnd-nodrag>
        </div>
    </div>
</script>
<!-- BASIC: LABEL -->
<script type="text/ng-template" id="field-basic-label-template">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label" for="{{$ctrl.prefix('-label')}}">
			<?php esc_html_e( 'Label', 'totalpoll' ); ?>
        </label>
        <input id="{{$ctrl.prefix('-label')}}" class="widefat" type="text"
               placeholder="<?php esc_html_e( 'Field label', 'totalpoll' ); ?>"
               ng-model="$ctrl.item.label"
               ng-blur="$ctrl.generateName()">
    </div>
</script>
<!-- BASIC: NAME  -->
<script type="text/ng-template" id="field-basic-name-template">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label" for="{{$ctrl.prefix('-name')}}">
			<?php esc_html_e( 'Name', 'totalpoll' ); ?>
        </label>
        <input id="{{$ctrl.prefix('-name')}}" class="widefat" type="text"
               placeholder="<?php esc_html_e( 'Field name', 'totalpoll' ); ?>" ng-model="$ctrl.item.name">
    </div>
</script>
<!-- BASIC: DEFAULT VALUE -->
<script type="text/ng-template" id="field-basic-default-value-template">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label"
               for="{{$ctrl.prefix('-default-value')}}">
			<?php esc_html_e( 'Default value', 'totalpoll' ); ?>
        </label>
        <input id="{{$ctrl.prefix('-default-value')}}" class="widefat" type="text"
               placeholder="<?php esc_html_e( 'Field default value', 'totalpoll' ); ?>"
               ng-if="$ctrl.item.type === 'text' || $ctrl.item.type === 'radio'"
               ng-model="$ctrl.item.defaultValue">
        <textarea id="{{$ctrl.prefix('-default-value')}}" class="widefat"
                  placeholder="<?php esc_html_e( 'Field default value', 'totalpoll' ); ?>"
                  ng-if="$ctrl.item.type === 'textarea' || $ctrl.item.type === 'select' || $ctrl.item.type === 'checkbox'"
                  ng-model="$ctrl.item.defaultValue"></textarea>
        <p class="totalpoll-feature-tip" ng-if="$ctrl.item.type === 'select' || $ctrl.item.type === 'checkbox'">
			<?php esc_html_e( 'Default value per line.', 'totalpoll' ); ?>
        </p>
    </div>
</script>
<!-- BASIC: OPTIONS  -->
<script type="text/ng-template" id="field-basic-options-template">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label" for="field-{{field.uid}}-options">
			<?php esc_html_e( 'Options', 'totalpoll' ); ?>
        </label>
        <textarea id="field-{{field.uid}}-options" class="widefat" type="text"
                  placeholder="<?php esc_html_e( 'option_key:Option label', 'totalpoll' ); ?>"
                  ng-model="$ctrl.item.options" rows="6"></textarea>
        <p class="totalpoll-feature-tip">
			<?php esc_html_e( 'Option per line.', 'totalpoll' ); ?>
        </p>
        <p class="totalpoll-feature-tip">
			<?php esc_html_e( 'Option format is "option : label"', 'totalpoll' ); ?>
        </p>
    </div>
</script>
<!-- BASIC: MULTIPLE SELECTION  -->
<script type="text/ng-template" id="field-basic-multiple-values-template">
    <div class="totalpoll-settings-item">
        <div class="totalpoll-settings-field">
            <label>
                <input type="checkbox" name="" ng-model="$ctrl.item.attributes.multiple">
				<?php esc_html_e( 'Allow multiple values', 'totalpoll' ); ?>
            </label>
        </div>
    </div>
</script>
<!-- VALIDATION: FILLED -->
<script type="text/ng-template" id="field-validations-filled-template">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" ng-checked="$ctrl.item.validations.filled.enabled"
                   ng-model="$ctrl.item.validations.filled.enabled">
			<?php esc_html_e( 'Filled (required)', 'totalpoll' ); ?>
        </label>
    </div>
</script>
<!-- VALIDATION: EMAIL -->
<script type="text/ng-template" id="field-validations-email-template">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" ng-checked="$ctrl.item.validations.email.enabled"
                   ng-model="$ctrl.item.validations.email.enabled">
			<?php esc_html_e( 'Email', 'totalpoll' ); ?>
        </label>
    </div>
</script>
<!-- VALIDATION: UNIQUE -->
<script type="text/ng-template" id="field-validations-unique-template">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" ng-checked="$ctrl.item.validations.unique.enabled"
                   ng-model="$ctrl.item.validations.unique.enabled">
			<?php esc_html_e( 'Unique', 'totalpoll' ); ?>
            <span class="totalpoll-feature-details"
                  tooltip="<?php esc_html_e( 'This field value must be unique in entries table.',
			                                 'totalpoll' ); ?>">?</span>
        </label>
    </div>
</script>
<!-- VALIDATION: FILTER -->
<script type="text/ng-template" id="field-validations-filter-template">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" ng-checked="$ctrl.item.validations.filter.enabled"
                   ng-model="$ctrl.item.validations.filter.enabled">
			<?php esc_html_e( 'Filter by list', 'totalpoll' ); ?>
        </label>
    </div>
    <div class="totalpoll-settings-item-advanced" ng-class="{active: $ctrl.item.validations.filter.enabled}">
        <table class="wp-list-table widefat striped"
               ng-controller="RepeaterCtrl as $repeater"
               ng-init="$repeater.items = $ctrl.item.validations.filter.rules = ($ctrl.item.validations.filter.rules || [])">
            <thead>
            <tr>
                <th class="totalpoll-width-15">
					<?php esc_html_e( 'Type', 'totalpoll' ); ?>
                </th>
                <th class="widefat">
					<?php esc_html_e( 'Value', 'totalpoll' ); ?>
                </th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr ng-repeat="item in $repeater.items track by $index">
                <td>
                    <select class="totalpoll-settings-field-input widefat" ng-model="item.type">
                        <option value="allow">
							<?php esc_html_e( 'Allow', 'totalpoll' ); ?>
                        </option>
                        <option value="deny">
							<?php esc_html_e( 'Deny', 'totalpoll' ); ?>
                        </option>
                    </select>
                </td>
                <td>
                    <input type="text" class="totalpoll-settings-field-input widefat" ng-model="item.value"
                           placeholder="<?php esc_html_e( '* means wildcard.', 'totalpoll' ); ?>">
                </td>
                <td>
                    <div class="button-group">
                        <button type="button" class="button button-icon" ng-click="$repeater.moveUp($index)"
                                ng-disabled="$index === 0">
                            <span class="dashicons dashicons-arrow-up-alt2"></span>
                        </button>
                        <button type="button" class="button button-icon" ng-click="$repeater.moveDown($index)"
                                ng-disabled="$index === $repeater.items.length - 1">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                        <button type="button" class="button button-danger" ng-click="$repeater.deleteItem($index)">
							<?php esc_html_e( 'Delete', 'totalpoll' ); ?>
                        </button>
                    </div>
                </td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="3">
                    <div class="textright">
                        <button type="button" class="button button-primary"
                                ng-click="$repeater.addItem({type: 'allow'})">
							<?php esc_html_e( 'Add new value', 'totalpoll' ); ?>
                        </button>
                    </div>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
</script>
<!-- VALIDATION: REGEX -->
<script type="text/ng-template" id="field-validations-regex-template">
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" ng-checked="$ctrl.item.validations.regex.enabled"
                   ng-model="$ctrl.item.validations.regex.enabled">
			<?php esc_html_e( 'Regular Expression', 'totalpoll' ); ?>
        </label>
    </div>
    <div class="totalpoll-settings-item-advanced" ng-class="{active: $ctrl.item.validations.regex.enabled}">
        <div class="totalpoll-settings-field">
            <label class="totalpoll-settings-field-label" for="field-{{$ctrl.item.uid}}-validations-regex-against">
				<?php esc_html_e( 'Expression', 'totalpoll' ); ?>
                <span class="totalpoll-feature-details"
                      tooltip="<?php esc_html_e( 'Run user input against a regular expression.',
				                                 'totalpoll' ); ?>">?</span>
            </label>
            <input id="field-{{$ctrl.item.uid}}-validations-regex-pattern" type="text"
                   class="totalpoll-settings-field-input widefat"
                   ng-model="$ctrl.item.validations.regex.pattern">
            <p class="totalpoll-feature-tip">
				<?php esc_html_e( 'Must be a valid regular expression.', 'totalpoll' ); ?>
            </p>
        </div>
        <div class="totalpoll-settings-field">
            <label class="totalpoll-settings-field-label"
                   for="field-{{$ctrl.item.uid}}-validations-regex-error-message">
				<?php esc_html_e( 'Error message', 'totalpoll' ); ?>
                <span class="totalpoll-feature-details"
                      tooltip="<?php esc_html_e( 'This message will be shown if the validation failed.',
				                                 'totalpoll' ); ?>">?</span>
            </label>
            <input id="field-{{$ctrl.item.uid}}-validations-regex-error-message" type="text"
                   ng-model="$ctrl.item.validations.regex.errorMessage"
                   class="totalpoll-settings-field-input widefat">
            <p class="totalpoll-feature-tip" ng-non-bindable>
				<?php esc_html_e( '{{label}} for a field label.', 'totalpoll' ); ?>
            </p>
        </div>
        <div class="totalpoll-settings-field">
            <label class="totalpoll-settings-field-label">
				<?php esc_html_e( 'Comparison', 'totalpoll' ); ?>
            </label> <label>
                <input type="radio" name="" ng-model="$ctrl.item.validations.regex.type"
                       ng-checked="$ctrl.item.validations.regex.type == 'match'"
                       value="match">
				<?php esc_html_e( 'Must match', 'totalpoll' ); ?>
            </label> &nbsp; <label>
                <input type="radio" name="" ng-model="$ctrl.item.validations.regex.type"
                       ng-checked="$ctrl.item.validations.regex.type == 'notmatch'"
                       value="notmatch">
				<?php esc_html_e( 'Must not match', 'totalpoll' ); ?>
            </label>
        </div>
        <div class="totalpoll-settings-field">
            <label class="totalpoll-settings-field-label">
				<?php esc_html_e( 'Modifiers', 'totalpoll' ); ?>
            </label> <label>
                <input type="checkbox" name="" ng-model="$ctrl.item.validations.regex.modifiers['i']" value="i">
				<?php esc_html_e( 'Case Insensitive', 'totalpoll' ); ?>
            </label> &nbsp; <label>
                <input type="checkbox" name="" ng-model="$ctrl.item.validations.regex.modifiers['m']" value="m">
				<?php esc_html_e( 'Multiline', 'totalpoll' ); ?>
            </label> &nbsp; <label>
                <input type="checkbox" name="" ng-model="$ctrl.item.validations.regex.modifiers['u']" value="u">
				<?php esc_html_e( 'Unicode Support', 'totalpoll' ); ?>
            </label>
        </div>
    </div>
</script>
<!-- HTML: FIELD CLASS -->
<script type="text/ng-template" id="field-html-css-class-template">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label" for="{{$ctrl.prefix('-class')}}">
			<?php esc_html_e( 'Field CSS classes', 'totalpoll' ); ?>
        </label>
        <input id="{{$ctrl.prefix('-class')}}" class="widefat" type="text"
               placeholder="<?php esc_html_e( 'Field classes', 'totalpoll' ); ?>"
               ng-model="$ctrl.item.attributes.class">
    </div>
</script>
<!-- HTML: FIELD TEMPLATE -->
<script type="text/ng-template" id="field-html-template-template">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label" for="{{$ctrl.prefix('-template')}}">
			<?php esc_html_e( 'Template', 'totalpoll' ); ?>
        </label>
        <input id="{{$ctrl.prefix('-template')}}" class="widefat" type="text"
               placeholder="<?php esc_html_e( 'Field template', 'totalpoll' ); ?>" ng-model="$ctrl.item.template">
        <p></p>
        <div class="button-group">
            <button type="button" class="button button-small"
                    ng-click="$ctrl.item.template = '<?php echo esc_attr( '<div class="totalpoll-form-field totalpoll-column-full">{{label}}{{field}}<div class="totalpoll-form-field-errors">{{errors}}</div></div>' ); ?>'">
				<?php esc_html_e( 'full column', 'totalpoll' ); ?>
            </button>
            <button type="button" class="button button-small"
                    ng-click="$ctrl.item.template = '<?php echo esc_attr( '<div class="totalpoll-form-field totalpoll-column-half">{{label}}{{field}}<div class="totalpoll-form-field-errors">{{errors}}</div></div>' ); ?>'">
				<?php esc_html_e( '1/2 column', 'totalpoll' ); ?>
            </button>
            <button type="button" class="button button-small"
                    ng-click="$ctrl.item.template = '<?php echo esc_attr( '<div class="totalpoll-form-field totalpoll-column-third">{{label}}{{field}}<div class="totalpoll-form-field-errors">{{errors}}</div></div>' ); ?>'">
				<?php esc_html_e( '1/3 column', 'totalpoll' ); ?>
            </button>
        </div>
        <p class="totalpoll-feature-tip" ng-non-bindable>
			<?php esc_html_e( '{{label}} for field label.', 'totalpoll' ); ?>
        </p>
        <p class="totalpoll-feature-tip" ng-non-bindable>
			<?php esc_html_e( '{{errors}} for field errors.', 'totalpoll' ); ?>
        </p>
        <p class="totalpoll-feature-tip" ng-non-bindable>
			<?php esc_html_e( '{{field}} for field input.', 'totalpoll' ); ?>
        </p>
    </div>
</script>
