<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Валидатор даты времени в формате ISO 8601
 * Не поддерживаются большинство форматов кроме: 2010-05-21T18:55:30+04:00
 */
class validator_myValidatorDatetimeIso8601Test extends myUnitTestCase
{
    /**
     * @see http://www.pelagodesign.com/blog/2009/05/20/iso-8601-date-validation-that-doesnt-suck/
     */
    public function plan()
    {
        return array(
            // Будем поддерживать только те форматы, которые передают часовой пояс
            array('2009-05-19T14:39Z', true),
            array('20090621T0545Z', true),
            array('2009-05-19 14:39:22-06:00', true),
            array('2009-05-19 14:39:22+0600', true),
            array('2009-05-19T14:39:22+0600', true),
            array('2009-05-19 14:39:22-01', true),
            array('0000-00-00T00:00:00+00', true),
            array('0000-00-00 00:00:00+0000', true),

            // Несуществующая дата/время
            array('2009-21-19T14:39:22+0600', false),
            array('2009-05-19T14:80:22+0600', false),

            // Должен пропускать, но мы не будем делать этого
            array('2009-12T12:34', false),
            array('2009', false),
            array('2009-05-19', false),
            array('2009-05-19', false),
            array('20090519', false),
            array('2009123', false),
            array('2009-05', false),
            array('2009-123', false),
            array('2009-222', false),
            array('2009-001', false),
            array('2009-W01-1', false),
            array('2009-W51-1', false),
            array('2009-W511', false),
            array('2009-W33', false),
            array('2009W511', false),
            array('2009-05-19', false),
            array('2009-05-19 00:00', false),
            array('2009-05-19 14', false),
            array('2009-05-19 14:31', false),
            array('2009-05-19 14:39:22', false),
            array('2009-W21-2', false),
            array('2009-W21-2T01:22', false),
            array('2009-139', false),
            array('2007-04-06T00:00', false),
            array('2007-04-05T24:00', false),
            array('2010-02-18T16:23:48.5', false),
            array('2010-02-18T16:23:48,444', false),
            array('2010-02-18T16:23:48,3-06:00', false),
            array('2010-02-18T16:23.4', false),
            array('2010-02-18T16:23,25', false),
            array('2010-02-18T16:23.33+0600', false),
            array('2010-02-18T16.23334444', false),
            array('2010-02-18T16,2283', false),
            array('2009-05-19 143922.500', false),
            array('2009-05-19 1439,55', false),

            // Не должен пропускать
            array('200905', false),
            array('2009367', false),
            array('2009-', false),
            array('2007-04-05T24:50', false),
            array('2009-000', false),
            array('2009-M511', false),
            array('2009M511', false),
            array('2009-05-19T14a39r', false),
            array('2009-05-19T14:3924', false),
            array('2009-0519', false),
            array('2009-05-1914:39', false),
            array('2009-05-19 14:', false),
            array('2009-05-19r14:39', false),
            array('2009-05-19 14a39a22', false),
            array('200912-01', false),
            array('2009-05-19 14:39:22+06a00', false),
            array('2009-05-19 146922.500', false),
            array('2010-02-18T16.5:23.35:48', false),
            array('2010-02-18T16:23.35:48', false),
            array('2010-02-18T16:23.35:48.45', false),
            array('2009-05-19 14.5.44', false),
            array('2010-02-18T16:23.33.600', false),
            array('2010-02-18T16,25:23:48,444', false),
        );

    }


    /**
     * @dataProvider plan
     */
    public function testValidator($input, $pass)
    {
        $validator = new myValidatorDatetimeIso8601;
        if ($pass) {
            $expected = new DateTime($input);
            $actual = new DateTime($validator->clean($input));
            $expected->setTimezone(new DateTimeZone('Europe/Paris'));
            $actual->setTimezone(new DateTimeZone('Europe/Paris'));
            $this->assertEquals($expected->format(DATE_ISO8601), $actual->format(DATE_ISO8601));
        } else {
            try {
                $validator->clean($input);
                $this->fail("Expected sfValidatorError");
            } catch (sfValidatorError $e) {
            }
        }
    }

}
