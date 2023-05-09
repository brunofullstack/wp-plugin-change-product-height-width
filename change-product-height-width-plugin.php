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
    echo '<h4 style="margin-left: 12px">Insira as medidas que irão definir a proporção</h4>';
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
    echo '<h5 id="alert" style="margin-left: 12px"></h5>';

    ?>
    <script>
        document.getElementById("wp_g_h_w_plugin_custom_field_height").addEventListener("keyup", callback("altura"))
        document.getElementById("wp_g_h_w_plugin_custom_field_width").addEventListener("keyup", callback("largura"))
        // Definindo uma função anônima que recebe um parâmetro e retorna uma função de callback
        function callback(parametro) {
            return function (event) {
                calcularArea(parametro);
            }
        };
        function calcularArea(inputName) {
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
            let new_regular_price = document.getElementById("_regular_price");
            switch (areaMetrosQuadrados > 0) {
                case (areaMetrosQuadrados <= 0.5):
                    new_regular_price.value = (<?php echo $registro->preco_0_05; ?> * areaMetrosQuadrados).toFixed(2)
                    break;
                case (areaMetrosQuadrados >= 0.6 && areaMetrosQuadrados <= 1):
                    new_regular_price.value = (<?php echo $registro->preco_05_1; ?> * areaMetrosQuadrados).toFixed(2)
                    break;
                case (areaMetrosQuadrados >= 1.1 && areaMetrosQuadrados <= 3):
                    new_regular_price.value = (<?php echo $registro->preco_1_3; ?> * areaMetrosQuadrados).toFixed(2)
                    break;
                case (areaMetrosQuadrados >= 3.1 && areaMetrosQuadrados <= 5):
                    new_regular_price.value = (<?php echo $registro->preco_3_5; ?> * areaMetrosQuadrados).toFixed(2)
                    break;
                default:
                    break;
            }

            let alert = document.getElementById("alert");
            
            if (cmAltura > <?php echo $registro->altura_maxima; ?> && inputName == 'altura') {
                alert.innerHTML = '';
                alert.innerHTML = '<span style="color:red; font-weight: bold;">ATENÇÃO! O máximo valor permitido para altura é de: <?php echo $registro->altura_maxima; ?> cm</span>'
                document.getElementById("wp_g_h_w_plugin_custom_field_height").value = 0;
            }
            if (cmLargura > <?php echo $registro->largura_maxima; ?> && inputName == 'largura') {
                alert.innerHTML = '';
                alert.innerHTML = '<span style="color:red; font-weight: bold;">ATENÇÃO! O máximo valor permitido para largura é de: <?php echo $registro->largura_maxima; ?> cm</span>'
                document.getElementById("wp_g_h_w_plugin_custom_field_width").value = 0;
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


/* CONFIG PAGE */
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
        $campo1 = $registro->altura_maxima;
        $campo2 = $registro->largura_maxima;
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
                            class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="wp_g_h_w_plugin_largura">Largura máxima (cm):</label></th>
                    <td><input name="wp_g_h_w_plugin_largura" type="number" step="0.01" id="wp_g_h_w_plugin_largura"
                            class="regular-text"></td>
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
                            class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="wp_g_h_w_plugin_preco_base_05_1">0,5 a 1M² em R$:</label></th>
                    <td><input name="wp_g_h_w_plugin_preco_base_05_1" type="number" step="0.01" id="wp_g_h_w_plugin_preco_base"
                            class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="wp_g_h_w_plugin_preco_base_1_3">1 a 3M² em R$:</label></th>
                    <td><input name="wp_g_h_w_plugin_preco_base_1_3" type="number" step="0.01" id="wp_g_h_w_plugin_preco_base"
                            class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="wp_g_h_w_plugin_preco_base_3_5">3 a 5M² em R$:</label></th>
                    <td><input name="wp_g_h_w_plugin_preco_base_3_5" type="number" step="0.01" id="wp_g_h_w_plugin_preco_base"
                            class="regular-text"></td>
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
/* ,/CONFIG PAGE */

/* CAMPO PERSONALIZADO NA PÁGINA DE PRODUTO */
// Adicionar campos personalizados na página de produto
add_action('woocommerce_before_add_to_cart_button', 'meus_campos_personalizados', 10);

function meus_campos_personalizados()
{
    $label_logo = "data:image/png;base64, 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";

    echo '<div class="container">';
    echo '<div class="form-group">';
    echo '<div><img style="display: inline-block;vertical-align: middle; margin-right: 5px" width="28px" src="' . $label_logo . '" alt="" /><h4 style="display: inline-block;vertical-align: middle;">Defina o tamanho:</h4></div>';
    echo '<div class="select_medidas">';
    echo '<select class="form-control" id="selectMedida" name="medida">';
    echo '<option value="altura">Inserir Altura</option>';
    echo '<option value="largura">Inserir Largura</option>';
    echo '</select>';
    echo '</div>';
    echo '</div>';
    echo '<div class="row" id="divMedida">';
    echo '<label id="largura_label" class="col-md-12" for="inputMedida">Largura em cm:</label>';
    echo '<label id="altura_label" class="col-md-12" for="inputMedida">Altura em cm:</label>';
    echo '<div id="div_altura" class="col-md-12">';
    woocommerce_form_field('altura', array(
        'id' => 'checkout_altura',
        'name' => 'checkout_altura',
        'type' => 'number',
        'class' => array(''),
        'required' => false,
        'placeholder' => __('Insira a altura em centímetros'),
    ), '');
    echo '</div>';
    echo '<div id="div_largura" class="col-md-7">';
    woocommerce_form_field('largura', array(
        'id' => 'checkout_largura',
        'name' => 'checkout_largura',
        'type' => 'number',
        'class' => array(''),
        'required' => false,
        'placeholder' => __('Insira a largura em centímetros'),
    ), '');
    echo '</div>';
    echo '</div>';
    echo '<input id="price_updated" name="price_updated" type="hidden" />';
    echo '<input id="altura_updated" name="altura_updated" type="hidden" />';
    echo '<input id="largura_updated" name="largura_updated" type="hidden" />';
    echo '</div>';
    echo '<div id="new-amount"></div>';

    ?>
    <style>
        .select_medidas {
            position: relative;
            display: inline-block;
            font-size: 16px;
            color: #333;
            border: 2px solid #ccc;
            border-radius: 4px;
            overflow: hidden;
            width: 200px;
        }

        .select_medidas select {
            background-color: transparent;
            border: none;
            padding: 8px;
            width: 100%;
            font-size: 16px;
            color: #333;
            cursor: pointer;
        }

        .select_medidas select:focus {
            outline: none;
        }

        .select_medidas::before {
            content: "\25BC";
            position: absolute;
            top: 0;
            right: 0;
            padding: 8px;
            background-color: #ccc;
            pointer-events: none;
        }

        .select_medidas:hover::before {
            background-color: #999;
            color: #fff;
        }
    </style>
    <!-- Script para mostrar/esconder o input de acordo com a opção selecionada -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var alturaLabel = document.getElementById("altura_label");
            var divAltura = document.getElementById("div_altura");
            var larguraLabel = document.getElementById("largura_label");
            var divLargura = document.getElementById("div_largura");
            var divMedida = document.getElementById("divMedida");

            alturaLabel.style.display = "block";
            divAltura.style.display = "block";
            larguraLabel.style.display = "none";
            divLargura.style.display = "none";
            divMedida.style.display = "block";

            function cleanInputs() {
                document.getElementById("checkout_largura").value = "";
                document.getElementById("checkout_altura").value = "";
            }

            var selectMedida = document.getElementById("selectMedida");
            selectMedida.addEventListener("change", function () {
                if (selectMedida.value == "altura") {
                    cleanInputs();
                    divMedida.style.display = "block";
                    divAltura.style.display = "block";
                    larguraLabel.style.display = "none";
                    divLargura.style.display = "none";
                    alturaLabel.style.display = "block";
                } else if (selectMedida.value == "largura") {
                    cleanInputs();
                    divMedida.style.display = "block";
                    divLargura.style.display = "block";
                    alturaLabel.style.display = "none";
                    divAltura.style.display = "none";
                    larguraLabel.style.display = "block";
                } else {
                    divMedida.style.display = "none";
                }
            });
        });

    </script>
    <script type="text/javascript">
        document.getElementById("checkout_largura").addEventListener("keyup", criarCallback("largura"));
        document.getElementById("checkout_altura").addEventListener("keyup", criarCallback("altura"));
        // Definindo uma função anônima que recebe um parâmetro e retorna uma função de callback
        function criarCallback(parametro) {
            return function (event) {
                calcularAreaCheckout(parametro);
            }
        };
        function calcularAreaCheckout(inputName) {
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


            const newPrice = document.getElementById("new-amount");
            let cmAltura = document.getElementById("checkout_altura").value;
            let cmLargura = document.getElementById("checkout_largura").value;

            <?php
            global $product;

            $product_id = $product->get_id(); // Obtém o ID do produto atual
        
            $largura_maxima = get_post_meta($product_id, 'wp_g_h_w_plugin_custom_field_width', true);
            $altura_maxima = get_post_meta($product_id, 'wp_g_h_w_plugin_custom_field_height', true);

            ?>
            // Proporção = Largura / Altura
            const proporcao = <?php echo $largura_maxima; ?> / <?php echo $altura_maxima; ?>;

            // Obter os valores das dimensões em centímetros
            if (cmAltura > 0) {
                cmLargura = cmAltura * proporcao;
                cmLargura = cmLargura.toFixed(2);
            } else {
                cmAltura = cmLargura / proporcao;
                cmAltura = cmAltura.toFixed(2);
            }

            // Converter as dimensões de centímetros para metros
            let metrosAltura = cmAltura / 100;
            let metrosLargura = cmLargura / 100;

            // Calcular a área em metros quadrados
            let areaMetrosQuadrados = metrosAltura * metrosLargura;

            // Exibir o resultado na página
            // selecionar o elemento HTML que deseja limpar e atualizar
            let finalAmount = document.getElementById("price_updated");
            let altura_updated = document.getElementById("altura_updated");
            let largura_updated = document.getElementById("largura_updated");
            altura_updated.value = cmAltura
            largura_updated.value = cmLargura


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
                    newPrice.innerHTML = "<span style='color:red; font-weight: bold;'>Insira outro valor</span>";
                    break;
            }


            if (cmAltura > <?php echo $registro->altura_maxima; ?> && inputName == 'altura') {
                newPrice.innerHTML = '';
                newPrice.innerHTML = '<span style="color:red; font-weight: bold;">ATENÇÃO! O máximo valor permitido para altura é de: <?php echo $registro->altura_maxima; ?> cm</span>'
                document.getElementById("checkout_altura").value = 0;
            }
            if (cmLargura > <?php echo $registro->largura_maxima; ?> && inputName == 'largura') {
                newPrice.innerHTML = '';
                newPrice.innerHTML = '<span style="color:red; font-weight: bold;">ATENÇÃO! O máximo valor permitido para largura é de: <?php echo $registro->largura_maxima; ?> cm</span>'
                document.getElementById("checkout_largura").value = 0;
            }

        }
    </script>
    <style>
        .new_amount {
            color: #f03764;
        }
    </style>
    <?php

}


