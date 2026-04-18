<?php
include $template->getPath( 'views/shared/header.php' );
?>
    <div class="totalpoll-questions">
		<?php
		foreach ( $poll->getQuestionsForVote() as $question ):
			include $template->getPath( 'views/vote/question.php' );
		endforeach;
		?>
    </div>
<?php
include $template->getPath( 'views/shared/footer.php' );