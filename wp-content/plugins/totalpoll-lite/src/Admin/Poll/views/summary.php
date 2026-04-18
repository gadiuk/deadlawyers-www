<style>
    #totalpoll-insights, #totalpoll-insights .totalpoll-processing {
        min-height: 240px;
    }

    .totalpoll-charts {
        display: block;
        width: 100%;
    }

    .totalpoll-charts + .totalpoll-charts {
        margin-top: calc(1rem - 1px);
        padding-top: 1rem;
        border-top: 1px solid #ddd;
    }

    .totalpoll-charts-item {
        display: block;
        width: 100%;
        min-height: 240px;
    }
</style>
<div id="totalpoll-insights">
    <insights-summary></insights-summary>
    <script type="text/ng-template" id="insights-summary-component-template">
        <div ng-class="{'totalpoll-processing': $ctrl.isProcessing()}">
            <div class="totalpoll-charts" ng-repeat="(uid, question) in $ctrl.metrics.questions">
                <h4>{{ question.content }}</h4>
                <chart class="totalpoll-charts-item"
                       ng-attr-options="{{ $ctrl.metrics[uid].options }}"
                       type="doughnut"
                       ng-model="$ctrl.metrics[uid]"></chart>
            </div>
        </div>
    </script>
</div>
