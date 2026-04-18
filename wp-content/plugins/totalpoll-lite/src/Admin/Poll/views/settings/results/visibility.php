<?php
/**
 * @var \TotalPoll\Admin\Poll\Editor $this
 */

?>
<div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label>
            <input type="radio" name=""
                   value="all"
                   ng-model="editor.settings.results.visibility"
                   ng-checked="editor.settings.results.visibility">
            <?php
            esc_html_e('Show results for voters and non-voters', 'totalpoll'); ?>
        </label>
    </div>
    <div class="totalpoll-settings-field">
        <label>
            <input type="radio" name=""
                   value="voters"
                   ng-model="editor.settings.results.visibility"
                   ng-checked="editor.settings.results.visibility">
            <?php
            esc_html_e('Show results for voters only', 'totalpoll'); ?>
        </label>
    </div>
    <div class="totalpoll-settings-field">
        <label>
            <input type="radio" name=""
                   value="none"
                   ng-model="editor.settings.results.visibility"
                   ng-checked="editor.settings.results.visibility" disabled>
            <?php
            esc_html_e('Hide results', 'totalpoll'); ?>
            <?php TotalPoll( 'upgrade-to-pro' ); ?>
        </label>
    </div>
</div>

<div class="totalpoll-settings-item" ng-if="editor.settings.results.visibility === 'none'">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
            <?php
            esc_html_e('Hidden results message', 'totalpoll'); ?>
        </label>

        <progressive-textarea ng-model="editor.settings.results.message" uid="results"></progressive-textarea>
    </div>

    <div class="totalpoll-settings-field">
        <label ng-if="editor.settings.vote.limitations.quota.value">
            <input type="checkbox" name=""
                   ng-model="editor.settings.results.untilReaching.quota"
                   ng-checked="editor.settings.results.untilReaching.quota">
            <?php
            esc_html_e('Until quota is reached', 'totalpoll'); ?>
        </label>
        &nbsp;&nbsp;
        <label ng-if="editor.settings.vote.limitations.period.end">
            <input type="checkbox" name=""
                   ng-model="editor.settings.results.untilReaching.endDate"
                   ng-checked="editor.settings.results.untilReaching.endDate">
            <?php
            esc_html_e('Until end date is reached', 'totalpoll'); ?>
        </label>
        <div class="totalpoll-settings-item" ng-include="'votes-template-variables'"></div>
    </div>
</div>
