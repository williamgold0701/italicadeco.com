<?php
// If this file is called directly, abort.

if ( ! defined( 'WPINC' ) ) {
    die;
}

add_filter('elementor/editor/localize_settings', function ($configs){
    $key = ['elementor_site', 'docs_elementor_site', 'help_the_content_url', 'help_right_click_url', 'help_flexbox_bc_url', 'elementPromotionURL', 'dynamicPromotionURL'];
    $key2 = ['help_preview_error_url', 'help_preview_http_error_url', 'help_preview_http_error_500_url', 'goProURL'];
    $tmp = [];
    if(is_array($configs)){
        foreach ($configs as $k => $v){
            if(in_array($k, $key)){
                $old_val = $configs[$k];
                $tmp[] = $old_val;
                $configs[$k] = str_replace(['elementor.com/pro', 'go.elementor.com'], ['la-studioweb.com/go/elementor-pro', 'la-studioweb.com/go/elementor'], $old_val);
	            $configs[$k] = 'https://la-studioweb.com/go/elementor-pro';
            }
            if( ($k == 'preview' || $k == 'icons') && is_array($v) ){
                foreach ($v as $k1 => $v1){
                    if(in_array($k1, $key2)){
                        $old_val2 = $v[$k1];
                        $tmp[] = $old_val2;
                        $v[$k1] = str_replace(['elementor.com/pro', 'go.elementor.com'], ['la-studioweb.com/go/elementor-pro', 'la-studioweb.com/go/elementor'], $old_val2);
	                    $v[$k1] = 'https://la-studioweb.com/go/elementor-pro';
                    }
                }
                $configs[$k] = $v;
            }
        }
    }
    if(!empty($configs['initial_document']['widgets'])){
        foreach ($configs['initial_document']['widgets'] as $widget => &$setting ) {
            if(isset($setting['help_url'])){
                $setting['help_url'] = 'https://elementor.com/help/'.str_replace('_', '-', $widget).'-widget/?ref=14171&campaign=la-studiowebdotcom';
	            $setting['help_url'] = 'https://la-studioweb.com/go/elementor-pro';
            }
        }
    }
    return $configs;
});

add_action('elementor/app/init', function (){
    add_action('wp_print_footer_scripts', function (){
        ?>
        <script type="text/javascript">
            (function($) {
                'use strict';
                function dpv_change_elementor_ref_url(){
                    $('a[href*="elementor.com"]').each(function () {
                        var _old = $(this).attr('href');
                        if(_old.indexOf('elementor.com/pro') >= 0 ){
                            $(this).attr('href', 'https://la-studioweb.com/go/elementor-pro');
                        }
                        else{
                            if(_old.indexOf('elementor.com/popup-builder') >= 0 ){
                                $(this).attr('href', 'https://la-studioweb.com/go/elementor/popup-builder');
                            }
                            else{
                                $(this).attr('href', _old.replace('go.elementor.com', 'la-studioweb.com/go/elementor'));
                                $(this).attr('href', 'https://la-studioweb.com/go/elementor-pro');
                            }
                        }
                    })
                }
                $(function(){
                    dpv_change_elementor_ref_url();
                });

            })(jQuery);
        </script>
        <?php
    }, 999);
});

add_action('admin_footer', function (){
    ?>
    <script type="text/javascript">
        (function($) {
            'use strict';
            function dpv_change_elementor_ref_url(){
                $('a[href*="elementor.com"]').each(function () {
                    var _old = $(this).attr('href');
                    if(_old.indexOf('elementor.com/pro') >= 0 ){
                        $(this).attr('href', 'https://la-studioweb.com/go/elementor-pro');
                    }
                    else{
                        if(_old.indexOf('elementor.com/popup-builder') >= 0 ){
                            $(this).attr('href', 'https://la-studioweb.com/go/elementor/popup-builder');
                        }
                        else{
                            $(this).attr('href', _old.replace('go.elementor.com', 'la-studioweb.com/go/elementor'));
                            $(this).attr('href', 'https://la-studioweb.com/go/elementor-pro');
                        }
                    }
                })
            }
            $(function(){
                dpv_change_elementor_ref_url();
            });

        })(jQuery);
    </script>
    <?php
}, 999);

add_filter('wp_redirect', function ( $location ){
    if( strpos($location, 'https://elementor.com/pro') !== false ){
        $location = 'https://la-studioweb.com/go/elementor-pro';
    }
    if( $location == 'https://go.elementor.com/docs-admin-menu/' ){
	    $location = 'https://la-studioweb.com/go/elementor-pro';
    }
    return $location;
}, 20);

add_action('elementor/editor/footer', function (){
    ?>
    <script type="text/javascript">
        var LaStudioScriptTemplateIds = ['#tmpl-elementor-panel-categories', '#tmpl-elementor-panel-global', '#tmpl-elementor-template-library-get-pro-button', '#elementor-preview-responsive-wrapper #elementor-notice-bar'];
        LaStudioScriptTemplateIds.forEach(function (id){
            var temp = document.querySelector(id);
            if(temp){
                temp.innerHTML = temp.innerHTML.replace(/href="(.*?)"/gi, 'href="https://la-studioweb.com/go/elementor-pro"');
            }
        });
    </script>
    <?php
}, 100);