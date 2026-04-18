<div id="totalpoll-log" class="wrap totalpoll-page" ng-app="log" ng-class="{ 'totalpoll-processing' : $ctrl.isProcessing() }">
    <h1 class="totalpoll-page-title"><?php esc_html_e( 'Log', 'totalpoll' ); ?></h1>
    <log-browser></log-browser>
    <script type="text/ng-template" id="log-browser-component-template">
        <table class="wp-list-table widefat striped totalpoll-log-browser-list" ng-class="{'totalpoll-processing': $ctrl.isProcessing()}">
            <thead class="totalpoll-log-browser-list-header-wrapper">
            <tr>
                <td colspan="<?php echo count( $columns ) + 1; ?>">
                    <div class="totalpoll-log-browser-list-header">
                        <div class="totalpoll-log-browser-list-header-visible-columns">
							<?php foreach ( $columns as $columnId => $column ): ?>
                                <label><input type="checkbox" ng-init="$ctrl.columns['<?php echo $columnId ?>'] = <?php echo empty( $column['default'] ) ? 'false' : 'true'; ?>" ng-model="$ctrl.columns.<?php echo esc_attr( $columnId ); ?>"><?php echo esc_html( $column['label'] ); ?></label>
							<?php endforeach; ?>
                        </div>
                        <div class="totalpoll-log-browser-list-header-date">
                            <span><?php esc_html_e( 'From', 'totalpoll' ); ?></span>
                            <input type="text" datetime-picker='{"timepicker":false, "mask":true, "format": "Y-m-d"}' ng-model="$ctrl.filters.from">
                            <span><?php esc_html_e( 'To', 'totalpoll' ); ?></span>
                            <input type="text" datetime-picker='{"timepicker":false, "mask":true, "format": "Y-m-d"}' ng-model="$ctrl.filters.to">
							<?php
							/**
							 * Fires after filter inputs in log browser interface.
							 *
							 * @since 4.0.0
							 */
							do_action( 'totalpoll/actions/admin/log/filters' );
							?>
                            <div class="button-group">
                                <button class="button" ng-click="$ctrl.resetFilters()" ng-disabled="!($ctrl.filters.from || $ctrl.filters.to)">
									<?php esc_html_e( 'Clear', 'totalpoll' ); ?>
                                </button>
                                <button class="button button-primary" ng-click="$ctrl.loadPage(1)">
									<?php esc_html_e( 'Apply', 'totalpoll' ); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
				<?php foreach ( $columns as $columnId => $column ): ?>
                    <th scope="col" <?php if ( empty( $column['compact'] ) ): ?>class="totalpoll-log-browser-list-collapsed totalpoll-log-browser-list-column-<?php echo esc_attr( $columnId ); ?>"<?php endif; ?>
                        ng-show="$ctrl.columns.<?php echo esc_attr( $columnId ); ?>"><?php echo esc_html( $column['label'] ); ?></th>
				<?php endforeach; ?>
                <th scope="col" class="totalpoll-log-browser-list-collapsed totalpoll-log-browser-list-column-actions"><?php echo esc_html_e( 'Actions', 'totalpoll' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr class="totalpoll-log-browser-list-item" ng-repeat="item in $ctrl.items track by $index">
				<?php foreach ( $columns as $columnId => $column ): ?>
					<?php if ( $columnId === 'status' ): ?>
                        <td class="totalpoll-log-browser-list-collapsed totalpoll-log-browser-list-column-<?php echo esc_attr( $columnId ); ?>" ng-class="{'success': item.isAccepted(), 'error': item.isRejected()}" ng-show="$ctrl.columns.status">{{item.getStatus()}}</td>
					<?php elseif ( $columnId === 'action' ): ?>
                        <td class="totalpoll-log-browser-list-collapsed totalpoll-log-browser-list-column-<?php echo esc_attr( $columnId ); ?>" ng-show="$ctrl.columns.action">{{item.getAction()}}</td>
					<?php elseif ( $columnId === 'date' ): ?>
                        <td class="totalpoll-log-browser-list-collapsed totalpoll-log-browser-list-column-<?php echo esc_attr( $columnId ); ?>" ng-show="$ctrl.columns.date" title="{{item.getDate()|date:'yyyy-MM-dd @ HH:mm'}} (<?php esc_attr_e( 'Local Time', 'totalpoll' ); ?>)">{{ item.getUTCDate()
                            }} (UTC)
                        </td>
					<?php elseif ( $columnId === 'poll' ): ?>
                        <td class="totalpoll-log-browser-list-collapsed totalpoll-log-browser-list-column-<?php echo esc_attr( $columnId ); ?>" ng-show="$ctrl.columns.poll"><a target="_blank" ng-href="{{item.getPollEditLink()}}">{{item.getPollTitle()}}</a></td>
					<?php elseif ( $columnId === 'user_id' ): ?>
                        <td class="totalpoll-log-browser-list-collapsed totalpoll-log-browser-list-column-<?php echo esc_attr( $columnId ); ?>" ng-show="$ctrl.columns.user_id">{{item.getUser('id') || 'N/A' }}</td>
					<?php elseif ( $columnId === 'user_login' ): ?>
                        <td class="totalpoll-log-browser-list-collapsed totalpoll-log-browser-list-column-<?php echo esc_attr( $columnId ); ?>" ng-show="$ctrl.columns.user_login">{{item.getUser('login') || 'N/A'}}</td>
					<?php elseif ( $columnId === 'user_name' ): ?>
                        <td class="totalpoll-log-browser-list-collapsed totalpoll-log-browser-list-column-<?php echo esc_attr( $columnId ); ?>" ng-show="$ctrl.columns.user_name">{{item.getUser('name') || 'Anonymous'}}</td>
					<?php elseif ( $columnId === 'user_email' ): ?>
                        <td class="totalpoll-log-browser-list-collapsed totalpoll-log-browser-list-column-<?php echo esc_attr( $columnId ); ?>" ng-show="$ctrl.columns.user_email">{{item.getUser('email') || 'N/A'}}</td>
					<?php elseif ( $columnId === 'browser' ): ?>
                        <td class="totalpoll-log-browser-list-collapsed totalpoll-log-browser-list-column-<?php echo esc_attr( $columnId ); ?>" ng-show="$ctrl.columns.browser">{{(item.getUseragent()|platform).description}}</td>
					<?php elseif ( $columnId === 'ip' ): ?>
                        <td class="totalpoll-log-browser-list-collapsed totalpoll-log-browser-list-column-<?php echo esc_attr( $columnId ); ?>" ng-show="$ctrl.columns.ip">{{item.getIP()}}</td>
					<?php elseif ( $columnId === 'details' ): ?>
                        <td class="totalpoll-log-browser-list-compact totalpoll-log-browser-list-column-<?php echo esc_attr( $columnId ); ?>" ng-show="$ctrl.columns.details" ng-bind-html="item.getDetails()|table"></td>
					<?php else: ?>
                        <td class="totalpoll-log-browser-list-collapsed totalpoll-log-browser-list-column-<?php echo esc_attr( $columnId ); ?>" ng-show="$ctrl.columns['<?php echo esc_attr( $columnId ); ?>']"><?php echo empty( $column['content'] ) ? '' : $column['content']; ?></td>
					<?php endif; ?>
				<?php endforeach; ?>
                <td class="totalpoll-log-browser-list-collapsed totalpoll-log-browser-list-column-actions">
                    <button type="button" class="button button-small" type="button" ng-click="$ctrl.openItem(item)"><?php esc_html_e( 'Open', 'totalpoll' ); ?></button>
                    <button type="button" class="button button-small" type="button" ng-click="$ctrl.removeItem(item, $event)"><?php esc_html_e( 'Delete', 'totalpoll' ); ?></button>
                </td>
            </tr>
            <tr ng-if="!$ctrl.items.length">
                <td colspan="<?php echo count( $columns ) + 1; ?>"><?php esc_html_e( 'Nothing. Nada. Niente. Nickts. Rien.', 'totalpoll' ); ?></td>
            </tr>
            </tbody>
            <tfoot>
            <tr class="totalpoll-log-browser-list-footer-wrapper">
                <td scope="col" colspan="<?php echo count( $columns ) + 1; ?>">
                    <div class="totalpoll-log-browser-list-footer">
                        <div class="totalpoll-log-browser-list-footer-pagination">
                            <div class="button-group">
                                <button class="button" ng-class="{'button-primary': $ctrl.hasPreviousPage()}" ng-click="$ctrl.previousPage()" ng-disabled="$ctrl.isFirstPage()"><?php esc_html_e( 'Previous', 'totalpoll' ); ?></button>
                                <button class="button" ng-class="{'button-primary': $ctrl.hasNextPage()}" ng-click="$ctrl.nextPage()" ng-disabled="$ctrl.isLastPage()"><?php esc_html_e( 'Next', 'totalpoll' ); ?></button>
                            </div>
                        </div>
                        <div class="totalpoll-log-browser-list-footer-export">
                            <button type="button" class="button button-small" type="button" style="margin-right: 1rem" ng-click="$ctrl.purgeLog($event)"><?php esc_html_e( 'Purge', 'totalpoll' ); ?></button>
                            <span><?php esc_html_e( 'Download as', 'totalpoll' ); ?></span>
                            <div class="button-group">
								<?php foreach ( $formats as $format => $label ): ?>
                                    <button class="button" ng-class="{'button-primary': $ctrl.canExport()}" ng-click="$ctrl.exportAs('<?php echo esc_js( $format ); ?>')" ng-disabled="!$ctrl.canExport()"><?php echo esc_html( $label ); ?></button>
								<?php endforeach; ?>
                                
                                <button class="button button-primary" disabled>CSV <?php TotalPoll( 'upgrade-to-pro' ); ?></button>
                                <button class="button button-primary" disabled>JSON <?php TotalPoll( 'upgrade-to-pro' ); ?></button>
								
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            </tfoot>
        </table>

        <h1 class="totalpoll-page-title" ng-if="$ctrl.exports.length"><?php esc_html_e( 'Exports', 'totalpoll' ); ?></h1>
        <div class="totalpoll-log-browser-exports" ng-if="$ctrl.exports.length">
            <div class="totalpoll-log-browser-exports-item" ng-repeat="export in $ctrl.exports track by $index">
                <span>{{export.file || export.format}} ({{export.status}})</span>
                <progress value="{{export.processed}}" max="{{export.total}}" ng-if="export.status !== 'finished'"></progress>
                <small ng-if="export.status !== 'finished'">{{ $ctrl.exportPercentage(export) }}%</small>
                <a href="{{ export.url }}" ng-if="export.url" class="button button-primary" download>Download</a>
            </div>
        </div>

        <div class="totalpoll-log-browser-modal" ng-show="$ctrl.isModalOpen()">
            <div class="totalpoll-log-browser-modal-content">
                <div class="totalpoll-log-browser-modal-header">
                    <span class="totalpoll-log-browser-modal-title"><?php esc_html_e( 'Log', 'totalpoll' ); ?>&nbsp;#{{$ctrl.currentItem.getId()}}</span>
                    <span class="totalpoll-log-browser-modal-status" ng-class="{'log-accepted': $ctrl.currentItem.isAccepted(), 'log-rejected': $ctrl.currentItem.isRejected()}">{{$ctrl.currentItem.getStatus()}}</span>
                    <span class="totalpoll-log-browser-modal-close dashicons dashicons-no-alt" ng-click="$ctrl.closeModal()"></span>
                </div>
                <div class="totalpoll-log-browser-modal-body">
                    <table class="wp-list-table widefat striped">
						<?php do_action( 'totalpoll/actions/admin/log/modal/before', $this ); ?>
                        <tr>
                            <th colspan="2"><?php esc_html_e( 'Poll', 'totalpoll' ); ?></th>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <a ng-attr-href="{{$ctrl.currentItem.getPollEditLink()}}">{{$ctrl.currentItem.getPollTitle()}}</a>
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2"><?php esc_html_e( 'Received choices', 'totalpoll' ); ?></th>
                        </tr>

                        <tr ng-repeat="question in $ctrl.currentItem.get('poll.questions')">
                            <td>{{ question.content }}</td>
                            <td ng-if="$ctrl.currentItem.get('details.skipped').indexOf(question.uid) !== -1">
                                <div ng-repeat="choice in question.choices" ng-if="$ctrl.currentItem.get('choices').indexOf(choice.uid) != -1">{{ choice.label }}</div>
                            </td>
                            <td ng-if="$ctrl.currentItem.get('details.skipped').indexOf(question.uid) == -1">
                                <span class="totalpoll-log-browser-modal-status log-warning" style="margin-left: 0;"><?php esc_html_e( 'Skipped', 'totalpoll' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'IP', 'totalpoll' ); ?></th>
                            <th><?php esc_html_e( 'Date', 'totalpoll' ); ?></th>
                        </tr>
                        <tr>
                            <td>{{$ctrl.currentItem.getIP()}}</td>
                            <td>{{$ctrl.currentItem.getUTCDate()}}</td>
                        </tr>
                        <tr>
                            <th colspan="2"><?php esc_html_e( 'Browser', 'totalpoll' ); ?></th>
                        </tr>
                        <tr>
                            <td colspan="2">{{$ctrl.currentItem.getUseragent()}}</td>
                        </tr>

                        <tr ng-if="$ctrl.currentItem.getUser().id > 0">
                            <th colspan="2"><?php esc_html_e( 'User', 'totalpoll' ); ?></th>
                        </tr>
                        <tr ng-if="$ctrl.currentItem.getUser().id > 0" ng-repeat="(key, value) in $ctrl.currentItem.getUser()">
                            <td>{{key}}</td>
                            <td>{{value}}</td>
                        </tr>
                        <tr ng-if="$ctrl.currentItem.getEntry()">
                            <th colspan="2"><?php esc_html_e( 'Entry', 'totalpoll' ); ?></th>
                        </tr>
                        <tr ng-if="$ctrl.currentItem.getEntry()" ng-repeat="(key, value) in $ctrl.currentItem.getEntry().fields">
                            <td>{{key}}</td>
                            <td>{{value}}</td>
                        </tr>
						<?php do_action( 'totalpoll/actions/admin/log/modal/after', $this ); ?>
                    </table>
                </div>
                <div class="totalpoll-log-browser-modal-footer">
                    <span role="button" class="dashicons dashicons-arrow-left-alt" ng-click="$ctrl.previousItem()" ng-attr-disabled="{{$ctrl.hasPreviousItem() ? undefined : true}}"></span>
                    <span>{{ $ctrl.currentItemIndex + 1 }} <?php esc_html_e( 'of', 'totalpoll' ); ?> {{ $ctrl.items.length }}</span>
                    <span role="button" class="dashicons dashicons-arrow-right-alt" ng-click="$ctrl.nextItem()" ng-attr-disabled="{{$ctrl.hasNextItem() ? undefined : true}}"></span>
                </div>
            </div>
        </div>
    </script>

</div>
