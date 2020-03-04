
//用來解決圖片顯示錯誤
window.addEventListener("load", function(event) {

    document.querySelectorAll('img').forEach(function(img){

        if (!img.complete || img.naturalWidth == 0) {

            img.src="{{ asset(Config::get('custom.no_image_url')) }}";

        }

        img.onerror = function(){

            img.src="{{ asset(Config::get('custom.no_image_url')) }}";

        };

    });

});
