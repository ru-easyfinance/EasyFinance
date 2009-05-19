<?php
/**
 * Класс построителя Pie chart на основе jpgraph
 *
 * @author   Евгений Панин <varenich@gmail.com>, Люберцы, Россия, 2008
 * @link  http://www.usefulclasses.com, http://www.phpAddDict.com
 * @package  GraphReport
 * @version  1.0
 */

include ("jpgraph/jpgraph.php");
include ("jpgraph/jpgraph_pie.php");
include ("jpgraph/jpgraph_pie3d.php");

/**
 * Строит график Pie chart
 * 
 * @package  GraphReport
 * @access   public
 *
 */ 
class GraphReport_jpgraph_pie extends GraphReport implements iGraphReport  {

	/**
   * Строит график Pie chart
   *
   * @param mixed $data Данные
   * @return string График
   * @throws Exception
   * @access public
   */
	public function build($data) {
		$vals =  array_values($data);
		$keys = array_keys($data);

		// Преобразовываем к процентным соотношениям
		//$s = array_sum($vals);
		//foreach ($vals as $i=>$v) $vals[$i] = round(($v*100)/$s,2);

		// Some data
		$data1 = array(40,21,17,14,23);

		// Create the Pie Graph.
		$graph = new PieGraph(600,300,"auto");
		$graph->SetShadow();

		// Set A title for the plot
		$graph->title->Set("Соотношение доходов");
		$graph->title->SetFont(FF_ARIAL,FS_BOLD);

		// Create
		$pp = new PiePlot3d($vals);
		$pp->SetTheme("water");
		//$pp->SetCenter(0.4);
		//$pp->SetAngle(30); 
		$pp->ExplodeAll(); 
		$pp->SetLegends($keys);
		//$pp->SetLabelType(PIE_VALUE_ABS); 
		
		$graph->legend->SetFont(FF_ARIAL);
		$graph->Add($pp);
		$graph->Stroke();


		

		//echo  $img;
	} // build

} // class


?>