function reloadChart()
{
	$form = $('chart_form');
	if($form) {
		return $form.sendPhpr('index_onUpdateChart',
			{
				update       : $('report_chart'),
				loadIndicator: {show: false}
			}
		);
	}
}

function updateReportData(reload_chart_object)
{
	listReload(false);
	reloadTotals();
	reloadChart();
}

function reloadTotals()
{
	return $('report_form').sendPhpr('index_onUpdateTotals',
		{
			update: $('report_totals'), 
			loadIndicator: {
				element: $('content'),
				hideOnSuccess: true,
				overlayOpacity: 0.8,
				overlayClass: 'whiteOverlay',
				hideElement: false,
				src: 'modules/backend/resources/images/loading_global.gif'
			},
		}
	);
}


function reportSetParameter(name, value)
{
	$('report_form').sendPhpr('index_onSetReportParameter', {
		extraFields: {'param': name, 'value': value}, 
		loadIndicator: {show: false}, 
		onSuccess: updateReportData.pass(false)
	});
}





