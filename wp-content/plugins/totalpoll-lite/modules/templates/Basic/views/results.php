<?php
include $template->getPath( 'views/shared/header.php' );

if ( $poll->isResultsHidden() ):
	echo $template->userContent( $poll->getHiddenResultsContent() );
else:
	?>
    <div class="totalpoll-questions">
		<?php
		foreach ( $poll->getQuestionsForResults() as $question ):
			include $template->getPath( 'views/results/question.php' );
		endforeach;
		?>
    </div>
	<?php
endif;

include $template->getPath( 'views/shared/footer.php' );
