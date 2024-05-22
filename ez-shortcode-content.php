<?php
/**
 * @package EZ_Shortcode_Content
 * @version 0.1
 */
/*
Plugin Name: EZ Shortcode Content (EZSC)
Plugin URI: 
Description: EZ Shortcode Content, referred to as "EZSC", use EZSC to create short code content and insert it into the article content to display customized content. Add the start and end time to the short code to achieve the function of regularly displaying hidden content.If no time is entered, it will always be displayed.
Author: nonelin
Version: 0.1
Author URI: https://dafatime.idv.tw
Text Domain:ezsc
Domain Path:/lang
*/
function load_ezsc_textdomain() {
    load_textdomain( 'ezsc', plugin_dir_path( __FILE__ ) . 'lang/ezsc-zh_TW.mo' );
    load_default_textdomain();
}
add_action( 'plugins_loaded', 'load_ezsc_textdomain' );

//建立文章類型
function ez_shortcode_content_post_type() {
    /*
     * $labels 描述了文章類型的顯示方式。
     */
    $labels = array(
        'menu_name' => __('EZSC', 'ezsc'), //選單第一層
        'name'          => __('EZSC List', 'ezsc'), // 列表頁標題
        'all_items' => __('EZSC List', 'ezsc'),      //子選單
        'singular_name' => 'ez_shortcode' ,  // 選單代稱
        'add_new' => __('Add EZSC', 'ezsc'),            // 子選單
        'add_new_item' => __('Add EZSC', 'ezsc'),   // 新增頁標題
        
    );
    /*
     * $supports 參數描述了文章類型支持的內容
     */
    $supports = array(
        'title',        // 文章標題
        'editor',       // 文章內容
        // 'excerpt',      // 允許簡短描述
        // 'author',       // 允許顯示和選擇作者
        // 'thumbnail',    // 允許精選圖片
        // 'comments',     // 啟用註釋
        // 'trackbacks',   // 支援引用
        // 'revisions',    // 顯示文章的自動保存版本
        // 'custom-fields' // 支援自定欄位
    );
    /*
     * $args 參數包含自定義文章類型的重要參數
     */
    $args = array(
        'labels'              => $labels,
        'description'         => __('使用短碼到文章中插入自訂的內容'), // 說明
        'show_in_rest'        => false, // 是否要使用 Gutenberg 編輯，設為 false 為舊的編輯畫面
        'supports'            => $supports,
        //'taxonomies'          => array('category'), // 允許 taxonomies 'category', 'post_tag'
        'hierarchical'        => false, // 允許分層分類，如果設置為 false，自定義帖子類型將表現得像文章，否則表現得像頁面
        'public'              => true,  // 公開文章類型
        'show_ui'             => true,  // 顯示此文章類型的界面
        'show_in_menu'        => true,  // 在管理菜單中顯示（左側面板）
        'show_in_nav_menus'   => true,  // 在外觀中顯示 -> 選單
        'show_in_admin_bar'   => true,  // 顯示在黑色管理欄
        // 'show_in_quick_edit' => false,
        'menu_position'       => 5,     // The position number in the left menu
        'menu_icon'           => 'dashicons-shortcode',  // The URL for the icon used for this post type
        'can_export'          => true,  // 允許使用工具導出內容 -> 導出
        'has_archive'         => true,  // 啟用文章類型存檔（按月、日或年）
        'exclude_from_search' => true, // 設置為true則前端搜索結果頁面不包含此類文章，設置為false則包含此類文章
        'publicly_queryable'  => true,  // 如果設置為 true，則允許在前端部分執行查詢
        'capability_type'     => 'post' // 允許像"Post"一樣讀取、編輯、刪除
    );
    register_post_type('ez_shortcode', $args); //創建一個帶有 slug 的文章類型是"books"和 $args 中的參數。
}
add_action('init', 'ez_shortcode_content_post_type');  // ,0); menu位置最上方


