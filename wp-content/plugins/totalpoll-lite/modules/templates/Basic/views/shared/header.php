<?php
echo $form->open();

echo $template->userContent( $poll->getHeader() );

if ( ! $poll->isResultsScreen() && $form->haveCustomFields() && $poll->getSettingsItem('design.behaviours.fieldsFirst', false)): ?>
    <div class="totalpoll-form-custom-fields">
		<?php echo $form->fields(); ?>
    </div>
<?php endif;

if ( $poll->hasError() ):
	?>
    <div class="totalpoll-message totalpoll-message-error"><?php echo $poll->getErrorMessage(); ?></div>
<?php
endif;
?>
<div style="visibility: hidden; position: absolute; width: 0px; height: 0px;">
    <svg id="totalpoll-check-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
        <path d="M0,0H24V24H0Z" fill="none"/>
        <path d="M9,15.1999l-4.2-4.2L2.3999,13.4,9,20.0001,22.0001,7,19.6,4.5999Z"/>
    </svg>
</div>
