<?php

/**
 * Class: LaStudioKit_Post_Content
 * Name: Post Content
 * Slug: lakit-post-content
 */

namespace Elementor;

if (!defined('WPINC')) {
    die;
}

use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

/**
 * Post Content Widget
 */
class LaStudioKit_Post_Content extends LaStudioKit_Base {

    protected function enqueue_addon_resources(){
	    if(!lastudio_kit_settings()->is_combine_js_css()) {
		    $this->add_style_depends( 'lastudio-kit-base' );
	    }
    }

    public function get_name() {
        return 'lakit-post-content';
    }

    protected function get_html_wrapper_class()
    {
        return 'lastudio-kit elementor-widget-theme-post-content elementor-' . $this->get_name();
    }

    protected function get_widget_title() {
        return esc_html__( 'Full Content', 'lastudio-kit' );
    }

    public function get_icon() {
        return 'eicon-post-excerpt';
    }

    public function get_categories() {
        return [ 'lastudiokit-builder' ];
    }

    public function show_in_panel() {
        // By default don't show.
        return false;
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_style',
            [
                'label' => __( 'Style', 'lastudio-kit' ),
                'tab' => Controls_Manager::TAB_STYLE,
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
                'selectors' => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __( 'Text Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}}' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
            ]
        );

        $this->end_controls_section();

    }

    protected function render() {

        static $did_posts = [];
        $post = get_post();

        if(is_null($post)){
            return;
        }

        if ( post_password_required( $post->ID ) ) {
            echo get_the_password_form( $post->ID );
            return;
        }

        // Avoid recursion
        if ( isset( $did_posts[ $post->ID ] ) ) {
            return;
        }
        $did_posts[ $post->ID ] = true;

        if($post->post_type === 'envato_tk_templates'){
            echo '<div class="lakit-post-content elementor-post__content">' . esc_html__('Post Content', 'lastudio-kit') . '</div>';  // XSS ok.
            return;
        }

        // End avoid recursion

        $editor = lastudio_kit()->elementor()->editor;

        $is_edit_mode = $editor->is_edit_mode();

        if ( lastudio_kit()->elementor()->preview->is_preview_mode( $post->ID ) ) {
            $content = lastudio_kit()->elementor()->preview->builder_wrapper( '' ); // XSS ok
        }
        else {
            $document = lastudio_kit()->elementor()->documents->get( $post->ID );
            // On view theme document show it's preview content.
            if ( $document ) {
                $preview_type = $document->get_settings( 'preview_type' );
                $preview_id = $document->get_settings( 'preview_id' );

                if ( 0 === strpos( $preview_type, 'single' ) && ! empty( $preview_id ) ) {
                    $post = get_post( $preview_id );

                    if ( ! $post ) {
                        return;
                    }
                }
            }

            // Set edit mode as false, so don't render settings and etc. use the $is_edit_mode to indicate if we need the CSS inline
            $editor->set_edit_mode( false );

            // Print manually (and don't use `the_content()`) because it's within another `the_content` filter, and the Elementor filter has been removed to avoid recursion.
            $content = lastudio_kit()->elementor()->frontend->get_builder_content( $post->ID, true );

            if ( empty( $content ) ) {
                lastudio_kit()->elementor()->frontend->remove_content_filter();

                // Split to pages.
                setup_postdata( $post );

                /** This filter is documented in wp-includes/post-template.php */
                echo apply_filters( 'the_content', get_the_content() );

                wp_link_pages( [
                    'before' => '<div class="page-links elementor-page-links"><span class="page-links-title elementor-page-links-title">' . __( 'Pages:', 'lastudio-kit' ) . '</span>',
                    'after' => '</div>',
                    'link_before' => '<span>',
                    'link_after' => '</span>',
                    'pagelink' => '<span class="screen-reader-text">' . __( 'Page', 'lastudio-kit' ) . ' </span>%',
                    'separator' => '<span class="screen-reader-text">, </span>',
                ] );

                lastudio_kit()->elementor()->frontend->add_content_filter();

                return;
            } else {
                $content = apply_filters( 'the_content', $content );
            }
        } // End if().

        // Restore edit mode state
        lastudio_kit()->elementor()->editor->set_edit_mode( $is_edit_mode );

        echo '<div class="lakit-post-content elementor-post__content">' . balanceTags( $content, true ) . '</div>';  // XSS ok.
    }

    public function render_plain_content() {}
    
}