<?php
/**
 * Plugin Name: Change product height width plugin
 * Description: Cadastro global que se aplica a todos os produtos é possivel definir um limite máximo e mínimo da medida e também os valores de acordo com o tamanho.
 * Version: 1.0.0
 * Author: Bruno Carvalho
 * Author URI: https://brunofullstack.github.io/
 **/

// Adiciona um novo campo no formulário de edição do produto
add_action('woocommerce_product_options_general_product_data', 'wp_g_h_w_plugin_custom_field');
function wp_g_h_w_plugin_custom_field()
{
    woocommerce_wp_text_input(
        array(
            'id' => 'wp_g_h_w_plugin_custom_field',
            'label' => __('Altura Inicial (cm)', 'meu-plugin'),
            'placeholder' => '',
            'desc_tip' => 'true',
            'description' => __('Estas medidas irão definir a proporção', 'meu-plugin'),
        )
    );
    woocommerce_wp_text_input(
        array(
            'id' => 'wp_g_h_w_plugin_custom_field',
            'label' => __('Largura Inicial (cm)', 'meu-plugin'),
            'placeholder' => '',
            'desc_tip' => 'true',
            'description' => __('Estas medidas irão definir a proporção', 'meu-plugin'),
        )
    );
}

// Salva o valor do campo personalizado
add_action('woocommerce_process_product_meta', 'wp_g_h_w_plugin_custom_field_save');
function wp_g_h_w_plugin_custom_field_save($post_id)
{
    $product = wc_get_product($post_id);
    $custom_field = isset($_POST['wp_g_h_w_plugin_custom_field']) ? $_POST['wp_g_h_w_plugin_custom_field'] : '';
    $product->update_meta_data('wp_g_h_w_plugin_custom_field', sanitize_text_field($custom_field));
    $product->save();
}

// Adiciona uma página de configuração para o plugin
add_action('admin_menu', 'wp_g_h_w_plugin_menu');
function wp_g_h_w_plugin_menu()
{
    add_menu_page(
        'Configurações de preço/altura/largura',
        // título da página
        'Configurações de preço/altura/largura',
        // título do menu
        'manage_options',
        // permissão de acesso
        'wp-change-hw-plugin',
        // slug da página
        'wp_g_h_w_plugin_settings_page' // função que renderiza a página
    );
}

