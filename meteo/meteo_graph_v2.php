<?php
//,Hydrometrie,UV,Luminosite_1,Luminosite_2

include "data/config.inc";

//parametres

// --- id appareil ----
if (isset($_GET['id_app'])) {
    $id_appareil = $_GET['id_app'];
} else {
    $id_appareil = 1;
}

// --- PAS ----
if (isset($_GET['pas'])) {
    $pas = $_GET['pas'];
} else {
    $pas = 2;
}

// --- backhour ----
if (isset($_GET['backhour'])) {
    $backhour = $_GET['backhour'];
} else {
    $backhour = 1;
}

//$id_appareil=1;
//$pas=3;

$date = date('Y/m/d H:i:s', time());

$date = new DateTime($date);
//$next_date=$date;

$date->sub(new DateInterval('PT' . $backhour . 'H'));
//echo $next_date->format('Y-m-d H:i:s') . "<br>";
$date = $date->format('Y-m-d H:i:s');

?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/c3.css">
    <style>

        button, input {
            background-color: #4CAF50; /* Green */
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }

        a {
            background-color: #AD4C50; /* Green */
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }

        select {
        / / width: 100 %;
            padding: 16px 20px;
            border: none;
            border-radius: 4px;
            background-color: #f1f1f1;
        }

    </style>

</head>
<body>
<?php
$curl = curl_init();
$enddate = date('Y-m-d', strtotime('-' . $backhour . ' day'));
$rul = "http://192.168.0.30/Meteo/data/AZERTYU?date=2019-03-25";

curl_setopt_array($curl, [
    CURLOPT_URL => $rul,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_POSTFIELDS => "{\"id\": \"102\", \"name\": \"The Changed two\"}",
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Postman-Token: 50e41bdf-2875-4eea-8726-728b50d47653",
        "cache-control: no-cache"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

$responseArray = json_decode($response, true);


?>


<script src="https://d3js.org/d3.v5.min.js" charset="utf-8"></script>
<script src="js/c3.min.js"></script>
<script>
    var data, axis_x_localtime;
    var data2 = {
        x: 'date',
        columns: [
            <?php


            $data_0 = "['date'";
            $data_1 = "['Temperature'";
            $data_2 = "['Pression'";
            $data_3 = "['Hydrometrie'";



            foreach ($responseArray['message'] as $id => $vals) {

                foreach ($vals as $ids => $val) {
                    $new_date = substr($val["date"], 0, 4) . substr($val["date"], 4, 3) . substr($val["date"], 7, 3) . substr($val["date"], 10, 20);

                    if ($id == 'temp') {
                        $data_0 = $data_0 . "," . strtotime($new_date) . "000";
                        $data_1 = $data_1 . "," . $val["value"];
                    }
                    if ($id == 'pressur') {
                        $data_2 = $data_2 . "," . $val["value"];
                    }
                    if ($id == 'humidity') {
                        $data_3 = $data_3 . "," . $val["value"];
                    }
                }
            }
            $data_0 = $data_0 . "],";
            $data_1 = $data_1 . "],";
            $data_2 = $data_2 . "],";
            $data_3 = $data_3 . "],";

            print $data_0 . "\n";
            print $data_1 . "\n";
            print $data_2 . "\n";
            print $data_3 . "\n";
            ?>

        ]
    };
    var generate = function () {
        return c3.generate({
            bindto: '#chart',
            data: data,
            point: {
                show: true
            },
            size: {
                height: 440
            },
            axis: {
                x: {
                    type: 'timeseries',
                    //localtime: true,
                    tick: {
                        rotate: 75,
                        count: 200,
                        format: "%d/%m/%Y %Hh%M" // https://github.com/mbostock/d3/wiki/Time-Formatting#wiki-format "%Y-%m-%d %H:%M:%S"
                    },
                    localtime: axis_x_localtime,
                    //axis_x_localtime = true;
                }
            }
        });
    };


    setTimeout(function () {
        data = data2;
        axis_x_localtime = true;
        chart = generate();
    }, 500);


    function show_p1() {
        hide_all();
        chart.show(['Temperature', 'data3']);
        chart.transform('spline', 'data3');
    }

    function show_p2() {
        hide_all();
        chart.show(['Pression', 'data3']);
    }

    function show_p3() {
        hide_all();
        chart.show(['Hydrometrie', 'data3']);
    }

    function show_p4() {
        hide_all();
        chart.show(['UV', 'data3']);
    }

    function show_p5() {
        hide_all();
        chart.show(['Luminosite_1', 'data3']);
    }

    function show_p6() {
        hide_all();
        chart.show(['Luminosite_2', 'data3']);
    }

    function show_all() {
        chart.show(['Temperature', 'data3']);
        chart.show(['Pression', 'data3']);
        chart.show(['Hydrometrie', 'data3']);
        chart.show(['UV', 'data3']);
        chart.show(['Luminosite_1', 'data3']);
        chart.show(['Luminosite_2', 'data3']);
    }

    function hide_all() {
        chart.hide(['Temperature', 'data3']);
        chart.hide(['Pression', 'data3']);
        chart.hide(['Hydrometrie', 'data3']);
        chart.hide(['UV', 'data3']);
        chart.hide(['Luminosite_1', 'data3']);
        chart.hide(['Luminosite_2', 'data3']);
    }


</script>

<br>
<button class="small" onclick="show_p1();">Temperature</button>
<button class="small" onclick="show_p2();">Pression AT</button>
<button class="small" onclick="show_p3();">Hydrometrie (%)</button>
<button class="small" onclick="show_all();">TOUT</button>
<a href="index.html">Retour</a>

<br>

<div id="chart"></div>
<br>
<form>
    Historique : <select name="backhour">
        <?php print '<option value = "' . $backhour . '" selected>' . ($backhour) . ' Jour(s)</option>'; ?>
        <option value="1">24 Heures</option>
        <option value="2">2 Jours</option>
        <option value="5">5 Jours</option>
        <option value="7">7 Jours</option>
    </select>

    <input class="small" type="submit" name="submit" value="Valider"/>
    <input type="reset" name="reset" value="Defaut"/>

</form>


</body>
</html>