<?php
/**
 * Plugin Name: Change product height width plugin
 * Description: Cadastro global que se aplica a todos os produtos é possivel definir um limite máximo e mínimo da medida e também os valores de acordo com o tamanho.
 * Version: 1.0.0
 * Author: Bruno Carvalho
 * Author URI: https://brunofullstack.github.io/
 **/

 // Adiciona um novo campo no formulário de edição do produto
add_action( 'woocommerce_product_options_general_product_data', 'meu_plugin_custom_field' );
function meu_plugin_custom_field() {
    woocommerce_wp_text_input( array(
        'id'          => 'meu_plugin_custom_field',
        'label'       => __( 'Altura Inicial (cm)', 'meu-plugin' ),
        'placeholder' => '',
        'desc_tip'    => 'true',
        'description' => __( 'Digite aqui a descrição do campo personalizado.', 'meu-plugin' ),
    ) );
    woocommerce_wp_text_input( array(
        'id'          => 'meu_plugin_custom_field',
        'label'       => __( 'Largura Inicial (cm)', 'meu-plugin' ),
        'placeholder' => '',
        'desc_tip'    => 'true',
        'description' => __( 'Digite aqui a descrição do campo personalizado.', 'meu-plugin' ),
    ) );
}

// Salva o valor do campo personalizado
add_action( 'woocommerce_process_product_meta', 'meu_plugin_custom_field_save' );
function meu_plugin_custom_field_save( $post_id ) {
    $product = wc_get_product( $post_id );
    $custom_field = isset( $_POST['meu_plugin_custom_field'] ) ? $_POST['meu_plugin_custom_field'] : '';
    $product->update_meta_data( 'meu_plugin_custom_field', sanitize_text_field( $custom_field ) );
    $product->save();
}

?>