function get_setted_values()
{
    global $wpdb;

    // Nome da tabela
    $nome_tabela = $wpdb->prefix . 'wp_global_height_width_plugin';

    // Consulta SQL para obter o primeiro registro
    $sql = "SELECT * FROM wp_global_height_width_plugin LIMIT 1";

    // Obter o primeiro registro da tabela
    $registro = $wpdb->get_row($sql);
    // var_dump($resultados[0]);

    // Verificar se há resultados
    if ($registro) {
        $campo1 = $registro->largura_maxima;
        $campo2 = $registro->altura_maxima;
        $campo3 = $registro->preco_0_05;
        $campo4 = $registro->preco_05_1;
        $campo5 = $registro->preco_1_3;
        $campo6 = $registro->preco_3_5;
        // Montar um formulário com os campos preenchidos pelos valores do registro
        echo '<h2>Tamanho</h2>';
        echo '<table class="form-table"><tbody><tr><th scope="row"><label for="wp_g_h_w_plugin_altura">Altura máxima (cm):</label></th>';
        echo '<td>';
        echo '<input name="wp_g_h_w_plugin_altura" type="number" step="0.01" id="wp_g_h_w_plugin_altura" value="' . $campo1 . '" class="regular-text">';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="wp_g_h_w_plugin_largura">Largura máxima (cm):</label></th>';
        echo '<td>';
        echo '<input name="wp_g_h_w_plugin_largura" type="number" step="0.01" id="wp_g_h_w_plugin_largura" value="' . $campo2 . '" class="regular-text">';
        echo '</td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';


        echo '<h2>Preços</h2>';
        echo '<table class="form-table"><tbody><tr><th scope="row"><label for="wp_g_h_w_plugin_preco_base_0_05">0 a 0,5M² em R$:</label></th>';
        echo '<td>';
        echo '<input name="wp_g_h_w_plugin_preco_base_0_05" type="number" step="0.01" id="wp_g_h_w_plugin_preco_base_0_05" value="' . $campo3 . '" class="regular-text">';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="wp_g_h_w_plugin_preco_base_05_1">0,5 a 1M² em R$:</label></th>';
        echo '<td>';
        echo '<input name="wp_g_h_w_plugin_preco_base_05_1" type="number" step="0.01" id="wp_g_h_w_plugin_preco_base_05_1" value="' . $campo4 . '" class="regular-text">';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="wp_g_h_w_plugin_preco_base_1_3">1 a 3M² em R$:</label></th>';
        echo '<td>';
        echo '<input name="wp_g_h_w_plugin_preco_base_1_3" type="number" step="0.01" id="wp_g_h_w_plugin_preco_base_1_3" value="' . $campo5 . '" class="regular-text">';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="wp_g_h_w_plugin_preco_base_3_5">3 a 5M² em R$:</label></th>';
        echo '<td>';
        echo '<input name="wp_g_h_w_plugin_preco_base_3_5" type="number" step="0.01" id="wp_g_h_w_plugin_preco_base_3_5" value="' . $campo6 . '" class="regular-text">';
        echo '</td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';

    } else {
        ?>

        <h2>Tamanho</h2>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="wp_g_h_w_plugin_altura">Altura máxima (cm):</label></th>
                    <td><input name="wp_g_h_w_plugin_altura" type="number" step="0.01" id="wp_g_h_w_plugin_altura"
                            value="<?php echo esc_attr($altura); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="wp_g_h_w_plugin_largura">Largura máxima (cm):</label></th>
                    <td><input name="wp_g_h_w_plugin_largura" type="number" step="0.01" id="wp_g_h_w_plugin_largura"
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
                    <th scope="row"><label for="wp_g_h_w_plugin_preco_base_0_05">0 a 0,5M² em R$:</label></th>
                    <td><input name="wp_g_h_w_plugin_preco_base_0_05" type="number" step="0.01" id="wp_g_h_w_plugin_preco_base"
                            value="<?php echo esc_attr($preco_base_0_05); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="wp_g_h_w_plugin_preco_base_05_1">0,5 a 1M² em R$:</label></th>
                    <td><input name="wp_g_h_w_plugin_preco_base_05_1" type="number" step="0.01" id="wp_g_h_w_plugin_preco_base"
                            value="<?php echo esc_attr($preco_base_05_1); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="wp_g_h_w_plugin_preco_base_1_3">1 a 3M² em R$:</label></th>
                    <td><input name="wp_g_h_w_plugin_preco_base_1_3" type="number" step="0.01" id="wp_g_h_w_plugin_preco_base"
                            value="<?php echo esc_attr($preco_base_1_3); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="wp_g_h_w_plugin_preco_base_3_5">3 a 5M² em R$:</label></th>
                    <td><input name="wp_g_h_w_plugin_preco_base_3_5" type="number" step="0.01" id="wp_g_h_w_plugin_preco_base"
                            value="<?php echo esc_attr($preco_base_3_5); ?>" class="regular-text"></td>
                </tr>
            </tbody>
        </table>
        <?php
    }
}

