<script type="text/ng-template" id="choice-component-template">
    <div class="totalpoll-containable-list-item" ng-class="{'active': !$ctrl.isCollapsed()}">
        <div class="totalpoll-containable-list-item-toolbar">
            <div class="totalpoll-containable-list-item-toolbar-collapse" ng-click="$ctrl.toggleCollapsed()">
                <span class="totalpoll-containable-list-item-toolbar-collapse-text">{{ $ctrl.index + 1 }}</span>
                <span class="dashicons dashicons-arrow-up" ng-if="!$ctrl.isCollapsed()"></span>
                <span class="dashicons dashicons-arrow-down" ng-if="$ctrl.isCollapsed()"></span>
            </div>
			<?php
			/**
			 * Fires before choice preview toolbar.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/before/admin/editor/choices/toolbar/preview', $this );
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
	                 * Fires after choice preview toolbar text.
	                 *
	                 * @since 4.0.0
	                 */
	                do_action( 'totalpoll/actions/editor/choices/toolbar/preview/text', $this );
	                ?>
                </span>
                <span class="totalpoll-containable-list-item-toolbar-preview-type">
                    <span ng-if="$ctrl.item.type === 'text'"><?php esc_html_e( 'Text', 'totalpoll' ); ?></span>
                    <span ng-if="$ctrl.item.type === 'image'"><?php esc_html_e( 'Image', 'totalpoll' ); ?></span>
                    <span ng-if="$ctrl.item.type === 'video'"><?php esc_html_e( 'Video', 'totalpoll' ); ?></span>
                    <span ng-if="$ctrl.item.type === 'audio'"><?php esc_html_e( 'Audio', 'totalpoll' ); ?></span>
                    <span ng-if="$ctrl.item.type === 'html'"><?php esc_html_e( 'HTML', 'totalpoll' ); ?></span>
					<?php
					/**
					 * Fires after choice preview toolbar type.
					 *
					 * @since 4.0.0
					 */
					do_action( 'totalpoll/actions/editor/choices/toolbar/preview/type', $this );
					?>
                </span>
            </div>
			<?php
			/**
			 * Fires after choice preview toolbar.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/after/admin/editor/choices/toolbar/preview', $this );
			?>

			<?php
			/**
			 * Fires before choice votes input.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/before/admin/editor/choices/toolbar/votes', $this );
			?>
            <dnd-nodrag>
                <div class="totalpoll-containable-list-item-toolbar-votes" ondragstart="return false;">
                    <label for="{{$ctrl.prefix('votes')}}">
						<?php esc_html_e( 'Votes', 'totalpoll' ); ?>
                    </label>
                    <input type="number"
                           min="0"
                           placeholder="<?php esc_html_e( 'Votes', 'totalpoll' ); ?>"
                           ng-init="$ctrl.item.votesOverride || ($ctrl.item.votesOverride = 0)"
                           ng-focus="$ctrl.onOverrideVotes({$event: $event})"
                           ng-style="{'width': ( ($ctrl.item.votesOverride.toString().length * 6) + 30 )}"
                           name="{{$ctrl.prefix('votes')}}" id="{{$ctrl.prefix('votes')}}"
                           ng-model="$ctrl.item.votesOverride">
                    <button type="reset" ng-if="$ctrl.item.votesOverride !== $ctrl.item.votes" class="button"
                            ng-click="$ctrl.item.votesOverride = $ctrl.item.votes">
						<?php esc_html_e( 'Revert', 'totalpoll' ); ?>
                    </button>
                </div>
            </dnd-nodrag>
			<?php
			/**
			 * Fires after choice votes input.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/after/admin/editor/choices/toolbar/votes', $this );
			?>

			<?php
			/**
			 * Fires before choice delete button.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/before/admin/editor/choices/toolbar/convert', $this );
			?>
            <div class="totalpoll-containable-list-item-toolbar-convert">
                <button class="button button-icon" type="button">
                    <span class="dashicons dashicons-randomize"></span>
                </button>
                <div class="button-dropdown">
                    <span class="button-dropdown-helper"><?php esc_html_e( 'Convert to...', 'totalpoll' ); ?></span>
                    <button class="button button-small widefat" type="button" ng-disabled="$ctrl.item.type == 'text'"
                            ng-class="{active: $ctrl.item.type == 'text'}" ng-click="$ctrl.convertTo('text')">
						<?php esc_html_e( 'Text', 'totalpoll' ); ?>
                    </button>
                    <button class="button button-small widefat" type="button" ng-disabled="$ctrl.item.type == 'image'"
                            ng-class="{active: $ctrl.item.type == 'image'}" ng-click="$ctrl.convertTo('image')">
						<?php esc_html_e( 'Image', 'totalpoll' ); ?>
                    </button>
                    <button class="button button-small widefat" type="button" ng-disabled="$ctrl.item.type == 'video'"
                            ng-class="{active: $ctrl.item.type == 'video'}" ng-click="$ctrl.convertTo('video')">
						<?php esc_html_e( 'Video', 'totalpoll' ); ?>
                    </button>
                    <button class="button button-small widefat" type="button" ng-disabled="$ctrl.item.type == 'audio'"
                            ng-class="{active: $ctrl.item.type == 'audio'}" ng-click="$ctrl.convertTo('audio')">
						<?php esc_html_e( 'Audio', 'totalpoll' ); ?>
                    </button>
                    <button class="button button-small widefat" type="button" ng-disabled="$ctrl.item.type == 'html'"
                            ng-class="{active: $ctrl.item.type == 'html'}" ng-click="$ctrl.convertTo('html')">
						<?php esc_html_e( 'HTML', 'totalpoll' ); ?>
                    </button>
                </div>
            </div>
            <div class="totalpoll-containable-list-item-toolbar-transfer" ng-if="$ctrl.questions.length > 1">
                <button class="button button-icon" type="button">
                    <span class="dashicons dashicons-external"></span>
                </button>
                <div class="button-dropdown">
                    <span class="button-dropdown-helper"><?php esc_html_e( 'Transfer to...', 'totalpoll' ); ?></span>

                    <button class="button button-small widefat" type="button" ng-repeat="question in $ctrl.questions"
                            ng-if="$ctrl.choiceQuestion.uid != question.uid" title="{{question.content.substr(0,60)}}"
                            ng-click="$ctrl.moveToQuestion($index)">
                        Question #{{$index + 1}}
                    </button>
                </div>
            </div>
			<?php
			/**
			 * Fires before choice convert button.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/after/admin/editor/choices/toolbar/convert', $this );
			?>

			<?php
			/**
			 * Fires before choice visibility toggle.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/before/admin/editor/choices/toolbar/visibility', $this );
			?>
            <dnd-nodrag>
                <div class="totalpoll-containable-list-item-toolbar-visibility"
                     ng-class="{'active': $ctrl.isVisible()}">
                    <button class="button button-icon" ng-click="$ctrl.toggleVisibility()" type="button">
                        <span class="dashicons dashicons-visibility" ng-if="$ctrl.isVisible()"></span>
                        <span class="dashicons dashicons-hidden" ng-if="!$ctrl.isVisible()"></span>
                    </button>
                </div>
            </dnd-nodrag>
			<?php
			/**
			 * Fires after choice visibility toggle.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/after/admin/editor/choices/toolbar/visibility', $this );
			?>

			<?php
			/**
			 * Fires before choice delete button.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/before/admin/editor/choices/toolbar/delete', $this );
			?>
            <dnd-nodrag>
                <div class="totalpoll-containable-list-item-toolbar-delete">
                    <button class="button button-danger button-icon" type="button"
                            ng-click="$ctrl.onDelete()">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </dnd-nodrag>
			<?php
			/**
			 * Fires before choice delete button.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/after/admin/editor/choices/toolbar/delete', $this );
			?>
        </div>

		<?php
		/**
		 * Fires before choice content.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/before/admin/editor/choices/content', $this );
		?>
        <dnd-nodrag>
            <div class="totalpoll-containable-list-item-editor"
                 ng-include="'choice-type-' + $ctrl.item.type + '-template'"
                 ng-hide="$ctrl.isCollapsed()">
            </div>
        </dnd-nodrag>
		<?php
		/**
		 * Fires after choice content.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/after/admin/editor/choices/content', $this );
		?>
    </div>
</script>
