var Advert = {
    render :function(){
    	if (document.URL.search("type=lost") != -1) {
    		if($(window).width() < 767) {
    			var city_input = $('.hide-on-med-and-up #city_filtr').val();
    			var category_lost = $('.hide-on-med-and-up #category_lost').val();
    		} else {
    			var city_input = $('.hide-on-small-only #city_filtr').val();
    			var category_lost = $('.hide-on-small-only #category_lost').val();
    		}
    	} else {
    		var city_input = $('#city_filtr').val();
    	}
        var lost_input = $('#lost-input').val();
        var found_input = $('#found-input').val();
        var place_input = $('#place').val();
        var date_from = $('#date_from').val();
        var date_to = $('#date_to').val();
        var category_found = $('#category_found').val();
        
        var advType = $('#advTyp').val();
        setTimeout(function() {
            Advert.loadAdvert(lost_input, found_input, city_input, place_input, date_from, date_to, category_found, category_lost,null,advType);
        },500);
        $(document).ready(function(){
	        $("#item_lost").on("click", "#pagination_list a", function (e) {
	        	e.stopPropagation();
	            e.preventDefault();
	            var page = $(this).attr("data-page"); //get page number from link
	            var lost_input = $(this).attr("data-lost");
	            var found_input = $(this).attr("data-found");
	            var city_input = $(this).attr("data-city");
	            var category_found = $(this).attr("data-fcat");
	            var category_lost = $(this).attr("data-lcat");
	            var advType = $('#advTyp').val();
	            Advert.loadAdvert(lost_input, found_input, city_input, place_input, date_from, date_to, category_found, category_lost, page, advType);
	            $("html, body").animate({ scrollTop: 0 }, "slow");
	
	        });
        });


        $("#found-input").keyup(function(){
            $("#foundLabel").show();

            $('#found-input').addClass('pasen');
            $("input#found-input").addClass("loading");
            $("#allFound").hide();

            var search_input = $(this).val();

            if(search_input.length<=0){
                $('#foundBox').hide();
                $("#allFound").show();
                $('#found-input').removeClass('pasen');
                $("#foundLabel").hide();
            }else {
                if (search_input.length == 0) {

                } else {

                    $.getJSON("/incvisio.lostfound/post/getAdvSearch", {
                        found_input: search_input,
                        ajax: 'true'
                    }, function (data) {
                        if(data.length==0){
                            var options = 'Нічого не знайдено';
                        }else {
                            var options = '';
                            if(data.length >= 5){
                                var iterator = 5;
                            }else{
                                iterator = data.length
                            }
                            for (var i = 0; i < iterator; i++) {

                                options += '<a href="/post/?type=found&found_input=' + data[i].title + '" style="color: black;">' + data[i].title + '</a><br>';
                            }
                        }
                        options += '<a href="/post/new/?type=found"><i class="fa fa-plus addnewp"></i><span style="color: #FEA500;">Додати новий</span></a>';
                        $("input#found-input").removeClass("loading");
                        $('#foundBox').html(options).show();

                    })
                }
            }
        });
        $("#lost-input").keypress(function(event) {
            if (event.which == 13) {
                event.preventDefault();
                $("#searchLost").submit();
            }
        });
        $("#found-input").keypress(function(event) {
            if (event.which == 13) {
                event.preventDefault();
                $("#searchFound").submit();
            }
        });
        $("#lost-input").keyup(function(){

            $("#lostLabel").show();
            $('input#lost-input').addClass('loading');
            $('#lost-input').addClass('pasen');
            $("#allLost").hide();

            var search_input = $(this).val();
            if(search_input.length<=0){
                $('#LostBox').hide();
                $("#allLost").show();
                $('#lost-input').removeClass('pasen');
                $("#lostLabel").hide();
            }else {
                if ( search_input.length ==0 ) {

                }else {
                    $.getJSON("/incvisio.lostfound/post/getAdvSearch", {
                        lost_input: search_input,
                        ajax: 'true'
                    }, function (data) {
                        if(data.length==0){
                            var options = 'Нічого не знайдено';
                        }else {
                            var options = '';
                            if(data.length >= 5){
                                var iterator = 5;
                            }else{
                                iterator = data.length
                            }
                            for (var i = 0; i < iterator; i++) {
                                options += '<a href="/post/?type=lost&lost_input=' + data[i].title + '" style="color: black;">' + data[i].title + '</a><br>';
                            }
                        }
                        options += '<a href="/post/new/?type=lost"><i class="fa fa-plus addnewp"></i><span style="color: #FEA500;">Додати новий</span></a>';
                        $("input#lost-input").removeClass("loading");
                        $('#LostBox').html(options).show();

                    })
                }
            }
        });
    },

    loadAdvert: function (lost_input, found_input, city_input, place_input, date_from, date_to, category_found, category_lost, page, adTyp){
        $.ajax({
            url : "/incvisio.lostfound/post/getPosts",
            data:{
                lost_input: lost_input,
                found_input: found_input,
                city_input: city_input,
                place_input: place_input,
                date_from: date_from,
                date_to: date_to,
                category_found: category_found,
                category_lost: category_lost,
                page:page,
                type:adTyp
            },
            type : "GET",
            dataType : "html",
            cache: false,
        }).done(function(data) {
            $('#item_lost').html(data);
        })
    },
    searchRender:function(){

    	if($(window).width() < 767) {
    		 $("#found-input").bind({
 	 	        click: function () {
 	 	            $("#found-input").attr('placeholder', 'Введіть текст для пошуку');
 	 	            $("#found-input").css("font-size", "18px");
 	 	        },
 	 	        mouseleave: function(){
 	 	            $("#found-input").attr('placeholder', 'Я знайшов');
 	 	            $("#found-input").css("font-size", "3rem");
 	 	        }
 	 	    });
    	} else {
	    	 $("#found-input").bind({
	 	        click: function () {
	 	            $("#found-input").attr('placeholder', 'Введіть текст для пошуку');
	 	            $("#found-input").css("font-size", "25px");
	 	        },
	 	        mouseleave: function(){
	 	            $("#found-input").attr('placeholder', 'Я знайшов');
	 	            $("#found-input").css("font-size", "3rem");
	 	        }
	 	    });
    	}

        $("#found-input").keyup(function(){
            $("#foundLabelFirst").hide();
            $("#foundLabel").show();

            $('#found-input').addClass('pasen');
            $("input#found-input").addClass("loading");
            $("#allFound").hide();

            var search_input = $(this).val();

            if(search_input.length<=0){
                $('#foundBox').hide();
                $("#allFound").show();
                $('#found-input').removeClass('pasen');
                $("#foundLabel").hide();
                $("#foundLabelFirst").show();
            }else {
                if (search_input.length == 0) {

                } else {

                    $.getJSON("/incvisio.lostfound/post/getAdvSearch", {
                        found_input: search_input,
                        ajax: 'true'
                    }, function (data) {
                        if(data.length==0){
                            var options = 'Нічого не знайдено<br>';
                        }else {
                            var options = '';
                            if(data.length >= 5){
                                var iterator = 5;
                            }else{
                                iterator = data.length
                            }
                            for (var i = 0; i < iterator; i++) {
                                options += '<a href="/post/?type=found&found_input=' + data[i].title + '" style="color: black;">' + data[i].title + '</a><br>';
                            }
                        }
                        options += '<a href="/post/new/?type=found"><i class="fa fa-plus addnewp"></i><span style="color: #FEA500;">Додати новий</span></a>';
                        $("input#found-input").removeClass("loading");
                        $('#foundBox').html(options).show();

                    })
                }
            }
        });
   
		if($(window).width() < 767) {
			$("#lost-input").bind({
		        click: function () {
		            $("#lost-input").attr('placeholder', 'Введіть текст для пошуку');
		            $("#lost-input").css("font-size", "18px");
		        },
		        mouseleave: function(){
		            $("#lost-input").attr('placeholder', 'Я загубив');
		            $("#lost-input").css("font-size", "3rem");
		        }
		    });
		} else {
			$("#lost-input").bind({
		        click: function () {
		            $("#lost-input").attr('placeholder', 'Введіть текст для пошуку');
		            $("#lost-input").css("font-size", "25px");
		        },
		        mouseleave: function(){
		            $("#lost-input").attr('placeholder', 'Я загубив');
		            $("#lost-input").css("font-size", "3rem");
		        }
		    });
		}
    	
        
        
        $("#lost-input").keyup(function(){
            $("#lostlabelFirst").hide();
            $("#lostLabel").show();
            //$('input#lost-input').addClass('loading');
            $('#lost-input').addClass('pasen');
            $("#allLost").hide();

            var search_input = $(this).val();
            if (search_input.length <= 0) {
                $('#LostBox').hide();
                $("#allLost").show();
                $('#lost-input').removeClass('pasen');
                $("#lostlabelFirst").show();
                $("#lostLabel").hide();
            } else {
                if ( search_input.length ==0 ) {

                } else {
                    $.getJSON("/incvisio.lostfound/post/getAdvSearch", {
                        lost_input: search_input,
                        ajax: 'true'
                    }, function (data) {
                        if (data.length==0){
                            var options = 'Нічого не знайдено<br>';
                        } else {
                            var options = '';
                            if(data.length >= 5){
                                var iterator = 5;
                            }else{
                                iterator = data.length
                            }
                            for (var i = 0; i < iterator; i++) {
                                options += '<a href="/post/?type=lost&lost_input=' + data[i].title + '" style="color: black;">' + data[i].title + '</a><br>';
                            }
                        }
                        options += '<a href="/post/new/?type=lost"><i class="fa fa-plus addnewp"></i><span style="color: #FEA500;">Додати новий</span></a>';
                        $("input#lost-input").removeClass("loading");
                        $('#LostBox').html(options).show();
                    })
                }
            }
        });
    }
};
$(function () {
    if(location.pathname=="/post" || location.pathname=="/post/" || location.pathname=="/incvisio.lostfound/post/index") {
        Advert.render();
    }
    if(location.pathname=="/") {
        Advert.searchRender();
    }
    //Advert.render();
});
