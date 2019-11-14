
// WordPress対応のため"jQuery"で記述開始＋function($)で以降は$の使用を可能にする
jQuery(function($){
  
  $(window).scroll(function () {
    // セレクタをまとめる
    var fadeIn = $('.fade-in, .fade-in-left, .fade-in-right, .fade-in-down');
    $(fadeIn).each(function () {
      var elemPos = $(this).offset().top,
        scroll = $(window).scrollTop(),
        windowHeight = $(window).height();
      if (scroll > elemPos - windowHeight + 100) {
        $(this).addClass('scroll-in');
      }
    });
  });
});