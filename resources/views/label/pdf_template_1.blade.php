@php
    function _s($array, $find, $str = 0)
    {
        if(empty($array)){
            return $str == 1 ? 'nem' : false;
        }
        $exists = false;
        if (!is_array($array)) {
            return;
        }
        foreach ($array as $key => $value) {
            if ($find == $value) {
                $exists = true;
            }
        }
        if ($str == 1) {
            return $exists ? 'igen' : 'nem';
        }

        return $exists;
    }
    
    // We need to access static methods of LabelRenderer logic for calc_class if used in view?
    // Legacy view code: <?= clabel::calc_class($data['a3'], 0, 1) ?>
    // We can pass this calculated value or use a helper. 
    // Let's rely on a helper class or method injection if possible. 
    // For now, I'll inline the calc_class logic logic or use a service alias if needed.
    // Or better, calculate it in the service and pass it to view. 
    // I'll assume $classCalculated is passed or I'll reimplement calc_class logic here briefly or make it a helper.
    // Ideally code should not be in view. But for 1:1 port of legacy "template", I will copy the helper function in the view or top of it like legacy.
    // Wait, the legacy "template" had the php function `_s` at the top. I did that.
    // It also called `clabel::calc_class`. I should probably pass that result in the data.
    
    // Helper for calc_class used in view
    if (!function_exists('calc_class_view')) {
        function calc_class_view($d, $number = 0, $type = 1) {
             if($type == 1){
                if ($d >= 150) { return $number == 0 ? "A+++" : 1; }
                elseif ($d >= 125 AND $d < 150) { return $number == 0 ? "A++" : 2; }
                elseif ($d >= 98 AND $d < 125) { return $number == 0 ? "A+" : 3; }
                elseif ($d >= 90 AND $d < 98) { return $number == 0 ? "A" : 4; }
                elseif ($d >= 82 AND $d < 90) { return $number == 0 ? "B" : 5; }
                elseif ($d >= 75 AND $d < 82) { return $number == 0 ? "C" : 6; }
                elseif ($d >= 36 AND $d < 75) { return $number == 0 ? "D" : 7; }
                elseif ($d >= 34 AND $d < 36) { return $number == 0 ? "E" : 8; }
                elseif ($d >= 30 AND $d < 34) { return $number == 0 ? "F" : 9; }
                elseif ($d < 30) { return $number == 0 ? "G" : 10; }
            }
            return "";
        }
    }
@endphp

    <html>
    <head>
        <style>
            table{
                width: 100%;
                border-collapse: collapse;
                border: solid 1px #222;
                font-family: sans-serif;
            }
            th{
                border: solid 1px #222;
                background-color: #bbb;
                font-weight: bold;
                text-align: left;
                font-weight: normal;
            }
            th.top{
                color: #fff;
            }
            td{
                border: solid 1px #222;
            }
            table.noborder, table.noborder td{
                border: none;
            }
            .big{
                font-size: 20px;
                background-color: #bbb;
                color: #fff;
                padding: 5px;
            }
        </style>
    </head>
<body>
<p class="big">
    <strong>
        Az energiafogyasztásra vonatkozó adatlap
    </strong>
    <br>
    <strong>
        <?= $data['b1'] ?? '' ?>
    </strong>
    <? if(!empty($data['b8'])): ?>
    <br>
    <?= $data['b8'] ?>
    <? endif; ?>
</p>
<p>
    A következő termékismertető adatok megfelelnek, a 2010/30/EU irányelv kiegészitéseként szolgáló
    811/2013, 812/2013, 813/2013 és 814/2013 EU rendeletek követelményeinek.
