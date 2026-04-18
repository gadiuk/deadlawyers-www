<?php $minimal = apply_filters( 'totalpoll/filters/admin/dashboard/minimal', false ); ?>
<div id="totalpoll-dashboard" class="wrap totalpoll-page" ng-app="dashboard">
    <h1 class="totalpoll-page-title"><?php esc_html_e( 'Dashboard', 'totalpoll' ); ?></h1>

    <div class="totalpoll-page-tabs">
        <div class="totalpoll-page-tabs-item active" track="{ event : 'overview', target: 'dashboard' }"
             tab-switch="dashboard>overview">
			<?php esc_html_e( 'Overview', 'totalpoll' ); ?>
        </div>
		<?php if ( ! $minimal ): ?>
            <a class="totalpoll-page-tabs-item" href="<?php echo esc_attr( $this->env['links.changelog'] ) ?>"
               target="_blank">
				<?php esc_html_e( 'What\'s new', 'totalpoll' ); ?>
            </a>
            <div class="totalpoll-page-tabs-item" tab-switch="dashboard>support">
				<?php esc_html_e( 'Support', 'totalpoll' ); ?>
            </div>
            <div class="totalpoll-page-tabs-item" tab-switch="dashboard>activation">
				<?php esc_html_e( 'Activation', 'totalpoll' ); ?>
            </div>
		<?php endif; ?>
    </div>
    <div class="totalpoll-row">
        <div class="totalpoll-column">
            <div tab="dashboard>overview" class="active">
                <dashboard-overview></dashboard-overview>
            </div>

			<?php if ( ! $minimal ): ?>
                <div tab="dashboard>get-started">
                    <dashboard-get-started></dashboard-get-started>
                </div>

                <div tab="dashboard>whats-new">
                    <dashboard-whats-new></dashboard-whats-new>
                </div>

                <div tab="dashboard>activation">
                    <dashboard-activation></dashboard-activation>
                </div>

                <div tab="dashboard>support">
                    <dashboard-support></dashboard-support>
                </div>

                <div tab="dashboard>my-account">
                    <dashboard-my-account></dashboard-my-account>
                </div>

                <dashboard-blog-feed></dashboard-blog-feed>
			<?php endif; ?>
        </div>

		<?php if ( ! $minimal ): ?>
            <div class="totalpoll-column totalpoll-column-sidebar">

                <a href="https://totalsuite.net/pricing/?utm_source=in-app&utm_medium=upgrade-box&utm_campaign=totalpoll" target="_blank" class="totalpoll-banner">
                    <img src="<?php echo esc_attr( $this->env['url'] ); ?>assets/dist/images/general/pro-version.svg"
                         alt="TotalPoll">
                </a>

                <div class="totalpoll-box totalpoll-box-subscribe">

                    <div style="display: flex; gap: 10px; align-items: center; padding: 15px; background:#fafafa;">
                        <div style="font-size: 24px;">🎁</div>
                        <div>
                            You want a <strong>20% discount</strong>?<br>Subscribe to our newsletter!
                        </div>
                    </div>

                    <div class="totalpoll-box-section">
                        <div class="totalpoll-box-title"><?php esc_html_e( 'Newsletter', 'totalpoll' ); ?></div>
                        <div class="totalpoll-box-description"><?php echo wp_kses( __( 'Get latest news about new features. You can unsubscribe anytime.',
						                                                               'totalpoll' ), [
							                                                           'a' => [
								                                                           'href'   => [],
								                                                           'target' => [],
							                                                           ],
						                                                           ] ); ?></div>
                        <a target="_blank" href="https://dashboard.mailerlite.com/forms/264929/96867416546477691/share?utm_source=in-app&utm_medium=upgrade-box&utm_campaign=totalpoll"
                           class="button button-primary button-large widefat"><?php esc_html_e( 'Subscribe',
						                                                                        'totalpoll' ); ?></a>
                    </div>
                </div>

                <div class="totalpoll-carousel" carousel>
                    <div class="totalpoll-carousel-slides" carousel-slides>
						<?php
						$products = [ 'totalcontest', 'totalrating', 'totalsurvey' ];
						shuffle( $products );

						foreach ( $products as $product ):

							$url = add_query_arg(
								[
									'utm_source'   => 'in-app',
									'utm_medium'   => 'dashboard-box',
									'utm_campaign' => 'totalpoll',
								],
								$this->env["links.{$product}"]
							);
							?>
                            <div class="totalpoll-carousel-slides-item" carousel-slides-item>
                                <a href="<?php echo esc_attr( $url ); ?>" target="_blank" class="totalpoll-banner">
                                    <img src="<?php echo esc_attr( $this->env['url'] ); ?>assets/dist/images/general/<?php echo $product; ?>-banner.svg"
                                         alt="<?php echo $product; ?>">
                                </a>
                            </div>
						<?php endforeach; ?>
                    </div>
                </div>

                <div class="totalpoll-box">
                    <div class="totalpoll-box-section">
                        <div class="totalpoll-box-title"><?php esc_html_e( 'Community Support', 'totalpoll' ); ?></div>
                        <div class="totalpoll-box-description"><?php esc_html_e( 'Jump aboard the expanding TotalSuite community for assistance!',
						                                                         'totalpoll' ); ?></div>
                        <a href="<?php echo esc_attr( $this->env['links.forums'] ); ?>" target="_blank"
                           class="button button-large"><?php esc_html_e( 'Visit Forums',
						                                                                'totalpoll' ); ?></a>
                    </div>
                </div>

                <div class="totalpoll-box">
                    <div class="totalpoll-box-section">
                        <div class="totalpoll-box-title">Remove these banners?</div>
                        <div class="totalpoll-box-description">Ditch the branding baggage with TotalPoll's white-label
                            feature.
                        </div>
                        <a href="https://totalsuite.net/pricing/?utm_source=in-app&utm_medium=whitelabel-box&utm_campaign=totalpoll"
                           target="_blank"
                           class="button button-primary widefat button-large"><?php esc_html_e( 'Upgrade to TotalPoll Business',
						                                                                        'totalpoll' ); ?></a>
                    </div>
                </div>
            </div>
		<?php endif; ?>

    </div>

    <!-- Templates -->
	<?php include __DIR__ . '/overview.php'; ?>
	<?php include __DIR__ . '/blog-feed.php'; ?>
	<?php include __DIR__ . '/get-started.php'; ?>
	<?php include __DIR__ . '/activation.php'; ?>
	<?php include __DIR__ . '/my-account.php'; ?>
	<?php include __DIR__ . '/support.php'; ?>
	<?php include __DIR__ . '/credits.php'; ?>
	<?php include __DIR__ . '/sidebar.php'; ?>
</div>
