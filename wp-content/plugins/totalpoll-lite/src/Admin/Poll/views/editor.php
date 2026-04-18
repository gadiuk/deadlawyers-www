<div id="totalpoll-poll-editor" ng-app="poll-editor" ng-controller="EditorCtrl as editor">
	<?php
	/**
	 * Fires before poll editor content.
	 *
	 * @since 4.0.0
	 */
	do_action( 'totalpoll/actions/before/admin/editor', $this );
	?>
	<?php include_once __DIR__ . '/loading.php'; ?>
    <!-- Migration -->
    <div class="totalpoll-box totalpoll-migration-nag" ng-class="{active: !editor.information.migrated}">
        <span class="dashicons dashicons-warning"></span>
        <div class="totalpoll-box-title"><?php esc_html_e( 'This poll has been created using an older version of TotalPoll, please migrate it.', 'totalpoll' ); ?></div>
        <a href="<?php echo esc_attr( admin_url( 'edit.php?post_type=poll&page=options&tab=options>migration' ) ) ?>" class="button button-primary"><?php esc_html_e( 'Migrate', 'totalpoll' ); ?></a>
    </div>

    <!-- Editor -->
    <div class="totalpoll-poll-wrapper">
        <div class="totalpoll-poll-tabs">
			<?php $firstTab = key( $tabs ) ?>
			<?php foreach ( $tabs as $tabId => $tab ): ?>
                <div class="totalpoll-poll-tabs-item <?php echo $tabId == $firstTab ? 'active' : ''; ?>" tab-switch="editor><?php echo esc_attr( $tabId ); ?>" <?php if ( $tabId == 'translations' ): ?>ng-if="editor.languages.length"<?php endif; ?>>
                    <div class="totalpoll-poll-tabs-item-icon">
                        <span class="dashicons dashicons-<?php echo esc_attr( $tab['icon'] ); ?>"></span>
                    </div>
					<?php echo esc_html( $tab['label'] ); ?>
                </div>
			<?php endforeach; ?>
        </div>
        <div class="totalpoll-poll-tabs-content-wrapper">
			<?php foreach ( $tabs as $tabId => $tab ): ?>
                <div class="totalpoll-tab-content <?php echo $tabId == $firstTab ? 'active' : ''; ?>" tab="editor><?php echo esc_attr( $tabId ); ?>">
					<?php
					/**
					 * Fires before poll editor tab content.
					 *
					 * @since 4.0.0
					 */
					do_action( 'totalpoll/actions/before/admin/editor/tabs/content', $tabId );

					$path = empty( $tab['file'] ) ? __DIR__ . "/{$tabId}/index.php" : $tab['file'];
					if ( file_exists( $path ) ):
						include_once $path;
					endif;

					/**
					 * Fires after poll editor tab content.
					 *
					 * @since 4.0.0
					 */
					do_action( 'totalpoll/actions/after/admin/editor/tabs/content', $tabId );
					?>
                </div>
			<?php endforeach; ?>
        </div>
    </div>

    <!-- Helpers -->
    <input type="hidden" name="totalpoll_current_tab" ng-value="getCurrentTab()">

    <!-- Poll settings field -->
    <textarea name="content" rows="30" class="widefat" readonly hidden
              ng-bind-template="{{editor.settings|json}}"><?php echo empty( $this->post ) ? '{}' : esc_textarea( $GLOBALS['post']->post_content ); ?></textarea>
	<?php
	// The ugly way, unfortunately.
	ob_start();
	wp_editor( '', 'tinymce-field', [
		'textarea_name'     => 'tinymce-textarea-name',
		'textarea_rows'     => 2,
		'drag_drop_upload'  => true,
		'tabfocus_elements' => 'content-html,save-post',
		'tinymce'           => [
			'wp_autoresize_on'   => false,
			'add_unload_trigger' => false,
		],
	] );
	$tinyMce = ob_get_clean();
	?>
    <script type="text/javascript">
        var TinyMCETemplate = <?php echo json_encode( $tinyMce ); ?>
    </script>

    <script type="text/ng-template" id="progressive-textarea-template">
        <textarea name="" ng-model="$ctrl.model" rows="{{$ctrl.rows || 4}}" ng-if="$ctrl.isSimple()" class="totalpoll-settings-field-input widefat"></textarea>
        <tinymce ng-model="$ctrl.model" uid="{{ $ctrl.uid }}" ng-if="$ctrl.isAdvanced()"></tinymce>
        <a ng-click="$ctrl.switchToAdvanced()" ng-if="$ctrl.isSimple()"><?php esc_html_e( 'Switch to advanced', 'totalpoll' ); ?></a>
    </script>

    <script type="text/javascript">
        document.querySelector('form#post').setAttribute('novalidate', 'novalidate');
    </script>

<!--    <feedback-collector></feedback-collector>-->

    <!-- Templates -->
	<?php //include __DIR__ . '/feedback-collector.php'; ?>
	<?php include __DIR__ . '/questions/questions.php'; ?>
	<?php include __DIR__ . '/questions/questions.php'; ?>
	<?php include __DIR__ . '/questions/question.php'; ?>
	<?php include __DIR__ . '/form/fields.php'; ?>
	<?php include __DIR__ . '/form/field.php'; ?>
	<?php include __DIR__ . '/form/field-text.php'; ?>
	<?php include __DIR__ . '/form/field-textarea.php'; ?>
	<?php include __DIR__ . '/form/field-select.php'; ?>
	<?php include __DIR__ . '/form/field-checkbox.php'; ?>
	<?php include __DIR__ . '/form/field-radio.php'; ?>
	<?php include __DIR__ . '/choices/choices.php'; ?>
	<?php include __DIR__ . '/choices/choice.php'; ?>
	<?php include __DIR__ . '/choices/choice-text.php'; ?>
	<?php include __DIR__ . '/choices/choice-image.php'; ?>
	<?php include __DIR__ . '/choices/choice-video.php'; ?>
	<?php include __DIR__ . '/choices/choice-audio.php'; ?>
	<?php include __DIR__ . '/choices/choice-html.php'; ?>

	<?php
	/**
	 * Fires after poll editor content.
	 *
	 * @since 4.0.0
	 */
	do_action( 'totalpoll/actions/after/admin/editor', $this );
	?>
</div>
