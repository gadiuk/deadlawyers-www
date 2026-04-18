<?php
return [
	'container' => [
		'colors' => [ 'background' => '', 'border' => '', 'color' => '' ],
		'border' => [ 'width' => '', 'style' => '', 'radius' => '' ],
	],
	'question'  => [
		'padding' =>
			[
				'top'    => '',
				'right'  => '',
				'bottom' => '',
				'left'   => '',
			],
		'border'  =>
			[
				'width' => '',
				'style' => '',
			],
		'colors'  =>
			[
				'background' => '',
				'border'     => '',
				'color'      => '',
			],
		'text'    =>
			[
				'fontFamily' => 'inherit',
				'fontSize'   => 'inherit',
				'fontWeight' => 'inherit',
				'lineHeight' => 'inherit',
				'transform'  => 'inherit',
				'align'      => 'inherit',
			],
	],
	'choice'    => [
		'padding' =>
			[
				'top'    => '0.75em',
				'right'  => '0.75em',
				'bottom' => '0.75em',
				'left'   => '0.75em',
			],
		'border'  =>
			[
				'width' => '1px',
				'style' => 'solid',
			],
		'colors'  =>
			[
				'background'        => '',
				'border'            => '',
				'color'             => '',
				'backgroundHover'   => '',
				'borderHover'       => '',
				'colorHover'        => '',
				'backgroundChecked' => '',
				'borderChecked'     => '',
				'colorChecked'      => '',
			],
		'text'    =>
			[
				'fontFamily' => 'inherit',
				'fontSize'   => 'inherit',
				'fontWeight' => 'inherit',
				'lineHeight' => 'inherit',
				'transform'  => 'inherit',
				'align'      => 'inherit',
			],
	],
	'votesbar'  => [
		'padding' =>
			[
				'top'    => '',
				'right'  => '',
				'bottom' => '',
				'left'   => '',
			],
		'border'  =>
			[
				'width' => '1px',
				'style' => 'solid',
			],
		'colors'  =>
			[
				'backgroundStart' => '',
				'backgroundEnd'   => '',
				'color'           => '',
				'border'          => '',
			],
		'text'    =>
			[
				'fontFamily' => 'inherit',
				'fontSize'   => '70%',
				'fontWeight' => 'inherit',
				'lineHeight' => '1',
				'transform'  => 'uppercase',
				'align'      => 'inherit',
			],
		'size'    =>
			[
				'height' => '6px',
			],
		'effects' =>
			[
				'duration' => '1000',
			],
	],
	'form'      => [
		'padding' => [
			'top'    => '1em',
			'right'  => '',
			'bottom' => '1em',
			'left'   => '',
		],
		'border'  => [
			'width' => '1px',
			'style' => 'solid',
		],
		'colors'  => [
			'background' => 'inherit',
			'color'      => 'inherit',
		],
		'text'    => [
			'fontFamily' => 'inherit',
			'fontWeight' => 'inherit',
			'fontSize'   => 'inherit',
			'lineHeight' => 'inherit',
			'align'      => 'inherit',
			'transform'  => 'inherit',
		],
		'label'   => [
			'text'    => [
				'align'      => 'inherit',
				'transform'  => 'inherit',
				'fontSize'   => 'inherit',
				'lineHeight' => 'inherit',
				'fontFamily' => 'inherit',
				'fontWeight' => 'inherit',
			],
			'padding' => [ 'top' => '0', 'right' => '0', 'bottom' => '0.5em', 'left' => '0', ],
			'colors'  => [
				'color' => 'inherit',
			],
		],
		'input'   => [
			'text'    => [
				'align'      => 'inherit',
				'transform'  => 'inherit',
				'fontSize'   => 'inherit',
				'lineHeight' => 'inherit',
				'fontFamily' => 'inherit',
				'fontWeight' => 'inherit',
			],
			'border'  => [ 'width' => '1px', 'style' => 'solid', 'radius' => '' ],
			'padding' => [ 'top' => '0.5em', 'right' => '0.5em', 'bottom' => '0.5em', 'left' => '0.5em', ],
			'colors'  => [
				'color'            => '',
				'background'       => '#ffffff',
				'border'           => '',
				'colorHover'       => '',
				'backgroundHover'  => '#ffffff',
				'borderHover'      => '',
				'colorActive'      => '',
				'backgroundActive' => '',
				'borderActive'     => '',
			],
			'shadows' => [
				'box' => 'inset 0 3px 0 rgba(0, 0, 0, 0.05)',
			],
		],
		'error'   => [
			'text'    => [
				'align'      => 'inherit',
				'transform'  => 'inherit',
				'fontSize'   => 'inherit',
				'lineHeight' => 'inherit',
				'fontFamily' => 'inherit',
				'fontWeight' => 'inherit',
			],
			'padding' => [ 'top' => '0.5em', 'right' => '0', 'bottom' => '0', 'left' => '0', ],
			'colors'  => [
				'background' => 'transparent',
				'color'      => '#f44336',
			],
		],
	],
	'message'   => [
		'padding' =>
			[
				'top'    => '1em',
				'right'  => '1em',
				'bottom' => '1em',
				'left'   => '1em',
			],
		'border'  =>
			[
				'width' => '1px',
				'style' => 'solid',
			],
		'colors'  =>
			[
				'background'      => '',
				'border'          => '',
				'color'           => 'inherit',
				'backgroundError' => '#FB8C00',
				'borderError'     => '#F57C00',
				'colorError'      => '#FFFFFF',
			],
		'text'    =>
			[
				'fontFamily' => 'inherit',
				'fontSize'   => 'inherit',
				'fontWeight' => 'inherit',
				'lineHeight' => 'inherit',
				'transform'  => 'inherit',
				'align'      => 'inherit',
			],
	],
	'button'    => [
		'colors'  => [
			'background'             => '',
			'color'                  => '',
			'border'                 => '',
			'backgroundHover'        => '',
			'colorHover'             => '',
			'borderHover'            => '',
			'backgroundPrimary'      => '',
			'colorPrimary'           => '',
			'borderPrimary'          => '',
			'backgroundPrimaryHover' => '',
			'colorPrimaryHover'      => '',
			'borderPrimaryHover'     => '',
		],
		'text'    => [
			'fontFamily' => 'inherit',
			'fontWeight' => 'inherit',
			'fontSize'   => 'inherit',
			'lineHeight' => '1',
			'align'      => 'center',
			'transform'  => 'inherit',
		],
		'border'  => [ 'width' => '1px', 'style' => 'solid', 'radius' => '' ],
		'padding' => [ 'top' => '1em', 'right' => '1em', 'left' => '1em', 'bottom' => '1em' ],
		'shadows' => [
			'box' => '',
		],
	],
];