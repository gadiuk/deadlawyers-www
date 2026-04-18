<div class="totalpoll-integration-steps totalpoll-pro-badge-container">
    <div class="totalpoll-integration-steps-item">
        <div class="totalpoll-integration-steps-item-number">
            <div class="totalpoll-integration-steps-item-number-circle">1</div>
        </div>
        <div class="totalpoll-integration-steps-item-content">
            <h3 class="totalpoll-h3">
                <?php esc_html_e('Prepare email template', 'totalpoll'); ?>
            </h3>
            <p>
                <?php esc_html_e('Start by preparing your email template with your questions and choices.', 'totalpoll'); ?>
            </p>
        </div>
    </div>
    <div class="totalpoll-integration-steps-item">
        <div class="totalpoll-integration-steps-item-number">
            <div class="totalpoll-integration-steps-item-number-circle">2</div>
        </div>
        <div class="totalpoll-integration-steps-item-content">
            <h3 class="totalpoll-h3">
                <?php esc_html_e('Adjust choices links', 'totalpoll'); ?>
            </h3>
            <p>
                <?php esc_html_e('Copy and paste choices links from the following list:', 'totalpoll'); ?>
            </p>
            <p>&nbsp;</p>
			<?php
			$permalink = esc_attr(
				add_query_arg( [
					'totalpoll' => [
						'action'  => 'vote',
						'choices' => [ '%QUESTION_UID%' => [ '%CHOICE_UID%' ] ]
					]
				], get_permalink() )
			);
			?>
            <table class="wp-list-table widefat striped" ng-repeat="question in $root.settings.questions">
                <thead>
                <tr>
                    <th colspan="2">
                        <?php esc_html_e('Question', 'totalpoll'); ?>
                        #{{$index+1}}
                    </th>
                </tr>
                </thead>
                <tr ng-repeat="choice in question.choices">
                    <td valign="middle">{{choice.label || (' (Choice #' + (choiceIndex + 1) + ')')}}</td>
                    <td class="totalpoll-width-5">
                        <button type="button" class="button button-primary button-small"
                                copy-to-clipboard="{{'<?php echo $permalink; ?>'.replace('%QUESTION_UID%', question.uid).replace('%CHOICE_UID%', choice.uid)}}">
                            <?php esc_html_e('Copy', 'totalpoll'); ?>
                        </button>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="totalpoll-integration-steps-item">
        <div class="totalpoll-integration-steps-item-number">
            <div class="totalpoll-integration-steps-item-number-circle">3</div>
        </div>
        <div class="totalpoll-integration-steps-item-content">
            <h3 class="totalpoll-h3">
                <?php esc_html_e('Preview', 'totalpoll'); ?>
            </h3>
            <p>
                <?php esc_html_e('Send a test email to yourself and test poll functionality.', 'totalpoll'); ?>
            </p>
        </div>
    </div>
    <?php TotalPoll( 'upgrade-to-pro' ); ?>
</div>
