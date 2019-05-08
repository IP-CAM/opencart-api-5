<?php
namespace Kushki;
use kushki\lib\Amount;
use kushki\lib\Kushki;
use kushki\lib\KushkiEnvironment;
use kushki\lib\Transaction;
use kushki\lib\ExtraTaxes;
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type");
header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS");
require_once '../../models/config.php';
require_once '../../models/connection.php';
require_once '../kushki/autoload.php';
require_once '../mail/mailers.php';
if ($_POST) {
    if (isset($_POST['kushkiToken']) && (!empty($_POST['kushkiToken']))) {
        if (isset($_POST['totalAmount']) && (!empty($_POST['totalAmount']))) {
            $paymentGateway = new PaymentRequest(); // Creamos un objeto de pago
            $payload = $paymentGateway->createPaymentObject(); // Crearmos la solicitud de pago
            $getBBDDSumCart = $paymentGateway->sumCart($_POST['customer']);
            // Verificamos si el front fue manipulado buscando data de la db
            if ($getBBDDSumCart != 0) {
                if ($getBBDDSumCart == $_POST['totalAmount']) {
                    $payment = $payload->charge($_POST['kushkiToken'], $paymentGateway->chargePaymentGateway());
                    if ($payment->isSuccessful()) {
                        if ($paymentGateway->sendEmail($_POST['firstname'], null)){
                         if ($paymentGateway->removeCart($_POST['customer'])) {
                            if ($paymentGateway->generateOrder()) {
                                /*$response = array();
                                $response['text'] = $payment->getResponseText();
                                $response['code'] = $payment->getResponseCode();
                                $response['ticket'] = $payment->getTicketNumber();*/
                                $ticket = base64_encode($payment->getTicketNumber());
                                header("Location: http://ragazzashop.com/empresas/#/confirm/payment/true?ticket={$ticket}");
                            }   
                        }
                      }
                    } else {
                        // echo "Error " . $transaccion->getResponseCode() . ": " . $transaccion->getResponseText();
                        $response = array();
                        $response['status'] = false;
                        $response['statusCode'] = 400;
                        $response['message'] = 'No pudimos procesar tu pago';
                        $response['text'] = $payment->getResponseText();
                        $response['code'] = $payment->getResponseCode();
                        header("Location: http://ragazzashop.com/empresas/#/confirm/payment/false?ticket=0");

                    }
                } else {
                    /*$response = array();
                    $response['status'] = false;
                    $response['statusCode'] = 400;
                    $response['message'] = 'El monto es invalido';*/
                    header("Location: http://ragazzashop.com/empresas/#/confirm/payment/false?ticket=0&msg=400");
                }
            } else {
                    /*$response = array();
                    $response['status'] = false;
                    $response['statusCode'] = 402;
                    $response['message'] = 'Fallo en las credenciales';*/
                    header("Location: http://ragazzashop.com/empresas/#/confirm/payment/false?ticket=0&msg=402");
            }
        }
    }
}
class PaymentRequest {
        public function __construct() {
        $this->BBDD = new \dbDriver;
        $this->driver = $this->BBDD->setPDO();
        $this->BBDD->setPDOConfig($this->driver);
    }
    public function createPaymentObject() {
        $merchantId = "1000000363945473759415366872700";
        $language = \kushki\lib\KushkiLanguage::ES;
        $currency = \kushki\lib\KushkiCurrency::USD;
        $environment = \kushki\lib\KushkiEnvironment::TESTING;
        $kushki = new Kushki($merchantId, $language, $currency, $environment);
        return $kushki;
    }
    public function chargePaymentGateway() {
        $amount = new Amount($_POST['totalAmount'], 0, 0, 0);
        return $amount;
    }
    public function sumCart($customer_id) {
        $sumCart = $this->BBDD->sumDriver('cart_client_id=?',PREFIX."carrito",$this->driver,"cart_price");
        $this->BBDD->runDriver(array(
            $this->BBDD->scapeCharts($customer_id)
        ), $sumCart);
        if($this->BBDD->verifyDriver($sumCart))
        {
            foreach ($this->BBDD->fetchDriver($sumCart) as $tot) {
                return $tot->total;
            }
        }else{
            return 0;
        }
    }
    public function removeCart($customer_id) {
        try {
            $cart = $this->BBDD->deleteDriver('cart_client_id = ?', PREFIX.'carrito', $this->driver);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($customer_id)
            ), $cart);
            return true;
        } catch (PDOException $ex) {
            exit('failure to connect with database server');
        }
    }
    public function generateOrder() {
        // Generamos una orden por cada producto, por si son de distintas empresas
        $time = time();
        $date = date("Y-m-d ", $time);
        $itemOrder = json_decode($_POST['custom_field']);
        foreach ($itemOrder as $item) {
                 $fields = 'user_id, invoice_no, invoice_prefix, store_id, store_name,
                   store_url, customer_id, customer_group_id, firstname,
                   lastname, email, telephone, fax, custom_field, payment_firstname,
                   payment_lastname, payment_company, payment_address_1, payment_address_2,
                   payment_city, payment_postcode, payment_country, payment_country_id, 
                   payment_zone, payment_zone_id, payment_address_format, payment_custom_field,
                   payment_method, payment_code, shipping_firstname, shipping_lastname, shipping_address_1,
                   shipping_address_2, shipping_city, shipping_postcode, shipping_country, shipping_country_id,
                   shipping_zone, shipping_zone_id, shipping_method, shipping_code,  total, order_status_id,  currency_id,
                   currency_code, date_added
                   '; // 43
        $sql = '?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?'; // 43   
      try {
            $invoice = $this->BBDD->insertDriver($sql,PREFIX.'order',$this->driver, $fields);
            $this->BBDD->runDriver(array(
                $this->BBDD->scapeCharts($_POST['customer']),
                $this->BBDD->scapeCharts(rand(5, 15)),
                $this->BBDD->scapeCharts("INV-{$date}-00"),
                $this->BBDD->scapeCharts($item->cart_product->user_id),
                $this->BBDD->scapeCharts($item->store->shop_name),
                $this->BBDD->scapeCharts('http://ragazzashop.com/empresas/#/login'),
                $this->BBDD->scapeCharts($_POST['customer']),
                $this->BBDD->scapeCharts(3),
                $this->BBDD->scapeCharts($_POST['firstname']),
                $this->BBDD->scapeCharts($_POST['lastname']),
                $this->BBDD->scapeCharts(null),
                $this->BBDD->scapeCharts($_POST['telephone']),
                $this->BBDD->scapeCharts($_POST['telephone']),
                $this->BBDD->scapeCharts($item->cart_product->product_id),
                $this->BBDD->scapeCharts($_POST['payment_firstname']),
                $this->BBDD->scapeCharts($_POST['payment_lastname']),
                $this->BBDD->scapeCharts($_POST['payment_company']),
                $this->BBDD->scapeCharts($_POST['payment_address_1']),
                $this->BBDD->scapeCharts($_POST['payment_address_2']),
                $this->BBDD->scapeCharts($_POST['payment_city']),
                $this->BBDD->scapeCharts($_POST['payment_postcode']),
                $this->BBDD->scapeCharts($_POST['payment_country']),
                $this->BBDD->scapeCharts($_POST['payment_country_id']),
                $this->BBDD->scapeCharts($_POST['payment_zone']),
                $this->BBDD->scapeCharts($_POST['payment_zone_id']),
                $this->BBDD->scapeCharts($_POST['payment_address_format']),
                $this->BBDD->scapeCharts($_POST['payment_custom_field']),
                $this->BBDD->scapeCharts('Card'),
                $this->BBDD->scapeCharts($_POST['payment_code']),
                $this->BBDD->scapeCharts($_POST['shipping_firstname']),
                $this->BBDD->scapeCharts($_POST['shipping_lastname']),
                $this->BBDD->scapeCharts($_POST['shipping_address_1']),
                $this->BBDD->scapeCharts($_POST['shipping_address_2']),
                $this->BBDD->scapeCharts($_POST['shipping_city']),
                $this->BBDD->scapeCharts($_POST['shipping_postcode']),
                $this->BBDD->scapeCharts($_POST['shipping_country']),
                $this->BBDD->scapeCharts($_POST['shipping_country_id']),
                $this->BBDD->scapeCharts($_POST['payment_zone']),
                $this->BBDD->scapeCharts($_POST['payment_zone_id']),
                $this->BBDD->scapeCharts('Flat Shipping Rate'),
                $this->BBDD->scapeCharts('flat.flat'),
                $this->BBDD->scapeCharts($item->cart_product->price),
                $this->BBDD->scapeCharts(1),
                $this->BBDD->scapeCharts($_POST['currency_id']),
                $this->BBDD->scapeCharts('USD'),
                $this->BBDD->scapeCharts($date),
            ), $invoice);
        } catch (PDOException $ex) {
             exit('failure to connect with database server'); 
        }
       }
       return true;
    }
    public function sendEmail($firstname, $email) {
        $mailer = new \Mailers();
        $sendConfirmation = $mailer->paymentMail($firstname, 'Confirmación de pago', "<!DOCTYPE html>
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
                                    Confirmación de compra
                                </h1>
                                <p style='margin: 0 0 10px;'>Hola $firstname! El equipo de Ragazza te informa que tu compra ha sido procesada con éxito, accede a tu panel para verificar tu pedido .</p>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 0 20px 20px;'>
                                <table align='center' role='presentation' cellspacing='0' cellpadding='0' border='0' style='margin: auto;'>
                                    <tr>
                                        <td class='button-td button-td-primary' style='border-radius: 4px; background: #222222;'>
                                            <a class='button-a button-a-primary' href='http://www.ragazzashop.com/account.php' style='background: #222222; border: 1px solid #000000; font-family: sans-serif; font-size: 15px; line-height: 15px; text-decoration: none; padding: 13px 17px; color: #ffffff; display: block; border-radius: 4px;'>
                                                Acceso a mi cuenta
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

</html>", $_POST['payer_email']);
        if ($sendConfirmation) {
            return true;
        } else {
            return false;
        }
    }
    protected $BBDD;
    protected $driver;
}
