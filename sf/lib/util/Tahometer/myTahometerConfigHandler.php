<?php
/**
 * myTahometerConfigHandler
 */
class myTahometerConfigHandler extends sfYamlConfigHandler
{
    // Умолчальные цвета
    private $_colors = array('red' => 'f00', 'green' => '0f0', 'yellow' => 'fc0');

    private $_parsedConfigData = array();


    /**
     * Подготовить конфигурацию тахометров
     *
     * @param   array   $tahometerConfig
     * @return  array
     */
    protected function prepareTahometers(array $tahometerConfig)
    {
        $data = array();
        $data[] = "\$tahometerConfig = array();\n";

        $realTahometerNames = array(
            myTahometer::NAME_MONEY,
            myTahometer::NAME_BUDGET,
            myTahometer::NAME_LOANS,
            myTahometer::NAME_DIFF,
            myTahometer::NAME_TOTAL,
        );

        // TODO: проверка всех параметров
        foreach ($tahometerConfig as $tahometerName => $settings) {
            if (!in_array($tahometerName, $realTahometerNames)) {
                throw new sfConfigurationException(sprintf('Tahometers configuration: unknown Tahometer %s', $tahometerName));
            }
            $data[] = "\$tahometerConfig['" . $tahometerName . "'] = " . var_export($settings, true) . ";";
        }

        $this->_parsedConfigData = array_merge($data, $this->_parsedConfigData);

        return $data;
    }


    /**
     * Пропарсить дополнительные глобальные параметры
     */
    protected function prepareGlobals(array $config = array())
    {
        $colors = isset($config['colors']) ? (array) $config['colors'] : array();
        $colors = array_merge($this->_colors, $colors);

        $colorsData = array("\n\n// Color settings for styling, t2078\n\$colors = array();");
        $colorsData[] = sprintf("\$colors[] = \"%s\"; // Red zone color"    , $colors['red']);
        $colorsData[] = sprintf("\$colors[] = \"%s\"; // Yellow zone color" , $colors['yellow']);
        $colorsData[] = sprintf("\$colors[] = \"%s\"; // Green zone color"  , $colors['green']);
        $colorsData[] = "// End of color settings";

        $this->_parsedConfigData = array_merge($colorsData, $this->_parsedConfigData);
    }


    /**
     * Execute
     *
     * @param   mixed   $configFiles
     * @return  string
     */
    public function execute($configFiles)
    {
        // Parse yaml config files
        $configs = self::parseYamls($configFiles);

        $tahometerConfig = isset($configs['Tahometers']) ? $configs['Tahometers'] : array();

        if ((!count($tahometerConfig) >= 5) || !isset($configs['global'])) {
            throw new sfConfigurationException('Error while parsing Tahometers configuration.');
        }

        $this->prepareTahometers($tahometerConfig);

        $this->prepareGlobals((array) $configs['global']);

        // compile data
        $retval = sprintf("<?php\n" .
        "// auto-generated by %s\n" .
        "// @author Svel <svel.sontz@gmail.com>\n" .
        "// @see https://easyfinance.ru\n" .
        "// date: %s\n%s\n", __CLASS__, date('Y-m-d H:i:s'), implode("\n", $this->_parsedConfigData));

        return $retval;
    }

}