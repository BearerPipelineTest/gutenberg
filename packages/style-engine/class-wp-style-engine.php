<?php
/**
 * WP_Style_Engine
 *
 * Generates classnames and block styles.
 *
 * @package Gutenberg
 */

if ( class_exists( 'WP_Style_Engine' ) ) {
	return;
}

/**
 * Singleton class representing the style engine.
 *
 * Consolidates rendering block styles to reduce duplication and streamline
 * CSS styles generation.
 *
 * This class is for internal core usage and is not supposed to be used by extenders (plugins and/or themes).
 * This is a low-level API that may need to do breaking changes. Please, use wp_style_engine_get_styles instead.
 *
 * @access private
 */
class WP_Style_Engine {
	/**
	 * Style definitions that contain the instructions to
	 * parse/output valid Gutenberg styles from a block's attributes.
	 * For every style definition, the follow properties are valid:
	 *  - classnames    => (array) an array of classnames to be returned for block styles. The key is a classname or pattern.
	 *                    A value of `true` means the classname should be applied always. Otherwise, a valid CSS property (string)
	 *                    to match the incoming value, e.g., "color" to match var:preset|color|somePresetSlug.
	 *  - css_vars      => (array) an array of key value pairs used to generate CSS var values. The key is a CSS var pattern, whose `$slug` fragment will be replaced with a preset slug.
	 *                    The value should be a valid CSS property (string) to match the incoming value, e.g., "color" to match var:preset|color|somePresetSlug.
	 *  - property_keys => (array) array of keys whose values represent a valid CSS property, e.g., "margin" or "border".
	 *  - path          => (array) a path that accesses the corresponding style value in the block style object.
	 *  - value_func    => (string) the name of a function to generate a CSS definition array for a particular style object. The output of this function should be `array( "$property" => "$value", ... )`.
	 */
	const BLOCK_STYLE_DEFINITIONS_METADATA = array(
		'color'      => array(
			'text'       => array(
				'property_keys' => array(
					'default' => 'color',
				),
				'path'          => array( 'color', 'text' ),
				'css_vars'      => array(
					'color' => '--wp--preset--color--$slug',
				),
				'classnames'    => array(
					'has-text-color'  => true,
					'has-$slug-color' => 'color',
				),
			),
			'background' => array(
				'property_keys' => array(
					'default' => 'background-color',
				),
				'path'          => array( 'color', 'background' ),
				'classnames'    => array(
					'has-background'             => true,
					'has-$slug-background-color' => 'color',
				),
			),
			'gradient'   => array(
				'property_keys' => array(
					'default' => 'background',
				),
				'path'          => array( 'color', 'gradient' ),
				'classnames'    => array(
					'has-background'                => true,
					'has-$slug-gradient-background' => 'gradient',
				),
			),
		),
		'border'     => array(
			'color'  => array(
				'property_keys' => array(
					'default'    => 'border-color',
					'individual' => 'border-%s-color',
				),
				'path'          => array( 'border', 'color' ),
				'classnames'    => array(
					'has-border-color'       => true,
					'has-$slug-border-color' => 'color',
				),
			),
			'radius' => array(
				'property_keys' => array(
					'default'    => 'border-radius',
					'individual' => 'border-%s-radius',
				),
				'path'          => array( 'border', 'radius' ),
			),
			'style'  => array(
				'property_keys' => array(
					'default'    => 'border-style',
					'individual' => 'border-%s-style',
				),
				'path'          => array( 'border', 'style' ),
			),
			'width'  => array(
				'property_keys' => array(
					'default'    => 'border-width',
					'individual' => 'border-%s-width',
				),
				'path'          => array( 'border', 'width' ),
			),
			'top'    => array(
				'value_func' => 'static::get_individual_property_css_declarations',
				'path'       => array( 'border', 'top' ),
				'css_vars'   => array(
					'color' => '--wp--preset--color--$slug',
				),
			),
			'right'  => array(
				'value_func' => 'static::get_individual_property_css_declarations',
				'path'       => array( 'border', 'right' ),
				'css_vars'   => array(
					'color' => '--wp--preset--color--$slug',
				),
			),
			'bottom' => array(
				'value_func' => 'static::get_individual_property_css_declarations',
				'path'       => array( 'border', 'bottom' ),
				'css_vars'   => array(
					'color' => '--wp--preset--color--$slug',
				),
			),
			'left'   => array(
				'value_func' => 'static::get_individual_property_css_declarations',
				'path'       => array( 'border', 'left' ),
				'css_vars'   => array(
					'color' => '--wp--preset--color--$slug',
				),
			),
		),
		'spacing'    => array(
			'padding' => array(
				'property_keys' => array(
					'default'    => 'padding',
					'individual' => 'padding-%s',
				),
				'path'          => array( 'spacing', 'padding' ),
				'css_vars'      => array(
					'spacing' => '--wp--preset--spacing--$slug',
				),
			),
			'margin'  => array(
				'property_keys' => array(
					'default'    => 'margin',
					'individual' => 'margin-%s',
				),
				'path'          => array( 'spacing', 'margin' ),
				'css_vars'      => array(
					'spacing' => '--wp--preset--spacing--$slug',
				),
			),
		),
		'typography' => array(
			'fontSize'       => array(
				'property_keys' => array(
					'default' => 'font-size',
				),
				'path'          => array( 'typography', 'fontSize' ),
				'classnames'    => array(
					'has-$slug-font-size' => 'font-size',
				),
			),
			'fontFamily'     => array(
				'property_keys' => array(
					'default' => 'font-family',
				),
				'path'          => array( 'typography', 'fontFamily' ),
				'classnames'    => array(
					'has-$slug-font-family' => 'font-family',
				),
			),
			'fontStyle'      => array(
				'property_keys' => array(
					'default' => 'font-style',
				),
				'path'          => array( 'typography', 'fontStyle' ),
			),
			'fontWeight'     => array(
				'property_keys' => array(
					'default' => 'font-weight',
				),
				'path'          => array( 'typography', 'fontWeight' ),
			),
			'lineHeight'     => array(
				'property_keys' => array(
					'default' => 'line-height',
				),
				'path'          => array( 'typography', 'lineHeight' ),
			),
			'textDecoration' => array(
				'property_keys' => array(
					'default' => 'text-decoration',
				),
				'path'          => array( 'typography', 'textDecoration' ),
			),
			'textTransform'  => array(
				'property_keys' => array(
					'default' => 'text-transform',
				),
				'path'          => array( 'typography', 'textTransform' ),
			),
			'letterSpacing'  => array(
				'property_keys' => array(
					'default' => 'letter-spacing',
				),
				'path'          => array( 'typography', 'letterSpacing' ),
			),
		),
	);

