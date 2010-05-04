<?php
define('DIR_LIB_AMTPDF', dirname(__FILE__));
if (!file_exists($dir = DIR_LIB_AMTPDF.'/../pdfGen/cache')) mkdir($dir);
if (!file_exists($dir = DIR_LIB_AMTPDF.'/../pdfGen/temp')) mkdir($dir);


function createPDF($lname, $fname, $mname, $lname_lat, $fname_lat, $birth_date, $birth_place, $gender, $citizenship, $inn, $country, $zip, $region, $city, $street, $house, $building, $app, $country_reg, $zip_reg, $region_reg, $city_reg, $street_reg, $house_reg, $building_reg, $app_reg, $passport_serie, $passport_number, $passport_given, $passport_code, $passport_date, $doc_title, $doc_serie, $doc_number, $doc_given, $doc_date, $doc_valid, $contact_phone, $contact_email, $contact_mobile, $contact_other, $work_company, $work_title, $work_address, $work_phone, $card_mode, $card_currency, $card_type, $card_urgency, $card_sms, $card_receipt_office, $card_receipt_email, $card_email, $add_name, $add_number, $add_limit, $add_14_type, $add_14_given, $password) {

ob_start();
    require(DIR_LIB_AMTPDF.'/template/page.php');
$page = ob_get_clean();

// -----------------------------------------------------------------------------
error_reporting(E_ALL);
require_once(DIR_LIB_AMTPDF.'/../pdfGen/config.inc.php');
require_once(HTML2PS_DIR.'pipeline.factory.class.php');
parse_config_file(HTML2PS_DIR.'html2ps.config');


class MyFetcher extends Fetcher {
  private $_content;

  function __construct($data) {
    $this->_content = $data;
  }

  function get_data() {
    return new FetchedDataURL($this->_content, array(), "");
  }

  function get_base_url() {
    '';
  }
}

$pipeline = PipelineFactory::create_default_pipeline('', '');
$pipeline->fetchers[] = new MyFetcher($page);
$tmp = tempnam(sys_get_temp_dir(), 'amtanketa');
$pipeline->destination = new DestinationBrowser($tmp);

$media = Media::predefined("A4");
$media->set_landscape(false);
$media->set_margins(array('left'   => 0,
                          'right'  => 0,
                          'top'    => 0,
                          'bottom' => 0));
$media->set_pixels(1280);

$GLOBALS['g_config'] = array(
    'cssmedia'     => 'screen',
    'scalepoints'  => '1',
    'renderimages' => true,
    'renderlinks'  => true,
    'renderfields' => true,
    'renderforms'  => false,
    'mode'         => 'html',
    'encoding'     => '',
    'debugbox'     => false,
    'pdfversion'    => '1.3',
    'draw_page_border' => false,
    'method'       => 'fpdf',
);
$pipeline->process('', $media);

unlink($tmp);
};
