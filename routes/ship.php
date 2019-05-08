<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
require_once '../models/config.php';
require_once '../models/connection.php';
require_once './mail/mailers.php';
if ($_GET) {
    $address = new Shipping();
    switch($_GET['operationType']) {
        case 'shipMethods':
            echo $address->getShipMethod();
            break;
        case 'updateShipInvoice': 
            echo $address->editOrderStatus($_GET['order_id'], $_GET['order_status']);
            break;
    }
}
class Shipping {
        public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    public function getShipMethod() {
        $shipMethods = $this->BBDD->selectDriver(null, PREFIX.'order_status', $this->driver);
        $this->BBDD->runDriver(null, $shipMethods);
        if ($this->BBDD->verifyDriver($shipMethods)) {
            return json_encode($this->BBDD->fetchDriver($shipMethods));
        } else {
            return json_encode('');
        }
    }
    public function editOrderStatus($order_id, $order_status_id) {
        $shipMethods = $this->BBDD->updateDriver('order_id = ?', PREFIX.'order', $this->driver, 'order_status_id = ?');
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($order_status_id),
            $this->BBDD->scapeCharts($order_id)
        ), $shipMethods);
        $mail = new Mailers();
        $PushNotf = $mail->paymentMail($_POST['firstname'],
                'Notificación de tu pedido en Ragazza',
                "<!DOCTYPE html>
<html lang='en' xmlns='http://www.w3.org/1999/xhtml' xmlns:v='urn:schemas-microsoft-com:vml' xmlns:o='urn:schemas-microsoft-com:office:office'>

<head>
    <meta charset='utf-8'>
    <!-- utf-8 works for most cases -->
    <meta name='viewport' content='width=device-width'>
    <!-- Forcing initial-scale shouldn't be necessary -->
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <!-- Use the latest (edge) version of IE rendering engine -->
    <meta name='x-apple-disable-message-reformatting'>
    <!-- Disable auto-scale in iOS 10 Mail entirely -->
    <title></title>
    <!-- The title tag shows in email notifications, like Android 4.4. -->

    <!-- Web Font / @font-face : BEGIN -->
    <!-- NOTE: If web fonts are not required, lines 10 - 27 can be safely removed. -->

    <!-- Desktop Outlook chokes on web font references and defaults to Times New Roman, so we force a safe fallback font. -->
    <!--[if mso]>
        <style>
            * {
                font-family: sans-serif !important;
            }
        </style>
    <![endif]-->

    <!-- All other clients get the webfont reference; some will render the font and others will silently fail to the fallbacks. More on that here: http://stylecampaign.com/blog/2015/02/webfont-support-in-email/ -->
    <!--[if !mso]><!-->
    <!-- insert web font reference, eg: <link href='https://fonts.googleapis.com/css?family=Roboto:400,700' rel='stylesheet' type='text/css'> -->
    <!--<![endif]-->

    <!-- Web Font / @font-face : END -->

    <!-- CSS Reset : BEGIN -->
    <style>
        /* What it does: Remove spaces around the email design added by some email clients. */
        /* Beware: It can remove the padding / margin and add a background color to the compose a reply window. */
        
        html,
        body {
            margin: 0 auto !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
        }
        /* What it does: Stops email clients resizing small text. */
        
        * {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
        /* What it does: Centers email on Android 4.4 */
        
        div[style*='margin: 16px 0'] {
            margin: 0 !important;
        }
        /* What it does: Stops Outlook from adding extra spacing to tables. */
        
        table,
        td {
            mso-table-lspace: 0pt !important;
            mso-table-rspace: 0pt !important;
        }
        /* What it does: Fixes webkit padding issue. Fix for Yahoo mail table alignment bug. Applies table-layout to the first 2 tables then removes for anything nested deeper. */
        
        table {
            border-spacing: 0 !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            margin: 0 auto !important;
        }
        
        table table table {
            table-layout: auto;
        }
        /* What it does: Prevents Windows 10 Mail from underlining links despite inline CSS. Styles for underlined links should be inline. */
        
        a {
            text-decoration: none;
        }
        /* What it does: Uses a better rendering method when resizing images in IE. */
        
        img {
            -ms-interpolation-mode: bicubic;
        }
        /* What it does: A work-around for email clients meddling in triggered links. */
        
        *[x-apple-data-detectors],
        /* iOS */
        
        .unstyle-auto-detected-links *,
        .aBn {
            border-bottom: 0 !important;
            cursor: default !important;
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }
        /* What it does: Prevents Gmail from displaying a download button on large, non-linked images. */
        
        .a6S {
            display: none !important;
            opacity: 0.01 !important;
        }
        /* If the above doesn't work, add a .g-img class to any image in question. */
        
        img.g-img+div {
            display: none !important;
        }
        /* What it does: Removes right gutter in Gmail iOS app: https://github.com/TedGoas/Cerberus/issues/89  */
        /* Create one of these media queries for each additional viewport size you'd like to fix */
        /* iPhone 4, 4S, 5, 5S, 5C, and 5SE */
        
        @media only screen and (min-device-width: 320px) and (max-device-width: 374px) {
            .email-container {
                min-width: 320px !important;
            }
        }
        
        @media only screen and (min-device-width: 375px) and (max-device-width: 413px) {
            .email-container {
                min-width: 375px !important;
            }
        }
        
        @media only screen and (min-device-width: 414px) {
            .email-container {
                min-width: 414px !important;
            }
        }
    </style>
    <!--[if mso]>
	<style type='text/css'>
		ul,
		ol {
			margin: 0 !important;
		}
		li {
			margin-left: 30px !important;
		}
		li.list-item-first {
			margin-top: 0 !important;
		}
		li.list-item-last {
			margin-bottom: 10px !important;
		}
	</style>
	<![endif]-->

    <style>
        .button-td,
        .button-a {
            transition: all 100ms ease-in;
        }
        
        .button-td-primary:hover,
        .button-a-primary:hover {
            background: #555555 !important;
            border-color: #555555 !important;
        }
        
        @media screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: auto !important;
            }
            .fluid {
                max-width: 100% !important;
                height: auto !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }
            .stack-column,
            .stack-column-center {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                direction: ltr !important;
            }
            .stack-column-center {
                text-align: center !important;
            }
            .center-on-narrow {
                text-align: center !important;
                display: block !important;
                margin-left: auto !important;
                margin-right: auto !important;
                float: none !important;
            }
            table.center-on-narrow {
                display: inline-block !important;
            }
            .email-container p {
                font-size: 17px !important;
            }
        }
    </style>
    <!--[if gte mso 9]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->

