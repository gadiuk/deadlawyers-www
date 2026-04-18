<div class="totalpoll-translations totalpoll-pro-badge-container" ng-controller="TranslationsCtrl as $ctrl">
    <table class="wp-list-table widefat totalpoll-translations-table">
        <thead>
            <tr>
                <th>
                    <?php esc_html_e('Original', 'totalpoll'); ?>
                </th>
                <th>
                    <select name="" class="widefat" ng-options="language as language.name for language in editor.languages" ng-model="$ctrl.language">
                        <option value="">
                            <?php esc_html_e('Select language', 'totalpoll'); ?>
                        </option>
                    </select>
                </th>
            </tr>
        </thead>
        <tbody ng-if="$ctrl.language">
            <tr ng-repeat-start="question in editor.settings.questions">
                <td colspan="2">
                    <?php esc_html_e('Question #{{$index+1}}', 'totalpoll'); ?>
                </td>
            </tr>
            <tr ng-if="question.content.length">
                <td>{{question.content}}</td>
                <td>
                    <textarea name="" rows="1" class="widefat" ng-style="{direction: $ctrl.language.direction}" ng-model="question.translations[$ctrl.language.code].content" placeholder="{{question.content}}"></textarea>
                </td>
            </tr>
            <tr ng-repeat="choice in question.choices" ng-if="choice.label.length">
                <td>{{choice.label}}</td>
                <td>
                    <input type="text" name="" class="widefat" ng-style="{direction: $ctrl.language.direction}" ng-model="choice.translations[$ctrl.language.code].label" placeholder="{{ choice.label }}">
                </td>
            </tr>
            <tr ng-repeat-end></tr>
            <tr>
                <td colspan="2">
                    <?php esc_html_e('Fields', 'totalpoll'); ?>
                </td>
            </tr>
            <tr ng-repeat="field in editor.settings.fields" ng-if="field.label.length">
                <td>{{field.label}}</td>
                <td>
                    <p>
                        <input type="text" name="" class="widefat" ng-style="{direction: $ctrl.language.direction}" ng-model="field.translations[$ctrl.language.code].label" placeholder="{{field.label}}">
                    </p>
                    <!-- options -->
                    <p ng-repeat="($key, option) in field.translations[$ctrl.language.code].options">
                        <input type="text" ng-model="field.translations[$ctrl.language.code].options[$key]" class="widefat" placeholder="{{ $ctrl.placeholders[field.uid.concat('-', $key)] }}">
                    </p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <?php esc_html_e('Content', 'totalpoll'); ?>
                </td>
            </tr>
            <tr ng-if="editor.settings.content.welcome.content.length">
                <td>{{editor.settings.content.welcome.content}}</td>
                <td>
                    <textarea name="" rows="1" class="widefat" ng-style="{direction: $ctrl.language.direction}" ng-model="editor.settings.content.welcome.translations[$ctrl.language.code].content"></textarea>
                </td>
            </tr>
            <tr ng-if="editor.settings.content.vote.above.length">
                <td>{{editor.settings.content.vote.above}}</td>
                <td>
                    <textarea name="" rows="1" class="widefat" ng-style="{direction: $ctrl.language.direction}" ng-model="editor.settings.content.vote.translations[$ctrl.language.code].above"></textarea>
                </td>
            </tr>
            <tr ng-if="editor.settings.content.vote.below.length">
                <td>{{editor.settings.content.vote.below}}</td>
                <td>
                    <textarea name="" rows="1" class="widefat" ng-style="{direction: $ctrl.language.direction}" ng-model="editor.settings.content.vote.translations[$ctrl.language.code].below"></textarea>
                </td>
            </tr>
            <tr ng-if="editor.settings.content.thankyou.content.length">
                <td>{{editor.settings.content.thankyou.content}}</td>
                <td>
                    <textarea name="" rows="1" class="widefat" ng-style="{direction: $ctrl.language.direction}" ng-model="editor.settings.content.thankyou.translations[$ctrl.language.code].content"></textarea>
                </td>
            </tr>
            <tr ng-if="editor.settings.content.results.above.length">
                <td>{{editor.settings.content.results.above}}</td>
                <td>
                    <textarea name="" rows="1" class="widefat" ng-style="{direction: $ctrl.language.direction}" ng-model="editor.settings.content.results.translations[$ctrl.language.code].above"></textarea>
                </td>
            </tr>
            <tr ng-if="editor.settings.content.results.below.length">
                <td>{{editor.settings.content.results.below}}</td>
                <td>
                    <textarea name="" rows="1" class="widefat" ng-style="{direction: $ctrl.language.direction}" ng-model="editor.settings.content.results.translations[$ctrl.language.code].below"></textarea>
                </td>
            </tr>
            <tr ng-if="editor.settings.results.message.length">
                <td>{{editor.settings.results.message}}</td>
                <td>
                    <textarea name="" rows="1" class="widefat" ng-style="{direction: $ctrl.language.direction}" ng-model="editor.settings.results.translations[$ctrl.language.code].message"></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <?php esc_html_e('SEO', 'totalpoll'); ?>
                </td>
            </tr>
            <tr ng-if="editor.settings.seo.poll.title.length">
                <td>{{editor.settings.seo.poll.title}}</td>
                <td>
                    <input type="text" name="" class="widefat" ng-style="{direction: $ctrl.language.direction}" ng-model="editor.settings.seo.poll.translations[$ctrl.language.code].title">
                </td>
            </tr>
            <tr ng-if="editor.settings.seo.poll.description.length">
                <td>{{editor.settings.seo.poll.description}}</td>
                <td>
                    <textarea name="" rows="1" class="widefat" ng-style="{direction: $ctrl.language.direction}" ng-model="editor.settings.seo.poll.translations[$ctrl.language.code].description"></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="totalpoll-translations-table-wrapper">
                    <details>
                        <summary>
                            <?php esc_html_e('User Interface', 'totalpoll'); ?>
                        </summary>
                        <table class="wp-list-table widefat totalpoll-translations-table">
                        <tbody>
                        <tr ng-repeat-start="expressionsGroup in $ctrl.expressions.original track by $index" class="totalpoll-options-list-title">
                            <td colspan="2">
                                <bdi>{{expressionsGroup.label}}</bdi>
                            </td>
                        </tr>
                        <tr ng-repeat-start="(rawExpression, expression) in expressionsGroup.expressions track by $index" ng-if="false"></tr>
                        <tr ng-repeat="translation in expression.translations track by $index" class="totalpoll-options-list-entry">
                            <td>
                                <bdi>{{translation}}</bdi>
                            </td>
                            <td>
                                <div class="tiny">
                                    <input type="text" class="widefat" ng-attr-placeholder="{{translation}}" ng-model="editor.prepareExpression($ctrl.language.code, rawExpression).translations[$index]" dir="auto">
                                </div>
                            </td>
                        </tr>
                        <tr ng-repeat-end></tr>
                        <tr ng-repeat-end></tr>
                        </tbody>
                        </table>
                    </details>
                </td>
            </tr>

        </tbody>
    </table>
    <?php TotalPoll( 'upgrade-to-pro' ); ?>
</div>
