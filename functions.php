<?php

function custom_theme_setup() {
  // head内にフィードリンクを出力
  add_theme_support('automatic-feed-links');
  // タイトルタグを動的に出力
  add_theme_support('title-tag');
  // アイキャッチ画像を有効化
  add_theme_support('post-thumbnails');
  // set_post_thumbnail_size( 175, 175, false); //true 画像を切り抜く false 縦横比維持
  // カスタムメニューを有効化
  add_theme_support('menus');
  // editor-style.css(ビジュアルエディタ用のCSS)を登録
  add_editor_style('editor-style.css');
  // テーマの位置を定義
  register_nav_menus(
    array(
      'header' => 'グローバルナビゲーション',
      'footer' => 'フッターナビゲーション',
    )
  );

  add_filter( 'post_thumbnail_html', 'custom_attribute' );
    function custom_attribute( $html ){
    // width height を削除する
    $html = preg_replace('/(width|height)="\d*"\s/', '', $html);
    return $html;
}

  // title-tagのセパレータを'-'から'|'に変更
function custom_title_separator($sep) {
  $sep = '|';
  return $sep;
}
add_filter( 'document_title_separator', 'custom_title_separator' );

  // カスタムヘッダーを有効化
  $custom_header_defauls = array(
	'width'         => 2000,
	'height'        => 800,
  'default-image' => get_template_directory_uri() . '/src/img/top/mv_all.jpg',
  'uploads' => true,
  );
  add_theme_support('custom-header', $custom_header_defauls);

  add_theme_support('custom-background');
}
add_action('after_setup_theme', 'custom_theme_setup');

//ヘッダー画像のIDを取得
function custom_header_get_attachment_id_by_url( $url ) {
  $parse_url  = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );
  $this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
  $file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );
  if ( ! isset( $parse_url[1] ) || empty( $parse_url[1] ) || ( $this_host != $file_host ) ) {
    return;
  }
  global $wpdb;
  $attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parse_url[1] ) );
  return $attachment[0];
}

// ウィジェットエリアの登録
function custom_widget_register() {
  register_sidebar(array(
    'name' => 'サイドバー',
    'id' => 'sidebar-primary', //テンプレートファイルで呼び出す際に使用
    'before_widget' => '<div class="widget widget-sidebar>',
    'after_widget' => '</div>',
    'before_title' => '<h2 class="widget-title">',
    'after_title' => '</h2>',
  ));
}
add_action('widgets_init' , 'custom_widget_register');

//レスポンシブなページネーションを作成する
function responsive_pagination($pages = '', $range = 4){
  $showitems = ($range * 2)+1;

  global $paged;
  if(empty($paged)) $paged = 1;

  //ページ情報の取得
  if($pages == '') {
    global $wp_query;
    $pages = $wp_query->max_num_pages;
    if(!$pages){
      $pages = 1;
    }
  }

  if(1 != $pages) {
    echo '<ul class="pagination" role="menubar" aria-label="Pagination">';
    //先頭へ
    echo '<li class="first"><a href="'.get_pagenum_link(1).'"><span>First</span></a></li>';
    //1つ戻る
    echo '<li class="previous"><a href="'.get_pagenum_link($paged - 1).'"><span>Previous</span></a></li>';
    //番号つきページ送りボタン
    for ($i=1; $i <= $pages; $i++)     {
      if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))       {
        echo ($paged == $i)? '<li class="current"><a>'.$i.'</a></li>':'<li><a href="'.get_pagenum_link($i).'" class="inactive" >'.$i.'</a></li>';
      }
    }
    //1つ進む
    echo '<li class="next"><a href="'.get_pagenum_link($paged + 1).'"><span>Next</span></a></li>';
    //最後尾へ
    echo '<li class="last"><a href="'.get_pagenum_link($pages).'"><span>Last</span></a></li>';
    echo '</ul>';
  }
}

