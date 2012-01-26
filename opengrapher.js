(function($) {
  $(function(){
    win = window.win || window.dialogArguments || opener || parent || top;
    win.$formfield = win.$formfield || null;

    $('.upload_image_button').click(function() {
      win.$formfield = $(this).siblings('.upload_image_value');
      tb_show('', 'media-upload.php?type=image&hbgs_filter=media&TB_iframe=true');

      return false;
    });

    $('body').ajaxSuccess(function(e, xhr, settings) {
      if (settings.url == "async-upload.php") {
        $frag = $("<div>"+xhr.responseText+"</div>");
        if(win.$formfield && win.$formfield.size()) {
          win.$formfield.val($frag.find(".urlfile").attr("title"));
          win.tb_remove();
        }
      }
    });
  });
}(jQuery));
