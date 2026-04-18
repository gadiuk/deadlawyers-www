<label for="choice-<?php echo esc_attr( $choice['uid'] ); ?>-selector"
       tabindex="0"
       aria-label="<?php esc_attr_e( 'Vote for:', 'totalpoll' ); ?> <?php echo esc_attr( $choice['label'] ); ?>"
       class="totalpoll-question-choices-item totalpoll-question-choices-item-type-<?php echo $choice['type']; ?> <?php echo $form->isChoiceChecked( $choice,
                                                                                                                                                     $question ) ? 'totalpoll-question-choices-item-checked' : ''; ?>" <?php echo $template->choiceAttributes( $choice ); ?>>
    <div class="totalpoll-question-choices-item-container">
        
        <div class="totalpoll-question-choices-item-control">
            <div class="totalpoll-question-choices-item-selector totalpoll-question-choices-item-selector-<?php echo $form->isQuestionSupportMultipleSelection( $question ) ? 'multiple' : 'single'; ?>">
				<?php echo $form->getChoiceInput( $choice, $question ); ?>
                <div class="totalpoll-question-choices-item-selector-box">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#totalpoll-check-icon"></use>
                    </svg>
                </div>
            </div>
            <div class="totalpoll-question-choices-item-label" role="button">
                <span <?php echo $template->choiceLabelAttributes( $choice ); ?>><?php echo $template->choiceLabel( $choice ); ?></span>
            </div>
        </div>
    </div>
</label>
