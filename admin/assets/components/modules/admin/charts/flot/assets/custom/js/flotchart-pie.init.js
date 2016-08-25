(function($)
{
	if (typeof charts == 'undefined') 
		return;

	charts.chart_pie = 
	{
		// chart data
		data: [
		    { label: "New Enrollment",  data: 38 },
		    { label: "Open Positions",  data: 23 },
		    { label: "Retirement",  data: 15 },
		    { label: "Replacement",  data: 9 },
		    { label: "Promotion",  data: 7 },
		    { label: "Struck Off",  data: 5 },
		    { label: "On Leave",  data: 3 }
		],

		// will hold the chart object
		plot: null,

		// chart options
		options: 
		{
			series: {
				pie: { 
					show: true,
					highlight: {
						opacity: 0.1
					},
					radius: 1,
					stroke: {
						color: '#fff',
						width: 2
					},
					startAngle: 2,
				    combine: {
	                    color: '#353535',
	                    threshold: 0.05
	                },
	                label: {
	                    show: true,
	                    radius: 1,
	                    formatter: function(label, series){
	                        return '<div class="label label-inverse">'+label+'&nbsp;'+Math.round(series.percent)+'%</div>';
	                    }
	                }
				},
				grow: {	active: false}
			},
			colors: [],
			legend:{show:false},
			grid: {
	            hoverable: true,
	            clickable: true,
	            backgroundColor : { }
	        },
	        tooltip: true,
			tooltipOpts: {
				content: "%s : %y.1"+"%",
				shifts: {
					x: -30,
					y: -50
				},
				defaultTheme: false
			}
		},
		
		placeholder: "#chart_pie",
		
		// initialize
		init: function()
		{
			// apply styling
			charts.utility.applyStyle(this);
			
			this.plot = $.plot($(this.placeholder), this.data, this.options);
		}
	};

	charts.chart_pie.init();
})(jQuery);