<?php
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License as LicenseResourceRepository;

add_filter("tera_notif_add_variables", "tera_dlm_add_var_lic", 10, 2);
add_action("tera_notif_add_variables_page", "add_variables_dlm", 12);

function getCustomerLicenseKeys_CRM($order) {
    if(is_plugin_active("terachat_notifications_dlm_extension/terachat_notifications_dlm_extension.php")){
        $data = array();

        /** @var WC_Order_Item_Product $item_data */
        foreach ($order->get_items() as $item_data) {
    
            /** @var WC_Product_Simple|WC_Product_Variation $product */
            $product = $item_data->get_product();
    
            // Check if the product has been activated for selling.
            if (!get_post_meta($product->get_id(), 'dlm_licensed_product', true)) {
                continue;
            }
            
            /** @var LicenseResourceModel[] $licenses */
            $licenses = LicenseResourceRepository::instance()->findAllBy(
                array(
                    'order_id' => $order->get_id(),
                    'product_id' => $product->get_id()
                )
            );
    
            $data[$product->get_id()]['name'] = $product->get_name();
            $data[$product->get_id()]['keys'] = $licenses;
        }
    
        return $data;
    }
}

function tera_dlm_add_var_lic($msg, $orden) {

        $rxLicenses = "/\{\{.+\}\}/im";
        preg_match_all($rxLicenses, $msg, $rxMatches);
        if(class_exists("WC_Subscription"))  $customer = getCustomerLicenseKeys_CRM(wcs_order_contains_renewal( $orden )  ? \WC_Subscriptions_Renewal_Order::get_parent_order( $orden )  : $orden);
        else $customer = getCustomerLicenseKeys_CRM($orden);
        $orden->add_order_note(json_encode($customer));
        foreach($rxMatches[0] as $rxMatch){
            $match = substr($rxMatch, 2, -2);
            
            $x = 1;
            $licences = [];
            foreach($customer as $k => $c){
                foreach($c["keys"] as $key){
                    $aux = $match;
                    /** @var LicenseResourceModel $license */
                    $license = $key;
                    $aux = str_replace("[account-c]", $x, $aux);
                    $aux = str_replace("[account-name]", $c["name"], $aux);
                    $aux = str_replace("[account-key]", $license->getDecryptedLicenseKey(), $aux);
                    $aux = str_replace("[account-exp-f1a]", date("d/m/Y H:i", strtotime($license->getExpiresAt())), $aux);
                    $aux = str_replace("[account-exp-f1b]", date("d/m/Y h:i a", strtotime($license->getExpiresAt())), $aux);
                    $aux = str_replace("[account-exp-f1c]", date("d/m/Y", strtotime($license->getExpiresAt())), $aux);
                    $aux = str_replace("[account-exp-f2a]", date("m/d/Y H:i", strtotime($license->getExpiresAt())), $aux);
                    $aux = str_replace("[account-exp-f2b]", date("m/d/Y h:i a", strtotime($license->getExpiresAt())), $aux);
                    $aux = str_replace("[account-exp-f2c]", date("m/d/Y", strtotime($license->getExpiresAt())), $aux);
                    $aux = str_replace("[account-exp-f3]", $license->getExpiresAt(), $aux);
                    $x++;
                    $licences[] = $aux;
                }
            }
            $msg = str_replace($rxMatch, implode("\n", $licences), $msg);
        }
    return $msg;
}

function add_variables_dlm(){ ?>
    <h3 class="text-muted">Detalles Cuentas de acceso</h3>

    <span>Para hacer uso de estas variables, debe utilizarlas dentro de llaves dobles: Ejemplo: <code>{{[account-c]. [account-name] Claves de acceso: [account-key], expira en: [account-exp-f1a]}}</code>. Mostrará todas las claves de acceso que el cliente ha comprado, una por línea.</span><br><br>account
    <code class="shortcw">[account-c]</code>: <span class="text-muted">Cuentas de acceso: Contador.</span><br>
    <code class="shortcw">[account-name]</code>: <span class="text-muted">Cuentas de acceso: Nombre.</span><br>
    <code class="shortcw">[account-key]</code>: <span class="text-muted">Cuentas de acceso: Clave.</span><br>
    <code class="shortcw">[account-exp-f1a]</code>: <span class="text-muted">Cuentas de acceso: Fecha de expiración en formato <b>dd/mm/aaaa HH:mm (24 horas)</b>.</span><br>
    <code class="shortcw">[account-exp-f1b]</code>: <span class="text-muted">Cuentas de acceso: Fecha de expiración en formato <b>dd/mm/aaaa hh:mm am/pm</b>.</span><br>
    <code class="shortcw">[account-exp-f1c]</code>: <span class="text-muted">Cuentas de acceso: Fecha de expiración en formato <b>dd/mm/aaaa</b>.</span><br>
    <code class="shortcw">[account-exp-f2a]</code>: <span class="text-muted">Cuentas de acceso: Fecha de expiración en formato <b>mm/dd/aaaa HH:mm (24 horas)</b>.</span><br>
    <code class="shortcw">[account-exp-f2b]</code>: <span class="text-muted">Cuentas de acceso: Fecha de expiración en formato <b>mm/dd/aaaa hh:mm am/pm</b>.</span><br>
    <code class="shortcw">[account-exp-f2c]</code>: <span class="text-muted">Cuentas de acceso: Fecha de expiración en formato <b></b>mm/dd/aaaa</b>.</span><br>
    <code class="shortcw">[account-exp-f3]</code>: <span class="text-muted">Cuentas de acceso: Fecha de expiración en formato <b>aaaa-mm-dd HH:mm</b></span><br>
<?php }