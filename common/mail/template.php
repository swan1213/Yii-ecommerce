<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Email</title>
    <style>
        /* -------------------------------------
            GLOBAL RESETS
        ------------------------------------- */
        img {
            border: none; -ms-interpolation-mode: bicubic; max-width: 100%; }
        span {
            padding:15px;}

        body {
            background-color: #ffffff;font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0;     -ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%; }

        table {
            border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; }
        table td {
            font-family: sans-serif; font-size: 14px; vertical-align: top; }

        /* -------------------------------------
            BODY & CONTAINER
        ------------------------------------- */

        .body {
            background-color: #ffffff; width: 100%; }

        /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
        .container {
            display: block; Margin: 0 auto !important; /* makes it centered */max-width: 700px; padding: 10px; width: 700px; }

        /* This should also be a block element, so that it will fill 100% of the .container */
        .content {
            box-sizing: border-box; display: block; Margin: 0 auto; max-width: 700px; padding: 10px; }

        /* -------------------------------------
            HEADER, FOOTER, MAIN
        ------------------------------------- */
        .main {
            background: #ffffff; border-radius: 3px; width: 100%; }

        .wrapper {
            box-sizing: border-box; padding: 10px; }

        .content-block {
            padding-bottom: 10px; padding-top: 10px;
        }
        .footer {
            clear: both; Margin-top: 0px;  padding-bottom: 10px; text-align: center; width: 100%; }
        .footer td,
        .footer a {
            color: #818a8c; font-family: PT serif; font-size: 14px; font-weight: 100; text-align: center; }
        /* -------------------------------------
            TYPOGRAPHY
        ------------------------------------- */

        h1 {
            color: #000000; font-family: PT serif; font-size: 36px; font-weight: 700; line-height: 1.2; text-align: center;}
        h4 {
            color: #818a8c; font-family: PT serif; font-size: 14px; font-weight: 100; line-height: 1.8; letter-spacing: 0px;}

        p,
        ul,
        ol {
            font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-top: 15px; Margin-bottom: 15px; }
        p li,
        ul li,
        ol li {
            list-style-position: inside; margin-left: 5px; }

        a {
            font-family: Montserriat; font-weight: 300; line-height: 1.4; letter-spacing: 1.6px; color: #3498db; text-decoration: underline; }
        .overlay {
            background-color: #FFFFFF;
            color: #000000;
            padding: 0.5em;
        }
        .shadow {
            background-color: #EEEEEE;
        }
        .shadow .overlay {
            bottom: 4px;
            position: relative;
            right: 4px;
        }

        /* -------------------------------------
            BUTTONS
        ------------------------------------- */
        .btn {
            box-sizing: border-box;
            width: 100%; }
        .btn > tbody > tr > td {
            padding-bottom: 0px; }
        .btn table {
            width: auto; }
        .btn table td {
            background-color: #ffffff;
            border-radius: 5px;
            text-align: center; }
        .btn a {
            background-color: #ffffff; width:100%; border: solid 1px #d8d8d8; border-radius: 20px; box-sizing: border-box; color: #444444; cursor: pointer;
            display: inline-block; font-size: 11px; margin: 0; padding: 10px 25px; text-decoration: none; text-transform: capitalize; }

        .btn-primary table td {
            vertical-align: middle;}

        .btn-primary .elliot_login a {
            background-color: #1b75bc; border-color: #1b75bc; border-radius: 20px; color: #ffffff; }

        .powered-by a {
            text-decoration: none; }
        .email_body {
            background-color:#fff; padding:25px; box-shadow: 0px 0px 20px #eeeeee; border-radius: 20px; border-style: hidden;
        }
        .email_title {
            border-radius: 20px; border-style: hidden; padding: 5px 10px;
        }
        .email_img {
            border-radius: 20px; border-style: hidden; background-color:#d8d8d8; box-shadow: 0px 0px 30px #eeeeee; padding: 5px 10px; height: 200px;
        }

        hr {
            border: 0;
            border-bottom: 1px solid #f6f6f6;
            Margin: 20px 0; }

        /* -------------------------------------
            RESPONSIVE AND MOBILE FRIENDLY STYLES
        ------------------------------------- */
        @media only screen and (max-width: 620px) {
            table[class=body] h1 {
                font-size: 28px !important; }
            table[class=body] p,
            table[class=body] ul,
            table[class=body] ol,
            table[class=body] td,
            table[class=body] span,
            table[class=body] a {
                font-size: 14px !important; }
            table[class=body] .wrapper,
            table[class=body] .article {
                padding: 10px !important; }
            table[class=body] .content {
                padding: 0 !important; }
            table[class=body] .container {
                padding: 0 !important;
                width: 100% !important; }
            table[class=body] .main {
                border-left-width: 0 !important;
                border-radius: 0 !important;
                border-right-width: 0 !important; }
            table[class=body] .btn table {
                width: 100% !important; }
            table[class=body] .btn a {
                width: 100% !important; }
            table[class=body] .img-responsive {
                height: auto !important;
                max-width: 100% !important;
                width: auto !important; }}

        /* -------------------------------------
            PRESERVE THESE STYLES IN THE HEAD
        ------------------------------------- */
        @media all {
            .ExternalClass {
                width: 100%; }
            .ExternalClass,
            .ExternalClass p,
            .ExternalClass span,
            .ExternalClass font,
            .ExternalClass td,
            .ExternalClass div {
                line-height: 100%; }
            .apple-link a {
                color: inherit !important;
                font-family: inherit !important;
                font-size: inherit !important;
                font-weight: inherit !important;
                line-height: inherit !important;
                text-decoration: none !important; }
            .btn-primary table td {
                vertical-align: middle !important;}
            .btn-primary .elliot_login a:hover {
                background-color: #1b75bc70 !important;
                border-color: #1b75bc70 !important; } }

    </style>
</head>
<body class="">
<table border="0" cellpadding="0" cellspacing="0" class="body">
    <tr>
        <td>&nbsp;</td>
        <td class="container">
            <div class="content">
                <table class="main">

                    <!-- START MAIN CONTENT AREA -->
                    <tr>
                        <td class="wrapper">
                            <table border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                            <tbody>
                                            <tr>
                                                <td align="center">
                                                    <table border="0" cellpadding="0" cellspacing="0">
                                                        <tbody>
                                                        <tr>
                                                            <td width="35%"> <a href="www.helloiamelliot.com" target="_blank">HELLOIAMELLIOT.COM</a> </td>
                                                            <td width="30%"> <center><img src="<?php echo $server.'img/elliot-logo-thumbnail-01.jpg'; ?>" alt="Logo" title="Logo" style="display:block;height:110px;width: 110px;"/></center>
                                                            </td>
                                                            <td width="35%"> <div  class="elliot_login"> <a href="www.elliot.global" target="_blank">LOGIN TO ELLIOT</a> </div> </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>

                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <table border="0" cellpadding="0" cellspacing="0">
                                                <tbody>
                                                <tr>
                                                    <td style="background-image:url('https://s86.co/img/shadow/shadow_top_left.png'); background-repeat:no-repeat; height:75px; width: 75px"></td>
                                                    <td style="background-image:url('https://s86.co/img/shadow/shadow_top.png'); background-repeat:repeat-x; height:75px;"></td>
                                                    <td style="background-image:url('https://s86.co/img/shadow/shadow_top_right.png'); background-repeat:no-repeat; height:75px; width: 75px"></td>
                                                </tr>
                                                <tr>
                                                    <td style="background-image:url('https://s86.co/img/shadow/shadow_left.png'); background-repeat:repeat-y; width:75px;"></td>
                                                    <td>
                                                        <div>
                                                            <table border="0" cellpadding="0" cellspacing="0">
                                                                <tbody>
                                                                <tr>
                                                                    <td align="left">
                                                                        <table border="0" cellpadding="0" cellspacing="0"  class="email_title">
                                                                            <tbody>
                                                                            <tr>
                                                                                <td> <H1><?php echo $title; ?></H1> </td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
<!--                                                                <tr>-->
<!--                                                                    <td align="left">-->
<!--                                                                        <table border="0" cellpadding="0" cellspacing="0"  class="email_img">-->
<!--                                                                            <tbody>-->
<!--                                                                            <tr>-->
<!--                                                                                <td> </td>-->
<!--                                                                            </tr>-->
<!--                                                                            </tbody>-->
<!--                                                                        </table>-->
<!--                                                                    </td>-->
<!--                                                                </tr>-->
                                                                </tbody>
                                                            </table>
                                                            <p><H4><?php echo $content; ?></H4></p>
                                                        </div>
                                                    </td>
                                                    <td style="background-image:url('https://s86.co/img/shadow/shadow_right.png'); background-repeat:repeat-y; width:75px;"></td>
                                                </tr>
                                                <tr>
                                                    <td style="background-image:url('https://s86.co/img/shadow/shadow_bottom_left.png'); background-repeat:no-repeat; height:75px; width: 75px"></td>
                                                    <td style="background-image:url('https://s86.co/img/shadow/shadow_bottom.png'); background-repeat:repeat-x; height:75px;"></td>
                                                    <td style="background-image:url('https://s86.co/img/shadow/shadow_bottom_right.png'); background-repeat:no-repeat; height:75px; width: 75px"></td>
                                                </tr>
                                                </tbody>
                                            </table>

                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <!-- START FOOTER -->
                            <div class="footer">
                                <table border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td class="content-block powered-by">
                                            <a href="<?php echo $server; ?>">www.elliot.global</a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                        <!-- END FOOTER -->
                    </tr>
                </table>

            </div>
        </td>
        <td>&nbsp;</td>
    </tr>
</table>
</body>
</html>