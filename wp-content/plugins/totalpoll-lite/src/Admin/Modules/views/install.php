<script type="text/ng-template" id="modules-installer-component-template">
    <div ng-show="$ctrl.message.type" ng-class="{'updated': $ctrl.message.type === 'success', 'error': $ctrl.message.type === 'error'}">
        <p>{{$ctrl.message.content}}</p>
    </div>
    <div class="totalpoll-modules-installer" ng-class="{uploading: $ctrl.isProcessing()}" ng-init="$root.$installer = $ctrl">

        <span class="dashicons dashicons-media-archive"></span>

        <div class="totalpoll-progress" ng-show="$ctrl.isProcessing()">
            <div class="totalpoll-progress-current">
                <div ng-style="{width: $ctrl.getUploadPercentage()}"></div>
            </div>
            <div class="totalpoll-progress-percentage">{{$ctrl.getUploadPercentage()}}</div>
        </div>

        <p><?php esc_html_e( 'If you have a template or extension in .zip format, you may install it by uploading it here.', 'totalpoll' ); ?></p>

        <button type="button" class="button button-large" ng-if="!$ctrl.file"><?php esc_html_e( 'Browse', 'totalpoll' ); ?></button>
        <input type="file" ng-model="$ctrl.file" accept="application/zip" ng-disabled="$ctrl.isProcessing()" id="uploadModuleFile">
        <button class="button button-large button-primary" ng-if="$ctrl.file" ng-click="$ctrl.install()"
                ng-disabled="$ctrl.isProcessing()"><?php esc_html_e( 'Install', 'totalpoll' ); ?> {{$ctrl.file.name}}
        </button>
    </div>
</script>
