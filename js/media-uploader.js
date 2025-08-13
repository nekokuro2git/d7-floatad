jQuery(document).ready(function($){
    var mediaUploader;

    $('.d7news-media-upload').click(function(e) {
        e.preventDefault();
        
        // 如果 mediaUploader 已經存在，直接開啟
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // 建立一個新的媒體上傳框架
        mediaUploader = wp.media({
            title: '選擇廣告圖片',
            button: {
                text: '使用此圖片'
            },
            multiple: false // 只允許選擇一張圖片
        });

        // 處理選擇圖片後的動作
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            // 將圖片網址填入圖片網址輸入框
            $('#d7news-image-url').val(attachment.url);
        });

        // 開啟媒體上傳框架
        mediaUploader.open();
    });
});