</p>
<table cellspacing="0" cellpadding="3">
    <tr>
        <th colspan="4" class="top"><strong>Termékismertető adatok</strong></th>
    </tr>
    <tr>
        <td colspan="3">Terméktípus</td>
        <td width="100"><?= $data['b1'] ?? '' ?></td>
    </tr>
    <tr>
        <td colspan="3">Kondenzációs kazán</td>
        <td><?= _s($data['c16'] ?? [], 0, 1) ?></td>
    </tr>
    <tr>
        <td colspan="3">Alacsony hőmérsékletű kazán</td>
        <td><?= _s($data['c16'] ?? [], 1, 1) ?></td>
    </tr>
    <tr>
        <td colspan="3">B11 típusú kazán</td>
        <td><?= _s($data['c16'] ?? [], 2, 1) ?></td>
    </tr>
    <tr>
        <td colspan="3">Kapcsolt helyiségfűtő berendezés</td>
        <td><?= _s($data['c16'] ?? [], 3, 1) ?></td>
    </tr>
    <tr>
        <td colspan="3">Ha igen, rendelkezik-e kiegészitő fűtőberendezéssel</td>
        <td><?= _s($data['c16'] ?? [], 4, 1) ?></td>
    </tr>
    <tr>
        <td colspan="3">Kombinált fűtőkészülék</td>
        <td><?= _s($data['c16'] ?? [], 5, 1) ?></td>
    </tr>
    <tr>
        <th></th>
        <th width="100" align="center">Szimbólum</th>
        <th width="100" align="center">Mennyiség</th>
        <th width="100" align="center">Mértékegység</th>
    </tr>
    <tr>
        <td>Mért hőteljesítmény</td>
        <td width="100" align="center">Prated</td>
        <td width="100" align="center"><?= $data['a5'] ?? '' ?></td>
        <td width="100">kW</td>
    </tr>
    <tr>
        <td>Szezonális helyiségfűtési hatásfok</td>
        <td width="100" align="center">η<sub style="font-size: 10px;">s</sub></td>
        <td width="100" align="center"><?= $data['a3'] ?? '' ?></td>
        <td width="100">%</td>
    </tr>
    <tr>
        <td>Energiahatékonysági osztály</td>
        <td width="100" align="center"></td>
        <td width="100" align="center"><?= calc_class_view($data['a3'] ?? 0, 0, 1) ?></td>
        <td width="100"></td>
    </tr>
    <tr>
        <th colspan="4"><strong>Hasznos hőteljesítmény</strong></th>
    </tr>
    <tr>
        <td>Mért hőteljesítményen és magas hőmérsékleten</td>
        <td width="100" align="center">P<sub style="font-size: 10px;">4</sub></td>
        <td width="100" align="center"><?= $data['c6'] ?? '' ?></td>
        <td width="100">kW</td>
    </tr>
    <tr>
        <td>A mért hőtelj. 30%-án és alacsony hőmérsékleten</td>
        <td width="100" align="center">P<sub style="font-size: 10px;">1</sub></td>
        <td width="100" align="center"><?= $data['c7'] ?? '' ?></td>
        <td width="100">kW</td>
    </tr>
    <tr>
        <th colspan="4"><strong>Hatásfok</strong></th>
    </tr>
    <tr>
        <td>Mért hőteljesitményen és magas hőmérsékleten</td>
        <td width="100" align="center">η<sub style="font-size: 10px;">4</sub></td>
        <td width="100" align="center"><?= $data['c8'] ?? '' ?></td>
        <td width="100">%</td>
    </tr>
    <tr>
        <td>A mért hőtelj. 30%-án és alacsony hőmérsékleten</td>
        <td width="100" align="center">η<sub style="font-size: 10px;">1</sub></td>
        <td width="100" align="center"><?= $data['c9'] ?? '' ?></td>
        <td width="100">%</td>
    </tr>
    <tr>
        <th colspan="4"><strong>Villamossegédenergia-fogyasztás</strong></th>
    </tr>
    <tr>
        <td>Teljes terhelés alatt</td>
        <td width="100" align="center">el<sub style="font-size: 10px;">max</sub></td>
        <td width="100" align="center"><?= $data['c10'] ?? '' ?></td>
        <td width="100">kW</td>
    </tr>
    <tr>
        <td>Részterhelés mellett</td>
        <td width="100" align="center">el<sub style="font-size: 10px;">min</sub></td>
        <td width="100" align="center"><?= $data['c11'] ?? '' ?></td>
        <td width="100">kW</td>
    </tr>
    <tr>
        <td>Készenléti üzemmódban</td>
        <td width="100" align="center">P<sub style="font-size: 10px;">SB</sub></td>
        <td width="100" align="center"><?= $data['c12'] ?? '' ?></td>
        <td width="100">kW</td>
    </tr>
    <tr>
        <th colspan="4"><strong>Egyéb elemek</strong></th>
    </tr>
    <tr>
        <td>Készenléti hőveszteség</td>
        <td width="100" align="center">P<sub style="font-size: 10px;">stby</sub></td>
        <td width="100" align="center"><?= $data['c13'] ?? '' ?></td>
        <td width="100">kW</td>
    </tr>
    <tr>
        <td>Nitrogén-oxid-kibocsátás (csak gáz v. olaj)</td>
        <td width="100" align="center">NO<sub style="font-size: 10px;">x</sub></td>
        <td width="100" align="center"><?= $data['c14'] ?? '' ?></td>
        <td width="100">mg/kWh</td>
    </tr>
    <tr>
        <td>Hangteljesítmény szint, beltéri</td>
        <td width="100" align="center">L<sub style="font-size: 10px;">WA</sub></td>
        <td width="100" align="center"><?= $data['a4'] ?? '' ?></td>
        <td width="100"></td>
    </tr>
</table>
<p style="text-align:center;font-size: 11px;">
    A készülék beépítése, felszerelése és üzembehelyezése kizárólag a gyártó utasítása alapján történhet! (Lásd Gépkönyv)
</p>
</body>
</html>
