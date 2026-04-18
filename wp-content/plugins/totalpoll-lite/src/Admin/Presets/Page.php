<?php
namespace TotalPoll\Admin\Presets;


use TotalPollVendors\TotalCore\Contracts\Foundation\Environment as EnvironmentContract;
use TotalPollVendors\TotalCore\Contracts\Http\Request as RequestContract;

class Page extends \TotalPollVendors\TotalCore\Admin\Pages\Page {

	public function __construct( RequestContract $request, EnvironmentContract $env ) {
		parent::__construct( $request, $env );
		$upgrade_to_pro = admin_url('edit.php?post_type=poll&page=upgrade-to-pro');
		wp_redirect($upgrade_to_pro);
	}

	public function render() {

	}
}