</head>

<body width='100%' style='margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #222222;'>
    <center style='width: 100%; background-color: #222222;'>
        <!--[if mso | IE]>
    <table role='presentation' border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: #222222;'>
    <tr>
    <td>
    <![endif]-->

        <!-- Visually Hidden Preheader Text : BEGIN -->
        <div style='display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;'>
            (Optional) This text will appear in the inbox preview, but not the email body. It can be used to supplement the email subject line or even summarize the email's contents. Extended text preheaders (~490 characters) seems like a better UX for anyone using
            a screenreader or voice-command apps like Siri to dictate the contents of an email. If this text is not included, email clients will automatically populate it using the text (including image alt text) at the start of the email's body.
        </div>
        <div style='display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;'>
            &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        </div>
        <table align='center' role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' style='margin: 0 auto;' class='email-container'>
            <tr>
                <td style='background-color: #ffffff;'>
                    <img src='http://www.ragazzashop.com/empresas/assets/images/logo.jpg' alt='alt_text' border='0' style='width: 50%; max-width: 600px; height: auto; background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 15px; color: #555555; margin: auto; text-align: center'
                        class='g-img'>
                </td>
            </tr>
            <tr>
                <td style='background-color: #ffffff;'>
                    <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%'>
                        <tr>
                            <td style='padding: 20px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;'>
                                <h1 style='margin: 0 0 10px; font-size: 25px; line-height: 30px; color: #333333; font-weight: normal;'>
                                    Tu Pedido {$_POST['product_name']} ha sido actualizado
                                </h1>
                                <p style='margin: 0 0 10px;'>Hola {$_POST['firstname']}! El equipo de Ragazza informa que tu pedido {$_POST['product_name']} ha sido actualizado por el vendedor
                                    <br>
                                    El estado de tu pedido esta en:  {$this->getShipById($_POST['comerce_status'])} te notificaremos cuando tu pedido haya sido modificado nuevamente
.</p>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 0 20px 20px;'>
                                <table align='center' role='presentation' cellspacing='0' cellpadding='0' border='0' style='margin: auto;'>
                                    <tr>
                                        <td class='button-td button-td-primary' style='border-radius: 4px; background: #222222;'>
                                            <a class='button-a button-a-primary' href='http://ragazzashop.com/empresas/#/invoice/customer/{$order_id}/{$_POST['customer_id']}/' style='background: #222222; border: 1px solid #000000; font-family: sans-serif; font-size: 15px; line-height: 15px; text-decoration: none; padding: 13px 17px; color: #ffffff; display: block; border-radius: 4px;'>
                                                Revisar mi pedido
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>
        <table align='center' role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' style='margin: 0 auto;' class='email-container'>
            <tr>
                <td style='padding: 20px; font-family: sans-serif; font-size: 12px; line-height: 15px; text-align: center; color: #888888;'>
                    <webversion style='color: #cccccc; text-decoration: underline; font-weight: bold;'>Ragazza Store</webversion>
                    <br><br> <br><span class='unstyle-auto-detected-links'>Ecuador<br>593 0998770574</span>
                    <br><br>
                </td>
            </tr>
        </table>
        <!-- Email Footer : END -->

        <!--[if mso | IE]>
    </td>
    </tr>
    </table>
    <![endif]-->
    </center>
</body>

</html>", $this->getCliEmail($_POST['customer_id']));
        if ($PushNotf) {
            $response = array();
            $response['status'] = true;
            $response['msg'] = 'Pedido actualizado con éxito';
            return json_encode($response);
        } else {
            return json_encode('');
        }
    }
    private function getCliEmail($customer_id) {
        $customer = $this->BBDD->selectDriver('customer_id = ?', PREFIX.'customer', $this->driver);
        $this->BBDD->runDriver(array($this->BBDD->scapeCharts($customer_id)), $customer);
        foreach($this->BBDD->fetchDriver($customer) as $resp) {
            return $resp->email;
        }
    }
    private function getShipById($ship_id) {
        $customer = $this->BBDD->selectDriver('order_status_id = ?', PREFIX.'order_status', $this->driver);
        $this->BBDD->runDriver(array($this->BBDD->scapeCharts($ship_id)), $customer);
        foreach($this->BBDD->fetchDriver($customer) as $resp) {
            return $resp->name;
        }
    }
    protected $BBDD;
    protected $driver;
}