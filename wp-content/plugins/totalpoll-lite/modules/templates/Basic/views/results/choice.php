<div class="totalpoll-question-choices-item totalpoll-question-choices-item-results totalpoll-question-choices-item-type-<?php echo $choice['type']; ?> <?php echo $poll->isChoiceChecked( $choice['uid'] ) ? 'totalpoll-question-choices-item-checked' : ''; ?>" <?php echo $template->choiceAttributes( $choice, $question ); ?>>
    <div class="totalpoll-question-choices-item-container">
        
        <div class="totalpoll-question-choices-item-label">
            <span <?php echo $template->choiceLabelAttributes( $choice ); ?>><?php echo $template->choiceLabel( $choice ); ?></span>
            <div class="totalpoll-question-choices-item-votes">
                <div class="totalpoll-question-choices-item-votes-bar" style="width: <?php echo $poll->getChoiceVotesPercentageWithLabel( $choice['uid'] ); ?>"></div>
                <div class="totalpoll-question-choices-item-votes-text <?php echo $choice['votes'] == 0 ? 'is-zero' : '' ?>"><?php echo $poll->getChoiceVotesFormatted( $choice['uid'] ); ?></div>
            </div>
        </div>
    </div>
</div>
