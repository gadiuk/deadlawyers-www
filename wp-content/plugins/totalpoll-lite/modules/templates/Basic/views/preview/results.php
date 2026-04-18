<div class="totalpoll-questions" ng-if="$preview.isScreen('results')">
    <div class="totalpoll-question" ng-repeat="(questionIndex, question) in $root.settings.questions" ng-if="$preview.isQuestionVisible(questionIndex)">
        <div class="totalpoll-question-container">
            <div class="totalpoll-question-content" ng-bind-html="$preview.escape(question.content)">
            </div>

            <div class="totalpoll-question-choices">
                <div class="totalpoll-question-choices-item totalpoll-question-choices-item-results totalpoll-question-choices-item-type-{{choice.type}}"
                     ng-repeat="choice in question.choices" ng-if="choice.visibility">
                    <div class="totalpoll-question-choices-item-container">
                        <div class="totalpoll-question-choices-item-content-container" ng-if="choice.type != 'text'">
                            <div class="totalpoll-question-choices-item-content" ng-if="choice.type === 'image'">
                                <img ng-src="{{ choice.image.thumbnail || choice.image.full }}">
                            </div>
                            <div class="totalpoll-question-choices-item-content" ng-if="choice.type === 'video'">
                                <video ng-if="!choice.video.html && choice.video.full" ng-src="{{choice.video.full}}" controls ng-attr-poster="{{ choice.video.thumbnail }}"
                                       preload="none"></video>
                                <div class="totalpoll-embed-container" ng-if="choice.video.html" ng-bind-html="$preview.escape(choice.video.html)"></div>
                            </div>
                            <div class="totalpoll-question-choices-item-content" ng-if="choice.type === 'audio'">
                                <audio ng-if="!choice.audio.html && choice.audio.full" ng-src="{{choice.audio.full}}" controls ng-attr-poster="{{ choice.video.thumbnail }}"
                                       preload="none"></audio>
                                <div class="totalpoll-embed-container" ng-if="choice.audio.html" ng-bind-html="$preview.escape(choice.audio.html)"></div>
                            </div>
                            <div class="totalpoll-question-choices-item-content" ng-if="choice.type === 'html'" ng-bind-html="$preview.escape(choice.html)">
                            </div>
                        </div>
                        <div class="totalpoll-question-choices-item-label">
                            <div class="totalpoll-question-choices-item-label-front">
                                <span>{{ choice.label }}</span>
                                <div class="totalpoll-question-choices-item-votes">
                                    <div class="totalpoll-question-choices-item-votes-bar" ng-style="{width: $preview.getPercentageWidthFor(question, choice)}"></div>
                                    <div class="totalpoll-question-choices-item-votes-text">{{ choice.votesOverride }} Votes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>