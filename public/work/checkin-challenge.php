<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>~*~ Today's checkin challenge ~*~</title>
    <style>
        body {
            /* Simulating a classic tiling space background using CSS gradients */
            background-color: #000000;
            background-image:
                    radial-gradient(white, rgba(255,255,255,.2) 2px, transparent 10px),
                    radial-gradient(white, rgba(255,255,255,.15) 1px, transparent 15px),
                    radial-gradient(white, rgba(255,255,255,.1) 2px, transparent 20px);
            background-size: 250px 250px, 150px 150px, 100px 100px;
            background-position: 0 0, 40px 60px, 130px 270px;
            color: #00FF00;
            font-family: "Comic Sans MS", "Chalkboard SE", sans-serif;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        /* The legendary blink tag reborn in CSS */
        @keyframes blinker {
            50% { opacity: 0; }
        }
        .blink {
            animation: blinker 0.8s step-start infinite;
            color: #FF00FF; /* Hot Pink */
        }

        @keyframes rainbow-text {
            0% { color: #FF0000; }
            17% { color: #FF8000; }
            33% { color: #FFFF00; }
            50% { color: #00FF00; }
            67% { color: #00FFFF; }
            83% { color: #0000FF; }
            100% { color: #FF00FF; }
        }

        .word-art {
            font-family: 'Times New Roman', Times, serif;
            font-size: 50px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0;
            animation: rainbow-text 2s infinite linear;
            text-shadow: 4px 4px 0px #000, 6px 6px 0px #CCC;
            letter-spacing: 5px;
        }

        hr.rainbow-line {
            border: 0;
            height: 8px;
            background-image: linear-gradient(to right, red, orange, yellow, green, blue, indigo, violet);
            margin: 30px auto;
            width: 85%;
            box-shadow: 0 5px 10px #FF00FF;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 50vh;
            padding: 20px;
        }

        .central-box {
            border: 25px ridge #FF00FF;
            background-color: #000080; /* Deep 90s Navy Blue */
            padding: 40px;
            width: 80%;
            max-width: 900px;
            /* Clashing neon glow effects */
            box-shadow: 0 0 40px 15px #00FFFF, inset 0 0 30px 10px #00FF00;
            position: relative;
        }

        .challenge-text {
            font-family: 'Times New Roman', Times, serif;
            font-size: 32px;
            color: #FFFF00; /* Yellow */
            font-weight: bold;
            line-height: 1.6;
            text-align: center;
            text-shadow: 3px 3px 0 #FF0000; /* Red shadow */
        }

        .fake-badges {
            margin: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .badge {
            background: linear-gradient(to bottom, #008080, #000080);
            color: #FFFFFF;
            border: 3px outset #C0C0C0;
            display: inline-block;
            font-family: 'Times New Roman', serif;
            font-size: 14px;
            padding: 4px 10px;
            font-weight: bold;
            text-shadow: 1px 1px #000;
        }

        .badge.ie { background: linear-gradient(to bottom, #4169E1, #00008B); }
        .badge.html3 { background: linear-gradient(to bottom, #FF8C00, #8B0000); }

        a {
            color: #00FFFF;
            text-decoration: underline;
        }
        a:hover {
            color: #FF0000;
            background-color: #FFFF00;
        }
    </style>
</head>
<body>

<marquee scrollamount="12" scrolldelay="0" style="background-color: #FF0000; color: #FFFF00; font-size: 24px; font-weight: bold; padding: 5px; border-bottom: 3px dashed #FFFFFF; border-top: 3px dashed #FFFFFF;">
    *~*~*~ Welcome to the checkin challenge! ~*~*~*
</marquee>

<div class="word-art">
    Checkin challenge for <?= date('j-n-Y'); ?>
</div>

<hr class="rainbow-line">

<marquee behavior="alternate" scrollamount="20" style="width: 50%; margin: 0 auto; color: #00FFFF; font-size: 20px;">
    <span class="blink">>></span> BIG POINTS TO BE HAD TODAY <span class="blink"><<</span>
</marquee>

<div class="wrapper">
    <div class="central-box">
        <div class="challenge-text">
            "Today's checkin challenge is to create a pull request that adds a recipe for delicious pasta carbonara on the landing page of JouwWeb. Checkin points go to the recipe that best commemmorates last Sunday's marathon! As per usual, don't forget to tag @villermen for review!"
        </div>
    </div>
</div>

<div>
    <h3><span class="blink">***</span> !~! Good luck with the challenge !~! <span class="blink">***</span></h3>
</div>

<hr class="rainbow-line">

<div class="fake-badges">
    <div class="badge">Best viewed in<br>Netscape Navigator</div>
    <div class="badge ie">Optimized for<br>Internet Explorer 4.0</div>
    <div class="badge html3">Valid<br>HTML 3.2</div>
</div>

</body>
</html>