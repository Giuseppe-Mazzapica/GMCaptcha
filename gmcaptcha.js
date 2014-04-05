(function($) {

    gmcaptcha.random = function(num) {
        var len = (!num) ? $('#gmcaptcha_container').data('num') : num;
        if (!len) {
            len = 5;
        }
        var str = '2,3,4,5,6,7,8,9,a,b,c,d,f,g,h,j,k,m,n,p,q,r,s,t,v,w,x,y,z';
        var letters = str.split(',');
        var i = 0;
        var random = '';
        while (i < len) {
            random += letters[Math.floor(Math.random() * letters.length)];
            i++;
        }
        return random;
    };

    gmcaptcha.refresh = function(code, a) {
        var $a = (!a) ? $('#gmcaptcha_container a.gmcaptcha') : a;
        if (!$a.length) {
            alert(gmcaptcha.ajax_error);
            return false;
        }
        if (!code) {
            code = gmcaptcha.random();
        }
        var $img = $a.children('img').eq(0);
        $.ajax({
            url: gmcaptcha.ajax_url,
            data: "c=" + code,
            type: 'GET'
        }).done(function(data) {
            var new_code = data['new'];
            var hidden_code = data['code'];
            var uri = data['datauri'];
            if (!new_code || !hidden_code || !uri) {
                alert(gmcaptcha.ajax_error);
                return false;
            }
            $a.data('code', new_code);
            $('#' + gmcaptcha.hidden).val(hidden_code);
            $img.attr('src', "data:image/jpg;base64," + uri);
            $a.data('disabled', null);
        }).fail(function() {
            alert(gmcaptcha.ajax_error);
        });
    };

    $(document).on('click', 'a.gmcaptcha', function(e) {
        var $a = $(this);
        if ($a.data('disabled')) {
            return false;
        }
        $a.data('disabled', 1);
        gmcaptcha.refresh($a.data('code'), $a);
    });

})(jQuery);

