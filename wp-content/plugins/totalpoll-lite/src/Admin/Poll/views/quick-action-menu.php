<span class="totalpoll-quick-action">
<a href="#" role="button"><?php echo $primaryItem; ?></a>
<span class="totalpoll-quick-action-menu">
    <span class="totalpoll-quick-action-menu-items">
	<?php foreach ( $items as $item ): ?>
		<a href="<?php echo esc_attr( $item['url'] ); ?>" class="totalpoll-quick-action-menu-item"><?php echo $item['label']; ?></a>
	<?php endforeach; ?>
    </span>
</span>
</span>
