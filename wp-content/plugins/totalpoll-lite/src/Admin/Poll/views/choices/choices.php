<script type="text/ng-template" id="choices-component-template">
    <div ng-show="$ctrl.droppable" class="totalpoll-droppable">
        <span class="totalpoll-droppable-content"><?php esc_html_e( 'Drop to add', 'totalpoll' ); ?></span>
    </div>

	<?php
	/**
	 * Fires before choices.
	 *
	 * @since 4.0.0
	 */
	do_action( 'totalpoll/actions/before/admin/editor/choices', $this );
	?>

    <div class="totalpoll-containable-toolbar">
        <div class="button-group">
			<?php
			/**
			 * Fires at the 1st position of toolbar buttons.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/editor/choices/toolbar/1', $this );
			?>
            <button type="button" class="button button-large" ng-click="$ctrl.collapseChoices()" ng-disabled="$ctrl.items.length === 0">
				<?php esc_html_e( 'Collapse', 'totalpoll' ); ?>
            </button>
			<?php
			/**
			 * Fires at the 2nd position of toolbar buttons.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/editor/choices/toolbar/2', $this );
			?>
            <button type="button" class="button button-large" ng-click="$ctrl.expandChoices()" ng-disabled="$ctrl.items.length === 0">
				<?php esc_html_e( 'Expand', 'totalpoll' ); ?>
            </button>
			<?php
			/**
			 * Fires at the 3rd position of toolbar buttons.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/editor/choices/toolbar/3', $this );
			?>
        </div>

        <div class="button-group">
			<?php
			/**
			 * Fires at the 4th position of toolbar buttons.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/editor/choices/toolbar/4', $this );
			?>
            <button type="button" class="button button-large" disabled>
				<?php esc_html_e( 'Bulk', 'totalpoll' ); ?>
                <?php TotalPoll( 'upgrade-to-pro' ); ?>
            </button>
			<?php
			/**
			 * Fires at the 5th position of toolbar buttons.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/editor/choices/toolbar/5', $this );
			?>
            <button type="button" class="button button-large" ng-click="$ctrl.shuffleChoices()" ng-disabled="$ctrl.items.length < 2">
				<?php esc_html_e( 'Shuffle', 'totalpoll' ); ?>
            </button>
			<?php
			/**
			 * Fires at the 6th position of toolbar buttons.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/editor/choices/toolbar/6', $this );
			?>
            <button type="button" class="button button-large" ng-click="$ctrl.randomVotes()" ng-disabled="$ctrl.items.length === 0">
				<?php esc_html_e( 'Random Votes', 'totalpoll' ); ?>
            </button>
			<?php
			/**
			 * Fires at the 7th position of toolbar buttons.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/editor/choices/toolbar/7', $this );
			?>
        </div>

        <div class="button-group">
			<?php
			/**
			 * Fires at the 8th position of toolbar buttons.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/editor/choices/toolbar/8', $this );
			?>
            <button type="button" class="button button-large" disabled ng-disabled="$ctrl.items.length === 0">
				<?php esc_html_e( 'Filter', 'totalpoll' ); ?>
                <?php TotalPoll( 'upgrade-to-pro' ); ?>
            </button>
			<?php
			/**
			 * Fires at the 9th position of toolbar buttons.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/editor/choices/toolbar/9', $this );
			?>
            <button type="button" class="button button-large button-danger" ng-click="$ctrl.deleteChoices()" ng-disabled="$ctrl.items.length === 0">
				<?php esc_html_e( 'Delete All', 'totalpoll' ); ?>
            </button>
			<?php
			/**
			 * Fires at the 10th position of toolbar buttons.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/editor/choices/toolbar/10', $this );
			?>
            <button type="button" class="button button-large button-danger" ng-click="$ctrl.resetVotes()" ng-disabled="$ctrl.items.length === 0">
				<?php esc_html_e( 'Reset Votes', 'totalpoll' ); ?>
            </button>
			<?php
			/**
			 * Fires at the 11th position of toolbar buttons.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/editor/choices/toolbar/11', $this );
			?>
        </div>
    </div>

    <div class="totalpoll-containable-bulk" ng-if="$ctrl.bulkInput">
        <textarea name="" ng-model="$ctrl.bulkContent" rows="6" placeholder="<?php esc_html_e( 'One choice per line.', 'totalpoll' ); ?>"></textarea>
        <button type="button" class="button button-large" ng-click="$ctrl.insertBulkChoices()">
			<?php esc_html_e( 'Insert', 'totalpoll' ); ?>
        </button>
    </div>

    <div class="totalpoll-containable-types" ng-if="$ctrl.filterList">
        <label class="totalpoll-containable-types-item">
            <input type="checkbox" ng-model="$ctrl.types.text">
			<?php esc_html_e( 'Text', 'totalpoll' ); ?>
        </label>
        <label class="totalpoll-containable-types-item">
            <input type="checkbox" ng-model="$ctrl.types.image">
			<?php esc_html_e( 'Image', 'totalpoll' ); ?>
        </label>
        <label class="totalpoll-containable-types-item">
            <input type="checkbox" ng-model="$ctrl.types.video">
			<?php esc_html_e( 'Video', 'totalpoll' ); ?>
        </label>
        <label class="totalpoll-containable-types-item">
            <input type="checkbox" ng-model="$ctrl.types.audio">
			<?php esc_html_e( 'Audio', 'totalpoll' ); ?>
        </label>
        <label class="totalpoll-containable-types-item">
            <input type="checkbox" ng-model="$ctrl.types.html">
			<?php esc_html_e( 'HTML', 'totalpoll' ); ?>
        </label>
    </div>

    <div class="totalpoll-empty-state" ng-if="$ctrl.items.length === 0" ondragstart="return false;">
        <div class="totalpoll-empty-state-text">
			<?php esc_html_e( 'No choices yet. Add some by clicking on buttons below.', 'totalpoll' ); ?>
        </div>
    </div>
    <div class="totalpoll-containable-list"
         dnd-list="$ctrl.items"
         dnd-disable-if="$ctrl.items.length < 2">
        <dnd-nodrag ng-repeat="item in $ctrl.items"
                    dnd-draggable="item"
                    dnd-effect-allowed="move"
                    ng-show="$ctrl.isTypeActive(item.type)"
                    dnd-moved="$ctrl.deleteChoice($index, true)">
            <choice item="item"
                    index="$index"
                    on-delete="$ctrl.deleteChoice($index)"
                    on-override-votes="$ctrl.confirmOverride($event)"></choice>
        </dnd-nodrag>
        <div class="dndPlaceholder totalpoll-list-placeholder">
            <div class="totalpoll-list-placeholder-text">
				<?php esc_html_e( 'Move here', 'totalpoll' ); ?>
            </div>
        </div>
    </div>

    <div class="totalpoll-buttons-horizontal">
        <div class="totalpoll-buttons-horizontal-item"
             track="{ event : 'question-type', target: 'text' }"
             ng-click="$ctrl.insertChoice({type: 'text'})">
            <div class="dashicons dashicons-editor-textcolor"></div>
            <div class="totalpoll-buttons-horizontal-item-title">
				<?php esc_html_e( 'Text', 'totalpoll' ); ?>
            </div>
        </div>
        <div class="totalpoll-buttons-horizontal-item totalpoll-pro-badge-container"
             track="{ event : 'question-type', target: 'image' }"
             disabled>
            <div class="dashicons dashicons-format-image"></div>
            <div class="totalpoll-buttons-horizontal-item-title">
				<?php esc_html_e( 'Image', 'totalpoll' ); ?>
            </div>
            <?php TotalPoll( 'upgrade-to-pro' ); ?>
        </div>
        <div class="totalpoll-buttons-horizontal-item totalpoll-pro-badge-container"
             track="{ event : 'question-type', target: 'video' }"
             disabled>
            <div class="dashicons dashicons-format-video"></div>
            <div class="totalpoll-buttons-horizontal-item-title">
				<?php esc_html_e( 'Video', 'totalpoll' ); ?>
            </div>
            <?php TotalPoll( 'upgrade-to-pro' ); ?>
        </div>
        <div class="totalpoll-buttons-horizontal-item totalpoll-pro-badge-container"
             track="{ event : 'question-type', target: 'audio' }"
             disabled>
            <div class="dashicons dashicons-format-audio"></div>
            <div class="totalpoll-buttons-horizontal-item-title">
				<?php esc_html_e( 'Audio', 'totalpoll' ); ?>
            </div>
            <?php TotalPoll( 'upgrade-to-pro' ); ?>
        </div>
        <div class="totalpoll-buttons-horizontal-item totalpoll-pro-badge-container"
             track="{ event : 'question-type', target: 'html' }"
             disabled>
            <div class="dashicons dashicons-editor-code"></div>
            <div class="totalpoll-buttons-horizontal-item-title">
				<?php esc_html_e( 'HTML', 'totalpoll' ); ?>
            </div>
            <?php TotalPoll( 'upgrade-to-pro' ); ?>
        </div>
		<?php do_action( 'totalpoll/actions/editor/choices/types', $this ); ?>
    </div>


	<?php
	/**
	 * Fires after choices.
	 *
	 * @since 4.0.0
	 */
	do_action( 'totalpoll/actions/after/admin/editor/choices', $this );
	?>
</script>
