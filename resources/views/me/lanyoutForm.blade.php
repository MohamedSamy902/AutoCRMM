@extends('layouts.admin.master')

@section('title')
    Default Forms
    {{-- {{ $title }} --}}
@endsection

@push('css')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<!-- Flowchart CSS and JS -->
<link rel="stylesheet" href="{{ asset('assets/css/jquery.flowchart.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/jquery.flowchart.css') }}">
<script src="{{ asset('assets/js/jquery.flowchart.js') }}"></script>

<style>
    .flowchart-example-container {
        width: 800px;
        height: 400px;
        background: white;
        border: 1px solid #BBB;
        margin-bottom: 10px;
    }
</style>
@endpush

@section('content')

<div id="chart_container">
    <div class="flowchart-example-container" id="flowchartworkspace"></div>
</div>
<div class="draggable_operators">
    <!-- <div class="draggable_operators_label">
        Operators (drag and drop them in the flowchart):
    </div> -->
    <!-- <div class="draggable_operators_divs">
        <div class="draggable_operator" data-nb-inputs="1" data-nb-outputs="0">1 input</div>
        <div class="draggable_operator" data-nb-inputs="0" data-nb-outputs="1">1 output</div>
        <div class="draggable_operator" data-nb-inputs="1" data-nb-outputs="1">1 input &amp; 1 output</div>
        <div class="draggable_operator" data-nb-inputs="1" data-nb-outputs="2">1 in &amp; 2 out</div>
        <div class="draggable_operator" data-nb-inputs="2" data-nb-outputs="1">2 in &amp; 1 out</div>
        <div class="draggable_operator" data-nb-inputs="2" data-nb-outputs="2">2 in &amp; 2 out</div>
    </div> -->
</div>



@endsection