	/**
	 * Util: Extracts the slug in kebab case from a preset string, e.g., "heavenly-blue" from 'var:preset|color|heavenlyBlue'.
	 *
	 * @param string? $style_value  A single css preset value.
	 * @param string  $property_key The CSS property that is the second element of the preset string. Used for matching.
	 *
	 * @return string The slug, or empty string if not found.
	 */
	protected static function get_slug_from_preset_value( $style_value, $property_key ) {
		if ( is_string( $style_value ) && str_contains( $style_value, "var:preset|{$property_key}|" ) ) {
			$index_to_splice = strrpos( $style_value, '|' ) + 1;
			return _wp_to_kebab_case( substr( $style_value, $index_to_splice ) );
		}
		return '';
	}

	/**
	 * Util: Generates a css var string, eg var(--wp--preset--color--background) from a preset string, eg. `var:preset|space|50`.
	 *
	 * @param string $style_value  A single css preset value.
	 * @param array  $css_vars The css var patterns used to generate the var string.
	 *
	 * @return string The css var, or an empty string if no match for slug found.
	 */
	protected static function get_css_var_value( $style_value, $css_vars ) {
		foreach ( $css_vars as  $property_key => $css_var_pattern ) {
			$slug = static::get_slug_from_preset_value( $style_value, $property_key );
			if ( static::is_valid_style_value( $slug ) ) {
				$var = strtr(
					$css_var_pattern,
					array( '$slug' => $slug )
				);
				return "var($var)";
			}
		}
		return '';
	}

