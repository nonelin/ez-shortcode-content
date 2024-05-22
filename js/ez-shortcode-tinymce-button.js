(function() {
    tinymce.PluginManager.add('ez_shortcode_tinymce_button', function(editor, url) {
        editor.addButton('ez_shortcode_tinymce_button', {
            text: 'EZSC',
            icon: false,
            onclick: function() {
                editor.windowManager.open({
                    title: 'Insert shortcode content',
                    body: [
                        {
                            type: 'textbox',
                            name: 'post_id',
                            label: 'EZSC ID'
                        },
                        {
                            type: 'textbox',
                            name: 'start_time',
                            label: 'Start time',
                            classes: 'flatpickr',
                        },
                        {
                            type: 'textbox',
                            name: 'end_time',
                            label: 'End time',
                            classes: 'flatpickr'
                        }
                    ],
                    onsubmit: function(e) {
                        var shortcode = '[ez_shortcode post_id="' + e.data.post_id + '" start_time="' + e.data.start_time + '" end_time="' + e.data.end_time + '"]';
                        editor.insertContent(shortcode);
                    }
                });

                flatpickr('.mce-flatpickr', {
                        enableTime: true, // 啟用時間選擇
                        dateFormat: 'Y-m-d H:i', // 日期和時間格式
                    });
            }
        });
    });
})();


//.flatpickr-calendar.open