<?php

echo form_open_multipart('admin/menus/add', array('role' => 'form'));

echo div(array('class' => 'form-group'));
echo form_label('Nome do menu', 'nome');
echo form_input(array('name' => 'nome', 'value' => set_value('nome'), 'class' => 'form-control'));
echo form_error('nome');
echo close_div();

echo row();

$options = config_item('pos_menus');

echo col(2);
echo div(array('class' => 'form-group'));
echo form_label('Posição', 'posicao');
echo form_dropdown('posicao', $options, null, null, 'form-control');
echo form_error('posicao');
echo close_div(2);

// Opções de status
$options = array(
    'lista' => 'Lista',
    'linha' => 'Linha',
    'coluna' => 'Coluna'
);


echo col(3);
echo div(array('class' => 'form-group'));
echo form_label('Estilo', 'estilo');
echo form_dropdown('estilo', $options, null, null, 'form-control');
echo close_div(3);

echo row();
echo col();
echo form_button(
        array(
            'type' => 'submit',
            'name' => 'submit',
            'content' => 'Cadastrar',
            'class' => 'btn btn-primary'
        )
);
echo nbs();
echo anchor('admin/menus', 'Cancelar', array('class' => 'btn btn-danger'));
echo close_div(2);

echo form_close();