	/**
	 * Util: Checks whether an incoming block style value is valid.
	 *
	 * @param string? $style_value  A single css preset value.
	 *
	 * @return boolean
	 */
	protected static function is_valid_style_value( $style_value ) {
		return '0' === $style_value || ! empty( $style_value );
	}

	/**
	 * Stores a CSS rule using the provided CSS selector and CSS declarations.
	 *
	 * @param string $store_name       A valid store key.
	 * @param string $css_selector     When a selector is passed, the function will return a full CSS rule `$selector { ...rules }`, otherwise a concatenated string of properties and values.
	 * @param array  $css_declarations An array of parsed CSS property => CSS value pairs.
	 *
	 * @return void.
	 */
	public static function store_css_rule( $store_name, $css_selector, $css_declarations ) {
		if ( empty( $store_name ) || empty( $css_selector ) || empty( $css_declarations ) ) {
			return;
		}
		static::get_store( $store_name )->add_rule( $css_selector )->add_declarations( $css_declarations );
	}

	/**
	 * Returns a store by store key.
	 *
	 * @param string $store_name A store key.
	 *
	 * @return WP_Style_Engine_CSS_Rules_Store
	 */
	public static function get_store( $store_name ) {
		return WP_Style_Engine_CSS_Rules_Store::get_store( $store_name );
	}

	/**
	 * Returns classnames and CSS based on the values in a styles object.
	 * Return values are parsed based on the instructions in BLOCK_STYLE_DEFINITIONS_METADATA.
	 *
	 * @param array $block_styles The style object.
	 * @param array $options      array(
	 *     'selector'                   => (string) When a selector is passed, `generate()` will return a full CSS rule `$selector { ...rules }`, otherwise a concatenated string of properties and values.
	 *     'convert_vars_to_classnames' => (boolean) Whether to skip converting CSS var:? values to var( --wp--preset--* ) values. Default is `false`.
	 * );.
	 *
	 * @return array array(
	 *     'declarations' => (array) An array of parsed CSS property => CSS value pairs.
	 *     'classnames'   => (array) A flat array of classnames.
	 * );
	 */
	public static function parse_block_styles( $block_styles, $options ) {
		$parsed_styles = array(
			'classnames'   => array(),
			'declarations' => array(),
		);
		if ( empty( $block_styles ) || ! is_array( $block_styles ) ) {
			return $parsed_styles;
		}

		// Collect CSS and classnames.
		foreach ( static::BLOCK_STYLE_DEFINITIONS_METADATA as $definition_group_key => $definition_group_style ) {
			if ( empty( $block_styles[ $definition_group_key ] ) ) {
				continue;
			}
			foreach ( $definition_group_style as $style_definition ) {
				$style_value = _wp_array_get( $block_styles, $style_definition['path'], null );

				if ( ! static::is_valid_style_value( $style_value ) ) {
					continue;
				}

				$parsed_styles['classnames']   = array_merge( $parsed_styles['classnames'], static::get_classnames( $style_value, $style_definition ) );
				$parsed_styles['declarations'] = array_merge( $parsed_styles['declarations'], static::get_css_declarations( $style_value, $style_definition, $options ) );
			}
		}

		return $parsed_styles;
	}

	/**
	 * Returns classnames, and generates classname(s) from a CSS preset property pattern, e.g., 'var:preset|color|heavenly-blue'.
	 *
	 * @param array         $style_value      A single raw style value or css preset property from the generate() $block_styles array.
	 * @param array<string> $style_definition A single style definition from BLOCK_STYLE_DEFINITIONS_METADATA.
	 *
	 * @return array        An array of CSS classnames.
	 */
	protected static function get_classnames( $style_value, $style_definition ) {
		if ( empty( $style_value ) ) {
			return array();
		}

		$classnames = array();
		if ( ! empty( $style_definition['classnames'] ) ) {
			foreach ( $style_definition['classnames'] as $classname => $property_key ) {
				if ( true === $property_key ) {
					$classnames[] = $classname;
				}

				$slug = static::get_slug_from_preset_value( $style_value, $property_key );

				if ( $slug ) {
					// Right now we expect a classname pattern to be stored in BLOCK_STYLE_DEFINITIONS_METADATA.
					// One day, if there are no stored schemata, we could allow custom patterns or
					// generate classnames based on other properties
					// such as a path or a value or a prefix passed in options.
					$classnames[] = strtr( $classname, array( '$slug' => $slug ) );
				}
			}
		}

		return $classnames;
	}