// Cria o conteúdo da página de configuração
function wp_g_h_w_plugin_settings_page()
{

    if (!current_user_can('manage_options')) {
        return;
    }

    $altura = get_option('wp_g_h_w_plugin_altura');
    $largura = get_option('wp_g_h_w_plugin_largura');
    $preco_base_0_05 = get_option('wp_g_h_w_plugin_preco_base_0_05');
    $preco_base_05_1 = get_option('wp_g_h_w_plugin_preco_base_05_1');
    $preco_base_1_3 = get_option('wp_g_h_w_plugin_preco_base_1_3');
    $preco_base_3_5 = get_option('wp_g_h_w_plugin_preco_base_3_5');

    if (isset($_POST['submit'])) {
        $altura = $_POST['wp_g_h_w_plugin_altura'];
        $largura = $_POST['wp_g_h_w_plugin_largura'];
        $preco_base_0_05 = $_POST['wp_g_h_w_plugin_preco_base_0_05'];
        $preco_base_05_1 = $_POST['wp_g_h_w_plugin_preco_base_05_1'];
        $preco_base_1_3 = $_POST['wp_g_h_w_plugin_preco_base_1_3'];
        $preco_base_3_5 = $_POST['wp_g_h_w_plugin_preco_base_3_5'];

        update_option('wp_g_h_w_plugin_altura', $altura);
        update_option('wp_g_h_w_plugin_largura', $largura);
        update_option('wp_g_h_w_plugin_preco_base', $preco_base_0_05);
        update_option('wp_g_h_w_plugin_preco_base', $preco_base_05_1);
        update_option('wp_g_h_w_plugin_preco_base', $preco_base_1_3);
        update_option('wp_g_h_w_plugin_preco_base', $preco_base_3_5);
    }

    ?>
    <div class="wrap">
        <h1>
            <?php echo esc_html(get_admin_page_title()); ?>
        </h1>
        <label>
            Cadastro global que se aplica a todos os produtos. Aqui é possivel definir um limite máximo e mínimo da medida e
            também os valores de acordo com o tamanho.
        </label>

        <form method="post">
            <input type="hidden" name="action" value="salvar_informacoes">
            <input type="hidden" name="my_form" value="1">


            <?php get_setted_values(); ?>


            <?php submit_button(); ?>
        </form>

    </div>
    <?php
}
// check if form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['my_form'])) {
    // call your function
    salvar_informacoes();
}
function salvar_informacoes()
{
    global $wpdb;

    // Apagar todas os registros anteriores para inseriro novo
    // INSIRA O CÓDIGO AQUI
    $sql = 'SELECT COUNT(*) FROM wp_global_height_width_plugin';
    $counter = $wpdb->get_var($sql);
    if ($counter > 0) {
        $sql_limpar = 'TRUNCATE wp_global_height_width_plugin';
        $wpdb->query($sql_limpar); // executa a consulta de limpeza
    }

    // Inserindo novo registro
    $altura = $_POST['wp_g_h_w_plugin_altura'];
    $largura = $_POST['wp_g_h_w_plugin_largura'];
    $preco_base_0_05 = $_POST['wp_g_h_w_plugin_preco_base_0_05'];
    $preco_base_05_1 = $_POST['wp_g_h_w_plugin_preco_base_05_1'];
    $preco_base_1_3 = $_POST['wp_g_h_w_plugin_preco_base_1_3'];
    $preco_base_3_5 = $_POST['wp_g_h_w_plugin_preco_base_3_5'];

    try {
        $wpdb->insert(
            'wp_global_height_width_plugin',
            array(
                'id' => 1,
                'altura_maxima' => $altura,
                'largura_maxima' => $largura,
                'preco_0_05' => $preco_base_0_05,
                'preco_05_1' => $preco_base_05_1,
                'preco_1_3' => $preco_base_1_3,
                'preco_3_5' => $preco_base_3_5,
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );

        $wpdb->close();

        echo 'Formulário salvo com sucesso!';
        // Add the success message to the redirected page
        add_action('wp_head', function () {
            echo '<div class="alert alert-success">Formulário salvo com sucesso!</div>';
        });

        exit;
    } catch (Exception $ex) {
        // jump to this part
        // if an exception occurred
    }


    // echo "Obrigado por se cadastrar";
    exit;
}

// END OF FORM


// Para verificar se uma tabela existe no WordPress e criar uma nova tabela usando a função global $wpdb
global $wpdb;

$table_exists = $wpdb->query("SHOW TABLES LIKE 'wp_global_height_width_plugin'");
if ($table_exists !== 1) {
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE wp_global_height_width_plugin (
        id INT(11) NOT NULL AUTO_INCREMENT,
        largura_maxima FLOAT NOT NULL,
        altura_maxima FLOAT NOT NULL,
        preco_0_05 DECIMAL(10, 2) NOT NULL,
        preco_05_1 DECIMAL(10, 2) NOT NULL,
        preco_1_3 DECIMAL(10, 2) NOT NULL,
        preco_3_5 DECIMAL(10, 2) NOT NULL,
        data_criacao DATETIME NOT NULL DEFAULT NOW(),
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
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