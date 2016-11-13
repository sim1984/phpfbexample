<?php

/*
 * Контроллер счёт фактур
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Invoice;
use App\Customer;
use App\Product;
use App\InvoiceLine;

class InvoiceController extends Controller {

    /**
     * Отображает список счёт-фактур
     *
     * @return Response
     */
    public function showInvoices() {
        //$invoices = Invoice::with('customer')->orderBy('INVOICE_DATE')->take(20)->get();
        //var_dump($invoices);

        $invoices = Invoice::with('customer');

        $filter = \DataFilter::source($invoices);
        $filter->add('INVOICE_DATE', 'Дата', 'daterange');
        $filter->add('customer.NAME', 'Заказчик', 'text');
        $filter->submit('Поиск');
        $filter->reset('Сброс');

        $grid = \DataGrid::source($filter);

        // выводимые столбцы грида
        // Поле, подпись, сортируемый
        $grid->add('INVOICE_DATE|strtotime|date[d.m.Y H:i:s]', 'Дата', true);
        $grid->add('TOTAL_SALE|number_format[2,., ]', 'Сумма');
        $grid->add('customer.NAME', 'Заказчик');
        $grid->add('PAID', 'Оплачено')
                ->cell(function( $value, $row) {
                    return $value ? 'Да' : 'Нет';
                });

        $grid->row(function($row) {
            // Денежные величины приживаем в право
            $row->cell('TOTAL_SALE')->style("text-align: right");
            // окрашиваем оплаченные в другой цвет
            if ($row->cell('PAID')->value == 'Да') {
                $row->style("background-color: #ddffee;");
            }
        });

        //shortcut to link DataEdit actions
        $grid->edit('/invoice/edit', 'Редактирование', 'show|modify|delete');
        $grid->link('/invoice/edit', "Добавление счёта", "TR");

        $grid->orderBy('INVOICE_DATE', 'desc'); // сортировка
        $grid->paginate(10); // количество записей на страницу

        return view('invoice', compact('filter', 'grid'));
    }

    /**
     * Добавление, редактирование и удаление счет фактуры
     * 
     * @return Response
     */
    public function editInvoice() {
        
        $error_msg = \Request::old('error_msg');

        $edit = \DataEdit::source(new Invoice());
        
        if (($edit->model->PAID) && ($edit->status === 'modify')) {
            $edit->status = 'show';
            $error_msg = 'Редактирование не возможно. Счёт уже оплачен.';
        }
        
        if (($edit->model->PAID) && ($edit->status === 'delete')) {
            $edit->status = 'show';
            $error_msg = 'Удаление не возможно. Счёт уже оплачен.';
        }        
        
        switch ($edit->status) {
            case 'create':
                $edit->label('Добавление счета');
                break;
            case 'modify':
                $edit->label('Редактирование счета');
                break;
            case 'do_delete':
                $edit->label('Удаление счета');
                break;
            case 'show':
                $edit->label('Счет');
                $edit->link('invoices', 'Назад', 'TR');
                if (!$edit->model->PAID)
                    $edit->link('invoice/pay/' . $edit->model->INVOICE_ID, 'Оплатить', 'BL');
                break;
        }


        $edit->back('insert|update|do_delete', 'invoices');


        $edit->add('INVOICE_DATE', 'Дата', 'datetime')
                ->rule('required')
                ->insertValue(date('Y-m-d H:i:s'));

        $edit->add('customer.NAME', 'Заказчик', 'autocomplete')
                ->rule('required')
                ->options(Customer::lists('NAME', 'CUSTOMER_ID')->all());

        $edit->add('TOTAL_SALE', 'Сумма', 'text')
                ->mode('readonly')
                ->insertValue('0.00');

        $paidCheckbox = $edit->add('PAID', 'Оплачено', 'checkbox')
                ->insertValue('0')
                ->mode('readonly');
        $paidCheckbox->checked_output = 'Да';
        $paidCheckbox->unchecked_output = 'Нет';


        $grid = $this->getInvoiceLineGrid($edit->model, $edit->status);

        return $edit->view('invoice_edit', compact('edit', 'grid', 'error_msg'));
    }

    public function payInvoice($id) {
        try {
            $invoice = Invoice::findOrFail($id);
            $invoice->pay();
        } catch (\Illuminate\Database\QueryException $e) {
            $pos = strpos($e->getMessage(), 'E_INVOICE_ALREADY_PAYED');
            if ($pos !== false) {
                return redirect('invoice/edit?show=' . $id)
                                ->withInput(['error_msg' => 'Счёт уже оплачен']);
            } else
                throw $e;
        }
        return redirect('invoice/edit?show=' . $id);
    }

    /**
     * Получение грида для строк счета фактуры
     * @param \App\Invoice $invoice
     * @param string $mode 
     * @return \DataGrid
     */
    private function getInvoiceLineGrid(Invoice $invoice, $mode) {

        $lines = InvoiceLine::with('product')->where('INVOICE_ID', $invoice->INVOICE_ID);


        $grid = \DataGrid::source($lines);

        $grid->add('product.NAME', 'Наименование');
        $grid->add('QUANTITY', 'Количество');
        $grid->add('SALE_PRICE|number_format[2,., ]', 'Стоимость')->style('min-width: 8em;');
        $grid->add('SUM_PRICE|number_format[2,., ]', 'Сумма')->style('min-width: 8em;');

        $grid->row(function($row) {
            $row->cell('QUANTITY')->style("text-align: right");
            // Денежные величины приживаем в право
            $row->cell('SALE_PRICE')->style("text-align: right");
            $row->cell('SUM_PRICE')->style("text-align: right");
        });

        //shortcut to link DataEdit actions
        if ($mode == 'modify') {
            $grid->edit('/invoice/editline', 'Редактирование', 'modify|delete');
            $grid->link('/invoice/editline?invoice_id=' . $invoice->INVOICE_ID, "Добавление позиции", "TR");
        }

        return $grid;
    }

    public function editInvoiceLine() {
        if (\Input::get('do_delete') == 1)
            return "not the first";

        $invoice_id = null;

        $edit = \DataEdit::source(new InvoiceLine());
        switch ($edit->status) {
            case 'create':
                $edit->label('Добавление позиции');
                $invoice_id = \Input::get('invoice_id');
                break;
            case 'modify':
                $edit->label('Редактирование позиции');
                $invoice_id = $edit->model->INVOICE_ID;
                break;
            case 'delete':
                $invoice_id = $edit->model->INVOICE_ID;
                break;
            case 'do_delete':
                $edit->label('Удаление позиции');
                $invoice_id = $edit->model->INVOICE_ID;
                break;
        }

        $base = str_replace(\Request::path(), '', strtok(\Request::fullUrl(), '?'));

        $back_url = $base . 'invoice/edit?modify=' . $invoice_id;
        //dd($back_url);

        $edit->back('insert|update|do_delete', $back_url);
        $edit->back_url = $back_url;

        $edit->add('INVOICE_ID', '', 'hidden')
                ->rule('required')
                ->insertValue($invoice_id)
                ->updateValue($invoice_id);

        $edit->add('product.NAME', 'Наименование', 'autocomplete')
                ->rule('required')
                ->options(Product::lists('NAME', 'PRODUCT_ID')->all());
        $edit->add('QUANTITY', 'Количество', 'text')
                ->rule('required');

        return $edit->view('invoice_line_edit', compact('edit'));
    }

}