	/**
	 * Returns an array of CSS declarations based on valid block style values.
	 *
	 * @param array         $style_value          A single raw style value from the generate() $block_styles array.
	 * @param array<string> $style_definition     A single style definition from BLOCK_STYLE_DEFINITIONS_METADATA.
	 * @param array         $options              array(
	 *     'convert_vars_to_classnames' => (boolean) Whether to skip converting CSS var:? values to var( --wp--preset--* ) values. Default is `false`.
	 * );.
	 *
	 * @return array        An array of CSS definitions, e.g., array( "$property" => "$value" ).
	 */
	protected static function get_css_declarations( $style_value, $style_definition, $options ) {
		if ( isset( $style_definition['value_func'] ) && is_callable( $style_definition['value_func'] ) ) {
			return call_user_func( $style_definition['value_func'], $style_value, $style_definition, $options );
		}

		$css_declarations     = array();
		$style_property_keys  = $style_definition['property_keys'];
		$should_skip_css_vars = isset( $options['convert_vars_to_classnames'] ) && true === $options['convert_vars_to_classnames'];

		// Build CSS var values from var:? values, e.g, `var(--wp--css--rule-slug )`
		// Check if the value is a CSS preset and there's a corresponding css_var pattern in the style definition.
		if ( is_string( $style_value ) && str_contains( $style_value, 'var:' ) ) {
			if ( ! $should_skip_css_vars && ! empty( $style_definition['css_vars'] ) ) {
				$css_var = static::get_css_var_value( $style_value, $style_definition['css_vars'] );
				if ( static::is_valid_style_value( $css_var ) ) {
					$css_declarations[ $style_property_keys['default'] ] = $css_var;
				}
			}
			return $css_declarations;
		}

		// Default rule builder.
		// If the input contains an array, assume box model-like properties
		// for styles such as margins and padding.
		if ( is_array( $style_value ) ) {
			// Bail out early if the `'individual'` property is not defined.
			if ( ! isset( $style_property_keys['individual'] ) ) {
				return $css_declarations;
			}

			foreach ( $style_value as $key => $value ) {
				if ( is_string( $value ) && str_contains( $value, 'var:' ) && ! $should_skip_css_vars && ! empty( $style_definition['css_vars'] ) ) {
					$value = static::get_css_var_value( $value, $style_definition['css_vars'] );
				}

				$individual_property = sprintf( $style_property_keys['individual'], _wp_to_kebab_case( $key ) );

				if ( $individual_property && static::is_valid_style_value( $value ) ) {
					$css_declarations[ $individual_property ] = $value;
				}
			}

			return $css_declarations;
		}

		$css_declarations[ $style_property_keys['default'] ] = $style_value;
		return $css_declarations;
	}

