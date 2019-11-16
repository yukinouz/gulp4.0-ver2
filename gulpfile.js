/* ==================================
npm install
ncu // package.jsonのバージョンをチェック
ncu -u // package.jsonを最新バージョンにアップデート
==================================*/

// プラグインの読み込み
var gulp = require('gulp');
var sass = require('gulp-sass'); //Sassコンパイル
var plumber = require('gulp-plumber'); //エラー時の強制終了を防止
var notify = require('gulp-notify'); //エラー発生時にデスクトップ通知する
var sourcemaps = require('gulp-sourcemaps'); //ソースマップを作成
var sassGlob = require('gulp-sass-glob'); //@importの記述を簡潔に
var browserSync = require('browser-sync'); //ブラウザ反映
var postcss = require('gulp-postcss'); //autoprefixerとセット
var autoprefixer = require('autoprefixer'); //ベンダープレフィックス付与
var cssdeclsort = require('css-declaration-sorter'); //css並べ替え
var imagemin = require('gulp-imagemin');
var pngquant = require('imagemin-pngquant');
var mozjpeg = require('imagemin-mozjpeg');
var ejs = require("gulp-ejs");
var rename = require("gulp-rename"); //.ejsの拡張子を変更

gulp.task("ejs", (done) => {
  gulp
    .src(["ejs/**/*.ejs", "!" + "ejs/**/_*.ejs"])
    .pipe(ejs({}, {}, { ext: '.html' })) //ejsを纏める
    .pipe(rename({ extname: ".html" })) //拡張子をhtmlに
    .pipe(gulp.dest("./")); //出力先
  done();
});

// scssのコンパイル
gulp.task('sass', function () {
  return gulp
    .src('./src/scss/**/*.scss')
    .pipe(plumber({ errorHandler: notify.onError("Error: <%= error.message %>") }))//エラーチェック
    .pipe(sassGlob())//importの読み込みを手軽に
    .pipe(sourcemaps.init()) //ソースマップを初期化
    .pipe(sass({
      outputStyle: 'expanded' //expanded, nested, campact, compressedから選択
    }))
    .pipe(postcss([autoprefixer(
      {
        // ☆IEは11以上、Androidは4.4以上
        // その他は最新2バージョンで必要なベンダープレフィックスを付与する
        "browserslist": [
          "last 2 versions", "ie >= 11", "Android >= 4"],
        cascade: false
      }
    )]))
    .pipe(postcss([cssdeclsort({ order: 'alphabetically' })]))//プロパティをソートし直す(アルファベット順)
    .pipe(sourcemaps.write('./sourcemaps')) //ソースマップを作成
    .pipe(gulp.dest('./src/css'));//コンパイル後の出力先
});

// 保存時のリロード
gulp.task('browser-sync', function (done) {
  browserSync.init({
    // ローカルサーバー開発
    // port: 8080, // デフォルトは3000
    // files: ['./**/*.php'],
    // proxy: 'http://testsite.wp/', //MAMP -> http:localhost8888/
    // open: true,
    //watchOptions: {
  //        debounceDelay: 1000  //1秒間、タスクの再実行を抑制
   //     }
    /*
    その他のオプションは下記を参照
    https://www.browsersync.io/docs/options
    */

    //ローカル開発
     server: {
       baseDir: "./",
       index: "index.html"
     }
  });
  done();
});

gulp.task('bs-reload', function (done) {
  browserSync.reload();
  done();
});

// 監視
gulp.task('watch', function (done) {
  gulp.watch('./src/scss/**/*.scss', gulp.task('sass')); //sassが更新されたらgulp sassを実行
  gulp.watch('./src/scss/**/*.scss', gulp.task('bs-reload')); //sassが更新されたらbs-reloadを実行
  gulp.watch('./src/js/*.js', gulp.task('bs-reload')); //jsが更新されたらbs-relaodを実行
  gulp.watch('./ejs/**/*.ejs', gulp.task('ejs')); //ejsが更新されたらgulp-ejsを実行
  gulp.watch('./ejs/**/*.ejs', gulp.task('bs-reload')); //ejsが更新されたらbs-reloadを実行
});

// default
gulp.task('default', gulp.series(gulp.parallel('browser-sync', 'watch')));

//圧縮率の定義
var imageminOption = [
  pngquant({ quality: [80-85], }),
  mozjpeg({ quality: 85 }),
  imagemin.gifsicle({
    interlaced: false,
    optimizationLevel: 1,
    colors: 256
  }),
  imagemin.jpegtran(),
  imagemin.optipng(),
  imagemin.svgo()
];
// 画像の圧縮
// $ gulp imageminで画像を圧縮
gulp.task('imagemin', function () {
  return gulp
    .src('./src/img/**/*.{png,jpg,gif,svg}')
    .pipe(imagemin(imageminOption))
    .pipe(gulp.dest('./src/img'));
});