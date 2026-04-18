<div class="totalpoll-options-expressions">
    <table class="wp-list-table widefat totalpoll-options-list">
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
                <div class="tiny"
                     ng-class="{'totalpoll-processing': $ctrl.isExpressionProcessing(expression), 'totalpoll-successful': $ctrl.isExpressionSaved(expression)}">
                    <input type="text" class="widefat" ng-attr-placeholder="{{translation}}"
                           ng-model="$ctrl.expressions.user[rawExpression]['translations'][$index]" ng-model-options="{debounce: 500}" dir="auto">
                </div>
            </td>
        </tr>
        <tr ng-repeat-end></tr>
        <tr ng-repeat-end></tr>
        </tbody>
    </table>
</div>