	/**
	 * Style value parser that returns a CSS definition array comprising style properties
	 * that have keys representing individual style properties, otherwise known as longhand CSS properties.
	 * e.g., "$style_property-$individual_feature: $value;", which could represent the following:
	 * "border-{top|right|bottom|left}-{color|width|style}: {value};" or,
	 * "border-image-{outset|source|width|repeat|slice}: {value};"
	 *
	 * @param array $style_value                    A single raw Gutenberg style attributes value for a CSS property.
	 * @param array $individual_property_definition A single style definition from BLOCK_STYLE_DEFINITIONS_METADATA.
	 * @param array $options                        array(
	 *     'convert_vars_to_classnames' => (boolean) Whether to skip converting CSS var:? values to var( --wp--preset--* ) values. Default is `false`.
	 * );.
	 *
	 * @return array An array of CSS definitions, e.g., array( "$property" => "$value" ).
	 */
	protected static function get_individual_property_css_declarations( $style_value, $individual_property_definition, $options ) {
		if ( ! is_array( $style_value ) || empty( $style_value ) || empty( $individual_property_definition['path'] ) ) {
			return array();
		}

		// The first item in $individual_property_definition['path'] array tells us the style property, e.g., "border".
		// We use this to get a corresponding CSS style definition such as "color" or "width" from the same group.
		// The second item in $individual_property_definition['path'] array refers to the individual property marker, e.g., "top".
		$definition_group_key    = $individual_property_definition['path'][0];
		$individual_property_key = $individual_property_definition['path'][1];
		$should_skip_css_vars    = isset( $options['convert_vars_to_classnames'] ) && true === $options['convert_vars_to_classnames'];
		$css_declarations        = array();

		foreach ( $style_value as $css_property => $value ) {
			if ( empty( $value ) ) {
				continue;
			}

			// Build a path to the individual rules in definitions.
			$style_definition_path = array( $definition_group_key, $css_property );
			$style_definition      = _wp_array_get( static::BLOCK_STYLE_DEFINITIONS_METADATA, $style_definition_path, null );

			if ( $style_definition && isset( $style_definition['property_keys']['individual'] ) ) {
				// Set a CSS var if there is a valid preset value.
				if ( is_string( $value ) && str_contains( $value, 'var:' ) && ! $should_skip_css_vars && ! empty( $individual_property_definition['css_vars'] ) ) {
					$value = static::get_css_var_value( $value, $individual_property_definition['css_vars'] );
				}
				$individual_css_property                      = sprintf( $style_definition['property_keys']['individual'], $individual_property_key );
				$css_declarations[ $individual_css_property ] = $value;
			}
		}
		return $css_declarations;
	}

	/**
	 * Returns compiled CSS from css_declarations.
	 *
	 * @param array  $css_declarations An array of parsed CSS property => CSS value pairs.
	 * @param string $css_selector     When a selector is passed, the function will return a full CSS rule `$selector { ...rules }`, otherwise a concatenated string of properties and values.
	 *
	 * @return string A compiled CSS string.
	 */
	public static function compile_css( $css_declarations, $css_selector ) {
		if ( empty( $css_declarations ) || ! is_array( $css_declarations ) ) {
			return '';
		}

		// Return an entire rule if there is a selector.
		if ( $css_selector ) {
			$css_rule = new WP_Style_Engine_CSS_Rule( $css_selector, $css_declarations );
			return $css_rule->get_css();
		}

		$css_declarations = new WP_Style_Engine_CSS_Declarations( $css_declarations );
		return $css_declarations->get_declarations_string();
	}

	/**
	 * Returns a compiled stylesheet from stored CSS rules.
	 *
	 * @param WP_Style_Engine_CSS_Rule[] $css_rules An array of WP_Style_Engine_CSS_Rule objects from a store or otherwise.
	 *
	 * @return string A compiled stylesheet from stored CSS rules.
	 */
	public static function compile_stylesheet_from_css_rules( $css_rules ) {
		$processor = new WP_Style_Engine_Processor();
		$processor->add_rules( $css_rules );
		return $processor->get_css();
	}
}

/**
 * Global public interface method to generate styles from a single style object, e.g.,
 * the value of a block's attributes.style object or the top level styles in theme.json.
 * See: https://developer.wordpress.org/block-editor/reference-guides/theme-json-reference/theme-json-living/#styles and
 * https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/
 *
 * Example usage:
 *
 * $styles = wp_style_engine_get_styles( array( 'color' => array( 'text' => '#cccccc' ) ) );
 * // Returns `array( 'css' => 'color: #cccccc', 'declarations' => array( 'color' => '#cccccc' ), 'classnames' => 'has-color' )`.
 *
 * @access public
 *
 * @param array                 $block_styles The style object.
 * @param array<string|boolean> $options      array(
 *     'context' => (string|null) An identifier describing the origin of the style object, e.g., 'block-supports' or 'global-styles'. Default is 'block-supports'.
 *                  When set, the style engine will attempt to store the CSS rules, where a selector is also passed.
 *     'convert_vars_to_classnames' => (boolean) Whether to skip converting CSS var:? values to var( --wp--preset--* ) values. Default is `false`.
 *     'selector'                   => (string) When a selector is passed, `generate()` will return a full CSS rule `$selector { ...rules }`, otherwise a concatenated string of properties and values.
 * );.
 *
 * @return array<string|array> array(
 *     'css'          => (string) A CSS ruleset or declarations block formatted to be placed in an HTML `style` attribute or tag.
 *     'declarations' => (array) An array of property/value pairs representing parsed CSS declarations.
 *     'classnames'   => (string) Classnames separated by a space.
 * );
 */
