<script type="text/ng-template" id="field-type-text-template">
	<?php
	/**
	 * Fires before text field content.
	 *
	 * @since 4.0.0
	 */
	do_action( 'totalpoll/actions/before/admin/editor/fields/type/text', $this );
	?>
    <div class="totalpoll-tab-content active" tab="editor>form>{{$ctrl.prefix('basic','>')}}">
		<?php
		/**
		 * Fires before text field basic tab content.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/before/admin/editor/fields/type/text/basic', $this );
		?>
        <div class="totalpoll-settings-item" ng-include="'field-basic-label-template'"></div>
        <div class="totalpoll-settings-item" ng-include="'field-basic-name-template'"></div>
        <div class="totalpoll-settings-item" ng-include="'field-basic-default-value-template'"></div>
		<?php
		/**
		 * Fires after text field basic tab content.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/after/admin/editor/fields/type/text/basic', $this );
		?>
    </div>
    <div class="totalpoll-tab-content" tab="editor>form>{{$ctrl.prefix('validations','>')}}">
		<?php
		/**
		 * Fires before text field validations tab content.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/before/admin/editor/fields/type/text/validations', $this );
		?>
        <div class="totalpoll-settings-item" ng-include="'field-validations-filled-template'"></div>
        <div class="totalpoll-settings-item" ng-include="'field-validations-email-template'"></div>
        <div class="totalpoll-settings-item" ng-include="'field-validations-unique-template'"></div>
        <div class="totalpoll-settings-item" ng-include="'field-validations-filter-template'"></div>
        <div class="totalpoll-settings-item" ng-include="'field-validations-regex-template'"></div>
		<?php
		/**
		 * Fires after text field validations tab content.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/after/admin/editor/fields/type/text/validations', $this );
		?>
    </div>
    <div class="totalpoll-tab-content" tab="editor>form>{{$ctrl.prefix('html','>')}}">
		<?php
		/**
		 * Fires before text field html tab content.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/before/admin/editor/fields/type/text/html', $this );
		?>
        <div class="totalpoll-settings-item" ng-include="'field-html-css-class-template'"></div>
        <div class="totalpoll-settings-item" ng-include="'field-html-template-template'"></div>
		<?php
		/**
		 * Fires after text field html tab content.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/after/admin/editor/fields/type/text/html', $this );
		?>
    </div>
	<?php
	/**
	 * Fires after text field content.
	 *
	 * @since 4.0.0
	 */
	do_action( 'totalpoll/actions/after/admin/editor/fields/type/text', $this );
	?>
</script>