<?php

/**
 * Class: LaStudioKit_Archive_Title
 * Name: Archive Title
 * Slug: lakit-archive-title
 */

namespace Elementor;

if (!defined('WPINC')) {
    die;
}


/**
 * Post Title Widget
 */
class LaStudioKit_Archive_Title extends LaStudioKit_Base {

    protected function enqueue_addon_resources(){
	    if(!lastudio_kit_settings()->is_combine_js_css()) {
		    $this->add_style_depends( 'lastudio-kit-base' );
	    }
    }

    public function get_name() {
        return 'lakit-archive-title';
    }

    protected function get_widget_title() {
        return esc_html__( 'Archive Title', 'lastudio-kit' );
    }

    public function get_icon() {
        return 'eicon-archive-title';
    }

    public function get_keywords() {
        return [ 'title', 'heading', 'archive' ];
    }

    public function get_categories() {
        return [ 'lastudiokit-builder' ];
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Post Title', 'lastudio-kit' ),
            ]
        );

        $this->add_control(
            'html_tag',
            [
                'label' => __( 'HTML Tag', 'lastudio-kit' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'p' => 'p',
                    'div' => 'div',
                    'span' => 'span',
                ],
                'default' => 'h2',
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label' => __( 'Alignment', 'lastudio-kit' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __( 'Left', 'lastudio-kit' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __( 'Center', 'lastudio-kit' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __( 'Right', 'lastudio-kit' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => __( 'Justified', 'lastudio-kit' ),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'link_to',
            [
                'label' => __( 'Link to', 'lastudio-kit' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => __( 'None', 'lastudio-kit' ),
                    'home' => __( 'Home URL', 'lastudio-kit' ),
                    'custom' => __( 'Custom URL', 'lastudio-kit' ),
                ],
            ]
        );

        $this->add_control(
            'link',
            [
                'label' => __( 'Link', 'lastudio-kit' ),
                'type' => Controls_Manager::URL,
                'placeholder' => __( 'https://your-link.com', 'lastudio-kit' ),
                'condition' => [
                    'link_to' => 'custom',
                ],
                'default' => [
                    'url' => '',
                ],
                'show_label' => false,
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__( 'Post Title', 'lastudio-kit' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'color',
            [
                'label' => __( 'Text Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .lakit-archive-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .lakit-archive-title a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}} .lakit-archive-title',
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow',
                'selector' => '{{WRAPPER}} .lakit-archive-title',
            ]
        );

        $this->add_control(
            'hover_animation',
            [
                'label' => __( 'Hover Animation', 'lastudio-kit' ),
                'type' => Controls_Manager::HOVER_ANIMATION,
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {


        if ( is_search() ) {
            $title = sprintf(
                '%1$s %2$s',
                '<span class="color-accent">' . __( 'Search Results for :', 'lastudio-kit' ) . '</span>',
                '&ldquo;' . get_search_query() . '&rdquo;'
            );
        }
        elseif ( ! is_home() ) {
            add_filter('get_the_archive_title_prefix', '__return_empty_string');
            $title    = get_the_archive_title();
        }
        elseif ( is_front_page() && ! is_singular( 'page' ) ) {

            if ( get_bloginfo( 'description' ) ) {
                $title = get_bloginfo( 'description' );
            } else {
                $title = esc_html__( 'Recent Posts', 'lastudio-kit' );
            }

            // Homepage posts page
        }
        elseif ( is_home() && ! is_singular( 'page' ) ) {
            $title = get_the_title( get_option( 'page_for_posts', true ) );
        }

        if ( empty( $title ) ){
            return;
        }

        $settings = $this->get_settings();

        switch ( $settings['link_to'] ) {
            case 'custom' :
                if ( ! empty( $settings['link']['url'] ) ) {
                    $link = esc_url( $settings['link']['url'] );
                } else {
                    $link = false;
                }
                break;

            case 'home' :
                $link = esc_url( get_home_url() );
                break;

            case 'none' :
            default:
                $link = false;
                break;
        }
        $target = $settings['link']['is_external'] ? 'target="_blank"' : '';

        $animation_class = ! empty( $settings['hover_animation'] ) ? 'elementor-animation-' . $settings['hover_animation'] : '';

        $html = sprintf( '<%1$s class="lakit-archive-title %2$s">', $settings['html_tag'], $animation_class );
        if ( $link ) {
            $html .= sprintf( '<a href="%1$s" %2$s>%3$s</a>', $link, $target, $title );
        } else {
            $html .= $title;
        }
        $html .= sprintf( '</%s>', $settings['html_tag'] );

        echo $html;
    }

    protected function content_template() {
        ?>
        <#
        var title = "<?php echo get_the_archive_title(); ?>";

        var link_url;
        switch( settings.link_to ) {
        case 'custom':
        link_url = settings.link.url;
        break;
        case 'home':
        link_url = '<?php echo esc_url( get_home_url() ); ?>';
        break;
        case 'none':
        default:
        link_url = false;
        }
        var target = settings.link.is_external ? 'target="_blank"' : '';

        var animation_class = '';
        if ( '' !== settings.hover_animation ) {
        animation_class = 'elementor-animation-' + settings.hover_animation;
        }

        var html = '<' + settings.html_tag + ' class="lakit-archive-title ' + animation_class + '">';
        if ( link_url ) {
        html += '<a href="' + link_url + '" ' + target + '>' + title + '</a>';
        } else {
        html += title;
        }
        html += '</' + settings.html_tag + '>';

        print( html );
        #>
        <?php
    }
    
}