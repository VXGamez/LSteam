jQuery(document).ready(function($){
	var sliderFinalWidth = 400,
		maxQuickWidth = 900;


	$('.targetaJoc').on('click', function(event){
		var selectedImage = $(this).children('.cd-item').children('.img-container').children('img'),
		titol = $(this).children('.cd-item').children('.card-body').children('h4'),
		preu = $(this).children('.cd-item').children('.card-body').children('h5'),
		storeImage = $(this).children('.cd-item').children('.card-footer').children('img');

		$("#quickViewImage").attr("src",selectedImage.attr('src'));
		$('#quickViewTitle').text(titol.html());
		$('#quickViewPrice').text(preu.html());
		$("#storeViewImage").attr("src",storeImage.attr('src'));


		var inputs = $(this).children('.cd-item').children('input');
		for(var i = 0; i < inputs.length; i++){
			// Match your input with inputs[i].name, etc.
		}

		//COMPRAR: <button type="button" class="btn btn-success" onclick="compra({{item.gameID}}, '{{item.title}}', {{item.salePrice}}, {{item.storeID}}, {{item.dealRating}}, '{{item.thumb}}', 0)" style="margin-left: 5%; margin-right: 5px;" >Buy</button> COMPRAR
		//AFEGIR FAVORITOS:<button type="button" class="btn btn-danger s-auto" onclick="compra({{item.gameID}}, '{{item.title}}', {{item.salePrice}}, {{item.storeID}}, {{item.dealRating}}, '{{item.thumb}}', 1)" >&#9825;</button>

		//compra({{item.gameID}}, '{{item.title}}', {{item.salePrice}}, {{item.storeID}}, {{item.dealRating}}, '{{item.thumb}}', 1)
		$("#ButtonComprarDetail").click(function(){
			compra(inputs[0].value, titol.html(), inputs[1].value, inputs[2].value, inputs[3].value, selectedImage.attr('src'), 0);
		});

		$("#ButtonFavoritosDetail").click(function(){
			compra(inputs[0].value, titol.html(), inputs[1].value, inputs[2].value, inputs[3].value, selectedImage.attr('src'), 2);
		});
		animateQuickView(selectedImage, sliderFinalWidth, maxQuickWidth, 'open');
	});

	$('body').on('click', function(event){
		if( $(event.target).is('.creu-sortir')  || $(event.target).is('#svgCreueta')) {
			closeQuickView( sliderFinalWidth, maxQuickWidth);
		}
	});

	$(document).keyup(function(event){
    	if(event.which=='27'){
			closeQuickView( sliderFinalWidth, maxQuickWidth);
		}
	});

	$(window).on('resize', function(){
		if($('.cd-quick-view').hasClass('is-visible')){
			window.requestAnimationFrame(resizeQuickView);
		}
	});

	function resizeQuickView() {
		var quickViewLeft = ($(window).width() - $('.cd-quick-view').width())/2,
			quickViewTop = ($(window).height() - $('.cd-quick-view').height())/2;
		$('.cd-quick-view').css({
		    "top": quickViewTop,
		    "left": quickViewLeft,
		});
	} 

	function closeQuickView(finalWidth, maxQuickWidth) {
		var selectedImage = $('.empty-box').find('img');
		if( !$('.cd-quick-view').hasClass('velocity-animating') && $('.cd-quick-view').hasClass('add-content')) {
			animateQuickView(selectedImage, finalWidth, maxQuickWidth, 'close');
		} else {
			closeNoAnimation(selectedImage, finalWidth, maxQuickWidth);
		}
		if(document.location.search.length){
			window.setTimeout(function (){
				window.location.replace('/user/wishlist');
			}, 1500);
		}

	}

	function animateQuickView(image, finalWidth, maxQuickWidth, animationType) {
		var parentListItem = image.parent('.img-container').parent('.cd-item'),
			topSelected = image.offset().top - $(window).scrollTop(),
			leftSelected = image.offset().left,
			widthSelected = image.width(),
			heightSelected = image.height(),
			windowWidth = $(window).width(),
			windowHeight = $(window).height(),
			finalLeft = (windowWidth - finalWidth)/2,
			finalHeight = finalWidth * heightSelected/widthSelected,
			finalTop = (windowHeight - finalHeight)/2,
			quickViewWidth = ( windowWidth * .8 < maxQuickWidth ) ? windowWidth * .8 : maxQuickWidth ,
			quickViewLeft = (windowWidth - quickViewWidth)/2;

		if( animationType == 'open') {
			parentListItem.addClass('empty-box');
			$('.cd-quick-view').css({
			    "top": topSelected,
			    "left": leftSelected,
			    "width": widthSelected,
			}).velocity({
				'top': finalTop+ 'px',
			    'left': finalLeft+'px',
			    'width': finalWidth+'px',
			}, 1000, [ 400, 20 ], function(){
				$('.cd-quick-view').addClass('animate-width').velocity({
					'left': quickViewLeft+'px',
			    	'width': quickViewWidth+'px',
				}, 300, 'ease' ,function(){
					$('.cd-quick-view').addClass('add-content');
				});
			}).addClass('is-visible');
		} else {
			$('.cd-quick-view').removeClass('add-content').velocity({
			    'top': finalTop+ 'px',
			    'left': finalLeft+'px',
			    'width': finalWidth+'px',
			}, 300, 'ease', function(){
				$('.cd-quick-view').removeClass('animate-width').velocity({
					"top": topSelected,
				    "left": leftSelected,
				    "width": widthSelected,
				}, 500, 'ease', function(){
					$('.cd-quick-view').removeClass('is-visible');
					parentListItem.removeClass('empty-box');
				});
			});
		}
	}
	function closeNoAnimation(image, finalWidth, maxQuickWidth) {
		var parentListItem = image.parent('.cd-item'),
			topSelected = image.offset().top - $(window).scrollTop(),
			leftSelected = image.offset().left,
			widthSelected = image.width();

		parentListItem.removeClass('empty-box');
		$('.cd-quick-view').velocity("stop").removeClass('add-content animate-width is-visible').css({
			"top": topSelected,
		    "left": leftSelected,
		    "width": widthSelected,
		});
	}

	if(document.location.search.length){
		const urlParams = new URLSearchParams(window.location.search);
		const myParam = urlParams.get('gameId');
		$('#jocId'+myParam).click();
	}


});