function wp_style_engine_get_styles( $block_styles, $options = array() ) {
	if ( ! class_exists( 'WP_Style_Engine' ) ) {
		return array();
	}

	$options = wp_parse_args(
		$options,
		array(
			'selector'                   => null,
			'context'                    => null,
			'convert_vars_to_classnames' => false,
		)
	);

	$parsed_styles = WP_Style_Engine::parse_block_styles( $block_styles, $options );

	// Output.
	$styles_output = array();

	if ( ! empty( $parsed_styles['declarations'] ) ) {
		$styles_output['css']          = WP_Style_Engine::compile_css( $parsed_styles['declarations'], $options['selector'] );
		$styles_output['declarations'] = $parsed_styles['declarations'];
		if ( ! empty( $options['context'] ) ) {
			WP_Style_Engine::store_css_rule( $options['context'], $options['selector'], $parsed_styles['declarations'] );
		}
	}

	if ( ! empty( $parsed_styles['classnames'] ) ) {
		$styles_output['classnames'] = implode( ' ', array_unique( $parsed_styles['classnames'] ) );
	}

	return array_filter( $styles_output );
}

/**
 * Returns compiled CSS from a collection of selectors and declarations.
 * This won't add to any store, but is useful for returning a compiled style sheet from any CSS selector + declarations combos.
 *
 * @access public
 *
 * @param array<array>  $css_rules array(
 *      array(
 *          'selector'    => (string) A CSS selector.
 *          declarations' => (boolean) An array of CSS definitions, e.g., array( "$property" => "$value" ).
 *      )
 *  );.
 * @param array<string> $options array(
 *     'context' => (string|null) An identifier describing the origin of the style object, e.g., 'block-supports' or 'global-styles'. Default is 'block-supports'.
 *                  When set, the style engine will attempt to store the CSS rules.
 * );.
 *
 * @return string A compiled CSS string.
 */
function wp_style_engine_get_stylesheet_from_css_rules( $css_rules, $options = array() ) {
	if ( ! class_exists( 'WP_Style_Engine' ) || empty( $css_rules ) ) {
		return '';
	}

	$options = wp_parse_args(
		$options,
		array(
			'context' => null,
		)
	);

	$css_rule_objects = array();
	foreach ( $css_rules as $css_rule ) {
		if ( empty( $css_rule['selector'] ) || empty( $css_rule['declarations'] ) || ! is_array( $css_rule['declarations'] ) ) {
			continue;
		}

		if ( ! empty( $options['context'] ) ) {
			WP_Style_Engine::store_css_rule( $options['context'], $css_rule['selector'], $css_rule['declarations'] );
		}

		$css_rule_objects[] = new WP_Style_Engine_CSS_Rule( $css_rule['selector'], $css_rule['declarations'] );
	}

	if ( empty( $css_rule_objects ) ) {
		return '';
	}

	return WP_Style_Engine::compile_stylesheet_from_css_rules( $css_rule_objects );
}

/**
 * Returns compiled CSS from a store, if found.
 *
 * @access public
 *
 * @param string $store_name A valid store name.
 *
 * @return string A compiled CSS string.
 */
function wp_style_engine_get_stylesheet_from_context( $store_name ) {
	if ( ! class_exists( 'WP_Style_Engine' ) || empty( $store_name ) ) {
		return '';
	}

	return WP_Style_Engine::compile_stylesheet_from_css_rules( WP_Style_Engine::get_store( $store_name )->get_all_rules() );
}
