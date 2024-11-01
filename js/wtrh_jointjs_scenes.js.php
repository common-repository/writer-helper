<?php
	$nodes = getArray_GameBook($book->id, false);
	$existsStoryboard = false;
	if( class_exists("WH_Storyboard") )
		if( WH_Storyboard::get_BookStoryboard($book->id) !== false )
			$existsStoryboard = true;
?>
<div id='wh_grapData'>
	<script type="text/javascript">
		var graphData = <?php echo json_encode($nodes); ?> ;
	</script>
</div>
<div id='graphDiv'></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js"></script>
<script src="<?php echo WTRH_JS_URL; ?>/jointjs/dagre.min.js"></script>
<script src="<?php echo WTRH_JS_URL; ?>/jointjs/lodash.js"></script>
<script src="<?php echo WTRH_JS_URL; ?>/jointjs/backbone.js"></script>
<script src="<?php echo WTRH_JS_URL; ?>/jointjs/joint.js"></script>
<script src="<?php echo WTRH_JS_URL; ?>/jointjs/joint.layout.DirectedGraph.min.js"></script>

<script type="text/javascript">
	// Init
	var wtr_labelpos    = "c";
	var wtr_minlen      = 1;
	var wtr_weight      = 1;
	var wtr_labeloffset = 10;
	var wtr_ranker      = "network-simplex"; // network-simplex, tight-tree, longer-path
	var wtr_rankdir     = "LR"; // TB:top-bottom, BT: bottom-top, RL: right-left, LR: left-right
	var wtr_align       = "UL"  // UL: up-left, UR: down-right, DL: down-left, DR: down-right
	var wtr_ranksep     = 60;   // rank separator
	var wtr_edgesep     = 20;
	var wtr_nodesep     = 50;

	function createGraph(graphData) {
		var Shape = joint.dia.Element.define('demo.Shape', {
			z: 2,
			size: {
				width: 30,
				height: 30
			},
			attrs: {
				body: {
					refWidth: '100%',
					refHeight: '100%',
					fill: 'ivory',
					stroke: 'gray',
					strokeWidth: 2,
					rx: 50,
					ry: 50
				},
				label: {
					refX: '50%',
					refY: '-40%',
					yAlignment: 'middle',
					xAlignment: 'middle',
					fontSize: 14
				}
			}
		}, {
			markup: [{
				tagName: 'rect',
				selector: 'body'
			}, {
				tagName: 'text',
				selector: 'label'
			}],

			setColor: function(color, border) {
				this.attr('body/stroke', border || 'black');
				return this.attr('body/fill', color || 'GhostWhite');
			},

			setText: function(text) {
				return this.attr('label/text', text || '');
			}
		});

		var Link = joint.dia.Link.define('demo.Link', {
			attrs: {
				line: {
					connection: true,
					stroke: 'gray',
					strokeWidth: 2,
					pointerEvents: 'none',
					targetMarker: {
						type: 'path',
						fill: 'gray',
						stroke: 'none',
						d: 'M 10 -10 0 0 10 10 z'
					}
				}
			},
			connector: {
				name: 'rounded'
			},
			z: 1,
			weight: 1,
			minLen: 1,
			labelPosition: 'c',
			labelOffset: 10,
			labelSize: {
				width: 50,
				height: 30
			},
			labels: []

		}, {

			markup: [{
				tagName: 'path',
				selector: 'line',
				attributes: {
					'fill': 'none'
				}
			}],

			connect: function(sourceId, targetId, color) {
				this.attributes.attrs.line.stroke = color;
				this.attributes.attrs.line.targetMarker.fill = color;
				return this.set({
					source: { id: sourceId },
					target: { id: targetId }
				});
			},

			setLinkColor: function(color) {
				this.attributes.attrs.line.stroke = color;
				this.attributes.attrs.line.targetMarker.fill = color;
			},

			setLabelSize: function(width, height) {
				this.labelSize.width  = width;
				this.labelSize.height = height;
			},
			
			setLabelText: function(text) {
				return this.prop('labels/0/attrs/labelText/text', text || '');
			}
		});

		var LayoutControls = joint.mvc.View.extend({

			events: {
				change: 'onChange',
				input: 'onChange'
			},

			options: {
				padding: 50
			},

			init: function() {

				var options = this.options;
				if (options.adjacencyList) {
					options.cells = this.buildGraphFromAdjacencyList(options.adjacencyList);
				}

				this.listenTo(options.paper.model, 'change', function(_, opt) {
					if (opt.layout) this.layout();
				});
			},

			onChange: function() {
				this.layout();
				this.trigger('layout');
			},

			layout: function() {

				var paper = this.options.paper;
				var graph = paper.model;
				var cells = this.options.cells;

				paper.freeze();
		console.log(cells);
				joint.layout.DirectedGraph.layout(cells, this.getLayoutOptions());

				if (graph.getCells().length === 0) {
					// The graph could be empty at the beginning to avoid cells rendering
					// and their subsequent update when elements are translated
					graph.resetCells(cells);
				}

				paper.fitToContent({
					padding: this.options.padding,
					allowNewOrigin: 'any',
					useModelGeometry: true
				});

				paper.unfreeze();
			},

			getLayoutOptions: function() {
				return {
					dagre: dagre,
					graphlib: dagre.graphlib,
					setVertices: true,
					setLabels: true,
					ranker: wtr_ranker,
					rankDir: wtr_rankdir,
					align: wtr_align,
					rankSep: parseInt(wtr_ranksep, 10),
					edgeSep: parseInt(wtr_edgesep, 10),
					nodeSep: parseInt(wtr_nodesep, 10)
				};
			},

			buildGraphFromAdjacencyList: function(adjacencyList) {

				var elements = [];
				var links = [];

				adjacencyList.forEach(function(line) {
		//console.log(line);
					var parentId    = line['id'];
					var parentLabel = line['desc'];
					var parentColor = line['color'];
					// Add element
					elements.push(
						new Shape({ id: parentId })
								.setColor(parentColor, line['border'])
								.setText(parentLabel)
					);
					// Add links
					line['children'].forEach(function(childArray) {
						links.push(
							new Link()
								.connect(parentId, childArray['id'], childArray['color'])
								//.setLabelText()
						);
					});
				});

				// Links must be added after all the elements. This is because when the links
				// are added to the graph, link source/target
				// elements must be in the graph already.
				return elements.concat(links);
			}

		});


		var LinkControls = joint.mvc.View.extend({

			highlighter: {
				name: 'stroke',
				options: {
					attrs: {
						'stroke': 'lightcoral',
						'stroke-width': 4
					}
				}
			},

			events: {
				change: 'updateLink',
				input: 'updateLink'
			},

			init: function() {
				this.highlight();
				this.updateControls();
			},

			updateLink: function() {
				this.options.cellView.model.set(this.getModelAttributes(), { layout: true });
				this.constructor.refresh();
			},

			updateControls: function() {

				var link = this.options.cellView.model;

				wtr_labelpos=link.get('labelPosition');
				wtr_labeloffset=link.get('labelOffset');
				wtr_minlen=link.get('minLen');
				wtr_weight=link.get('weight');
			},

			getModelAttributes: function() {
				return {
					minLen: parseInt(wtr_minlen, 10),
					weight: parseInt(wtr_weight, 10),
					labelPosition: wtr_labelpos,
					labelOffset: parseInt(wtr_labeloffset, 10)
				};
			},

			onRemove: function() {
				this.unhighlight();
			},

			highlight: function() {
				this.options.cellView.highlight('rect', { highlighter: this.highlighter });
			},

			unhighlight: function() {
				this.options.cellView.unhighlight('rect', { highlighter: this.highlighter });
			}

		}, {

			create: function(linkView) {
		 /*    
				this.remove();
				this.instance = new this({
					el: this.template.cloneNode(true),
					cellView: linkView
				});
				this.instance.$el.insertAfter('#layout-controls');*/
			},

			remove: function() {
				if (this.instance) {
					this.instance.remove();
					this.instance = null;
				}
			},

			refresh: function() {
				if (this.instance) {
					this.instance.unhighlight();
					this.instance.highlight();
				}
			},

			instance: null,

			template: null
		});

		var controls = new LayoutControls({
		//    el: document.getElementById('layout-controls'),
			el: null,
			paper: new joint.dia.Paper({
				el: document.getElementById('graphDiv'),
				sorting: joint.dia.Paper.sorting.APPROX,
				interactive: function(cellView) {
					return cellView.model.isElement();
				}
			}).on({
				'link:pointerdown': LinkControls.create,
				'blank:pointerdown element:pointerdown': LinkControls.remove
			}, LinkControls),
			adjacencyList: graphData
		}).on({
			'layout': LinkControls.refresh
		}, LinkControls);

		controls.layout();
	}
<?php if( ! $existsStoryboard ) { ?>
	createGraph(graphData);
<?php }?>
</script>

<button onclick='wtr_refreshGraphData(<?php echo $book->id; ?>)'>
	<?php _e('See/Refresh the graph of scenes','wtr_helper');?>
</button>