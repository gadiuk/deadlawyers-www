<script type="text/ng-template" id="dashboard-blog-feed-component-template">
    <div class="totalpoll-row">
        <div class="totalpoll-column">
            <div class="totalpoll-box totalpoll-blog">
                <div class="totalpoll-box-section">
                    <div class="totalpoll-box-title"><?php esc_html_e( 'Picks from our blog', 'totalpoll' ); ?></div>
                </div>
                <div class="totalpoll-box-links">
                    <a class="totalpoll-box-links-item" href="{{ post.url }}" target="_blank" title="{{post.title}}"
                       ng-repeat="post in $ctrl.posts">
                        <div>
                            <h4 class="totalpoll-box-links-item-title">{{ post.title }}</h4>
                            <p class="totalpoll-box-links-item-description">{{ post.excerpt }}</p>
                        </div>
                        <img ng-src="{{ post.thumbnail }}" alt="{{ post.title }}">
                    </a>
                </div>
            </div>
        </div>
    </div>
</script>
