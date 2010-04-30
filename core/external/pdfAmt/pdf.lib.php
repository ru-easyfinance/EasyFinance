<?php
define('DIR_LIB_AMTPDF', dirname(__FILE__));
if (!file_exists($dir = DIR_LIB_AMTPDF.'/../pdfGen/cache')) mkdir($dir);
if (!file_exists($dir = DIR_LIB_AMTPDF.'/../pdfGen/temp')) mkdir($dir);


function createPDF($lname, $fname, $mname, $lname_lat, $fname_lat, $birth_date, $birth_place, $gender, $citizenship, $inn, $country, $zip, $region, $city, $street, $house, $building, $app, $country_reg, $zip_reg, $region_reg, $city_reg, $street_reg, $house_reg, $building_reg, $app_reg, $passport_serie, $passport_number, $passport_given, $passport_code, $passport_date, $doc_title, $doc_serie, $doc_number, $doc_given, $doc_date, $doc_valid, $contact_phone, $contact_email, $contact_mobile, $contact_other, $work_company, $work_title, $work_address, $work_phone, $card_mode, $card_currency, $card_type, $card_urgency, $card_sms, $card_receipt_office, $card_receipt_email, $card_email, $add_name, $add_number, $add_limit, $add_14_type, $add_14_given) {

$data_array=$lname;
$data_array.='$$$'.$fname;
$data_array.='$$$'.$mname;
$data_array.='$$$'.$lname_lat;
$data_array.='$$$'.$fname_lat;
$data_array.='$$$'.$birth_date;
$data_array.='$$$'.$birth_place;
$data_array.='$$$'.$gender;
$data_array.='$$$'.$citizenship;
$data_array.='$$$'.$inn;
$data_array.='$$$'.$country;
$data_array.='$$$'.$zip;
$data_array.='$$$'.$region;
$data_array.='$$$'.$city;
$data_array.='$$$'.$street;
$data_array.='$$$'.$house;
$data_array.='$$$'.$building;
$data_array.='$$$'.$app;
$data_array.='$$$'.$country_reg;
$data_array.='$$$'.$zip_reg;
$data_array.='$$$'.$region_reg;
$data_array.='$$$'.$city_reg;
$data_array.='$$$'.$street_reg;
$data_array.='$$$'.$house_reg;
$data_array.='$$$'.$building_reg;
$data_array.='$$$'.$app_reg;
$data_array.='$$$'.$passport_serie;
$data_array.='$$$'.$passport_number;
$data_array.='$$$'.$passport_given;
$data_array.='$$$'.$passport_code;
$data_array.='$$$'.$passport_date;
$data_array.='$$$'.$doc_title;
$data_array.='$$$'.$doc_serie;
$data_array.='$$$'.$doc_number;
$data_array.='$$$'.$doc_given;
$data_array.='$$$'.$doc_date;
$data_array.='$$$'.$doc_valid;
$data_array.='$$$'.$contact_phone;
$data_array.='$$$'.$contact_email;
$data_array.='$$$'.$contact_mobile;
$data_array.='$$$'.$contact_other;
$data_array.='$$$'.$work_company;
$data_array.='$$$'.$work_title;
$data_array.='$$$'.$work_address;
$data_array.='$$$'.$work_phone;
$data_array.='$$$'.$card_mode;
$data_array.='$$$'.$card_currency;
$data_array.='$$$'.$card_type;
$data_array.='$$$'.$card_urgency;
$data_array.='$$$'.$card_sms;
$data_array.='$$$'.$card_receipt_office;
$data_array.='$$$'.$card_receipt_email;
$data_array.='$$$'.$card_email;
$data_array.='$$$'.$add_name;
$data_array.='$$$'.$add_number;
$data_array.='$$$'.$add_limit;
$data_array.='$$$'.$add_14_type;
$data_array.='$$$'.$add_14_given;

file_put_contents(DIR_LIB_AMTPDF.'/template/data', $data_array);

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
