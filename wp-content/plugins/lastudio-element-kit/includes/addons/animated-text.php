<?php

/**
 * Class: LaStudioKit_Animated_Text
 * Name: Animated Text
 * Slug: lakit-animated-text
 */

namespace Elementor;

if (!defined('WPINC')) {
    die;
}

/**
 * LaStudioKit_Animated_Text Widget
 */
class LaStudioKit_Animated_Text extends LaStudioKit_Base {

    protected function enqueue_addon_resources(){

	    if(!lastudio_kit_settings()->is_combine_js_css()){
		    wp_register_script( 'lastudio-kit-anime-js', lastudio_kit()->plugin_url('assets/js/lib/anime.min.js'), [], lastudio_kit()->get_version(), true);
		    wp_register_script( $this->get_name(), lastudio_kit()->plugin_url('assets/js/addons/animated-text.js'), ['lastudio-kit-anime-js'], lastudio_kit()->get_version(), true);
		    $this->add_script_depends( $this->get_name() );
		    if(!lastudio_kit()->is_optimized_css_mode()) {
			    wp_register_style( $this->get_name(), lastudio_kit()->plugin_url('assets/css/addons/animated-text.min.css'), ['lastudio-kit-base'], lastudio_kit()->get_version());
			    $this->add_style_depends( $this->get_name() );
		    }
	    }
	    else{
	    	wp_enqueue_script('lastudio-kit-anime-js');
	    }

    }

	public function get_widget_css_config($widget_name){
		$file_url = lastudio_kit()->plugin_url(  'assets/css/addons/animated-text.min.css' );
		$file_path = lastudio_kit()->plugin_path( 'assets/css/addons/animated-text.min.css' );
		return [
			'key' => $widget_name,
			'version' => lastudio_kit()->get_version(true),
			'file_path' => $file_path,
			'data' => [
				'file_url' => $file_url
			]
		];
	}


	public function get_name() {
        return 'lakit-animated-text';
    }

    protected function get_widget_title() {
        return esc_html__( 'Animated Text', 'lastudio-kit');
    }

    public function get_icon() {
        return 'lastudio-kit-icon-animated-text';
    }

