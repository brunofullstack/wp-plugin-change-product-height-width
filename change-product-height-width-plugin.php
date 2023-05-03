<?php
/**
 * Plugin Name: Change product height width plugin
 * Description: Cadastro global que se aplica a todos os produtos é possivel definir um limite máximo e mínimo da medida e também os valores de acordo com o tamanho.
 * Version: 1.0.0
 * Author: Bruno Carvalho
 * Author URI: https://brunofullstack.github.io/
 **/

// Adiciona um novo campo no formulário de edição do produto
add_action('woocommerce_product_options_general_product_data', 'meu_plugin_custom_field');
function meu_plugin_custom_field()
{
    woocommerce_wp_text_input(
        array(
            'id' => 'meu_plugin_custom_field',
            'label' => __('Altura Inicial (cm)', 'meu-plugin'),
            'placeholder' => '',
            'desc_tip' => 'true',
            'description' => __('Digite aqui a descrição do campo personalizado.', 'meu-plugin'),
        )
    );
    woocommerce_wp_text_input(
        array(
            'id' => 'meu_plugin_custom_field',
            'label' => __('Largura Inicial (cm)', 'meu-plugin'),
            'placeholder' => '',
            'desc_tip' => 'true',
            'description' => __('Digite aqui a descrição do campo personalizado.', 'meu-plugin'),
        )
    );
}

// Salva o valor do campo personalizado
add_action('woocommerce_process_product_meta', 'meu_plugin_custom_field_save');
function meu_plugin_custom_field_save($post_id)
{
    $product = wc_get_product($post_id);
    $custom_field = isset($_POST['meu_plugin_custom_field']) ? $_POST['meu_plugin_custom_field'] : '';
    $product->update_meta_data('meu_plugin_custom_field', sanitize_text_field($custom_field));
    $product->save();
}

// Adiciona uma página de configuração para o plugin
add_action('admin_menu', 'meu_plugin_menu');
function meu_plugin_menu()
{
    add_menu_page(
        'Configurações de preço/altura/largura',
        // título da página
        'Configurações de preço/altura/largura',
        // título do menu
        'manage_options',
        // permissão de acesso
        'meu-plugin-settings',
        // slug da página
        'meu_plugin_settings_page' // função que renderiza a página
    );
}


// Cria o conteúdo da página de configuração
function meu_plugin_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $altura = get_option('meu_plugin_altura');
    $largura = get_option('meu_plugin_largura');
    $preco_base = get_option('meu_plugin_preco_base');

    if (isset($_POST['submit'])) {
        $altura = $_POST['meu_plugin_altura'];
        $largura = $_POST['meu_plugin_largura'];
        $preco_base = $_POST['meu_plugin_preco_base'];

        update_option('meu_plugin_altura', $altura);
        update_option('meu_plugin_largura', $largura);
        update_option('meu_plugin_preco_base', $preco_base);
    }

    ?>
    <div class="wrap">
        <h1>
            <?php echo esc_html(get_admin_page_title()); ?>
        </h1>
        <label for="ping_sites">
            Cadastro global que se aplica a todos os produtos. Aqui é possivel definir um limite máximo e mínimo da medida e também os valores de acordo com o tamanho.
        </label>
        <form method="post">
            <h2>
                Tamanho
            </h2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="meu_plugin_altura">Altura máxima (cm):</label></th>
                        <td><input name="meu_plugin_altura" type="number" step="0.01" id="meu_plugin_altura"
                                value="<?php echo esc_attr($altura); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="meu_plugin_largura">Largura máxima (cm):</label></th>
                        <td><input name="meu_plugin_largura" type="number" step="0.01" id="meu_plugin_largura"
                                value="<?php echo esc_attr($largura); ?>" class="regular-text"></td>
                    </tr>
                </tbody>
            </table>
            <h2>
                Preços
            </h2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="meu_plugin_preco_base">0 a 0,5M² em R$:</label></th>
                        <td><input name="meu_plugin_preco_base" type="number" step="0.01" id="meu_plugin_preco_base"
                                value="<?php echo esc_attr($preco_base); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="meu_plugin_preco_base">0,5 a 1M² em R$:</label></th>
                        <td><input name="meu_plugin_preco_base" type="number" step="0.01" id="meu_plugin_preco_base"
                                value="<?php echo esc_attr($preco_base); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="meu_plugin_preco_base">1 a 3M² em R$:</label></th>
                        <td><input name="meu_plugin_preco_base" type="number" step="0.01" id="meu_plugin_preco_base"
                                value="<?php echo esc_attr($preco_base); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="meu_plugin_preco_base">3 a 5M² em R$:</label></th>
                        <td><input name="meu_plugin_preco_base" type="number" step="0.01" id="meu_plugin_preco_base"
                                value="<?php echo esc_attr($preco_base); ?>" class="regular-text"></td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// add_action( 'template_redirect', 'my_callback' );
function my_callback()
{
    if (some_condition()) {
        wp_redirect("http://www.example.com/contact-us", 301);
        exit();
    }
}
?>