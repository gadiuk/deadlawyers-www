<div class="totalpoll-buttons">
    <button type="button" class="totalpoll-button totalpoll-buttons-results"
            ng-if="$preview.isPreviousButtonVisible()"
            ng-click="$preview.previewPrevious()"><?php esc_html_e( 'Previous', 'totalpoll' ); ?></button>
    <button type="button" class="totalpoll-button totalpoll-buttons-results"
            ng-if="$preview.isNextButtonVisible()"
            ng-click="$preview.previewNext()"><?php esc_html_e( 'Next', 'totalpoll' ); ?></button>
    <button type="button" class="totalpoll-button totalpoll-buttons-results"
            ng-if="$preview.isResultsButtonVisible()"
            ng-click="$preview.previewVote()"><?php esc_html_e( 'Results', 'totalpoll' ); ?></button>
    <button type="button" class="totalpoll-button totalpoll-button-primary totalpoll-buttons-vote"
            ng-if="$preview.isVoteButtonVisible()"
            ng-click="$preview.previewResults()"><?php esc_html_e( 'Vote', 'totalpoll' ); ?></button>
    <button type="button" class="totalpoll-button totalpoll-button-primary totalpoll-buttons-vote"
            ng-if="$preview.isBackButtonVisible()"
            ng-click="$preview.previewBack()"><?php esc_html_e( 'Back', 'totalpoll' ); ?></button>
</div>

<div class="totalpoll-share" ng-if="$root.settings.share.websites.facebook || $root.settings.share.websites.twitter || $root.settings.share.websites.googleplus || $root.settings.share.websites.pinterest">
    <a ng-if="$root.settings.share.websites.facebook" href="#" class=" totalpoll-share-service totalpoll-share-service-facebook">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
            <path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"></path>
        </svg>
    </a>
    <a ng-if="$root.settings.share.websites.twitter" href="#" class="totalpoll-share-service totalpoll-share-service-twitter">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
            <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"></path>
        </svg>
    </a>
    <a ng-if="$root.settings.share.websites.googleplus" href="#" class="totalpoll-share-service totalpoll-share-service-googleplus">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
            <path d="M7 11v2.4h3.97c-.16 1.029-1.2 3.02-3.97 3.02-2.39 0-4.34-1.979-4.34-4.42 0-2.44 1.95-4.42 4.34-4.42 1.36 0 2.27.58 2.79 1.08l1.9-1.83c-1.22-1.14-2.8-1.83-4.69-1.83-3.87 0-7 3.13-7 7s3.13 7 7 7c4.04 0 6.721-2.84 6.721-6.84 0-.46-.051-.81-.111-1.16h-6.61zm0 0 17 2h-3v3h-2v-3h-3v-2h3v-3h2v3h3v2z"
                  fill-rule="evenodd" clip-rule="evenodd"></path>
        </svg>
    </a>
    <a ng-if="$root.settings.share.websites.pinterest" href="#" class="totalpoll-share-service totalpoll-share-service-pinterest">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
            <path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"
                  fill-rule="evenodd" clip-rule="evenodd"></path>
        </svg>
    </a>
</div>

</div>
</div>