// Atualizar o preço do produto com base nos campos personalizados
add_filter('woocommerce_add_cart_item_data', 'atualizar_preco_do_produto', 10, 2);

function atualizar_preco_do_produto($cart_item_data, $product_id)
{
    // Obter o valor dos campos personalizados
    $novo_preco = isset($_POST['price_updated']) ? $_POST['price_updated'] : '';
    $altura_updated = isset($_POST['altura_updated']) ? $_POST['altura_updated'] : '';
    $largura_updated = isset($_POST['largura_updated']) ? $_POST['largura_updated'] : '';

    // Armazenar o novo preço do produto no carrinho
    $cart_item_data['new_price'] = $novo_preco;
    $cart_item_data['altura_updated'] = $altura_updated;
    $cart_item_data['largura_updated'] = $largura_updated;

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


// Salvar os valores dos campos personalizados como metadados do pedido
add_action('woocommerce_process_shop_order_meta', 'save_custom_fields_editable', 10, 2);
function save_custom_fields_editable($post_id, $post)
{
    if (isset($_POST['altura_updated'])) {
        $altura_updated = sanitize_text_field($_POST['altura_updated']);
        if (!empty($altura_updated)) {
            update_post_meta($post_id, 'wp_plugin_custom_field_height', $altura_updated);
        }
    }
    if (isset($_POST['largura_updated'])) {
        $largura_updated = sanitize_text_field($_POST['largura_updated']);
        if (!empty($largura_updated)) {
            update_post_meta($post_id, 'wp_plugin_custom_field_width', $largura_updated);
        }
    }
}
// Exibir as informações personalizadas do pedido na página de edição de pedido

// Preenche a coluna com os valores do cart item data
add_action('woocommerce_checkout_create_order_line_item', 'checkout_create_order_line_item', 10, 4);
function checkout_create_order_line_item($item, $cart_item_key, $values, $order)
{
    if (isset($values['altura_updated'])) {
        $item->add_meta_data(
            __('Altura', 'plugin-republic'),
            $values['altura_updated'],
            true
        );
    }
    if (isset($values['largura_updated'])) {
        $item->add_meta_data(
            __('Largura', 'plugin-republic'),
            $values['largura_updated'],
            true
        );
    }
}

/* ./DETALHTES DO PEDIDO */

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