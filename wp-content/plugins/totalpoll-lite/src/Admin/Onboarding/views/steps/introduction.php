<script type="text/ng-template" id="introduction-component-template">
    <h2 class="title"><?php echo $this->getContent('start.title') ?></h2>
    <p class="lead"><?php echo $this->getContent('start.description') ?></p>

    <div class="row equal-height">
	    <?php foreach ($this->getContent('start.posts', []) as $post): ?>
        <div class="col-md-4">
            <article class="card onboarding-card">
                <a href="<?php echo esc_attr($post['url']) ?>" target="_blank" class="link"></a>
                <header class="header">
                    <i class="material-icons icon">open_in_new</i>
                    <img class="thumbnail" src="<?php echo esc_attr($post['thumbnail']) ?>" />
                </header>
                <div class="body">
                    <h4 class="title"><?php echo esc_attr($post['title']) ?></h4>
                    <p class="description"><?php echo esc_attr($post['description']) ?></p>
                </div>
            </article>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="more-box">
        <img src="<?php echo esc_attr( $this->env['url'] ); ?>assets/dist/images/onboarding/reading.svg" alt="Reading">

        <h3 class="title">Looking for more?</h3>
        <p class="description">Visit our knowledge base and learn more about TotalPoll.</p>
        <a href="https://totalsuite.net/product/totalpoll/documentation/" target="_blank"
           class="button -primary">Browse TotalSuite.net
        </a>
    </div>
</script>