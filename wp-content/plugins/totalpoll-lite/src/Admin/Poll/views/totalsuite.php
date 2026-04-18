<?php
$product = [ 'totalcontest', 'totalrating', 'totalsurvey' ][ mt_rand( 0, 2 ) ];

$url = add_query_arg(
	[
		'utm_source'   => 'in-app',
		'utm_medium'   => 'poll-editor-box',
		'utm_campaign' => 'totalpoll',
	],
	$this->env["links.{$product}"]
);
?>
<div class="totalpoll-banner">
    <style type="text/css">
        .totalpoll-banner {
            display: block;
            margin-bottom: 20px;
        }
    </style>

    <a href="<?php echo esc_attr( $url ); ?>" target="_blank">
        <img src="<?php echo esc_attr( $this->env['url'] ); ?>assets/dist/images/general/<?php echo $product; ?>-banner.svg" alt="<?php echo $product; ?>">
    </a>
</div>

<script type="text/javascript">
    var banner = document.querySelector('.totalpoll-banner');
    banner.parentElement.parentElement.parentElement.append(banner);
    banner.previousElementSibling.remove();
    banner.after(banner);
</script>
