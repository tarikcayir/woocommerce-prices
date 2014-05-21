/**
 * Script for "Woocommerce Prices" Wordpress plugin
 */

(function($){

	$(document).ready(function() {
	
		/**
		 * Load all products at startup
		 */ 
		getProducts();
		
		/**
		 * Handle action dropdown changes
		 */
		$('#select-action').change(function() {
			var action = $(this).find('option:selected').val();
			switch (action) {
				case 'newRegularPrice':
				case 'increaseRegularPrice':
				case 'decreaseRegularPrice':
				case 'increaseSalePrice':
				case 'decreaseSalePrice':
					$('.sale-days-field, .sale-from-field, .sale-to-field').hide();				
					$('.action-value-field').show();
					break;
				case 'newSalePrice':
				case 'newSaleDiscount':
					$('.sale-days-field').hide();
					$('.action-value-field, .sale-from-field, .sale-to-field').show();
					break;			
				case 'newSaleFrom':
					$('.action-value-field, .sale-days-field, .sale-to-field').hide();
					$('.sale-from-field').show();
					break;
				case 'newSaleTo':
					$('.action-value-field, .sale-days-field, .sale-from-field').hide();
					$('.sale-to-field').show();
					break;	
				case 'increaseSaleFrom':
				case 'decreaseSaleFrom':
				case 'increaseSaleTo':
				case 'decreaseSaleTo':
					$('.sale-days-field').show();
					$('.action-value-field, .sale-from-field, .sale-to-field').hide();
					break;
			}
		});		
	
		/**
		 * Clear taxonomy dropdowns
		 */
		$('#select-category').change(function() {
			$('#select-taxonomy').val('');
			$('#select-term option').remove();
		});

		/**
		 * Populate terms dropdown
		 */
		$('#select-taxonomy').change(function() {
			$('#select-category').val('-1');
			var data = {
				action: 'get_terms_options',
				nonce: $('#wooprices_nonce').val(),
				taxonomy: $(this).find('option:selected').val()
			};
			$.post(ajaxurl, data, function(response) {
				$('#select-term').html(response);
			});
		});

		/**
		 *  Setup datepicker
		 */
		$('#sale-from, #sale-to, .editor-text').datepicker({
			dateFormat: "yy-mm-dd"
		});
		
		/**
		 * Edit button handler
		 */ 
		$('.edit-button').click(function() {
			editProducts();
		});
		
		/**
		 * Get button handler
		 */
		$('.get-button').click(function() {
			var get = confirm('All changes will be lost. Continue?');
			if (get) {
				getProducts();
			}
		});		
		
		/**
		 * Save button handler
		 */
		$('.save-button').click(function() {
			var save = confirm('Do you want to save these changes?');
			if (save) {
				saveProducts();
			}
		});
		
		/**
		 * Setup SlickGrid
		 */ 
		var checkboxSelector = new Slick.CheckboxSelectColumn({
      cssClass: 'slick-cell-checkboxsel'
    });			
		var gridColumns = [
			checkboxSelector.getColumnDefinition(),
			{id: 'title', name: 'Product', field: 'title', width: 270},
			{id: 'regular_price', name: 'Regular Price', field: 'regular_price', editor: Slick.Editors.Text},
			{id: 'sale_price', name: 'Sale Price', field: 'sale_price', editor: Slick.Editors.Text},
			{id: 'sale_from', name: 'Sale From', field: 'sale_from', editor: customDateEditor},
			{id: 'sale_to', name: 'Sale To', field: 'sale_to', editor: customDateEditor}
		];		
		var gridOptions = {
			enableCellNavigation: true,
			enableColumnReorder: false,
			defaultColumnWidth: 100,
			headerRowHeight: 30,
			rowHeight: 30,
			autoHeight: true,
			editable: true
		};		
		var gridData = [];
		var grid = new Slick.Grid("#products-grid", gridData, gridColumns, gridOptions);		
    grid.registerPlugin(checkboxSelector);		
    grid.setSelectionModel(new Slick.RowSelectionModel({
			selectActiveRow: false
		}));
		
		/**
		 * Get product prices
		 */ 
		function getProducts() {
			$('#products-grid, .save-success').hide();
			$('.prices-loading').show();
			var data = {
				action: 'get_products',
				nonce: $('#wooprices_nonce').val(),
				category: $('#select-category option:selected').val(),
				taxonomy: $('#select-taxonomy option:selected').val(),
				term_id: $('#select-term option:selected').val()
			};
			$.post(ajaxurl, data, function(json) {
				var data = jQuery.parseJSON(json);
				if (data) {
					$('.prices-loading').hide();
					$('#products-grid').show();
					grid.setData(data);
					// Select all rows 
					var rows = [];
					for (var i = 0; i < data.length; i++) {
						rows.push(i);
					}
					grid.setSelectedRows(rows);
					grid.render(); 
				}
			});		
		}
		
		/**
		 * Edit product data in grid
		 */ 
		function editProducts() {
			grid.getEditController().commitCurrentEdit();
			$('.save-success').hide();
			var action = $('#select-action option:selected').val();
			switch (action) {
				case 'newRegularPrice':
				case 'increaseRegularPrice':
				case 'decreaseRegularPrice':
					editRegularPrice(action);
					break;
				case 'newSalePrice':
				case 'newSaleDiscount':
				case 'increaseSalePrice':
				case 'decreaseSalePrice':
					editSalePrice(action);
					break;
				case 'newSaleFrom':
				case 'increaseSaleFrom':
				case 'decreaseSaleFrom':
					editFromDate(action);
					break;
				case 'newSaleTo':
				case 'increaseSaleTo':
				case 'decreaseSaleTo':
					editToDate(action);
					break;
			}
		}
		
		/**
		 * Edit regular prices in grid
		 */		
		function editRegularPrice(action) {
			var value = $('#action-value').val();
			var amount = getAmountValue(value);
			var percent = getPercentValue(value);
			var selected = grid.getSelectedRows();
			var products = grid.getData();
			// Edit selected products
			jQuery.each(selected, function (index, i) {
				if ('' == value) {
					products[i].regular_price = '';
					return;
				}
				var regular = accounting.unformat(products[i].regular_price, wooPrices.decimalSeparator);
				switch (action) {
					// New regular price						
					case 'newRegularPrice':
						if (amount) {
							regular = amount;
						} else if (percent) {
							regular = regular * percent / 100;
						}
						break;
					// Increase regular price								
					case 'increaseRegularPrice':
						if (amount) {
							regular += amount;
						} else if (percent) {
							regular += regular * percent / 100;
						}	
						break;
					// Decrease regular price								
					case 'decreaseRegularPrice':
						if (amount) {
							regular -= amount;
						} else if (percent) {
							regular -= regular * percent / 100;
						}		
						if (regular < 0) {
							regular = 0;
						}
						break;
				}
				products[i].regular_price = accounting.formatMoney(regular, '', 2, '', wooPrices.decimalSeparator);
			});
			grid.setData(products);
			grid.render(); 
		}
		
		/**
		 * Edit sale prices in grid
		 */			
		function editSalePrice(action) {
			var value = $('#action-value').val();
			var newFrom = $('#sale-from').val();
			var newTo = $('#sale-to').val();
			var amount = getAmountValue(value);
			var percent = getPercentValue(value);	
			var selected = grid.getSelectedRows();
			var products = grid.getData();
			// Edit selected products
			jQuery.each(selected, function (index, i) {
				if ('' == value) {
					products[i].sale_price = '';
					return;
				}
				var regular = accounting.unformat(products[i].regular_price, wooPrices.decimalSeparator);
				var sale = accounting.unformat(products[i].sale_price, wooPrices.decimalSeparator);
				var from = products[i].sale_from;
				var to = products[i].sale_to;
				switch (action) {
					// New sale price
					case 'newSalePrice':
						if (amount) {
							sale = amount;
						} else if (percent) {
							sale = sale * percent / 100;
						}	else {
							sale = '';
						}
						if (newFrom) {
							from = newFrom;
						}
						if (newTo) {
							to = newTo;
						}				
						break;
					// New sale discount
					case 'newSaleDiscount':
						if (amount) {
							sale = regular - amount;
						} else if (percent) {
							sale = regular - (regular * percent / 100);
						}	
						if (newFrom) {
							from = newFrom;
						}
						if (newTo) {
							to = newTo;
						}
						break;		
					// Increase sale price
					case 'increaseSalePrice':
						if (!sale) {
							return;
						} else if (amount) {
							sale += amount;
						} else if (percent) {
							sale += sale * percent / 100;
						}
						break;
					// Decrease sale price
					case 'decreaseSalePrice':
						if (!sale) {
							return;
						} else if (amount) {
							sale -= amount;
						} else if (percent) {
							sale -= sale * percent / 100;
						}
						break;
				}
				// Correct for negative price
				if (sale != '' && sale < 0) {
					sale = 0;
				}
				products[i].sale_price = accounting.formatMoney(sale, '', 2, '', wooPrices.decimalSeparator);
				products[i].sale_from = from;			
				products[i].sale_to = to;			
			});
			grid.setData(products);
			grid.render(); 
		}
		
		/**
		 * Edit "sale from" dates in grid
		 */ 
		function editFromDate(action) {
			var value = $('#sale-days').val();
			var days = parseInt(value);
			var selected = grid.getSelectedRows();
			var products = grid.getData();
			// Edit selected products
			jQuery.each(selected, function (index, i) {
				from = products[i].sale_from;
				switch (action) {
					case 'newSaleFrom':
						from = $('#sale-from').val();
						break;				
					case 'increaseSaleFrom':
						from = changeSaleDate(from, days);
						break;
					case 'decreaseSaleFrom':
						from = changeSaleDate(from, -days);
						break;
				}
				products[i].sale_from = from;	
			});
			grid.setData(products);
			grid.render(); 			
		}
		
		/**
		 * Edit "sale to" dates in grid
		 */ 
		function editToDate(action) {
			var value = $('#sale-days').val();
			var days = parseInt(value);
			var selected = grid.getSelectedRows();
			var products = grid.getData();
			// Edit selected products
			jQuery.each(selected, function (index, i) {
				var to = products[i].sale_to;
				switch (action) {
					case 'newSaleTo':
						to = $('#sale-to').val();
						break;	
					case 'increaseSaleTo':
						to = changeSaleDate(to, days);
						break;
					case 'decreaseSaleTo':
						to = changeSaleDate(to, -days);
						break;
				}
				products[i].sale_to = to;	
			});
			grid.setData(products);
			grid.render(); 			
		}	
		
		/**
		 * Change sale date by number of days
		 */ 		
		function changeSaleDate(date, days) {
			if (date && days) {
				var date = $.datepicker.parseDate('yy-mm-dd', date);					
				date.setDate(date.getDate() + days);
				date = $.datepicker.formatDate('yy-mm-dd', date);	
			}
			return date;
		}
		
		/**
		 * Save grid data to products
		 */ 
		function saveProducts() {
			grid.getEditController().commitCurrentEdit();
			// Get selected products
			var selected = grid.getSelectedRows();
			var data = grid.getData();
			var products = [];
			jQuery.each(selected, function (index, i) {			
				products.push(data[i]);
			});
			// Save products
			var data = {
				action: 'save_products',
				nonce: $('#wooprices_nonce').val(),		
				products: products
			};
			$('.save-success').hide();
			$('.saving-products').css('display', 'inline-block');
			$.post(ajaxurl, data, function(response) {
				$('.saving-products').hide();
				$('.save-success').css('display', 'inline-block');
			})		
		}	

		/**
		 * Parse value to get fixed amount
		 */ 
		function getAmountValue(value) {
			var amount = false;
			if (value.indexOf('%') == -1) {
				amount = value.replace('%', '');
				amount = accounting.unformat(amount, wooPrices.decimalSeparator);
			}	
			return amount;
		}
		
		/**
	   * Parse value to get percentage
		 */ 
		function getPercentValue(value) {
			var percent = false;
			if (value.indexOf('%') >= 0) {
				percent = value.replace('%', '');
				percent = accounting.unformat(percent, wooPrices.decimalSeparator);	
			}	
			return percent;
		}			
		
		/**
 		 * Custom date editor for SlickGrid. 
		 * Almost identical to default date editor. 
		 * Only changes are date format and button URL.
		 */
		function customDateEditor(args) {
			var $input;
			var defaultValue;
			var scope = this;
			var calendarOpen = false;

			this.init = function () {
				$input = $("<INPUT type=text class='editor-text' />");
				$input.appendTo(args.container);
				$input.focus().select();
				$input.datepicker({
					dateFormat: "yy-mm-dd",
					showOn: "button",
					buttonImageOnly: true,
					buttonImage: wooPrices.pluginURL + '/slickgrid/images/calendar.gif',
					beforeShow: function () {
						calendarOpen = true;
					},
					onClose: function () {
						calendarOpen = false;
					}
				});
				$input.width($input.width() - 18);
			};

			this.destroy = function () {
				$.datepicker.dpDiv.stop(true, true);
				$input.datepicker("hide");
				$input.datepicker("destroy");
				$input.remove();
			};

			this.show = function () {
				if (calendarOpen) {
					$.datepicker.dpDiv.stop(true, true).show();
				}
			};

			this.hide = function () {
				if (calendarOpen) {
					$.datepicker.dpDiv.stop(true, true).hide();
				}
			};

			this.position = function (position) {
				if (!calendarOpen) {
					return;
				}
				$.datepicker.dpDiv
						.css("top", position.top + 30)
						.css("left", position.left);
			};

			this.focus = function () {
				$input.focus();
			};

			this.loadValue = function (item) {
				defaultValue = item[args.column.field];
				$input.val(defaultValue);
				$input[0].defaultValue = defaultValue;
				$input.select();
			};

			this.serializeValue = function () {
				return $input.val();
			};

			this.applyValue = function (item, state) {
				item[args.column.field] = state;
			};

			this.isValueChanged = function () {
				return (!($input.val() == "" && defaultValue == null)) && ($input.val() != defaultValue);
			};

			this.validate = function () {
				return {
					valid: true,
					msg: null
				};
			};

			this.init();
		}
		
	});

})(jQuery);