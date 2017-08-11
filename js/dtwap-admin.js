jQuery(document).ready( function($){
	
	$('.themes .theme .theme-actions').each( function( index, element ){
		
		var theme_folder  = $(this).parents('.theme').attr('aria-describedby').split(' ')[0].slice( 0, -7 );
		var download_link = '<a href="?dtwap_download='+theme_folder+'" class="button button-primary dtwap_download_link">'+dtwap.download_title+'</a>';
		
		$(this).prepend(download_link)
	});
});