			$(document).ready(function() {
				enableSelectBoxes();
			});
			
			function enableSelectBoxes(){
				$('div.selectBox').each(function(){
					$(this).children('span.selected').html($(this).children('div.selectOptions').children('span.selectOption:first').html());
					$(this).attr('value',$(this).children('div.selectOptions').children('span.selectOption:first').attr('value'));
					
					$(this).children('span.selected,span.selectArrow').click(function(){
						if($(this).parent().children('div.selectOptions').css('display') == 'none'){
							$(this).parent().children('div.selectOptions').css('display','block');
						}
						else
						{
							$(this).parent().children('div.selectOptions').css('display','none');
						}
					});
					
					$(this).find('span.selectOption').click(function(){
						$(this).parent().css('display','none');
						$(this).closest('div.selectBox').attr('value',$(this).attr('value'));
						$(this).parent().siblings('span.selected').html($(this).html());
					});
				});				
			}//-->
			
			
			
			        var theme_list_open = false;
        
        $(document).ready(function () {
        
        	function fixHeight () {
        	
        	var headerHeight = $("#switcher").height();
        	        	
        	$("#iframe").attr("height", (($(window).height()) - headerHeight) + 'px');
        	
        	}
        	
        	$(window).resize(function () {
               	
        		fixHeight();
        	
        	}).resize();
        	
        	$("#theme_select").click( function () {
        	
        		if (theme_list_open == true) {
        	
        		$(".center ul li ul").hide();
        		
        		theme_list_open = false;
        		
        		} else {
        		
        		$(".center ul li ul").show();         		
        		
        		theme_list_open = true;
        		
        		}
        		
        		return false;
        	
        	});
        	
        	$("#theme_list ul li a").click(function () {
        	
        	var theme_data = $(this).attr("rel").split(",");
        	        	
        	$("li.purchase a").attr("href", theme_data[1]);
        	$("li.remove_frame a").attr("href", theme_data[0]);
        	$("#iframe").attr("src", theme_data[0]);
        	
        	$("#theme_list a#theme_select").text($(this).text());
        	
        	$(".center ul li ul").hide();
        	
        	theme_list_open = false;
        	
        	return false;
        	
        	});
        
        });