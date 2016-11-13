<?php

/*
 * Контроллер заказчиков
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Customer;


class CustomerController extends Controller {

    /**
     * Отображает список заказчиков
     *
     * @return Response
     */
    public function showCustomers() {
        $filter = \DataFilter::source(new Customer);
        //simple like 
        $filter->add('NAME', 'Наименование', 'text');
        $filter->submit('Поиск');
        $filter->reset('Сброс');

        //$customers = Customer::select()->orderBy('NAME')->take(20)->get();
        $grid = \DataGrid::source($filter);

        // выводимые столбцы грида
        // Поле, подпись, сортируемый
        $grid->add('NAME', 'Наименование', true);
        $grid->add('ADDRESS', 'Адрес');
        $grid->add('ZIPCODE', 'Индекс');
        $grid->add('PHONE', 'Телефон');

        $grid->edit('/customer/edit', 'Редактирование', 'show|modify|delete'); //shortcut to link DataEdit actions
        $grid->link('/customer/edit', "Добавление заказчика", "TR");

        $grid->orderBy('NAME', 'asc'); // сортировка
        $grid->paginate(10); // количество записей на страницу

        return view('customer', compact('filter', 'grid'));
    }

    /**
     * Добавление, редактирование и удаление заказчика
     * 
     * @return Response
     */
    public function editCustomer() {
        if (\Input::get('do_delete') == 1)
            return "not the first";
        $edit = \DataEdit::source(new Customer());
        switch ($edit->status) {
            case 'create':
                $edit->label('Добавление заказчика');
                break;
            case 'modify':
                $edit->label('Редактирование заказчика');
                break;
            case 'do_delete':
                $edit->label('Удаление заказчика');
                break;
            case 'show':
                $edit->label('Карточка заказчика');
                $edit->link('customers', 'Назад', 'TR');
                break;
        }

        $edit->back('insert|update|do_delete', 'customers');

        $edit->add('NAME', 'Наименование', 'text')->rule('required|max:60');
        $edit->add('ADDRESS', 'Адрес', 'textarea')->attributes(['rows' => 3])->rule('max:250');
        $edit->add('ZIPCODE', 'Индекс', 'text')->rule('max:10');
        $edit->add('PHONE', 'Телефон', 'text')->rule('max:14');

        return $edit->view('customer_edit', compact('edit'));
    }

}
