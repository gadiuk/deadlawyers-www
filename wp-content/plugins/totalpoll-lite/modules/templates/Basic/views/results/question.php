<div class="totalpoll-question" <?php echo $template->questionAttributes( $question ); ?>>
    <div class="totalpoll-question-container">
        <div class="totalpoll-question-content" <?php echo $template->questionContentAttributes( $question ); ?>>
			<?php echo $template->questionContent( $question ); ?>
        </div>

        <div class="totalpoll-question-choices">
			<?php foreach ( $question['choices'] as $choice ): ?>
				<?php if ( $choice['visibility'] ): ?>
					<?php include $template->getPath( 'views/results/choice.php' ); ?>
				<?php endif; ?>
			<?php endforeach; ?>
        </div>
    </div>
</div>