    protected function register_controls() {

        $css_scheme = apply_filters(
            'lastudio-kit/animated-text/css-scheme',
            array(
                'animated_text_instance' => '.lakit-animated-text',
                'before_text'            => '.lakit-animated-text__before-text',
                'animated_text'          => '.lakit-animated-text__animated-text',
                'animated_text_item'     => '.lakit-animated-text__animated-text-item',
                'after_text'             => '.lakit-animated-text__after-text',
            )
        );

        $this->_start_controls_section(
            'section_general',
            array(
                'label' => esc_html__( 'Content', 'lastudio-kit' ),
            )
        );

        $this->_add_control(
            'before_text_content',
            array(
                'label'   => esc_html__( 'Before Text', 'lastudio-kit' ),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__( 'Let us', 'lastudio-kit' ),
                'dynamic' => array( 'active' => true ),
            )
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'item_text',
            array(
                'label'   => esc_html__( 'Text', 'lastudio-kit' ),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__( 'Create', 'lastudio-kit' ),
                'dynamic' => array( 'active' => true ),
            )
        );

        $this->_add_control(
            'animated_text_list',
            array(
                'type'    => Controls_Manager::REPEATER,
                'label'   => esc_html__( 'Animated Text', 'lastudio-kit' ),
                'fields'  => $repeater->get_controls(),
                'default' => array(
                    array(
                        'item_text' => esc_html__( 'Create', 'lastudio-kit' ),
                    ),
                    array(
                        'item_text' => esc_html__( 'Animate', 'lastudio-kit' ),
                    ),
                ),
                'title_field' => '{{{ item_text }}}',
            )
        );

        $this->_add_control(
            'after_text_content',
            array(
                'label'   => esc_html__( 'After Text', 'lastudio-kit' ),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__( 'your text', 'lastudio-kit' ),
                'dynamic' => array( 'active' => true ),
            )
        );

        $this->_end_controls_section();

        $this->_start_controls_section(
            'section_settings',
            array(
                'label' => esc_html__( 'Settings', 'lastudio-kit' ),
            )
        );

        $this->_add_control(
            'animation_effect',
            array(
                'label'   => esc_html__( 'Animation Effect', 'lastudio-kit' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'fx1',
                'options' => array(
                    'fx1'  => esc_html__( 'Joke', 'lastudio-kit' ),
                    'fx2'  => esc_html__( 'Kinnect', 'lastudio-kit' ),
                    'fx3'  => esc_html__( 'Circus', 'lastudio-kit' ),
                    'fx4'  => esc_html__( 'Rotation fall', 'lastudio-kit' ),
                    'fx5'  => esc_html__( 'Simple Fall', 'lastudio-kit' ),
                    'fx6'  => esc_html__( 'Rotation', 'lastudio-kit' ),
                    'fx7'  => esc_html__( 'Anime', 'lastudio-kit' ),
                    'fx8'  => esc_html__( 'Label', 'lastudio-kit' ),
                    'fx9'  => esc_html__( 'Croco', 'lastudio-kit' ),
                    'fx10' => esc_html__( 'Scaling', 'lastudio-kit' ),
                    'fx11' => esc_html__( 'Fun', 'lastudio-kit' ),
                    'fx12' => esc_html__( 'Typing', 'lastudio-kit' ),
                ),
            )
        );

        $this->_add_control(
            'animation_delay',
            array(
                'label'   => esc_html__( 'Animation Speed', 'lastudio-kit' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 3000,
                'min'     => 500,
                'step'    => 100,
            )
        );

        $this->_add_control(
            'animation_start',
            array(
                'label'   => esc_html__( 'Animation Start After', 'lastudio-kit' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 0,
                'min'     => 0,
                'step'    => 100
            )
        );

        $this->_add_control(
            'split_type',
            array(
                'label'   => esc_html__( 'Split Type', 'lastudio-kit' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'symbol',
                'options' => array(
                    'symbol' => esc_html__( 'Symbols', 'lastudio-kit' ),
                    'word'   => esc_html__( 'Words', 'lastudio-kit' ),
                ),
            )
        );

        $this->_add_responsive_control(
            'animated_text_alignment',
            array(
                'label'   => esc_html__( 'Alignment', 'lastudio-kit' ),
                'type'    => Controls_Manager::CHOOSE,
                'default' => 'center',
                'options' => array(
                    'left'    => array(
                        'title' => esc_html__( 'Left', 'lastudio-kit' ),
                        'icon'  => 'eicon-text-align-left',
                    ),
                    'center' => array(
                        'title' => esc_html__( 'Center', 'lastudio-kit' ),
                        'icon'  => 'eicon-text-align-center',
                    ),
                    'right' => array(
                        'title' => esc_html__( 'Right', 'lastudio-kit' ),
                        'icon'  => 'eicon-text-align-right',
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} ' . $css_scheme['animated_text_instance'] => 'text-align: {{VALUE}};',
                ),
            )
        );

        $this->_end_controls_section();

        $this->_start_controls_section(
            'section_general_text_style',
            array(
                'label'      => esc_html__( 'General Text', 'lastudio-kit' ),
                'tab'        => Controls_Manager::TAB_STYLE,
                'show_label' => false,
            )
        );

        $this->_add_control(
            'text_color',
            array(
                'label' => esc_html__( 'Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} ' . $css_scheme['animated_text_instance'] => 'color: {{VALUE}}',
                ),
            )
        );

        $this->_add_control(
            'text_bg_color',
            array(
                'label' => esc_html__( 'Background color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} ' . $css_scheme['animated_text_instance'] => 'background-color: {{VALUE}}',
                ),
            )
        );

        $this->_add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'text_typography',
                'label'    => esc_html__( 'Typography', 'lastudio-kit' ),
                'selector' => '{{WRAPPER}} ' . $css_scheme['animated_text_instance'],
            )
        );

        $this->_add_responsive_control(
            'text_padding',
            array(
                'label'      => esc_html__( 'Padding', 'lastudio-kit' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%', 'em' ),
                'selectors'  => array(
                    '{{WRAPPER}} '  . $css_scheme['animated_text_instance'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->_end_controls_section();

        $this->_start_controls_section(
            'section_before_text_style',
            array(
                'label'      => esc_html__( 'Before Text', 'lastudio-kit' ),
                'tab'        => Controls_Manager::TAB_STYLE,
                'show_label' => false,
            )
        );

        $this->_add_control(
            'before_text_color',
            array(
                'label' => esc_html__( 'Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} ' . $css_scheme['before_text'] => 'color: {{VALUE}}',
                ),
            )
        );

        $this->_add_control(
            'before_text_bg_color',
            array(
                'label' => esc_html__( 'Background color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} ' . $css_scheme['before_text'] => 'background-color: {{VALUE}}',
                ),
            )
        );

        $this->_add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'before_text_typography',
                'label'    => esc_html__( 'Typography', 'lastudio-kit' ),
                'selector' => '{{WRAPPER}} ' . $css_scheme['before_text'],
            )
        );

        $this->_add_responsive_control(
            'before_text_padding',
            array(
                'label'      => esc_html__( 'Padding', 'lastudio-kit' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%', 'em' ),
                'selectors'  => array(
                    '{{WRAPPER}} '  . $css_scheme['before_text'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->_end_controls_section();

        $this->_start_controls_section(
            'section_animated_text_style',
            array(
                'label'      => esc_html__( 'Animated Text', 'lastudio-kit' ),
                'tab'        => Controls_Manager::TAB_STYLE,
                'show_label' => false,
            )
        );

        $this->_add_control(
            'animated_text_color',
            array(
                'label' => esc_html__( 'Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} ' . $css_scheme['animated_text'] => 'color: {{VALUE}}',
                ),
            )
        );

        $this->_add_control(
            'animated_text_bg_color',
            array(
                'label' => esc_html__( 'Background color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} ' . $css_scheme['animated_text'] => 'background-color: {{VALUE}}',
                ),
            )
        );

        $this->_add_control(
            'animated_text_cursor_color',
            array(
                'label' => esc_html__( 'Cursor Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} ' . $css_scheme['animated_text_item'] . ':after' => 'background-color: {{VALUE}}',
                ),
                'condition' => array(
                    'animation_effect' => 'fx12',
                ),
            )
        );

        $this->_add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'animated_text_typography',
                'label'    => esc_html__( 'Typography', 'lastudio-kit' ),
                'selector' => '{{WRAPPER}} ' . $css_scheme['animated_text'],
            )
        );

        $this->_add_responsive_control(
            'animated_text_padding',
            array(
                'label'      => esc_html__( 'Padding', 'lastudio-kit' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%', 'em' ),
                'selectors'  => array(
                    '{{WRAPPER}} '  . $css_scheme['animated_text'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->_end_controls_section();

        $this->_start_controls_section(
            'section_after_text_style',
            array(
                'label'      => esc_html__( 'After Text', 'lastudio-kit' ),
                'tab'        => Controls_Manager::TAB_STYLE,
                'show_label' => false,
            )
        );

        $this->_add_control(
            'after_text_color',
            array(
                'label' => esc_html__( 'Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} ' . $css_scheme['after_text'] => 'color: {{VALUE}}',
                ),
            )
        );

        $this->_add_control(
            'after_text_bg_color',
            array(
                'label' => esc_html__( 'Background color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} ' . $css_scheme['after_text'] => 'background-color: {{VALUE}}',
                ),
            )
        );

        $this->_add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'after_text_typography',
                'label'    => esc_html__( 'Typography', 'lastudio-kit' ),
                'selector' => '{{WRAPPER}} ' . $css_scheme['after_text'],
            )
        );

        $this->_add_responsive_control(
            'after_text_padding',
            array(
                'label'      => esc_html__( 'Padding', 'lastudio-kit' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%', 'em' ),
                'selectors'  => array(
                    '{{WRAPPER}} '  . $css_scheme['after_text'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->_end_controls_section();

    }

    /**
     * Generate spenned html string
     *
     * @param  string $str Base text
     * @return string
     */
    public function str_to_spanned_html( $base_string, $split_type = 'symbol' ) {

        $spanned_array = array();

        $base_words = explode( ' ', $base_string );
        if ( 'symbol' === $split_type ) {
            foreach ( $base_words as $symbol ) {
                $symbols_array = $this->_string_split( $symbol );
                $tmp = [];
                foreach ($symbols_array as $item){
                    $tmp[] = sprintf( '<span class="lakit-animated-span">%s</span>', $item );
                }
                $spanned_array[] = '<span>'.join('', $tmp).'</span>';
            }

        }
        else {
            foreach ( $base_words as $symbol ) {
                $spanned_array[] = sprintf( '<span class="lakit-animated-span">%s</span>', $symbol );
            }
        }
        return join( '<span class="lakit-animated-span">&nbsp;</span>', $spanned_array );
    }

    /**
     * Split string
     *
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    public function _string_split( $string ) {

        $strlen = mb_strlen( $string );
        $result = array();

        while ( $strlen ) {

            $result[] = mb_substr( $string, 0, 1, "UTF-8" );
            $string   = mb_substr( $string, 1, $strlen, "UTF-8" );
            $strlen   = mb_strlen( $string );

        }

        return $result;
    }

    /**
     * Generate setting json
     *
     * @return string
     */
    public function generate_setting_json() {
        $module_settings = $this->get_settings_for_display();

        $settings = array(
            'effect' => $module_settings['animation_effect'],
            'start'  => isset($module_settings['animation_start']) ? $module_settings['animation_start'] : 0,
            'delay'  => $module_settings['animation_delay'],
        );

        return sprintf( 'data-settings="%1$s"', esc_attr(json_encode( $settings )) );
    }

    protected function render() {

        $this->_context = 'render';

        $this->_open_wrap();
        include $this->_get_global_template( 'index' );
        $this->_close_wrap();
    }

}