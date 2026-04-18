<div class="totalpoll-question"
     totalpoll-min-selection="<?php echo absint( $question['settings']['selection']['minimum'] ); ?>"
     totalpoll-max-selection="<?php echo absint( $question['settings']['selection']['maximum'] ); ?>" <?php echo $template->questionAttributes( $question ); ?>>
    <div class="totalpoll-question-container">
        <div class="totalpoll-question-content" <?php echo $template->questionContentAttributes( $question ); ?>>
			<?php echo $template->questionContent( $question ); ?>
        </div>

        <div class="totalpoll-question-choices">
			<?php foreach ( $question['choices'] as $choice ): ?>
				<?php if ( ! empty( $choice['visibility'] ) ): ?>
					<?php include $template->getPath( 'views/vote/choice.php' ); ?>
				<?php endif; ?>
			<?php endforeach; ?>
            
        </div>
    </div>
</div>
