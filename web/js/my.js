$(document).ready(function() {
    // responsive width checking
    var widthCheck = 767;

    var resizeTimer = null,
    verticalFlg = $(window).width() > widthCheck;
    

    
    if ($(window).width() < widthCheck) {
        $("#myCarousel").removeClass('vertical');
    }
    $("#myCarousel").carousel();
    

    
    $(window).resize(function() {
        resizeTimer && clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            var flg = ($(window).width() > widthCheck);
            if (verticalFlg != flg) {
                verticalFlg = flg;
                $("#myCarousel").toggleClass('vertical');
            }
        }, 200);
    });


    
    // loginform on dropdown menu
    $('.dropdown-menu').find('form').click(function (e) {
        e.stopPropagation();
    });
    
   
    // wrong username: 
    $(document).ajaxError(function(e,jqxhr, settings) {
        try {
            if (jqxhr.status == 403) {
                window.location.replace(base_url + 'login');
            }
        } catch (err) {
            alert('wrong pass or loin or something just goes wrong\n' + err.message);
        }
        
    });
 
    $('.hero-unit h1').hover(function(e){
        txt = $(this).html();
        if (e.ctrlKey) {
            $(this).html('&#1046;&#1040;&#1044;&#1053;&#1067;&#1045;.&#1089;&#1074;&#1086;');
        }
    }, function() {
        $(this).html(txt);
    }); 


    // table forms
    $('tr').hover( function() {
        hoverElem=this;
        //add in a  slight delay to allow Internet explorer to catch up
        setTimeout( function() {
            tr_id = $(hoverElem).attr('id');
        },0);
    }, function(){});
    
    //cam packs to user
    $('.tableform').submit(function(e){
        e.preventDefault();
        var url = $( this ).attr( 'action' );

        var form_id = ".tableform #"+tr_id+ " input";
        $.post( url, $(form_id).serialize(), function(data) {
            alert(data);
        });
    
    });
    
    // inline edit
    $('.editable').editable(base_url+'admin/update', { 
        indicator : '<img src="../img/indicator.gif">',
        tooltip   : 'Двойной клик для редактирования',
        placeholder: 'Двойной клик для редактирования',
        event : "dblclick",
        submitdata : {
            list: $('.tableform').attr('id')
        }
    });
     
    $('.editable-area').editable(base_url+'admin/update', { 
        indicator : '<img src="../img/indicator.gif">',
        tooltip   : 'Двойной клик для редактирования',
        placeholder: 'Двойной клик для редактирования',
        event : "dblclick",
        type      : 'textarea',
        submit    : 'OK',
        submitdata : {
            list: $('.tableform').attr('id')
        }
    });
    
         
    $('.editable-checkbox').editable(base_url+'admin/update', { 
        indicator : '<img src="../img/indicator.gif">',
        tooltip   : 'Двойной клик для редактирования',
        placeholder: 'Двойной клик для редактирования',
        event : "dblclick",
        type      : 'checkbox',
        submit    : 'OK',
        submitdata : {
            list: $('.tableform').attr('id')
        }
    });
    
    $('.del').click(function(){
        return window.confirm('Правда не нужно?');
    });


});
