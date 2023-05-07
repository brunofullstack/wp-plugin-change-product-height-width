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
            'id' => 'wp_g_h_w_plugin_custom_field_height',
            'name' => 'wp_g_h_w_plugin_custom_field_height',
            'label' => __('Altura Inicial (cm)', 'meu-plugin'),
            'placeholder' => '',
            'desc_tip' => 'true',
            'description' => __('Estas medidas irão definir a proporção.', 'meu-plugin'),
        )
    );
    woocommerce_wp_text_input(
        array(
            'id' => 'wp_g_h_w_plugin_custom_field_width',
            'name' => 'wp_g_h_w_plugin_custom_field_width',
            'label' => __('Largura Inicial (cm)', 'meu-plugin'),
            'placeholder' => '',
            'desc_tip' => 'true',
            'description' => __('Estas medidas irão definir a proporção.', 'meu-plugin'),
        )
    );

    ?>
    <script>
        document.getElementById("wp_g_h_w_plugin_custom_field_width").addEventListener("keyup", calcularArea)
        function calcularArea() {
            // Obter os valores das dimensões em centímetros
            let cmAltura = document.getElementById("wp_g_h_w_plugin_custom_field_height").value;
            let cmLargura = document.getElementById("wp_g_h_w_plugin_custom_field_width").value;

            // Converter as dimensões de centímetros para metros
            let metrosAltura = cmAltura / 100;
            let metrosLargura = cmLargura / 100;

            // Calcular a área em metros quadrados
            let areaMetrosQuadrados = metrosAltura * metrosLargura;

            // Exibir o resultado na página
            // document.getElementById("resultado").innerHTML = "A área é de " + areaMetrosQuadrados + " metros quadrados.";

            // Exibir o resultado na página
            document.getElementById("_regular_price").value = areaMetrosQuadrados;


            //Vefica o range em m² para definir o preço
            //((pegas os preços antes no banco))
            <?php
            global $wpdb;
            // Nome da tabela
            $nome_tabela = $wpdb->prefix . 'wp_global_height_width_plugin';

            // Consulta SQL para obter o primeiro registro
            $sql = "SELECT * FROM wp_global_height_width_plugin LIMIT 1";

            // Obter o primeiro registro da tabela
            $registro = $wpdb->get_row($sql);
            // $json_configured_values = json_encode($registro);
            // echo $json_configured_values;
            ?>
            switch (areaMetrosQuadrados > 0) {
                case (areaMetrosQuadrados <= 0.5):
                    document.getElementById("_regular_price").value = <?php echo $registro->preco_0_05; ?> * areaMetrosQuadrados
                    break;
                case (areaMetrosQuadrados >= 0.6 && areaMetrosQuadrados <= 1):
                    document.getElementById("_regular_price").value = <?php echo $registro->preco_05_1; ?> * areaMetrosQuadrados
                    break;
                case (areaMetrosQuadrados >= 1.1 && areaMetrosQuadrados <= 3):
                    document.getElementById("_regular_price").value = <?php echo $registro->preco_1_3; ?> * areaMetrosQuadrados
                    break;
                case (areaMetrosQuadrados >= 3.1 && areaMetrosQuadrados <= 5):
                    document.getElementById("_regular_price").value = <?php echo $registro->preco_3_5; ?> * areaMetrosQuadrados
                    break;
                default:
                    console.log("O valor é maior ou igual a 15.");
                    break;
            }

        }
    </script>
    <?php
}
// Recupera o valor do campo no formulário de edição do produto
add_action('woocommerce_process_product_meta', 'salvar_novo_campo');