/**
* ページネーション出力関数
* $paged : 現在のページ
* $pages : 全ページ数
* $range : 左右に何ページ表示するか
* $show_only : 1ページしかない時に表示するかどうか
*/
function pagination( $pages, $paged, $range = 2, $show_only = false ) {

  $pages = ( int ) $pages;    //float型で渡ってくるので明示的に int型 へ
  $paged = $paged ?: 1;       //get_query_var('paged')をそのまま投げても大丈夫なように

  //表示テキスト
  $text_first   = "« 最初へ";
  $text_before  = "‹ 前へ";
  $text_next    = "次へ ›";
  $text_last    = "最後へ »";

  if ( $show_only && $pages === 1 ) {
    // １ページのみで表示設定が true の時
    echo '<div class="pagination"><span class="current pager">1</span></div>';
    return;
  }

  if ( $pages === 1 ) return;    // １ページのみで表示設定もない場合

  if ( 1 !== $pages ) {
    //２ページ以上の時
    echo '<div class="pagination"><span class="page_num">Page ', $paged ,' of ', $pages ,'</span>';
    if ( $paged > $range + 1 ) {
      // 「最初へ」 の表示
      echo '<a href="', get_pagenum_link(1) ,'" class="first">', $text_first ,'</a>';
    }
    if ( $paged > 1 ) {
      // 「前へ」 の表示
      echo '<a href="', get_pagenum_link( $paged - 1 ) ,'" class="prev">', $text_before ,'</a>';
    }
    for ( $i = 1; $i <= $pages; $i++ ) {
      if ( $i <= $paged + $range && $i >= $paged - $range ) {
        // $paged +- $range 以内であればページ番号を出力
        if ( $paged === $i ) {
            echo '<span class="current pager">', $i ,'</span>';
        } else {
          echo '<a href="', get_pagenum_link( $i ) ,'" class="pager">', $i ,'</a>';
          }
        }
    }
    if ( $paged < $pages ) {
      // 「次へ」 の表示
      echo '<a href="', get_pagenum_link( $paged + 1 ) ,'" class="next">', $text_next ,'</a>';
    }
    if ( $paged + $range < $pages ) {
      // 「最後へ」 の表示
      echo '<a href="', get_pagenum_link( $pages ) ,'" class="last">', $text_last ,'</a>';
    }
    echo '</div>';
  }
}

/*
//All in One SEO Pack で出力される<link rel="prev/next">を消す
add_filter('aioseop_prev_link', '__return_empty_string' );
add_filter('aioseop_next_link', '__return_empty_string' );
*/

// excerptの文字数を変更
function custom_excerpt_length( $length ) {
     return 55;	
}	
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

// excerptの続き文字を変更
function new_excerpt_more($more) {
	return '・・・';
}
add_filter('excerpt_more', 'new_excerpt_more');

// excerptの文字数を引数で指定できるようになる
// custom_excerpt($lnegth); を使用
function custom_excerpt($length = 110) {
  global $post;

  $suffix = '...';

  $content = mb_substr(strip_tags($post->post_excerpt),0,$length);

  if (!$content) {
    $content =  $post->post_content;
    $content =  strip_shortcodes($content);
    $content =  strip_tags($content);
    $content =  str_replace(' ', '', $content);
    $content =  html_entity_decode($content, ENT_QUOTES, 'UTF-8');

    if (mb_strlen($content, 'UTF-8') > $length) {
      $content =  mb_substr($content, 0, $length, 'UTF-8');
      $content .= $suffix;
    }
  }

  return $content;
}

// shortcodeでテーマファイル内画像URL参照時利用
add_shortcode('tdir', 'tmp_dir');
function tmp_dir() {
return get_template_directory_uri();
}

// shortcodeで投稿画面でhome_urlを使う
add_shortcode('hmurl', 'hmurl_dir');
function hmurl_dir() {
return esc_url(home_url());
}

// CSS読み込み
function my_styles() {
  wp_enqueue_style( 'slick-theme', get_template_directory_uri() . '/src/css/slick/slick-theme.css', array(), '1.0.0' );
  wp_enqueue_style( 'slick', get_template_directory_uri() . '/src/css/slick/slick.css', array(), '1.0.0' );
  wp_enqueue_style( 'slick-style', get_template_directory_uri() . '/src/css/slick/style.css', array(), '1.0.0' );
  wp_enqueue_style( 'style', get_template_directory_uri() . '/src/css/style.css', array(), '1.0.0' );
  wp_enqueue_style( 'fontawesome-style', 'https://use.fontawesome.com/releases/v5.8.1/css/all.css' );
}
add_action( 'wp_enqueue_scripts', 'my_styles' );

// wordpress jQuery 無効化
function my_scripts_method() {
    wp_deregister_script('jquery');
    wp_enqueue_script('jquery','https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js', array());
}
add_action( 'wp_enqueue_scripts', 'my_scripts_method' );

// js読み込み
function my_scripts() {
  wp_enqueue_script( 'script', get_template_directory_uri() . '/src/js/script.js', array( 'jquery' ), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'my_scripts' );

// 現在適用しているテーマのeditor-style.cssを読み込む
add_action( 'enqueue_block_editor_assets', 'gutenberg_stylesheets_custom_demo' );
function gutenberg_stylesheets_custom_demo() {
  $editor_style_url = get_theme_file_uri('/editor-style.css');
  wp_enqueue_style( 'theme-editor-style', $editor_style_url );
}