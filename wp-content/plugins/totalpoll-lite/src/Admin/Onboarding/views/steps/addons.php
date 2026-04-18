<script  type="text/ng-template" id="addons-component-template">
        <h2 class="title"><?php echo $this->getContent('addons.title') ?></h2>
        <p class="lead"><?php echo $this->getContent('addons.description') ?></p>
        <ng-template #addons>
            <div class="row">
                <div class="col-md-6" ng-repeat="extension in $ctrl.FeaturedModules">
                    <div class="card addon-card">
                        <a class="link" ng-href="{{ extension.permalink }}" target="_blank"></a>
                        <div class="header">
                            <i class="material-icons icon">launch</i>
                            <img ng-src="{{ extension.images.icon }}" class="thumbnail">
                        </div>
                        <div class="body">
                            <h4 class="title">{{ extension.name }}</h4>
                            <p class="description">{{ extension.description}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </ng-template>
        <br><br>
        <aside class="more-box">
            <h4>Looking for more?</h4>
            <p>Explore more extensions on totalsuite.net and supercharge your debut.</p>
            <a href="https://totalsuite.net/product/totalpoll/add-ons/" target="_blank" class="button -primary">Browse Store</a>
            <img src="<?php echo esc_attr( $this->env['url'] ); ?>assets/dist/images/onboarding/more.svg" alt="More">
        </aside>
</script>