function salvar_novo_campo($post_id)
{
    $altura = isset($_POST['wp_g_h_w_plugin_custom_field_height']) ? sanitize_text_field($_POST['wp_g_h_w_plugin_custom_field_height']) : '';
    $largura = isset($_POST['wp_g_h_w_plugin_custom_field_width']) ? sanitize_text_field($_POST['wp_g_h_w_plugin_custom_field_width']) : '';

    update_post_meta($post_id, 'wp_g_h_w_plugin_custom_field_height', $altura);
    update_post_meta($post_id, 'wp_g_h_w_plugin_custom_field_width', $largura);
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
        echo '<input required name="wp_g_h_w_plugin_altura" type="number" step="0.01" id="wp_g_h_w_plugin_altura" value="' . $campo1 . '" class="regular-text">';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="wp_g_h_w_plugin_largura">Largura máxima (cm):</label></th>';
        echo '<td>';
        echo '<input required name="wp_g_h_w_plugin_largura" type="number" step="0.01" id="wp_g_h_w_plugin_largura" value="' . $campo2 . '" class="regular-text">';
        echo '</td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';


        echo '<h2>Preços</h2>';
        echo '<table class="form-table"><tbody><tr><th scope="row"><label for="wp_g_h_w_plugin_preco_base_0_05">0 a 0,5M² em R$:</label></th>';
        echo '<td>';
        echo '<input required name="wp_g_h_w_plugin_preco_base_0_05" type="number" step="0.01" id="wp_g_h_w_plugin_preco_base_0_05" value="' . $campo3 . '" class="regular-text">';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="wp_g_h_w_plugin_preco_base_05_1">0,5 a 1M² em R$:</label></th>';
        echo '<td>';
        echo '<input required name="wp_g_h_w_plugin_preco_base_05_1" type="number" step="0.01" id="wp_g_h_w_plugin_preco_base_05_1" value="' . $campo4 . '" class="regular-text">';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="wp_g_h_w_plugin_preco_base_1_3">1 a 3M² em R$:</label></th>';
        echo '<td>';
        echo '<input required name="wp_g_h_w_plugin_preco_base_1_3" type="number" step="0.01" id="wp_g_h_w_plugin_preco_base_1_3" value="' . $campo5 . '" class="regular-text">';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="wp_g_h_w_plugin_preco_base_3_5">3 a 5M² em R$:</label></th>';
        echo '<td>';
        echo '<input required name="wp_g_h_w_plugin_preco_base_3_5" type="number" step="0.01" id="wp_g_h_w_plugin_preco_base_3_5" value="' . $campo6 . '" class="regular-text">';
        echo '</td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        echo '<p>Todos os campos são obrigarórios<span style="color: red"> *</span></p>';

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

        echo '<div class="alert alert-success">Configurações salvas com sucesso! <br /> <a href=""> << Voltar</a></div>';
        exit;

    } catch (Exception $ex) {
        echo $ex;
        exit;
    }

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

/* CAMPO PERSONALIZADO NA PÁGINA DE PRODUTO */
// Adicionar campos personalizados na página de produto
add_action('woocommerce_before_add_to_cart_button', 'meus_campos_personalizados', 10);