//自訂顯示列表欄位
add_filter('manage_ez_shortcode_posts_columns', 'ez_shortcode_table_head');
function ez_shortcode_table_head($columns) {
    // 自訂要顯示的欄位，必須使用 unset 刪除預設的欄位，才能自訂欄位排序
    unset( $columns['date'] );
    unset( $columns['title'] );
    //自訂列表欄位排序
    $columns['id'] = __('ID');
    $columns['title'] = __('Title');
    $columns['author'] = __('Author');
    $columns['date'] = __('Date');

    return $columns;
}
add_action('manage_ez_shortcode_posts_custom_column', 'ez_shortcode_table_content', 10, 2);
function ez_shortcode_table_content($column_name, $post_id) {
    // 在文章列表頁面中顯示自訂欄位的內容
    switch ($column_name) {
        case 'id':
            echo $post_id;
            break;
	}
}

//建立短碼
function guide_custom_shortcode_example($atts) {

    // 解析参数
    $atts = shortcode_atts( array(
        'post_id' => '',
        'start_time' => '',
        'end_time' => '',
    ), $atts );

    // 檢查是否提供了文章ID、開始時間和結束時間
    if ( empty( $atts['post_id'] ) ) {
        return __('<p>Please provide EZSC ID, start time, and end time.</p>', 'ezsc');
    }

    // 獲取文章物件
    $post = get_post( $atts['post_id'] );

    // 檢查文章類型是否為 "ez_shortcode"
    if ( ! $post || $post->post_type !== 'ez_shortcode' ) {
        return __('<p>The provided article ID is not an article of type "ez_shortcode".</p>', 'ezsc');
    }


    // 獲取當前時間
    $current_time = current_time( 'timestamp' );
    // 將開始時間和結束時間轉換為時間戳
    $start_time = strtotime( $atts['start_time'] );
    $end_time = strtotime( $atts['end_time'] );

    if ( empty( $atts['start_time'] ) || empty( $atts['end_time'] ) ) {
        $post_content = get_post_field( 'post_content', $atts['post_id'] );
        return $post_content;
    } else {
        // 檢查當前時間是否在開始時間和結束時間範圍內
        if ( $current_time >= $start_time && $current_time <= $end_time ) {
            // 如果在時間範圍內，檢索文章內容
            $post_content = get_post_field( 'post_content', $atts['post_id'] );
            return $post_content;
        } else {
            // 如果不在時間範圍內，返回空內容
            return '';
        }
    }
}
add_shortcode( 'ez_shortcode', 'guide_custom_shortcode_example' );

// 在文章编辑器中添加TinyMCE外掛
function ez_shortcode_tinymce_button() {
    add_filter('mce_buttons', 'register_ez_shortcode_tinymce_button');
    add_filter('mce_external_plugins', 'add_ez_shortcode_tinymce_button');
}
add_action('admin_head-post.php', 'ez_shortcode_tinymce_button');
add_action('admin_head-post-new.php', 'ez_shortcode_tinymce_button');

function register_ez_shortcode_tinymce_button($buttons) {
    array_push($buttons, "ez_shortcode_tinymce_button");
    return $buttons;
}
//即見即所得編輯器添加按鈕
function add_ez_shortcode_tinymce_button($plugin_array) {
    $plugin_array['ez_shortcode_tinymce_button'] = plugin_dir_url(__FILE__) . 'js/ez-shortcode-tinymce-button.js?'.time(); 
    return $plugin_array;
}

// 到ACF外掛的即見即所得編輯器添加按鈕
function add_custom_tinymce_button_to_acf_editor() {
    ?>
    <script type="text/javascript">
        (function($) {
            acf.add_filter('wysiwyg_tinymce_settings', function( mceInit, id ){
                // 將您的短代碼按鈕新增至工具列
                mceInit.toolbar1 += ',ez_shortcode_tinymce_button';
                // mceInit.toolbar2 += '';

                return mceInit;
            });
        })(jQuery);
    </script>
    <?php
}
add_action('acf/input/admin_footer', 'add_custom_tinymce_button_to_acf_editor');


//日期時間選擇器js & css
function enqueue_flatpickr() {
    wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', array('jquery'), null, true); 
    wp_enqueue_style('flatpickr-style', plugin_dir_url(__FILE__).'css/flatpickr.min.css', array(), null); 
}
add_action('admin_enqueue_scripts', 'enqueue_flatpickr');



