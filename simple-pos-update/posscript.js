(function ($) {
    
$(document).ready(function ()
{
 
    $(".vpos_ajax").on('dblclick', function ()
    {
        var position = $(this).val();
        var postid = $(this).data('postid');
        
        var obj = this;
        
            $(obj).hide();
        
            
            $.post(ajaxurl, { 'action': 'posupdate', 'postid':postid, 'position':position }, function (data) 
            {
                   
                 //alert(data);
                 $(obj).show();
              
            });
        
    });
    

});

})(jQuery);


