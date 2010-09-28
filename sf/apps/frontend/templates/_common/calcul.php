<?php
/**
 * Виджет: Калькулятор
 */
?>
<div class="calculator_block">
    <h2>Калькулятор</h2>
    <div class="calculatorRW">
        <div class="input">
            <input type="text" value="0" id="calculatorRW" />
        </div>
        <div class="panel">
            <table>
                <tr>
                    <td class="printed"><div>1</div></td>
                    <td class="printed"><div>2</div></td>
                    <td class="printed"><div>3</div></td>
                    <td class="printed"><div>/</div></td>
                    <td class="special" event="clear"><div>C</div></td>
                </tr>
                <tr>
                    <td class="printed"><div>4</div></td>
                    <td class="printed"><div>5</div></td>
                    <td class="printed"><div>6</div></td>
                    <td class="printed"><div>*</div></td>
                    <td class="special" event="back"><div>←</div></td>
                </tr>
                <tr>
                    <td class="printed"><div>7</div></td>
                    <td class="printed"><div>8</div></td>
                    <td class="printed"><div>9</div></td>
                    <td class="printed"><div>-</div></td>
                    <td rowspan="2" class="special double" event="calc"><div> = </div></td>
                </tr>
                <tr>
                    <td class="printed"><div>0</div></td>
                    <td class="printed"><div>000</div></td>
                    <td class="printed"><div>.</div></td>
                    <td class="printed"><div>+</div></td>
                </tr>
            </table>
        </div>
    </div>
</div>
