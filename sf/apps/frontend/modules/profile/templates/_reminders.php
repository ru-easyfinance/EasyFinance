<?php require_once(dirname(__FILE__) . '/../../../lib/helper/myDateTimezoneHelper.php'); ?>

<div id="remindersOptions" class="inside form" >
<table>
    <tr>
        <td>Часовой пояс:<br />
            <select id="selTimeZoneOffset" name="timezone">
            <?php foreach(myDateTimezoneHelper::getZones() as $name => $zone): ?>
                <option value="<?php echo $name; ?>">(<?php echo $zone['offset']; ?>) <?php echo $zone['title']; ?></option>
            <?php endforeach; ?>
            </select>
        </td>
    </tr>
</table>

<?php include_partial('global/common/reminders'); ?>

<br />
<button id="save_reminders">Сохранить</button>

</div>
