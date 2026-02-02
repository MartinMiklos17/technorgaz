@php
    function _s_2($array, $find, $str = 0)
    {
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
        if ($str == 2) {
            return $exists ? 'van' : 'nincs';
        }
        return $exists;
    }
    // Rename to avoid collision or use namespace if possible, but for blade simplicity using _s_2
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
    A következő terméksimertető adatok megfelelnek a 2010/30/EU irányelv kiegészítéseként szolgáló
    2015/1186 EU rendelet követelményeinek.
</p>
<table cellspacing="0" cellpadding="3">
    <tr>
        <th colspan="4" class="top"><strong>Termékismertető adatok</strong></th>
    </tr>
    <tr>
        <td colspan="3">Modellazonosító</td>
        <td width="100"><?= $data['a2'] ?? '' ?></td>
    </tr>
    <tr>
        <td colspan="3">Közvetett fűtési képesség</td>
        <td><?= _s_2($data['c8'] ?? [], 3, 2) ?></td>
    </tr>
    <tr>
        <td>Közvetlen hőteljesítmény</td>
        <td colspan="2" align="center"><?= $data['a4'] ?? '' ?></td>
        <td>kW</td>
    </tr>
    <tr>
        <td>Közvetett hőteljesítmény</td>
        <td colspan="2" align="center"><?= $data['a5'] ?? '' ?></td>
        <td>kW</td>
    </tr>
    <tr>
        <td>
            NOx kibocsátás
            <small>(csak gáz vagy olaj)</small>
        </td>
        <td colspan="2" align="center"><?= $data['b6'] ?? '' ?></td>
        <td>mg/kWh</td>
    </tr>
    <tr>
        <th colspan="4"><strong>Tüzelőanyag</strong></th>
    </tr>
    <tr>
        <td>Tüzelőanyag típusa</td>
        <td align="center" colspan="2"><?= $data['c10'] ?? '' ?></td>
        <td align="center"><?= $data['c9'] ?? '' ?></td>
    </tr>
    <tr>
        <th><strong>Hőteljesítmény</strong></th>
        <th width="100" align="center">Szimbólum</th>
        <th width="100" align="center">Mennyiség</th>
        <th width="100" align="center">Mértékegység</th>
    </tr>
    <tr>
        <td>Névleges hőteljesítmény</td>
        <td width="100" align="center">Pnom</td>
        <td width="100" align="center"><?= $data['c11'] ?? '' ?></td>
        <td width="100">kW</td>
    </tr>
    <tr>
        <td>Minimális hőteljesítmény (indikatív)</td>
        <td width="100" align="center">Pmin</td>
        <td width="100" align="center"><?= $data['c12'] ?? '' ?></td>
        <td width="100">kW</td>
    </tr>
    <tr>
        <th colspan="4"><strong>Hatásfok (NCV)</strong></th>
    </tr>
    <tr>
        <td>Névleges hőteljesítményhez tartozó hatásfok</td>
        <td width="100" align="center">η<sub style="font-size: 10px;">th, nom</sub></td>
        <td width="100" align="center"><?= $data['b3'] ?? '' ?></td>
        <td width="100">%</td>
    </tr>
    <tr>
        <td>Minimális hőteljesítményhez tartozó hatásfok (indikatív)</td>
        <td width="100" align="center">η<sub style="font-size: 10px;">th, min</sub></td>
        <td width="100" align="center"><?= $data['c13'] ?? '' ?></td>
        <td width="100">%</td>
    </tr>
    <tr>
        <th colspan="4"><strong>Kiegészítő villamos segédenergia fogyasztás</strong></th>
    </tr>
    <tr>
        <td>Névleges hőteljesítményen</td>
        <td width="100" align="center">el<sub style="font-size: 10px;">max</sub></td>
        <td width="100" align="center"><?= $data['c14'] ?? '' ?></td>
        <td width="100">kW</td>
    </tr>
    <tr>
        <td>Minimális hőteljesítményen</td>
        <td width="100" align="center">el<sub style="font-size: 10px;">min</sub></td>
        <td width="100" align="center"><?= $data['c15'] ?? '' ?></td>
        <td width="100">kW</td>
    </tr>
    <tr>
        <td>Készenléti üzemmódban</td>
        <td width="100" align="center">P<sub style="font-size: 10px;">SB</sub></td>
        <td width="100" align="center"><?= $data['c16'] ?? '' ?></td>
        <td width="100">kW</td>
    </tr>
    <tr>
        <th colspan="4"><strong>Az állandó gyújtóláng energiaigénye</strong></th>
    </tr>
    <tr>
        <td>A gyújtóláng energiaigénye</td>
        <td width="100" align="center">P<sub style="font-size: 10px;">pilot</sub></td>
        <td width="100" align="center"><?= $data['c17'] ?? '' ?></td>
        <td width="100">kW</td>
    </tr>
    <tr>
        <th colspan="4"><strong>A teljesítmény illetve a beltéri hőmérséklet szabályozásának típusa</strong></th>
    </tr>
    <tr>
        <td colspan="4" align="center"><?= $data['c18'] ?? '' ?></td>
    </tr>
    <tr>
        <th colspan="4"><strong>Más szabályozási lehetőségek</strong></th>
    </tr>
    <tr>
        <td colspan="3">Beltéri hőmérséklet-szabályozás jelenlét-érzékeléssel</td>
        <td width="100" align="center"><?= _s_2($data['c8'] ?? [], 0, 1) ?></td>
    </tr>
    <tr>
        <td colspan="3">Beltéri hőmérséklet-szabályozás nyitottablak-érzékeléssel</td>
        <td width="100" align="center"><?= _s_2($data['c8'] ?? [], 1, 1) ?></td>
    </tr>
    <tr>
        <td colspan="3">Távszabályozási lehetőség</td>
        <td width="100" align="center"><?= _s_2($data['c8'] ?? [], 2, 1) ?></td>
    </tr>
    <tr>
        <td colspan="3">Közvetett fűtési képesség</td>
        <td width="100" align="center"><?= _s_2($data['c8'] ?? [], 3, 1) ?></td>
    </tr>
</table>
</body>
</html>
