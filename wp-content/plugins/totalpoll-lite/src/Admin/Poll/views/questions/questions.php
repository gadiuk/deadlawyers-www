<script type="text/ng-template" id="questions-component-template">
    <div class="totalpoll-questions-list">
        <div class="totalpoll-questions-list-tabs"
             dnd-list="$ctrl.items"
             dnd-disable-if="$ctrl.items.length < 2">
			<?php
			/**
			 * Fires before questions.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/before/admin/editor/questions' );
			?>
            <div class="totalpoll-questions-list-tabs-item"
                 ng-repeat="item in $ctrl.items"
                 ng-class="{'active': $ctrl.isCurrentQuestion(item.uid)}"
                 ng-click="$ctrl.setCurrentQuestion($index)"
                 dnd-draggable="item"
                 dnd-effect-allowed="move"
                 dnd-moved="$ctrl.deleteQuestion($index, true, false)">
                <div class="totalpoll-questions-list-tabs-item-title">
					<?php esc_html_e( 'Question', 'totalpoll' ); ?>
                    #{{$index + 1}}
                    <small>{{item.choices.length}}
						<?php esc_html_e( 'Choices', 'totalpoll' ); ?>
                    </small>
                    <div ng-if="$ctrl.previewQuestion" ng-bind-html="$ctrl.escape(item.content)"
                         class="totalpoll-questions-list-tabs-item-preview">

                    </div>
                </div>
                <button class="button button-danger button-icon button-small" type="button"
                        ng-disabled="$ctrl.items.length < 2"
                        ng-click="$ctrl.deleteQuestion($index, false, true)"
                        title="<?php esc_attr_e( 'Delete', 'totalpoll' ); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
				<?php
				/**
				 * Fires after questions sidebar buttons.
				 *
				 * @since 4.0.0
				 */
				do_action( 'totalpoll/actions/admin/editor/questions/sidebar/buttons' );
				?>
            </div>

            <div class="totalpoll-settings-item show-question-preview">
                <div class="totalpoll-settings-item">
                    <div class="totalpoll-settings-field">
                        <label>
                            <input type="checkbox" name="" ng-model="$ctrl.previewQuestion">
					        <?php esc_html_e( 'Show question preview', 'totalpoll' ); ?>
                        </label>
                    </div>
                </div>
            </div>

            <div class="button button-primary" ng-click="$ctrl.addQuestion()">
                <span class="dashicons dashicons-plus"></span>
				<?php esc_html_e( 'New Question', 'totalpoll' ); ?>
            </div>

            <div class="totalpoll-questions-bulk-insert" ng-if="$ctrl.bulkInsert">
                <div class="totalpoll-questions-bulk-insert-content">
                    <h3><?php esc_html_e( 'Insert questions in bulk', 'totalpoll' ); ?></h3>
                    <hr>
                    <label for=""
                           class="totalpoll-settings-field-label"><?php esc_html_e( 'Question separator (optional)',
					                                                                'totalpoll' ); ?></label>
                    <input class="totalpoll-settings-field-input widefat" ng-model="$ctrl.bulkSeperator">

                    <small><kbd>\n</kbd><?php esc_attr_e( "represents new line.", 'totalpoll' ); ?></small>
                    <br><br>
                    <label for=""
                           class="totalpoll-settings-field-label"><?php esc_html_e( 'Insert questions and choices',
					                                                                'totalpoll' ); ?></label>
                    <textarea name=""
                              class="totalpoll-settings-field-input widefat"
                              rows="10"
                              placeholder="{{ 'Question 1\n* Choice 1\n* Choice 2\n* Choice 3' + $ctrl.bulkSeperator.replaceAll('\\n', '\n') + 'Question 2\n* Choice 1\n* Choice 2\n* Choice 3' }}"
                              ng-model="$ctrl.bulkQuestions"></textarea>

                    <br><br>
                    <label for=""
                           class="totalpoll-settings-field-label"><?php esc_html_e( 'With every parsed question, insert the following choices (optional)',
					                                                                'totalpoll' ); ?></label>
                    <textarea name=""
                              class="totalpoll-settings-field-input widefat"
                              rows="4"
                              placeholder="<?php esc_attr_e( "Choice 1\nChoice 2\nChoice 3",
					                                         'totalpoll' ); ?>"
                              ng-model="$ctrl.bulkChoices"></textarea>
                    <button type="button" class="button button-primary button-large widefat"
                            ng-click="$ctrl.parseBulkInsert()">
						<?php esc_html_e( 'Parse', 'totalpoll' ); ?>
                    </button>

                    <div ng-if="$ctrl.bulkQuestionsAndChoices.length">
                        <br>
                        <label for="" class="totalpoll-settings-field-label"><?php esc_html_e( 'Parsed questions',
						                                                                       'totalpoll' ); ?></label>
                        <table class="wp-list-table widefat striped">
                            <tr>
                                <th>Question</th>
                                <th>Choices</th>
                            </tr>
                            <tr ng-repeat="item in $ctrl.bulkQuestionsAndChoices">
                                <td>
                                    <strong>{{ item.content }}</strong>
                                </td>
                                <td>
                                    <ol>
                                        <li ng-repeat="choice in item.choices">{{ choice.label }}</li>
                                    </ol>
                                </td>
                            </tr>
                        </table>

                        <button type="button"
                                class="button button-primary button-large widefat" ng-click="$ctrl.processBulkInsert()">
							<?php esc_html_e( 'Insert questions and choices', 'totalpoll' ); ?>
                        </button>
                    </div>
                    <button type="button" class="button button-link widefat" ng-click="$ctrl.hideBulkInsert()">
						<?php esc_html_e( 'Cancel', 'totalpoll' ); ?>
                    </button>
                </div>
            </div>

            <div class="button button-link" ng-click="$ctrl.showBulkInsert()"><?php esc_html_e( 'Bulk insert questions',
			                                                                                    'totalpoll' ); ?></div>

            <div class="dndPlaceholder totalpoll-questions-list-tabs-item totalpoll-questions-list-tabs-item-placeholder">
				<?php esc_html_e( 'Move here', 'totalpoll' ); ?>
            </div>

			<?php
			/**
			 * Fires after questions.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/after/admin/editor/questions' );
			?>
        </div>
        <question ng-if="$ctrl.getCurrentQuestion()" item="$ctrl.getCurrentQuestion()" index="$ctrl.currentIndex"
                  class="totalpoll-questions-list-item"></question>
    </div>
</script>
