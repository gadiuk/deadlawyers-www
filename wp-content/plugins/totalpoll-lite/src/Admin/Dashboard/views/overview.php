<script type="text/ng-template" id="dashboard-overview-component-template">
    <div class="totalpoll-box">
        <div class="totalpoll-create-poll" ng-if="$ctrl.polls != null && !$ctrl.polls.length">
            <img src="<?php echo esc_attr( $this->env['url'] ); ?>assets/dist/images/general/create-poll.svg">
            <div class="totalpoll-box-title"><?php esc_html_e( 'It\'s time to create your first poll.',
			                                                   'totalpoll' ); ?></div>
            <div class="totalpoll-box-description"><?php esc_html_e( 'There are no polls yet.', 'totalpoll' ); ?></div>
            <a href="<?php echo esc_attr( admin_url( 'post-new.php?post_type=poll' ) ); ?>"
               class="button button-large button-primary"><?php esc_html_e( 'Create Poll', 'totalpoll' ); ?>
            </a>
        </div>
        <div class="totalpoll-overview" ng-class="{'totalpoll-processing': $ctrl.polls === null}">
            <div class="totalpoll-overview-item" ng-repeat="poll in $ctrl.polls">
                <a class="totalpoll-overview-item-segment totalpoll-overview-item-title" ng-href="{{ poll.editLink }}">
                    {{ poll.title || '<?php esc_html_e( '
                    Untitled
                    ', '
                    totalpoll
                    ' ); ?>' }}
                    <span class="totalpoll-overview-item-status" ng-class="{'active': poll.status == 'publish'}">
                        {{ poll.status === 'publish' ? '<?php esc_html_e( 'Live
                        ', '
                        totalpoll
                        ' ); ?>'
                        :
                        '<?php esc_html_e( '
                        Pending
                        ', '
                        totalpoll
                        ' ); ?>'}}
                    </span>
                </a>
                <a class="totalpoll-overview-item-segment totalpoll-overview-item-action" ng-href="{{ poll.permalink }}"
                   target="_blank">
                    <span class="dashicons dashicons-external"></span>
                </a>
                <div class="totalpoll-overview-item-segment totalpoll-overview-item-number">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <span>{{poll.statistics.votes | number}} <?php esc_html_e( 'Votes', 'totalpoll' ); ?></span>
                </div>
                <div class="totalpoll-overview-item-segment totalpoll-overview-item-number">
                    <span class="dashicons dashicons-admin-users"></span>
                    <span>{{poll.statistics.entries | number}} <?php esc_html_e( 'Entries', 'totalpoll' ); ?></span>
                </div>
            </div>

            <div class="totalpoll-overview-item" ng-if="$ctrl.polls != null && $ctrl.polls.length">
                <div class="totalpoll-overview-item-segment">
                    <a href="<?php echo esc_attr( admin_url( 'post-new.php?post_type=poll' ) ); ?>"
                       class="button button-large button-primary"><?php esc_html_e( 'Create Poll', 'totalpoll' ); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</script>
