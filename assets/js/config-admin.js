;(function( $ ) {
	"use strict";

	$.fn.ssgm_plugin = function() {

		var self = false;

		var _base = {

			// Sidebars accordion
			accordion: function(){
				jQuery("#smk-sidebars").on("click", "h3.accordion-section-title", function(event){
					if( $( event.target ).hasClass('smk-delete-sidebar') ){
						return;
					}
					
					var current = $(this);
					
					if( current.parents("li.accordion-section").hasClass("open") ){
						$(this).parents("li.accordion-section").removeClass("open");
						$("#smk-sidebars .accordion-section-content").slideUp("fast");
					}
					else{
						$("#smk-sidebars .accordion-section-content").slideUp("fast");
						$(this).next().slideDown("fast");

						$("#smk-sidebars li.accordion-section").removeClass("open");
						$(this).parents("li.accordion-section").addClass("open");
					}
				});
			},

			// Close all accordion sections
			closeAllAccordionSections: function(){
				$("#smk-sidebars li.accordion-section").removeClass("open");
				$("#smk-sidebars .accordion-section-content").slideUp("fast");
			},

			// Make accordion sections sortable
			sortableAccordionSections: function(){
				var blocks = jQuery("#smk-sidebars ul.connected-sidebars-lists");
				blocks.sortable({
					items: "> li",
					axis: "y",
					tolerance: "pointer",
					connectWith: ".connected-sidebars-lists",
					handle: ".smk-sidebar-section-icon",
					// cancel: '.moderate-sidebar, .accordion-section-content',
					start: function( event, ui ) {
						self.closeAllAccordionSections();
					}
				});
				blocks.find('h3.accordion-section-title').disableSelection();
			},

			// Random ID
			randomID: function(_nr, mode){
				var text = "",
					nb = "0123456789",
					lt = "abcdefghijklmnopqrstuvwxyz",
					possible;
					if( mode == 'l' ){
						possible = lt;
					}
					else if( mode == 'n' ){
						possible = nb;
					}
					else{
						possible = nb + lt;
					}

				for( var i=0; i < _nr; i++ ){
					text += possible.charAt(Math.floor(Math.random() * possible.length));
				}

				return text;
			},

			hideNoSidebarsNotice: function(){
				$('#no-sidebars-notice').slideUp('fast');
			},

			// Add new sidebar
			addNew: function(){

				var counter = $('#smk-sidebar-generator-counter').val();
				counter = ( counter ) ? parseInt( counter, 10 ) : 0;

				jQuery(".add-new-sidebar").on("click", function(event){
					counter = counter + 1;
					var template       = $('.sidebar-template').clone(),
					    sidebar_prefix = $(this).data('sidebars-prefix'),
					    id             = sidebar_prefix + counter + self.randomID(2, 'n') + self.randomID(3, 'l'); 
					
					template.removeClass('sidebar-template');

					// Inputs
					template.find('input, select').each(function(){
						var name  = $(this).attr('name');
						var value = $(this).attr('value');
						if( $(this).is('[name]') ){
							$(this).attr( 'name', name.replace( '__id__', id ) );
						}
						if( $(this).attr( 'value' ) ){
							$(this).attr( 'value', value.replace( '__id__', id ).replace( '__index__', counter ) );
						}
					});

					// Condition button
					var new_button_name = template.find('.condition-add').data( 'name' ).replace( '__id__', id );
					template.find('.condition-add').attr( 'data-name', new_button_name );
					template.find('.condition-add').attr( 'data-sidebar-id', id );

					// Index
					var h3 = template.find('h3.accordion-section-title span.name').html().replace( '__index__', counter );
					template.find('h3.accordion-section-title span.name').html( h3 );

					// Shortcode
					var shortcode = template.find('.smk-sidebar-shortcode').html().replace( '__id__', id );
					template.find('.smk-sidebar-shortcode').html( shortcode );

					// Template ID
					var template_id = template.attr('id');
					template.attr('id', template_id.replace( '__id__', id ))

					// Close other accordion sections
					self.closeAllAccordionSections();

					template.find(".conditions-all").hide();

					// Append the new sidebar as a new accordion section and slide down it
					template.appendTo('#smk-sidebars ul.connected-sidebars-lists').addClass("open").hide();
					template.find(".accordion-section-content").show();
					template.slideDown('fast');

					$('#smk-sidebar-generator-counter').val( counter );

					self.hideNoSidebarsNotice();
					
					$(document).trigger('smk-sidebar-js-refresh');

					event.stopImmediatePropagation();
				}).disableSelection();
			},

			// Live name and description update
			liveSet: function(){
				var container = jQuery('#smk-sidebars');

				container.on('change', '.smk-sidebar-name', function(){
					$(this).parents('li').find('h3.accordion-section-title span.name').html( $(this).val() );

				}).on('keyup', '.smk-sidebar-name', function(){
					$(this).parents('li').find('h3.accordion-section-title span.name').html( $(this).val() );

				});

				container.on('change', '.smk-sidebar-description', function(){
					$(this).parents('li').find('h3.accordion-section-title span.description').html( $(this).val() );

				}).on('keyup', '.smk-sidebar-description', function(){
					$(this).parents('li').find('h3.accordion-section-title span.description').html( $(this).val() );

				});
			},

			// Delete sidebar
			deleteSidebar: function(){
				jQuery("#smk-sidebars").on("click", ".smk-delete-sidebar", function(){
					var _bt = $(this);

					// Delete it if is confirmed
					if( _bt.hasClass('to-confirm') ){
						$('.wrap').addClass('sbg-removed-active');// Show removed sidebars

						_bt.parents('li').slideUp('fast', function() {
							_bt.remove();
						});
					}

					//Ask confirmation
					else{
						_bt.addClass('to-confirm').text('Are you sure?');
						deletion_timer(3, _bt);
						setTimeout(function(){
							_bt.removeClass('to-confirm').text('Delete');
						}, 3000);
					}

				});
			},

			// Get specific options for current condition choice via ajax
			targetIfCondition: function(){
				console.log('ok');
				$('.condition-equalto').select2({
					ajax: {
						url: ajaxurl,
						//  data: function (params) {
						// 	var query = {
						// 		search: params.term,
						// 		page: params.page
						// 	}

						// 	// Query paramters will be ?search=[term]&page=[page]
						// 	return query;
						// },
						processResults: function (data) {
							console.log(data);
							return {
								results: data.items
							};
						}
					}
				});
				// jQuery("#smk-sidebars").on("change", ".condition-if", function(){
					
					// jQuery.ajax({
					// 	type: "POST",
					// 	url: ajaxurl,
					// 	dataType: "json",
					// 	data: {
					// 		'action': 'smk-sidebar-generator_load_equalto',
					// 		'data':   { condition_if: selected }
					// 	},
					// 	success: function(response){
					// 		$.each(response, function(key, value) { 
					// 			to_change.prepend($("<option></option>").attr("value",key).text(value)); 
					// 		});

					// 		condition_parent.find('.conditions-second').show();

					// 		// $("body").append( $("<script />", {
					// 		// 	id: 'condition_if_' + selected.replace("::", "_"),
					// 		// 	html: response
					// 		// }) );
					// 	},
					// 	complete: function(response){
					// 	}
					// });//ajax
				// });
				// jQuery("#smk-sidebars").on("change", ".condition-if", function(){
				// 	var condition_parent = $(this).parents('.condition-parent'),
				// 	    selected = $(this).val(),
				// 	    to_change = condition_parent.find('.condition-equalto');

				// 	to_change.empty();

				// 	jQuery.ajax({
				// 		type: "POST",
				// 		url: ajaxurl,
				// 		dataType: "json",
				// 		data: {
				// 			'action': 'smk-sidebar-generator_load_equalto',
				// 			'data':   { condition_if: selected }
				// 		},
				// 		success: function(response){
				// 			$.each(response, function(key, value) { 
				// 				to_change.prepend($("<option></option>").attr("value",key).text(value)); 
				// 			});

				// 			condition_parent.find('.conditions-second').show();

				// 			// $("body").append( $("<script />", {
				// 			// 	id: 'condition_if_' + selected.replace("::", "_"),
				// 			// 	html: response
				// 			// }) );
				// 		},
				// 		complete: function(response){
				// 		}
				// 	});//ajax
				// });
			},

			// Clone a condition. Mainly used to add new condition. That's a fake clone
			conditionAdd: function(){
				$('#smk-sidebars').on('click', '.condition-add', function( event ){
					event.preventDefault();

					var _btn           = $(this),
					conditions_all     = _btn.parents('.conditions-all'),
					created_conditions = conditions_all.find('.created-conditions'),
					_name_             = _btn.data('name'),
					_sidebar_id_       = _btn.data('sidebar-id'),
					cloned_elem        = $('.smk-sidebars-condition-template .condition-parent').clone(),
					max_index          = 0;

					var main_condition_selector = conditions_all.find('.main-condition-selector');
					var main_condition = main_condition_selector.val();
					
					//If is a valid condition selected
					if( main_condition && 'none' !== main_condition ){
						//If this condition has been selected already, stop
						if( created_conditions.find('.condition-if').filter( function() { 
							var _t = $(this);
							var $exists = _t.val() === main_condition; 
							// console.log( _t.val() );
							if( $exists ){
								_t.parents('.condition-parent').addClass('warning');
								setTimeout(function(){
									_t.parents('.condition-parent').removeClass('warning');
								}, 300)
							}
							return $exists;
						}).length > 0 ){
							return;
						}
					}	
					else{
						main_condition_selector.select2("open");
						return;
					}

					// All nice, create the condition
					created_conditions.find('.cond-field').each(function(){
						var name   = $(this).attr('name'),
						this_nr    = name.match(/\[(\d+)\]/),
						the_number = parseInt( this_nr[1], 10 );

						if( the_number > max_index ){
							max_index = the_number;
						}
					});

					cloned_elem.find('.cond-field').each(function( index, elem ){
						var new_name  = $(elem).attr('name');
						$(elem).attr( 'name', new_name.replace( '__cond_name__', _name_ ).replace( '__id__', _sidebar_id_ ).replace( /\[\d+\]/g, '['+ (max_index + 1) +']' ) );
					});
					cloned_elem.find('select option').each(function(){
						$(this).removeAttr('selected');
					});

					cloned_elem.find('.condition-if').val( main_condition );

					cloned_elem.find('.condition-equalto').attr('data-main-cond', main_condition);;
					
					var cond =  smk_sidebar_config.conditions_selector[ main_condition.split('::')[0] ];
					var cond_info =  cond['label'];
					var cond_info_type =  cond['options'][ main_condition ];
					cloned_elem.find('.conditions-first').children('.cond-info').html( cond_info +': <em>'+ cond_info_type +'</em>' );

					cloned_elem.hide(); //Hide new condition
					created_conditions.children('.condition-placeholder').remove(); //Remove the notice if exists
					created_conditions.append( cloned_elem ); //Appent it
					cloned_elem.slideDown('fast'); //... and finally slide it 


					self.sortableconditions();
					
					$(document).trigger('smk-sidebar-js-refresh');
					$(document).trigger('smk-sidebar-sign-refresh');
				});
			},
			
			// Remove a condition
			conditionRemove: function(){
				$('#smk-sidebars').on('click', '.condition-remove', function(){
					$(this).parents('.condition-parent').slideUp( "fast", function() {
						$(this).remove();
						$(document).trigger('smk-sidebar-sign-refresh');
					});
				});
			},

			// Make conditions sortable
			sortableconditions: function(){
				var blocks = jQuery("#smk-sidebars .created-conditions");
				blocks.sortable({
					items: "> .condition-parent",
					axis: "y",
					tolerance: "pointer",
					handle: ".smk-sidebar-condition-icon",
				});
			},

			//Allow to use condition only if the user select the sidebar to replace
			allowConditions: function(){
				$('#smk-sidebars').find('.sidebars-to-replace-select').filter( function() { 
					var _t = $(this);
					if( ! _t.val() ){
						_t.parents('li').find('.conditions-all').hide();
					}
				});

				$('#smk-sidebars').on('change', '.sidebars-to-replace-select', function() { 
					var _t = $(this),
					cond_block = _t.parents('li').find('.conditions-all');
					if( _t.val() ){
						cond_block.show();
					}
					else{
						cond_block.hide();
					}
				});
			},


			tooltip: function(){
				Tipped.create('.tip', {
					// position: 'right',
					behavior: 'hide'
				});
			},
			
			select2: function(){
				$('.smk-sidebars-list select').select2();

				$(document).on( 'smk-sidebar-js-refresh', function(){
					$('.smk-sidebars-list select').select2();
				});
			},

			sidebarTabs: function(){
				var tabs_blocks = $('.sidebar-info-tabs');
				$.each( tabs_blocks, function( key, value ){
					var _t = $(this);
					var active_tab = _t.find('.sidebar-info-tab.active');
					_t.find( '.tabs [data-target]' ).hide()
					
					if( active_tab.length > 0 ){
						var active = active_tab.data('id')
						_t.find( '.tabs [data-target="'+ active +'"]' ).show();
					}
					else{
						_t.find( '.sidebar-info-tab[data-id="name"]' ).addClass('active');
						_t.find( '.tabs [data-target="name"]' ).show();
					}
				});

				$( '#smk-sidebars' ).on('click', '.sidebar-info-tab', function(){
					var _t = $(this);
					var _id = _t.data('id');
					var tabs_blocks = _t.parents('.sidebar-info-tabs');

					tabs_blocks.find('.sidebar-info-tab').removeClass('active');
					_t.addClass('active');

					tabs_blocks.find( '.tabs [data-target]' ).hide()
					tabs_blocks.find( '.tabs [data-target="'+ _id +'"]' ).show()
				});
			},

			infoSignsRefresh: function(){
				$('#smk-sidebars .accordion-section').each( function(){
					var li = $(this),
					replaces_sign = li.find('.info-signs').children('[data-info="replaces"]'),
					has_cond_sign = li.find('.info-signs').children('[data-info="has_conditions"]');

					if( li.find('.sidebars-to-replace-select').val() ){
						replaces_sign.addClass('active');
						if( li.find('.created-conditions .condition-parent').length > 0 ){
							has_cond_sign.addClass('active');
						}
						else{
							has_cond_sign.removeClass('active');
						}
					}
					else{
						replaces_sign.removeClass('active');
						has_cond_sign.removeClass('active');
					}
				});
			},

			infoSigns: function(){
				self.infoSignsRefresh();

				Tipped.create('.info-signs span', {
					behavior: 'hide'
				});

				$('#smk-sidebars').on( 'change', '.sidebars-to-replace-select', function(){
					$(document).trigger('smk-sidebar-sign-refresh');
				});

				// $('#smk-sidebars').on( 'change', '.smk-sidebar-enable-conditions', function(){
				// 	$(document).trigger('smk-sidebar-sign-refresh');
				// });

				$(document).on('smk-sidebar-sign-refresh', function(){
					self.infoSignsRefresh();
				});
			},

			/*
			-------------------------------------------------------------------------------
			Construct plugin
			-------------------------------------------------------------------------------
			*/
			__construct: function(){
				self = this;

				self.accordion();
				self.sortableAccordionSections();
				self.addNew();
				self.liveSet();
				self.deleteSidebar();
				self.targetIfCondition();
				self.conditionAdd();
				self.conditionRemove();
				self.sortableconditions();
				self.allowConditions();
				self.tooltip();
				self.select2();
				self.sidebarTabs();
				self.infoSigns();
				
				return this;
			}

		};

		/*
		-------------------------------------------------------------------------------
		Rock it!
		-------------------------------------------------------------------------------
		*/
		_base.__construct();

	};


	function deletion_timer(duration, display) {
		var timer_span = display.find('.timer'),
			start = Date.now(),
			diff,
			minutes,
			seconds;
		
		//Create timer span if it does not exists
		if( timer_span.length < 1 ){
			display.append( ' <span class="timer">0s</span>' );
		}

		//Find the timer span again(in case if it has been create just before)
		var timer_span = display.find('.timer');

		//Timer fn for interval
		function timer() {
			// get the number of seconds that have elapsed since 
			// deletion_timer() was called
			diff = duration - (((Date.now() - start) / 1000) | 0);

			// does the same job as parseInt truncates the float
			minutes = (diff / 60) | 0;
			seconds = (diff % 60) | 0;

			minutes = minutes < 10 ? "0" + minutes : minutes;
			seconds = seconds < 10 ? "0" + seconds : seconds;

			var the_time = ( diff > 59 ) ? minutes + "m " + seconds + "s" : diff + "s";

			timer_span.text( the_time ); 

			if (diff <= 0) {
				// add one second so that the count down starts at the full duration
				// example 05:00 not 04:59
				start = Date.now() + 1000;
			}
		};
		// we don't want to wait a full second before the timer starts
		timer();
		setInterval(timer, 1000);

		setTimeout(function(){
			display.find('.timer').remove();
		}, duration*1000);
	}


	$(document).on( 'ready load', function(){
		$('body').ssgm_plugin();
	});

})(jQuery);