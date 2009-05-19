<?php
/**
 * Класс построителя Pie chart
 *
 * @author   Евгений Панин <varenich@gmail.com>, Люберцы, Россия, 2008
 * @link  http://www.usefulclasses.com, http://www.phpAddDict.com
 * @package  GraphReport
 * @version  1.0
 */

require_once 'Image/Graph.php';

/**
 * Строит график Pie chart
 * 
 * @package  GraphReport
 * @access   public
 *
 */ 
class GraphReport_pie extends GraphReport implements iGraphReport  {

	/**
   * Строит график Pie chart
   *
   * @param mixed $data Данные
   * @return string График
   * @throws Exception
   * @access public
   */
	public function build($data) {
		
		/**$Graph = Image_Graph::factory('graph', array(400, 300));
		
		$Plotarea = $Graph->addNew('plotarea'); 
		
		$Dataset = Image_Graph::factory('dataset');
		$Dataset->addPoint('Denmark', 10);
		$Dataset->addPoint('Norway', 3);
		$Dataset->addPoint('Sweden', 8);
		$Dataset->addPoint('Finland', 5);
		
		
		$Plot = $Plotarea->addNew('bar', $Dataset);
		
		//pre($Dataset);
		//pre($Plotarea);
		
		$Graph->done(); 
		*/
		
		
		// create the graph
		$Graph =& Image_Graph::factory('graph', array(400, 300));

		// add a TrueType font
		$Font =& $Graph->addNew('font', 'Verdana');
		// set the font size to 7 pixels
		$Font->setSize(7);

		$Graph->setFont($Font);

		// create the plotarea
		$Graph->add(
		    Image_Graph::vertical(
		        Image_Graph::factory('title', array('Meat Export', 12)),
		        Image_Graph::horizontal(
		            $Plotarea = Image_Graph::factory('plotarea'),
		            $Legend = Image_Graph::factory('legend'),
		            70
		        ),
		        5            
		    )
		); 
		//$Legend->setPlotarea($Plotarea);
		//$Legend->setAlignment(IMAGE_GRAPH_ALIGN_VERTICAL); 

		// create the 1st dataset
		$Dataset1 =& Image_Graph::factory('dataset');
		$Dataset1->addPoint('Beef', rand(1, 10),'beef');
		$Dataset1->addPoint('Pork', rand(1, 10),'pork');
		$Dataset1->addPoint('Poultry', rand(1, 10),'poultry');
		$Dataset1->addPoint('Camels', rand(1, 10),'camels');
		$Dataset1->addPoint('Other', rand(1, 10),'other');
		// create the 1st plot as smoothed area chart using the 1st dataset
		$Plot =& $Plotarea->addNew('pie', array(&$Dataset1));
		$Plotarea->hideAxis();

		// create a Y data value marker
		$Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_PCT_Y_TOTAL);
		// create a pin-point marker type
		$PointingMarker =& $Plot->addNew('Image_Graph_Marker_Pointing_Angular', array(20, &$Marker));
		// and use the marker on the 1st plot
		$Plot->setMarker($PointingMarker);
		// format value marker labels as percentage values
		$Marker->setDataPreprocessor(Image_Graph::factory('Image_Graph_DataPreprocessor_Formatted', '%0.1f%%'));

		$Plot->Radius = 2;

		$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
		$Plot->setFillStyle($FillArray);
		$FillArray->addNew('gradient', array(IMAGE_GRAPH_GRAD_RADIAL, 'white', 'green'));
		$FillArray->addNew('gradient', array(IMAGE_GRAPH_GRAD_RADIAL, 'white', 'blue'));
		$FillArray->addNew('gradient', array(IMAGE_GRAPH_GRAD_RADIAL, 'white', 'yellow'));
		$FillArray->addNew('gradient', array(IMAGE_GRAPH_GRAD_RADIAL, 'white', 'red'));
		$FillArray->addNew('gradient', array(IMAGE_GRAPH_GRAD_RADIAL, 'white', 'orange'));

		$Plot->explode(5);

		$Plot->setStartingAngle(90);

		// output the Graph
		$Graph->done();
		

	} // build

} // class
?>