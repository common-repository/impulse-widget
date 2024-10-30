(function() {
       tinymce.PluginManager.add('impulse_button', function( editor, url ) {
           editor.addButton('impulse_button', {
                       text: 'Add Impulse Trigger',
                       image: url + '/default.png',
                       onclick: function() {
                         // change the shortcode as per your requirement
                          editor.insertContent('<br>[impulse_trigger]<br>');
                      }
             });
       });
})();