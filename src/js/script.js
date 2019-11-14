
// WordPress対応のため"jQuery"で記述開始＋function($)で以降は$の使用を可能にする
jQuery(function($){
  // ページリロードボタン
  $('#reload').on('click', function(){
		location.reload();
  });

// ページ内リンクを全てスムーススクロールにする
	$('a[href^="#"]').on('click',function(){
    var speed = 500;
    var href= $(this).attr("href");
    var target = $(href == "#" || href == "" ? 'html' : href);
    var position = target.offset().top;
    $("html, body").animate({scrollTop:position}, speed, "swing");
    return false;
  });

  // ページトップボタン
  // 最初は隠す
  $('#pagetop').hide();
  // 100スクロールしたらボタンが出現、それ以外は隠す
  $(window).scroll(function () {
    if ($(this).scrollTop() > 100) {
      $('#pagetop').fadeIn();
    } else {
      $('#pagetop').fadeOut();
    }
  });
  // クリックでトップへ戻る
  // animate.cssで調整
  $('#pagetop').click(function () {
    $('body, html').animate({
      scrollTop: 0
    }, 500);
    return false;
  });

  // カレントページナビゲーション
  $(function () {
    $('#gNav li a').each(function () {
      // 各aタグのhref内の値を取得する
      var $href = $(this).attr('href');
      if (location.href.match($href)) {
        $(this).addClass('current');
        // liを取りたい場合
        // $(this).parent('li').addClass('current');
      } else {
        $(this).removeClass('current');
        // liを取りたい場合
        // $(this).parent('li').removeClass('current');
      }
    });
  });

  // メニュークリックでコンテンツを閉じる
  $('.drawer-nav a').on('click', function () {
    $('#drawer-checkbox').prop('checked', false);
  });
});