@push('scripts')
<script type="text/javascript">
    /* global $ */
    $(document).ready(function () {
        var $flowchart = $('#flowchartworkspace');
        var $container = $flowchart.parent();


        // Apply the plugin on a standard, empty div...
        $flowchart.flowchart({
            data: defaultFlowchartData,
            defaultSelectedLinkColor: '#000055',
            grid: 10,
            multipleLinksOnInput: true,
            multipleLinksOnOutput: true
        });


        // function getOperatorData($element) {
        // 	var nbInputs = parseInt($element.data('nb-inputs'), 10);
        // 	var nbOutputs = parseInt($element.data('nb-outputs'), 10);
        // 	var data = {
        // 		properties: {
        // 			title: $element.text(),
        // 			inputs: {},
        // 			outputs: {}
        // 		}
        // 	};

        // 	var i = 0;
        // 	for (i = 0; i < nbInputs; i++) {
        // 		data.properties.inputs['input_' + i] = {
        // 			label: 'Input ' + (i + 1)
        // 		};
        // 	}
        // 	for (i = 0; i < nbOutputs; i++) {
        // 		data.properties.outputs['output_' + i] = {
        // 			label: 'Output ' + (i + 1)
        // 		};
        // 	}

        // 	return data;
        // }



        //-----------------------------------------
        //--- operator and link properties
        //--- start
        // var $operatorProperties = $('#operator_properties');
        // $operatorProperties.hide();
        // var $linkProperties = $('#link_properties');
        // $linkProperties.hide();
        // var $operatorTitle = $('#operator_title');
        // var $linkColor = $('#link_color');

        // $flowchart.flowchart({
        // 	onOperatorSelect: function (operatorId) {
        // 		$operatorProperties.show();
        // 		$operatorTitle.val($flowchart.flowchart('getOperatorTitle', operatorId));
        // 		return true;
        // 	},
        // 	onOperatorUnselect: function () {
        // 		$operatorProperties.hide();
        // 		return true;
        // 	},
        // 	onLinkSelect: function (linkId) {
        // 		$linkProperties.show();
        // 		$linkColor.val($flowchart.flowchart('getLinkMainColor', linkId));
        // 		return true;
        // 	},
        // 	onLinkUnselect: function () {
        // 		$linkProperties.hide();
        // 		return true;
        // 	}
        // });

        // $operatorTitle.keyup(function () {
        // 	var selectedOperatorId = $flowchart.flowchart('getSelectedOperatorId');
        // 	if (selectedOperatorId != null) {
        // 		$flowchart.flowchart('setOperatorTitle', selectedOperatorId, $operatorTitle.val());
        // 	}
        // });

        // $linkColor.change(function () {
        // 	var selectedLinkId = $flowchart.flowchart('getSelectedLinkId');
        // 	if (selectedLinkId != null) {
        // 		$flowchart.flowchart('setLinkMainColor', selectedLinkId, $linkColor.val());
        // 	}
        // });
        // //--- end
        //--- operator and link properties
        //-----------------------------------------

        //-----------------------------------------
        //--- delete operator / link button
        //--- start
        // $flowchart.parent().siblings('.delete_selected_button').click(function () {
        // 	$flowchart.flowchart('deleteSelected');
        // });
        //--- end
        //--- delete operator / link button
        //-----------------------------------------



        //-----------------------------------------
        //--- create operator button
        //--- start
        // var operatorI = 0;
        // $flowchart.parent().siblings('.create_operator').click(function () {
        // 	var operatorId = 'created_operator_' + operatorI;
        // 	var operatorData = {
        // 		top: ($flowchart.height() / 2) - 30,
        // 		left: ($flowchart.width() / 2) - 100 + (operatorI * 10),
        // 		properties: {
        // 			title: 'Operator ' + (operatorI + 3),
        // 			inputs: {
        // 				input_1: {
        // 					label: 'Input 1',
        // 				}
        // 			},
        // 			outputs: {
        // 				output_1: {
        // 					label: 'Output 1',
        // 				}
        // 			}
        // 		}
        // 	};

        // 	operatorI++;

        // 	$flowchart.flowchart('createOperator', operatorId, operatorData);

        // });
        // //--- end
        //--- create operator button
        //-----------------------------------------




        //-----------------------------------------
        //--- draggable operators
        //--- start
        //var operatorId = 0;
        // var $draggableOperators = $('.draggable_operator');
        // $draggableOperators.draggable({
        // 	cursor: "move",
        // 	opacity: 0.7,

        // 	// helper: 'clone',
        // 	appendTo: 'body',
        // 	zIndex: 1000,

        // 	helper: function (e) {
        // 		var $this = $(this);
        // 		var data = getOperatorData($this);
        // 		return $flowchart.flowchart('getOperatorElement', data);
        // 	},
        // 	stop: function (e, ui) {
        // 		var $this = $(this);
        // 		var elOffset = ui.offset;
        // 		var containerOffset = $container.offset();
        // 		if (elOffset.left > containerOffset.left &&
        // 			elOffset.top > containerOffset.top &&
        // 			elOffset.left < containerOffset.left + $container.width() &&
        // 			elOffset.top < containerOffset.top + $container.height()) {

        // 			var flowchartOffset = $flowchart.offset();

        // 			var relativeLeft = elOffset.left - flowchartOffset.left;
        // 			var relativeTop = elOffset.top - flowchartOffset.top;

        // 			var positionRatio = $flowchart.flowchart('getPositionRatio');
        // 			relativeLeft /= positionRatio;
        // 			relativeTop /= positionRatio;

        // 			var data = getOperatorData($this);
        // 			data.left = relativeLeft;
        // 			data.top = relativeTop;

        // 			$flowchart.flowchart('addOperator', data);
        // 		}
        // 	}
        // });
        // //--- end
        //--- draggable operators
        //-----------------------------------------


        //-----------------------------------------
        //--- save and load
        //--- start
        // function Flow2Text() {
        // 	var data = $flowchart.flowchart('getData');
        // 	$('#flowchart_data').val(JSON.stringify(data, null, 2));
        // }
        // $('#get_data').click(Flow2Text);

        // function Text2Flow() {
        // 	var data = JSON.parse($('#flowchart_data').val());
        // 	$flowchart.flowchart('setData', data);
        // }
        // $('#set_data').click(Text2Flow);

        /*global localStorage*/
        // function SaveToLocalStorage() {
        // 	if (typeof localStorage !== 'object') {
        // 		alert('local storage not available');
        // 		return;
        // 	}
        // 	Flow2Text();
        // 	localStorage.setItem("stgLocalFlowChart", $('#flowchart_data').val());
        // }
        // $('#save_local').click(SaveToLocalStorage);

        // function LoadFromLocalStorage() {
        // 	if (typeof localStorage !== 'object') {
        // 		alert('local storage not available');
        // 		return;
        // 	}
        // 	var s = localStorage.getItem("stgLocalFlowChart");
        // 	if (s != null) {
        // 		$('#flowchart_data').val(s);
        // 		Text2Flow();
        // 	}
        // 	else {
        // 		alert('local storage empty');
        // 	}
        // }
        $('#load_local').click(LoadFromLocalStorage);
        //--- end
        //--- save and load
        //-----------------------------------------


    });

    var defaultFlowchartData = {
        operators: {
            operator1: {
                top: 20,
                left: 20,
                properties: {
                    title: 'Operator 1',
                    inputs: {},
                    outputs: {
                        output_1: {
                            label: 'Output 1',
                        },

                        output_2: {
                            label: 'Output 1',
                        },

                        output_3: {
                            label: 'Output 1',
                        },
                        output_4: {
                            label: 'Output 1',
                        }

                    }
                }
            },
            operator2: {
                top: 80,
                left: 300,
                properties: {
                    title: 'Operator 2',
                    inputs: {
                        input_1: {
                            label: 'Input 1',
                        },
                        input_2: {
                            label: 'Input 2',
                        },
                    },
                    outputs: {}
                }
            },
        },
        links: {
            link_1: {
                fromOperator: 'operator1',
                fromConnector: 'output_1',
                toOperator: 'operator2',
                toConnector: 'input_2',
            },

            link_2: {
                fromOperator: 'operator1',
                fromConnector: 'output_2',
                toOperator: 'operator2',
                toConnector: 'input_2',
            },
        }
    };
    // if (false) console.log('remove lint unused warning', defaultFlowchartData);
</script>
    @endpush
