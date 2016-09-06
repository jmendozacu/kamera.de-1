
jQuery(document).ready(function($){

  (function(selector){

    var $nav = $(selector);
    var $megamenu  = $('.megamenu', $nav);
    var effect = MEGAMENU_EFFECT; 
    $megamenu.hover(function(){
      /* get total padding + border + margin of the popup */
      var extraWidth       = 0
      var wrapWidthPopup   = $(this).find('.dropdown').outerWidth(true); /*include padding + margin + border*/
      var actualWidthPopup = $(this).find('.dropdown').width(); /*no padding, margin, border*/
      extraWidth           = wrapWidthPopup - actualWidthPopup;    

      /* calculate new width of the popup*/
      var widthblock1 = $(this).find('.dropdown .block1').outerWidth(true);
      var widthblock2 = $(this).find('.dropdown .block2').outerWidth(true);
      var new_width_popup = 0;
      if(widthblock1 && !widthblock2){
         new_width_popup = widthblock1;
      }
      if(!widthblock1 && widthblock2){
         new_width_popup = widthblock2;
      }
      if(widthblock1 && widthblock2){
          if(widthblock1 >= widthblock2){
              new_width_popup = widthblock1;
          }
          if(widthblock1 < widthblock2){
              new_width_popup = widthblock2;
          }
      }
      var new_outer_width_popup = new_width_popup + extraWidth;

      /*define top and left of the popup*/
      var wraper = $nav;
      var wWraper = wraper.outerWidth();
      var posWraper = wraper.offset();
      var pos = $(this).offset();

      //var xTop = pos.top - posWraper.top + MEGAMENU_OFFSET;
      var xLeft = pos.left - posWraper.left;
      if ((xLeft + new_outer_width_popup) > wWraper) xLeft = wWraper - new_outer_width_popup;

      //$(this).find('.dropdown').css('top',xTop);
      $(this).find('.dropdown').css('left',xLeft);

      /*set new width popup*/
      $(this).find('.dropdown').css('width',new_width_popup);
      $(this).find('.dropdown .block1').css('width',new_width_popup);
       
    })

    $("#megamenu_link ul li").each(function(){
        var url = document.URL;
        $("#megamenu_link ul li a").removeClass("act");
        $('#megamenu_link ul li a[href="'+url+'"]').addClass('act');
    }); 
    
    $('.megamenu_no_child').hover(function(){
        $(this).addClass("active");
    },function(){
        $(this).removeClass("active");
    })
    
    $('.megamenu').hover(function(){
        if($(this).attr("id") != "megamenu_link"){
            $(this).addClass("active");
        }
    },function(){
        $(this).removeClass("active");
    })

  })('#nav_megamenu');


  (function(selector){ // vertical megamenu

    var $nav = $(selector);
    var $megamenu  = $('.megamenu', $nav);

    $megamenu.hover(function(){
      var $dropdown = $(this).find('.dropdown');
      if(!$dropdown.hasClass('fixedWidth')){
        var widthblock1 = $(this).find('.dropdown .block1').outerWidth(true);
        var widthblock2 = $(this).find('.dropdown .block2').outerWidth(true);
        $(this).find('.dropdown .block1').width(widthblock1);
        $(this).find('.dropdown .block2').width(widthblock2);
        $(this).find('.dropdown').css('width', widthblock1 + widthblock2);
        $dropdown.addClass('fixedWidth');
      }
      $(this).find('.dropdown').addClass('active');
    },function(){
      $(this).find('.dropdown').removeClass('active');
    })
  })('#nav_vmegamenu');


  // Mobiemenu
  (function($){
       $.fn.extend({  
           mobilemenu: function() {       
              return this.each(function() { 
                var $ul = $(this);
          if($ul.data('accordiated')) return false;
                            
          $.each($ul.find('ul, li>div'), function(){
            $(this).data('accordiated', true);
            $(this).hide();
          });
          
          $.each($ul.find('span.head'), function(){
            $(this).click(function(e){
              activate(this);
              return void(0);
            });
          });
          
          var active = (location.hash)?$(this).find('a[href=' + location.hash + ']')[0]:'';

          if(active){
            activate(active, 'toggle');
            $(active).parents().show();
          }
          
          function activate(el,effect){
            $(el).parent('li').toggleClass('active').siblings().removeClass('active').children('ul, div').slideUp('fast');
            $(el).siblings('ul, div')[(effect || 'slideToggle')]((!effect)?'fast':null);
          }
          
              });
          } 
      }); 
  })(jQuery);

  (function(selector){
    var $container = $(selector);
    var $menu  = $('.mobilemenu', $container);

  $("li.parent", $menu).each(function(){
        $(this).append('<span class="head"><a href="javascript:void(0)"></a></span>');
      });
  
  $menu.mobilemenu();
  
  $("li.active", $menu).each(function(){
    $(this).children().next("ul").css('display', 'block');
  });
    
  $('.btn-navbar', $container).toggle(function() {
    $container.find('#navbar-inner').removeClass('navbar-inactive');
    $container.find('#navbar-inner').addClass('navbar-active');
  }, function() {
    $container.find('#navbar-inner').removeClass('navbar-active');
    $container.find('#navbar-inner').addClass('navbar-inactive');
  });
  })('.nav-mobilemenu-container');

});