function meus_campos_personalizados()
{
    // echo '<div class="meus-campos">';
    // echo '<p>Se preferir personalize a altura e largura que preferir digitando abaixo:</p>';
    // woocommerce_form_field('altura', array(
    //     'id' => 'checkout_altura',
    //     'name' => 'checkout_altura',
    //     'type' => 'text',
    //     'class' => array('meu-campo1'),
    //     'label' => __('Altura'),
    //     'required' => true,
    //     'placeholder' => __('Digite um valor para altura'),
    // ), '');
    // woocommerce_form_field('largura', array(
    //     'id' => 'checkout_largura',
    //     'name' => 'checkout_largura',
    //     'type' => 'text',
    //     'class' => array('meu-campo2'),
    //     'label' => __('Largura'),
    //     'required' => true,
    //     'placeholder' => __('Digite um valor para altura'),
    // ), '');
    // echo '</div>';
    // echo '<input id="price_updated" name="price_updated" type="hidden" />';
    // echo '<div id="new-amount"></div>';

    echo '<div class="container">';
    echo '<h1>Selecione a medida desejada:</h1>';
    echo '<form>';
    echo '<div class="form-group">';
    echo '<label for="selectMedida">Selecione a medida:</label>';
    echo '<select class="form-control" id="selectMedida" name="medida">';
    echo '<option value="altura">Altura</option>';
    echo '<option value="largura">Largura</option>';
    echo '</select>';
    echo '</div>';
    echo '<div class="row" id="divMedida">';
    echo '<label id="largura_label" class="col-md-5" for="inputMedida">Largura em cm:</label>';
    echo '<label id="altura_label" class="col-md-5" for="inputMedida">Altura em cm:</label>';
    echo '<div id="div_altura" class="col-md-7">';
    woocommerce_form_field('altura', array(
        'id' => 'checkout_altura',
        'name' => 'checkout_altura',
        'type' => 'text',
        'class' => array(''),
        'required' => true,
        'placeholder' => __('Insira a altura em centímetros'),
    ), '');
    echo '</div>';
    echo '<div id="div_largura" class="col-md-7">';
    woocommerce_form_field('largura', array(
        'id' => 'checkout_largura',
        'name' => 'checkout_largura',
        'type' => 'text',
        'class' => array(''),
        'required' => true,
        'placeholder' => __('Insira a largura em centímetros'),
    ), '');
    echo '</div>';
    echo '</div>';
    echo '<input id="price_updated" name="price_updated" type="hidden" />';
    echo '</form>';
    echo '</div>';
    echo '<div id="new-amount"></div>';

    ?>
    <!-- Importando os arquivos do Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>
    <!-- Script para mostrar/esconder o input de acordo com a opção selecionada -->
    <script>
        $(document).ready(function () {
            $("#altura_label").show();
            $("#div_altura").show();
            $("#largura_label").hide();
            $("#div_largura").hide();
            $("#divMedida").show();
            function cleanInputs() {
                document.getElementById("checkout_largura").value = "";
                document.getElementById("checkout_altura").value = "";
            }
            $("#selectMedida").change(function () {
                if ($("#selectMedida").val() == "altura") {
                    cleanInputs();
                    $("#divMedida").show();
                    $("#div_altura").show();
                    $("#largura_label").hide();
                    $("#div_largura").hide();
                    $("#altura_label").show();
                    $("#inputMedida").attr("placeholder", "Insira a altura em centímetros");
                } else if ($("#selectMedida").val() == "largura") {
                    cleanInputs();
                    $("#divMedida").show();
                    $("#div_largura").show();
                    $("#altura_label").hide();
                    $("#div_altura").hide();
                    $("#largura_label").show();
                    $("#inputMedida").attr("placeholder", "Insira a largura em centímetros");
                } else {
                    $("#divMedida").hide();
                }
            });
        });
    </script>
    <script type="text/javascript">
        document.getElementById("checkout_largura").addEventListener("keyup", calcularAreaCheckout);
        document.getElementById("checkout_altura").addEventListener("keyup", calcularAreaCheckout);
        function calcularAreaCheckout() {
            //Vefica o range em m² para definir o preço
            //((pegas os preços antes no banco))
            <?php
            global $wpdb;
            // Nome da tabela
            $nome_tabela = $wpdb->prefix . 'wp_global_height_width_plugin';

            // Consulta SQL para obter o primeiro registro
            $sql = "SELECT * FROM wp_global_height_width_plugin LIMIT 1";

            // Obter o primeiro registro da tabela
            $registro = $wpdb->get_row($sql);
            // $json_configured_values = json_encode($registro);
            // echo $json_configured_values;
            ?>
            // Proporção = Largura / Altura
            const proporcao = <?php echo $registro->largura_maxima; ?> / <?php echo $registro->altura_maxima; ?>;


            // Obter os valores das dimensões em centímetros
            let cmAltura = document.getElementById("checkout_altura").value;
            let cmLargura = document.getElementById("checkout_largura").value;

            if (cmAltura > 0) {
                cmLargura = cmAltura * proporcao;
            } else {
                cmAltura = cmLargura / proporcao;
            }
            // Obter os valores das dimensões em centímetros
            var selectMedida = document.getElementById("selectMedida");
            selectMedida.addEventListener("change", function () {
                if (selectMedida.value == "altura") {
                    cmLargura = cmAltura * proporcao;
                    cmLargura.toFixed(2)
                } else if (selectMedida.value == "largura") {
                    cmAltura = cmLargura / proporcao;
                    cmAltura.toFixed(2)
                }
            });

            // Converter as dimensões de centímetros para metros
            let metrosAltura = cmAltura / 100;
            let metrosLargura = cmLargura / 100;

            // Calcular a área em metros quadrados
            let areaMetrosQuadrados = metrosAltura * metrosLargura;

            // Exibir o resultado na página
            // selecionar o elemento HTML que deseja limpar e atualizar
            const newPrice = document.getElementById("new-amount");
            let finalAmount = document.getElementById("price_updated");

            // limpar o conteúdo do elemento HTML
            newPrice.innerHTML = '';

            // switch p/ criar uma nova tag HTML
            let new_amount = 0;
            switch (areaMetrosQuadrados > 0) {
                case (areaMetrosQuadrados <= 0.5):
                    new_amount = (<?php echo $registro->preco_0_05; ?> * areaMetrosQuadrados).toFixed(2);
                    newPrice.innerHTML = 'O novo valor para as medidas ' + cmAltura + 'cm X ' + cmLargura + 'cm' + ' será de: <h2 class="new_amount"></h2>R$ ' + new_amount + '</h2>';
                    finalAmount.value = (<?php echo $registro->preco_0_05; ?> * areaMetrosQuadrados).toFixed(2);
                    break;
                case (areaMetrosQuadrados >= 0.6 && areaMetrosQuadrados <= 1):
                    new_amount = (<?php echo $registro->preco_05_1; ?> * areaMetrosQuadrados).toFixed(2);
                    newPrice.innerHTML = 'O novo valor para as medidas ' + cmAltura + 'cm X ' + cmLargura + 'cm' + ' será de: <h2 class="new_amount">R$ ' + new_amount + '</h2>';
                    finalAmount.value = (<?php echo $registro->preco_05_1; ?> * areaMetrosQuadrados).toFixed(2);
                    break;
                case (areaMetrosQuadrados >= 1.1 && areaMetrosQuadrados <= 3):
                    new_amount = (<?php echo $registro->preco_1_3; ?> * areaMetrosQuadrados).toFixed(2);
                    newPrice.innerHTML = 'O novo valor para as medidas ' + cmAltura + 'cm X ' + cmLargura + 'cm' + ' será de: <h2 class="new_amount">R$ ' + new_amount + '</h2>';
                    finalAmount.value = (<?php echo $registro->preco_1_3; ?> * areaMetrosQuadrados).toFixed(2);
                    break;
                case (areaMetrosQuadrados >= 3.1 && areaMetrosQuadrados <= 5):
                    new_amount = (<?php echo $registro->preco_3_5; ?> * areaMetrosQuadrados).toFixed(2);
                    newPrice.innerHTML = 'O novo valor para as medidas ' + cmAltura + 'cm X ' + cmLargura + 'cm' + ' será de: <h2 class="new_amount">R$ ' + new_amount + '</h2>';
                    finalAmount.value = (<?php echo $registro->preco_3_5; ?> * areaMetrosQuadrados).toFixed(2);
                    break;
                default:
                    newPrice.innerHTML = "O valor é maior que a proporção máxima! Insira outro valor!";
                    break;
            }

        }
    </script>
    <style>
        .new_amount{ color: #f03764;}
    </style>
    <?php

}


// Atualizar o preço do produto com base nos campos personalizados
add_filter('woocommerce_add_cart_item_data', 'atualizar_preco_do_produto', 10, 2);

function atualizar_preco_do_produto($cart_item_data, $product_id)
{
    // Obter o valor dos campos personalizados
    $novo_preco = isset($_POST['price_updated']) ? $_POST['price_updated'] : '';

    // Armazenar o novo preço do produto no carrinho
    $cart_item_data['new_price'] = $novo_preco;
    return $cart_item_data;
}

// Exibir o novo preço do produto no carrinho e na página de checkout
add_filter('woocommerce_cart_item_price', 'exibir_novo_preco_do_produto', 10, 3);

function exibir_novo_preco_do_produto($preco, $cart_item, $cart_item_key)
{
    if (isset($cart_item['new_price'])) {
        $preco = wc_price($cart_item['new_price']);
    }
    return $preco;
}

// Atualizar o preço total do pedido com base no novo preço do produto
add_action('woocommerce_before_calculate_totals', 'atualizar_preco_total_do_pedido');

function atualizar_preco_total_do_pedido($cart)
{
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    // Loop pelos itens do carrinho
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['new_price'])) {
            $cart_item['data']->set_price($cart_item['new_price']);
        }
    }
}
/* CAMPO PERSONALIZADO NA PÁGINA DE PRODUTO */


// add_action( 'template_redirect', 'my_callback' );
function my_callback()
{
    if (some_condition()) {
        wp_redirect("http://www.example.com/contact-us", 301);
        exit();
    }
}
?>