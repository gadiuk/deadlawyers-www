<div class="totalpoll-questions" ng-if="$preview.isScreen('vote')">
    <div class="totalpoll-question" ng-repeat="(questionIndex, question) in $root.settings.questions" ng-if="$preview.isQuestionVisible(questionIndex)">
        <div class="totalpoll-question-container">
            <div class="totalpoll-question-content" ng-bind-html="$preview.escape(question.content)">
            </div>

            <div class="totalpoll-question-choices">
                <label for="choice-{{choice.uid}}-selector" class="totalpoll-question-choices-item totalpoll-question-choices-item-type-{{choice.type}}"
                       ng-class="{'totalpoll-question-choices-item-checked': $preview.isChoiceSelected(question, choice)}"
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
                        <div class="totalpoll-question-choices-item-control">
                            <div class="totalpoll-question-choices-item-selector">
                                <input ng-attr-type="{{ $preview.getInputType(question) }}"
                                       ng-attr-name="totalpoll[choices][{{question.uid}}]"
                                       ng-checked="$preview.isChoiceSelected(question, choice) || null"
                                       ng-click="$preview.toggleChoice(question, choice)"
                                       id="choice-{{choice.uid}}-selector"
                                       value="{{choice.uid}}">

                                <div class="totalpoll-question-choices-item-selector-box">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#totalpoll-check-icon"></use>
                                    </svg>
                                </div>
                            </div>
                            <div class="totalpoll-question-choices-item-label">
                                <span>{{ choice.label }}</span>
                            </div>
                        </div>
                    </div>
                </label>

            </div>
        </div>
    </div>
</div>