<?php

/*
 * Контроллер товаров
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Product;

use DB;


class ProductController extends Controller {
    /**
     * Отображает список заказчиков
     *
     * @return Response
     */
    public function showProducts() {
        //$products = Product::select()->orderBy('NAME')->take(20)->get();
        //var_dump($products);
        
        $filter = \DataFilter::source(new Product);
        //simple like 
        $filter->add('NAME', 'Наименование', 'text');
        $filter->submit('Поиск');
        $filter->reset('Сброс');

        $grid = \DataGrid::source($filter);

        // выводимые столбцы грида
        // Поле, подпись, сортируемый
        $grid->add('NAME', 'Наименование', true);
        $grid->add('PRICE|number_format[2,., ]', 'Стоимость');
        
        $grid->row(function($row) {
            // Денежные величины приживаем в право
            $row->cell('PRICE')->style("text-align: right");
        });         

        $grid->edit('/product/edit', 'Редактирование', 'show|modify|delete'); //shortcut to link DataEdit actions
        $grid->link('/product/edit', "Добавление товара", "TR");

        $grid->orderBy('NAME', 'asc'); // сортировка
        $grid->paginate(10); // количество записей на страницу

        return view('product', compact('filter', 'grid'));
    }  
    
    /**
     * Добавление, редактирование и удаление заказчика
     * 
     * @return Response
     */
    public function editProduct() {
        if (\Input::get('do_delete') == 1)
            return "not the first";
        $edit = \DataEdit::source(new Product());
        switch ($edit->status) {
            case 'create':
                $edit->label('Добавление товара');
                break;
            case 'modify':
                $edit->label('Редактирование товара');
                break;
            case 'do_delete':
                $edit->label('Удаление товара');
                break;
            case 'show':
                $edit->label('Карточка товара');
                $edit->link('products', 'Назад', 'TR');
                break;
        }

        $edit->back('insert|update|do_delete', 'products');

        $edit->add('NAME', 'Наименование', 'text')->rule('required|max:100');
        $edit->add('PRICE', 'Стоимость', 'text')->rule('max:19');
        $edit->add('DESCRIPTION', 'Описание', 'textarea')->attributes(['rows' => 8])->rule('max:8192');

        return $edit->view('product_edit', compact('edit'));
    }    
}

