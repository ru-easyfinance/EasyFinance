<?php
/**
 * Шаблон 500 ошибки
 *
 * почему-то без установки header-а не устанавливается заголовок, хотя должен
 * @see sfException::outputStackTrace#174
 */

    header('Content-Type: text/xml; charset='.sfConfig::get('sf_charset', 'utf-8'));
?>
<?xml version="1.0" encoding="utf-8"?>
<response server_time="<?php echo date('c'); ?>">
    <error code="500">Internal Server Error</error>
</response>
